<?php

namespace App\Services\Assessment;

use App\Enum\AssessmentKetenagaanType;
use App\Enum\KompetensiGuru;
use App\Models\Assessment;
use App\Models\AssessmentCombination;
use App\Models\AssessmentCombinationItem;
use App\Models\AssessmentForm;
use App\Models\AssessmentFormField;
use App\Support\Assessment\AssessmentStructureMetadataResolver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use InvalidArgumentException;

class AssessmentCombinationService
{
    public function __construct(
        private readonly AssessmentStructureMetadataResolver $metadataResolver
    ) {}

    public function buildAssessmentCatalogByKetenagaan(): array
    {
        return collect(AssessmentKetenagaanType::cases())
            ->mapWithKeys(function (AssessmentKetenagaanType $ketenagaan) {
                return [
                    $ketenagaan->value => $this->mapAssessmentCatalogItems(
                        $this->getSourceAssessments($ketenagaan)
                    )->all(),
                ];
            })
            ->all();
    }

    public function buildFormCatalogByKetenagaan(): array
    {
        return $this->buildAssessmentCatalogByKetenagaan();
    }

    public function syncCombinationsForAssessment(Assessment $assessment): array
    {
        if (
            ! Schema::hasTable('assessment_combinations')
            || ! Schema::hasTable('assessment_combination_items')
        ) {
            return [
                'updated' => 0,
                'combination_ids' => [],
            ];
        }

        $assessment->loadMissing([
            'forms' => function ($query) {
                $query->orderBy('urutan')
                    ->with([
                        'fields' => function ($fieldQuery) {
                            $fieldQuery->orderBy('urutan');
                        },
                    ]);
            },
        ]);

        $combinationIds = AssessmentCombinationItem::query()
            ->where('assessment_id', $assessment->id)
            ->pluck('assessment_combination_id')
            ->map(fn ($combinationId) => (int) $combinationId)
            ->filter(fn (int $combinationId) => $combinationId > 0)
            ->unique()
            ->values();

        if ($combinationIds->isEmpty()) {
            return [
                'updated' => 0,
                'combination_ids' => [],
            ];
        }

        $context = $this->buildAssessmentSyncContext($assessment);
        $updatedIds = [];

        AssessmentCombination::query()
            ->with([
                'items' => function ($query) {
                    $query->orderBy('assessment_order')
                        ->orderBy('form_order')
                        ->orderBy('field_order')
                        ->orderBy('id');
                },
            ])
            ->whereIn('id', $combinationIds->all())
            ->get()
            ->each(function (AssessmentCombination $combination) use ($assessment, $context, &$updatedIds) {
                if ($this->syncSingleCombinationForAssessment($combination, $assessment, $context)) {
                    $updatedIds[] = (int) $combination->id;
                }
            });

        return [
            'updated' => count($updatedIds),
            'combination_ids' => $updatedIds,
        ];
    }

