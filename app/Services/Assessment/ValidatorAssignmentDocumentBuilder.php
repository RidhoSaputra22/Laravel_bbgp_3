<?php

namespace App\Services\Assessment;

use App\Models\AssessmentCombination;
use App\Models\ValidatorAssignment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ValidatorAssignmentDocumentBuilder
{
    public const SCHEMA_VERSION = 'validator_assignment-v1';

    /**
     * @return array<string, mixed>
     */
    public function relations(): array
    {
        $relations = [
            'validatorForm.sections.fields',
            'responses',
        ];

        if (Schema::hasTable('validator_assignment_assessment_assignments')) {
            $relations[] = 'assessmentAssignments.combination';
        }

        return $relations;
    }

    public function document(ValidatorAssignment $assignment): array
    {
        $assignment->loadMissing($this->relations());

        $sourceAssignments = $this->resolveSourceSnapshots($assignment)
            ->map(fn (mixed $source) => is_array($source) ? $this->sourceAssignment($source) : null)
            ->filter()
            ->values();
        $formSections = $assignment->validatorForm?->sections ?? collect();
        $formFields = $formSections->flatMap->fields->values();
        $responses = $assignment->responses
            ->map(fn ($response) => [
                'validator_form_field_id' => (int) $response->validator_form_field_id,
                'answer_text' => $response->answer_text,
                'answer_payload' => $response->answer_payload,
                'score' => $response->score,
                'created_at' => $response->created_at?->toISOString(),
                'updated_at' => $response->updated_at?->toISOString(),
            ])
            ->values()
            ->all();
        $syncedAt = now()->toIso8601String();

        return [
            '_id' => 'validator-assignment:'.$assignment->id,
            'schema_version' => self::SCHEMA_VERSION,
            'validator_assignment_id' => (int) $assignment->id,
            'assignment' => [
                'id' => (int) $assignment->id,
                'code' => $assignment->code,
                'title' => $assignment->title,
                'notes' => $assignment->notes,
                'validator_form_id' => $assignment->validator_form_id,
                'source_mode' => $assignment->source_mode,
                'assessment_id' => $assignment->assessment_id,
                'status' => $assignment->status,
                'start_date' => $assignment->start_date?->toDateString(),
                'due_date' => $assignment->due_date?->toDateString(),
                'started_at' => $assignment->started_at?->toISOString(),
                'submitted_at' => $assignment->submitted_at?->toISOString(),
            ],
            'validator' => $this->validator($assignment->validator_snapshot),
            'validator_form' => $this->validatorForm($assignment),
            'assessment_assignments' => $sourceAssignments->all(),
            'responses' => $responses,
            'result' => [
                'score_total' => $assignment->score_total,
                'score_max' => $assignment->score_max,
                'score_percentage' => $assignment->score_percentage,
                'recommendation' => $assignment->recommendation,
                'final_notes' => $assignment->final_notes,
            ],
            'meta' => [
                'snapshot_source' => 'validator_assignment',
                'source_mode' => $assignment->source_mode,
                'assessment_assignment_count' => $sourceAssignments->count(),
                'assessment_count' => $sourceAssignments
                    ->sum(fn (array $source) => count($source['assessments'] ?? [])),
                'total_questions' => $formFields->count(),
                'required_questions' => $formFields->where('is_required', true)->count(),
                'answered_questions' => collect($responses)
                    ->filter(fn (array $response) => filled($response['answer_text'] ?? null))
                    ->count(),
                'scored_questions' => $formFields
                    ->where('is_active', true)
                    ->where('is_scored', true)
                    ->count(),
                'scoring_scope' => 'validator_form_only',
                'generated_at' => $syncedAt,
            ],
            'sync' => [
                'is_active' => $assignment->status !== 'cancelled',
                'source_updated_at' => $assignment->updated_at?->toISOString(),
                'synced_at' => $syncedAt,
            ],
        ];
    }

    public function tombstone(int $assignmentId): array
    {
        $now = now()->toIso8601String();

        return [
            '_id' => 'validator-assignment:'.$assignmentId,
            'schema_version' => self::SCHEMA_VERSION,
            'validator_assignment_id' => $assignmentId,
            'assessment_assignments' => [],
            'responses' => [],
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

    private function sourceAssignment(array $source): array
    {
        return [
            'id' => $source['id'] ?? null,
            'code' => $source['code'] ?? null,
            'title' => $source['title'] ?? null,
            'is_active' => (bool) ($source['is_active'] ?? true),
            'status_distribusi' => $source['status_distribusi'] ?? null,
            'target_ketenagaan' => [
                'kode' => $source['target_ketenagaan'] ?? null,
                'label' => $source['target_ketenagaan_label'] ?? ($source['target_ketenagaan'] ?? null),
            ],
            'description' => $source['description'] ?? null,
            'start_date' => $source['start_date'] ?? null,
            'end_date' => $source['end_date'] ?? null,
            'total_target' => $source['total_target'] ?? null,
            'captured_at' => $source['captured_at'] ?? null,
            'assessments' => collect($source['assessments'] ?? [])
                ->map(fn (mixed $assessment) => is_array($assessment)
                    ? $this->sourceAssessment($assessment)
                    : null)
                ->filter()
                ->values()
                ->all(),
        ];
    }

    private function resolveSourceSnapshots(ValidatorAssignment $assignment): \Illuminate\Support\Collection
    {
        $snapshots = collect($assignment->resolved_assignment_snapshots);

        if ($assignment->source_mode === ValidatorAssignment::SOURCE_MODE_ALL_FORMS) {
            return $snapshots;
        }

        if (! $assignment->relationLoaded('assessmentAssignments')
            || $assignment->assessmentAssignments->isEmpty()) {
            return $snapshots;
        }

        $selectedCombinationIds = $assignment->assessmentAssignments
            ->values()
            ->mapWithKeys(function ($sourceAssignment, int $index) use ($snapshots) {
                $combinationId = (int) data_get($snapshots->get($index, []), 'combination_id', 0);

                return $combinationId > 0
                    ? [(int) $sourceAssignment->id => $combinationId]
                    : [];
            })
            ->all();
        $targetCombinationSnapshots = $this->loadTargetCombinationSnapshots(
            $assignment->assessmentAssignments,
            $selectedCombinationIds
        );

        return $assignment->assessmentAssignments
            ->values()
            ->map(function ($sourceAssignment, int $index) use (
                $snapshots,
                $targetCombinationSnapshots,
                $selectedCombinationIds
            ) {
                $snapshot = $snapshots->get($index, []);
                $selectedCombinationId = (int) ($selectedCombinationIds[(int) $sourceAssignment->id] ?? 0);
                $combinationSnapshots = $targetCombinationSnapshots[(int) $sourceAssignment->id] ?? [];
                $combination = $sourceAssignment->relationLoaded('combination')
                    ? $sourceAssignment->getRelation('combination')
                    : null;

                if ($combination && $combination->is_active !== false
                    && is_array($combination->structure_snapshot)
                    && ($selectedCombinationId === 0 || $selectedCombinationId === (int) $combination->id)) {
                    array_unshift($combinationSnapshots, $combination->structure_snapshot);
                }

                $combinationSnapshots = collect($combinationSnapshots)
                    ->filter(fn (mixed $combinationSnapshot) => is_array($combinationSnapshot))
                    ->unique(fn (array $combinationSnapshot) => (string) data_get(
                        $combinationSnapshot,
                        'combination.id',
                        serialize($combinationSnapshot)
                    ))
                    ->values()
                    ->all();

                if ($combinationSnapshots === []) {
                    return $snapshot;
                }

                return array_merge($snapshot, [
                    'assessments' => $this->combinationAssessments($combinationSnapshots),
                ]);
            });
    }

    private function loadTargetCombinationSnapshots(
        Collection $sourceAssignments,
        array $selectedCombinationIds = []
    ): array
    {
        if (! Schema::hasTable('assessment_assignment_targets') || ! Schema::hasTable('assessment_combinations')) {
            return [];
        }

        $targetRows = DB::table('assessment_assignment_targets')
            ->whereIn('assessment_assignment_id', $sourceAssignments->pluck('id')->all())
            ->whereNotNull('assessment_combination_id')
            ->distinct()
            ->get(['assessment_assignment_id', 'assessment_combination_id']);
        $combinationIds = $targetRows
            ->pluck('assessment_combination_id')
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->merge(array_values($selectedCombinationIds))
            ->unique();

        if ($combinationIds->isEmpty()) {
            return [];
        }

        $snapshots = AssessmentCombination::query()
            ->whereIn('id', $combinationIds)
            ->where('is_active', true)
            ->orderByDesc('id')
            ->get(['id', 'structure_snapshot'])
            ->mapWithKeys(fn (AssessmentCombination $combination) => [
                (int) $combination->id => $combination->structure_snapshot,
            ]);

        return $targetRows
            ->groupBy('assessment_assignment_id')
            ->map(function (Collection $rows) use ($snapshots, $selectedCombinationIds) {
                $selectedCombinationId = (int) ($selectedCombinationIds[(int) $rows->first()->assessment_assignment_id] ?? 0);

                if ($selectedCombinationId > 0) {
                    $snapshot = $snapshots->get($selectedCombinationId);

                    return is_array($snapshot) && ! empty($snapshot['assessments'])
                        ? [$snapshot]
                        : [];
                }

                foreach ($rows->sortByDesc('assessment_combination_id') as $row) {
                    $snapshot = $snapshots->get((int) $row->assessment_combination_id);

                    if (is_array($snapshot) && ! empty($snapshot['assessments'])) {
                        return [$snapshot];
                    }
                }

                return [];
            })
            ->all();
    }

    private function combinationAssessments(array $snapshots): array
    {
        return collect($snapshots)
            ->flatMap(fn (array $snapshot) => $snapshot['assessments'] ?? [])
            ->filter(fn (mixed $assessment) => is_array($assessment) && isset($assessment['id']))
            ->groupBy('id')
            ->map(function (Collection $assessmentGroup) {
                $assessment = $assessmentGroup->first();
                $assessment['code'] = $assessment['code'] ?? $assessment['kode_assessment'] ?? null;
                $assessment['title'] = $assessment['title'] ?? $assessment['judul'] ?? null;
                $assessment['description'] = $assessment['description'] ?? $assessment['deskripsi'] ?? null;
                $assessment['instructions'] = $assessment['instructions'] ?? $assessment['petunjuk'] ?? null;
                $assessment['captured_at'] = $assessment['captured_at'] ?? null;
                $assessment['forms'] = $assessmentGroup
                    ->flatMap(fn (array $item) => $item['forms'] ?? [])
                    ->filter(fn (mixed $form) => is_array($form) && isset($form['id']))
                    ->groupBy('id')
                    ->map(function (Collection $formGroup) {
                        $form = $formGroup->first();
                        $form['code'] = $form['code'] ?? $form['kode_form'] ?? null;
                        $form['title'] = $form['title'] ?? $form['judul_form'] ?? null;
                        $form['description'] = $form['description'] ?? $form['deskripsi'] ?? null;
                        $form['fields'] = $formGroup
                            ->flatMap(fn (array $item) => $item['fields'] ?? [])
                            ->filter(fn (mixed $field) => is_array($field) && isset($field['id']))
                            ->unique('id')
                            ->map(fn (array $field) => [
                                'id' => $field['id'],
                                'label' => $field['label'] ?? null,
                                'description' => $field['description'] ?? $field['deskripsi'] ?? null,
                                'type' => $field['type'] ?? $field['tipe_field'] ?? null,
                                'options' => $field['options'] ?? $field['opsi_field'] ?? null,
                                'required' => (bool) ($field['required'] ?? $field['is_required'] ?? false),
                            ])
                            ->values()
                            ->all();

                        return $form;
                    })
                    ->values()
                    ->all();

                return $assessment;
            })
            ->values()
            ->all();
    }

    private function sourceAssessment(array $assessment): array
    {
        return [
            'id' => $assessment['id'] ?? null,
            'code' => $assessment['code'] ?? null,
            'title' => $assessment['title'] ?? null,
            'description' => $assessment['description'] ?? null,
            'instructions' => $assessment['instructions'] ?? null,
            'instrument_type' => $assessment['instrument_type'] ?? null,
            'target_ketenagaan' => $assessment['target_ketenagaan'] ?? null,
            'status' => $assessment['status'] ?? null,
            'captured_at' => $assessment['captured_at'] ?? null,
            'forms' => collect($assessment['forms'] ?? [])
                ->map(fn (mixed $form) => is_array($form) ? $this->sourceForm($form) : null)
                ->filter()
                ->values()
                ->all(),
        ];
    }

    private function sourceForm(array $form): array
    {
        return [
            'id' => $form['id'] ?? null,
            'code' => $form['code'] ?? null,
            'title' => $form['title'] ?? null,
            'description' => $form['description'] ?? null,
            'fields' => collect($form['fields'] ?? [])
                ->map(fn (mixed $field) => is_array($field) ? [
                    'id' => $field['id'] ?? null,
                    'label' => $field['label'] ?? null,
                    'description' => $field['description'] ?? null,
                    'type' => $field['type'] ?? null,
                    'options' => $field['options'] ?? null,
                    'required' => (bool) ($field['required'] ?? false),
                ] : null)
                ->filter()
                ->values()
                ->all(),
        ];
    }

    private function validatorForm(ValidatorAssignment $assignment): ?array
    {
        $form = $assignment->validatorForm;

        if (! $form) {
            return null;
        }

        return [
            'id' => (int) $form->id,
            'code' => $form->code,
            'title' => $form->title,
            'description' => $form->description,
            'instructions' => $form->instructions,
            'status' => $form->status,
            'is_active' => (bool) $form->is_active,
            'sections' => $form->sections
                ->map(fn ($section) => [
                    'id' => (int) $section->id,
                    'title' => $section->title,
                    'description' => $section->description,
                    'sort_order' => (int) $section->sort_order,
                    'fields' => $section->fields
                        ->map(fn ($field) => [
                            'id' => (int) $field->id,
                            'label' => $field->label,
                            'description' => $field->description,
                            'field_type' => $field->field_type,
                            'options' => $field->resolvedOptions(),
                            'is_required' => (bool) $field->is_required,
                            'is_scored' => (bool) $field->is_scored,
                            'max_score' => $field->max_score,
                            'sort_order' => (int) $field->sort_order,
                            'is_active' => (bool) $field->is_active,
                        ])
                        ->values()
                        ->all(),
                ])
                ->values()
                ->all(),
        ];
    }

    private function validator(mixed $snapshot): array
    {
        $snapshot = is_array($snapshot) ? $snapshot : [];

        return [
            'user_id' => $snapshot['user_id'] ?? null,
            'guru_id' => $snapshot['guru_id'] ?? null,
            'name' => $snapshot['name'] ?? null,
            'email' => $snapshot['email'] ?? null,
            'no_ktp' => $snapshot['no_ktp'] ?? null,
            'role' => $snapshot['role'] ?? null,
            'eksternal_jabatan' => $snapshot['eksternal_jabatan'] ?? null,
            'jenis_jabatan' => $snapshot['jenis_jabatan'] ?? null,
        ];
    }
}
