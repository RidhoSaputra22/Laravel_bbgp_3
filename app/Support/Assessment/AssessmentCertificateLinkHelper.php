<?php

namespace App\Support\Assessment;

use Illuminate\Support\Str;

class AssessmentCertificateLinkHelper
{
    /**
     * @param  array<string, mixed>  $snapshot
     * @param  array<int, array<string, mixed>>  $answerLookup
     * @return array<int, array<string, string|int|null>>
     */
    public static function collectFromSnapshot(array $snapshot, array $answerLookup): array
    {
        return collect($snapshot['assessments'] ?? [])
            ->filter(fn ($assessment) => is_array($assessment))
            ->flatMap(function (array $assessment) use ($answerLookup) {
                $assessmentTitle = trim((string) ($assessment['judul'] ?? 'Assessment'));

                return collect($assessment['forms'] ?? [])
                    ->filter(fn ($form) => is_array($form))
                    ->flatMap(function (array $form) use ($assessmentTitle, $answerLookup) {
                        $formTitle = trim((string) ($form['judul_form'] ?? 'Form'));

                        return collect($form['fields'] ?? [])
                            ->filter(fn ($field) => is_array($field))
                            ->flatMap(function (array $field) use ($assessmentTitle, $formTitle, $answerLookup) {
                                $fieldId = (int) ($field['id'] ?? 0);

                                if ($fieldId <= 0) {
                                    return [];
                                }

                                $answer = $answerLookup[$fieldId] ?? null;

                                if (! is_array($answer)) {
                                    return [];
                                }

                                return match ($field['tipe_field'] ?? 'text') {
                                    'repeater' => static::collectRepeaterLinks($assessmentTitle, $formTitle, $field, $answer),
                                    'file' => static::collectFileLink($assessmentTitle, $formTitle, $field, $answer),
                                    default => [],
                                };
                            });
                    });
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $field
     * @param  array<string, mixed>  $answer
     * @return array<int, array<string, string|int|null>>
     */
    private static function collectRepeaterLinks(
        string $assessmentTitle,
        string $formTitle,
        array $field,
        array $answer
    ): array {
        $columns = AssessmentAnswerViewHelper::resolveRepeaterColumns($field, $answer);
        $rows = AssessmentAnswerViewHelper::resolveRepeaterRows($answer);

        if ($columns === [] || $rows === []) {
            return [];
        }

        $linkColumns = collect($columns)
            ->filter(fn ($column) => is_array($column))
            ->filter(fn (array $column) => static::isCertificateLinkDefinition($column))
            ->values()
            ->all();

        if ($linkColumns === []) {
            return [];
        }

        return collect($rows)
            ->filter(fn ($row) => is_array($row))
            ->flatMap(function (array $row, int $rowIndex) use (
                $assessmentTitle,
                $formTitle,
                $field,
                $columns,
                $linkColumns
            ) {
                $rowTitle = static::resolveRowTitle($columns, $row, $rowIndex + 1);
                $rowDetail = static::resolveRowDetail($columns, $row);

                return collect($linkColumns)
                    ->map(function (array $linkColumn) use (
                        $assessmentTitle,
                        $formTitle,
                        $field,
                        $row,
                        $rowIndex,
                        $rowTitle,
                        $rowDetail
                    ) {
                        $columnName = trim((string) ($linkColumn['nama_field'] ?? ''));
                        $url = trim((string) ($row[$columnName] ?? ''));

                        if (! static::isValidExternalUrl($url, $linkColumn)) {
                            return null;
                        }

                        return [
                            'assessment_title' => $assessmentTitle !== '' ? $assessmentTitle : 'Assessment',
                            'form_title' => $formTitle !== '' ? $formTitle : 'Form',
                            'field_label' => static::resolveDefinitionLabel($field, 'Pertanyaan'),
                            'link_label' => static::resolveDefinitionLabel($linkColumn, 'Link Sertifikat'),
                            'title' => $rowTitle,
                            'detail' => $rowDetail,
                            'url' => $url,
                            'row_number' => $rowIndex + 1,
                        ];
                    })
                    ->filter()
                    ->values()
                    ->all();
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $field
     * @param  array<string, mixed>  $answer
     * @return array<int, array<string, string|int|null>>
     */
    private static function collectFileLink(
        string $assessmentTitle,
        string $formTitle,
        array $field,
        array $answer
    ): array {
        if (! static::isCertificateLinkDefinition($field)) {
            return [];
        }

        $url = trim((string) (data_get($answer, 'payload.link_url') ?: data_get($answer, 'file_url')));

        if (! static::isValidExternalUrl($url, $field)) {
            return [];
        }

        return [[
            'assessment_title' => $assessmentTitle !== '' ? $assessmentTitle : 'Assessment',
            'form_title' => $formTitle !== '' ? $formTitle : 'Form',
            'field_label' => static::resolveDefinitionLabel($field, 'Pertanyaan'),
            'link_label' => static::resolveDefinitionLabel($field, 'Link Sertifikat'),
            'title' => static::resolveDefinitionLabel($field, 'Dokumen Sertifikat'),
            'detail' => trim((string) ($field['deskripsi'] ?? '')) ?: null,
            'url' => $url,
            'row_number' => 1,
        ]];
    }

    /**
     * @param  array<int, array<string, mixed>>  $columns
     * @param  array<string, mixed>  $row
     */
    private static function resolveRowTitle(array $columns, array $row, int $rowNumber): string
    {
        $preferredNames = [
            'nama_pelatihan',
            'nama_prestasi',
            'judul_karya',
            'pengalaman',
            'nama_sertifikat',
            'judul',
            'nama',
        ];

        foreach ($preferredNames as $preferredName) {
            $value = trim((string) ($row[$preferredName] ?? ''));

            if ($value !== '') {
                return $value;
            }
        }

        foreach ($columns as $column) {
            if (! is_array($column) || static::isCertificateLinkDefinition($column)) {
                continue;
            }

            $columnName = trim((string) ($column['nama_field'] ?? ''));
            $value = trim((string) ($row[$columnName] ?? ''));

            if ($value !== '') {
                return $value;
            }
        }

        return 'Entri '.$rowNumber;
    }

    /**
     * @param  array<int, array<string, mixed>>  $columns
     * @param  array<string, mixed>  $row
     */
    private static function resolveRowDetail(array $columns, array $row): ?string
    {
        $details = collect($columns)
            ->filter(fn ($column) => is_array($column))
            ->reject(fn (array $column) => static::isCertificateLinkDefinition($column))
            ->map(function (array $column) use ($row) {
                $columnName = trim((string) ($column['nama_field'] ?? ''));
                $value = AssessmentAnswerViewHelper::formatRepeaterCell($column, $row[$columnName] ?? null);

                if ($value === '-') {
                    return null;
                }

                return sprintf('%s: %s', static::resolveDefinitionLabel($column, $columnName), $value);
            })
            ->filter()
            ->take(2)
            ->values()
            ->all();

        return $details !== [] ? implode(' • ', $details) : null;
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    private static function isCertificateLinkDefinition(array $definition): bool
    {
        $fieldType = trim((string) ($definition['tipe_field'] ?? ''));

        if (! in_array($fieldType, ['url', 'file'], true)) {
            return false;
        }

        $label = Str::lower(trim((string) ($definition['label'] ?? '')));
        $fieldName = Str::lower(trim((string) ($definition['nama_field'] ?? '')));
        $combined = trim($label.' '.$fieldName);

        return str_contains($combined, 'sertifikat')
            || str_contains($combined, 'sertifikasi')
            || str_contains($combined, 'piagam')
            || str_contains($combined, 'surat keputusan')
            || (bool) preg_match('/(^|[\s_\/-])sk($|[\s_\/-])/u', $combined);
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    private static function resolveDefinitionLabel(array $definition, string $fallback): string
    {
        $label = trim((string) ($definition['label'] ?? ''));
        $fieldName = trim((string) ($definition['nama_field'] ?? ''));

        if ($label !== '') {
            return $label;
        }

        if ($fieldName !== '') {
            return Str::headline(str_replace('_', ' ', $fieldName));
        }

        return $fallback;
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    private static function isValidExternalUrl(?string $url, array $definition): bool
    {
        return AssessmentUrlValidationHelper::matchesDefinition($url, $definition);
    }
}
