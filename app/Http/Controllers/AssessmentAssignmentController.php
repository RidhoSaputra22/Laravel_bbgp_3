<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AssessmentAssignment;
use App\Models\AssessmentForm;
use App\Models\AssessmentFormField;
use App\Models\Guru;
use App\Models\JabatanKependidikan;
use App\Models\JabatanPendidik;
use App\Models\JabatanStakeHolder;
use App\Services\Assessment\AssessmentAttemptLifecycleService;
use App\Services\AssessmentAssignmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AssessmentAssignmentController extends Controller
{
    private const GURU_PAGE_SIZE = 10;

    private string $menu = 'assessment-penugasan';

    public function __construct(
        private readonly AssessmentAssignmentService $assignmentService,
        private readonly AssessmentAttemptLifecycleService $attemptLifecycleService
    ) {}

    public function index()
    {
        $this->authorizeAccess();

        $datas = AssessmentAssignment::with(['assessments', 'creator'])
            ->withCount(['targets', 'sessions'])
            ->orderByDesc('id')
            ->get();

        return view('pages.admin.assessment.assignment.index', [
            'menu' => $this->menu,
            'datas' => $datas,
        ]);
    }

    public function create()
    {
        $this->authorizeAccess();

        $assessmentList = Assessment::query()
            ->select([
                'id',
                'kode_assessment',
                'judul',
                'status',
            ])
            ->selectSub(
                AssessmentForm::query()
                    ->selectRaw('count(*)')
                    ->whereColumn('assessment_id', 'assessments.id')
                    ->where('is_active', true),
                'forms_count'
            )
            ->selectSub(
                AssessmentFormField::query()
                    ->selectRaw('count(*)')
                    ->join('assessment_forms', 'assessment_forms.id', '=', 'assessment_form_fields.assessment_form_id')
                    ->whereColumn('assessment_forms.assessment_id', 'assessments.id')
                    ->where('assessment_forms.is_active', true)
                    ->where('assessment_form_fields.is_active', true),
                'fields_count'
            )
            ->where('assessments.is_active', true)
            ->whereIn('status', ['draft', 'publish'])
            ->orderBy('judul')
            ->get();

        $selectedGuruIds = collect(old('guru_ids', []))
            ->filter(fn ($id) => filled($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $selectedGuruItems = $this->buildSelectedGuruItems($selectedGuruIds);

        return view('pages.admin.assessment.assignment.create', [
            'menu' => $this->menu,
            'assessmentList' => $assessmentList,
            'selectedGuruIds' => $selectedGuruIds,
            'selectedGuruItems' => $selectedGuruItems,
            'batchThreshold' => AssessmentAssignmentService::BATCH_THRESHOLD,
            'sessionCapacity' => AssessmentAssignmentService::TARGETS_PER_SESSION,
            'defaultSessionDurationHours' => AssessmentAssignmentService::DEFAULT_SESSION_DURATION_HOURS,
            'sessionDurationOptions' => AssessmentAssignmentService::SESSION_DURATION_OPTIONS,
        ]);
    }

    public function guruOptions(Request $request): JsonResponse
    {
        $this->authorizeAccess();

        $query = $this->guruSelectionQuery();

        $requestedIds = collect($request->input('ids', []))
            ->filter(fn ($id) => filled($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($requestedIds->isNotEmpty()) {
            $guruById = $query
                ->whereIn('id', $requestedIds->all())
                ->get()
                ->keyBy('id');

            return response()->json([
                'items' => $requestedIds
                    ->map(fn (int $guruId) => $guruById->get($guruId))
                    ->filter()
                    ->map(fn (Guru $guru) => $this->transformGuruTableItem($guru))
                    ->values()
                    ->all(),
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => $requestedIds->count(),
                    'total' => $requestedIds->count(),
                    'from' => $requestedIds->isEmpty() ? 0 : 1,
                    'to' => $requestedIds->count(),
                ],
            ]);
        }

        $keyword = trim((string) $request->input('q', ''));

        if ($keyword !== '') {
            $query->where(function ($builder) use ($keyword) {
                $builder->where('nama_lengkap', 'like', '%'.$keyword.'%')
                    ->orWhere('email', 'like', '%'.$keyword.'%')
                    ->orWhere('satuan_pendidikan', 'like', '%'.$keyword.'%')
                    ->orWhere('kabupaten', 'like', '%'.$keyword.'%');
            });
        }

        $perPage = max(5, min((int) $request->input('per_page', self::GURU_PAGE_SIZE), 50));
        $page = max((int) $request->input('page', 1), 1);
        $paginator = $query
            ->orderBy('nama_lengkap')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'items' => $paginator->getCollection()
                ->map(fn (Guru $guru) => $this->transformGuruTableItem($guru))
                ->values()
                ->all(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem() ?? 0,
                'to' => $paginator->lastItem() ?? 0,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeAccess();

        $validated = $this->validatePayload($request);

        try {
            $assignment = $this->assignmentService->createAssignment(
                $validated,
                session('user_id') ? (int) session('user_id') : null
            );

            return redirect()
                ->route('assessment.assignment.show', $assignment->id)
                ->with('message', 'store');
        } catch (\Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors([
                    'assignment' => 'Terjadi kesalahan saat memproses penugasan assessment.',
                ]);
        }
    }

    public function show(string $id)
    {
        $this->authorizeAccess();

        $assignment = AssessmentAssignment::with([
            'assessments.forms.fields',
            'creator',
            'sessions.targets',
            'targets.guru',
            'targets.session',
            'targets.attempt',
        ])
            ->withCount(['targets', 'sessions'])
            ->findOrFail($id);

        $this->attemptLifecycleService->syncExpiredTargets($assignment->targets);
        $assignment->load([
            'assessments.forms.fields',
            'creator',
            'sessions.targets',
            'targets.guru',
            'targets.session',
            'targets.attempt',
        ]);

        return view('pages.admin.assessment.assignment.show', [
            'menu' => $this->menu,
            'assignment' => $assignment,
        ]);
    }

    private function authorizeAccess(): void
    {
        abort_unless(
            in_array(session('role'), ['admin', 'superadmin', 'kepala', 'database'], true),
            403
        );
    }

    private function guruSelectionQuery()
    {
        return Guru::query()
            ->select([
                'id',
                'nama_lengkap',
                'email',
                'satuan_pendidikan',
                'kabupaten',
                'eksternal_jabatan',
                'jenis_jabatan',
                'status_kepegawaian',
                'is_verif',
            ]);
    }

    private function resolveGuruSelectionMode(Request $request): string
    {
        $selectionMode = trim((string) $request->input('guru_selection_mode', 'manual'));

        return $selectionMode === 'select_all' ? 'select_all' : 'manual';
    }

    private function normalizeGuruIdList(array $guruIds): array
    {
        return collect($guruIds)
            ->filter(fn ($guruId) => filled($guruId))
            ->map(fn ($guruId) => (int) $guruId)
            ->filter(fn (int $guruId) => $guruId > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function normalizeGuruSelectionScope(array $scope): array
    {
        $nestedFilters = data_get($scope, 'filters', []);
        $filters = array_filter([
            'eksternal_jabatan' => trim((string) data_get($nestedFilters, 'eksternal_jabatan', data_get($scope, 'eksternal_jabatan', ''))),
            'jenis_jabatan' => trim((string) data_get($nestedFilters, 'jenis_jabatan', data_get($scope, 'jenis_jabatan', ''))),
        ], fn (string $value) => $value !== '');

        return [
            'q' => trim((string) data_get($scope, 'q', '')),
            'filters' => $filters,
        ];
    }

    private function applyGuruSelectionScope($query, array $scope): void
    {
        $normalizedScope = $this->normalizeGuruSelectionScope($scope);
        $keyword = $normalizedScope['q'];

        foreach ($normalizedScope['filters'] as $column => $value) {
            $query->where($column, $value);
        }

        if ($keyword === '') {
            return;
        }

        $query->where(function ($builder) use ($keyword) {
            $builder->where('nama_lengkap', 'like', '%'.$keyword.'%')
                ->orWhere('email', 'like', '%'.$keyword.'%')
                ->orWhere('eksternal_jabatan', 'like', '%'.$keyword.'%')
                ->orWhere('jenis_jabatan', 'like', '%'.$keyword.'%')
                ->orWhere('satuan_pendidikan', 'like', '%'.$keyword.'%')
                ->orWhere('kabupaten', 'like', '%'.$keyword.'%');
        });
    }

    private function countGuruSelectionByScope(array $scope, array $excludedIds = []): int
    {
        $query = Guru::query();

        $this->applyGuruSelectionScope($query, $scope);

        if ($excludedIds !== []) {
            $query->whereNotIn('id', $this->normalizeGuruIdList($excludedIds));
        }

        return (int) $query->count();
    }

    private function buildSelectedGuruItems(array $selectedGuruIds): array
    {
        if ($selectedGuruIds === []) {
            return [];
        }

        $guruById = $this->guruSelectionQuery()
            ->whereIn('id', $selectedGuruIds)
            ->get()
            ->keyBy('id');

        return collect($selectedGuruIds)
            ->map(fn (int $guruId) => $guruById->get($guruId))
            ->filter()
            ->map(fn (Guru $guru) => $this->transformGuruTableItem($guru))
            ->values()
            ->all();
    }

    private function transformGuruTableItem(Guru $guru): array
    {
        $descriptionParts = array_filter([
            $guru->email ?: null,
            $guru->satuan_pendidikan ?: 'Instansi belum diisi',
            $guru->kabupaten ?: 'Kabupaten belum diisi',
            $guru->is_verif === 'sudah' ? 'Terverifikasi' : 'Belum verifikasi',
        ]);

        return [
            'id' => (string) $guru->id,
            'label' => $guru->nama_lengkap,
            'description' => implode(' | ', $descriptionParts),
            'cells' => [
                $guru->nama_lengkap,
                $guru->email ?: '-',
                $guru->satuan_pendidikan ?: 'Instansi belum diisi',
                $guru->kabupaten ?: 'Kabupaten belum diisi',
                $guru->is_verif === 'sudah' ? 'Terverifikasi' : 'Belum verifikasi',
            ],
            'payload' => [
                'nama' => $guru->nama_lengkap,
                'email' => $guru->email,
                'satuan_pendidikan' => $guru->satuan_pendidikan,
                'kabupaten' => $guru->kabupaten,
                'status_verifikasi' => $guru->is_verif === 'sudah' ? 'Terverifikasi' : 'Belum verifikasi',
                'status_kepegawaian' => $guru->status_kepegawaian,
            ],
        ];
    }

    private function validatePayload(Request $request): array
    {
        $validator = Validator::make(
            $request->all(),
            [
                'judul_penugasan' => 'required|string|max:255',
                'assessment_ids' => 'required|array|min:1',
                'assessment_ids.*' => [
                    'required',
                    'integer',
                    'distinct',
                    Rule::exists('assessments', 'id')->where(function ($query) {
                        $query->where('is_active', true)
                            ->whereIn('status', ['draft', 'publish']);
                    }),
                ],
                'deskripsi' => 'nullable|string',
                'tanggal_mulai' => 'nullable|date|required_with:jam_mulai',
                'jam_mulai' => 'nullable|date_format:H:i|required_with:tanggal_mulai',
                'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
                'durasi_sesi_jam' => [
                    'required',
                    'integer',
                    Rule::in(AssessmentAssignmentService::SESSION_DURATION_OPTIONS),
                ],
                'guru_selection_mode' => 'nullable|string|in:manual,select_all',
                'guru_ids' => 'nullable|array',
                'guru_ids.*' => 'required|integer|distinct|exists:gurus,id',
                'guru_selection_scope' => 'nullable|array',
                'guru_selection_scope.*' => 'nullable|string',
                'guru_excluded_ids' => 'nullable|array',
                'guru_excluded_ids.*' => 'required|integer|distinct|exists:gurus,id',
            ],
            [
                'judul_penugasan.required' => 'Judul penugasan wajib diisi.',
                'assessment_ids.required' => 'Minimal pilih satu form assessment.',
                'assessment_ids.min' => 'Minimal pilih satu form assessment.',
                'assessment_ids.*.exists' => 'Ada form assessment yang dipilih tetapi datanya tidak valid atau sudah nonaktif.',
                'tanggal_mulai.required_with' => 'Tanggal mulai wajib diisi jika jam mulai dipakai.',
                'jam_mulai.required_with' => 'Jam mulai wajib diisi jika tanggal mulai dipakai.',
                'jam_mulai.date_format' => 'Format jam mulai harus berupa HH:MM.',
                'durasi_sesi_jam.required' => 'Durasi sesi assessment wajib dipilih.',
                'durasi_sesi_jam.in' => 'Durasi sesi assessment harus sesuai pilihan yang tersedia.',
                'guru_ids.*.exists' => 'Ada guru yang dipilih tetapi datanya tidak ditemukan.',
                'guru_excluded_ids.*.exists' => 'Ada guru yang dikecualikan tetapi datanya tidak ditemukan.',
                'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            ]
        );

        $validator->after(function ($validator) use ($request) {
            $selectionMode = $this->resolveGuruSelectionMode($request);

            if ($selectionMode === 'select_all') {
                $scope = $this->normalizeGuruSelectionScope($request->input('guru_selection_scope', []));
                $excludedIds = $this->normalizeGuruIdList($request->input('guru_excluded_ids', []));

                if ($this->countGuruSelectionByScope($scope, $excludedIds) < 1) {
                    $validator->errors()->add('guru_ids', 'Minimal pilih satu guru untuk ditugasi.');
                }

                return;
            }

            if ($this->normalizeGuruIdList($request->input('guru_ids', [])) === []) {
                $validator->errors()->add('guru_ids', 'Minimal pilih satu guru untuk ditugasi.');
            }
        });

        $validated = $validator->validate();
        $selectionMode = $this->resolveGuruSelectionMode($request);

        $validated['guru_selection_mode'] = $selectionMode;
        $validated['guru_ids'] = $this->normalizeGuruIdList($validated['guru_ids'] ?? []);
        $validated['guru_selection_scope'] = $this->normalizeGuruSelectionScope($validated['guru_selection_scope'] ?? []);
        $validated['guru_excluded_ids'] = $this->normalizeGuruIdList($validated['guru_excluded_ids'] ?? []);

        return $validated;
    }
}
