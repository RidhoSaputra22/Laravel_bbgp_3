<?php

namespace App\Services\Assessment;

use App\Enum\AssessmentKetenagaanType;
use App\Models\Assessment;
use App\Models\AssessmentAssignment;
use App\Models\User;
use App\Models\ValidatorAssignment;
use App\Models\ValidatorForm;
use App\Models\ValidatorFormField;
use App\Support\Assessment\ValidatorAccess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ValidatorAssignmentService
{
    public function createForAllEligibleValidators(array $data, ?int $assignedBy): array
    {
        $validators = ValidatorAccess::eligibleUsersQuery()->with('guru')->get();

        if ($validators->isEmpty()) {
            throw ValidationException::withMessages([
                'validators' => 'Belum ada akun Stakeholder dengan jabatan Validator.',
            ]);
        }

        $sourceIds = AssessmentAssignment::active()
            ->whereIn('target_ketenagaan', [
                AssessmentKetenagaanType::TENAGA_PENDIDIK->value,
                AssessmentKetenagaanType::TENAGA_KEPENDIDIKAN->value,
            ])
            ->has('assessments')
            ->pluck('id');

        if ($sourceIds->isEmpty()) {
            throw ValidationException::withMessages([
                'assessment_assignments' => 'Belum ada penugasan assessment aktif untuk Tenaga Pendidik atau Tenaga Kependidikan.',
            ]);
        }

        $data['assessment_assignment_ids'] = $sourceIds->all();
        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($data, $assignedBy, $validators, $sourceIds, &$created, &$skipped) {
            foreach ($validators as $validator) {
                $alreadyAssigned = ValidatorAssignment::query()
                    ->where('validator_form_id', (int) $data['validator_form_id'])
                    ->where('validator_user_id', $validator->id)
                    ->whereIn('status', ['assigned', 'in_progress'])
                    ->whereHas('assessmentAssignments', fn ($query) => $query->whereIn(
                        'assessment_assignments.id',
                        $sourceIds
                    ))
                    ->exists();

                if ($alreadyAssigned) {
                    $skipped++;

                    continue;
                }

                $this->create(array_merge($data, ['validator_user_id' => $validator->id]), $assignedBy);
                $created++;
            }
        });

        return compact('created', 'skipped');
    }

    public function create(array $data, ?int $assignedBy): ValidatorAssignment
    {
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

        $assignmentLookup = AssessmentAssignment::active()
            ->whereIn('target_ketenagaan', [
                AssessmentKetenagaanType::TENAGA_PENDIDIK->value,
                AssessmentKetenagaanType::TENAGA_KEPENDIDIKAN->value,
            ])
            ->whereIn('id', $requestedIds)
            ->with('assessments.forms.fields')
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

        $alreadyAssigned = ValidatorAssignment::query()
            ->where('validator_form_id', $form->id)
            ->where('validator_user_id', $validator->id)
            ->whereIn('status', ['assigned', 'in_progress'])
            ->whereHas('assessmentAssignments', fn ($query) => $query->whereIn(
                'assessment_assignments.id',
                $requestedIds
            ))
            ->exists();

        if ($alreadyAssigned) {
            throw ValidationException::withMessages([
                'assessment_assignment_ids' => 'Validator ini masih memiliki QA aktif dengan form yang sama pada salah satu penugasan yang dipilih.',
            ]);
        }

        return DB::transaction(function () use ($data, $assignedBy, $sourceAssignments, $form, $validator) {
            $firstAssessment = $sourceAssignments->first()->assessments->first();
            $snapshots = $sourceAssignments
                ->map(fn (AssessmentAssignment $source) => $this->buildAssessmentAssignmentSnapshot($source))
                ->all();
            $assignment = ValidatorAssignment::create([
                'code' => $this->generateCode(),
                'title' => $data['title'],
                'validator_form_id' => $form->id,
                'assessment_id' => $firstAssessment->id,
                'validator_user_id' => (int) $data['validator_user_id'],
                'assigned_by' => $assignedBy,
                'notes' => $data['notes'] ?? null,
                'assessment_snapshot' => $this->buildAssessmentSnapshot($firstAssessment),
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
            ]);

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

    private function buildAssessmentAssignmentSnapshot(AssessmentAssignment $assignment): array
    {
        return [
            'id' => $assignment->id,
            'code' => $assignment->kode_penugasan,
            'title' => $assignment->judul_penugasan,
            'target_ketenagaan' => $assignment->target_ketenagaan,
            'target_ketenagaan_label' => $assignment->target_ketenagaan_label,
            'description' => $assignment->deskripsi,
            'start_date' => $assignment->tanggal_mulai?->toDateString(),
            'end_date' => $assignment->tanggal_selesai?->toDateString(),
            'total_target' => $assignment->total_target,
            'captured_at' => now()->toIso8601String(),
            'assessments' => $assignment->assessments
                ->map(fn (Assessment $assessment) => $this->buildAssessmentSnapshot($assessment))
                ->values()
                ->all(),
        ];
    }
}
