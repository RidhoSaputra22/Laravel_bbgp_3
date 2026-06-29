<?php

namespace App\Services\Assessment\Engine;

use App\Models\AssessmentAttemptAnswer;
use App\Support\Assessment\ChoiceOptionNormalizer;
use App\Support\Assessment\FuzzyMembership;
use App\Support\Assessment\ScoringConfigNormalizer;
use App\Support\Assessment\TextSimilarityAnalyzer;

class FieldAutoScoringEngine
{
    public function __construct(
        private readonly ScoringConfigNormalizer $configNormalizer,
        private readonly TextSimilarityAnalyzer $textSimilarityAnalyzer
    ) {}

    public function score(
        array $field,
        ?AssessmentAttemptAnswer $answer,
        ?array $form = null,
        ?array $assessment = null
    ): array {
        $config = $this->configNormalizer->normalizeField($field, $form, $assessment);

        if (! $this->answerHasContent($answer)) {
            return $this->emptyResult($config, 'Field belum memiliki jawaban untuk dihitung.');
        }

        if (! ($config['enabled'] ?? false)) {
            return $this->emptyResult($config, 'Auto scoring dimatikan untuk field ini.', true, 'manual_disabled');
        }

        return match ($config['method']) {
            'choice_option_score', 'choice_option_average', 'choice_option_sum', 'choice_option_max' => $this->scoreChoice($field, $answer, $config),
            'numeric_threshold', 'numeric_range' => $this->scoreNumeric($answer, $config),
            'semantic_similarity', 'keyword_coverage' => $this->scoreSemanticText($field, $answer, $config),
            'repeater_completeness' => $this->scoreRepeater($field, $answer, $config),
            default => $this->scorePresence($answer, $config),
        };
    }

    private function scorePresence(?AssessmentAttemptAnswer $answer, array $config): array
    {
        if (! $this->answerHasContent($answer)) {
            return $this->emptyResult($config, 'Belum ada jawaban yang bisa dinilai.');
        }

        $score = $config['presence_score'] ?? data_get($config, 'scale.max', 5);

        return $this->finalizeResult(
            $config,
            $score,
            0.95,
            'Jawaban tersedia dan memenuhi aturan kehadiran bukti.',
            [
                'method' => 'presence',
                'presence_score' => $score,
            ],
            false,
            'auto_presence'
        );
    }

    private function scoreChoice(array $field, AssessmentAttemptAnswer $answer, array $config): array
    {
        $normalizedOptions = ChoiceOptionNormalizer::normalizeMany($field['opsi_field'] ?? []);
        $selectedValues = collect(data_get($answer->answer_payload ?? [], 'values', []))
            ->filter(fn ($value) => filled($value))
            ->values();

        if ($selectedValues->isEmpty()) {
            $selectedValue = trim((string) data_get($answer->answer_payload ?? [], 'value', $answer->answer_text));

            if ($selectedValue !== '') {
                $selectedValues = collect([$selectedValue]);
            }
        }

        if ($selectedValues->isEmpty()) {
            return $this->emptyResult($config, 'Pilihan jawaban belum tersedia.');
        }

        $selectedOptions = collect($normalizedOptions)
            ->filter(function (array $option) use ($selectedValues) {
                return $selectedValues->contains(fn ($selectedValue) => in_array((string) $selectedValue, $option['aliases'] ?? [], true));
            })
            ->values();

        if ($selectedOptions->isEmpty()) {
            return $this->emptyResult($config, 'Pilihan jawaban tidak cocok dengan opsi yang tersedia.', true);
        }

        $scores = $selectedOptions
            ->map(fn (array $option) => is_numeric($option['score'] ?? null) ? (float) $option['score'] : null)
            ->filter(fn ($score) => $score !== null)
            ->values();

        if ($scores->isEmpty()) {
            return $this->scorePresence($answer, $config);
        }

        $aggregation = $config['method'] === 'choice_option_sum'
            ? 'sum'
            : ($config['method'] === 'choice_option_max' ? 'max' : ($config['choice_aggregation'] ?? 'average'));

        $rawScore = match ($aggregation) {
            'sum' => (float) $scores->sum(),
            'max' => (float) $scores->max(),
            'min' => (float) $scores->min(),
            default => (float) $scores->avg(),
        };

        $advancedRules = $config['advanced_rules'] ?? [];
        $maxRawSum = is_numeric($advancedRules['max_raw_sum'] ?? null) ? (float) $advancedRules['max_raw_sum'] : null;

        if ($aggregation === 'sum' && $maxRawSum && $maxRawSum > 0) {
            $ratio = min(max($rawScore / $maxRawSum, 0), 1);
            $rawScore = $this->scaleFromRatio($ratio, $config);
        }

        return $this->finalizeResult(
            $config,
            $rawScore,
            0.98,
            'Skor dihitung langsung dari bobot opsi yang dipilih peserta.',
            [
                'method' => 'choice',
                'aggregation' => $aggregation,
                'selected_values' => $selectedValues->all(),
                'selected_options' => $selectedOptions->map(fn (array $option) => [
                    'label' => $option['label'],
                    'value' => $option['value'],
                    'score' => $option['score'],
                ])->all(),
            ],
            false,
            'auto_choice_option'
        );
    }

