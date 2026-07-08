<?php

namespace App\Services\Assessment;

use App\Models\AssessmentAttempt;
use App\Models\AssessmentAttemptAnswer;
use App\Support\Assessment\ChoiceOptionNormalizer;
use App\Support\Assessment\TextareaWordLimit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssessmentAttemptService
{
    public function __construct(
        private readonly AssessmentScoringService $scoringService,
        private readonly AssessmentAutoScoringService $autoScoringService
    ) {}

    public function saveSnapshot(
        AssessmentAttempt $attempt,
        array $answers,
        array $files,
        array $fieldIds,
        array $flaggedFieldIds = [],
        array $clientSnapshotBucket = []
    ): AssessmentAttempt {
        if ($attempt->status === 'submitted') {
            return $this->loadAttemptRelations($attempt);
        }

        $snapshot = $attempt->structure_snapshot ?? [];
        $allFields = $this->flattenFields($snapshot);
        $availableFieldIds = $this->extractFieldIds($allFields);
        $this->syncFlaggedFieldIds($attempt, $availableFieldIds, $flaggedFieldIds);
        $processedFieldIds = $this->normalizeFieldIds($fieldIds);
        $fields = $this->filterFieldsByIds($allFields, $processedFieldIds);
        $normalizedClientSnapshotBucket = $this->normalizeClientSnapshotBucket($clientSnapshotBucket);

        if ($fields === []) {
            if ($normalizedClientSnapshotBucket !== []) {
                $this->persistClientSnapshotBucket($attempt, $normalizedClientSnapshotBucket, now());
            }

            return $this->loadAttemptRelations($attempt->fresh());
        }

        $existingAnswers = $attempt->answers()
            ->whereIn('assessment_form_field_id', $processedFieldIds)
            ->get()
            ->keyBy('assessment_form_field_id');
        $normalizedAnswers = $this->validateAndNormalizeAnswers(
            $fields,
            $answers,
            $files,
            $existingAnswers,
            false
        );
        $savedAt = now();

        DB::transaction(function () use (
            $attempt,
            $normalizedAnswers,
            $processedFieldIds,
            $snapshot,
            $savedAt,
            $normalizedClientSnapshotBucket
        ) {
            $this->persistNormalizedAnswers(
                $attempt,
                $normalizedAnswers['answers'],
                $processedFieldIds,
                $normalizedAnswers['preserve_existing_answer_field_ids'],
                $savedAt
            );

            $freshAnswers = $attempt->answers()->get();
            $attempt->setRelation('answers', $freshAnswers);
            $summary = $this->buildSummaryFromSnapshot(
                $snapshot,
                $freshAnswers,
                $attempt->started_at ?: $savedAt,
                $savedAt
            );

            $updatedSnapshot = $this->mergeClientSnapshotBucket(
                $attempt->structure_snapshot ?? $snapshot,
                $normalizedClientSnapshotBucket,
                $savedAt
            );
            $attributes = [
                'status' => 'in_progress',
                'answered_questions' => (int) $summary['answered_questions'],
                'answered_required_questions' => (int) $summary['answered_required_questions'],
                'last_answered_at' => $savedAt,
            ];

            if ($updatedSnapshot !== ($attempt->structure_snapshot ?? [])) {
                $attributes['structure_snapshot'] = $updatedSnapshot;
            }

            $attempt->forceFill($attributes)->save();

            if (array_key_exists('structure_snapshot', $attributes)) {
                $attempt->structure_snapshot = $updatedSnapshot;
            }
        });

        return $this->loadAttemptRelations($attempt->fresh());
    }

    public function submit(
        AssessmentAttempt $attempt,
        array $answers,
        array $files,
        array $flaggedFieldIds = []
    ): AssessmentAttempt {
        if ($attempt->status === 'submitted') {
            return $this->loadAttemptRelations($attempt);
        }

        return $this->finalizeAttempt($attempt, $answers, $files, [
            'flagged_field_ids' => $flaggedFieldIds,
        ]);
    }

    public function submitExpired(
        AssessmentAttempt $attempt,
        array $answers = [],
        array $files = [],
        array $flaggedFieldIds = []
    ): AssessmentAttempt {
        if ($attempt->status === 'submitted') {
            return $this->loadAttemptRelations($attempt);
        }

        return $this->finalizeAttempt($attempt, $answers, $files, [
            'force_zero_for_unanswered' => true,
            'flagged_field_ids' => $flaggedFieldIds,
        ]);
    }

    public function submitDisqualified(
        AssessmentAttempt $attempt,
        array $answers = [],
        array $files = [],
        ?string $reason = null,
        array $flaggedFieldIds = []
    ): AssessmentAttempt {
        if ($attempt->status === 'submitted') {
            return $this->loadAttemptRelations($attempt);
        }

        $reason = trim((string) ($reason ?? ''));

        return $this->finalizeAttempt($attempt, $answers, $files, [
            'force_zero_for_unanswered' => true,
            'submission_mode' => 'security_disqualified',
            'submission_note' => $reason !== ''
                ? $reason
                : 'Assessment dihentikan oleh sistem guard karena terdeteksi pelanggaran aturan ujian.',
            'completion_mode' => null,
            'timed_out_at' => null,
            'disqualified_at' => now(),
            'disqualification_reason' => $reason !== ''
                ? $reason
                : 'Assessment dihentikan oleh sistem guard karena terdeteksi pelanggaran aturan ujian.',
            'flagged_field_ids' => $flaggedFieldIds,
        ]);
    }

    public function buildResultSummary(AssessmentAttempt $attempt): array
    {
        if ($attempt->result_summary) {
            return $attempt->result_summary;
        }

        return $this->buildSummaryFromSnapshot(
            $attempt->structure_snapshot ?? [],
            $attempt->answers,
            $attempt->started_at,
            $attempt->submitted_at
        );
    }

    public function buildScoringSummary(AssessmentAttempt $attempt): array
    {
        $currentVersion = (int) data_get($attempt->scoring_summary ?? [], 'summary_version', 0);

        if ($attempt->scoring_summary && $currentVersion === $this->scoringService->summaryVersion()) {
            return $attempt->scoring_summary;
        }

        $summary = $this->scoringService->buildSummary($attempt->loadMissing('answers'));

        if ($attempt->exists) {
            $attempt->forceFill([
                'scoring_summary' => $summary,
            ])->save();
        }

        return $summary;
    }

    public function refreshScoringSummary(AssessmentAttempt $attempt): array
    {
        $summary = $this->scoringService->buildSummary($attempt->loadMissing('answers'));

        if ($attempt->exists) {
            $attempt->forceFill([
                'scoring_summary' => $summary,
            ])->save();
        }

        return $summary;
    }

    public function buildAnswerLookup(AssessmentAttempt $attempt): array
    {
        return $attempt->answers
            ->mapWithKeys(function (AssessmentAttemptAnswer $answer) {
                return [
                    $answer->assessment_form_field_id => [
                        'text' => $answer->answer_text,
                        'payload' => $answer->answer_payload ?? [],
                        'file_path' => $answer->answer_file_path,
                        'file_url' => $answer->answer_file_path ? asset('storage/'.$answer->answer_file_path) : null,
                        'rows' => data_get($answer->answer_payload ?? [], 'rows', []),
                        'columns' => data_get($answer->answer_payload ?? [], 'columns', []),
                        'auto_score' => $answer->auto_score,
                        'auto_score_reason' => $answer->auto_score_reason,
                        'auto_score_metadata' => $answer->auto_score_metadata ?? [],
                        'auto_score_confidence' => data_get($answer->auto_score_metadata ?? [], 'confidence'),
                        'assessor_score' => $answer->assessor_score,
                        'assessor_notes' => $answer->assessor_notes,
                        'assessor_score_label' => $answer->assessor_score
                            ? \App\Enum\LevelKompetensi::tryFrom((int) $answer->assessor_score)?->label()
                            : null,
                        'final_score' => $answer->auto_score,
                        'final_score_label' => \App\Enum\LevelKompetensi::fromScore((float) $answer->auto_score)?->label(),
                        'answered_at' => $answer->answered_at?->format('d M Y H:i'),
                    ],
                ];
            })
            ->all();
    }

    private function finalizeAttempt(
        AssessmentAttempt $attempt,
        array $answers,
        array $files,
        array $options = []
    ): AssessmentAttempt {
        $forceZeroForUnanswered = (bool) ($options['force_zero_for_unanswered'] ?? false);
        $snapshot = $attempt->structure_snapshot ?? [];
        $fields = $this->flattenFields($snapshot);
        $processedFieldIds = $this->extractFieldIds($fields);
        $flaggedFieldIds = $this->syncFlaggedFieldIds(
            $attempt,
            $processedFieldIds,
            $options['flagged_field_ids'] ?? []
        );
        $existingAnswers = $attempt->answers()
            ->whereIn('assessment_form_field_id', $processedFieldIds)
            ->get()
            ->keyBy('assessment_form_field_id');
        $normalizedAnswers = $this->validateAndNormalizeAnswers(
            $fields,
            $answers,
            $files,
            $existingAnswers,
            ! $forceZeroForUnanswered,
            $flaggedFieldIds,
            ! $forceZeroForUnanswered
        );
        $submittedAt = now();
        $submissionMode = (string) ($options['submission_mode'] ?? ($forceZeroForUnanswered ? 'deadline_auto' : 'manual'));
        $submissionNote = $options['submission_note']
            ?? ($forceZeroForUnanswered
                ? 'Batas waktu berakhir. Jawaban terakhir yang tersimpan diproses otomatis dan soal kosong diberi skor 0.'
                : 'Jawaban dikirim langsung oleh peserta.');
        $completionMode = array_key_exists('completion_mode', $options)
            ? $options['completion_mode']
            : ($forceZeroForUnanswered ? 'timeout' : 'manual');
        $timedOutAt = array_key_exists('timed_out_at', $options)
            ? $options['timed_out_at']
            : ($forceZeroForUnanswered ? $submittedAt : null);
        $disqualifiedAt = array_key_exists('disqualified_at', $options)
            ? $options['disqualified_at']
            : null;
        $disqualificationReason = array_key_exists('disqualification_reason', $options)
            ? $options['disqualification_reason']
            : null;

        DB::transaction(function () use (
            $attempt,
            $snapshot,
            $fields,
            $processedFieldIds,
            $normalizedAnswers,
            $submittedAt,
            $forceZeroForUnanswered,
            $submissionMode,
            $submissionNote,
            $completionMode,
            $timedOutAt,
            $disqualifiedAt,
            $disqualificationReason
        ) {
            $this->persistNormalizedAnswers(
                $attempt,
                $normalizedAnswers['answers'],
                $processedFieldIds,
                $normalizedAnswers['preserve_existing_answer_field_ids'],
                $submittedAt
            );

            $freshAnswers = $attempt->answers()->get();
            $attempt->setRelation('answers', $freshAnswers);
            $this->autoScoringService->scoreAttempt($attempt);

            if ($forceZeroForUnanswered) {
                $this->stampZeroScoreForUnansweredFields($attempt, $fields, $submittedAt);
            }

            $freshAnswers = $attempt->answers()->get();
            $attempt->setRelation('answers', $freshAnswers);
            $summary = $this->buildSummaryFromSnapshot(
                $snapshot,
                $freshAnswers,
                $attempt->started_at ?: $submittedAt,
                $submittedAt
            );

            $summary['submission_mode'] = $submissionMode;
            $summary['submission_note'] = $submissionNote;
            $summary['auto_submitted_at'] = $forceZeroForUnanswered ? $submittedAt->toIso8601String() : null;
            $summary['disqualified_at'] = $disqualifiedAt?->toIso8601String();
            $summary['disqualification_reason'] = $disqualificationReason;

            $scoringSummary = $this->scoringService->buildSummary($attempt);

            $attempt->forceFill([
                'status' => 'submitted',
                'result_summary' => $summary,
                'scoring_summary' => $scoringSummary,
                'answered_questions' => (int) $summary['answered_questions'],
                'answered_required_questions' => (int) $summary['answered_required_questions'],
                'deadline_at' => $attempt->deadline_at,
                'submitted_at' => $submittedAt,
                'completion_mode' => $completionMode,
                'timed_out_at' => $timedOutAt,
                'last_answered_at' => $submittedAt,
                'disqualified_at' => $disqualifiedAt,
                'disqualification_reason' => $disqualificationReason,
            ])->save();

            $target = $attempt->target;

            if ($target) {
                $target->forceFill([
                    'status' => 'selesai',
                    'started_at' => $target->started_at ?: $attempt->started_at ?: $submittedAt,
                    'deadline_at' => $target->deadline_at ?: $attempt->deadline_at,
                    'submitted_at' => $submittedAt,
                    'completion_mode' => $completionMode,
                    'timed_out_at' => $timedOutAt,
                ])->save();
            }
        });

        return $this->loadAttemptRelations($attempt->fresh());
    }

    private function validateAndNormalizeAnswers(
        array $fields,
        array $answers,
        array $files,
        Collection $existingAnswers,
        bool $requireRequiredFields,
        array $flaggedFieldIds = [],
        bool $enforceFlaggedAnswers = false
    ): array {
        $messages = [];
        $normalized = [];
        $preserveExistingAnswerFieldIds = [];
        $normalizedFlaggedFieldIds = $this->normalizeFieldIds($flaggedFieldIds);

        foreach ($fields as $field) {
            $fieldId = (string) $field['id'];
            $fieldKey = 'answers.'.$fieldId;
            $fieldType = $field['tipe_field'];
            $fieldLabel = $field['label'];
            $isRequired = $requireRequiredFields && (bool) ($field['is_required'] ?? false);
            $requiresFlagAnswer = $enforceFlaggedAnswers
                && in_array((int) $field['id'], $normalizedFlaggedFieldIds, true);
            $mustBeAnswered = $isRequired || $requiresFlagAnswer;
            $uploadedFile = $files[$fieldId] ?? null;
            $existingAnswer = $existingAnswers->get((int) $fieldId);

            if ($fieldType === 'file') {
                if ($mustBeAnswered && ! $uploadedFile && ! filled($existingAnswer?->answer_file_path)) {
                    $messages[$fieldKey] = "File untuk pertanyaan {$fieldLabel} wajib diunggah.";

                    continue;
                }

                if (! $uploadedFile) {
                    if (filled($existingAnswer?->answer_file_path)) {
                        $preserveExistingAnswerFieldIds[] = (int) $fieldId;
                    }

                    continue;
                }

                if (! $uploadedFile instanceof UploadedFile || ! $uploadedFile->isValid()) {
                    $messages[$fieldKey] = "File untuk pertanyaan {$fieldLabel} tidak valid.";

                    continue;
                }

                if ($uploadedFile->getSize() > 5 * 1024 * 1024) {
                    $messages[$fieldKey] = "File untuk pertanyaan {$fieldLabel} maksimal 5 MB.";

                    continue;
                }

                $normalized[(int) $fieldId] = [
                    'assessment_id' => $field['assessment_id'],
                    'assessment_form_id' => $field['assessment_form_id'],
                    'answer_text' => $uploadedFile->getClientOriginalName(),
                    'answer_payload' => [
                        'type' => 'file',
                        'original_name' => $uploadedFile->getClientOriginalName(),
                    ],
                    'answer_file_path' => null,
                    'uploaded_file' => $uploadedFile,
                ];

                continue;
            }

            if ($fieldType === 'checkbox') {
                $selectedValues = collect(Arr::wrap($answers[$fieldId] ?? []))
                    ->map(fn ($value) => trim((string) $value))
                    ->filter()
                    ->values()
                    ->all();

                if ($mustBeAnswered && $selectedValues === []) {
                    $messages[$fieldKey] = "Minimal pilih satu jawaban untuk pertanyaan {$fieldLabel}.";

                    continue;
                }

                if ($selectedValues === []) {
                    continue;
                }

                $normalizedOptions = ChoiceOptionNormalizer::normalizeMany($field['opsi_field'] ?? []);
                $allowedValues = collect($normalizedOptions)
                    ->flatMap(fn (array $option) => $option['aliases'] ?? [])
                    ->map(fn ($value) => (string) $value)
                    ->unique()
                    ->all();
                $invalidValues = array_diff($selectedValues, $allowedValues);

                if ($invalidValues !== []) {
                    $messages[$fieldKey] = "Ada pilihan yang tidak valid pada pertanyaan {$fieldLabel}.";

                    continue;
                }

                $selectedOptions = collect($normalizedOptions)
                    ->filter(function (array $option) use ($selectedValues) {
                        return collect($selectedValues)
                            ->contains(fn ($selectedValue) => in_array((string) $selectedValue, $option['aliases'] ?? [], true));
                    })
                    ->values();

                $normalized[(int) $fieldId] = [
                    'assessment_id' => $field['assessment_id'],
                    'assessment_form_id' => $field['assessment_form_id'],
                    'answer_text' => implode(', ', $selectedValues),
                    'answer_payload' => [
                        'type' => 'checkbox',
                        'values' => $selectedValues,
                        'selected_options' => $selectedOptions->map(fn (array $option) => array_filter([
                            'label' => $option['label'] ?? null,
                            'value' => $option['value'] ?? null,
                            'score' => $option['score'] ?? null,
                        ], static fn ($value) => $value !== null && $value !== ''))->all(),
                    ],
                    'answer_file_path' => null,
                ];

                continue;
            }

            if ($fieldType === 'repeater') {
                $normalizedRepeater = $this->normalizeRepeaterAnswer(
                    $field,
                    $answers[$fieldId] ?? null,
                    $requireRequiredFields,
                    $requiresFlagAnswer
                );

                if ($normalizedRepeater['message']) {
                    $messages[$fieldKey] = $normalizedRepeater['message'];

                    continue;
                }

                if ($normalizedRepeater['rows'] === []) {
                    continue;
                }

                $normalized[(int) $fieldId] = [
                    'assessment_id' => $field['assessment_id'],
                    'assessment_form_id' => $field['assessment_form_id'],
                    'answer_text' => count($normalizedRepeater['rows']).' entri',
                    'answer_payload' => [
                        'type' => 'repeater',
                        'rows' => $normalizedRepeater['rows'],
                        'columns' => $normalizedRepeater['columns'],
                        'row_count' => count($normalizedRepeater['rows']),
                    ],
                    'answer_file_path' => null,
                ];

                continue;
            }

            $value = $answers[$fieldId] ?? null;
            $textValue = is_array($value) ? '' : trim((string) ($value ?? ''));
            $matchedOption = null;

            if ($mustBeAnswered && $textValue === '') {
                $messages[$fieldKey] = "Jawaban untuk pertanyaan {$fieldLabel} wajib diisi.";

                continue;
            }

            if ($textValue === '') {
                continue;
            }

            if ($fieldType === 'textarea') {
                $wordCount = TextareaWordLimit::count($textValue);

                if ($wordCount < TextareaWordLimit::minWords()) {
                    $messages[$fieldKey] = "Jawaban untuk pertanyaan {$fieldLabel} minimal "
                        .TextareaWordLimit::minWords()." kata. Saat ini {$wordCount} kata.";

                    continue;
                }

                if ($wordCount > TextareaWordLimit::maxWords()) {
                    $messages[$fieldKey] = "Jawaban untuk pertanyaan {$fieldLabel} maksimal "
                        .TextareaWordLimit::maxWords()." kata. Saat ini {$wordCount} kata.";

                    continue;
                }
            }

            if ($fieldType === 'email' && ! filter_var($textValue, FILTER_VALIDATE_EMAIL)) {
                $messages[$fieldKey] = "Format email pada pertanyaan {$fieldLabel} tidak valid.";

                continue;
            }

            if ($fieldType === 'number' && ! is_numeric($textValue)) {
                $messages[$fieldKey] = "Jawaban pada pertanyaan {$fieldLabel} harus berupa angka.";

                continue;
            }

            if ($fieldType === 'date') {
                try {
                    $date = Carbon::createFromFormat('Y-m-d', $textValue);
                } catch (\Throwable $exception) {
                    $date = null;
                }

                if (! $date || $date->format('Y-m-d') !== $textValue) {
                    $messages[$fieldKey] = "Format tanggal pada pertanyaan {$fieldLabel} tidak valid.";

                    continue;
                }
            }

            if ($fieldType === 'radio') {
                $matchedOption = collect(ChoiceOptionNormalizer::normalizeMany($field['opsi_field'] ?? []))
                    ->first(function (array $option) use ($textValue) {
                        $aliases = collect($option['aliases'] ?? [])
                            ->map(fn ($value) => trim((string) $value))
                            ->filter(fn ($value) => $value !== '')
                            ->all();

                        return in_array($textValue, $aliases, true);
                    });

                if (! is_array($matchedOption)) {
                    $messages[$fieldKey] = "Pilihan jawaban pada pertanyaan {$fieldLabel} tidak valid.";

                    continue;
                }

                $textValue = trim((string) ($matchedOption['value'] ?? $textValue));

                $normalized[(int) $fieldId] = [
                    'assessment_id' => $field['assessment_id'],
                    'assessment_form_id' => $field['assessment_form_id'],
                    'answer_text' => $textValue,
                    'answer_payload' => array_filter([
                        'type' => 'radio',
                        'value' => $textValue,
                        'label' => trim((string) ($matchedOption['label'] ?? '')) ?: null,
                        'score' => is_numeric($matchedOption['score'] ?? null) ? (float) $matchedOption['score'] : null,
                        'level_kompetensi' => $matchedOption['level_kompetensi'] ?? null,
                        'level_kompetensi_label' => $matchedOption['level_kompetensi_label'] ?? null,
                    ], static fn ($value) => $value !== null && $value !== ''),
                    'answer_file_path' => null,
                ];

                continue;
            }

            if ($fieldType === 'select') {
                $matchedOption = collect(ChoiceOptionNormalizer::normalizeMany($field['opsi_field'] ?? []))
                    ->first(fn (array $option) => in_array($textValue, $option['aliases'] ?? [], true));

                if (! is_array($matchedOption)) {
                    $messages[$fieldKey] = "Pilihan jawaban pada pertanyaan {$fieldLabel} tidak valid.";

                    continue;
                }

                $textValue = trim((string) ($matchedOption['value'] ?? $textValue));
            }

            $normalized[(int) $fieldId] = [
                'assessment_id' => $field['assessment_id'],
                'assessment_form_id' => $field['assessment_form_id'],
                'answer_text' => $textValue,
                'answer_payload' => array_filter([
                    'type' => $fieldType,
                    'value' => $textValue,
                    'label' => is_array($matchedOption ?? null)
                        ? (trim((string) ($matchedOption['label'] ?? '')) ?: null)
                        : null,
                    'score' => is_array($matchedOption ?? null) && is_numeric($matchedOption['score'] ?? null)
                        ? (float) $matchedOption['score']
                        : null,
                ], static fn ($value) => $value !== null && $value !== ''),
                'answer_file_path' => null,
            ];
        }

        if ($messages !== []) {
            throw ValidationException::withMessages($messages);
        }

        return [
            'answers' => $normalized,
            'preserve_existing_answer_field_ids' => array_values(array_unique($preserveExistingAnswerFieldIds)),
        ];
    }

    private function normalizeRepeaterAnswer(
        array $field,
        mixed $value,
        bool $requireRequiredFields,
        bool $mustHaveAnyRow = false
    ): array {
        $config = is_array($field['opsi_field'] ?? null) ? $field['opsi_field'] : [];
        $columns = collect($config['columns'] ?? [])
            ->filter(fn ($column) => is_array($column))
            ->map(function (array $column) {
                $columnName = trim((string) ($column['nama_field'] ?? ''));
                $columnLabel = trim((string) ($column['label'] ?? ''));

                return [
                    'label' => $columnLabel !== '' ? $columnLabel : $columnName,
                    'nama_field' => $columnName,
                    'tipe_field' => trim((string) ($column['tipe_field'] ?? 'text')) ?: 'text',
                    'opsi_field' => is_array($column['opsi_field'] ?? null) ? $column['opsi_field'] : [],
                    'placeholder' => trim((string) ($column['placeholder'] ?? '')),
                    'is_required' => (bool) ($column['is_required'] ?? false),
                ];
            })
            ->filter(fn ($column) => $column['nama_field'] !== '')
            ->values()
            ->all();

        if ($columns === []) {
            return [
                'rows' => [],
                'columns' => [],
                'message' => "Konfigurasi tabel untuk pertanyaan {$field['label']} belum valid.",
            ];
        }

        $rows = collect(is_array($value) ? $value : [])
            ->filter(fn ($row) => is_array($row))
            ->values();
        $normalizedRows = [];
        $enforceCompleteness = $requireRequiredFields || $mustHaveAnyRow;
        $minRows = $enforceCompleteness ? max((int) ($config['min_rows'] ?? 0), 0) : 0;
        $maxRows = max((int) ($config['max_rows'] ?? 0), 0);

        foreach ($rows as $rowIndex => $row) {
            $normalizedRow = [];
            $hasContent = false;

            foreach ($columns as $column) {
                $columnName = $column['nama_field'];
                $columnValue = is_array($row[$columnName] ?? null)
                    ? ''
                    : trim((string) ($row[$columnName] ?? ''));

                if ($columnValue !== '') {
                    $hasContent = true;
                }

                if ($columnValue !== '') {
                    if ($column['tipe_field'] === 'textarea') {
                        $wordCount = TextareaWordLimit::count($columnValue);

                        if ($wordCount < TextareaWordLimit::minWords()) {
                            return [
                                'rows' => [],
                                'columns' => $columns,
                                'message' => "Kolom {$column['label']} pada baris ".($rowIndex + 1)
                                    ." untuk pertanyaan {$field['label']} minimal "
                                    .TextareaWordLimit::minWords()." kata. Saat ini {$wordCount} kata.",
                            ];
                        }

                        if ($wordCount > TextareaWordLimit::maxWords()) {
                            return [
                                'rows' => [],
                                'columns' => $columns,
                                'message' => "Kolom {$column['label']} pada baris ".($rowIndex + 1)
                                    ." untuk pertanyaan {$field['label']} maksimal "
                                    .TextareaWordLimit::maxWords()." kata. Saat ini {$wordCount} kata.",
                            ];
                        }
                    }

                    if ($column['tipe_field'] === 'email' && ! filter_var($columnValue, FILTER_VALIDATE_EMAIL)) {
                        return [
                            'rows' => [],
                            'columns' => $columns,
                            'message' => "Kolom {$column['label']} pada baris ".($rowIndex + 1)." untuk pertanyaan {$field['label']} tidak valid.",
                        ];
                    }

                    if ($column['tipe_field'] === 'number' && ! is_numeric($columnValue)) {
                        return [
                            'rows' => [],
                            'columns' => $columns,
                            'message' => "Kolom {$column['label']} pada baris ".($rowIndex + 1)." untuk pertanyaan {$field['label']} harus berupa angka.",
                        ];
                    }

                    if ($column['tipe_field'] === 'date') {
                        try {
                            $date = Carbon::createFromFormat('Y-m-d', $columnValue);
                        } catch (\Throwable $exception) {
                            $date = null;
                        }

                        if (! $date || $date->format('Y-m-d') !== $columnValue) {
                            return [
                                'rows' => [],
                                'columns' => $columns,
                                'message' => "Kolom {$column['label']} pada baris ".($rowIndex + 1)." untuk pertanyaan {$field['label']} tidak valid.",
                            ];
                        }
                    }

                    if ($column['tipe_field'] === 'select') {
                        $allowedValues = collect($column['opsi_field'] ?? [])
                            ->map(fn ($optionValue) => (string) $optionValue)
                            ->all();

                        if ($allowedValues !== [] && ! in_array($columnValue, $allowedValues, true)) {
                            return [
                                'rows' => [],
                                'columns' => $columns,
                                'message' => "Pilihan {$column['label']} pada baris ".($rowIndex + 1)." untuk pertanyaan {$field['label']} tidak valid.",
                            ];
                        }
                    }
                }

                $normalizedRow[$columnName] = $columnValue;
            }

            if (! $hasContent) {
                continue;
            }

            foreach ($columns as $column) {
                if (
                    $enforceCompleteness &&
                    $column['is_required'] &&
                    ($normalizedRow[$column['nama_field']] ?? '') === ''
                ) {
                    return [
                        'rows' => [],
                        'columns' => $columns,
                        'message' => "Kolom {$column['label']} pada baris ".($rowIndex + 1)." untuk pertanyaan {$field['label']} wajib diisi.",
                    ];
                }
            }

            $normalizedRows[] = $normalizedRow;
        }

        if (($mustHaveAnyRow || ($requireRequiredFields && (bool) ($field['is_required'] ?? false))) && $normalizedRows === []) {
            return [
                'rows' => [],
                'columns' => $columns,
                'message' => "Minimal isi satu baris pada pertanyaan {$field['label']}.",
            ];
        }

        if ($minRows > 0 && count($normalizedRows) < $minRows) {
            return [
                'rows' => [],
                'columns' => $columns,
                'message' => "Pertanyaan {$field['label']} minimal harus memiliki {$minRows} baris terisi.",
            ];
        }

        if ($maxRows > 0 && count($normalizedRows) > $maxRows) {
            return [
                'rows' => [],
                'columns' => $columns,
                'message' => "Pertanyaan {$field['label']} maksimal hanya boleh memiliki {$maxRows} baris terisi.",
            ];
        }

        return [
            'rows' => $normalizedRows,
            'columns' => $columns,
            'message' => null,
        ];
    }

    private function persistNormalizedAnswers(
        AssessmentAttempt $attempt,
        array $normalizedAnswers,
        array $processedFieldIds,
        array $preservedFieldIds,
        Carbon $answeredAt
    ): void {
        $fieldIdsToDelete = array_values(array_diff(
            $processedFieldIds,
            array_keys($normalizedAnswers),
            $preservedFieldIds
        ));

        if ($fieldIdsToDelete !== []) {
            $attempt->answers()
                ->whereIn('assessment_form_field_id', $fieldIdsToDelete)
                ->delete();
        }

        foreach ($normalizedAnswers as $fieldId => $normalizedAnswer) {
            $persistedAnswer = $this->prepareAnswerForPersistence($attempt, $normalizedAnswer);

            AssessmentAttemptAnswer::updateOrCreate(
                [
                    'assessment_attempt_id' => $attempt->id,
                    'assessment_form_field_id' => $fieldId,
                ],
                [
                    'assessment_id' => $persistedAnswer['assessment_id'],
                    'assessment_form_id' => $persistedAnswer['assessment_form_id'],
                    'answer_text' => $persistedAnswer['answer_text'],
                    'answer_payload' => $persistedAnswer['answer_payload'],
                    'answer_file_path' => $persistedAnswer['answer_file_path'],
                    'answered_at' => $answeredAt,
                    'auto_score' => null,
                    'auto_score_reason' => null,
                    'auto_score_metadata' => null,
                    'auto_scored_at' => null,
                    'assessor_score' => null,
                    'assessor_notes' => null,
                    'assessor_user_id' => null,
                    'assessor_scored_at' => null,
                ]
            );
        }
    }

    private function stampZeroScoreForUnansweredFields(
        AssessmentAttempt $attempt,
        array $fields,
        Carbon $submittedAt
    ): void {
        $answersByFieldId = $attempt->answers()->get()->keyBy('assessment_form_field_id');

        foreach ($fields as $field) {
            $fieldId = (int) ($field['id'] ?? 0);

            if ($fieldId <= 0) {
                continue;
            }

            $existingAnswer = $answersByFieldId->get($fieldId);

            if ($this->answerHasContent($existingAnswer)) {
                continue;
            }

            AssessmentAttemptAnswer::updateOrCreate(
                [
                    'assessment_attempt_id' => $attempt->id,
                    'assessment_form_field_id' => $fieldId,
                ],
                [
                    'assessment_id' => $field['assessment_id'] ?? null,
                    'assessment_form_id' => $field['assessment_form_id'] ?? null,
                    'answer_text' => null,
                    'answer_payload' => [
                        'type' => $field['tipe_field'] ?? 'text',
                        'forced_zero_for_unanswered' => true,
                        'submission_mode' => 'deadline_auto',
                    ],
                    'answer_file_path' => null,
                    'answered_at' => null,
                    'auto_score' => 0,
                    'auto_score_reason' => 'Pertanyaan tidak dijawab hingga batas waktu berakhir.',
                    'auto_score_metadata' => [
                        'confidence' => 1,
                        'source' => 'deadline_auto_zero',
                        'requires_manual_review' => false,
                        'forced_zero_for_unanswered' => true,
                    ],
                    'auto_scored_at' => $submittedAt,
                    'assessor_score' => null,
                    'assessor_notes' => null,
                    'assessor_user_id' => null,
                    'assessor_scored_at' => null,
                ]
            );
        }
    }

    private function prepareAnswerForPersistence(AssessmentAttempt $attempt, array $normalizedAnswer): array
    {
        $uploadedFile = $normalizedAnswer['uploaded_file'] ?? null;

        unset($normalizedAnswer['uploaded_file']);

        if ($uploadedFile instanceof UploadedFile) {
            $storedPath = $uploadedFile->store('assessment/attempts/'.$attempt->id, 'public');

            $normalizedAnswer['answer_payload']['path'] = $storedPath;
            $normalizedAnswer['answer_file_path'] = $storedPath;
        }

        return $normalizedAnswer;
    }

    private function buildSummaryFromSnapshot(
        array $snapshot,
        Collection $answers,
        ?Carbon $startedAt,
        ?Carbon $submittedAt
    ): array {
        $answerMap = $answers->keyBy('assessment_form_field_id');
        $assessmentBreakdown = [];
        $totalQuestions = 0;
        $requiredQuestions = 0;
        $answeredQuestions = 0;
        $answeredRequiredQuestions = 0;

        foreach ($snapshot['assessments'] ?? [] as $assessment) {
            $assessmentTotal = 0;
            $assessmentRequired = 0;
            $assessmentAnswered = 0;
            $assessmentAnsweredRequired = 0;
            $forms = [];

            foreach ($assessment['forms'] ?? [] as $form) {
                $formTotal = 0;
                $formRequired = 0;
                $formAnswered = 0;
                $formAnsweredRequired = 0;

                foreach ($form['fields'] ?? [] as $field) {
                    $formTotal++;
                    $assessmentTotal++;
                    $totalQuestions++;

                    $isRequired = (bool) ($field['is_required'] ?? false);

                    if ($isRequired) {
                        $formRequired++;
                        $assessmentRequired++;
                        $requiredQuestions++;
                    }

                    $answer = $answerMap->get($field['id']);

                    if ($this->answerHasContent($answer)) {
                        $formAnswered++;
                        $assessmentAnswered++;
                        $answeredQuestions++;

                        if ($isRequired) {
                            $formAnsweredRequired++;
                            $assessmentAnsweredRequired++;
                            $answeredRequiredQuestions++;
                        }
                    }
                }

                $forms[] = [
                    'id' => $form['id'],
                    'judul_form' => $form['judul_form'],
                    'kode_form' => $form['kode_form'],
                    'total_questions' => $formTotal,
                    'required_questions' => $formRequired,
                    'answered_questions' => $formAnswered,
                    'answered_required_questions' => $formAnsweredRequired,
                ];
            }

            $assessmentBreakdown[] = [
                'id' => $assessment['id'],
                'kode_assessment' => $assessment['kode_assessment'],
                'judul' => $assessment['judul'],
                'total_questions' => $assessmentTotal,
                'required_questions' => $assessmentRequired,
                'answered_questions' => $assessmentAnswered,
                'answered_required_questions' => $assessmentAnsweredRequired,
                'forms' => $forms,
            ];
        }

        $completionPercentage = $totalQuestions > 0
            ? (int) round(($answeredQuestions / $totalQuestions) * 100)
            : 0;
        $durationMinutes = ($startedAt && $submittedAt)
            ? $startedAt->diffInMinutes($submittedAt)
            : 0;

        return [
            'total_questions' => $totalQuestions,
            'required_questions' => $requiredQuestions,
            'answered_questions' => $answeredQuestions,
            'answered_required_questions' => $answeredRequiredQuestions,
            'completion_percentage' => $completionPercentage,
            'duration_minutes' => $durationMinutes,
            'submitted_at' => optional($submittedAt)->toIso8601String(),
            'assessment_breakdown' => $assessmentBreakdown,
        ];
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

    private function filterFieldsByIds(array $fields, array $fieldIds): array
    {
        if ($fieldIds === []) {
            return [];
        }

        return collect($fields)
            ->filter(fn ($field) => in_array((int) ($field['id'] ?? 0), $fieldIds, true))
            ->values()
            ->all();
    }

    private function syncFlaggedFieldIds(
        AssessmentAttempt $attempt,
        array $availableFieldIds,
        array $flaggedFieldIds
    ): array {
        $normalizedRequestedFlaggedIds = $this->normalizeFieldIds($flaggedFieldIds);
        $normalizedFlaggedIds = collect($availableFieldIds)
            ->filter(function (int $fieldId) use ($normalizedRequestedFlaggedIds) {
                return in_array($fieldId, $normalizedRequestedFlaggedIds, true);
            })
            ->values()
            ->all();

        $snapshot = $attempt->structure_snapshot ?? [];
        $currentFlaggedIds = $this->normalizeFieldIds(data_get($snapshot, 'meta.flagged_field_ids', []));

        if ($currentFlaggedIds === $normalizedFlaggedIds) {
            return $normalizedFlaggedIds;
        }

        data_set($snapshot, 'meta.flagged_field_ids', $normalizedFlaggedIds);

        $attempt->forceFill([
            'structure_snapshot' => $snapshot,
        ])->save();

        $attempt->structure_snapshot = $snapshot;

        return $normalizedFlaggedIds;
    }

    private function persistClientSnapshotBucket(
        AssessmentAttempt $attempt,
        array $clientSnapshotBucket,
        Carbon $savedAt
    ): void {
        $currentSnapshot = $attempt->structure_snapshot ?? [];
        $updatedSnapshot = $this->mergeClientSnapshotBucket($currentSnapshot, $clientSnapshotBucket, $savedAt);

        if ($updatedSnapshot === $currentSnapshot) {
            return;
        }

        $attempt->forceFill([
            'structure_snapshot' => $updatedSnapshot,
        ])->save();

        $attempt->structure_snapshot = $updatedSnapshot;
    }

    private function mergeClientSnapshotBucket(
        array $snapshot,
        array $clientSnapshotBucket,
        Carbon $savedAt
    ): array {
        if ($clientSnapshotBucket === []) {
            return $snapshot;
        }

        $autosaveMeta = is_array(data_get($snapshot, 'meta.autosave', []))
            ? data_get($snapshot, 'meta.autosave', [])
            : [];
        $existingTraceLog = collect($autosaveMeta['trace_log'] ?? [])
            ->filter(fn ($entry) => is_array($entry))
            ->values();
        $trace = is_array($clientSnapshotBucket['trace'] ?? null) ? $clientSnapshotBucket['trace'] : [];
        $mergedTraceLog = $existingTraceLog
            ->concat($trace)
            ->slice(-90)
            ->values()
            ->all();

        $autosaveMeta = array_merge($autosaveMeta, [
            'last_saved_at' => $savedAt->toIso8601String(),
            'last_flush_reason' => $clientSnapshotBucket['flush_reason'] ?? 'autosave_threshold_reached',
            'last_threshold' => (int) ($clientSnapshotBucket['threshold'] ?? 3),
            'last_dirty_field_ids' => $clientSnapshotBucket['dirty_field_ids'] ?? [],
            'last_dirty_field_count' => count($clientSnapshotBucket['dirty_field_ids'] ?? []),
            'last_form_data_field_ids' => $clientSnapshotBucket['form_data_field_ids'] ?? [],
            'last_form_data_field_count' => count($clientSnapshotBucket['form_data_field_ids'] ?? []),
            'last_flagged_dirty' => (bool) ($clientSnapshotBucket['flagged_dirty'] ?? false),
            'last_bucket_started_at' => $clientSnapshotBucket['started_at'] ?? null,
            'last_trace' => $trace,
            'last_trace_count' => count($trace),
            'trace_log' => $mergedTraceLog,
        ]);

        data_set($snapshot, 'meta.autosave', $autosaveMeta);

        return $snapshot;
    }

    private function normalizeClientSnapshotBucket(array $clientSnapshotBucket): array
    {
        $trace = collect($clientSnapshotBucket['trace'] ?? [])
            ->filter(fn ($entry) => is_array($entry))
            ->map(fn (array $entry) => $this->normalizeClientSnapshotTraceEntry($entry))
            ->filter(fn (array $entry) => $entry !== [])
            ->slice(-30)
            ->values()
            ->all();
        $dirtyFieldIds = $this->normalizeFieldIds($clientSnapshotBucket['dirty_field_ids'] ?? []);
        $formDataFieldIds = $this->normalizeFieldIds($clientSnapshotBucket['form_data_field_ids'] ?? []);
        $flaggedDirty = (bool) ($clientSnapshotBucket['flagged_dirty'] ?? false);

        if ($trace === [] && $dirtyFieldIds === [] && $formDataFieldIds === [] && ! $flaggedDirty) {
            return [];
        }

        $flushReason = trim((string) ($clientSnapshotBucket['flush_reason'] ?? 'autosave_threshold_reached'));
        $startedAt = trim((string) ($clientSnapshotBucket['started_at'] ?? ''));
        $threshold = max(1, (int) ($clientSnapshotBucket['threshold'] ?? 3));

        return [
            'flush_reason' => mb_substr($flushReason !== '' ? $flushReason : 'autosave_threshold_reached', 0, 80),
            'threshold' => $threshold,
            'dirty_field_ids' => $dirtyFieldIds,
            'form_data_field_ids' => $formDataFieldIds,
            'flagged_dirty' => $flaggedDirty,
            'started_at' => $startedAt !== '' ? mb_substr($startedAt, 0, 60) : null,
            'trace' => $trace,
        ];
    }

    private function normalizeClientSnapshotTraceEntry(array $entry): array
    {
        $type = trim((string) ($entry['type'] ?? ''));

        if ($type === '') {
            return [];
        }

        $normalizedEntry = [
            'type' => mb_substr($type, 0, 80),
            'changed' => (bool) ($entry['changed'] ?? false),
        ];

        $sequence = (int) ($entry['sequence'] ?? 0);

        if ($sequence > 0) {
            $normalizedEntry['sequence'] = $sequence;
        }

        foreach ([
            'field_id',
            'assessment_index',
            'from_assessment_index',
            'to_assessment_index',
        ] as $key) {
            $value = $entry[$key] ?? null;

            if (is_numeric($value) && (int) $value >= 0) {
                $normalizedEntry[$key] = (int) $value;
            }
        }

        $clientOccurredAt = trim((string) ($entry['client_occurred_at'] ?? ''));

        if ($clientOccurredAt !== '') {
            $normalizedEntry['client_occurred_at'] = mb_substr($clientOccurredAt, 0, 60);
        }

        return $normalizedEntry;
    }

    private function extractFieldIds(array $fields): array
    {
        return collect($fields)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values()
            ->all();
    }

    private function normalizeFieldIds(array $fieldIds): array
    {
        return collect($fieldIds)
            ->map(fn ($fieldId) => (int) $fieldId)
            ->filter(fn ($fieldId) => $fieldId > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function flattenFields(array $snapshot): array
    {
        return collect($snapshot['assessments'] ?? [])
            ->flatMap(fn ($assessment) => $assessment['forms'] ?? [])
            ->flatMap(fn ($form) => $form['fields'] ?? [])
            ->values()
            ->all();
    }

    private function loadAttemptRelations(AssessmentAttempt $attempt): AssessmentAttempt
    {
        return $attempt->load([
            'answers',
            'securityEvents',
            'target.assignment.assessments.forms.fields',
            'target.assignment.combination',
            'target.combination',
            'target.session',
            'target.guru',
        ]);
    }
}
