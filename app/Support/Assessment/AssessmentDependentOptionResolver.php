<?php

namespace App\Support\Assessment;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AssessmentDependentOptionResolver
{
    public function isEnabled(array $field): bool
    {
        $config = $this->normalizeConfig($field['dependency_config'] ?? null);

        return $config !== null && $config['enabled'];
    }

    public function normalizeConfig(mixed $config): ?array
    {
        if (! is_array($config)) {
            return null;
        }

        $parentField = $this->normalizeFieldName($config['parent_field'] ?? null);
        $optionsByParent = $config['options_by_parent'] ?? null;

        if ($parentField === '' || ! is_array($optionsByParent) || $optionsByParent === []) {
            return null;
        }

        $normalizedOptions = [];

        foreach ($optionsByParent as $parentValue => $options) {
            $parentKey = trim((string) $parentValue);

            if ($parentKey === '' || ! is_array($options)) {
                return null;
            }

            $normalizedParentOptions = ChoiceOptionNormalizer::normalizeMany($options);

            if ($normalizedParentOptions === []) {
                return null;
            }

            $normalizedOptions[$parentKey] = $normalizedParentOptions;
        }

        if ($normalizedOptions === []) {
            return null;
        }

        $emptyBehavior = $config['empty_behavior'] ?? 'disabled';

        return [
            'enabled' => (bool) ($config['enabled'] ?? true),
            'parent_field' => $parentField,
            'options_by_parent' => $normalizedOptions,
            'empty_behavior' => in_array(
                $emptyBehavior,
                ['disabled', 'empty'],
                true
            ) ? $emptyBehavior : 'disabled',
            'reset_on_parent_change' => (bool) ($config['reset_on_parent_change'] ?? true),
        ];
    }

    /**
     * @param array<string, mixed> $field
     * @param array<string, mixed> $answersByFieldName
     * @return array<int, array<string, mixed>>
     */
    public function resolveOptions(array $field, array $answersByFieldName = []): array
    {
        $config = $this->normalizeConfig($field['dependency_config'] ?? null);

        if ($config === null || ! $config['enabled']) {
            return [];
        }

        $parentValue = $this->normalizeAnswerValue(
            $answersByFieldName[$config['parent_field']] ?? null
        );

        if ($parentValue === '') {
            return [];
        }

        return array_values($config['options_by_parent'][$parentValue] ?? []);
    }

    public function parentFieldName(array $field): ?string
    {
        $config = $this->normalizeConfig($field['dependency_config'] ?? null);

        return $config !== null && $config['enabled']
            ? $config['parent_field']
            : null;
    }

    public function normalizeFieldName(mixed $value): string
    {
        $fieldName = trim((string) ($value ?? ''));
        $fieldName = str_replace(['/', '\\'], ' ', $fieldName);
        $fieldName = Str::slug($fieldName, '_');

        return preg_replace('/^(?:soal_)?\d+_/', '', $fieldName) ?? $fieldName;
    }

    public function normalizeAnswerValue(mixed $value): string
    {
        if (is_array($value)) {
            return trim((string) ($value['value'] ?? Arr::first($value) ?? ''));
        }

        return trim((string) ($value ?? ''));
    }
}
