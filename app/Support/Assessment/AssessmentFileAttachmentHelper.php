<?php

namespace App\Support\Assessment;

use Illuminate\Support\Str;

class AssessmentFileAttachmentHelper
{
    /**
     * @param  array<string, mixed>  $snapshot
     * @param  array<int, array<string, mixed>>  $answerLookup
     * @return array<int, array<string, mixed>>
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

                                if ($fieldId <= 0 || ($field['tipe_field'] ?? null) !== 'file') {
                                    return [];
                                }

                                $answer = $answerLookup[$fieldId] ?? null;

                                if (! is_array($answer) || ! AssessmentAnswerViewHelper::hasAnswer($field, $answer)) {
                                    return [];
                                }

                                return static::collectFileAnswer($assessmentTitle, $formTitle, $field, $answer);
                            });
                    });
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $field
     * @param  array<string, mixed>  $answer
     * @return array<int, array<string, mixed>>
     */
    private static function collectFileAnswer(
        string $assessmentTitle,
        string $formTitle,
        array $field,
        array $answer
    ): array {
        $payload = is_array(data_get($answer, 'payload')) ? data_get($answer, 'payload') : [];
        $url = static::resolveFileUrl($answer);
        $fileName = static::resolveFileName($field, $answer, $url);
        $mimeType = trim((string) data_get($payload, 'mime_type'));
        $inputMode = static::resolveInputMode($field, $payload);
        $source = filled(data_get($answer, 'file_path')) ? 'uploaded_file' : ($inputMode === 'link' ? 'external_link' : 'file');
        $preview = AssessmentAttachmentPreviewHelper::resolve($url, $fileName, $mimeType, $source);

        return [[
            'assessment_title' => $assessmentTitle !== '' ? $assessmentTitle : 'Assessment',
            'form_title' => $formTitle !== '' ? $formTitle : 'Form',
            'field_label' => static::resolveDefinitionLabel($field, 'File Assessment'),
            'description' => trim((string) ($field['deskripsi'] ?? '')) ?: null,
            'url' => $url,
            'file_name' => $fileName,
            'mime_type' => $mimeType,
            'size' => data_get($payload, 'size'),
            'size_text' => AssessmentAttachmentPreviewHelper::formatBytes(data_get($payload, 'size')),
            'input_mode' => $inputMode,
            'source' => $source,
            'answered_at' => data_get($answer, 'answered_at'),
        ] + $preview];
    }

    private static function resolveFileUrl(array $answer): string
    {
        return collect([
            data_get($answer, 'payload.link_url'),
            data_get($answer, 'file_url'),
            data_get($answer, 'text'),
        ])
            ->map(fn ($value) => trim((string) $value))
            ->first(fn (string $value) => $value !== '' && AssessmentUrlValidationHelper::isValidHttpUrl($value), '')
            ?: trim((string) data_get($answer, 'file_url'));
    }

    private static function resolveFileName(array $field, array $answer, string $url): string
    {
        $fileName = trim((string) (
            data_get($answer, 'payload.original_name')
            ?: data_get($answer, 'text')
            ?: basename((string) parse_url($url, PHP_URL_PATH))
        ));

        if ($fileName !== '' && ! AssessmentUrlValidationHelper::isValidHttpUrl($fileName)) {
            return $fileName;
        }

        return static::resolveDefinitionLabel($field, 'File Assessment');
    }

    private static function resolveInputMode(array $field, array $payload): string
    {
        $inputMode = trim((string) (
            data_get($payload, 'input_mode')
            ?: data_get($field, 'opsi_field.input_mode')
            ?: 'file'
        ));

        return in_array($inputMode, ['file', 'link'], true) ? $inputMode : 'file';
    }

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
}
