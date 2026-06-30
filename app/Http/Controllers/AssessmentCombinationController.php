<?php

namespace App\Http\Controllers;

use App\Enum\AssessmentKetenagaanType;
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
            'formCatalogByKetenagaan' => $this->combinationService->buildFormCatalogByKetenagaan(),
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
        $formCatalogByKetenagaan = $this->combinationService->buildFormCatalogByKetenagaan();

        $validator = Validator::make(
            $request->all(),
            [
                'judul' => 'required|string|max:255',
                'deskripsi' => 'nullable|string',
                'target_ketenagaan' => [
                    'required',
                    'string',
                    Rule::in(array_keys(AssessmentKetenagaanType::options())),
                ],
                'form_take_counts' => 'required|array|min:1',
            ],
            [
                'judul.required' => 'Judul kombinasi wajib diisi.',
                'target_ketenagaan.required' => 'Ketenagaan wajib dipilih.',
                'target_ketenagaan.in' => 'Ketenagaan harus sesuai pilihan yang tersedia.',
                'form_take_counts.required' => 'Konfigurasi form kombinasi wajib diisi.',
            ]
        );

        $validator->after(function ($validator) use ($request, $formCatalogByKetenagaan) {
            $targetKetenagaan = AssessmentKetenagaanType::tryFromMixed($request->input('target_ketenagaan'));

            if (! $targetKetenagaan) {
                return;
            }

            $availableForms = collect($formCatalogByKetenagaan[$targetKetenagaan->value] ?? [])->values();

            if ($availableForms->isEmpty()) {
                $validator->errors()->add(
                    'target_ketenagaan',
                    'Belum ada form aktif yang bisa dijadikan kombinasi pada ketenagaan ini.'
                );

                return;
            }

            $takeCounts = collect((array) $request->input('form_take_counts', []))
                ->mapWithKeys(function ($count, $formId) {
                    return [
                        (int) $formId => max((int) $count, 0),
                    ];
                });
            $availableFormIds = $availableForms
                ->pluck('form_id')
                ->map(fn ($formId) => (int) $formId)
                ->all();
            $invalidFormIds = $takeCounts
                ->keys()
                ->reject(fn ($formId) => in_array((int) $formId, $availableFormIds, true))
                ->values()
                ->all();

            if ($invalidFormIds !== []) {
                $validator->errors()->add(
                    'form_take_counts',
                    'Ada form yang tidak sesuai dengan ketenagaan kombinasi yang dipilih.'
                );
            }

            $availableForms->each(function (array $form) use ($validator, $takeCounts) {
                $formId = (int) $form['form_id'];
                $requestedCount = (int) ($takeCounts->get($formId, 0) ?? 0);
                $maxCount = (int) ($form['available_question_count'] ?? 0);

                if ($requestedCount < 1) {
                    $validator->errors()->add(
                        'form_take_counts.'.$formId,
                        'Jumlah soal untuk setiap form minimal 1.'
                    );

                    return;
                }

                if ($requestedCount > $maxCount) {
                    $validator->errors()->add(
                        'form_take_counts.'.$formId,
                        'Jumlah soal tidak boleh melebihi '.$maxCount.' soal aktif pada form ini.'
                    );
                }
            });
        });

        return $validator->validate();
    }
}
