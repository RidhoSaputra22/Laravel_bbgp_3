<?php

namespace App\Services\Assessment;

use App\Models\Assessment;
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
    public function create(array $data, ?int $assignedBy): ValidatorAssignment
    {
        $validator = User::with('guru')->findOrFail((int) $data['validator_user_id']);

        if (! ValidatorAccess::isEligibleUser($validator)) {
            throw ValidationException::withMessages([
                'validator_user_id' => 'User yang dipilih harus memiliki role Stakeholder dan jabatan Validator.',
            ]);
        }

        $form = ValidatorForm::with('sections.fields')->findOrFail((int) $data['validator_form_id']);
        $assessment = Assessment::with('forms.fields')->findOrFail((int) $data['assessment_id']);

        if ($form->status !== 'published' || ! $form->is_active) {
            throw ValidationException::withMessages([
                'validator_form_id' => 'Form validator harus berstatus dipublikasikan dan aktif.',
            ]);
        }

        if (! $assessment->is_active) {
            throw ValidationException::withMessages([
                'assessment_id' => 'Assessment yang dipilih sudah tidak aktif.',
            ]);
        }

        $alreadyAssigned = ValidatorAssignment::query()
            ->where('validator_form_id', $form->id)
            ->where('assessment_id', $assessment->id)
            ->where('validator_user_id', $validator->id)
            ->whereIn('status', ['assigned', 'in_progress'])
            ->exists();

        if ($alreadyAssigned) {
            throw ValidationException::withMessages([
                'validator_user_id' => 'Validator ini masih memiliki penugasan aktif untuk assessment dan form yang sama.',
            ]);
        }

        return DB::transaction(function () use ($data, $assignedBy, $assessment, $form, $validator) {
            return ValidatorAssignment::create([
                'code' => $this->generateCode(),
                'title' => $data['title'],
                'validator_form_id' => $form->id,
                'assessment_id' => $assessment->id,
                'validator_user_id' => (int) $data['validator_user_id'],
                'assigned_by' => $assignedBy,
                'notes' => $data['notes'] ?? null,
                'assessment_snapshot' => $this->buildAssessmentSnapshot($assessment),
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
}
