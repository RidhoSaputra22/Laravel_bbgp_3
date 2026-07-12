<?php

namespace App\Support\Assessment;

class ChoiceFieldOtherOption
{
    public const VALUE = '__other_option__';

    public const LABEL = 'Lainnya';

    public static function isEnabled(array $field): bool
    {
        return (bool) data_get(
            $field,
            'validasi.allow_other_input',
            data_get($field, 'allow_other_input', false)
        );
    }

    public static function option(): array
    {
        return [
            'label' => static::LABEL,
            'value' => static::VALUE,
            'is_other' => true,
        ];
    }

    public static function appendOption(array $field, array $options): array
    {
        if (! static::isEnabled($field)) {
            return $options;
        }

        $alreadyExists = collect($options)->contains(function ($option) {
            if (! is_array($option)) {
                return trim((string) $option) === static::VALUE;
            }

            return trim((string) ($option['value'] ?? '')) === static::VALUE;
        });

        if ($alreadyExists) {
            return $options;
        }

        $options[] = static::option();

        return $options;
    }

    public static function isSelected(?array $answer): bool
    {
        return (bool) data_get($answer, 'payload.is_other', data_get($answer, 'is_other', false))
            || trim((string) data_get($answer, 'payload.value', data_get($answer, 'value'))) === static::VALUE;
    }

    public static function resolveText(?array $answer): string
    {
        return trim((string) (
            data_get($answer, 'payload.other_text', data_get($answer, 'other_text'))
            ?: data_get($answer, 'text')
            ?: ''
        ));
    }
}
