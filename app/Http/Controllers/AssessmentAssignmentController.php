<?php

namespace App\Http\Controllers;

use App\Enum\AssessmentKetenagaanType;
use App\Models\Assessment;
use App\Models\AssessmentAssignment;
use App\Models\AssessmentCombination;
use App\Models\AssessmentForm;
use App\Models\AssessmentFormField;
use App\Models\Guru;
use App\Support\Assessment\AssessmentSecurityConfig;
use App\Services\Assessment\AssessmentAttemptLifecycleService;
use App\Services\Assessment\AssessmentMonitoringService;
use App\Services\AssessmentAssignmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        private readonly AssessmentAttemptLifecycleService $attemptLifecycleService,
        private readonly AssessmentMonitoringService $assessmentMonitoringService
    ) {}

    public function index()
    {
        $this->authorizeAccess();

        $datas = AssessmentAssignment::with([
            'assessments.forms.fields',
            'creator',
            'combination',
        ])
            ->orderByDesc('id')
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

        $assignment = AssessmentAssignment::findOrFail($id);

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

        $perPage = max(10, min((int) $request->input('target_per_page', self::TARGET_PAGE_SIZE), 100));
        $targets = $assignment->targets()
            ->select([
                'id',
                'assessment_assignment_id',
                'assessment_assignment_session_id',
                'guru_id',
                'status',
                'assigned_at',
                'started_at',
                'deadline_at',
                'submitted_at',
                'completion_mode',
                'timed_out_at',
            ])
            ->with([
                'guru:id,nama_lengkap,email,eksternal_jabatan,jenis_jabatan,satuan_pendidikan,kabupaten',
                'session:id,label_sesi,waktu_mulai,waktu_selesai',
                'attempt:id,assessment_assignment_target_id,status,completion_mode,submitted_at,timed_out_at,disqualified_at,disqualification_reason,serious_violation_count,warning_violation_count',
            ])
            ->paginate($perPage, ['*'], 'target_page')
            ->withQueryString();
        $targets->setCollection(
            $this->attemptLifecycleService->syncExpiredTargets($targets->getCollection())
        );

        return view('pages.admin.assessment.assignment.show', [
            'menu' => $this->menu,
            'assignment' => $assignment,
            'targets' => $targets,
            'monitoring' => $this->assignmentService->buildAssignmentMonitoring($assignment),
            'monitoringPanel' => $this->assessmentMonitoringService->buildAssignmentDetail($assignment),
        ]);
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
            'combinationOptionsByKetenagaan' => $this->buildCombinationOptionsByKetenagaan(),
            'jabatanOptionsByKetenagaan' => $this->buildJabatanOptionsByKetenagaan(),
            'kabupatenOptionsByKetenagaan' => $this->buildKabupatenOptionsByKetenagaan(),
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

    private function buildKetenagaanSummaries(): array
    {
        $assessmentsByKetenagaan = $this->availableAssessmentsQuery()
            ->orderBy('judul')
            ->get()
            ->groupBy('target_ketenagaan');

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
                            ->map(function ($assessment) {
                                return [
                                    'id' => (int) $assessment->id,
                                    'kode' => $assessment->kode_assessment,
                                    'judul' => $assessment->judul,
                                    'status' => ucfirst($assessment->status),
                                    'forms' => (int) ($assessment->forms_count ?? 0),
                                    'fields' => (int) ($assessment->fields_count ?? 0),
                                ];
                            })
                            ->all(),
                    ],
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
        array $selectedKabupaten = []
    ): int {
        return (int) $this->buildParticipantTargetQuery($case, $selectedJabatan, $selectedKabupaten)->count();
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

    private function buildParticipantTargetQuery(
        AssessmentKetenagaanType $case,
        array $selectedJabatan = [],
        array $selectedKabupaten = []
    ) {
        $query = Guru::query()
            ->where('eksternal_jabatan', $case->guruValue());

        if ($selectedJabatan !== []) {
            $query->whereIn('jenis_jabatan', $selectedJabatan);
        }

        if ($selectedKabupaten !== []) {
            $query->whereIn('kabupaten', $selectedKabupaten);
        }

        return $query;
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
                'target_ketenagaan' => [
                    'required',
                    'string',
                    Rule::in(array_keys(AssessmentKetenagaanType::options())),
                ],
                'target_jabatan' => 'required|array|min:1',
                'target_jabatan.*' => 'required|string|max:255',
                'target_kabupaten' => 'required|array|min:1',
                'target_kabupaten.*' => 'required|string|max:255',
                'deskripsi' => 'nullable|string',
                'tanggal_mulai' => 'nullable|date|required_with:jam_mulai',
                'jam_mulai' => 'nullable|date_format:H:i|required_with:tanggal_mulai',
                'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
                'durasi_sesi_jam' => [
                    'required',
                    'integer',
                    Rule::in(AssessmentAssignmentService::SESSION_DURATION_OPTIONS),
                ],
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
                'tanggal_mulai.required_with' => 'Tanggal mulai wajib diisi jika jam mulai dipakai.',
                'jam_mulai.required_with' => 'Jam mulai wajib diisi jika tanggal mulai dipakai.',
                'jam_mulai.date_format' => 'Format jam mulai harus berupa HH:MM.',
                'durasi_sesi_jam.required' => 'Durasi sesi assessment wajib dipilih.',
                'durasi_sesi_jam.in' => 'Durasi sesi assessment harus sesuai pilihan yang tersedia.',
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
        });

        $validated = $validator->validate();
        $validated['security_config'] = AssessmentSecurityConfig::fromRequest($validated);

        return $validated;
    }
}
