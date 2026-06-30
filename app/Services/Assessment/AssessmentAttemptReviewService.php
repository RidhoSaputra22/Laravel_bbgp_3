<?php

namespace App\Services\Assessment;

use App\Enum\AssessmentInstrumentType;
use App\Models\AssessmentAttempt;
use App\Models\AssessmentAttemptAnswer;
use App\Support\Assessment\AssessmentStructureMetadataResolver;
use Illuminate\Support\Facades\DB;

class AssessmentAttemptReviewService
{
    public function __construct(
        private readonly AssessmentStructureMetadataResolver $metadataResolver,
        private readonly AssessmentAttemptService $attemptService
    ) {}

    public function buildManualReviewFields(AssessmentAttempt $attempt): array
    {
        $snapshot = $attempt->structure_snapshot ?? [];
        $answerMap = $attempt->answers->keyBy('assessment_form_field_id');
        $fields = [];

        foreach ($snapshot['assessments'] ?? [] as $assessmentData) {
            $assessmentMeta = $this->metadataResolver->decorateAssessment($assessmentData);
            $instrument = AssessmentInstrumentType::tryFromMixed($assessmentMeta['instrument_type'] ?? null);

            if (! $instrument) {
                continue;
            }

            foreach ($assessmentMeta['forms'] ?? [] as $formData) {
                $formMeta = $this->metadataResolver->decorateForm($formData, $assessmentMeta);

                if (! ($formMeta['is_scoreable'] ?? false)) {
                    continue;
                }

                foreach ($formMeta['fields'] ?? [] as $fieldData) {
                    $answer = $answerMap->get($fieldData['id']);

                    if (! $this->answerHasContent($answer)) {
                        continue;
                    }

                    $needsReview = ! is_numeric($answer?->auto_score)
                        || (bool) data_get($answer?->auto_score_metadata ?? [], 'requires_manual_review', false);

                    if (! $needsReview) {
                        continue;
                    }

                    $fields[(int) $fieldData['id']] = [
                        'assessment' => $assessmentMeta,
                        'form' => $formMeta,
                        'field' => $fieldData,
                    ];
                }
            }
        }

        return $fields;
    }

    public function scoreAttempt(
        AssessmentAttempt $attempt,
        array $scores,
        array $notes,
        ?int $assessorUserId = null
    ): AssessmentAttempt {
        $manualFields = $this->buildManualReviewFields($attempt->loadMissing('answers'));
        $answers = $attempt->answers
            ->whereIn('assessment_form_field_id', array_keys($manualFields))
            ->keyBy('assessment_form_field_id');
        $scoredAt = now();

        DB::transaction(function () use ($answers, $scores, $notes, $assessorUserId, $scoredAt) {
            /** @var AssessmentAttemptAnswer $answer */
            foreach ($answers as $fieldId => $answer) {
                $score = $scores[$fieldId] ?? null;
                $note = trim((string) ($notes[$fieldId] ?? ''));

                $answer->forceFill([
                    'assessor_score' => is_numeric($score) ? (int) $score : null,
                    'assessor_notes' => $note !== '' ? $note : null,
                    'assessor_user_id' => is_numeric($score) ? $assessorUserId : null,
                    'assessor_scored_at' => is_numeric($score) ? $scoredAt : null,
                ])->save();
            }
        });

        $attempt = $attempt->fresh([
            'answers',
            'target.assignment.assessments.forms.fields',
            'target.assignment.combination',
            'target.combination',
            'target.session',
            'target.guru',
        ]);

        $this->attemptService->refreshScoringSummary($attempt);

        return $attempt->fresh([
            'answers',
            'target.assignment.assessments.forms.fields',
            'target.assignment.combination',
            'target.combination',
            'target.session',
            'target.guru',
        ]);
    }

    private function answerHasContent(?AssessmentAttemptAnswer $answer): bool
    {
        if (! $answer) {
            return false;
        }

        if (filled($answer->answer_text) || filled($answer->answer_file_path)) {
            return true;
        }

        $payload = $answer->answer_payload ?? [];

        if (filled($payload['value'] ?? null)) {
            return true;
        }

        if (collect($payload['values'] ?? [])->filter(fn ($value) => filled($value))->isNotEmpty()) {
            return true;
        }

        return collect($payload['rows'] ?? [])
            ->filter(fn ($row) => is_array($row) && collect($row)->filter(fn ($value) => filled($value))->isNotEmpty())
            ->isNotEmpty();
    }
}