    private function scoreNumeric(AssessmentAttemptAnswer $answer, array $config): array
    {
        $numericValue = data_get($answer->answer_payload ?? [], 'value', $answer->answer_text);

        if (! is_numeric($numericValue)) {
            return $this->emptyResult($config, 'Nilai angka tidak valid untuk dihitung.', true);
        }

        $value = (float) $numericValue;
        $rules = $config['numeric_rules'] ?? [];
        $direction = $rules['direction'] ?? 'greater_is_better';
        $scaleMin = (float) data_get($config, 'scale.min', 1);
        $scaleMax = (float) data_get($config, 'scale.max', 5);
        $midScore = $scaleMin + (($scaleMax - $scaleMin) / 2);
        $minScore = $rules['min_score'] ?? $scaleMin;
        $targetScore = $rules['target_score'] ?? $midScore;
        $maxScore = $rules['max_score'] ?? $scaleMax;
        $minThreshold = $rules['min_threshold'];
        $targetThreshold = $rules['target_threshold'];
        $maxThreshold = $rules['max_threshold'];

        $score = match ($direction) {
            'lower_is_better' => $this->scoreNumericLowerIsBetter(
                $value,
                $minThreshold,
                $targetThreshold,
                $maxThreshold,
                $minScore,
                $targetScore,
                $maxScore
            ),
            'range' => $this->scoreNumericRange(
                $value,
                $minThreshold,
                $maxThreshold,
                $minScore,
                $maxScore,
                (float) data_get($config, 'advanced_rules.tolerance', 0)
            ),
            default => $this->scoreNumericGreaterIsBetter(
                $value,
                $minThreshold,
                $targetThreshold,
                $maxThreshold,
                $minScore,
                $targetScore,
                $maxScore
            ),
        };

        return $this->finalizeResult(
            $config,
            $score,
            0.9,
            'Skor numerik dihitung dari ambang batas yang ditetapkan admin.',
            [
                'method' => 'numeric',
                'direction' => $direction,
                'value' => $value,
                'rules' => $rules,
            ],
            false,
            'auto_numeric_threshold'
        );
    }

