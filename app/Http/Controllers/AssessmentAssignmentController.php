<?php

namespace App\Http\Controllers;

use App\Enum\AssessmentInstrumentType;
use App\Enum\AssessmentKetenagaanType;
use App\Models\Assessment;
use App\Models\AssessmentAssignment;
use App\Models\AssessmentAssignmentTarget;
use App\Models\AssessmentCombination;
use App\Models\AssessmentForm;
use App\Models\AssessmentFormField;
use App\Models\Guru;
use App\Support\Assessment\AssessmentSecurityConfig;
use App\Support\Assessment\AssessmentSchoolTargetKey;
use App\Support\Assessment\AssessmentStageConfig;
use App\Services\Assessment\AssessmentAttemptService;
use App\Services\Assessment\AssessmentMonitoringService;
use App\Services\AssessmentAssignmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AssessmentAssignmentController extends Controller
{
    private const GURU_PAGE_SIZE = 10;
    private const TARGET_PAGE_SIZE = 25;

    private string $menu = 'assessment-penugasan';

    public function __construct(
        private readonly AssessmentAssignmentService $assignmentService,
        private readonly AssessmentMonitoringService $assessmentMonitoringService,
        private readonly AssessmentAttemptService $attemptService
    ) {}

    public function index()
    {
        $this->authorizeAccess();

        $datas = AssessmentAssignment::with([
            'assessments.forms.fields',
            'creator',
            'combination',
        ])
            ->newestFirst()
            ->get();

        $monitoringByAssignmentId = $datas
            ->mapWithKeys(fn (AssessmentAssignment $assignment) => [
                $assignment->id => $this->assignmentService->buildAssignmentMonitoring($assignment, false),
            ])
            ->all();

        return view('pages.admin.assessment.assignment.index', [
            'menu' => $this->menu,
            'datas' => $datas,
            'monitoringByAssignmentId' => $monitoringByAssignmentId,
        ]);
    }

    public function create()
    {
        $this->authorizeAccess();

        return view(
            'pages.admin.assessment.assignment.create',
            $this->buildFormViewData()
        );
    }

    public function edit(string $id)
    {
        $this->authorizeAccess();

        $assignment = AssessmentAssignment::with('assessments')->findOrFail($id);

        return view(
            'pages.admin.assessment.assignment.create',
            $this->buildFormViewData($assignment)
        );
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

        $scope = $this->normalizeGuruSelectionScope([
            'q' => $request->input('q'),
            'filters' => [
                'eksternal_jabatan' => $request->input('eksternal_jabatan'),
                'jenis_jabatan' => $request->input('jenis_jabatan'),
            ],
        ]);

        $this->applyGuruSelectionScope($query, $scope);

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

    public function show(Request $request, string $id)
    {
        $this->authorizeAccess();

        $assignment = AssessmentAssignment::with([
            'assessments.forms.fields',
            'combination',
            'creator',
            'sessions',
        ])
            ->withCount(['targets', 'sessions'])
            ->findOrFail($id);

        $monitoringExplorerFilters = $this->resolveMonitoringExplorerFilters($request);
        $monitoringExplorerMode = $this->resolveMonitoringExplorerMode($request);
        $monitoringExplorerPerPage = max(
            10,
            min((int) $request->input('monitor_per_page', self::TARGET_PAGE_SIZE), 50)
        );

        return view('pages.admin.assessment.assignment.show', [
            'menu' => $this->menu,
            'assignment' => $assignment,
            'monitoring' => $this->assignmentService->buildAssignmentMonitoring($assignment),
            'monitoringPanel' => $this->assessmentMonitoringService->buildAssignmentDetail($assignment),
            'participantAdditionPanel' => $this->buildParticipantAdditionPanel($assignment),
            'monitoringExplorer' => $this->assessmentMonitoringService->buildAssignmentExplorer(
                $assignment,
                $monitoringExplorerFilters,
                $monitoringExplorerMode,
                $monitoringExplorerPerPage,
                max((int) $request->input('monitor_page', 1), 1)
            ),
        ]);
    }

    public function monitoringIndividuals(Request $request, string $id): JsonResponse
    {
        $this->authorizeAccess();

        $assignment = AssessmentAssignment::findOrFail($id);

        return response()->json(
            $this->assessmentMonitoringService->buildAssignmentExplorerDataTable(
                $assignment,
                $this->resolveMonitoringExplorerFilters($request),
                [
                    'draw' => (int) $request->input('draw', 0),
                    'start' => (int) $request->input('start', 0),
                    'length' => (int) $request->input('length', self::TARGET_PAGE_SIZE),
                    'search' => $request->input('search.value'),
                ]
            )
        );
    }

    public function addParticipantOptions(Request $request, string $id): JsonResponse
    {
        $this->authorizeAccess();

        $assignment = AssessmentAssignment::findOrFail($id);
        $additionPanel = $this->buildParticipantAdditionPanel($assignment);

        if (! ($additionPanel['can_open_modal'] ?? false)) {
            return response()->json([
                'items' => [],
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => self::GURU_PAGE_SIZE,
                    'total' => 0,
                    'from' => 0,
                    'to' => 0,
                ],
            ]);
        }

        $query = $this->assignmentService
            ->buildAssignableParticipantQueryForAssignment($assignment)
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
        $existingGuruIds = AssessmentAssignmentTarget::query()
            ->where('assessment_assignment_id', $assignment->id)
            ->pluck('guru_id')
            ->map(fn ($guruId) => (int) $guruId)
            ->all();

        if ($existingGuruIds !== []) {
            $query->whereNotIn('id', $existingGuruIds);
        }

        $this->applyGuruSelectionScope($query, [
            'q' => $request->input('q'),
        ]);

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

    public function addParticipants(Request $request, string $id)
    {
        $this->authorizeAccess();

        $assignment = AssessmentAssignment::findOrFail($id);

        try {
            $result = $this->assignmentService->addParticipants(
                $assignment,
                $this->normalizeGuruIdList((array) $request->input('guru_ids', []))
            );

            /** @var \App\Models\AssessmentAssignment $updatedAssignment */
            $updatedAssignment = $result['assignment'];

            return redirect()
                ->route('assessment.assignment.show', $updatedAssignment->id)
                ->with('assignment_notice', $this->buildAddParticipantsNotice($result));
        } catch (ValidationException $exception) {
            return redirect()
                ->to(route('assessment.assignment.show', $assignment->id).'#assignment-add-participants')
                ->withErrors($exception->errors(), 'addParticipants')
                ->withInput();
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()
                ->to(route('assessment.assignment.show', $assignment->id).'#assignment-add-participants')
                ->withErrors([
                    'guru_ids' => 'Terjadi kesalahan saat menambahkan peserta baru ke penugasan assessment.',
                ], 'addParticipants')
                ->withInput();
        }
    }

    public function retryDisqualifiedTarget(Request $request, string $targetId)
    {
        $this->authorizeAccess();

        $target = AssessmentAssignmentTarget::with('attempt')
            ->findOrFail((int) $targetId);

        try {
            $this->attemptService->reopenDisqualified($target);

            return redirect()
                ->to($this->resolveTargetRetryReturnUrl($request, $target))
                ->with(
                    'assignment_notice',
                    'Peserta berhasil diizinkan mengulangi assessment dari record terakhir tanpa menghapus jawaban sebelumnya.'
                );
        } catch (ValidationException $exception) {
            return redirect()
                ->to($this->resolveTargetRetryReturnUrl($request, $target))
                ->withErrors($exception->errors());
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()
                ->to($this->resolveTargetRetryReturnUrl($request, $target))
                ->withErrors([
                    'assignment' => 'Terjadi kesalahan saat membuka ulang attempt peserta yang didiskualifikasi.',
                ]);
        }
    }

    public function update(Request $request, string $id)
    {
        $this->authorizeAccess();

        $assignment = AssessmentAssignment::findOrFail($id);
        $validated = $this->validatePayload($request);

        try {
            $result = $this->assignmentService->updateAssignment(
                $assignment,
                $validated,
                session('user_id') ? (int) session('user_id') : null
            );

            /** @var \App\Models\AssessmentAssignment $updatedAssignment */
            $updatedAssignment = $result['assignment'];

            return redirect()
                ->route('assessment.assignment.show', $updatedAssignment->id)
                ->with('message', 'update')
                ->with('assignment_notice', $this->buildUpdateNotice($result));
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors([
                    'assignment' => 'Terjadi kesalahan saat memperbarui penugasan assessment.',
                ]);
        }
    }

    public function retry(string $id)
    {
        $this->authorizeAccess();

        $assignment = AssessmentAssignment::findOrFail($id);

        try {
            $result = $this->assignmentService->retryAssignment($assignment);

            /** @var \App\Models\AssessmentAssignment $retriedAssignment */
            $retriedAssignment = $result['assignment'];

            return redirect()
                ->route('assessment.assignment.show', $retriedAssignment->id)
                ->with('assignment_notice', $this->buildRetryNotice($result));
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()
                ->route('assessment.assignment.show', $assignment->id)
                ->withErrors([
                    'assignment' => 'Terjadi kesalahan saat menjalankan retry penugasan assessment.',
                ]);
        }
    }

    public function updateActivationStatus(Request $request, string $id)
    {
        $this->authorizeAccess();

        $assignment = AssessmentAssignment::findOrFail($id);
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $assignment->forceFill([
            'is_active' => (bool) $validated['is_active'],
        ])->save();

        return back()->with('assignment_notice', $this->buildActivationNotice($assignment));
    }

    public function destroy(string $id)
    {
        $this->authorizeAccess();

        $assignment = AssessmentAssignment::findOrFail($id);

        try {
            $result = $this->assignmentService->deleteAssignment($assignment);

            return redirect()
                ->route('assessment.assignment.index')
                ->with('assignment_notice', $this->buildDeleteNotice($result));
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()
                ->route('assessment.assignment.show', $assignment->id)
                ->withErrors([
                    'assignment' => 'Terjadi kesalahan saat menghapus penugasan assessment.',
                ]);
        }
    }

    private function authorizeAccess(): void
    {
        abort_unless(
            in_array(session('role'), ['admin', 'superadmin', 'kepala', 'database'], true),
            403
        );
    }

    private function resolveMonitoringExplorerFilters(Request $request): array
    {
        return [
            'kabupaten' => $this->normalizeMonitoringExplorerFilterValue(
                $request->input('monitor_kabupaten')
            ),
            'jabatan' => $this->normalizeMonitoringExplorerFilterValue(
                $request->input('monitor_jabatan')
            ),
            'satuan_pendidikan' => $this->normalizeMonitoringExplorerFilterValue(
                $request->input('monitor_satuan_pendidikan')
            ),
        ];
    }

    private function resolveMonitoringExplorerMode(Request $request): string
    {
        $mode = trim((string) $request->input('monitor_view', 'individual'));

        return in_array($mode, ['individual', 'summary'], true)
            ? $mode
            : 'individual';
    }

    private function normalizeMonitoringExplorerFilterValue(mixed $value): ?string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : null;
    }

    private function resolveTargetRetryReturnUrl(Request $request, AssessmentAssignmentTarget $target): string
    {
        $fallbackUrl = route('assessment.assignment.show', $target->assessment_assignment_id).'#monitoring-explorer';
        $returnUrl = trim((string) $request->input('return_url', ''));
        $expectedBaseUrl = route('assessment.assignment.show', $target->assessment_assignment_id);

        if ($returnUrl !== '' && Str::startsWith($returnUrl, $expectedBaseUrl)) {
            return str_contains($returnUrl, '#') ? $returnUrl : $returnUrl.'#monitoring-explorer';
        }

        return $fallbackUrl;
    }

    private function buildFormViewData(?AssessmentAssignment $assignment = null): array
    {
        return [
            'menu' => $this->menu,
            'assignment' => $assignment,
            'isEditMode' => $assignment !== null,
            'pageTitle' => $assignment ? 'Edit Penugasan Assessment' : 'Buat Penugasan Assessment',
            'formAction' => $assignment
                ? route('assessment.assignment.update', $assignment->id)
                : route('assessment.assignment.store'),
            'formMethod' => $assignment ? 'PUT' : 'POST',
            'submitLabel' => $assignment ? 'Simpan Perubahan' : 'Simpan Penugasan',
            'ketenagaanOptions' => AssessmentKetenagaanType::options(),
            'ketenagaanSummaries' => $this->buildKetenagaanSummaries(),
            'assignmentStageConfigs' => $this->buildAssignmentStageConfigMap($assignment),
            'combinationOptionsByKetenagaan' => $this->buildCombinationOptionsByKetenagaan(),
            'jabatanOptionsByKetenagaan' => $this->buildJabatanOptionsByKetenagaan(),
            'kabupatenOptionsByKetenagaan' => $this->buildKabupatenOptionsByKetenagaan(),
            'satuanPendidikanOptionsByKetenagaan' => $this->buildSatuanPendidikanOptionsByKetenagaan(),
            'batchThreshold' => AssessmentAssignmentService::BATCH_THRESHOLD,
            'batchQueueConnection' => AssessmentAssignmentService::QUEUE_CONNECTION,
            'batchQueueName' => AssessmentAssignmentService::QUEUE_NAME,
            'sessionCapacity' => AssessmentAssignmentService::TARGETS_PER_SESSION,
            'defaultSessionDurationHours' => AssessmentAssignmentService::DEFAULT_SESSION_DURATION_HOURS,
            'sessionDurationOptions' => AssessmentAssignmentService::SESSION_DURATION_OPTIONS,
        ];
    }

    private function buildUpdateNotice(array $result): string
    {
        $parts = ['Penugasan assessment berhasil diperbarui.'];

        if (($result['reset_target_count'] ?? 0) > 0) {
            $parts[] = $result['reset_target_count'].' target lama direset.';
        }

        if (($result['deleted_attempt_count'] ?? 0) > 0) {
            $parts[] = $result['deleted_attempt_count'].' riwayat pengerjaan dihapus.';
        }

        if (($result['deleted_answer_count'] ?? 0) > 0) {
            $parts[] = $result['deleted_answer_count'].' jawaban lama dibersihkan.';
        }

        if (($result['deleted_file_count'] ?? 0) > 0) {
            $parts[] = $result['deleted_file_count'].' file unggahan ikut dihapus.';
        }

        if (($result['new_target_count'] ?? 0) > 0) {
            $parts[] = $result['new_target_count'].' peserta sekarang harus memulai assessment dari nol.';
        }

        if ($result['queued'] ?? false) {
            $parts[] = 'Distribusi ulang dijalankan melalui batch job.';
        }

        return implode(' ', $parts);
    }

    private function buildDeleteNotice(array $result): string
    {
        $parts = ['Penugasan assessment berhasil dihapus permanen.'];

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

        return implode(' ', $parts);
    }

    private function buildRetryNotice(array $result): string
    {
        if ($result['already_complete'] ?? false) {
            return 'Tidak ada target yang perlu di-resume. Status penugasan sudah disegarkan kembali.';
        }

        $distributionMethod = ($result['queued'] ?? false) ? 'batch job' : 'proses langsung';

        return 'Retry penugasan dijalankan. Resume untuk '
            .($result['resumed_count'] ?? 0)
            .' target diproses melalui '.$distributionMethod.'.';
    }

    private function buildActivationNotice(AssessmentAssignment $assignment): string
    {
        if ($assignment->isActive()) {
            return 'Penugasan assessment diaktifkan kembali. Penugasan kembali tampil di portal peserta sesuai jadwal dan status distribusinya.';
        }

        return 'Penugasan assessment dinonaktifkan. Penugasan tidak lagi tampil di portal peserta, tetapi target, attempt, jawaban, dan histori tetap tersimpan.';
    }

    private function buildAddParticipantsNotice(array $result): string
    {
        $parts = [
            ($result['added_count'] ?? 0).' peserta baru berhasil ditambahkan tanpa reset penugasan.',
            'Peserta lama tetap menggunakan progres dan sesi yang sudah ada.',
        ];

        if (($result['created_session_count'] ?? 0) > 0) {
            $parts[] = $result['created_session_count'].' sesi tambahan dibuat untuk menampung peserta baru secara aman.';
        }

        return implode(' ', $parts);
    }

    private function buildParticipantAdditionPanel(AssessmentAssignment $assignment): array
    {
        $selectedGuruIds = $this->normalizeGuruIdList((array) old('guru_ids', []));
        $canChangeTargets = ! in_array($assignment->status_distribusi, ['diproses', 'gagal'], true);
        $availableTotal = 0;

        if ($canChangeTargets) {
            $query = $this->assignmentService
                ->buildAssignableParticipantQueryForAssignment($assignment);
            $existingGuruIds = AssessmentAssignmentTarget::query()
                ->where('assessment_assignment_id', $assignment->id)
                ->pluck('guru_id')
                ->map(fn ($guruId) => (int) $guruId)
                ->all();

            if ($existingGuruIds !== []) {
                $query->whereNotIn('id', $existingGuruIds);
            }

            $availableTotal = (int) $query->count();
        }

        $disabledReason = null;

        if (! $canChangeTargets) {
            $disabledReason = $assignment->status_distribusi === 'diproses'
                ? 'Penugasan masih diproses queue. Tunggu sampai distribusi selesai sebelum menambah peserta baru.'
                : 'Penugasan sedang berstatus gagal. Selesaikan distribusi bermasalah terlebih dahulu sebelum menambah peserta baru.';
        } elseif ($availableTotal < 1) {
            $disabledReason = 'Tidak ada peserta tambahan pada ketenagaan ini atau semua peserta pada ketenagaan ini sudah pernah ditugaskan.';
        }

        return [
            'available_total' => $availableTotal,
            'can_open_modal' => $canChangeTargets && $availableTotal > 0,
            'disabled_reason' => $disabledReason,
            'selected_ids' => $selectedGuruIds,
            'selected_items' => $this->buildSelectedGuruItems($selectedGuruIds),
        ];
    }

    private function buildKetenagaanSummaries(): array
    {
        $assessmentsByKetenagaan = $this->availableAssessmentsQuery()
            ->get()
            ->groupBy('target_ketenagaan')
            ->map(fn ($items) => $this->sortAssessmentsForDefaultStages($items));

        $participantCounts = Guru::query()
            ->selectRaw('eksternal_jabatan, count(*) as aggregate')
            ->whereIn(
                'eksternal_jabatan',
                collect(AssessmentKetenagaanType::cases())->map(fn (AssessmentKetenagaanType $case) => $case->guruValue())->all()
            )
            ->groupBy('eksternal_jabatan')
            ->pluck('aggregate', 'eksternal_jabatan');

        return collect(AssessmentKetenagaanType::cases())
            ->mapWithKeys(function (AssessmentKetenagaanType $case) use ($assessmentsByKetenagaan, $participantCounts) {
                $items = $assessmentsByKetenagaan->get($case->value, collect())->values();

                return [
                    $case->value => [
                        'value' => $case->value,
                        'label' => $case->label(),
                        'badge_class' => $case->badgeClass(),
                        'icon_class' => $case->iconClass(),
                        'assessment_count' => $items->count(),
                        'form_count' => $items->sum(fn ($assessment) => (int) ($assessment->forms_count ?? 0)),
                        'field_count' => $items->sum(fn ($assessment) => (int) ($assessment->fields_count ?? 0)),
                        'user_count' => (int) ($participantCounts[$case->guruValue()] ?? 0),
                        'assessment_items' => $items
                            ->map(function ($assessment, int $index) {
                                $defaultStageConfig = AssessmentStageConfig::defaultForAssessment(
                                    $assessment->instrument_type,
                                    $index
                                );

                                return [
                                    'id' => (int) $assessment->id,
                                    'kode' => $assessment->kode_assessment,
                                    'judul' => $assessment->judul,
                                    'status' => ucfirst($assessment->status),
                                    'instrument_type' => $assessment->instrument_type,
                                    'instrument_label' => AssessmentInstrumentType::tryFromMixed(
                                        $assessment->instrument_type
                                    )?->label(),
                                    'forms' => (int) ($assessment->forms_count ?? 0),
                                    'fields' => (int) ($assessment->fields_count ?? 0),
                                    'default_stage_config' => $defaultStageConfig,
                                ];
                            })
                            ->all(),
                    ],
                ];
            })
            ->all();
    }

    private function buildAssignmentStageConfigMap(?AssessmentAssignment $assignment = null): array
    {
        if (! $assignment) {
            return [];
        }

        $assignment->loadMissing('assessments');

        return $assignment->assessments
            ->values()
            ->mapWithKeys(function (Assessment $assessment, int $index) {
                return [
                    (int) $assessment->id => AssessmentStageConfig::normalize(
                        is_array($assessment->pivot?->stage_config ?? null)
                            ? $assessment->pivot->stage_config
                            : [],
                        AssessmentStageConfig::defaultForAssessment(
                            $assessment->instrument_type,
                            $index
                        )
                    ),
                ];
            })
            ->all();
    }

    private function availableAssessmentsQuery()
    {
        return Assessment::query()
            ->select([
                'assessments.id',
                'assessments.kode_assessment',
                'assessments.judul',
                'assessments.instrument_type',
                'assessments.status',
                'assessments.target_ketenagaan',
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
            ->where('assessments.status', 'publish');
    }

    private function sortAssessmentsForDefaultStages($assessments)
    {
        return collect($assessments)
            ->sort(function ($left, $right) {
                return [
                    AssessmentInstrumentType::assignmentStageOrderFor($left->instrument_type ?? null),
                    strtolower(trim((string) ($left->judul ?? ''))),
                    (int) ($left->id ?? 0),
                ] <=> [
                    AssessmentInstrumentType::assignmentStageOrderFor($right->instrument_type ?? null),
                    strtolower(trim((string) ($right->judul ?? ''))),
                    (int) ($right->id ?? 0),
                ];
            })
            ->values();
    }

    private function countAvailableCombinationsForKetenagaan(AssessmentKetenagaanType $case): int
    {
        return $this->assignmentService
            ->countAvailableCombinationsForKetenagaan($case);
    }

    private function countAvailableParticipantsForKetenagaan(AssessmentKetenagaanType $case): int
    {
        return $this->countAvailableParticipantsForFilters($case);
    }

    private function countAvailableParticipantsForFilters(
        AssessmentKetenagaanType $case,
        array $selectedJabatan = [],
        array $selectedKabupaten = [],
        array $selectedSatuanPendidikan = []
    ): int {
        return (int) $this->buildParticipantTargetQuery(
            $case,
            $selectedJabatan,
            $selectedKabupaten,
            $selectedSatuanPendidikan
        )->count();
    }

    private function buildJabatanOptionsByKetenagaan(): array
    {
        $countsByKetenagaan = Guru::query()
            ->selectRaw('eksternal_jabatan, jenis_jabatan, count(*) as aggregate')
            ->whereIn(
                'eksternal_jabatan',
                collect(AssessmentKetenagaanType::cases())
                    ->map(fn (AssessmentKetenagaanType $case) => $case->guruValue())
                    ->all()
            )
            ->whereNotNull('jenis_jabatan')
            ->where('jenis_jabatan', '!=', '')
            ->groupBy('eksternal_jabatan', 'jenis_jabatan')
            ->orderBy('jenis_jabatan')
            ->get()
            ->groupBy('eksternal_jabatan');

        return collect(AssessmentKetenagaanType::cases())
            ->mapWithKeys(function (AssessmentKetenagaanType $case) use ($countsByKetenagaan) {
                $items = $countsByKetenagaan
                    ->get($case->guruValue(), collect())
                    ->values()
                    ->map(function ($row) use ($case) {
                        $jabatan = (string) $row->jenis_jabatan;
                        $userCount = (int) $row->aggregate;

                        return [
                            'id' => $jabatan,
                            'label' => $jabatan,
                            'description' => $userCount.' user pada '.$case->label(),
                            'cells' => [
                                $jabatan,
                                $userCount.' user',
                            ],
                            'payload' => [
                                'jenis_jabatan' => $jabatan,
                                'ketenagaan' => $case->value,
                                'ketenagaan_label' => $case->label(),
                                'user_count' => $userCount,
                            ],
                        ];
                    })
                    ->all();

                return [
                    $case->value => $items,
                ];
            })
            ->all();
    }

    private function buildCombinationOptionsByKetenagaan(): array
    {
        return collect(AssessmentKetenagaanType::cases())
            ->mapWithKeys(function (AssessmentKetenagaanType $case) {
                $items = $this->assignmentService
                    ->getAvailableCombinationOptionSummariesForKetenagaan($case)
                    ->values()
                    ->map(function (AssessmentCombination $combination) {
                        return [
                            'id' => (int) $combination->id,
                            'kode' => $combination->kode_kombinasi,
                            'judul' => $combination->kode_kombinasi,
                            'description' => trim(implode(' | ', array_filter([
                                $combination->total_assessments.' assessment sumber',
                                $combination->total_forms.' form',
                                $combination->total_questions.' soal',
                            ]))),
                            'total_assessments' => (int) $combination->total_assessments,
                            'total_forms' => (int) $combination->total_forms,
                            'total_questions' => (int) $combination->total_questions,
                        ];
                    })
                    ->all();

                return [
                    $case->value => $items,
                ];
            })
            ->all();
    }

    private function buildKabupatenOptionsByKetenagaan(): array
    {
        $countsByKetenagaan = Guru::query()
            ->selectRaw('eksternal_jabatan, kabupaten, jenis_jabatan, count(*) as aggregate')
            ->whereIn(
                'eksternal_jabatan',
                collect(AssessmentKetenagaanType::cases())
                    ->map(fn (AssessmentKetenagaanType $case) => $case->guruValue())
                    ->all()
            )
            ->whereNotNull('jenis_jabatan')
            ->where('jenis_jabatan', '!=', '')
            ->whereNotNull('kabupaten')
            ->where('kabupaten', '!=', '')
            ->groupBy('eksternal_jabatan', 'kabupaten', 'jenis_jabatan')
            ->orderBy('kabupaten')
            ->orderBy('jenis_jabatan')
            ->get()
            ->groupBy('eksternal_jabatan');

        return collect(AssessmentKetenagaanType::cases())
            ->mapWithKeys(function (AssessmentKetenagaanType $case) use ($countsByKetenagaan) {
                $items = $countsByKetenagaan
                    ->get($case->guruValue(), collect())
                    ->groupBy('kabupaten')
                    ->map(function ($rows, $kabupaten) use ($case) {
                        $countsByJabatan = $rows
                            ->mapWithKeys(fn ($row) => [
                                (string) $row->jenis_jabatan => (int) $row->aggregate,
                            ])
                            ->all();
                        $userCount = array_sum($countsByJabatan);

                        return [
                            'id' => (string) $kabupaten,
                            'label' => (string) $kabupaten,
                            'description' => $userCount.' user lintas jabatan pada '.$case->label(),
                            'cells' => [
                                (string) $kabupaten,
                                $userCount.' user',
                            ],
                            'payload' => [
                                'kabupaten' => (string) $kabupaten,
                                'ketenagaan' => $case->value,
                                'ketenagaan_label' => $case->label(),
                                'user_count' => $userCount,
                                'counts_by_jabatan' => $countsByJabatan,
                            ],
                        ];
                    })
                    ->values()
                    ->all();

                return [
                    $case->value => $items,
                ];
            })
            ->all();
    }

    private function buildSatuanPendidikanOptionsByKetenagaan(): array
    {
        $countsByKetenagaan = Guru::query()
            ->selectRaw('eksternal_jabatan, kabupaten, satuan_pendidikan, jenis_jabatan, count(*) as aggregate')
            ->whereIn(
                'eksternal_jabatan',
                collect(AssessmentKetenagaanType::cases())
                    ->map(fn (AssessmentKetenagaanType $case) => $case->guruValue())
                    ->all()
            )
            ->whereNotNull('jenis_jabatan')
            ->where('jenis_jabatan', '!=', '')
            ->whereNotNull('kabupaten')
            ->where('kabupaten', '!=', '')
            ->whereNotNull('satuan_pendidikan')
            ->where('satuan_pendidikan', '!=', '')
            ->groupBy('eksternal_jabatan', 'kabupaten', 'satuan_pendidikan', 'jenis_jabatan')
            ->orderBy('kabupaten')
            ->orderBy('satuan_pendidikan')
            ->orderBy('jenis_jabatan')
            ->get()
            ->groupBy('eksternal_jabatan');

        return collect(AssessmentKetenagaanType::cases())
            ->mapWithKeys(function (AssessmentKetenagaanType $case) use ($countsByKetenagaan) {
                $items = $countsByKetenagaan
                    ->get($case->guruValue(), collect())
                    ->groupBy(function ($row) {
                        return AssessmentSchoolTargetKey::encode(
                            (string) $row->kabupaten,
                            (string) $row->satuan_pendidikan
                        );
                    })
                    ->map(function ($rows, $selectionKey) use ($case) {
                        $firstRow = $rows->first();
                        $kabupaten = (string) ($firstRow->kabupaten ?? '');
                        $school = (string) ($firstRow->satuan_pendidikan ?? '');
                        $countsByJabatan = $rows
                            ->mapWithKeys(fn ($row) => [
                                (string) $row->jenis_jabatan => (int) $row->aggregate,
                            ])
                            ->all();
                        $userCount = array_sum($countsByJabatan);

                        return [
                            'id' => (string) $selectionKey,
                            'label' => $school,
                            'description' => $userCount.' user pada '.$school,
                            'cells' => [
                                $school,
                                $kabupaten,
                                $userCount.' user',
                            ],
                            'payload' => [
                                'kabupaten' => $kabupaten,
                                'satuan_pendidikan' => $school,
                                'ketenagaan' => $case->value,
                                'ketenagaan_label' => $case->label(),
                                'user_count' => $userCount,
                                'counts_by_jabatan' => $countsByJabatan,
                            ],
                        ];
                    })
                    ->values()
                    ->all();

                return [
                    $case->value => $items,
                ];
            })
            ->all();
    }

    private function availableJabatanValuesForKetenagaan(AssessmentKetenagaanType $case): array
    {
        return Guru::query()
            ->where('eksternal_jabatan', $case->guruValue())
            ->whereNotNull('jenis_jabatan')
            ->where('jenis_jabatan', '!=', '')
            ->orderBy('jenis_jabatan')
            ->distinct()
            ->pluck('jenis_jabatan')
            ->map(fn ($jabatan) => (string) $jabatan)
            ->values()
            ->all();
    }

    private function availableKabupatenValuesForFilters(
        AssessmentKetenagaanType $case,
        array $selectedJabatan = []
    ): array {
        return $this->buildParticipantTargetQuery($case, $selectedJabatan)
            ->whereNotNull('kabupaten')
            ->where('kabupaten', '!=', '')
            ->orderBy('kabupaten')
            ->distinct()
            ->pluck('kabupaten')
            ->map(fn ($kabupaten) => (string) $kabupaten)
            ->values()
            ->all();
    }

    private function availableSatuanPendidikanValuesForFilters(
        AssessmentKetenagaanType $case,
        array $selectedJabatan = [],
        array $selectedKabupaten = []
    ): array {
        return $this->buildParticipantTargetQuery($case, $selectedJabatan, $selectedKabupaten)
            ->whereNotNull('satuan_pendidikan')
            ->where('satuan_pendidikan', '!=', '')
            ->select(['kabupaten', 'satuan_pendidikan'])
            ->orderBy('kabupaten')
            ->orderBy('satuan_pendidikan')
            ->get()
            ->map(function ($guru) {
                return AssessmentSchoolTargetKey::encode(
                    $guru->kabupaten,
                    $guru->satuan_pendidikan
                );
            })
            ->filter(fn ($selectionKey) => $selectionKey !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function normalizeTargetJabatanList(mixed $targetJabatan): array
    {
        return collect(is_array($targetJabatan) ? $targetJabatan : [$targetJabatan])
            ->filter(fn ($jabatan) => filled($jabatan))
            ->map(fn ($jabatan) => trim((string) $jabatan))
            ->filter(fn (string $jabatan) => $jabatan !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function normalizeTargetKabupatenList(mixed $targetKabupaten): array
    {
        return collect(is_array($targetKabupaten) ? $targetKabupaten : [$targetKabupaten])
            ->filter(fn ($kabupaten) => filled($kabupaten))
            ->map(fn ($kabupaten) => trim((string) $kabupaten))
            ->filter(fn (string $kabupaten) => $kabupaten !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function normalizeTargetSatuanPendidikanList(mixed $targetSatuanPendidikan): array
    {
        return collect(is_array($targetSatuanPendidikan) ? $targetSatuanPendidikan : [$targetSatuanPendidikan])
            ->filter(fn ($selectionKey) => filled($selectionKey))
            ->map(fn ($selectionKey) => trim((string) $selectionKey))
            ->filter(fn (string $selectionKey) => $selectionKey !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function buildParticipantTargetQuery(
        AssessmentKetenagaanType $case,
        array $selectedJabatan = [],
        array $selectedKabupaten = [],
        array $selectedSatuanPendidikan = []
    ) {
        $query = Guru::query()
            ->where('eksternal_jabatan', $case->guruValue());

        if ($selectedJabatan !== []) {
            $query->whereIn('jenis_jabatan', $selectedJabatan);
        }

        if ($selectedKabupaten !== []) {
            $query->whereIn('kabupaten', $selectedKabupaten);
        }

        $this->applyTargetSatuanPendidikanFilter($query, $selectedSatuanPendidikan);

        return $query;
    }

    private function applyTargetSatuanPendidikanFilter($query, array $selectedSatuanPendidikan): void
    {
        $groups = $this->groupSatuanPendidikanSelectionsByKabupaten($selectedSatuanPendidikan);

        if ($groups === []) {
            return;
        }

        $query->where(function ($builder) use ($groups) {
            foreach ($groups as $kabupaten => $schools) {
                $builder->orWhere(function ($nestedQuery) use ($kabupaten, $schools) {
                    if ($kabupaten !== '__ANY__') {
                        $nestedQuery->where('kabupaten', $kabupaten);
                    }

                    $nestedQuery->whereIn('satuan_pendidikan', $schools);
                });
            }
        });
    }

    private function groupSatuanPendidikanSelectionsByKabupaten(array $selectedSatuanPendidikan): array
    {
        return collect($selectedSatuanPendidikan)
            ->map(fn ($selectionKey) => AssessmentSchoolTargetKey::decode($selectionKey))
            ->filter(fn (array $selection) => $selection['satuan_pendidikan'] !== '')
            ->groupBy(fn (array $selection) => $selection['kabupaten'] ?? '__ANY__')
            ->map(function ($rows) {
                return collect($rows)
                    ->pluck('satuan_pendidikan')
                    ->map(fn ($school) => trim((string) $school))
                    ->filter(fn (string $school) => $school !== '')
                    ->unique()
                    ->values()
                    ->all();
            })
            ->filter(fn (array $schools) => $schools !== [])
            ->all();
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
                'eksternal_jabatan' => $guru->eksternal_jabatan,
                'jenis_jabatan' => $guru->jenis_jabatan,
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
                'session_enabled' => 'nullable|boolean',
                'target_ketenagaan' => [
                    'required',
                    'string',
                    Rule::in(array_keys(AssessmentKetenagaanType::options())),
                ],
                'target_jabatan' => 'required|array|min:1',
                'target_jabatan.*' => 'required|string|max:255',
                'target_kabupaten' => 'required|array|min:1',
                'target_kabupaten.*' => 'required|string|max:255',
                'target_satuan_pendidikan' => 'required|array|min:1',
                'target_satuan_pendidikan.*' => 'required|string|max:255',
                'deskripsi' => 'nullable|string',
                'tanggal_mulai' => 'nullable|date|required_with:jam_mulai',
                'jam_mulai' => 'nullable|date_format:H:i|required_with:tanggal_mulai',
                'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
                'durasi_sesi_jam' => [
                    'required',
                    'integer',
                    Rule::in(AssessmentAssignmentService::SESSION_DURATION_OPTIONS),
                ],
                'stage_configs' => 'nullable|array',
                'stage_configs.*' => 'nullable|array',
                'stage_configs.*.enabled' => 'nullable|boolean',
                'stage_configs.*.entry_mode' => 'nullable|string|in:direct,start_button',
                'stage_configs.*.allow_draft' => 'nullable|boolean',
                'stage_configs.*.finalize_mode' => 'nullable|string|in:manual,auto',
                'stage_configs.*.lock_until_previous_stages_completed' => 'nullable|boolean',
                'stage_configs.*.time_limit_minutes' => 'nullable|integer|min:1|max:600',
                'stage_configs.*.security_enabled' => 'nullable|boolean',
                'stage_configs.*.security_require_fullscreen' => 'nullable|boolean',
                'stage_configs.*.security_max_serious_violations' => 'nullable|integer|min:1|max:10',
                'stage_configs.*.security_temporary_lock_seconds' => 'nullable|integer|min:1|max:30',
                'stage_configs.*.security_fullscreen_grace_seconds' => 'nullable|integer|min:3|max:60',
                'security_enabled' => 'nullable|boolean',
                'security_require_fullscreen' => 'nullable|boolean',
                'security_max_serious_violations' => 'nullable|integer|min:1|max:10',
                'security_temporary_lock_seconds' => 'nullable|integer|min:1|max:30',
                'security_fullscreen_grace_seconds' => 'nullable|integer|min:3|max:60',
            ],
            [
                'judul_penugasan.required' => 'Judul penugasan wajib diisi.',
                'target_ketenagaan.required' => 'Ketenagaan target wajib dipilih.',
                'target_ketenagaan.in' => 'Ketenagaan target harus sesuai pilihan yang tersedia.',
                'target_jabatan.required' => 'Pilih minimal satu jabatan target.',
                'target_jabatan.array' => 'Format jabatan target tidak valid.',
                'target_jabatan.min' => 'Pilih minimal satu jabatan target.',
                'target_jabatan.*.required' => 'Jabatan target tidak boleh kosong.',
                'target_kabupaten.required' => 'Pilih minimal satu kabupaten target.',
                'target_kabupaten.array' => 'Format kabupaten target tidak valid.',
                'target_kabupaten.min' => 'Pilih minimal satu kabupaten target.',
                'target_kabupaten.*.required' => 'Kabupaten target tidak boleh kosong.',
                'target_satuan_pendidikan.required' => 'Pilih minimal satu satuan pendidikan target.',
                'target_satuan_pendidikan.array' => 'Format satuan pendidikan target tidak valid.',
                'target_satuan_pendidikan.min' => 'Pilih minimal satu satuan pendidikan target.',
                'target_satuan_pendidikan.*.required' => 'Satuan pendidikan target tidak boleh kosong.',
                'tanggal_mulai.required_with' => 'Tanggal mulai wajib diisi jika jam mulai dipakai.',
                'jam_mulai.required_with' => 'Jam mulai wajib diisi jika tanggal mulai dipakai.',
                'jam_mulai.date_format' => 'Format jam mulai harus berupa HH:MM.',
                'durasi_sesi_jam.required' => 'Durasi pengerjaan assessment wajib dipilih.',
                'durasi_sesi_jam.in' => 'Durasi pengerjaan assessment harus sesuai pilihan yang tersedia.',
                'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
                'security_max_serious_violations.min' => 'Batas pelanggaran serius minimal 1.',
                'security_max_serious_violations.max' => 'Batas pelanggaran serius maksimal 10.',
                'security_temporary_lock_seconds.min' => 'Durasi kunci sementara minimal 1 detik.',
                'security_temporary_lock_seconds.max' => 'Durasi kunci sementara maksimal 30 detik.',
                'security_fullscreen_grace_seconds.min' => 'Tenggang fullscreen minimal 3 detik.',
                'security_fullscreen_grace_seconds.max' => 'Tenggang fullscreen maksimal 60 detik.',
            ]
        );

        $validator->after(function ($validator) use ($request) {
            $targetKetenagaan = AssessmentKetenagaanType::tryFromMixed(
                $request->input('target_ketenagaan')
            );

            if (! $targetKetenagaan) {
                return;
            }

            if ($this->countAvailableCombinationsForKetenagaan($targetKetenagaan) < 1) {
                $validator->errors()->add(
                    'target_ketenagaan',
                    'Belum ada kombinasi soal aktif untuk ketenagaan yang dipilih.'
                );
            }

            if ($this->countAvailableParticipantsForKetenagaan($targetKetenagaan) < 1) {
                $validator->errors()->add(
                    'target_ketenagaan',
                    'Belum ada user/peserta pada ketenagaan yang dipilih.'
                );
            }

            $selectedTargetJabatan = $this->normalizeTargetJabatanList(
                (array) $request->input('target_jabatan', [])
            );
            $availableJabatan = $this->availableJabatanValuesForKetenagaan($targetKetenagaan);

            if ($availableJabatan === []) {
                $validator->errors()->add(
                    'target_jabatan',
                    'Belum ada data jabatan untuk ketenagaan yang dipilih.'
                );

                return;
            }

            if ($selectedTargetJabatan === []) {
                return;
            }

            $invalidTargetJabatan = array_values(array_diff($selectedTargetJabatan, $availableJabatan));

            if ($invalidTargetJabatan !== []) {
                $validator->errors()->add(
                    'target_jabatan',
                    'Jabatan target harus sesuai dengan ketenagaan yang dipilih.'
                );

                return;
            }

            if ($this->countAvailableParticipantsForFilters($targetKetenagaan, $selectedTargetJabatan) < 1) {
                $validator->errors()->add(
                    'target_jabatan',
                    'Belum ada user/peserta pada jabatan yang dipilih.'
                );

                return;
            }

            $selectedTargetKabupaten = $this->normalizeTargetKabupatenList(
                (array) $request->input('target_kabupaten', [])
            );
            $availableKabupaten = $this->availableKabupatenValuesForFilters(
                $targetKetenagaan,
                $selectedTargetJabatan
            );

            if ($availableKabupaten === []) {
                $validator->errors()->add(
                    'target_kabupaten',
                    'Belum ada data kabupaten untuk kombinasi ketenagaan dan jabatan yang dipilih.'
                );

                return;
            }

            if ($selectedTargetKabupaten === []) {
                return;
            }

            $invalidTargetKabupaten = array_values(array_diff($selectedTargetKabupaten, $availableKabupaten));

            if ($invalidTargetKabupaten !== []) {
                $validator->errors()->add(
                    'target_kabupaten',
                    'Kabupaten target harus sesuai dengan ketenagaan dan jabatan yang dipilih.'
                );

                return;
            }

            if ($this->countAvailableParticipantsForFilters(
                $targetKetenagaan,
                $selectedTargetJabatan,
                $selectedTargetKabupaten
            ) < 1) {
                $validator->errors()->add(
                    'target_kabupaten',
                    'Belum ada user/peserta pada kabupaten yang dipilih.'
                );
            }

            $selectedTargetSatuanPendidikan = $this->normalizeTargetSatuanPendidikanList(
                (array) $request->input('target_satuan_pendidikan', [])
            );
            $availableSatuanPendidikan = $this->availableSatuanPendidikanValuesForFilters(
                $targetKetenagaan,
                $selectedTargetJabatan,
                $selectedTargetKabupaten
            );

            if ($availableSatuanPendidikan === []) {
                $validator->errors()->add(
                    'target_satuan_pendidikan',
                    'Belum ada data satuan pendidikan untuk kombinasi ketenagaan, jabatan, dan kabupaten yang dipilih.'
                );

                return;
            }

            if ($selectedTargetSatuanPendidikan === []) {
                return;
            }

            $invalidTargetSatuanPendidikan = array_values(
                array_diff($selectedTargetSatuanPendidikan, $availableSatuanPendidikan)
            );

            if ($invalidTargetSatuanPendidikan !== []) {
                $validator->errors()->add(
                    'target_satuan_pendidikan',
                    'Satuan pendidikan target harus sesuai dengan ketenagaan, jabatan, dan kabupaten yang dipilih.'
                );

                return;
            }

            if ($this->countAvailableParticipantsForFilters(
                $targetKetenagaan,
                $selectedTargetJabatan,
                $selectedTargetKabupaten,
                $selectedTargetSatuanPendidikan
            ) < 1) {
                $validator->errors()->add(
                    'target_satuan_pendidikan',
                    'Belum ada user/peserta pada satuan pendidikan yang dipilih.'
                );
            }
        });

        $validated = $validator->validate();
        $validated['session_enabled'] = filter_var(
            $request->input('session_enabled', true),
            FILTER_VALIDATE_BOOLEAN
        );
        $validated['security_config'] = AssessmentSecurityConfig::fromRequest($validated);

        return $validated;
    }
}
