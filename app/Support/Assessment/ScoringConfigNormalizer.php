<?php

namespace App\Support\Assessment;

use App\Enum\AssessmentInstrumentType;

class ScoringConfigNormalizer
{
    public function normalizeAssessment(array $assessment): array
    {
        $instrument = AssessmentInstrumentType::tryFromMixed($assessment['instrument_type'] ?? null);
        $config = is_array($assessment['scoring_config'] ?? null) ? $assessment['scoring_config'] : [];

        return [
            'profile' => $config['profile'] ?? $instrument?->value ?? 'generic',
            'weight' => $this->toFloat($config['weight'] ?? $instrument?->weight(), $instrument?->weight() ?? 1.0),
            'verification_gap_threshold' => $this->toFloat($config['verification_gap_threshold'] ?? 1.50, 1.50),
            'empty_response_threshold_percent' => $this->toFloat($config['empty_response_threshold_percent'] ?? 10, 10),
            'advanced_rules' => $this->parseAdvancedRules($config['advanced_rules_text'] ?? $config['advanced_rules'] ?? null),
        ];
    }

    public function normalizeForm(array $form, ?array $assessment = null): array
    {
        $config = is_array($form['scoring_config'] ?? null) ? $form['scoring_config'] : [];
        $assessmentConfig = $assessment ? $this->normalizeAssessment($assessment) : [
            'profile' => 'generic',
            'advanced_rules' => [],
        ];

        return [
            'profile' => trim((string) ($config['profile'] ?? $assessmentConfig['profile'] ?? 'generic')) ?: 'generic',
            'weight' => $this->toFloat($config['weight'] ?? 1, 1),
            'exclude_from_competency' => (bool) ($config['exclude_from_competency'] ?? false),
            'synthetic_criteria' => is_array($config['synthetic_criteria'] ?? null) ? $config['synthetic_criteria'] : [],
            'advanced_rules' => $this->parseAdvancedRules($config['advanced_rules_text'] ?? $config['advanced_rules'] ?? null),
        ];
    }

