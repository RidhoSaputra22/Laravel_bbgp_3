<?php

namespace App\Services\Assessment;

use App\Enum\AssessmentKetenagaanType;
use App\Models\Assessment;
use App\Models\AssessmentAssignment;
use App\Models\AssessmentCombination;
use App\Models\User;
use App\Models\ValidatorAssignment;
use App\Models\ValidatorForm;
use App\Models\ValidatorFormField;
use App\Support\Assessment\ValidatorAccess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ValidatorAssignmentService
{
    public function createForAllEligibleValidators(array $data, ?int $assignedBy): array
    {
        $sourceMode = $this->normalizeSourceMode($data['source_mode'] ?? null);
        $validators = ValidatorAccess::eligibleUsersQuery()->with('guru')->get();

        if ($validators->isEmpty()) {
            throw ValidationException::withMessages([
                'validators' => 'Belum ada akun Stakeholder dengan jabatan Validator.',
            ]);
        }

        $sourceQuery = AssessmentAssignment::active()
            ->whereIn('target_ketenagaan', [
                AssessmentKetenagaanType::TENAGA_PENDIDIK->value,
                AssessmentKetenagaanType::TENAGA_KEPENDIDIKAN->value,
            ])
            ->has('assessments');

        if ($sourceMode === ValidatorAssignment::SOURCE_MODE_COMBINATION
            && Schema::hasTable('assessment_combinations')) {
            $sourceQuery->where(function ($query) {
                $query->whereHas(
                    'combination',
                    fn ($combinationQuery) => $combinationQuery->where('is_active', true)
                );

                if (Schema::hasTable('assessment_assignment_targets')) {
                    $query->orWhereHas(
                        'targets',
                        fn ($targetQuery) => $targetQuery
                            ->whereNotNull('assessment_combination_id')
                            ->whereHas('combination', fn ($combinationQuery) => $combinationQuery->where('is_active', true))
                    );
                }
            });
        }

        $sourceIds = $sourceQuery->pluck('id');

        if ($sourceIds->isEmpty()) {
            throw ValidationException::withMessages([
                'assessment_assignments' => $sourceMode === ValidatorAssignment::SOURCE_MODE_COMBINATION
                    ? 'Belum ada penugasan assessment aktif dengan kombinasi soal untuk Tenaga Pendidik atau Tenaga Kependidikan.'
                    : 'Belum ada penugasan assessment aktif untuk Tenaga Pendidik atau Tenaga Kependidikan.',
            ]);
        }

        $data['assessment_assignment_ids'] = $sourceIds->all();
        $data['source_mode'] = $sourceMode;
        $combinationQueues = $sourceMode === ValidatorAssignment::SOURCE_MODE_COMBINATION
            ? $this->buildCombinationQueues($sourceIds)
            : [];
        $created = 0;
        $skipped = 0;

        DB::transaction(function () use (
            $data,
            $assignedBy,
            $validators,
            $sourceIds,
            $sourceMode,
            $combinationQueues,
            &$created,
            &$skipped
        ) {
            foreach ($validators->values() as $validatorIndex => $validator) {
                $alreadyAssignedQuery = ValidatorAssignment::query()
                    ->where('validator_form_id', (int) $data['validator_form_id'])
                    ->where('validator_user_id', $validator->id)
                    ->whereIn('status', ['assigned', 'in_progress'])
                    ->whereHas('assessmentAssignments', fn ($query) => $query->whereIn(
                        'assessment_assignments.id',
                        $sourceIds
                    ));
                $this->applySourceModeFilter($alreadyAssignedQuery, $sourceMode);
                $alreadyAssigned = $alreadyAssignedQuery->exists();

                if ($alreadyAssigned) {
                    $skipped++;

                    continue;
                }

                $selectedCombinationIds = [];
                foreach ($sourceIds as $sourceId) {
                    $queue = $combinationQueues[(int) $sourceId] ?? [];

                    if ($queue !== []) {
                        $selectedCombinationIds[(int) $sourceId] = $queue[$validatorIndex % count($queue)];
                    }
                }

                $this->create(array_merge($data, [
                    'validator_user_id' => $validator->id,
                    'selected_combination_ids' => $selectedCombinationIds,
                ]), $assignedBy);
                $created++;
            }
        });

        return compact('created', 'skipped');
    }

    public function create(array $data, ?int $assignedBy): ValidatorAssignment
    {
        $sourceMode = $this->normalizeSourceMode($data['source_mode'] ?? null);
        $validator = User::with('guru')->findOrFail((int) $data['validator_user_id']);

        if (! ValidatorAccess::isEligibleUser($validator)) {
            throw ValidationException::withMessages([
                'validator_user_id' => 'User yang dipilih harus memiliki role Stakeholder dan jabatan Validator.',
            ]);
        }

        $form = ValidatorForm::with('sections.fields')->findOrFail((int) $data['validator_form_id']);
        $requestedIds = collect($data['assessment_assignment_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($requestedIds->isEmpty()) {
            throw ValidationException::withMessages([
                'assessment_assignment_ids' => 'Minimal satu penugasan assessment aktif wajib dipilih.',
            ]);
        }

        $assignmentQuery = AssessmentAssignment::active()
            ->whereIn('target_ketenagaan', [
                AssessmentKetenagaanType::TENAGA_PENDIDIK->value,
                AssessmentKetenagaanType::TENAGA_KEPENDIDIKAN->value,
            ])
            ->whereIn('id', $requestedIds);

        $relations = ['assessments'];
        if ($sourceMode === ValidatorAssignment::SOURCE_MODE_COMBINATION
            && Schema::hasTable('assessment_combinations')) {
            $relations[] = 'combination';
        } else {
            $relations[] = 'assessments.forms.fields';
        }

        $assignmentLookup = $assignmentQuery
            ->with($relations)
            ->get()
            ->keyBy('id');
        $sourceAssignments = $requestedIds->map(fn ($id) => $assignmentLookup->get($id))->filter()->values();

        if ($form->status !== 'published' || ! $form->is_active) {
            throw ValidationException::withMessages([
                'validator_form_id' => 'Form validator harus berstatus dipublikasikan dan aktif.',
            ]);
        }

        if ($sourceAssignments->count() !== $requestedIds->count()) {
            throw ValidationException::withMessages([
                'assessment_assignment_ids' => 'Pilihan harus berupa penugasan aktif milik Tenaga Pendidik atau Tenaga Kependidikan.',
            ]);
        }

        if ($sourceAssignments->contains(fn ($source) => $source->assessments->isEmpty())) {
            throw ValidationException::withMessages([
                'assessment_assignment_ids' => 'Setiap penugasan yang dipilih harus memiliki minimal satu assessment.',
            ]);
        }

        $selectedCombinationIds = $sourceMode === ValidatorAssignment::SOURCE_MODE_COMBINATION
            ? $this->normalizeSelectedCombinationIds($data['selected_combination_ids'] ?? [])
            : [];
        $combinationSnapshots = $sourceMode === ValidatorAssignment::SOURCE_MODE_COMBINATION
            ? $this->loadCombinationSnapshots($sourceAssignments, $selectedCombinationIds)
            : [];

        if ($sourceMode === ValidatorAssignment::SOURCE_MODE_COMBINATION
            && $sourceAssignments->contains(
            fn (AssessmentAssignment $source) => ! $this->hasCombinationSnapshot(
                $combinationSnapshots[(int) $source->id] ?? []
            )
        )) {
            throw ValidationException::withMessages([
                'assessment_assignment_ids' => 'Setiap penugasan assessment yang dipilih harus memiliki kombinasi soal aktif.',
            ]);
        }

        $alreadyAssignedQuery = ValidatorAssignment::query()
            ->where('validator_form_id', $form->id)
            ->where('validator_user_id', $validator->id)
            ->whereIn('status', ['assigned', 'in_progress'])
            ->whereHas('assessmentAssignments', fn ($query) => $query->whereIn(
                'assessment_assignments.id',
                $requestedIds
            ));
        $this->applySourceModeFilter($alreadyAssignedQuery, $sourceMode);
        $alreadyAssigned = $alreadyAssignedQuery->exists();

        if ($alreadyAssigned) {
            throw ValidationException::withMessages([
                'assessment_assignment_ids' => 'Validator ini masih memiliki QA aktif dengan form yang sama pada salah satu penugasan yang dipilih.',
            ]);
        }

        return DB::transaction(function () use (
            $data,
            $assignedBy,
            $sourceAssignments,
            $combinationSnapshots,
            $sourceMode,
            $form,
            $validator
        ) {
            $firstAssessment = $sourceAssignments->first()->assessments->first();
            $snapshots = $sourceAssignments
                ->map(fn (AssessmentAssignment $source) => $this->buildAssessmentAssignmentSnapshot(
                    $source,
                    $combinationSnapshots[(int) $source->id] ?? [],
                    $sourceMode
                ))
                ->all();
            $firstAssessmentSnapshot = data_get($snapshots, '0.assessments.0');
            $attributes = [
                'code' => $this->generateCode(),
                'title' => $data['title'],
                'validator_form_id' => $form->id,
                'assessment_id' => $firstAssessmentSnapshot['id'] ?? $firstAssessment->id,
                'validator_user_id' => (int) $data['validator_user_id'],
                'assigned_by' => $assignedBy,
                'notes' => $data['notes'] ?? null,
                'assessment_snapshot' => $firstAssessmentSnapshot ?: $this->buildAssessmentSnapshot($firstAssessment),
                'assessment_assignment_snapshots' => $snapshots,
                'validator_snapshot' => [
                    'user_id' => $validator->id,
                    'guru_id' => $validator->guru?->id,
                    'name' => $validator->guru?->nama_lengkap ?? $validator->name,
                    'email' => $validator->guru?->email,
                    'no_ktp' => $validator->no_ktp,
                    'role' => $validator->role,
                    'eksternal_jabatan' => $validator->guru?->eksternal_jabatan,
                    'jenis_jabatan' => $validator->guru?->jenis_jabatan,
                ],
                'status' => 'assigned',
                'start_date' => $data['start_date'] ?? null,
                'due_date' => $data['due_date'] ?? null,
            ];

            if (Schema::hasColumn('validator_assignments', 'source_mode')) {
                $attributes['source_mode'] = $sourceMode;
            }

            $assignment = ValidatorAssignment::create($attributes);

            $assignment->assessmentAssignments()->attach(
                $sourceAssignments->values()->mapWithKeys(fn ($source, $index) => [
                    $source->id => ['sort_order' => $index + 1],
                ])->all()
            );

            return $assignment;
        });
    }

    public function saveResponses(ValidatorAssignment $assignment, array $answers): void
    {
        $fields = $assignment->validatorForm->sections
            ->flatMap->fields
            ->where('is_active', true)
            ->keyBy('id');

        DB::transaction(function () use ($assignment, $answers, $fields) {
            foreach ($fields as $fieldId => $field) {
                $answer = $answers[$fieldId] ?? null;

                $isArray = is_array($answer);
                $answerPayload = $isArray
                    ? collect($answer)->map(fn ($item) => trim((string) $item))->filter()->values()->all()
                    : null;
                $answerText = $isArray ? implode(', ', $answerPayload) : trim((string) $answer);

                if ($answerText === '') {
                    $assignment->responses()
                        ->where('validator_form_field_id', $field->id)
                        ->delete();

                    continue;
                }

                $assignment->responses()->updateOrCreate(
                    ['validator_form_field_id' => $field->id],
                    [
                        'answer_text' => $answerText !== '' ? $answerText : null,
                        'answer_payload' => $answerPayload,
                        'score' => $this->resolveScore($field, $answer),
                    ]
                );
            }

            if ($assignment->status === 'assigned') {
                $assignment->update([
                    'status' => 'in_progress',
                    'started_at' => $assignment->started_at ?: now(),
                ]);
            }
        });
    }

    public function submit(
        ValidatorAssignment $assignment,
        array $answers,
        string $recommendation,
        ?string $finalNotes
    ): void {
        DB::transaction(function () use ($assignment, $answers, $recommendation, $finalNotes) {
            $this->saveResponses($assignment, $answers);
            $assignment->load('responses.field');

            $scoreTotal = $assignment->responses
                ->filter(fn ($response) => $response->field?->is_scored && $response->score !== null)
                ->sum('score');
            $scoreMax = $assignment->validatorForm->sections
                ->flatMap->fields
                ->where('is_active', true)
                ->where('is_scored', true)
                ->sum(fn (ValidatorFormField $field) => (float) ($field->max_score ?: 0));

            $assignment->update([
                'status' => 'submitted',
                'started_at' => $assignment->started_at ?: now(),
                'submitted_at' => now(),
                'score_total' => $scoreMax > 0 ? $scoreTotal : null,
                'score_max' => $scoreMax > 0 ? $scoreMax : null,
                'score_percentage' => $scoreMax > 0 ? round(($scoreTotal / $scoreMax) * 100, 2) : null,
                'recommendation' => $recommendation,
                'final_notes' => $finalNotes,
            ]);
        });
    }

    public function reset(ValidatorAssignment $assignment): void
    {
        DB::transaction(function () use ($assignment) {
            $assignment->responses()->delete();
            $assignment->update([
                'status' => 'assigned',
                'started_at' => null,
                'submitted_at' => null,
                'score_total' => null,
                'score_max' => null,
                'score_percentage' => null,
                'recommendation' => null,
                'final_notes' => null,
            ]);
        });
    }

    private function resolveScore(ValidatorFormField $field, mixed $answer): ?float
    {
        if (! $field->is_scored || is_array($answer) || ! is_numeric($answer)) {
            return null;
        }

        $score = (float) $answer;

        if ($field->max_score !== null) {
            $score = min($score, (float) $field->max_score);
        }

        return max(0, $score);
    }

    private function generateCode(): string
    {
        do {
            $code = 'VAL-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
        } while (ValidatorAssignment::where('code', $code)->exists());

        return $code;
    }

    private function buildAssessmentSnapshot(Assessment $assessment): array
    {
        return [
            'id' => $assessment->id,
            'code' => $assessment->kode_assessment,
            'title' => $assessment->judul,
            'description' => $assessment->deskripsi,
            'instructions' => $assessment->petunjuk,
            'instrument_type' => $assessment->instrument_type,
            'target_ketenagaan' => $assessment->target_ketenagaan,
            'status' => $assessment->status,
            'captured_at' => now()->toIso8601String(),
            'forms' => $assessment->forms->map(fn ($form) => [
                'id' => $form->id,
                'code' => $form->kode_form,
                'title' => $form->judul_form,
                'description' => $form->deskripsi,
                'fields' => $form->fields->map(fn ($field) => [
                    'id' => $field->id,
                    'label' => $field->label,
                    'description' => $field->deskripsi,
                    'type' => $field->tipe_field,
                    'options' => $field->opsi_field,
                    'required' => $field->is_required,
                ])->values()->all(),
            ])->values()->all(),
        ];
    }

    private function buildAssessmentAssignmentSnapshot(
        AssessmentAssignment $assignment,
        array $combinationSnapshots = [],
        string $sourceMode = ValidatorAssignment::SOURCE_MODE_COMBINATION
    ): array
    {
        if ($sourceMode === ValidatorAssignment::SOURCE_MODE_COMBINATION) {
            $combinationSnapshots = $combinationSnapshots !== []
                ? $combinationSnapshots
                : ($this->combinationSnapshot($assignment) ? [$this->combinationSnapshot($assignment)] : []);
        } else {
            $combinationSnapshots = [];
        }

        $combinationId = $sourceMode === ValidatorAssignment::SOURCE_MODE_COMBINATION
            ? (int) data_get($combinationSnapshots, '0.combination.id', 0)
            : 0;

        return [
            'id' => $assignment->id,
            'code' => $assignment->kode_penugasan,
            'title' => $assignment->judul_penugasan,
            'combination_id' => $combinationId > 0 ? $combinationId : null,
            'is_active' => (bool) $assignment->is_active,
            'status_distribusi' => $assignment->status_distribusi,
            'target_ketenagaan' => $assignment->target_ketenagaan,
            'target_ketenagaan_label' => $assignment->target_ketenagaan_label,
            'description' => $assignment->deskripsi,
            'start_date' => $assignment->tanggal_mulai?->toDateString(),
            'end_date' => $assignment->tanggal_selesai?->toDateString(),
            'total_target' => $assignment->total_target,
            'captured_at' => now()->toIso8601String(),
            'assessments' => $combinationSnapshots !== []
                ? $this->buildCombinationAssessmentSnapshots($combinationSnapshots)
                : $assignment->assessments
                    ->map(fn (Assessment $assessment) => $this->buildAssessmentSnapshot($assessment))
                    ->values()
                    ->all(),
        ];
    }

    private function hasCombinationSnapshot(array $snapshots): bool
    {
        if (! Schema::hasTable('assessment_combinations')) {
            return true;
        }

        return $snapshots !== [];
    }

    private function normalizeSourceMode(?string $sourceMode): string
    {
        return in_array($sourceMode, ValidatorAssignment::SOURCE_MODES, true)
            ? $sourceMode
            : ValidatorAssignment::SOURCE_MODE_COMBINATION;
    }

    private function applySourceModeFilter($query, string $sourceMode): void
    {
        if (! Schema::hasColumn('validator_assignments', 'source_mode')) {
            return;
        }

        $query->where(function ($modeQuery) use ($sourceMode) {
            $modeQuery->where('source_mode', $sourceMode);

            if ($sourceMode === ValidatorAssignment::SOURCE_MODE_COMBINATION) {
                $modeQuery->orWhereNull('source_mode');
            }
        });
    }

    /**
     * Resolve assignment-level combinations and the combinations actually
     * assigned to participants. The latter is needed for legacy assignments
     * whose combination_id is null but whose targets already have combinations.
     *
     * @return array<int, array<int, array<string, mixed>>>
     */
    private function loadCombinationSnapshots(Collection $assignments, array $selectedCombinationIds = []): array
    {
        if (! Schema::hasTable('assessment_combinations')) {
            return [];
        }

        $snapshotsByAssignment = $assignments
            ->mapWithKeys(function (AssessmentAssignment $assignment) {
                $snapshot = $this->combinationSnapshot($assignment);

                return [(int) $assignment->id => $snapshot ? [$snapshot] : []];
            })
            ->all();

        if (! Schema::hasTable('assessment_assignment_targets')) {
            return $snapshotsByAssignment;
        }

        $targetRows = DB::table('assessment_assignment_targets')
            ->whereIn('assessment_assignment_id', $assignments->pluck('id')->all())
            ->whereNotNull('assessment_combination_id')
            ->distinct()
            ->get(['assessment_assignment_id', 'assessment_combination_id']);
        $combinationIds = $targetRows
            ->pluck('assessment_combination_id')
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->merge(array_values($selectedCombinationIds))
            ->unique()
            ->values();

        if ($combinationIds->isEmpty()) {
            return $snapshotsByAssignment;
        }

        $combinationSnapshots = AssessmentCombination::query()
            ->whereIn('id', $combinationIds)
            ->where('is_active', true)
            ->orderByDesc('id')
            ->get(['id', 'structure_snapshot'])
            ->mapWithKeys(fn (AssessmentCombination $combination) => [
                (int) $combination->id => is_array($combination->structure_snapshot)
                    ? $combination->structure_snapshot
                    : null,
            ]);

        foreach ($targetRows->groupBy('assessment_assignment_id') as $assignmentId => $rows) {
            $selectedCombinationId = (int) ($selectedCombinationIds[(int) $assignmentId] ?? 0);

            if ($selectedCombinationId > 0) {
                $snapshot = $combinationSnapshots->get($selectedCombinationId);

                if (is_array($snapshot) && ! empty($snapshot['assessments'])) {
                    $snapshotsByAssignment[(int) $assignmentId] = [$snapshot];
                }

                continue;
            }

            if (($snapshotsByAssignment[(int) $assignmentId] ?? []) !== []) {
                continue;
            }

            $rows = $rows->sortByDesc('assessment_combination_id');

            foreach ($rows as $targetRow) {
                $combinationId = (int) $targetRow->assessment_combination_id;
                $snapshot = $combinationSnapshots->get($combinationId);

                if (is_array($snapshot) && ! empty($snapshot['assessments'])) {
                    $snapshotsByAssignment[(int) $assignmentId] = [$snapshot];
                    break;
                }
            }
        }

        return $snapshotsByAssignment;
    }

    private function buildCombinationQueues(Collection $sourceIds): array
    {
        if (! Schema::hasTable('assessment_combinations')) {
            return [];
        }

        $assignmentIds = $sourceIds->map(fn ($id) => (int) $id)->values();
        $combinationIdsByAssignment = $assignmentIds->mapWithKeys(fn (int $assignmentId) => [
            $assignmentId => [],
        ])->all();

        if (Schema::hasColumn('assessment_assignments', 'assessment_combination_id')) {
            DB::table('assessment_assignments')
                ->whereIn('id', $assignmentIds->all())
                ->whereNotNull('assessment_combination_id')
                ->get(['id', 'assessment_combination_id'])
                ->each(function ($row) use (&$combinationIdsByAssignment) {
                    $combinationIdsByAssignment[(int) $row->id][] = (int) $row->assessment_combination_id;
                });
        }

        if (Schema::hasTable('assessment_assignment_targets')) {
            DB::table('assessment_assignment_targets')
                ->whereIn('assessment_assignment_id', $assignmentIds->all())
                ->whereNotNull('assessment_combination_id')
                ->distinct()
                ->get(['assessment_assignment_id', 'assessment_combination_id'])
                ->each(function ($row) use (&$combinationIdsByAssignment) {
                    $combinationIdsByAssignment[(int) $row->assessment_assignment_id][] = (int) $row->assessment_combination_id;
                });
        }

        $combinationIds = collect($combinationIdsByAssignment)
            ->flatten()
            ->filter()
            ->unique()
            ->values();
        $activeIds = AssessmentCombination::query()
            ->whereIn('id', $combinationIds->all())
            ->where('is_active', true)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return collect($combinationIdsByAssignment)
            ->map(fn (array $ids) => collect($ids)
                ->filter(fn (int $id) => in_array($id, $activeIds, true))
                ->unique()
                ->shuffle()
                ->values()
                ->all())
            ->all();
    }

    private function normalizeSelectedCombinationIds(mixed $selectedCombinationIds): array
    {
        return collect((array) $selectedCombinationIds)
            ->mapWithKeys(fn ($combinationId, $assignmentId) => [
                (int) $assignmentId => (int) $combinationId,
            ])
            ->filter(fn (int $combinationId, int $assignmentId) => $assignmentId > 0 && $combinationId > 0)
            ->all();
    }

    private function combinationSnapshot(AssessmentAssignment $assignment): ?array
    {
        $combination = $assignment->relationLoaded('combination')
            ? $assignment->getRelation('combination')
            : null;

        if (! $combination || $combination->is_active === false) {
            return null;
        }

        $snapshot = $combination->structure_snapshot;

        return is_array($snapshot) && ! empty($snapshot['assessments']) ? $snapshot : null;
    }

    private function buildCombinationAssessmentSnapshots(array $snapshots): array
    {
        $assessments = [];

        foreach ($snapshots as $snapshot) {
            foreach ($snapshot['assessments'] ?? [] as $assessment) {
                if (! is_array($assessment) || ! isset($assessment['id'])) {
                    continue;
                }

                $assessmentId = (int) $assessment['id'];
                $assessments[$assessmentId] ??= [
                    'id' => $assessmentId,
                    'code' => $assessment['code'] ?? $assessment['kode_assessment'] ?? null,
                    'title' => $assessment['title'] ?? $assessment['judul'] ?? null,
                    'description' => $assessment['description'] ?? $assessment['deskripsi'] ?? null,
                    'instructions' => $assessment['instructions'] ?? $assessment['petunjuk'] ?? null,
                    'instrument_type' => $assessment['instrument_type'] ?? null,
                    'target_ketenagaan' => $assessment['target_ketenagaan'] ?? null,
                    'status' => $assessment['status'] ?? null,
                    'captured_at' => $snapshot['generated_at'] ?? now()->toIso8601String(),
                    'forms' => [],
                ];

                foreach ($assessment['forms'] ?? [] as $form) {
                    if (! is_array($form) || ! isset($form['id'])) {
                        continue;
                    }

                    $formId = (int) $form['id'];
                    $assessments[$assessmentId]['forms'][$formId] ??= [
                        'id' => $formId,
                        'code' => $form['code'] ?? $form['kode_form'] ?? null,
                        'title' => $form['title'] ?? $form['judul_form'] ?? null,
                        'description' => $form['description'] ?? $form['deskripsi'] ?? null,
                        'fields' => [],
                    ];

                    foreach ($form['fields'] ?? [] as $field) {
                        if (! is_array($field) || ! isset($field['id'])) {
                            continue;
                        }

                        $fieldId = (int) $field['id'];
                        $assessments[$assessmentId]['forms'][$formId]['fields'][$fieldId] = [
                            'id' => $fieldId,
                            'label' => $field['label'] ?? null,
                            'description' => $field['description'] ?? $field['deskripsi'] ?? null,
                            'type' => $field['type'] ?? $field['tipe_field'] ?? null,
                            'options' => $field['options'] ?? $field['opsi_field'] ?? null,
                            'required' => (bool) ($field['required'] ?? $field['is_required'] ?? false),
                        ];
                    }
                }
            }
        }

        return collect($assessments)
            ->map(function (array $assessment) {
                $assessment['forms'] = collect($assessment['forms'])
                    ->map(function (array $form) {
                        $form['fields'] = array_values($form['fields']);

                        return $form;
                    })
                    ->values()
                    ->all();

                return $assessment;
            })
            ->values()
            ->all();
    }
}
