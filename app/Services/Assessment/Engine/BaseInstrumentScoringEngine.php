<?php

namespace App\Services\Assessment\Engine;

use App\Models\AssessmentAttemptAnswer;
use App\Support\Assessment\ScoringConfigNormalizer;
use Illuminate\Support\Collection;

abstract class BaseInstrumentScoringEngine
{
    public function __construct(
        protected readonly FieldAutoScoringEngine $fieldAutoScoringEngine,
        protected readonly ScoringConfigNormalizer $configNormalizer
    ) {}

    public function buildFormSummary(array $assessment, array $form, Collection $answersByFieldId): array
    {
        $formConfig = $this->configNormalizer->normalizeForm($form, $assessment);
        $formFieldIds = collect($form['fields'] ?? [])
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
        $formAnswers = $answersByFieldId->only($formFieldIds);
        $fieldItems = [];
        $answeredScorableCount = 0;
        $totalScoreableItems = 0;
        $pendingManualItems = 0;

        foreach (array_values($form['fields'] ?? []) as $fieldIndex => $field) {
            $field = $this->mergeFieldOverrides($assessment, $form, $field, $fieldIndex);
            $answer = $answersByFieldId->get($field['id'] ?? null);
            $itemSummary = $this->buildFieldSummary($assessment, $form, $field, $answer);
            $fieldItems[] = $itemSummary;
            $totalScoreableItems++;

            if ($itemSummary['answered']) {
                $answeredScorableCount++;
            }

            $expectsSystemScore = (bool) data_get($itemSummary, 'scoring_config.enabled', false);

            if ($itemSummary['answered'] && ($itemSummary['manual_pending'] || ($expectsSystemScore && $itemSummary['score'] === null))) {
                $pendingManualItems++;
            }
        }

        $syntheticItems = $this->buildSyntheticItems($assessment, $form, $formAnswers, $fieldItems);
        $allItems = array_merge($fieldItems, $syntheticItems);
        $pendingSyntheticItems = (int) collect($syntheticItems)
            ->filter(function (array $item) {
                $expectsSystemScore = (bool) data_get($item, 'scoring_config.enabled', false);

                return ($item['answered'] ?? false) && (($item['manual_pending'] ?? false) || ($expectsSystemScore && $item['score'] === null));
            })
            ->count();
        $weightedScores = collect($allItems)
            ->filter(fn (array $item) => $item['score'] !== null && ! $item['manual_pending'])
            ->map(fn (array $item) => [
                'score' => (float) $item['score'],
                'weight' => max((float) ($item['weight'] ?? 1), 0.01),
            ])
            ->values();
        $displayScores = collect($allItems)
            ->filter(fn (array $item) => $item['score'] !== null)
            ->map(fn (array $item) => [
                'score' => (float) $item['score'],
                'weight' => max((float) ($item['weight'] ?? 1), 0.01),
            ])
            ->values();

        return [
            'score' => $this->weightedAverage($weightedScores),
            'display_score' => $this->weightedAverage($displayScores),
            'items' => $allItems,
            'answered_items' => $answeredScorableCount,
            'total_items' => $totalScoreableItems + count($syntheticItems),
            'scored_items' => $weightedScores->count(),
            'pending_manual_items' => $pendingManualItems + $pendingSyntheticItems,
            'is_complete' => ($answeredScorableCount + count($syntheticItems)) > 0
                && ($pendingManualItems + $pendingSyntheticItems) === 0,
            'form_config' => $formConfig,
        ];
    }

