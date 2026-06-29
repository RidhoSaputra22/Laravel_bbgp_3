<?php

namespace App\Services\Assessment;

use App\Models\AssessmentAttempt;
use App\Models\AssessmentAttemptAnswer;
use App\Services\Assessment\Engine\FieldAutoScoringEngine;

class AssessmentAutoScoringService
{
    public function __construct(
        private readonly FieldAutoScoringEngine $fieldAutoScoringEngine
    ) {}

    public function scoreAttempt(AssessmentAttempt $attempt): void
    {
        $snapshot = $attempt->structure_snapshot ?? [];
        $contexts = $this->flattenFieldContexts($snapshot);
        $answers = $attempt->relationLoaded('answers')
            ? $attempt->answers
            : $attempt->answers()->get();

        /** @var AssessmentAttemptAnswer $answer */
        foreach ($answers as $answer) {
            $fieldId = (int) $answer->assessment_form_field_id;
            $context = $contexts[$fieldId] ?? null;

            if (! $context) {
                $answer->forceFill([
                    'auto_score' => null,
                    'auto_score_reason' => null,
                    'auto_score_metadata' => null,
                    'auto_scored_at' => null,
                ])->save();

                continue;
            }

            $result = $this->fieldAutoScoringEngine->score(
                $context['field'],
                $answer,
                $context['form'],
                $context['assessment']
            );

            $answer->forceFill([
                'auto_score' => is_numeric($result['score'] ?? null) ? round((float) $result['score'], 2) : null,
                'auto_score_reason' => $result['reason'] ?? null,
                'auto_score_metadata' => array_merge((array) ($result['metadata'] ?? []), [
                    'confidence' => $result['confidence'] ?? null,
                    'source' => $result['source'] ?? 'auto_score',
                    'requires_manual_review' => (bool) ($result['requires_manual_review'] ?? false),
                ]),
                'auto_scored_at' => is_numeric($result['score'] ?? null) ? now() : null,
            ])->save();
        }

        $attempt->setRelation('answers', $attempt->answers()->get());
    }

    /**
     * @return array<int, array<string, array<string, mixed>>>
     */
    private function flattenFieldContexts(array $snapshot): array
    {
        $contexts = [];

        foreach ($snapshot['assessments'] ?? [] as $assessment) {
            foreach ($assessment['forms'] ?? [] as $form) {
                foreach ($form['fields'] ?? [] as $field) {
                    $fieldId = (int) ($field['id'] ?? 0);

                    if ($fieldId <= 0) {
                        continue;
                    }

                    $contexts[$fieldId] = [
                        'assessment' => $assessment,
                        'form' => $form,
                        'field' => $field,
                    ];
                }
            }
        }

        return $contexts;
    }
}
