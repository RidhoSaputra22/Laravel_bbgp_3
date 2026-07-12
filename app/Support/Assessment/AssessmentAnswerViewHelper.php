<?php

namespace App\Support\Assessment;

use Illuminate\Support\Carbon;

class AssessmentAnswerViewHelper
{
    public static function resolveOptionMap(array $field): array
    {
        return collect($field['opsi_field'] ?? [])
            ->flatMap(function ($option, $index) {
                if (! is_array($option)) {
                    $value = trim((string) $option);

                    return $value !== '' ? [$value => $value] : [];
                }

                $normalizedOption = ChoiceOptionNormalizer::normalize($option, $index);
                $label = trim((string) ($normalizedOption['label'] ?? ''));

                return collect($normalizedOption['aliases'] ?? [])
                    ->mapWithKeys(function ($value) use ($label) {
                        $value = trim((string) $value);

                        return $value !== '' ? [$value => $label] : [];
                    })
                    ->all();
            })
            ->all();
    }

    public static function resolveSelectedValues(array $field, ?array $answer): array
    {
        if (! $answer) {
            return [];
        }

        if (($field['tipe_field'] ?? null) === 'checkbox') {
            return collect(data_get($answer, 'payload.values', []))
                ->map(fn ($value) => trim((string) $value))
                ->filter(fn ($value) => $value !== '')
                ->values()
                ->all();
        }

        $textValue = trim((string) data_get($answer, 'text', ''));

        return $textValue !== '' ? [$textValue] : [];
    }

    public static function resolveAnswerText(array $field, ?array $answer): string
    {
        if (! $answer) {
            return '';
        }

        $fieldType = $field['tipe_field'] ?? 'text';

        if ($fieldType === 'checkbox') {
            $optionMap = static::resolveOptionMap($field);

            return collect(static::resolveSelectedValues($field, $answer))
                ->map(fn ($value) => $optionMap[(string) $value] ?? (string) $value)
                ->implode(', ');
        }

        if (in_array($fieldType, ['radio', 'select'], true)) {
            $selectedValue = static::resolveSelectedValues($field, $answer)[0] ?? '';
            $optionMap = static::resolveOptionMap($field);

            return $optionMap[(string) $selectedValue] ?? (string) $selectedValue;
        }

        if ($fieldType === 'file') {
            if (filled(data_get($answer, 'payload.link_url'))) {
                return trim((string) data_get($answer, 'payload.link_url'));
            }

            return (string) (data_get($answer, 'payload.original_name') ?: data_get($answer, 'text', ''));
        }

        if ($fieldType === 'date') {
            return static::formatDateAnswer(data_get($answer, 'text'));
        }

        if ($fieldType === 'repeater') {
            $rows = static::resolveRepeaterRows($answer);

            return $rows === [] ? '' : count($rows).' entri';
        }

        return trim((string) data_get($answer, 'text', ''));
    }

    public static function hasAnswer(array $field, ?array $answer): bool
    {
        if (! $answer) {
            return false;
        }

        $fieldType = $field['tipe_field'] ?? 'text';

        if ($fieldType === 'checkbox') {
            return static::resolveSelectedValues($field, $answer) !== [];
        }

        if ($fieldType === 'file') {
            return filled(data_get($answer, 'file_url')) || filled(data_get($answer, 'text'));
        }

        if ($fieldType === 'repeater') {
            return static::resolveRepeaterRows($answer) !== [];
        }

        return trim((string) data_get($answer, 'text', '')) !== '';
    }

    public static function resolveRepeaterColumns(array $field, ?array $answer = null): array
    {
        $columns = data_get($answer, 'columns', []);

        if (is_array($columns) && $columns !== []) {
            return $columns;
        }

        return collect(data_get($field, 'opsi_field.columns', []))
            ->filter(fn ($column) => is_array($column))
            ->values()
            ->all();
    }

    public static function resolveRepeaterRows(?array $answer): array
    {
        return collect(data_get($answer, 'rows', []))
            ->filter(fn ($row) => is_array($row))
            ->values()
            ->all();
    }

    public static function formatRepeaterCell(array $column, mixed $value): string
    {
        $textValue = trim((string) $value);

        if ($textValue === '') {
            return '-';
        }

        if (($column['tipe_field'] ?? null) === 'date') {
            return static::formatDateAnswer($textValue) ?: $textValue;
        }

        return $textValue;
    }

    public static function formatDateAnswer(?string $value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        try {
            return Carbon::parse($value)->format('d M Y');
        } catch (\Throwable $exception) {
            return $value;
        }
    }
}