    protected function buildFieldSummary(
        array $assessment,
        array $form,
        array $field,
        ?AssessmentAttemptAnswer $answer
    ): array {
        $fieldConfig = $this->configNormalizer->normalizeField($field, $form, $assessment);
        $answered = $this->answerHasContent($answer);
        $autoResult = $answered
            ? $this->resolveAutoScoreResult($assessment, $form, $field, $answer)
            : null;
        $autoScore = is_numeric($answer?->auto_score) ? (float) $answer->auto_score : ($autoResult['score'] ?? null);
        $forcedZeroForUnanswered = ! $answered
            && is_numeric($answer?->auto_score)
            && (bool) data_get($answer?->auto_score_metadata ?? [], 'forced_zero_for_unanswered', false);
        $manualPending = false;
        $score = null;
        $scoreSource = null;
        $confidence = $autoResult['confidence'] ?? null;

        if (($answered || $forcedZeroForUnanswered) && $autoScore !== null) {
            $score = $autoScore;
            $scoreSource = $forcedZeroForUnanswered
                ? (string) data_get($answer?->auto_score_metadata ?? [], 'source', 'deadline_auto_zero')
                : ($autoResult['source'] ?? 'auto_score');
        } elseif ($answered && ($fieldConfig['enabled'] ?? false)) {
            $scoreSource = 'auto_unavailable';
        }

        return [
            'field_id' => $field['id'] ?? null,
            'field_label' => $field['label'] ?? 'Pertanyaan',
            'field_type' => $field['tipe_field'] ?? 'text',
            'rubric_code' => $fieldConfig['rubric_code'] ?? null,
            'weight' => (float) ($fieldConfig['weight'] ?? 1),
            'answered' => $answered,
            'score' => $score !== null ? round((float) $score, 2) : null,
            'formatted_score' => $score !== null ? number_format((float) $score, 2) : null,
            'level' => $score !== null ? \App\Enum\LevelKompetensi::fromScore((float) $score)?->shortLabel() : null,
            'score_source' => $scoreSource,
            'manual_pending' => $manualPending,
            'forced_zero_for_unanswered' => $forcedZeroForUnanswered,
            'assessor_score' => is_numeric($answer?->assessor_score) ? (float) $answer->assessor_score : null,
            'auto_score' => $autoScore !== null ? round((float) $autoScore, 2) : null,
            'auto_confidence' => $confidence,
            'auto_reason' => $autoResult['reason'] ?? null,
            'auto_metadata' => $autoResult['metadata'] ?? null,
            'scoring_config' => $fieldConfig,
        ];
    }

    protected function resolveAutoScoreResult(
        array $assessment,
        array $form,
        array $field,
        AssessmentAttemptAnswer $answer
    ): array {
        if (is_numeric($answer->auto_score)) {
            return [
                'score' => (float) $answer->auto_score,
                'confidence' => (float) data_get($answer->auto_score_metadata ?? [], 'confidence', 0),
                'reason' => $answer->auto_score_reason,
                'metadata' => $answer->auto_score_metadata ?? [],
                'source' => (string) data_get($answer->auto_score_metadata ?? [], 'source', 'auto_score'),
                'requires_manual_review' => false,
            ];
        }

        return $this->fieldAutoScoringEngine->score($field, $answer, $form, $assessment);
    }

    protected function buildSyntheticItems(
        array $assessment,
        array $form,
        Collection $answersByFieldId,
        array $fieldItems
    ): array {
        return [];
    }

    protected function mergeFieldOverrides(array $assessment, array $form, array $field, int $fieldIndex): array
    {
        $overrides = $this->fieldOverrides($assessment, $form, $field, $fieldIndex);
        $scoringConfig = array_merge(
            is_array($field['scoring_config'] ?? null) ? $field['scoring_config'] : [],
            is_array($overrides['scoring_config'] ?? null) ? $overrides['scoring_config'] : []
        );

        return array_merge($field, $overrides, [
            'scoring_config' => $scoringConfig,
        ]);
    }

    protected function fieldOverrides(array $assessment, array $form, array $field, int $fieldIndex): array
    {
        return [];
    }

    protected function weightedAverage(Collection $items): ?float
    {
        if ($items->isEmpty()) {
            return null;
        }

        $weightTotal = (float) $items->sum('weight');

        if ($weightTotal <= 0) {
            return null;
        }

        return round(
            (float) $items->sum(fn (array $item) => ((float) $item['score']) * (((float) $item['weight']) / $weightTotal)),
            2
        );
    }

    protected function answerHasContent(?AssessmentAttemptAnswer $answer): bool
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