    private function scoreSemanticText(array $field, AssessmentAttemptAnswer $answer, array $config): array
    {
        $answerText = trim((string) data_get($answer->answer_payload ?? [], 'value', $answer->answer_text));

        if ($answerText === '') {
            return $this->emptyResult($config, 'Belum ada teks jawaban yang bisa dianalisis.');
        }

        if (($config['reference_answer'] ?? '') === '' && ($config['keyword_groups'] ?? []) === []) {
            return $this->scorePresence($answer, $config);
        }

        $analysis = $this->textSimilarityAnalyzer->analyze(
            $answerText,
            $config['reference_answer'] ?? null,
            $config['keyword_groups'] ?? [],
            $config['synonyms'] ?? [],
            $config['advanced_rules'] ?? ['min_words' => $config['min_words'] ?? 0]
        );

        $cosine = (float) ($analysis['cosine_similarity'] ?? 0);
        $keywordCoverage = (float) ($analysis['keyword_coverage'] ?? 0);
        $phraseCoverage = (float) ($analysis['phrase_coverage'] ?? 0);
        $structureScore = (float) ($analysis['structure_score'] ?? 0);
        $lengthScore = (float) ($analysis['length_score'] ?? 0);
        $signalScore = (float) ($analysis['signal_score'] ?? 0);
        $weights = array_merge([
            'cosine' => 0.28,
            'keyword' => 0.28,
            'phrase' => 0.16,
            'structure' => 0.14,
            'length' => 0.08,
            'signal' => 0.06,
        ], (array) data_get($config, 'advanced_rules.fuzzy_weights', []));

        $blendedStrength = (
            ($cosine * (float) $weights['cosine']) +
            ($keywordCoverage * (float) $weights['keyword']) +
            ($phraseCoverage * (float) $weights['phrase']) +
            ($structureScore * (float) $weights['structure']) +
            ($lengthScore * (float) $weights['length']) +
            ($signalScore * (float) $weights['signal'])
        );

        $fuzzyStrength = min(
            (
                (FuzzyMembership::high($cosine, 0.25, 0.7) * 0.35) +
                (FuzzyMembership::high($keywordCoverage, 0.2, 0.75) * 0.30) +
                (FuzzyMembership::high($structureScore, 0.2, 0.7) * 0.15) +
                (FuzzyMembership::medium($lengthScore, 0.25, 0.7, 1.0) * 0.10) +
                (FuzzyMembership::high($signalScore, 0.2, 0.7) * 0.10)
            ),
            1.0
        );

        $treeScore = $this->semanticDecisionTreeScore(
            $config,
            $keywordCoverage,
            $cosine,
            $structureScore,
            $lengthScore,
            $signalScore
        );
        $fuzzyScaleScore = $this->scaleFromRatio(min(max(($blendedStrength * 0.65) + ($fuzzyStrength * 0.35), 0), 1), $config);
        $finalScore = (($fuzzyScaleScore * 0.55) + ($treeScore * 0.45));
        $confidence = min(max((0.4 * $keywordCoverage) + (0.3 * $cosine) + (0.15 * $structureScore) + (0.15 * $lengthScore), 0.05), 0.98);
        $requiresManualReview = (bool) ($config['manual_review_below_confidence'] ?? false)
            && $confidence < (float) ($config['confidence_threshold'] ?? 0.55);

        return $this->finalizeResult(
            $config,
            $finalScore,
            $confidence,
            sprintf(
                'Kemiripan semantik %.0f%%, cakupan kata kunci %.0f%%, dan struktur jawaban %.0f%%.',
                $cosine * 100,
                $keywordCoverage * 100,
                $structureScore * 100
            ),
            array_merge($analysis, [
                'method' => 'semantic_similarity',
                'blended_strength' => round($blendedStrength, 4),
                'fuzzy_strength' => round($fuzzyStrength, 4),
                'tree_score' => round($treeScore, 2),
            ]),
            $requiresManualReview,
            'auto_semantic_similarity'
        );
    }

