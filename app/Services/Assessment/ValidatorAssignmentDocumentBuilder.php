<?php

namespace App\Services\Assessment;

use App\Models\ValidatorAssignment;

class ValidatorAssignmentDocumentBuilder
{
    public const SCHEMA_VERSION = 'validator_assignment-v1';

    /**
     * @return array<string, mixed>
     */
    public function relations(): array
    {
        return [
            'validatorForm.sections.fields',
            'responses',
        ];
    }

    public function document(ValidatorAssignment $assignment): array
    {
        $assignment->loadMissing($this->relations());

        $sourceAssignments = collect($assignment->resolved_assignment_snapshots)
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
