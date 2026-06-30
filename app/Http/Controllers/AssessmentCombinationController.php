<?php

namespace App\Http\Controllers;

use App\Enum\AssessmentKetenagaanType;
use App\Enum\KompetensiGuru;
use App\Models\AssessmentCombination;
use App\Services\Assessment\AssessmentCombinationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AssessmentCombinationController extends Controller
{
    private string $menu = 'assessment-kombinasi';

    public function __construct(
        private readonly AssessmentCombinationService $combinationService
    ) {}

    public function index()
    {
        $this->authorizeAccess();

        $datas = AssessmentCombination::query()
            ->with('generator')
            ->withCount(['items', 'assignments'])
            ->orderByDesc('id')
            ->get();

        return view('pages.admin.assessment.combination.index', [
            'menu' => $this->menu,
            'datas' => $datas,
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
            $combination = $this->combinationService->createCombination(
                $validated,
                session('user_id') ? (int) session('user_id') : null
            );

            return redirect()
                ->route('assessment.combination.show', $combination->id)
                ->with('message', 'store');
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
            ->with(['generator', 'items', 'assignments'])
            ->findOrFail($id);

        return view('pages.admin.assessment.combination.show', [
            'menu' => $this->menu,
            'combination' => $combination,
            'snapshot' => $combination->structure_snapshot ?? [],
        ]);
    }

    public function destroy(string $id)
    {
        $this->authorizeAccess();

        $combination = AssessmentCombination::query()
            ->withCount('assignments')
            ->findOrFail($id);

        if ($combination->assignments_count > 0) {
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
                'competency_selection_modes' => 'required|array|min:1',
                'competency_take_counts' => 'required|array|min:1',
            ],
            [
                'target_ketenagaan.required' => 'Ketenagaan wajib dipilih.',
                'target_ketenagaan.in' => 'Ketenagaan harus sesuai pilihan yang tersedia.',
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
}