    private function scoreRepeater(array $field, AssessmentAttemptAnswer $answer, array $config): array
    {
        $rows = collect(data_get($answer->answer_payload ?? [], 'rows', []))
            ->filter(fn ($row) => is_array($row))
            ->values();
        $columns = collect(data_get($answer->answer_payload ?? [], 'columns', data_get($field, 'opsi_field.columns', [])))
            ->filter(fn ($column) => is_array($column))
            ->values();

        if ($rows->isEmpty() || $columns->isEmpty()) {
            return $this->emptyResult($config, 'Data tabel berulang belum lengkap untuk dihitung.');
        }

        $requiredColumns = $columns->filter(fn ($column) => (bool) ($column['is_required'] ?? false));
        $requiredCellCount = max($requiredColumns->count() * $rows->count(), 0);
        $filledRequiredCells = $rows->sum(function (array $row) use ($requiredColumns) {
            return $requiredColumns->filter(fn ($column) => filled($row[$column['nama_field']] ?? null))->count();
        });
        $requiredCompleteness = $requiredCellCount > 0
            ? $filledRequiredCells / $requiredCellCount
            : 1.0;
        $textCells = $rows->flatMap(function (array $row) use ($columns) {
            return $columns
                ->filter(fn ($column) => in_array($column['tipe_field'] ?? 'text', ['text', 'textarea'], true))
                ->map(fn ($column) => trim((string) ($row[$column['nama_field']] ?? '')));
        })->filter();
        $richnessScore = min(
            $textCells->filter(fn ($value) => str_word_count($value) >= 4)->count() / max($textCells->count(), 1),
            1.0
        );
        $rowTarget = max(
            (int) data_get($config, 'advanced_rules.target_rows', data_get($field, 'opsi_field.min_rows', 1)),
            1
        );
        $rowAdequacy = min($rows->count() / $rowTarget, 1.0);
        $keywordCoverage = 0.0;

        if (($config['keyword_groups'] ?? []) !== []) {
            $analysis = $this->textSimilarityAnalyzer->analyze(
                $textCells->implode(' '),
                $config['reference_answer'] ?? null,
                $config['keyword_groups'] ?? [],
                $config['synonyms'] ?? [],
                $config['advanced_rules'] ?? []
            );
            $keywordCoverage = (float) ($analysis['keyword_coverage'] ?? 0);
        }

        $strength = min(
            (0.35 * $rowAdequacy) +
            (0.35 * $requiredCompleteness) +
            (0.15 * $richnessScore) +
            (0.15 * $keywordCoverage),
            1.0
        );

        return $this->finalizeResult(
            $config,
            $this->scaleFromRatio($strength, $config),
            min(max((0.45 * $requiredCompleteness) + (0.30 * $rowAdequacy) + (0.25 * $richnessScore), 0.1), 0.92),
            'Skor dihitung dari kelengkapan baris, kolom wajib, dan kualitas isi bukti tabel.',
            [
                'method' => 'repeater',
                'row_count' => $rows->count(),
                'row_target' => $rowTarget,
                'required_completeness' => round($requiredCompleteness, 4),
                'richness_score' => round($richnessScore, 4),
                'keyword_coverage' => round($keywordCoverage, 4),
            ],
            false,
            'auto_repeater_completeness'
        );
    }

    private function semanticDecisionTreeScore(
        array $config,
        float $keywordCoverage,
        float $cosine,
        float $structureScore,
        float $lengthScore,
        float $signalScore
    ): float {
        $scaleMin = (float) data_get($config, 'scale.min', 1);
        $scaleMax = (float) data_get($config, 'scale.max', 5);
        $range = $scaleMax - $scaleMin;

        return match (true) {
            $keywordCoverage >= 0.85 && $cosine >= 0.60 && $structureScore >= 0.55 => $scaleMax,
            $keywordCoverage >= 0.65 && $cosine >= 0.45 && $lengthScore >= 0.60 => $scaleMin + ($range * 0.8),
            $keywordCoverage >= 0.45 && ($cosine >= 0.32 || $structureScore >= 0.40 || $signalScore >= 0.40) => $scaleMin + ($range * 0.55),
            $keywordCoverage >= 0.20 || $cosine >= 0.18 || $lengthScore >= 0.35 => $scaleMin + ($range * 0.28),
            default => $scaleMin,
        };
    }

    private function scoreNumericGreaterIsBetter(
        float $value,
        ?float $minThreshold,
        ?float $targetThreshold,
        ?float $maxThreshold,
        float $minScore,
        float $targetScore,
        float $maxScore
    ): float {
        if ($minThreshold === null && $targetThreshold === null && $maxThreshold === null) {
            return $maxScore;
        }

        $minThreshold ??= $targetThreshold ?? $maxThreshold ?? $value;
        $targetThreshold ??= $maxThreshold ?? $minThreshold;
        $maxThreshold ??= $targetThreshold;

        if ($value <= $minThreshold) {
            return $minScore;
        }

        if ($value >= $maxThreshold) {
            return $maxScore;
        }

        if ($value <= $targetThreshold) {
            return $this->interpolate($value, $minThreshold, $targetThreshold, $minScore, $targetScore);
        }

        return $this->interpolate($value, $targetThreshold, $maxThreshold, $targetScore, $maxScore);
    }