    public function normalizeField(array $field, ?array $form = null, ?array $assessment = null): array
    {
        $fieldType = trim((string) ($field['tipe_field'] ?? 'text')) ?: 'text';
        $formConfig = $form ? $this->normalizeForm($form, $assessment) : [
            'profile' => 'generic',
            'advanced_rules' => [],
        ];
        $config = is_array($field['scoring_config'] ?? null) ? $field['scoring_config'] : [];
        $advancedRules = $this->parseAdvancedRules($config['advanced_rules_text'] ?? $config['advanced_rules'] ?? null);
        $scaleMin = $this->toFloat($config['scale_min'] ?? data_get($advancedRules, 'scale.min') ?? 1, 1);
        $scaleMax = $this->toFloat($config['scale_max'] ?? data_get($advancedRules, 'scale.max') ?? 5, 5);

        return [
            'enabled' => (bool) ($config['enabled'] ?? $config['auto_score_enabled'] ?? ($form['is_scoreable'] ?? true)),
            'rubric_code' => trim((string) ($config['rubric_code'] ?? '')),
            'profile' => trim((string) ($config['profile'] ?? $formConfig['profile'] ?? 'generic')) ?: 'generic',
            'method' => trim((string) ($config['method'] ?? $this->defaultMethodFor($fieldType, $formConfig['profile'] ?? 'generic'))),
            'weight' => max($this->toFloat($config['weight'] ?? $config['field_weight'] ?? 1, 1), 0.01),
            'scale' => [
                'min' => min($scaleMin, $scaleMax),
                'max' => max($scaleMin, $scaleMax),
                'precision' => max((int) ($config['precision'] ?? 2), 0),
            ],
            'presence_score' => $this->nullableFloat($config['score_if_answered'] ?? $config['presence_score'] ?? null),
            'reference_answer' => trim((string) ($config['reference_answer'] ?? '')),
            'keyword_groups' => $this->parseKeywordGroups(
                $config['keyword_groups_text'] ?? $config['keyword_groups'] ?? data_get($advancedRules, 'keyword_groups')
            ),
            'synonyms' => $this->parseSynonyms(
                $config['synonym_map_text'] ?? $config['synonyms'] ?? data_get($advancedRules, 'synonyms')
            ),
            'choice_aggregation' => trim((string) ($config['choice_aggregation'] ?? 'average')) ?: 'average',
            'min_words' => max((int) ($config['min_words'] ?? data_get($advancedRules, 'min_words') ?? $this->defaultMinWordsFor($fieldType)), 0),
            'confidence_threshold' => $this->toFloat($config['confidence_threshold'] ?? data_get($advancedRules, 'confidence_threshold') ?? 0.55, 0.55),
            'manual_review_below_confidence' => (bool) ($config['manual_review_below_confidence'] ?? false),
            'numeric_rules' => [
                'direction' => trim((string) ($config['numeric_direction'] ?? data_get($advancedRules, 'numeric_rules.direction') ?? 'greater_is_better')) ?: 'greater_is_better',
                'min_threshold' => $this->nullableFloat($config['min_threshold'] ?? data_get($advancedRules, 'numeric_rules.min_threshold')),
                'target_threshold' => $this->nullableFloat($config['target_threshold'] ?? data_get($advancedRules, 'numeric_rules.target_threshold')),
                'max_threshold' => $this->nullableFloat($config['max_threshold'] ?? data_get($advancedRules, 'numeric_rules.max_threshold')),
                'min_score' => $this->nullableFloat($config['min_score'] ?? data_get($advancedRules, 'numeric_rules.min_score')),
                'target_score' => $this->nullableFloat($config['target_score'] ?? data_get($advancedRules, 'numeric_rules.target_score')),
                'max_score' => $this->nullableFloat($config['max_score'] ?? data_get($advancedRules, 'numeric_rules.max_score')),
            ],
            'advanced_rules' => $advancedRules,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function parseAdvancedRules(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE && is_array($decoded) ? $decoded : [];
    }

    /**
     * @return array<int, array<int, string>>
     */
    private function parseKeywordGroups(mixed $value): array
    {
        if (is_array($value)) {
            return $this->normalizeKeywordGroupsArray($value);
        }

        $raw = trim((string) $value);

        if ($raw === '') {
            return [];
        }

        if (! $this->keywordGroupsUsesLegacySeparators($raw)) {
            return collect(explode(',', $raw))
                ->map(fn ($keyword) => trim((string) $keyword))
                ->filter()
                ->values()
                ->map(fn ($keyword) => [$keyword])
                ->all();
        }

        return collect(preg_split('/\r\n|\r|\n/', $raw))
            ->map(function ($line) {
                return collect(preg_split('/\s*(?:\||;|,)\s*/', (string) $line))
                    ->map(fn ($part) => trim((string) $part))
                    ->filter()
                    ->values()
                    ->all();
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, mixed>  $value
     * @return array<int, array<int, string>>
     */
    private function normalizeKeywordGroupsArray(array $value): array
    {
        return collect($value)
            ->map(function ($item) {
                return collect((array) $item)
                    ->map(fn ($part) => trim((string) $part))
                    ->filter()
                    ->values()
                    ->all();
            })
            ->filter()
            ->values()
            ->all();
    }

    private function keywordGroupsUsesLegacySeparators(string $value): bool
    {
        return preg_match('/[\r\n|;]/', $value) === 1;
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function parseSynonyms(mixed $value): array
    {
        if (is_array($value)) {
            return collect($value)
                ->mapWithKeys(function ($variants, $baseWord) {
                    $base = trim((string) $baseWord);

                    if ($base === '') {
                        return [];
                    }

                    return [
                        $base => collect((array) $variants)
                            ->map(fn ($variant) => trim((string) $variant))
                            ->filter()
                            ->values()
                            ->all(),
                    ];
                })
                ->all();
        }

        $raw = trim((string) $value);

        if ($raw === '') {
            return [];
        }

        return collect(preg_split('/\r\n|\r|\n/', $raw))
            ->mapWithKeys(function ($line) {
                [$baseWord, $variants] = array_pad(preg_split('/\s*[:=]\s*/', (string) $line, 2), 2, '');
                $baseWord = trim((string) $baseWord);

                if ($baseWord === '') {
                    return [];
                }

                return [
                    $baseWord => collect(preg_split('/\s*(?:\||;|,)\s*/', (string) $variants))
                        ->map(fn ($variant) => trim((string) $variant))
                        ->filter()
                        ->values()
                        ->all(),
                ];
            })
            ->all();
    }

    private function defaultMethodFor(string $fieldType, string $profile): string
    {
        return match ($fieldType) {
            'radio', 'select' => 'choice_option_score',
            'checkbox' => 'choice_option_average',
            'number' => 'numeric_threshold',
            'textarea' => in_array($profile, ['studi_kasus', 'study_case_default', 'portofolio'], true)
                ? 'semantic_similarity'
                : 'presence',
            'text' => $profile === 'portofolio' ? 'semantic_similarity' : 'presence',
            'file', 'date', 'email' => 'presence',
            'repeater' => 'repeater_completeness',
            default => 'presence',
        };
    }

    private function defaultMinWordsFor(string $fieldType): int
    {
        return match ($fieldType) {
            'textarea' => 40,
            'text' => 8,
            default => 0,
        };
    }

    private function toFloat(mixed $value, float $default): float
    {
        return is_numeric($value) ? (float) $value : $default;
    }

    private function nullableFloat(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }
}