    public function createCombination(array $payload, ?int $generatedBy = null): AssessmentCombination
    {
        $targetKetenagaan = AssessmentKetenagaanType::tryFromMixed($payload['target_ketenagaan'] ?? null);

        if (! $targetKetenagaan) {
            throw new InvalidArgumentException('Ketenagaan kombinasi tidak valid.');
        }

        $sourceAssessments = $this->getSourceAssessments($targetKetenagaan);
        $competencySelections = $this->normalizeCompetencySelections(
            $payload['competency_selection_modes'] ?? [],
            $payload['competency_take_counts'] ?? [],
        );
        $selectedRows = [];
        $selectionConfigAssessments = [];
        $selectionAttempts = 0;
        $randomSeed = null;
        $signatureHash = null;

        do {
            $selectionAttempts++;
            $randomSeed = Str::upper(Str::random(16));
            [$selectedRows, $selectionConfigAssessments] = $this->buildSelectionRows(
                $sourceAssessments,
                $competencySelections,
                $randomSeed
            );
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
            'assessments' => $selectionConfigAssessments,
        ];

        return DB::transaction(function () use (
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
                'judul' => $kodeKombinasi,
                'deskripsi' => null,
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

    private function syncSingleCombinationForAssessment(
        AssessmentCombination $combination,
        Assessment $assessment,
        array $context
    ): bool {
        $affectedItems = $combination->items
            ->where('assessment_id', $assessment->id)
            ->values();

        if ($affectedItems->isEmpty()) {
            return false;
        }

        $refreshedRows = $affectedItems
            ->map(fn (AssessmentCombinationItem $item) => $this->resolveRefreshedRowForAssessmentItem($item, $context))
            ->filter(fn ($row) => is_array($row))
            ->values();

        $combination->items()->where('assessment_id', $assessment->id)->delete();

        if ($refreshedRows->isNotEmpty()) {
            $combination->items()->createMany($refreshedRows->all());
        }

        $combination->load([
            'items' => function ($query) {
                $query->orderBy('assessment_order')
                    ->orderBy('form_order')
                    ->orderBy('field_order')
                    ->orderBy('id');
            },
        ]);

        $selectionConfig = $this->refreshSelectionConfigForAssessment(
            (array) ($combination->selection_config ?? []),
            $assessment,
            $refreshedRows,
            $context
        );
        $allRows = $combination->items
            ->map(fn (AssessmentCombinationItem $item) => $item->toArray())
            ->all();
        $assessmentIds = $combination->items
            ->pluck('assessment_id')
            ->map(fn ($assessmentId) => (int) $assessmentId)
            ->filter(fn (int $assessmentId) => $assessmentId > 0)
            ->unique()
            ->values();
        $sourceAssessments = Assessment::query()
            ->whereIn('id', $assessmentIds->all())
            ->get()
            ->keyBy('id');
        $hasEligibleSources = $assessmentIds->isNotEmpty()
            && $assessmentIds->every(function (int $assessmentId) use ($sourceAssessments, $combination) {
                $sourceAssessment = $sourceAssessments->get($assessmentId);

                return $sourceAssessment
                    && $this->isAssessmentEligibleForCombination($sourceAssessment, $combination->target_ketenagaan);
            });

        $combination->forceFill([
            'selection_config' => $selectionConfig,
            'structure_snapshot' => $this->buildStructureSnapshot(
                $combination,
                $allRows,
                $combination->generated_at ?: $combination->created_at ?: now()
            ),
            'total_assessments' => (int) $combination->items->pluck('assessment_id')->filter()->unique()->count(),
            'total_forms' => (int) $combination->items->pluck('assessment_form_id')->filter()->unique()->count(),
            'total_questions' => (int) $combination->items->count(),
            'signature_hash' => $this->buildSignatureHash($allRows),
            'is_active' => $hasEligibleSources,
        ])->save();

        return true;
    }

    private function mapAssessmentCatalogItems(Collection $assessments): Collection
    {
        return $assessments
            ->values()
            ->map(function (Assessment $assessment, int $assessmentIndex) {
                return $this->mapAssessmentCatalogItem(
                    $this->analyzeAssessmentSource($assessment, $assessmentIndex)
                );
            })
            ->values();
    }

    private function mapAssessmentCatalogItem(array $analysis): array
    {
        /** @var Assessment $assessment */
        $assessment = $analysis['assessment'];
        $assessmentMeta = $analysis['assessment_meta'];

        $competencies = collect(KompetensiGuru::cases())
            ->map(function (KompetensiGuru $kompetensi) use ($analysis) {
                $group = $analysis['competency_pools'][$kompetensi->value] ?? [
                    'forms' => [],
                    'pool' => [],
                ];
                $forms = collect($group['forms'] ?? [])->values();

                return [
                    'kompetensi' => $kompetensi->value,
                    'kompetensi_label' => $kompetensi->label(),
                    'available_form_count' => $forms->count(),
                    'available_question_count' => count($group['pool'] ?? []),
                    'form_titles' => $forms->pluck('form_title')->all(),
                    'form_codes' => $forms->pluck('form_code')->all(),
                    'indikator_codes' => $forms->pluck('indikator_kode')->filter()->values()->all(),
                ];
            })
            ->values()
            ->all();

        $autoIncludedForms = collect($analysis['auto_included_forms'])
            ->map(function (array $formEntry) {
                return [
                    'form_id' => (int) $formEntry['form']->id,
                    'form_code' => $formEntry['form']->kode_form,
                    'form_title' => $formEntry['form']->judul_form,
                    'form_description' => $formEntry['form']->deskripsi,
                    'available_question_count' => (int) $formEntry['available_question_count'],
                    'indikator_kode' => $formEntry['form_meta']['indikator_kode'] ?? null,
                    'indikator_label' => $formEntry['form_meta']['indikator_label'] ?? null,
                    'is_scoreable' => (bool) ($formEntry['form_meta']['is_scoreable'] ?? false),
                ];
            })
            ->values()
            ->all();

        return [
            'assessment_id' => (int) $assessment->id,
            'assessment_code' => $assessment->kode_assessment,
            'assessment_title' => $assessment->judul,
            'assessment_order' => (int) $analysis['assessment_order'],
            'instrument_type' => $assessmentMeta['instrument_type'] ?? null,
            'instrument_label' => $assessmentMeta['instrument_label'] ?? null,
            'competencies' => $competencies,
            'auto_included_forms' => $autoIncludedForms,
            'auto_included_form_count' => count($autoIncludedForms),
            'auto_included_question_count' => (int) collect($autoIncludedForms)
                ->sum('available_question_count'),
            'total_forms' => (int) $analysis['total_forms'],
            'total_questions' => (int) $analysis['total_questions'],
        ];
    }

    private function analyzeAssessmentSource(Assessment $assessment, int $assessmentIndex = 0): array
    {
        $assessmentMeta = $this->metadataResolver->decorateAssessment([
            'id' => $assessment->id,
            'kode_assessment' => $assessment->kode_assessment,
            'judul' => $assessment->judul,
            'deskripsi' => $assessment->deskripsi,
            'petunjuk' => $assessment->petunjuk,
            'instrument_type' => $assessment->instrument_type,
            'scoring_config' => $assessment->scoring_config,
        ]);
        $assessmentOrder = $assessmentIndex + 1;
        $competencyPools = collect(KompetensiGuru::cases())
            ->mapWithKeys(function (KompetensiGuru $kompetensi) {
                return [
                    $kompetensi->value => [
                        'kompetensi' => $kompetensi->value,
                        'kompetensi_label' => $kompetensi->label(),
                        'forms' => [],
                        'pool' => [],
                    ],
                ];
            })
            ->all();
        $autoIncludedForms = [];
        $totalForms = 0;
        $totalQuestions = 0;

        foreach ($assessment->forms->filter(fn (AssessmentForm $form) => $form->fields->isNotEmpty())->values() as $formIndex => $form) {
            $availableFields = $form->fields->values();
            $availableFieldCount = $availableFields->count();

            if ($availableFieldCount < 1) {
                continue;
            }

            $totalForms++;
            $totalQuestions += $availableFieldCount;

            $formMeta = $this->buildFormMeta($assessmentMeta, $form, $availableFields);
            $formEntry = [
                'form' => $form,
                'form_meta' => $formMeta,
                'form_order' => $formIndex + 1,
                'available_fields' => $availableFields,
                'available_question_count' => $availableFieldCount,
            ];

            if (filled($formMeta['kompetensi'] ?? null)) {
                $kompetensiKey = (string) $formMeta['kompetensi'];

                if (! isset($competencyPools[$kompetensiKey])) {
                    $competencyPools[$kompetensiKey] = [
                        'kompetensi' => $kompetensiKey,
                        'kompetensi_label' => $formMeta['kompetensi_label'] ?? ucfirst($kompetensiKey),
                        'forms' => [],
                        'pool' => [],
                    ];
                }

                $competencyPools[$kompetensiKey]['forms'][] = [
                    'form_id' => (int) $form->id,
                    'form_code' => $form->kode_form,
                    'form_title' => $form->judul_form,
                    'indikator_kode' => $formMeta['indikator_kode'] ?? null,
                    'indikator_label' => $formMeta['indikator_label'] ?? null,
                    'available_question_count' => $availableFieldCount,
                ];

                foreach ($availableFields as $fieldIndex => $field) {
                    $competencyPools[$kompetensiKey]['pool'][] = [
                        'assessment' => $assessment,
                        'assessment_meta' => $assessmentMeta,
                        'assessment_order' => $assessmentOrder,
                        'form' => $form,
                        'form_meta' => $formMeta,
                        'form_order' => $formIndex + 1,
                        'field' => $field,
                        'field_source_order' => $fieldIndex + 1,
                    ];
                }

                continue;
            }

            $autoIncludedForms[] = $formEntry;
        }

        return [
            'assessment' => $assessment,
            'assessment_meta' => $assessmentMeta,
            'assessment_order' => $assessmentOrder,
            'competency_pools' => $competencyPools,
            'auto_included_forms' => $autoIncludedForms,
            'total_forms' => $totalForms,
            'total_questions' => $totalQuestions,
        ];
    }

    private function buildFormMeta(array $assessmentMeta, AssessmentForm $form, Collection $availableFields): array
    {
        return $this->metadataResolver->decorateForm([
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
    }

    private function normalizeCompetencySelections(mixed $selectionModes, mixed $takeCounts): array
    {
        $modeSource = is_array($selectionModes) ? $selectionModes : [];
        $countSource = is_array($takeCounts) ? $takeCounts : [];
        $assessmentIds = collect(array_merge(array_keys($modeSource), array_keys($countSource)))
            ->map(fn ($assessmentId) => (int) $assessmentId)
            ->filter(fn (int $assessmentId) => $assessmentId > 0)
            ->unique()
            ->values();
        $normalized = [];

        foreach ($assessmentIds as $assessmentId) {
            $assessmentModes = is_array($modeSource[$assessmentId] ?? null) ? $modeSource[$assessmentId] : [];
            $assessmentCounts = is_array($countSource[$assessmentId] ?? null) ? $countSource[$assessmentId] : [];
            $competencyKeys = collect(array_merge(array_keys($assessmentModes), array_keys($assessmentCounts)))
                ->filter(fn ($value) => is_string($value) && trim($value) !== '')
                ->unique()
                ->values();

            foreach ($competencyKeys as $competencyKey) {
                $normalized[$assessmentId][$competencyKey] = [
                    'mode' => ($assessmentModes[$competencyKey] ?? 'count') === 'all' ? 'all' : 'count',
                    'count' => max((int) ($assessmentCounts[$competencyKey] ?? 0), 0),
                ];
            }
        }

        return $normalized;
    }

    private function buildSelectionRows(Collection $assessments, array $competencySelections, string $randomSeed): array
    {
        $rows = [];
        $selectionConfigAssessments = [];

        foreach ($assessments->values() as $assessmentIndex => $assessment) {
            $analysis = $this->analyzeAssessmentSource($assessment, $assessmentIndex);
            $assessmentMeta = $analysis['assessment_meta'];
            $selectedAssessmentRows = [];
            $selectionConfigCompetencies = [];

            foreach (KompetensiGuru::cases() as $kompetensi) {
                $group = $analysis['competency_pools'][$kompetensi->value] ?? [
                    'forms' => [],
                    'pool' => [],
                ];
                $availableCount = count($group['pool'] ?? []);
                $availableFormCount = count($group['forms'] ?? []);
                $selection = $competencySelections[(int) $assessment->id][$kompetensi->value] ?? [
                    'mode' => 'count',
                    'count' => min(10, $availableCount),
                ];
                $selectionMode = $selection['mode'] === 'all' ? 'all' : 'count';
                $requestedCount = $selectionMode === 'all'
                    ? $availableCount
                    : max((int) ($selection['count'] ?? 0), 0);

                $selectionConfigCompetencies[] = $this->buildSelectionConfigCompetency(
                    $kompetensi,
                    $group,
                    $selectionMode,
                    $availableFormCount,
                    $availableCount,
                    $requestedCount
                );

                if ($availableCount < 1) {
                    continue;
                }

                if ($selectionMode === 'all') {
                    $selectedPool = $this->sortSelectionPool(collect($group['pool'] ?? []));
                } else {
                    if ($requestedCount < 1) {
                        throw new InvalidArgumentException(
                            'Jumlah soal per kompetensi minimal 1 atau pilih semua soal.'
                        );
                    }

                    if ($requestedCount > $availableCount) {
                        throw new InvalidArgumentException(
                            'Jumlah soal kompetensi melebihi soal aktif yang tersedia.'
                        );
                    }

                    $selectedPool = collect($group['pool'] ?? [])
                        ->shuffle($this->resolveSelectionSeed($randomSeed, (int) $assessment->id, $kompetensi->value))
                        ->take($requestedCount)
                        ->values();
                    $selectedPool = $this->sortSelectionPool($selectedPool);
                }

                foreach ($selectedPool as $poolEntry) {
                    $selectedAssessmentRows[] = $this->buildSelectionRow($poolEntry);
                }
            }

            $selectionConfigAutoForms = collect($analysis['auto_included_forms'])
                ->map(function (array $formEntry) use (&$selectedAssessmentRows, $assessment, $assessmentMeta, $analysis) {
                    foreach ($formEntry['available_fields'] as $fieldIndex => $field) {
                        $selectedAssessmentRows[] = $this->buildSelectionRow([
                            'assessment' => $assessment,
                            'assessment_meta' => $assessmentMeta,
                            'form' => $formEntry['form'],
                            'form_meta' => $formEntry['form_meta'],
                            'form_order' => (int) $formEntry['form_order'],
                            'field' => $field,
                            'field_source_order' => $fieldIndex + 1,
                        ], (int) $analysis['assessment_order']);
                    }

                    return $this->buildSelectionConfigAutoForm($formEntry);
                })
                ->values()
                ->all();

            if ($selectedAssessmentRows === []) {
                continue;
            }

            $rows = array_merge($rows, $selectedAssessmentRows);
            $selectionConfigAssessments[] = [
                'assessment_id' => (int) $assessment->id,
                'assessment_code' => $assessment->kode_assessment,
                'assessment_title' => $assessment->judul,
                'instrument_type' => $assessmentMeta['instrument_type'] ?? null,
                'instrument_label' => $assessmentMeta['instrument_label'] ?? null,
                'competencies' => $selectionConfigCompetencies,
                'auto_included_forms' => $selectionConfigAutoForms,
                'auto_included_form_count' => count($selectionConfigAutoForms),
                'auto_included_question_count' => (int) collect($selectionConfigAutoForms)
                    ->sum('selected_question_count'),
                'selected_question_count' => count($selectedAssessmentRows),
            ];
        }

        return [$rows, $selectionConfigAssessments];
    }

    private function buildSelectionConfigCompetency(
        KompetensiGuru $kompetensi,
        array $group,
        string $selectionMode,
        int $availableFormCount,
        int $availableCount,
        int $requestedCount
    ): array {
        return [
            'kompetensi' => $kompetensi->value,
            'kompetensi_label' => $kompetensi->label(),
            'selection_mode' => $availableCount < 1 ? 'unavailable' : $selectionMode,
            'available_form_count' => $availableFormCount,
            'available_question_count' => $availableCount,
            'requested_question_count' => $availableCount < 1 ? 0 : $requestedCount,
            'selected_question_count' => $availableCount < 1 ? 0 : $requestedCount,
            'forms' => collect($group['forms'] ?? [])->values()->all(),
        ];
    }

    private function buildSelectionConfigAutoForm(array $formEntry): array
    {
        return [
            'form_id' => (int) $formEntry['form']->id,
            'form_code' => $formEntry['form']->kode_form,
            'form_title' => $formEntry['form']->judul_form,
            'form_description' => $formEntry['form']->deskripsi,
            'indikator_kode' => $formEntry['form_meta']['indikator_kode'] ?? null,
            'indikator_label' => $formEntry['form_meta']['indikator_label'] ?? null,
            'available_question_count' => (int) $formEntry['available_question_count'],
            'selected_question_count' => (int) $formEntry['available_question_count'],
            'selection_mode' => 'fixed_all',
        ];
    }

    private function sortSelectionPool(Collection $pool): Collection
    {
        return $pool
            ->sortBy(fn (array $entry) => $this->buildPoolSortKey($entry))
            ->values();
    }

    private function buildPoolSortKey(array $entry): string
    {
        return sprintf(
            '%05d-%05d-%05d',
            (int) ($entry['assessment_order'] ?? 0),
            (int) ($entry['form_order'] ?? 0),
            (int) ($entry['field_source_order'] ?? 0),
        );
    }

    private function buildSelectionRow(array $poolEntry, ?int $assessmentOrder = null): array
    {
        /** @var Assessment $assessment */
        $assessment = $poolEntry['assessment'];
        /** @var AssessmentForm $form */
        $form = $poolEntry['form'];
        /** @var AssessmentFormField $field */
        $field = $poolEntry['field'];
        $assessmentMeta = $poolEntry['assessment_meta'];
        $formMeta = $poolEntry['form_meta'];

        return [
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
            'assessment_order' => $assessmentOrder ?? (int) ($poolEntry['assessment_order'] ?? 0),
            'form_order' => (int) ($poolEntry['form_order'] ?? 0),
            'field_order' => (int) ($poolEntry['field_source_order'] ?? 0),
        ];
    }

    private function buildSignatureHash(array $rows): string
    {
        $signature = collect($rows)
            ->map(fn (array $row) => (string) ($row['assessment_form_field_id'] ?? ''))
            ->filter(fn (string $fieldId) => $fieldId !== '')
            ->sort()
            ->values()
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
                'judul' => $combination->kode_kombinasi,
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

    private function buildAssessmentSyncContext(Assessment $assessment): array
    {
        $assessmentMeta = $this->metadataResolver->decorateAssessment([
            'id' => $assessment->id,
            'kode_assessment' => $assessment->kode_assessment,
            'judul' => $assessment->judul,
            'deskripsi' => $assessment->deskripsi,
            'petunjuk' => $assessment->petunjuk,
            'instrument_type' => $assessment->instrument_type,
            'scoring_config' => $assessment->scoring_config,
        ]);
        $activeForms = $assessment->forms
            ->filter(fn (AssessmentForm $form) => (bool) $form->is_active)
            ->sortBy('urutan')
            ->values();
        $fieldsById = [];
        $fieldsByFormIdAndName = [];
        $fieldsByFormIdAndOrder = [];
        $fieldsByFormCodeAndName = [];
        $fieldsByFormCodeAndOrder = [];
        $fieldsByFormOrderAndOrder = [];
        $competencyPools = collect(KompetensiGuru::cases())
            ->mapWithKeys(function (KompetensiGuru $kompetensi) {
                return [
                    $kompetensi->value => [
                        'kompetensi' => $kompetensi->value,
                        'kompetensi_label' => $kompetensi->label(),
                        'forms' => [],
                        'pool' => [],
                    ],
                ];
            })
            ->all();
        $autoIncludedForms = [];

        foreach ($activeForms as $formIndex => $form) {
            $activeFields = $form->fields
                ->filter(fn (AssessmentFormField $field) => (bool) $field->is_active)
                ->sortBy('urutan')
                ->values();

            if ($activeFields->isEmpty()) {
                continue;
            }

            $resolvedFormOrder = (int) ($form->urutan ?: ($formIndex + 1));
            $formMeta = $this->buildFormMeta($assessmentMeta, $form, $activeFields);
            $formEntry = [
                'form' => $form,
                'form_meta' => $formMeta,
                'form_order' => $resolvedFormOrder,
                'available_fields' => $activeFields,
                'available_question_count' => $activeFields->count(),
            ];

            foreach ($activeFields as $fieldIndex => $field) {
                $resolvedFieldOrder = (int) ($field->urutan ?: ($fieldIndex + 1));
                $poolEntry = [
                    'assessment' => $assessment,
                    'assessment_meta' => $assessmentMeta,
                    'assessment_order' => 1,
                    'form' => $form,
                    'form_meta' => $formMeta,
                    'form_order' => $resolvedFormOrder,
                    'field' => $field,
                    'field_source_order' => $resolvedFieldOrder,
                ];
                $formCode = trim((string) ($form->kode_form ?? ''));
                $fieldName = trim((string) ($field->nama_field ?? ''));

                $fieldsById[(int) $field->id] = $poolEntry;

                if ($fieldName !== '') {
                    $fieldsByFormIdAndName[$form->id.'|'.$fieldName] = $poolEntry;

                    if ($formCode !== '') {
                        $fieldsByFormCodeAndName[$formCode.'|'.$fieldName] = $poolEntry;
                    }
                }

                $fieldsByFormIdAndOrder[$form->id.'|'.$resolvedFieldOrder] = $poolEntry;
                $fieldsByFormOrderAndOrder[$resolvedFormOrder.'|'.$resolvedFieldOrder] = $poolEntry;

                if ($formCode !== '') {
                    $fieldsByFormCodeAndOrder[$formCode.'|'.$resolvedFieldOrder] = $poolEntry;
                }

                if (filled($formMeta['kompetensi'] ?? null)) {
                    $kompetensiKey = (string) $formMeta['kompetensi'];

                    if (! isset($competencyPools[$kompetensiKey])) {
                        $competencyPools[$kompetensiKey] = [
                            'kompetensi' => $kompetensiKey,
                            'kompetensi_label' => $formMeta['kompetensi_label'] ?? ucfirst($kompetensiKey),
                            'forms' => [],
                            'pool' => [],
                        ];
                    }

                    $competencyPools[$kompetensiKey]['pool'][] = $poolEntry;
                }
            }

            if (filled($formMeta['kompetensi'] ?? null)) {
                $kompetensiKey = (string) $formMeta['kompetensi'];
                $competencyPools[$kompetensiKey]['forms'][] = [
                    'form_id' => (int) $form->id,
                    'form_code' => $form->kode_form,
                    'form_title' => $form->judul_form,
                    'indikator_kode' => $formMeta['indikator_kode'] ?? null,
                    'indikator_label' => $formMeta['indikator_label'] ?? null,
                    'available_question_count' => $activeFields->count(),
                ];

                continue;
            }

            $autoIncludedForms[] = $formEntry;
        }

        return [
            'assessment' => $assessment,
            'assessment_meta' => $assessmentMeta,
            'competency_pools' => $competencyPools,
            'auto_included_forms' => $autoIncludedForms,
            'fields_by_id' => $fieldsById,
            'fields_by_form_id_and_name' => $fieldsByFormIdAndName,
            'fields_by_form_id_and_order' => $fieldsByFormIdAndOrder,
            'fields_by_form_code_and_name' => $fieldsByFormCodeAndName,
            'fields_by_form_code_and_order' => $fieldsByFormCodeAndOrder,
            'fields_by_form_order_and_order' => $fieldsByFormOrderAndOrder,
        ];
    }

    private function resolveRefreshedRowForAssessmentItem(
        AssessmentCombinationItem $item,
        array $context
    ): ?array {
        $poolEntry = $this->resolveContextEntryForCombinationItem($item, $context);

        if (! $poolEntry) {
            return null;
        }

        return $this->buildSelectionRow(
            $poolEntry,
            (int) ($item->assessment_order ?: ($poolEntry['assessment_order'] ?? 1))
        );
    }

    private function resolveContextEntryForCombinationItem(
        AssessmentCombinationItem $item,
        array $context
    ): ?array {
        $fieldId = (int) ($item->assessment_form_field_id ?? 0);

        if ($fieldId > 0 && isset($context['fields_by_id'][$fieldId])) {
            return $context['fields_by_id'][$fieldId];
        }

        $formId = (int) ($item->assessment_form_id ?? 0);
        $fieldName = trim((string) ($item->field_name ?? ''));
        $formCode = trim((string) ($item->form_code ?? ''));
        $fieldOrder = (int) ($item->field_order ?? 0);
        $formOrder = (int) ($item->form_order ?? 0);
        $lookupCandidates = [
            $formId > 0 && $fieldName !== ''
                ? $context['fields_by_form_id_and_name'][$formId.'|'.$fieldName] ?? null
                : null,
            $formCode !== '' && $fieldName !== ''
                ? $context['fields_by_form_code_and_name'][$formCode.'|'.$fieldName] ?? null
                : null,
            $formId > 0 && $fieldOrder > 0
                ? $context['fields_by_form_id_and_order'][$formId.'|'.$fieldOrder] ?? null
                : null,
            $formCode !== '' && $fieldOrder > 0
                ? $context['fields_by_form_code_and_order'][$formCode.'|'.$fieldOrder] ?? null
                : null,
            $formOrder > 0 && $fieldOrder > 0
                ? $context['fields_by_form_order_and_order'][$formOrder.'|'.$fieldOrder] ?? null
                : null,
        ];

        foreach ($lookupCandidates as $candidate) {
            if (is_array($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function refreshSelectionConfigForAssessment(
        array $selectionConfig,
        Assessment $assessment,
        Collection $refreshedRows,
        array $context
    ): array {
        $assessmentEntries = collect($selectionConfig['assessments'] ?? [])
            ->values();
        $existingEntry = $assessmentEntries
            ->firstWhere('assessment_id', $assessment->id) ?? [];
        $existingCompetencyMap = collect($existingEntry['competencies'] ?? [])
            ->mapWithKeys(fn ($entry) => [(string) ($entry['kompetensi'] ?? '') => $entry]);
        $selectionAssessment = [
            'assessment_id' => (int) $assessment->id,
            'assessment_code' => $assessment->kode_assessment,
            'assessment_title' => $assessment->judul,
            'instrument_type' => $context['assessment_meta']['instrument_type'] ?? null,
            'instrument_label' => $context['assessment_meta']['instrument_label'] ?? null,
            'competencies' => collect(KompetensiGuru::cases())
                ->map(function (KompetensiGuru $kompetensi) use ($context, $existingCompetencyMap, $refreshedRows) {
                    $group = $context['competency_pools'][$kompetensi->value] ?? [
                        'forms' => [],
                        'pool' => [],
                    ];
                    $availableCount = count($group['pool'] ?? []);
                    $existingConfig = $existingCompetencyMap->get($kompetensi->value, []);
                    $selectedCount = (int) $refreshedRows
                        ->where('kompetensi', $kompetensi->value)
                        ->count();
                    $selectionMode = $availableCount < 1
                        ? 'unavailable'
                        : (($existingConfig['selection_mode'] ?? 'count') === 'all' ? 'all' : 'count');

                    return [
                        'kompetensi' => $kompetensi->value,
                        'kompetensi_label' => $kompetensi->label(),
                        'selection_mode' => $selectionMode,
                        'available_form_count' => count($group['forms'] ?? []),
                        'available_question_count' => $availableCount,
                        'requested_question_count' => $availableCount < 1
                            ? 0
                            : max((int) ($existingConfig['requested_question_count'] ?? $selectedCount), 0),
                        'selected_question_count' => $selectedCount,
                        'forms' => collect($group['forms'] ?? [])
                            ->values()
                            ->all(),
                    ];
                })
                ->values()
                ->all(),
            'auto_included_forms' => collect($context['auto_included_forms'] ?? [])
                ->map(function (array $formEntry) use ($refreshedRows) {
                    $formId = (int) ($formEntry['form']->id ?? 0);
                    $selectedCount = (int) $refreshedRows
                        ->where('assessment_form_id', $formId)
                        ->count();

                    return [
                        'form_id' => $formId,
                        'form_code' => $formEntry['form']->kode_form,
                        'form_title' => $formEntry['form']->judul_form,
                        'form_description' => $formEntry['form']->deskripsi,
                        'indikator_kode' => $formEntry['form_meta']['indikator_kode'] ?? null,
                        'indikator_label' => $formEntry['form_meta']['indikator_label'] ?? null,
                        'available_question_count' => (int) ($formEntry['available_question_count'] ?? 0),
                        'selected_question_count' => $selectedCount,
                        'selection_mode' => 'fixed_all',
                    ];
                })
                ->values()
                ->all(),
            'auto_included_form_count' => count($context['auto_included_forms'] ?? []),
            'auto_included_question_count' => (int) $refreshedRows->whereNull('kompetensi')->count(),
            'selected_question_count' => (int) $refreshedRows->count(),
        ];

        $selectionConfig['assessments'] = $assessmentEntries
            ->reject(fn ($entry) => (int) ($entry['assessment_id'] ?? 0) === (int) $assessment->id)
            ->push($selectionAssessment)
            ->values()
            ->all();

        if (blank($selectionConfig['target_ketenagaan'] ?? null)) {
            $selectionConfig['target_ketenagaan'] = $assessment->target_ketenagaan;
        }

        return $selectionConfig;
    }

    private function isAssessmentEligibleForCombination(
        Assessment $assessment,
        ?string $targetKetenagaan
    ): bool {
        return (bool) $assessment->is_active
            && $assessment->status === 'publish'
            && ($targetKetenagaan === null || $assessment->target_ketenagaan === $targetKetenagaan);
    }

    private function resolveSelectionSeed(string $randomSeed, int $assessmentId, string|int $selector): int
    {
        return (int) sprintf(
            '%u',
            crc32(implode('|', [
                'assessment-combination',
                $randomSeed,
                $assessmentId,
                (string) $selector,
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
