<?php

namespace App\Support\Assessment;

use Illuminate\Support\Str;

class AssessmentCertificateLinkHelper
{
    /**
     * Repeater portfolio terbaru memakai kolom link generik untuk bukti dokumen.
     * Kolom ini tetap perlu diperlakukan sebagai link sertifikat/SK pada konteks field tertentu.
     *
     * @var array<int, string>
     */
    private const GENERIC_PORTFOLIO_LINK_COLUMNS = [
        'tautan_link_google_drive',
    ];

    /**
     * @var array<int, string>
     */
    private const PORTFOLIO_EVIDENCE_REPEATER_FIELDS = [
        'riwayat_pendidikan_formal',
        'pengalaman_pelatihan',
        'pengalaman_pelatihan_relevan',
        'pengalaman_mengajar',
        'riwayat_pengalaman_mengajar',
        'penguasaan_profesional',
        'contoh_praktik_penguasaan_profesional',
        'karya_inovasi_best_practice',
        'karya_inovasi_prestasi_diseminasi',
    ];

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
                                    'file', 'url', 'text' => static::collectSingleValueLink($assessmentTitle, $formTitle, $field, $answer),
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

        if ($rows === []) {
            return [];
        }

        $linkColumns = collect($columns)
            ->filter(fn ($column) => is_array($column))
            ->filter(fn (array $column) => static::isCertificateLinkDefinition($column, $field))
            ->values()
            ->all();

        if ($linkColumns === []) {
            $linkColumns = static::inferLinkColumnsFromRows($rows, $columns, $field);
        }

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
                $rowTitle = static::resolveRowTitle($columns, $row, $rowIndex + 1, $field);
                $rowDetail = static::resolveRowDetail($columns, $row, $field);

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
    private static function collectSingleValueLink(
        string $assessmentTitle,
        string $formTitle,
        array $field,
        array $answer
    ): array {
        if (! static::isCertificateLinkDefinition($field)) {
            return [];
        }

        $url = collect([
            data_get($answer, 'payload.link_url'),
            data_get($answer, 'file_url'),
            data_get($answer, 'payload.value'),
            data_get($answer, 'text'),
        ])
            ->map(fn ($value) => trim((string) $value))
            ->first(fn (string $value) => $value !== '');

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
     * @param  array<string, mixed>|null  $parentField
     */
    private static function resolveRowTitle(
        array $columns,
        array $row,
        int $rowNumber,
        ?array $parentField = null
    ): string
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
            if (! is_array($column) || static::isCertificateLinkDefinition($column, $parentField)) {
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
     * @param  array<string, mixed>|null  $parentField
     */
    private static function resolveRowDetail(array $columns, array $row, ?array $parentField = null): ?string
    {
        $details = collect($columns)
            ->filter(fn ($column) => is_array($column))
            ->reject(fn (array $column) => static::isCertificateLinkDefinition($column, $parentField))
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
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<int, array<string, mixed>>  $columns
     * @param  array<string, mixed>  $field
     * @return array<int, array<string, mixed>>
     */
    private static function inferLinkColumnsFromRows(array $rows, array $columns, array $field): array
    {
        $columnsByName = collect($columns)
            ->filter(fn ($column) => is_array($column))
            ->mapWithKeys(function (array $column) {
                $columnName = trim((string) ($column['nama_field'] ?? ''));

                return $columnName !== '' ? [$columnName => $column] : [];
            })
            ->all();

        return collect($rows)
            ->filter(fn ($row) => is_array($row))
            ->flatMap(fn (array $row) => array_keys($row))
            ->map(fn ($columnName) => trim((string) $columnName))
            ->filter(fn (string $columnName) => $columnName !== '')
            ->unique()
            ->map(function (string $columnName) use ($columnsByName, $field) {
                $existingDefinition = $columnsByName[$columnName] ?? [];

                if (! static::isLikelyCertificateLinkKey($columnName, $field, $existingDefinition)) {
                    return null;
                }

                return [
                    'label' => trim((string) ($existingDefinition['label'] ?? '')) ?: Str::headline(str_replace('_', ' ', $columnName)),
                    'nama_field' => $columnName,
                    'tipe_field' => trim((string) ($existingDefinition['tipe_field'] ?? 'url')) ?: 'url',
                    'validasi' => is_array($existingDefinition['validasi'] ?? null) ? $existingDefinition['validasi'] : [],
                    'opsi_field' => is_array($existingDefinition['opsi_field'] ?? null) ? $existingDefinition['opsi_field'] : [],
                    'allowed_domains' => $existingDefinition['allowed_domains'] ?? [],
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $definition
     * @param  array<string, mixed>|null  $parentField
     */
    private static function isCertificateLinkDefinition(array $definition, ?array $parentField = null): bool
    {
        $label = Str::lower(trim((string) ($definition['label'] ?? '')));
        $fieldName = Str::lower(trim((string) ($definition['nama_field'] ?? '')));
        $combined = trim($label.' '.$fieldName);

        $fieldType = trim((string) ($definition['tipe_field'] ?? ''));

        if (
            $fieldType === 'url'
            && static::isGenericPortfolioEvidenceColumn($fieldName, $parentField)
            && static::containsLinkKeyword($combined)
        ) {
            return true;
        }

        if ($combined === '' || ! static::containsCertificateKeyword($combined)) {
            return false;
        }

        if (in_array($fieldType, ['url', 'file'], true)) {
            return true;
        }

        return static::containsLinkKeyword($combined);
    }

    private static function containsCertificateKeyword(string $combined): bool
    {
        return str_contains($combined, 'sertifikat')
            || str_contains($combined, 'sertifikasi')
            || str_contains($combined, 'piagam')
            || str_contains($combined, 'surat keputusan')
            || (bool) preg_match('/(^|[\s_\/-])sk($|[\s_\/-])/u', $combined);
    }

    private static function containsLinkKeyword(string $combined): bool
    {
        return str_contains($combined, 'link')
            || str_contains($combined, 'url')
            || str_contains($combined, 'tautan')
            || str_contains($combined, 'drive')
            || str_contains($combined, 'dokumen');
    }

    /**
     * @param  array<string, mixed>  $field
     * @param  array<string, mixed>  $definition
     */
    private static function isLikelyCertificateLinkKey(
        string $value,
        array $field,
        array $definition = []
    ): bool
    {
        $normalized = Str::lower(trim($value));

        if (static::isGenericPortfolioEvidenceColumn($normalized, $field)) {
            return true;
        }

        return $normalized !== ''
            && static::containsCertificateKeyword($normalized)
            && static::containsLinkKeyword(trim($normalized.' '.Str::lower(trim((string) ($definition['label'] ?? '')))));
    }

    /**
     * @param  array<string, mixed>|null  $parentField
     */
    private static function isGenericPortfolioEvidenceColumn(string $columnName, ?array $parentField = null): bool
    {
        if (! in_array($columnName, self::GENERIC_PORTFOLIO_LINK_COLUMNS, true)) {
            return false;
        }

        $parentFieldName = Str::lower(trim((string) ($parentField['nama_field'] ?? '')));

        return in_array($parentFieldName, self::PORTFOLIO_EVIDENCE_REPEATER_FIELDS, true);
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
