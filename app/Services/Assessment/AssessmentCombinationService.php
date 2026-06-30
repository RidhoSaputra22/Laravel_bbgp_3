<?php

namespace App\Services\Assessment;

use App\Enum\AssessmentKetenagaanType;
use App\Models\Assessment;
use App\Models\AssessmentCombination;
use App\Models\AssessmentForm;
use App\Models\AssessmentFormField;
use App\Support\Assessment\AssessmentStructureMetadataResolver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class AssessmentCombinationService
{
    public function __construct(
        private readonly AssessmentStructureMetadataResolver $metadataResolver
    ) {}

    public function buildFormCatalogByKetenagaan(): array
    {
        return collect(AssessmentKetenagaanType::cases())
            ->mapWithKeys(function (AssessmentKetenagaanType $ketenagaan) {
                return [
                    $ketenagaan->value => $this->mapFormCatalogItems(
                        $this->getSourceAssessments($ketenagaan)
                    )->all(),
                ];
            })
            ->all();
    }

    public function createCombination(array $payload, ?int $generatedBy = null): AssessmentCombination
    {
        $targetKetenagaan = AssessmentKetenagaanType::tryFromMixed($payload['target_ketenagaan'] ?? null);

        if (! $targetKetenagaan) {
            throw new InvalidArgumentException('Ketenagaan kombinasi tidak valid.');
        }

        $sourceAssessments = $this->getSourceAssessments($targetKetenagaan);
        $takeCounts = $this->normalizeFormTakeCounts($payload['form_take_counts'] ?? []);
        $selectedRows = [];
        $selectionConfigForms = [];
        $selectionAttempts = 0;
        $randomSeed = null;
        $signatureHash = null;

        do {
            $selectionAttempts++;
            $randomSeed = Str::upper(Str::random(16));
            [$selectedRows, $selectionConfigForms] = $this->buildSelectionRows($sourceAssessments, $takeCounts, $randomSeed);
            $signatureHash = $this->buildSignatureHash($selectedRows);
            $isDuplicate = $signatureHash !== ''
                && AssessmentCombination::query()
                    ->where('target_ketenagaan', $targetKetenagaan->value)
                    ->where('signature_hash', $signatureHash)
                    ->exists();
        } while ($isDuplicate && $selectionAttempts < 10);

        if ($selectedRows === []) {
            throw new InvalidArgumentException('Kombinasi belum memiliki soal terpilih.');
        }

        $generatedAt = now();
        $kodeKombinasi = $this->generateUniqueCode();
        $selectionConfig = [
            'target_ketenagaan' => $targetKetenagaan->value,
            'forms' => $selectionConfigForms,
        ];

        return DB::transaction(function () use (
            $payload,
            $generatedBy,
            $targetKetenagaan,
            $kodeKombinasi,
            $randomSeed,
            $signatureHash,
            $selectionConfig,
            $selectedRows,
            $generatedAt
        ) {
            $combination = AssessmentCombination::create([
                'kode_kombinasi' => $kodeKombinasi,
                'judul' => trim((string) $payload['judul']),
                'deskripsi' => filled($payload['deskripsi'] ?? null) ? trim((string) $payload['deskripsi']) : null,
                'target_ketenagaan' => $targetKetenagaan->value,
                'random_seed' => $randomSeed,
                'signature_hash' => $signatureHash,
                'selection_config' => $selectionConfig,
                'total_assessments' => (int) collect($selectedRows)->pluck('assessment_id')->filter()->unique()->count(),
                'total_forms' => (int) collect($selectedRows)->pluck('assessment_form_id')->filter()->unique()->count(),
                'total_questions' => count($selectedRows),
                'generated_by' => $generatedBy ?: null,
                'generated_at' => $generatedAt,
                'is_active' => true,
            ]);

            $combination->items()->createMany(
                collect($selectedRows)
                    ->map(function (array $row) use ($combination) {
                        return array_merge($row, [
                            'assessment_combination_id' => $combination->id,
                        ]);
                    })
                    ->all()
            );

            $snapshot = $this->buildStructureSnapshot($combination, $selectedRows, $generatedAt);

            $combination->forceFill([
                'structure_snapshot' => $snapshot,
            ])->save();

            return $combination->load(['items', 'generator']);
        });
    }

    public function getSourceAssessments(?AssessmentKetenagaanType $ketenagaan = null): Collection
    {
        return Assessment::query()
            ->with([
                'forms' => function ($query) {
                    $query->where('is_active', true)
                        ->orderBy('urutan')
                        ->with([
                            'fields' => function ($fieldQuery) {
                                $fieldQuery->where('is_active', true)
                                    ->orderBy('urutan');
                            },
                        ]);
                },
            ])
            ->where('is_active', true)
            ->where('status', 'publish')
            ->when(
                $ketenagaan,
                fn ($query) => $query->where('target_ketenagaan', $ketenagaan->value)
            )
            ->orderBy('judul')
            ->get()
            ->filter(function (Assessment $assessment) {
                return $assessment->forms
                    ->filter(fn (AssessmentForm $form) => $form->fields->isNotEmpty())
                    ->isNotEmpty();
            })
            ->values();
    }

    private function mapFormCatalogItems(Collection $assessments): Collection
    {
        return $assessments
            ->values()
            ->flatMap(function (Assessment $assessment, int $assessmentIndex) {
                $assessmentMeta = $this->metadataResolver->decorateAssessment([
                    'id' => $assessment->id,
                    'kode_assessment' => $assessment->kode_assessment,
                    'judul' => $assessment->judul,
                    'deskripsi' => $assessment->deskripsi,
                    'petunjuk' => $assessment->petunjuk,
                    'instrument_type' => $assessment->instrument_type,
                    'scoring_config' => $assessment->scoring_config,
                ]);

                return $assessment->forms
                    ->filter(fn (AssessmentForm $form) => $form->fields->isNotEmpty())
                    ->values()
                    ->map(function (AssessmentForm $form, int $formIndex) use ($assessment, $assessmentMeta, $assessmentIndex) {
                        $formMeta = $this->metadataResolver->decorateForm([
                            'id' => $form->id,
                            'judul_form' => $form->judul_form,
                            'kode_form' => $form->kode_form,
                            'deskripsi' => $form->deskripsi,
                            'kompetensi' => $form->kompetensi,
                            'indikator_kode' => $form->indikator_kode,
                            'indikator_label' => $form->indikator_label,
                            'is_scoreable' => $form->is_scoreable,
                            'scoring_config' => $form->scoring_config,
                            'fields' => $form->fields->map(fn (AssessmentFormField $field) => [
                                'label' => $field->label,
                                'deskripsi' => $field->deskripsi,
                                'bantuan' => $field->bantuan,
                            ])->all(),
                        ], $assessmentMeta);

                        return [
                            'assessment_id' => (int) $assessment->id,
                            'assessment_code' => $assessment->kode_assessment,
                            'assessment_title' => $assessment->judul,
                            'assessment_order' => $assessmentIndex + 1,
                            'instrument_type' => $assessmentMeta['instrument_type'] ?? null,
                            'instrument_label' => $assessmentMeta['instrument_label'] ?? null,
                            'form_id' => (int) $form->id,
                            'form_code' => $form->kode_form,
                            'form_title' => $form->judul_form,
                            'form_description' => $form->deskripsi,
                            'form_order' => $formIndex + 1,
                            'kompetensi' => $formMeta['kompetensi'] ?? null,
                            'kompetensi_label' => $formMeta['kompetensi_label'] ?? null,
                            'indikator_kode' => $formMeta['indikator_kode'] ?? null,
                            'indikator_label' => $formMeta['indikator_label'] ?? null,
                            'is_scoreable' => (bool) ($formMeta['is_scoreable'] ?? false),
                            'available_question_count' => $form->fields->count(),
                        ];
                    });
            })
            ->values();
    }

    private function normalizeFormTakeCounts(mixed $takeCounts): array
    {
        $source = is_array($takeCounts) ? $takeCounts : [];

        return collect($source)
            ->mapWithKeys(function ($count, $formId) {
                $normalizedFormId = (int) $formId;
                $normalizedCount = max((int) $count, 0);

                if ($normalizedFormId <= 0) {
                    return [];
                }

                return [
                    $normalizedFormId => $normalizedCount,
                ];
            })
            ->all();
    }

    private function buildSelectionRows(Collection $assessments, array $takeCounts, string $randomSeed): array
    {
        $rows = [];
        $selectionConfigForms = [];

        foreach ($assessments->values() as $assessmentIndex => $assessment) {
            $assessmentMeta = $this->metadataResolver->decorateAssessment([
                'id' => $assessment->id,
                'kode_assessment' => $assessment->kode_assessment,
                'judul' => $assessment->judul,
                'deskripsi' => $assessment->deskripsi,
                'petunjuk' => $assessment->petunjuk,
                'instrument_type' => $assessment->instrument_type,
                'scoring_config' => $assessment->scoring_config,
            ]);

            foreach ($assessment->forms->values() as $formIndex => $form) {
                $takeCount = (int) ($takeCounts[$form->id] ?? 0);

                if ($takeCount < 1) {
                    continue;
                }

                $availableFields = $form->fields->values();
                $availableFieldCount = $availableFields->count();

                if ($takeCount > $availableFieldCount) {
                    throw new InvalidArgumentException('Jumlah soal melebihi soal aktif yang tersedia pada salah satu form.');
                }

                $formMeta = $this->metadataResolver->decorateForm([
                    'id' => $form->id,
                    'judul_form' => $form->judul_form,
                    'kode_form' => $form->kode_form,
                    'deskripsi' => $form->deskripsi,
                    'kompetensi' => $form->kompetensi,
                    'indikator_kode' => $form->indikator_kode,
                    'indikator_label' => $form->indikator_label,
                    'is_scoreable' => $form->is_scoreable,
                    'scoring_config' => $form->scoring_config,
                    'fields' => $availableFields->map(fn (AssessmentFormField $field) => [
                        'label' => $field->label,
                        'deskripsi' => $field->deskripsi,
                        'bantuan' => $field->bantuan,
                    ])->all(),
                ], $assessmentMeta);

                $selectedFields = $availableFields
                    ->shuffle($this->resolveSelectionSeed($randomSeed, (int) $assessment->id, (int) $form->id))
                    ->take($takeCount)
                    ->values();

                $selectionConfigForms[] = [
                    'assessment_id' => (int) $assessment->id,
                    'assessment_code' => $assessment->kode_assessment,
                    'assessment_title' => $assessment->judul,
                    'form_id' => (int) $form->id,
                    'form_code' => $form->kode_form,
                    'form_title' => $form->judul_form,
                    'available_question_count' => $availableFieldCount,
                    'requested_question_count' => $takeCount,
                ];

                foreach ($selectedFields as $fieldOrder => $field) {
                    $rows[] = [
                        'assessment_id' => $assessment->id,
                        'assessment_form_id' => $form->id,
                        'assessment_form_field_id' => $field->id,
                        'assessment_code' => $assessment->kode_assessment,
                        'assessment_title' => $assessment->judul,
                        'instrument_type' => $assessmentMeta['instrument_type'] ?? null,
                        'form_code' => $form->kode_form,
                        'form_title' => $form->judul_form,
                        'form_description' => $form->deskripsi,
                        'kompetensi' => $formMeta['kompetensi'] ?? null,
                        'indikator_kode' => $formMeta['indikator_kode'] ?? null,
                        'indikator_label' => $formMeta['indikator_label'] ?? null,
                        'form_is_scoreable' => (bool) ($formMeta['is_scoreable'] ?? false),
                        'form_scoring_config' => $form->scoring_config,
                        'field_label' => $field->label,
                        'field_description' => $field->deskripsi,
                        'field_name' => $field->nama_field,
                        'field_type' => $field->tipe_field,
                        'field_placeholder' => $field->placeholder,
                        'field_help' => $field->bantuan,
                        'field_options' => $field->opsi_field,
                        'field_validation' => $field->validasi,
                        'field_scoring_config' => $field->scoring_config,
                        'field_width' => $field->lebar_kolom ?: 'col-md-12',
                        'field_is_required' => (bool) $field->is_required,
                        'assessment_order' => $assessmentIndex + 1,
                        'form_order' => $formIndex + 1,
                        'field_order' => $fieldOrder + 1,
                    ];
                }
            }
        }

        return [$rows, $selectionConfigForms];
    }

    private function buildSignatureHash(array $rows): string
    {
        $signature = collect($rows)
            ->map(fn (array $row) => (string) ($row['assessment_form_field_id'] ?? ''))
            ->filter(fn (string $fieldId) => $fieldId !== '')
            ->implode('|');

        return $signature !== '' ? hash('sha256', $signature) : '';
    }

    private function buildStructureSnapshot(
        AssessmentCombination $combination,
        array $rows,
        \Illuminate\Support\Carbon $generatedAt
    ): array {
        $assessments = collect($rows)
            ->groupBy(fn (array $row) => (int) ($row['assessment_id'] ?? 0))
            ->sortBy(fn (Collection $group) => (int) ($group->first()['assessment_order'] ?? 0))
            ->map(function (Collection $assessmentRows, int $assessmentId) {
                $firstAssessmentRow = $assessmentRows->first();
                $sourceAssessment = Assessment::query()->find($assessmentId);
                $assessmentMeta = $this->metadataResolver->decorateAssessment([
                    'id' => $assessmentId,
                    'kode_assessment' => $firstAssessmentRow['assessment_code'] ?? null,
                    'judul' => $firstAssessmentRow['assessment_title'] ?? null,
                    'deskripsi' => $sourceAssessment?->deskripsi,
                    'petunjuk' => $sourceAssessment?->petunjuk,
                    'instrument_type' => $firstAssessmentRow['instrument_type'] ?? null,
                    'scoring_config' => $sourceAssessment?->scoring_config,
                ]);

                $forms = $assessmentRows
                    ->groupBy(fn (array $row) => (int) ($row['assessment_form_id'] ?? 0))
                    ->sortBy(fn (Collection $group) => (int) ($group->first()['form_order'] ?? 0))
                    ->map(function (Collection $formRows, int $formId) use ($assessmentMeta) {
                        $firstFormRow = $formRows->first();
                        $sourceForm = AssessmentForm::query()->find($formId);
                        $formMeta = $this->metadataResolver->decorateForm([
                            'id' => $formId,
                            'judul_form' => $firstFormRow['form_title'] ?? null,
                            'kode_form' => $firstFormRow['form_code'] ?? null,
                            'deskripsi' => $firstFormRow['form_description'] ?? null,
                            'kompetensi' => $firstFormRow['kompetensi'] ?? null,
                            'indikator_kode' => $firstFormRow['indikator_kode'] ?? null,
                            'indikator_label' => $firstFormRow['indikator_label'] ?? null,
                            'is_scoreable' => (bool) ($firstFormRow['form_is_scoreable'] ?? false),
                            'scoring_config' => $firstFormRow['form_scoring_config'] ?? [],
                            'fields' => $formRows->map(fn (array $row) => [
                                'label' => $row['field_label'] ?? null,
                                'deskripsi' => $row['field_description'] ?? null,
                                'bantuan' => $row['field_help'] ?? null,
                            ])->all(),
                        ], $assessmentMeta);

                        $fields = $formRows
                            ->sortBy('field_order')
                            ->values()
                            ->map(function (array $row) {
                                return [
                                    'id' => (int) ($row['assessment_form_field_id'] ?? 0),
                                    'assessment_id' => (int) ($row['assessment_id'] ?? 0),
                                    'assessment_form_id' => (int) ($row['assessment_form_id'] ?? 0),
                                    'label' => $row['field_label'],
                                    'deskripsi' => $row['field_description'],
                                    'nama_field' => $row['field_name'],
                                    'tipe_field' => $row['field_type'],
                                    'placeholder' => $row['field_placeholder'],
                                    'bantuan' => $row['field_help'],
                                    'opsi_field' => $row['field_options'] ?? [],
                                    'validasi' => $row['field_validation'] ?? [],
                                    'scoring_config' => $row['field_scoring_config'] ?? [],
                                    'lebar_kolom' => $row['field_width'] ?? 'col-md-12',
                                    'is_required' => (bool) ($row['field_is_required'] ?? false),
                                ];
                            })
                            ->all();

                        return [
                            'id' => $formMeta['id'],
                            'assessment_id' => $assessmentMeta['id'],
                            'judul_form' => $formMeta['judul_form'],
                            'kode_form' => $formMeta['kode_form'],
                            'deskripsi' => $formMeta['deskripsi'],
                            'kompetensi' => $formMeta['kompetensi'],
                            'kompetensi_label' => $formMeta['kompetensi_label'],
                            'indikator_kode' => $formMeta['indikator_kode'],
                            'indikator_label' => $formMeta['indikator_label'],
                            'is_scoreable' => (bool) ($formMeta['is_scoreable'] ?? false),
                            'scoring_config' => $firstFormRow['form_scoring_config'] ?? ($sourceForm?->scoring_config ?? []),
                            'fields' => $fields,
                        ];
                    })
                    ->values()
                    ->all();

                return [
                    'id' => $assessmentMeta['id'],
                    'kode_assessment' => $assessmentMeta['kode_assessment'],
                    'judul' => $assessmentMeta['judul'],
                    'deskripsi' => $assessmentMeta['deskripsi'],
                    'petunjuk' => $assessmentMeta['petunjuk'],
                    'instrument_type' => $assessmentMeta['instrument_type'],
                    'instrument_label' => $assessmentMeta['instrument_label'],
                    'scoring_config' => $sourceAssessment?->scoring_config ?? [],
                    'forms' => $forms,
                ];
            })
            ->values()
            ->all();

        $allFields = collect($assessments)
            ->flatMap(fn (array $assessment) => $assessment['forms'] ?? [])
            ->flatMap(fn (array $form) => $form['fields'] ?? []);

        return [
            'generated_at' => $generatedAt->toIso8601String(),
            'combination' => [
                'id' => $combination->id,
                'kode_kombinasi' => $combination->kode_kombinasi,
                'judul' => $combination->judul,
                'target_ketenagaan' => $combination->target_ketenagaan,
            ],
            'assessments' => $assessments,
            'meta' => [
                'source' => 'assessment_combination',
                'total_questions' => $allFields->count(),
                'required_questions' => $allFields->where('is_required', true)->count(),
                'randomization' => [
                    'version' => 1,
                    'question_order' => 'fixed_from_combination',
                    'choice_order' => 'radio_options_for_pilihan_ganda_kompleks',
                ],
            ],
        ];
    }

    private function resolveSelectionSeed(string $randomSeed, int $assessmentId, int $formId): int
    {
        return (int) sprintf(
            '%u',
            crc32(implode('|', [
                'assessment-combination',
                $randomSeed,
                $assessmentId,
                $formId,
            ]))
        );
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = 'KMB-ASM-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(4));
        } while (AssessmentCombination::query()->where('kode_kombinasi', $code)->exists());

        return $code;
    }
}
