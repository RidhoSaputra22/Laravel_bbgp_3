<?php

namespace App\Http\Controllers;

use App\Enum\AssessmentKetenagaanType;
use App\Enum\KompetensiGuru;
use App\Models\Assessment;
use App\Models\AssessmentCombination;
use App\Models\AssessmentCombinationGeneration;
use App\Models\JabatanKependidikan;
use App\Models\JabatanPendidik;
use App\Models\JabatanStakeHolder;
use App\Services\Assessment\AssessmentCombinationGenerationService;
use App\Services\Assessment\AssessmentCombinationService;
use App\Services\AssessmentAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AssessmentCombinationController extends Controller
{
    private string $menu = 'assessment-kombinasi';

    public function __construct(
        private readonly AssessmentCombinationService $combinationService,
        private readonly AssessmentCombinationGenerationService $generationService,
        private readonly AssessmentAssignmentService $assignmentService
    ) {}

    public function index()
    {
        $this->authorizeAccess();

        $resetSourceGenerationIds = $this->pendingResetSourceGenerationIds();

        $datasQuery = AssessmentCombination::query()
            ->select($this->indexCombinationSelectColumns())
            ->with([
                'generator:id,name',
                'generation:id,kode_generate',
            ])
            ->withCount(['items', 'assignments', 'assignmentTargets'])
            ->orderByDesc('id');

        if ($resetSourceGenerationIds !== []) {
            $datasQuery->whereNotIn('assessment_combination_generation_id', $resetSourceGenerationIds);
        }

        $datas = $datasQuery->get();

        $generationsQuery = AssessmentCombinationGeneration::query()
            ->select($this->indexGenerationSelectColumns())
            ->with('generator:id,name')
            ->withCount('combinations')
            ->orderByDesc('id');

        if ($resetSourceGenerationIds !== []) {
            $generationsQuery->whereNotIn('id', $resetSourceGenerationIds);
        }

        $generations = $generationsQuery->get();
        $generationMonitoring = $generations
            ->mapWithKeys(function (AssessmentCombinationGeneration $generation) {
                return [
                    $generation->id => $this->generationService->buildGenerationMonitoring($generation, false),
                ];
            })
            ->all();
        $generationAssignmentUsage = $generations
            ->mapWithKeys(function (AssessmentCombinationGeneration $generation) {
                return [
                    $generation->id => $this->assignmentService->countAssignmentsForCombinationGeneration($generation),
                ];
            })
            ->all();

        return view('pages.admin.assessment.combination.index', [
            'menu' => $this->menu,
            'datas' => $datas,
            'generations' => $generations,
            'generationMonitoring' => $generationMonitoring,
            'generationAssignmentUsage' => $generationAssignmentUsage,
        ]);
    }

    public function create()
    {
        $this->authorizeAccess();

        return view(
            'pages.admin.assessment.combination.create',
            $this->buildFormViewData()
        );
    }

    public function store(Request $request)
    {
        $this->authorizeAccess();

        $validated = $this->validatePayload($request);

        try {
            $generation = $this->generationService->createGeneration(
                $validated,
                session('user_id') ? (int) session('user_id') : null
            );

            return redirect()
                ->route('assessment.combination.generation.show', $generation->id)
                ->with('combination_notice', $this->buildGenerationCreateNotice($generation));
        } catch (\Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors([
                    'combination' => 'Terjadi kesalahan saat membuat kombinasi soal.',
                ]);
        }
    }

    public function show(string $id)
    {
        $this->authorizeAccess();

        $combination = AssessmentCombination::query()
            ->with(['generator', 'items', 'assignments', 'assignmentTargets', 'generation'])
            ->withCount(['assignments', 'assignmentTargets'])
            ->findOrFail($id);

        return view('pages.admin.assessment.combination.show', [
            'menu' => $this->menu,
            'combination' => $combination,
            'snapshot' => $combination->structure_snapshot ?? [],
        ]);
    }

    public function generationShow(string $id)
    {
        $this->authorizeAccess();

        $generation = AssessmentCombinationGeneration::query()
            ->select($this->generationShowSelectColumns())
            ->with([
                'generator:id,name',
                'combinations' => function ($query) {
                    $query->select($this->generationShowCombinationSelectColumns())
                        ->with(['generator:id,name'])
                        ->withCount(['items', 'assignments', 'assignmentTargets']);
                },
            ])
            ->withCount('combinations')
            ->findOrFail($id);

        return view('pages.admin.assessment.combination.generation-show', [
            'menu' => $this->menu,
            'generation' => $generation,
            'monitoring' => $this->generationService->buildGenerationMonitoring($generation),
        ]);
    }

    public function editGeneration(string $id)
    {
        $this->authorizeAccess();

        $generation = AssessmentCombinationGeneration::query()->findOrFail($id);

        return view(
            'pages.admin.assessment.combination.create',
            $this->buildFormViewData($generation)
        );
    }

    public function retryGeneration(string $id)
    {
        $this->authorizeAccess();

        $generation = AssessmentCombinationGeneration::query()->findOrFail($id);

        try {
            $result = $this->generationService->retryGeneration($generation);

            /** @var \App\Models\AssessmentCombinationGeneration $retriedGeneration */
            $retriedGeneration = $result['generation'];

            return redirect()
                ->route('assessment.combination.generation.show', $retriedGeneration->id)
                ->with('combination_notice', $this->buildGenerationRetryNotice($result));
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()
                ->route('assessment.combination.generation.show', $generation->id)
                ->withErrors([
                    'combination' => $exception->getMessage() !== ''
                        ? $exception->getMessage()
                        : 'Terjadi kesalahan saat menjalankan retry generate kombinasi soal.',
                ]);
        }
    }

    public function resetGeneration(Request $request, string $id)
    {
        $this->authorizeAccess();

        if (! $this->hasResetSourceGenerationColumn()) {
            return back()->withErrors([
                'combination' => 'Migration reset kombinasi belum dijalankan. Jalankan php artisan migrate terlebih dahulu.',
            ]);
        }

        $generation = AssessmentCombinationGeneration::query()
            ->with('combinations:id,assessment_combination_generation_id,kode_kombinasi')
            ->findOrFail($id);
        $validated = $this->validatePayload($request);
        $replacementGeneration = null;
        $cleanupResult = $this->emptyGenerationCleanupSummary();

        try {
            $this->generationService->cancelGenerationProcessing($generation);
            $replacementGeneration = $this->generationService->createGenerationRecord(
                $validated,
                session('user_id') ? (int) session('user_id') : null
            );
            $replacementGeneration->forceFill([
                'reset_source_generation_id' => $generation->id,
            ])->save();

            $cleanupResult['assignment_count'] = $this->assignmentService
                ->countAssignmentsForCombinationGeneration($generation);
            $replacementGeneration = $this->generationService->dispatchGeneration($replacementGeneration->fresh());

            return redirect()
                ->route('assessment.combination.generation.show', $replacementGeneration->id)
                ->with(
                    'combination_notice',
                    $this->buildGenerationResetNotice($generation, $replacementGeneration, $cleanupResult)
                );
        } catch (\Throwable $exception) {
            report($exception);

            if ($replacementGeneration) {
                try {
                    $this->generationService->deleteGenerationHistory($replacementGeneration->fresh());
                } catch (\Throwable $cleanupException) {
                    report($cleanupException);
                }
            }

            return back()
                ->withInput()
                ->withErrors([
                    'combination' => 'Terjadi kesalahan saat mereset pengaturan generate kombinasi soal.',
                ]);
        }
    }

    public function resetAllGenerations()
    {
        $this->authorizeAccess();

        if (! $this->hasResetSourceGenerationColumn()) {
            return redirect()
                ->route('assessment.combination.index')
                ->withErrors([
                    'combination' => 'Migration reset kombinasi belum dijalankan. Jalankan php artisan migrate terlebih dahulu.',
                ]);
        }

        $pendingResetCount = AssessmentCombinationGeneration::query()
            ->whereNotNull('reset_source_generation_id')
            ->count();

        if ($pendingResetCount > 0) {
            return redirect()
                ->route('assessment.combination.index')
                ->withErrors([
                    'combination' => 'Reset semua belum bisa dijalankan karena masih ada reset penugasan yang diproses job.',
                ]);
        }

        $generations = AssessmentCombinationGeneration::query()
            ->orderBy('id')
            ->get();

        if ($generations->isEmpty()) {
            return redirect()
                ->route('assessment.combination.index')
                ->with('combination_notice', 'Belum ada kombinasi soal yang perlu direset.');
        }

        $replacementGenerations = [];
        $assignmentCount = 0;

        try {
            foreach ($generations as $generation) {
                $this->generationService->cancelGenerationProcessing($generation);
                $assignmentCount += $this->assignmentService
                    ->countAssignmentsForCombinationGeneration($generation);

                $replacementGeneration = $this->generationService->createReplacementGeneration(
                    $generation,
                    session('user_id') ? (int) session('user_id') : null
                );
                $replacementGenerations[] = $replacementGeneration;
                $this->generationService->dispatchGeneration($replacementGeneration);
            }

            $parts = [
                'Reset semua kombinasi untuk '.$generations->count().' proses generate berhasil dikirim ke antrean.',
                'Penugasan tetap dipertahankan dan akan dialihkan ke kombinasi baru oleh job.',
            ];

            if ($assignmentCount > 0) {
                $parts[] = $assignmentCount.' penugasan menunggu proses reset.';
            }

            return redirect()
                ->route('assessment.combination.index')
                ->with('combination_notice', implode(' ', $parts));
        } catch (\Throwable $exception) {
            report($exception);

            foreach ($replacementGenerations as $replacementGeneration) {
                try {
                    $this->generationService->deleteGenerationHistory($replacementGeneration->fresh());
                } catch (\Throwable $cleanupException) {
                    report($cleanupException);
                }
            }

            return redirect()
                ->route('assessment.combination.index')
                ->withErrors([
                    'combination' => 'Terjadi kesalahan saat mereset semua kombinasi soal.',
                ]);
        }
    }

    public function destroyGeneration(string $id)
    {
        $this->authorizeAccess();

        $generation = AssessmentCombinationGeneration::query()
            ->with('combinations:id,assessment_combination_generation_id,kode_kombinasi')
            ->findOrFail($id);

        try {
            $result = $this->assignmentService->deleteAssignmentsForCombinationGeneration($generation);
            $this->generationService->deleteGenerationHistory($generation);

            return redirect()
                ->route('assessment.combination.index')
                ->with('combination_notice', $this->buildGenerationDeleteNotice($generation, $result));
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()
                ->route('assessment.combination.index')
                ->withErrors([
                    'combination' => 'Terjadi kesalahan saat menghapus riwayat generate.',
                ]);
        }
    }

    public function destroy(string $id)
    {
        $this->authorizeAccess();

        $combination = AssessmentCombination::query()
            ->withCount(['assignments', 'assignmentTargets'])
            ->findOrFail($id);

        if ($combination->assignments_count > 0 || $combination->assignment_targets_count > 0) {
            return redirect()
                ->route('assessment.combination.index')
                ->withErrors([
                    'combination' => 'Kombinasi soal tidak bisa dihapus karena sudah dipakai pada penugasan assessment.',
                ]);
        }

        $combination->delete();

        return redirect()
            ->route('assessment.combination.index')
            ->with('combination_notice', 'Kombinasi soal berhasil dihapus.');
    }

    private function authorizeAccess(): void
    {
        abort_unless(
            in_array(session('role'), ['admin', 'superadmin', 'kepala', 'database'], true),
            403
        );
    }

    private function hasResetSourceGenerationColumn(): bool
    {
        return Schema::hasColumn('assessment_combination_generations', 'reset_source_generation_id');
    }

    private function pendingResetSourceGenerationIds(): array
    {
        if (! $this->hasResetSourceGenerationColumn()) {
            return [];
        }

        return AssessmentCombinationGeneration::query()
            ->whereNotNull('reset_source_generation_id')
            ->pluck('reset_source_generation_id')
            ->map(fn ($generationId) => (int) $generationId)
            ->filter(fn (int $generationId) => $generationId > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function buildFormViewData(?AssessmentCombinationGeneration $generation = null): array
    {
        $initialFormData = $this->buildInitialFormDataFromGeneration($generation);

        return [
            'menu' => $this->menu,
            'generation' => $generation,
            'isEditMode' => $generation !== null,
            'pageTitle' => $generation ? 'Edit & Reset Generate Kombinasi' : 'Buat Kombinasi Soal',
            'formAction' => $generation
                ? route('assessment.combination.generation.reset', $generation->id)
                : route('assessment.combination.store'),
            'formMethod' => 'POST',
            'submitLabel' => $generation ? 'Reset dan Generate Ulang' : 'Kirim ke Antrean Generate',
            'ketenagaanOptions' => AssessmentKetenagaanType::options(),
            'assessmentCatalogByKetenagaan' => $this->combinationService->buildAssessmentCatalogByKetenagaan(),
            'jabatanOptionsByKetenagaan' => $this->buildJabatanOptionsByKetenagaan(),
            'initialFormData' => $initialFormData,
            'relatedAssignmentUsageCount' => $generation
                ? $this->assignmentService->countAssignmentsForCombinationGeneration($generation)
                : 0,
        ];
    }

    private function indexCombinationSelectColumns(): array
    {
        $columns = [
            'id',
            'assessment_combination_generation_id',
            'generation_sequence',
            'kode_kombinasi',
            'target_ketenagaan',
            'total_assessments',
            'total_forms',
            'total_questions',
            'generated_by',
            'generated_at',
            'is_active',
            'created_at',
        ];

        if (Schema::hasColumn('assessment_combinations', 'target_jabatan')) {
            $columns[] = 'target_jabatan';
        }

        return $columns;
    }

    private function indexGenerationSelectColumns(): array
    {
        $columns = [
            'id',
            'kode_generate',
            'target_ketenagaan',
            'total_kombinasi',
            'status',
            'job_batch_id',
            'generated_by',
            'created_at',
        ];

        if (Schema::hasColumn('assessment_combination_generations', 'target_jabatan')) {
            $columns[] = 'target_jabatan';
        }

        if (Schema::hasColumn('assessment_combination_generations', 'reset_source_generation_id')) {
            $columns[] = 'reset_source_generation_id';
        }

        return $columns;
    }

    private function generationShowSelectColumns(): array
    {
        $columns = [
            'id',
            'kode_generate',
            'target_ketenagaan',
            'total_kombinasi',
            'status',
            'job_batch_id',
            'generated_by',
            'created_at',
            'processed_at',
        ];

        if (Schema::hasColumn('assessment_combination_generations', 'target_jabatan')) {
            $columns[] = 'target_jabatan';
        }

        return $columns;
    }

    private function generationShowCombinationSelectColumns(): array
    {
        $columns = [
            'id',
            'assessment_combination_generation_id',
            'generation_sequence',
            'kode_kombinasi',
            'total_assessments',
            'total_forms',
            'total_questions',
            'generated_by',
            'generated_at',
            'created_at',
        ];

        if (Schema::hasColumn('assessment_combinations', 'target_jabatan')) {
            $columns[] = 'target_jabatan';
        }

        return $columns;
    }

    private function buildInitialFormDataFromGeneration(
        ?AssessmentCombinationGeneration $generation
    ): array {
        $selectionConfig = is_array($generation?->selection_config ?? null)
            ? $generation->selection_config
            : [];
        $includedAssessmentIds = $this->normalizeAssessmentIds(
            $selectionConfig['included_assessment_ids'] ?? []
        );

        return [
            'target_ketenagaan' => $selectionConfig['target_ketenagaan']
                ?? $generation?->target_ketenagaan
                ?? AssessmentKetenagaanType::TENAGA_PENDIDIK->value,
            'target_jabatan' => Assessment::normalizeTargetJabatan(
                $selectionConfig['target_jabatan'] ?? $generation?->targetJabatanSelections()
                    ?? [Assessment::TARGET_JABATAN_ALL]
            ) ?: [Assessment::TARGET_JABATAN_ALL],
            'total_kombinasi' => max(
                (int) ($selectionConfig['total_kombinasi'] ?? $generation?->total_kombinasi ?? 1),
                1
            ),
            'included_assessment_ids' => $includedAssessmentIds !== [] ? $includedAssessmentIds : null,
            'competency_selection_modes' => $this->normalizeGenerationAssessmentConfigMap(
                $selectionConfig['competency_selection_modes'] ?? []
            ),
            'competency_take_counts' => $this->normalizeGenerationAssessmentConfigMap(
                $selectionConfig['competency_take_counts'] ?? []
            ),
        ];
    }

    private function normalizeGenerationAssessmentConfigMap(mixed $config): array
    {
        return collect((array) $config)
            ->mapWithKeys(function ($value, $assessmentId) {
                $normalizedAssessmentId = (int) $assessmentId;

                if ($normalizedAssessmentId < 1) {
                    return [];
                }

                return [
                    $normalizedAssessmentId => is_array($value) ? $value : [],
                ];
            })
            ->all();
    }

    private function validatePayload(Request $request): array
    {
        $validator = Validator::make(
            $request->all(),
            [
                'target_ketenagaan' => [
                    'required',
                    'string',
                    Rule::in(array_keys(AssessmentKetenagaanType::options())),
                ],
                'target_jabatan' => 'nullable|array',
                'target_jabatan.*' => 'required|string|max:255',
                'total_kombinasi' => 'required|integer|min:1',
                'included_assessment_ids' => 'required|array|min:1',
                'included_assessment_ids.*' => 'integer|min:1',
                'competency_selection_modes' => 'nullable|array',
                'competency_take_counts' => 'nullable|array',
            ],
            [
                'target_ketenagaan.required' => 'Ketenagaan wajib dipilih.',
                'target_ketenagaan.in' => 'Ketenagaan harus sesuai pilihan yang tersedia.',
                'target_jabatan.array' => 'Pilihan jabatan kombinasi tidak valid.',
                'target_jabatan.*.required' => 'Jabatan kombinasi tidak boleh kosong.',
                'total_kombinasi.required' => 'Jumlah kombinasi yang ingin dibuat wajib diisi.',
                'total_kombinasi.integer' => 'Jumlah kombinasi harus berupa angka bulat.',
                'total_kombinasi.min' => 'Jumlah kombinasi minimal 1.',
                'included_assessment_ids.required' => 'Pilih minimal satu assessment sumber.',
                'included_assessment_ids.array' => 'Pilihan assessment sumber tidak valid.',
                'included_assessment_ids.min' => 'Pilih minimal satu assessment sumber.',
            ]
        );

        $validator->after(function ($validator) use ($request) {
            $targetKetenagaan = AssessmentKetenagaanType::tryFromMixed($request->input('target_ketenagaan'));

            if (! $targetKetenagaan) {
                return;
            }

            $targetJabatan = Assessment::normalizeTargetJabatan(
                $request->input('target_jabatan', [Assessment::TARGET_JABATAN_ALL])
            );
            $targetJabatan = $targetJabatan !== [] ? $targetJabatan : [Assessment::TARGET_JABATAN_ALL];
            $availableJabatanIds = collect($this->buildJabatanOptionsByKetenagaan()[$targetKetenagaan->value] ?? [])
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->all();
            $invalidJabatanIds = collect($targetJabatan)
                ->reject(fn (string $jabatan) => in_array($jabatan, $availableJabatanIds, true))
                ->values()
                ->all();

            if ($invalidJabatanIds !== []) {
                $validator->errors()->add(
                    'target_jabatan',
                    'Ada jabatan yang tidak sesuai dengan ketenagaan kombinasi yang dipilih.'
                );
            }

            $availableAssessments = collect(
                $this->combinationService->buildAssessmentCatalogByKetenagaan(
                    $targetKetenagaan->value,
                    $targetJabatan
                )[$targetKetenagaan->value] ?? []
            )->values();
            $includedAssessmentIds = $this->normalizeAssessmentIds($request->input('included_assessment_ids', []));

            if ($availableAssessments->isEmpty()) {
                $validator->errors()->add(
                    'target_ketenagaan',
                    'Belum ada assessment aktif yang bisa dijadikan kombinasi pada ketenagaan ini.'
                );

                return;
            }

            $selectionModes = collect((array) $request->input('competency_selection_modes', []))
                ->mapWithKeys(function ($modes, $assessmentId) {
                    return [
                        (int) $assessmentId => is_array($modes) ? $modes : [],
                    ];
                });
            $takeCounts = collect((array) $request->input('competency_take_counts', []))
                ->mapWithKeys(function ($counts, $assessmentId) {
                    return [
                        (int) $assessmentId => is_array($counts) ? $counts : [],
                    ];
                });
            $availableAssessmentIds = $availableAssessments
                ->pluck('assessment_id')
                ->map(fn ($assessmentId) => (int) $assessmentId)
                ->all();
            $availableAssessmentLookup = array_fill_keys($availableAssessmentIds, true);
            $invalidIncludedAssessmentIds = collect($includedAssessmentIds)
                ->reject(fn ($assessmentId) => isset($availableAssessmentLookup[(int) $assessmentId]))
                ->values()
                ->all();

            if ($includedAssessmentIds === []) {
                $validator->errors()->add(
                    'included_assessment_ids',
                    'Pilih minimal satu assessment sumber.'
                );

                return;
            }

            if ($invalidIncludedAssessmentIds !== []) {
                $validator->errors()->add(
                    'included_assessment_ids',
                    'Ada assessment yang tidak sesuai dengan ketenagaan atau jabatan kombinasi yang dipilih.'
                );
            }

            $invalidAssessmentIds = $selectionModes
                ->keys()
                ->merge($takeCounts->keys())
                ->unique()
                ->reject(fn ($assessmentId) => in_array((int) $assessmentId, $availableAssessmentIds, true))
                ->values()
                ->all();

            if ($invalidAssessmentIds !== []) {
                $validator->errors()->add(
                    'competency_selection_modes',
                    'Ada assessment yang tidak sesuai dengan ketenagaan atau jabatan kombinasi yang dipilih.'
                );
            }

            $selectedAssessmentLookup = array_fill_keys($includedAssessmentIds, true);
            $selectedAssessments = $availableAssessments
                ->filter(function (array $assessment) use ($selectedAssessmentLookup) {
                    return isset($selectedAssessmentLookup[(int) ($assessment['assessment_id'] ?? 0)]);
                })
                ->values();

            $selectedAssessments->each(function (array $assessment) use ($validator, $selectionModes, $takeCounts) {
                $assessmentId = (int) ($assessment['assessment_id'] ?? 0);
                $assessmentModes = collect((array) $selectionModes->get($assessmentId, []));
                $assessmentCounts = collect((array) $takeCounts->get($assessmentId, []));

                collect(KompetensiGuru::cases())->each(function (KompetensiGuru $kompetensi) use (
                    $validator,
                    $assessment,
                    $assessmentId,
                    $assessmentModes,
                    $assessmentCounts
                ) {
                    $assessmentCompetencies = collect($assessment['competencies'] ?? []);
                    $competencyConfig = $assessmentCompetencies->firstWhere('kompetensi', $kompetensi->value) ?? [];
                    $availableCount = (int) ($competencyConfig['available_question_count'] ?? 0);
                    $mode = $assessmentModes->get($kompetensi->value, 'count');
                    $isAllMode = $mode === 'all';
                    $requestedCount = max((int) ($assessmentCounts->get($kompetensi->value, 0) ?? 0), 0);

                    if ($availableCount < 1) {
                        return;
                    }

                    if (! in_array($mode, ['count', 'all'], true)) {
                        $validator->errors()->add(
                            'competency_selection_modes.'.$assessmentId.'.'.$kompetensi->value,
                            'Mode pengambilan soal untuk '.$kompetensi->label().' tidak valid.'
                        );

                        return;
                    }

                    if ($isAllMode) {
                        return;
                    }

                    if ($requestedCount < 1) {
                        $validator->errors()->add(
                            'competency_take_counts.'.$assessmentId.'.'.$kompetensi->value,
                            'Jumlah soal untuk kompetensi '.$kompetensi->label().' minimal 1 atau pilih semua soal.'
                        );

                        return;
                    }

                    if ($requestedCount > $availableCount) {
                        $validator->errors()->add(
                            'competency_take_counts.'.$assessmentId.'.'.$kompetensi->value,
                            'Jumlah soal kompetensi '.$kompetensi->label().' tidak boleh melebihi '.$availableCount.' soal aktif.'
                        );
                    }
                });
            });
        });

        $validated = $validator->validate();
        $validated['included_assessment_ids'] = $this->normalizeAssessmentIds(
            $validated['included_assessment_ids'] ?? []
        );
        $validated['target_jabatan'] = Assessment::normalizeTargetJabatan(
            $validated['target_jabatan'] ?? [Assessment::TARGET_JABATAN_ALL]
        ) ?: [Assessment::TARGET_JABATAN_ALL];

        return $validated;
    }

    private function buildJabatanOptionsByKetenagaan(): array
    {
        return collect(AssessmentKetenagaanType::cases())
            ->mapWithKeys(function (AssessmentKetenagaanType $case) {
                $items = [[
                    'id' => Assessment::TARGET_JABATAN_ALL,
                    'label' => 'Semua Jabatan',
                    'description' => 'Seluruh jabatan pada '.$case->label(),
                    'cells' => ['Semua Jabatan', 'Seluruh jabatan'],
                    'payload' => [
                        'ketenagaan' => $case->value,
                        'ketenagaan_label' => $case->label(),
                        'is_all' => true,
                    ],
                ]];

                foreach ($this->masterJabatanValuesForKetenagaan($case) as $jabatan) {
                    $items[] = [
                        'id' => $jabatan,
                        'label' => $jabatan,
                        'description' => 'Jabatan pada '.$case->label(),
                        'cells' => [$jabatan, 'Jabatan terkait'],
                        'payload' => [
                            'jenis_jabatan' => $jabatan,
                            'ketenagaan' => $case->value,
                            'ketenagaan_label' => $case->label(),
                        ],
                    ];
                }

                return [$case->value => $items];
            })
            ->all();
    }

    private function masterJabatanValuesForKetenagaan(AssessmentKetenagaanType $case): array
    {
        $model = match ($case) {
            AssessmentKetenagaanType::TENAGA_PENDIDIK => JabatanPendidik::class,
            AssessmentKetenagaanType::TENAGA_KEPENDIDIKAN => JabatanKependidikan::class,
            AssessmentKetenagaanType::STAKEHOLDER => JabatanStakeHolder::class,
        };
        $table = (new $model)->getTable();

        if (! Schema::hasTable($table)) {
            return [];
        }

        return $model::query()
            ->orderBy('name')
            ->pluck('name')
            ->map(fn ($jabatan) => trim((string) $jabatan))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function normalizeAssessmentIds(mixed $assessmentIds): array
    {
        return collect((array) $assessmentIds)
            ->map(fn ($assessmentId) => (int) $assessmentId)
            ->filter(fn (int $assessmentId) => $assessmentId > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function buildGenerationCreateNotice(AssessmentCombinationGeneration $generation): string
    {
        return 'Permintaan generate '
            .$generation->total_kombinasi
            .' kombinasi dikirim ke antrean batch. Pantau progresnya pada halaman ini.';
    }

    private function buildGenerationRetryNotice(array $result): string
    {
        if ($result['already_complete'] ?? false) {
            return 'Tidak ada kombinasi yang perlu di-resume. Semua hasil sudah lengkap.';
        }

        $actionLabel = ($result['all_failed'] ?? false) ? 'Retry semua' : 'Resume sisa gagal';

        return $actionLabel
            .' dijalankan untuk '
            .($result['resumed_count'] ?? 0)
            .' kombinasi melalui batch job.';
    }

    private function buildGenerationDeleteNotice(
        AssessmentCombinationGeneration $generation,
        array $result
    ): string {
        $deletedAssignmentCount = (int) ($result['deleted_assignment_count'] ?? 0);
        $parts = [
            'Riwayat generate '.$generation->kode_generate.' berhasil dihapus.',
        ];

        if ($deletedAssignmentCount < 1) {
            $parts[] = 'Kombinasi soal dari riwayat tersebut ikut dihapus.';

            return implode(' ', $parts);
        }

        $parts[] = $deletedAssignmentCount.' penugasan assessment terkait dihapus permanen.';

        if (($result['deleted_target_count'] ?? 0) > 0) {
            $parts[] = $result['deleted_target_count'].' target penugasan dibersihkan.';
        }

        if (($result['deleted_attempt_count'] ?? 0) > 0) {
            $parts[] = $result['deleted_attempt_count'].' riwayat pengerjaan dihapus.';
        }

        if (($result['deleted_answer_count'] ?? 0) > 0) {
            $parts[] = $result['deleted_answer_count'].' jawaban peserta dihapus.';
        }

        if (($result['deleted_file_count'] ?? 0) > 0) {
            $parts[] = $result['deleted_file_count'].' file unggahan ikut dihapus.';
        }

        $parts[] = 'Kombinasi soal dari riwayat tersebut ikut dihapus.';

        return implode(' ', $parts);
    }

    private function buildGenerationResetNotice(
        AssessmentCombinationGeneration $generation,
        AssessmentCombinationGeneration $replacementGeneration,
        array $result
    ): string {
        $parts = [
            'Pengaturan '.$generation->kode_generate.' berhasil direset.',
            'Proses baru '.$replacementGeneration->kode_generate.' dikirim ke antrean batch.',
            'Penugasan assessment terkait tetap dipertahankan dan akan direset otomatis memakai kombinasi baru setelah generate selesai.',
        ];

        if (($result['assignment_count'] ?? 0) > 0) {
            $parts[] = ($result['assignment_count']).' penugasan menunggu proses reset melalui job.';
        }

        $parts[] = 'Kombinasi lama akan dibersihkan setelah seluruh reset penugasan berhasil.';

        return implode(' ', $parts);
    }

    private function emptyGenerationCleanupSummary(): array
    {
        return [
            'combination_count' => 0,
            'assignment_count' => 0,
        ];
    }
}
