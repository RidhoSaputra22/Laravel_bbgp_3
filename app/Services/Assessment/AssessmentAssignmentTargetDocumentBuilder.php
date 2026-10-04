<?php

namespace App\Services\Assessment;

use App\Enum\AssessmentInstrumentType;
use App\Enum\AssessmentKetenagaanType;
use App\Models\AssessmentAssignment;
use App\Models\AssessmentAssignmentTarget;
use App\Models\Guru;
use App\Support\Assessment\ParticipantAutoFillResolver;
use App\Support\Assessment\ScoringConfigNormalizer;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class AssessmentAssignmentTargetDocumentBuilder
{
    public const SCHEMA_VERSION = 'assessment_assignment-target-v1';

    /** @var array<int, AssessmentAssignment> */
    private array $assignmentCache = [];

    /** @var array<int, bool> */
    private array $assignmentSchemaLoaded = [];

    /** @var array<string, array> */
    private array $reusableSnapshots = [];

    public function __construct(
        private readonly AssessmentQuestionRandomizerService $randomizer,
        private readonly ScoringConfigNormalizer $scoringConfigNormalizer,
        private readonly ?ParticipantAutoFillResolver $participantAutoFillResolver = null
    ) {}

    /**
     * Keep the backfill/job query limited to columns used by the projection.
     *
     * @return array<int, string>
     */
    public static function targetColumns(): array
    {
        return [
            'id',
            'assessment_assignment_id',
            'assessment_assignment_session_id',
            'assessment_combination_id',
            'guru_id',
            'is_validator',
            'status',
            'assigned_at',
            'started_at',
            'deadline_at',
            'submitted_at',
            'completion_mode',
            'timed_out_at',
            'updated_at',
        ];
    }

    /**
     * Relations used by a target projection. Large/unused columns are omitted.
     *
     * @return array<string, mixed>
     */
    public function targetRelations(): array
    {
        return [
            'guru' => fn ($query) => $query->select([
                'id',
                'nama_lengkap',
                'no_ktp',
                'nip',
                'nuptk',
                'email',
                'jabatan',
                'status_kepegawaian',
                'eksternal_jabatan',
                'jenis_jabatan',
                'kategori_jabatan',
                'tugas_jabatan',
                'latar_jabatan',
                'gender',
                'tempat_lahir',
                'tgl_lahir',
                'agama',
                'pendidikan',
                'kabupaten',
                'satuan_pendidikan',
                'npsn_sekolah',
                'alamat_satuan',
                'alamat_rumah',
                'no_hp',
                'no_wa',
                'npwp',
                'no_rek',
                'jenis_bank',
            ]),
            'combination' => fn ($query) => $query->select([
                'id',
                'kode_kombinasi',
                'judul',
                'target_ketenagaan',
                'structure_snapshot',
            ]),
            'attempt' => fn ($query) => $query->select([
                'id',
                'assessment_assignment_target_id',
                'structure_snapshot',
            ]),
        ];
    }

    public function rememberAssignment(AssessmentAssignment $assignment): void
    {
        $assignmentId = (int) $assignment->getKey();

        if ($assignmentId > 0) {
            $this->assignmentCache[$assignmentId] = $assignment;
        }

        if ($this->hasFullAssignmentSchema($assignment)) {
            $this->assignmentSchemaLoaded[$assignmentId] = true;
        }
    }

    /**
     * Attach assignments once per process instead of eager-loading the same
     * assessment/forms schema for every target chunk.
     */
    public function hydrateAssignments(Collection $targets): void
    {
        $targets = $targets->values();
        $assignmentIds = $targets
            ->pluck('assessment_assignment_id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values();

        $targets->each(function (AssessmentAssignmentTarget $target): void {
            $assignmentId = (int) $target->assessment_assignment_id;
            $assignment = $target->relationLoaded('assignment')
                ? $target->getRelation('assignment')
                : null;

            if ($assignment instanceof AssessmentAssignment && $assignmentId > 0) {
                $this->assignmentCache[$assignmentId] = $assignment;
            }
        });

        $missingIds = $assignmentIds
            ->filter(fn (int $id): bool => ! array_key_exists($id, $this->assignmentCache))
            ->values();

        if ($missingIds->isNotEmpty()) {
            AssessmentAssignment::query()
                ->select([
                    'id',
                    'kode_penugasan',
                    'judul_penugasan',
                    'deskripsi',
                    'is_active',
                    'status_distribusi',
                    'target_ketenagaan',
                    'assessment_combination_id',
                    'tanggal_mulai',
                    'tanggal_selesai',
                ])
                ->whereIn('id', $missingIds->all())
                ->withoutPreview()
                ->with($this->assignmentBaseRelations())
                ->get()
                ->each(function (AssessmentAssignment $assignment): void {
                    $this->assignmentCache[(int) $assignment->getKey()] = $assignment;
                });
        }

        $this->loadRequiredAssignmentRelations($targets);

        $targets->each(function (AssessmentAssignmentTarget $target): void {
            $assignmentId = (int) $target->assessment_assignment_id;
            $target->setRelation('assignment', $this->assignmentCache[$assignmentId] ?? null);
        });
    }

    /**
     * Relations needed to identify the assignment combination. The large
     * assessment/form tree is loaded lazily only when no snapshot is usable.
     *
     * @return array<string, mixed>
     */
    public function assignmentBaseRelations(): array
    {
        return [
            'combination' => fn ($query) => $query->select([
                'id',
                'kode_kombinasi',
                'judul',
                'target_ketenagaan',
                'structure_snapshot',
            ]),
        ];
    }

    /**
     * Combination snapshots only need stage metadata from the assignment.
     * Loading the complete forms tree here would repeat a large SQL payload.
     *
     * @return array<string, mixed>
     */
    public function assignmentStageRelations(): array
    {
        return [
            'assessments' => fn ($query) => $query->select([
                'assessments.id',
                'assessments.instrument_type',
            ]),
        ];
    }

    /**
     * Assessment/form fields needed when the assignment has no combination
     * snapshot. Keeping this separate avoids loading the large tree for
     * combination-backed targets.
     *
     * @return array<string, mixed>
     */
    public function assignmentSchemaRelations(): array
    {
        return [
            'assessments' => fn ($query) => $query->select([
                'assessments.id',
                'assessments.kode_assessment',
                'assessments.judul',
                'assessments.deskripsi',
                'assessments.petunjuk',
                'assessments.instrument_type',
                'assessments.scoring_config',
                'assessments.is_active',
            ]),
            'assessments.forms' => fn ($query) => $query->select([
                'id',
                'assessment_id',
                'judul_form',
                'kode_form',
                'deskripsi',
                'kompetensi',
                'indikator_kode',
                'indikator_label',
                'is_scoreable',
                'scoring_config',
                'is_active',
                'urutan',
            ]),
            'assessments.forms.fields' => fn ($query) => $query->select([
                'id',
                'assessment_form_id',
                'label',
                'deskripsi',
                'nama_field',
                'tipe_field',
                'placeholder',
                'bantuan',
                'opsi_field',
                'autofill_source',
                'lookup_source',
                'dependency_config',
                'validasi',
                'scoring_config',
                'is_required',
                'is_active',
                'urutan',
            ]),
        ];
    }

    /**
     * Full assignment relation set for callers that explicitly need it.
     *
     * @return array<string, mixed>
     */
    public function assignmentRelations(): array
    {
        return $this->assignmentBaseRelations() + $this->assignmentSchemaRelations();
    }

    private function reusableSnapshot(
        AssessmentAssignmentTarget $target,
        ?AssessmentAssignment $assignment
    ): array {
        $combination = $target->combination ?: $assignment?->combination;
        if ($assignment && ! $combination?->structure_snapshot) {
            $this->loadAssignmentSchema($assignment);
        }

        $key = implode(':', [
            (int) ($assignment?->id ?? 0),
            (int) (
                $target->combination?->id
                ?? $target->assessment_combination_id
                ?? $assignment?->combination?->id
                ?? $assignment?->assessment_combination_id
                ?? 0
            ),
        ]);

        if (array_key_exists($key, $this->reusableSnapshots)) {
            return $this->randomizer->randomizeSnapshotForTarget(
                $this->reusableSnapshots[$key],
                (int) ($target->getKey() ?? 0)
            );
        }

        $snapshot = $this->randomizer->buildSnapshot($target, false);
        $this->normalizeAdvancedRules($snapshot);

        $this->reusableSnapshots[$key] = $snapshot;

        return $this->randomizer->randomizeSnapshotForTarget(
            $snapshot,
            (int) ($target->getKey() ?? 0)
        );
    }

    private function loadAssignmentSchema(AssessmentAssignment $assignment): void
    {
        $assignmentId = (int) $assignment->getKey();

        if (! $assignment->exists || ($this->assignmentSchemaLoaded[$assignmentId] ?? false)) {
            return;
        }

        if ($this->hasFullAssignmentSchema($assignment)) {
            $this->assignmentSchemaLoaded[$assignmentId] = true;

            return;
        }

        // A combination target may have loaded only stage metadata first.
        $assignment->unsetRelation('assessments');
        $assignment->load($this->assignmentSchemaRelations());
        $this->assignmentSchemaLoaded[$assignmentId] = true;
        $this->assignmentCache[$assignmentId] = $assignment;
    }

    /**
     * Load only the relation set required by each target type. This keeps
     * combination-backed batches on the small assessment metadata query.
     */
    private function loadRequiredAssignmentRelations(Collection $targets): void
    {
        $schemaIds = collect();
        $stageIds = collect();

        $targets->each(function (AssessmentAssignmentTarget $target) use (&$schemaIds, &$stageIds): void {
            $assignmentId = (int) $target->assessment_assignment_id;
            $assignment = $this->assignmentCache[$assignmentId] ?? null;

            if (! $assignment || $this->hasAttemptSnapshot($target)) {
                return;
            }

            $combination = $target->relationLoaded('combination')
                ? ($target->getRelation('combination') ?: $assignment->combination)
                : $assignment->combination;

            if ($this->hasStructureSnapshot($combination)) {
                if (! $assignment->relationLoaded('assessments')) {
                    $stageIds->push($assignmentId);
                }

                return;
            }

            if (! ($this->assignmentSchemaLoaded[$assignmentId] ?? false)) {
                $schemaIds->push($assignmentId);
            }
        });

        $schemaIds = $schemaIds->unique()->values();
        $stageIds = $stageIds
            ->diff($schemaIds)
            ->unique()
            ->values();

        $schemaAssignments = new EloquentCollection(
            $schemaIds
                ->map(fn (int $id) => $this->assignmentCache[$id] ?? null)
                ->filter()
                ->values()
                ->all()
        );

        if ($schemaAssignments->isNotEmpty()) {
            $schemaAssignments->each(function (AssessmentAssignment $assignment): void {
                $assignment->unsetRelation('assessments');
            });
            $schemaAssignments->load($this->assignmentSchemaRelations());

            $schemaAssignments->each(function (AssessmentAssignment $assignment): void {
                $assignmentId = (int) $assignment->getKey();
                $this->assignmentSchemaLoaded[$assignmentId] = true;
                $this->assignmentCache[$assignmentId] = $assignment;
            });
        }

        $stageAssignments = new EloquentCollection(
            $stageIds
                ->map(fn (int $id) => $this->assignmentCache[$id] ?? null)
                ->filter()
                ->filter(fn (AssessmentAssignment $assignment): bool => ! $assignment->relationLoaded('assessments'))
                ->values()
                ->all()
        );

        if ($stageAssignments->isNotEmpty()) {
            $stageAssignments->load($this->assignmentStageRelations());
        }
    }

    private function hasFullAssignmentSchema(AssessmentAssignment $assignment): bool
    {
        if (! $assignment->relationLoaded('assessments')) {
            return false;
        }

        return $assignment->assessments->every(
            fn ($assessment): bool => $assessment->relationLoaded('forms')
        );
    }

    private function hasAttemptSnapshot(AssessmentAssignmentTarget $target): bool
    {
        $snapshot = $target->attempt?->structure_snapshot;

        return is_array($snapshot) && ! empty($snapshot['assessments']);
    }

    private function hasStructureSnapshot(mixed $combination): bool
    {
        $snapshot = $combination?->structure_snapshot;

        return is_array($snapshot) && ! empty($snapshot['assessments']);
    }

    /**
     * Payload kept compatible with the assignment API participant schema.
     */
    public function participant(
        AssessmentAssignmentTarget $target,
        ?AssessmentAssignment $assignment = null
    ): array {
        $assignment ??= $target->assignment;
        $target->setRelation('assignment', $assignment);
        $attemptSnapshot = $target->attempt?->structure_snapshot;
        $hasAttemptSnapshot = is_array($attemptSnapshot) && ! empty($attemptSnapshot['assessments']);
        $snapshot = $hasAttemptSnapshot
            ? $attemptSnapshot
            : $this->reusableSnapshot($target, $assignment);
        if ($hasAttemptSnapshot) {
            $this->normalizeAdvancedRules($snapshot);
        }

        $combination = $target->combination ?: $assignment?->combination;

        return [
            'id' => $target->id,
            'status' => $target->status,
            'assigned_at' => $target->assigned_at?->toISOString(),
            'started_at' => $target->started_at?->toISOString(),
            'deadline_at' => $target->deadline_at?->toISOString(),
            'submitted_at' => $target->submitted_at?->toISOString(),
            'completion_mode' => $target->completion_mode,
            'timed_out_at' => $target->timed_out_at?->toISOString(),
            'session_id' => $target->assessment_assignment_session_id,
            'user' => $this->user($target->guru, $assignment),
            'combination' => $this->combination($combination),
            'forms' => $this->groupAssessmentsByInstrument($snapshot['assessments'] ?? []),
            'meta' => array_merge(
                is_array($snapshot['meta'] ?? null) ? $snapshot['meta'] : [],
                ['snapshot_source' => $hasAttemptSnapshot ? 'attempt' : ($combination ? 'combination' : 'assignment')]
            ),
        ];
    }

    public function document(
        AssessmentAssignmentTarget $target,
        ?AssessmentAssignment $assignment = null
    ): array {
        $assignment ??= $target->assignment;
        $participant = $this->participant($target, $assignment);
        $this->applyAutofillDefaults($participant['forms'], $target->guru);
        $syncedAt = now()->toIso8601String();

        return [
            '_id' => 'assessment-target:'.$target->id,
            'schema_version' => self::SCHEMA_VERSION,
            'assignment_target_id' => (int) $target->id,
            'assignment' => [
                'id' => $assignment?->id,
                'kode_penugasan' => $assignment?->kode_penugasan,
                'judul_penugasan' => $assignment?->judul_penugasan,
                'deskripsi' => $assignment?->deskripsi,
                'is_active' => (bool) ($assignment?->is_active ?? false),
                'status_distribusi' => $assignment?->status_distribusi,
                'ketenagaan' => $this->ketenagaan($assignment?->target_ketenagaan),
                'tanggal_mulai' => $assignment?->tanggal_mulai?->toDateString(),
                'tanggal_selesai' => $assignment?->tanggal_selesai?->toDateString(),
            ],
            'user' => $participant['user'],
            'target' => [
                'id' => (int) $target->id,
                'status' => $participant['status'],
                'session_id' => $participant['session_id'],
                'assigned_at' => $participant['assigned_at'],
                'started_at' => $participant['started_at'],
                'deadline_at' => $participant['deadline_at'],
                'submitted_at' => $participant['submitted_at'],
                'completion_mode' => $participant['completion_mode'],
                'timed_out_at' => $participant['timed_out_at'],
            ],
            'combination' => $participant['combination'],
            'forms' => $participant['forms'],
            'meta' => array_merge($participant['meta'], ['generated_at' => $syncedAt]),
            'sync' => [
                'is_active' => true,
                'source_updated_at' => $target->updated_at?->toISOString(),
                'synced_at' => $syncedAt,
            ],
        ];
    }

    public function tombstone(int $targetId): array
    {
        $now = now()->toIso8601String();

        return [
            '_id' => 'assessment-target:'.$targetId,
            'schema_version' => self::SCHEMA_VERSION,
            'assignment_target_id' => $targetId,
            'target' => [
                'id' => $targetId,
                'status' => 'dibatalkan',
            ],
            'forms' => [],
            'meta' => [
                'snapshot_source' => 'tombstone',
                'generated_at' => $now,
            ],
            'sync' => [
                'is_active' => false,
                'source_updated_at' => $now,
                'synced_at' => $now,
                'deleted_at' => $now,
            ],
        ];
    }

    private function ketenagaan(?string $value): array
    {
        $type = AssessmentKetenagaanType::tryFromMixed($value);

        return [
            'kode' => $value,
            'label' => $type?->label() ?? $value,
        ];
    }

    private function user(?Guru $guru, ?AssessmentAssignment $assignment): ?array
    {
        if (! $guru) {
            return null;
        }

        return [
            'id' => $guru->id,
            'nama' => $guru->nama_lengkap,
            'nik' => $guru->no_ktp,
            'nip' => $guru->nip,
            'nuptk' => $guru->nuptk,
            'email' => $guru->email,
            'role' => $guru->eksternal_jabatan ?: $this->ketenagaan($assignment?->target_ketenagaan)['label'],
            'jabatan' => $guru->jenis_jabatan ?: $guru->jabatan,
            'kabupaten' => $guru->kabupaten,
            'satuan_pendidikan' => $guru->satuan_pendidikan,
        ];
    }

    private function applyAutofillDefaults(array &$forms, ?Guru $guru): void
    {
        $resolver = $this->participantAutoFillResolver ?? app(ParticipantAutoFillResolver::class);

        foreach ($forms as &$instrumentGroup) {
            if (! is_array($instrumentGroup['assessments'] ?? null)) {
                continue;
            }

            foreach ($instrumentGroup['assessments'] as &$assessment) {
                if (! is_array($assessment['forms'] ?? null)) {
                    continue;
                }

                foreach ($assessment['forms'] as &$form) {
                    $shouldApplyAutofill = ($instrumentGroup['instrument_type'] ?? null)
                        === AssessmentInstrumentType::PORTOFOLIO->value
                        && str_contains(
                            strtoupper((string) ($form['kode_form'] ?? $form['judul_form'] ?? '')),
                            'IDENTITAS'
                        );

                    if (! is_array($form['fields'] ?? null)) {
                        continue;
                    }

                    foreach ($form['fields'] as &$field) {
                        if (! is_array($field)) {
                            continue;
                        }

                        $field['autofill_source'] = $resolver->normalizeSource(
                            $field['autofill_source'] ?? null,
                            $field['tipe_field'] ?? null
                        ) ?: $resolver->normalizeSource(
                            $resolver->inferSourceFromField(
                                $field['label'] ?? null,
                                $field['nama_field'] ?? null
                            ),
                            $field['tipe_field'] ?? null
                        );
                        $field['default_value'] = $shouldApplyAutofill && $guru && $field['autofill_source']
                            ? ($resolver->resolveForField($field, $guru)['value'] ?? null)
                            : null;
                    }
                    unset($field);
                }
                unset($form);
            }
            unset($assessment);
        }
        unset($instrumentGroup);
    }

    private function combination($combination): ?array
    {
        if (! $combination) {
            return null;
        }

        return [
            'id' => $combination->id,
            'kode_kombinasi' => $combination->kode_kombinasi,
            'judul' => $combination->judul,
            'target_ketenagaan' => $combination->target_ketenagaan,
        ];
    }

    private function groupAssessmentsByInstrument(array $assessments): array
    {
        return collect($assessments)
            ->filter(fn ($assessment) => is_array($assessment))
            ->groupBy(fn (array $assessment) => (string) ($assessment['instrument_type'] ?? 'lainnya'))
            ->sortBy(fn (Collection $group, string $instrumentType) => AssessmentInstrumentType::assignmentStageOrderFor($instrumentType))
            ->map(function (Collection $group, string $instrumentType) {
                $type = AssessmentInstrumentType::tryFromMixed($instrumentType);

                return [
                    'instrument_type' => $type?->value ?? $instrumentType,
                    'instrument_label' => $type?->label()
                        ?? (string) data_get($group->first(), 'instrument_label', $instrumentType),
                    'assessments' => $group->values()->all(),
                ];
            })
            ->values()
            ->all();
    }

    private function normalizeAdvancedRules(array &$snapshot): void
    {
        if (! is_array($snapshot['assessments'] ?? null)) {
            return;
        }

        foreach ($snapshot['assessments'] as &$assessment) {
            if (! is_array($assessment)) {
                continue;
            }

            $this->normalizeScoringConfig($assessment);

            if (! is_array($assessment['forms'] ?? null)) {
                continue;
            }

            foreach ($assessment['forms'] as &$form) {
                if (! is_array($form)) {
                    continue;
                }

                $this->normalizeScoringConfig($form);

                if (! is_array($form['fields'] ?? null)) {
                    continue;
                }

                foreach ($form['fields'] as &$field) {
                    if (is_array($field)) {
                        $this->normalizeScoringConfig($field);
                    }
                }
                unset($field);
            }
            unset($form);
        }
        unset($assessment);
    }

    private function normalizeScoringConfig(array &$item): void
    {
        if (! is_array($item['scoring_config'] ?? null)
            || ! array_key_exists('advanced_rules_text', $item['scoring_config'])) {
            return;
        }

        $item['scoring_config']['advanced_rules_text'] = $this->scoringConfigNormalizer->parseAdvancedRules(
            $item['scoring_config']['advanced_rules_text']
        );
    }
}