    private function scoreNumericLowerIsBetter(
        float $value,
        ?float $minThreshold,
        ?float $targetThreshold,
        ?float $maxThreshold,
        float $minScore,
        float $targetScore,
        float $maxScore
    ): float {
        if ($minThreshold === null && $targetThreshold === null && $maxThreshold === null) {
            return $maxScore;
        }

        $bestThreshold = $minThreshold ?? $targetThreshold ?? $value;
        $targetThreshold ??= $bestThreshold;
        $maxThreshold ??= $targetThreshold;

        if ($value <= $bestThreshold) {
            return $maxScore;
        }

        if ($value >= $maxThreshold) {
            return $minScore;
        }

        if ($value <= $targetThreshold) {
            return $this->interpolate($value, $bestThreshold, $targetThreshold, $maxScore, $targetScore);
        }

        return $this->interpolate($value, $targetThreshold, $maxThreshold, $targetScore, $minScore);
    }

    private function scoreNumericRange(
        float $value,
        ?float $minThreshold,
        ?float $maxThreshold,
        float $minScore,
        float $maxScore,
        float $tolerance = 0
    ): float {
        if ($minThreshold === null || $maxThreshold === null) {
            return $maxScore;
        }

        if ($value >= $minThreshold && $value <= $maxThreshold) {
            return $maxScore;
        }

        $distance = $value < $minThreshold ? ($minThreshold - $value) : ($value - $maxThreshold);
        $allowedDistance = $tolerance > 0 ? $tolerance : max(($maxThreshold - $minThreshold) * 0.5, 1);

        if ($distance >= $allowedDistance) {
            return $minScore;
        }

        return $this->interpolate($distance, 0, $allowedDistance, $maxScore, $minScore);
    }

    private function scaleFromRatio(float $ratio, array $config): float
    {
        $ratio = min(max($ratio, 0), 1);
        $scaleMin = (float) data_get($config, 'scale.min', 1);
        $scaleMax = (float) data_get($config, 'scale.max', 5);

        return $scaleMin + (($scaleMax - $scaleMin) * $ratio);
    }

    private function finalizeResult(
        array $config,
        float|int|null $score,
        float $confidence,
        string $reason,
        array $metadata,
        bool $requiresManualReview = false,
        string $source = 'auto'
    ): array {
        if (! is_numeric($score)) {
            return $this->emptyResult($config, $reason, $requiresManualReview, $source, $metadata, $confidence);
        }

        $scaleMin = (float) data_get($config, 'scale.min', 1);
        $scaleMax = (float) data_get($config, 'scale.max', 5);
        $precision = (int) data_get($config, 'scale.precision', 2);
        $normalizedScore = round(min(max((float) $score, $scaleMin), $scaleMax), $precision);

        return [
            'score' => $normalizedScore,
            'confidence' => round(min(max($confidence, 0), 1), 4),
            'reason' => $reason,
            'source' => $source,
            'metadata' => $metadata,
            'requires_manual_review' => $requiresManualReview,
        ];
    }

    private function emptyResult(
        array $config,
        string $reason,
        bool $requiresManualReview = false,
        string $source = 'auto_unavailable',
        array $metadata = [],
        float $confidence = 0.0
    ): array {
        return [
            'score' => null,
            'confidence' => round(min(max($confidence, 0), 1), 4),
            'reason' => $reason,
            'source' => $source,
            'metadata' => $metadata,
            'requires_manual_review' => $requiresManualReview,
        ];
    }

    private function interpolate(float $value, float $fromX, float $toX, float $fromY, float $toY): float
    {
        if (abs($toX - $fromX) < 0.0001) {
            return $toY;
        }

        $ratio = ($value - $fromX) / ($toX - $fromX);

        return $fromY + (($toY - $fromY) * min(max($ratio, 0), 1));
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
