<?php

namespace App\Support\Assessment;

use App\Enum\LevelKompetensi;

class ChoiceOptionNormalizer
{
    public static function normalizeMany(?array $options): array
    {
        $normalizedOptions = [];

        foreach (static::prepareOptionList($options ?? []) as $index => $option) {
            $normalizedOption = static::normalize($option, $index);

            if ($normalizedOption['label'] === '' && $normalizedOption['value'] === '') {
                continue;
            }

            $normalizedOptions[] = $normalizedOption;
        }

        return array_values($normalizedOptions);
    }

    private static function prepareOptionList(array $options): array
    {
        if ($options === []) {
            return [];
        }

        if (array_is_list($options)) {
            return array_values($options);
        }

        if (array_key_exists('label', $options) || array_key_exists('value', $options)) {
            return [$options];
        }

        return collect($options)
            ->map(function ($label, $value) {
                if (is_array($label)) {
                    return $label;
                }

                $normalizedLabel = trim((string) $label);
                $normalizedValue = is_scalar($value) ? trim((string) $value) : '';

                return [
                    'label' => $normalizedLabel,
                    'value' => $normalizedValue !== '' ? $normalizedValue : $normalizedLabel,
                ];
            })
            ->values()
            ->all();
    }

    public static function normalize(mixed $option, ?int $index = null): array
    {
        if (! is_array($option)) {
            $text = trim((string) $option);

            return [
                'label' => $text,
                'value' => $text,
                'aliases' => $text !== '' ? [$text] : [],
                'score' => null,
                'level_kompetensi' => null,
                'level_kompetensi_label' => null,
            ];
        }

        $rawLabel = trim((string) ($option['label'] ?? ''));
        $rawValue = trim((string) ($option['value'] ?? ''));

        if (static::shouldSwapLabelAndValue($rawLabel, $rawValue)) {
            [$rawLabel, $rawValue] = [$rawValue, $rawLabel];
        }

        $label = $rawLabel;
        $value = $rawValue;

        if ($label === '' && $value !== '') {
            $label = $value;
        }

        if ($value === '' && $label !== '') {
            $value = $label;
        }

        $aliases = array_values(array_unique(array_filter([
            $value,
            $label,
            trim((string) ($option['value'] ?? '')),
            trim((string) ($option['label'] ?? '')),
        ], fn ($item) => $item !== '')));
        $competencyLevel = static::resolveCompetencyLevel(
            $option,
            $index,
            $value,
            $label,
            $rawValue,
            $rawLabel
        );

        return [
            'label' => $label,
            'value' => $value,
            'aliases' => $aliases,
            'score' => is_numeric($option['score'] ?? null)
                ? (float) $option['score']
                : ($competencyLevel?->value !== null ? (float) $competencyLevel->value : null),
            'level_kompetensi' => $competencyLevel?->value,
            'level_kompetensi_label' => $competencyLevel?->label(),
        ];
    }

    private static function resolveCompetencyLevel(
        array $option,
        ?int $index,
        string $normalizedValue,
        string $normalizedLabel,
        string $rawValue,
        string $rawLabel
    ): ?LevelKompetensi {
        $explicitLevel = LevelKompetensi::tryFromMixed(
            $option['level_kompetensi']
                ?? $option['kompetensi_level']
                ?? $option['competency_level']
                ?? null
        );

        if ($explicitLevel) {
            return $explicitLevel;
        }

        $codeCandidates = [
            $normalizedValue,
            $normalizedLabel,
            $rawValue,
            $rawLabel,
        ];

        foreach ($codeCandidates as $candidate) {
            $levelFromCode = LevelKompetensi::tryFromChoiceCode($candidate);

            if ($levelFromCode) {
                return $levelFromCode;
            }
        }

        if ($index !== null && (bool) ($option['infer_level_from_sequence'] ?? false)) {
            return LevelKompetensi::tryFromSequence($index + 1);
        }

        return null;
    }

    private static function shouldSwapLabelAndValue(string $label, string $value): bool
    {
        if ($label === '' || $value === '') {
            return false;
        }

        return static::looksLikeCode($label) && static::looksLikeAnswerText($value);
    }

    private static function looksLikeCode(string $value): bool
    {
        if ($value === '' || preg_match('/\s/', $value) === 1) {
            return false;
        }

        if (mb_strlen($value) > 6) {
            return false;
        }

        return preg_match('/^[A-Za-z0-9._-]+$/', $value) === 1;
    }

    private static function looksLikeAnswerText(string $value): bool
    {
        if ($value === '') {
            return false;
        }

        return preg_match('/\s/', $value) === 1 || mb_strlen($value) >= 6;
    }
}
