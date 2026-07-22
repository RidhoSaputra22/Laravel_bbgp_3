<?php

namespace App\Http\Controllers;

use App\Enum\AssessmentKetenagaanType;
use App\Enum\KompetensiGuru;
use App\Models\AssessmentCombination;
use App\Models\AssessmentCombinationGeneration;
use App\Services\Assessment\AssessmentCombinationGenerationService;
use App\Services\Assessment\AssessmentCombinationService;
use App\Services\AssessmentAssignmentService;
use Illuminate\Http\Request;
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

        $datas = AssessmentCombination::query()
            ->with(['generator', 'generation'])
            ->withCount(['items', 'assignments', 'assignmentTargets'])
            ->orderByDesc('id')
            ->get();
        $generations = AssessmentCombinationGeneration::query()
            ->with('generator')
            ->withCount('combinations')
            ->orderByDesc('id')
            ->get();
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

        return view('pages.admin.assessment.combination.create', [
            'menu' => $this->menu,
            'ketenagaanOptions' => AssessmentKetenagaanType::options(),
            'assessmentCatalogByKetenagaan' => $this->combinationService->buildAssessmentCatalogByKetenagaan(),
        ]);
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
            ->with([
                'generator',
                'combinations' => function ($query) {
                    $query->with(['generator'])
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

    private function validatePayload(Request $request): array
    {
        $assessmentCatalogByKetenagaan = $this->combinationService->buildAssessmentCatalogByKetenagaan();

        $validator = Validator::make(
            $request->all(),
            [
                'target_ketenagaan' => [
                    'required',
                    'string',
                    Rule::in(array_keys(AssessmentKetenagaanType::options())),
                ],
                'total_kombinasi' => 'required|integer|min:1',
                'competency_selection_modes' => 'required|array|min:1',
                'competency_take_counts' => 'required|array|min:1',
            ],
            [
                'target_ketenagaan.required' => 'Ketenagaan wajib dipilih.',
                'target_ketenagaan.in' => 'Ketenagaan harus sesuai pilihan yang tersedia.',
                'total_kombinasi.required' => 'Jumlah kombinasi yang ingin dibuat wajib diisi.',
                'total_kombinasi.integer' => 'Jumlah kombinasi harus berupa angka bulat.',
                'total_kombinasi.min' => 'Jumlah kombinasi minimal 1.',
                'competency_selection_modes.required' => 'Konfigurasi kompetensi kombinasi wajib diisi.',
                'competency_take_counts.required' => 'Jumlah soal kompetensi wajib diisi.',
            ]
        );

        $validator->after(function ($validator) use ($request, $assessmentCatalogByKetenagaan) {
            $targetKetenagaan = AssessmentKetenagaanType::tryFromMixed($request->input('target_ketenagaan'));

            if (! $targetKetenagaan) {
                return;
            }

            $availableAssessments = collect($assessmentCatalogByKetenagaan[$targetKetenagaan->value] ?? [])->values();

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
                    'Ada assessment yang tidak sesuai dengan ketenagaan kombinasi yang dipilih.'
                );
            }

            $availableAssessments->each(function (array $assessment) use ($validator, $selectionModes, $takeCounts) {
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

        return $validator->validate();
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
}
