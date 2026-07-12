<?php

namespace App\Http\Controllers\Assessment;

use App\Enum\AssessmentInstrumentType;
use App\Enum\AssessmentKetenagaanType;
use App\Http\Controllers\Controller;
use App\Models\AssessmentAssignmentTarget;
use App\Models\AssessmentAttempt;
use App\Models\Guru;
use App\Services\Assessment\AssessmentAttemptLifecycleService;
use App\Services\Assessment\AssessmentAttemptSecurityService;
use App\Services\Assessment\AssessmentAttemptService;
use App\Services\Assessment\AssessmentPortalAuthService;
use App\Services\Assessment\AssessmentPortalService;
use App\Support\Assessment\AssessmentStageConfig;
use App\Support\Assessment\AssessmentStageProgress;
use App\Support\Assessment\AssessmentTrainingSummaryHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PortalController extends Controller
{
    private const ADMIN_RESULT_ROLES = ['admin', 'superadmin', 'kepala', 'database'];

    public function __construct(
        private readonly AssessmentPortalAuthService $authService,
        private readonly AssessmentPortalService $portalService,
        private readonly AssessmentAttemptLifecycleService $attemptLifecycleService,
        private readonly AssessmentAttemptService $attemptService,
        private readonly AssessmentAttemptSecurityService $attemptSecurityService
    ) {}

    public function landing()
    {
        return $this->authService->isAuthenticated()
            ? redirect()->route('assessment.portal.dashboard')
            : redirect()->route('assessment.portal.auth');
    }

    public function dashboard()
    {
        $guru = $this->requireGuru();
        $dashboardCards = collect($this->portalService->getDashboardTargets($guru))
            ->map(function (array $item) {
                $target = $this->attemptLifecycleService->syncExpiredTarget($item['target']);

                return [
                    'target' => $target,
                    'meta' => $this->portalService->buildTargetMeta($target),
                ];
            })
            ->values()
            ->all();

        return view('assessment.index', [
            'menu' => 'assessment-portal',
            'guru' => $guru,
            'dashboardCards' => $dashboardCards,
        ]);
    }

    public function start(Request $request, string $id)
    {
        $guru = $this->requireGuru();
        $target = $this->portalService->findTargetForGuru($guru, (int) $id);
        $target = $this->attemptLifecycleService->syncExpiredTarget($target);
        $meta = $this->portalService->buildTargetMeta($target);

        if ($meta['status'] === 'submitted') {
            return redirect()->route('assessment.portal.result', $target->id);
        }

        if (! in_array($meta['status'], ['ready', 'in_progress'], true)) {
            return redirect()
                ->route('assessment.portal.dashboard')
                ->withErrors([
                    'portal' => $meta['description'],
                ]);
        }

        $attempt = $this->attemptLifecycleService->ensureAttempt($target, false);

        if ($this->usesStageFlow($target, $attempt)) {
            $stageIndex = $this->resolveRequestedStageIndex($request, $attempt);
            $progress = $this->resolveStageProgress($attempt);

            if (! AssessmentStageProgress::canAccessStage($progress, $stageIndex)) {
                return redirect()
                    ->route('assessment.portal.show', ['id' => $target->id])
                    ->withErrors([
                        'portal' => AssessmentStageProgress::lockReason($progress, $stageIndex)
                            ?: 'Tahap yang diminta belum bisa diakses.',
                    ]);
            }

            $stage = AssessmentStageProgress::stage($progress, $stageIndex);

            if (($stage['status'] ?? null) !== AssessmentStageProgress::STATUS_SUBMITTED) {
                $attempt = $this->attemptService->markStageStarted($attempt, $stageIndex);
            }

            return redirect()
                ->route('assessment.portal.show', ['id' => $target->id, 'stage' => $stageIndex])
                ->with('assessment_portal_success', 'Tahap assessment dimulai.');
        }

        $this->attemptLifecycleService->ensureAttempt($target, true);

        return redirect()
            ->route('assessment.portal.show', $target->id)
            ->with('assessment_portal_success', 'Assessment dimulai. Timer pengerjaan sudah berjalan.');
    }

    public function show(Request $request, string $id)
    {
        $guru = $this->requireGuru();
        $target = $this->portalService->findTargetForGuru($guru, (int) $id);
        $target = $this->attemptLifecycleService->syncExpiredTarget($target);
        $meta = $this->portalService->buildTargetMeta($target);

        if ($meta['status'] === 'submitted') {
            return redirect()->route('assessment.portal.result', $target->id);
        }

        $attempt = $this->attemptLifecycleService->ensureAttempt($target, false);
        $stageFlowEnabled = $this->usesStageFlow($target, $attempt);
        $renderStageOverview = $stageFlowEnabled && ! $this->hasRequestedStageSelection($request);

        if (! $stageFlowEnabled && $meta['status'] === 'ready') {
            return redirect()
                ->route('assessment.portal.dashboard')
                ->withErrors([
                    'portal' => 'Klik tombol Mulai Ujian terlebih dahulu agar waktu mulai dan timer assessment tercatat.',
                ]);
        }

        if ($meta['status'] !== 'in_progress') {
            if (! $stageFlowEnabled || $meta['status'] !== 'ready') {
                return redirect()
                    ->route('assessment.portal.dashboard')
                    ->withErrors([
                        'portal' => $meta['description'],
                    ]);
            }
        }

        $currentStageIndex = null;

        if ($stageFlowEnabled) {
            $progress = $this->resolveStageProgress($attempt);

            if ($renderStageOverview) {
                $currentStageIndex = AssessmentStageProgress::resolveCurrentStageIndex($progress);
            } else {
                $stageIndex = $this->resolveRequestedStageIndex($request, $attempt);

                if (! AssessmentStageProgress::canAccessStage($progress, $stageIndex)) {
                    $stageIndex = AssessmentStageProgress::resolveCurrentStageIndex($progress);
                }

                $stage = AssessmentStageProgress::stage($progress, $stageIndex);
                $stageConfig = AssessmentStageProgress::stageConfig($progress, $stageIndex);

                if (
                    $stage
                    && (
                        ($stage['status'] ?? null) === AssessmentStageProgress::STATUS_DRAFT
                        || (
                            ($stage['status'] ?? null) === AssessmentStageProgress::STATUS_READY
                            && ($stageConfig['entry_mode'] ?? null) === AssessmentStageConfig::ENTRY_DIRECT
                        )
                    )
                ) {
                    $attempt = $this->attemptService->markStageStarted($attempt, $stageIndex);
                    $progress = $this->resolveStageProgress($attempt);
                }

                $currentStageIndex = $stageIndex;
            }
        } else {
            $attempt = $this->attemptLifecycleService->ensureAttempt($target, true);
        }

        if ($this->attemptSecurityService->hasReachedSeriousLimit($attempt, $currentStageIndex) && ! $attempt->disqualified_at) {
            $this->attemptSecurityService->disqualify($attempt, [
                'reason' => 'Assessment dihentikan karena batas pelanggaran guard ujian telah tercapai.',
                'record_trigger' => false,
                'metadata' => [
                    'source' => 'portal_show_enforcement',
                ],
            ]);

            return redirect()
                ->route('assessment.portal.result', $target->id)
                ->with('assessment_portal_warning', 'Assessment dihentikan karena batas pelanggaran guard ujian telah tercapai.');
        }

        $freshTarget = $target->fresh([
            'assignment.assessments.forms.fields',
            'assignment.combination',
            'combination',
            'session',
            'attempt.answers',
            'attempt.securityEvents',
        ]);
        $freshAttempt = $freshTarget->attempt ?: $attempt->fresh([
            'answers',
            'securityEvents',
            'target.assignment.assessments.forms.fields',
            'target.assignment.combination',
            'target.combination',
            'target.session',
            'target.guru',
        ]);

        if ($renderStageOverview) {
            return view('assessment.show.overview', [
                'menu' => 'assessment-portal',
                'guru' => $guru,
                'target' => $freshTarget,
                'attempt' => $freshAttempt,
                'meta' => $this->portalService->buildTargetMeta($freshTarget),
                'stageOverview' => $this->portalService->buildStageOverview($freshTarget, $freshAttempt),
            ]);
        }

        return view('assessment.show.show', [
            'menu' => 'assessment-portal',
            'guru' => $guru,
            'target' => $freshTarget,
            'attempt' => $freshAttempt,
            'meta' => $this->portalService->buildTargetMeta($freshTarget),
            'selectedStageIndex' => $currentStageIndex,
            'answerLookup' => $this->attemptService->buildAnswerLookup($freshAttempt),
            'securityPayload' => $this->attemptSecurityService->buildClientPayload($freshAttempt, $currentStageIndex),
        ]);
    }

    public function autosave(Request $request, string $id): JsonResponse
    {
        $guru = $this->requireGuru();
        $target = $this->portalService->findTargetForGuru($guru, (int) $id);
        $meta = $this->portalService->buildTargetMeta($target);

        if ($meta['status'] === 'submitted') {
            return response()->json([
                'status' => 'submitted',
                'message' => 'Assessment ini sudah selesai diproses.',
                'redirect_url' => route('assessment.portal.result', $target->id),
            ]);
        }

        if (
            ! in_array($meta['status'], ['ready', 'in_progress'], true) &&
            ! $this->attemptLifecycleService->isPastDeadline($target)
        ) {
            return response()->json([
                'message' => $meta['description'],
            ], 422);
        }

        $attempt = $this->attemptLifecycleService->ensureAttempt($target, false);

        if ($this->usesStageFlow($target, $attempt)) {
            $stageIndex = $this->resolveRequestedStageIndex($request, $attempt);
            $progress = $this->resolveStageProgress($attempt);

            if (! AssessmentStageProgress::canAccessStage($progress, $stageIndex)) {
                return response()->json([
                    'message' => AssessmentStageProgress::lockReason($progress, $stageIndex)
                        ?: 'Tahap yang diminta belum tersedia.',
                ], 422);
            }

            $stage = AssessmentStageProgress::stage($progress, $stageIndex);
            $stageConfig = AssessmentStageProgress::stageConfig($progress, $stageIndex);

            if (
                $stage
                && ($stage['status'] ?? null) === AssessmentStageProgress::STATUS_READY
                && ($stageConfig['entry_mode'] ?? null) === AssessmentStageConfig::ENTRY_START_BUTTON
            ) {
                return response()->json([
                    'message' => 'Klik tombol Mulai pada tahap ini terlebih dahulu.',
                ], 422);
            }

            if (
                $stage
                && ($stage['status'] ?? null) === AssessmentStageProgress::STATUS_READY
                && ($stageConfig['entry_mode'] ?? null) === AssessmentStageConfig::ENTRY_DIRECT
            ) {
                $attempt = $this->attemptService->markStageStarted($attempt, $stageIndex);
            }
        } else {
            $attempt = $this->attemptLifecycleService->ensureAttempt($target, true);
        }

        if ($attempt->status === 'submitted') {
            return response()->json([
                'status' => 'submitted',
                'message' => $attempt->disqualification_reason ?: 'Assessment ini sudah selesai diproses.',
                'redirect_url' => route('assessment.portal.result', $target->id),
            ]);
        }

        $stageIndex = $this->usesStageFlow($target, $attempt)
            ? $this->resolveRequestedStageIndex($request, $attempt)
            : null;

        if ($this->attemptSecurityService->hasReachedSeriousLimit($attempt, $stageIndex) && ! $attempt->disqualified_at) {
            $this->attemptSecurityService->disqualify(
                $attempt,
                [
                    'reason' => 'Assessment dihentikan karena batas pelanggaran guard ujian telah tercapai.',
                    'record_trigger' => false,
                    'metadata' => [
                        'source' => 'autosave_enforcement',
                    ],
                ],
                $request->input('answers', []),
                $request->file('answers', []),
                $request->input('flagged_field_ids', []),
                $request->input('field_ids', [])
            );

            return response()->json([
                'status' => 'disqualified',
                'message' => 'Assessment dihentikan karena batas pelanggaran guard ujian telah tercapai.',
                'redirect_url' => route('assessment.portal.result', $target->id),
            ]);
        }

        if ($this->attemptLifecycleService->isPastDeadline($target)) {
            if ($stageIndex !== null) {
                $expiredAttempt = $this->attemptService->submitExpiredStage(
                    $attempt,
                    $stageIndex,
                    $request->input('answers', []),
                    $request->file('answers', []),
                    $request->input('flagged_field_ids', [])
                );

                $redirectUrl = $expiredAttempt->status === 'submitted'
                    ? route('assessment.portal.result', $target->id)
                    : route('assessment.portal.show', $target->id);
            } else {
                $this->attemptService->submitExpired(
                    $attempt,
                    $request->input('answers', []),
                    $request->file('answers', []),
                    $request->input('flagged_field_ids', []),
                    $request->input('field_ids', [])
                );
                $redirectUrl = route('assessment.portal.result', $target->id);
            }

            return response()->json([
                'status' => 'expired_submitted',
                'message' => 'Batas waktu berakhir. Jawaban terakhir langsung diproses dan soal kosong diberi skor 0.',
                'redirect_url' => $redirectUrl,
            ]);
        }

        $clientSnapshotBucket = $this->decodeClientSnapshotBucket($request->input('client_snapshot_bucket'));
        $savedAttempt = $this->attemptService->saveSnapshot(
            $attempt,
            $request->input('answers', []),
            $request->file('answers', []),
            $request->input('field_ids', []),
            $request->input('flagged_field_ids', []),
            $clientSnapshotBucket,
            $stageIndex
        );
        $isManualStageDraftSave = $stageIndex !== null
            && ($clientSnapshotBucket['flush_reason'] ?? null) === 'manual_stage_draft';

        return response()->json([
            'status' => 'saved',
            'message' => $isManualStageDraftSave
                ? 'Draft tahap berhasil disimpan.'
                : 'Snapshot jawaban berhasil disimpan.',
            'answered_questions' => $savedAttempt->answered_questions,
            'answered_required_questions' => $savedAttempt->answered_required_questions,
            'saved_at' => optional($savedAttempt->last_answered_at)->toIso8601String(),
            'redirect_url' => $isManualStageDraftSave
                ? route('assessment.portal.show', $target->id)
                : null,
        ]);
    }

    public function submit(Request $request, string $id)
    {
        $guru = $this->requireGuru();
        $target = $this->portalService->findTargetForGuru($guru, (int) $id);
        $initialMeta = $this->portalService->buildTargetMeta($target);

        if ($target->attempt && $target->attempt->status === 'submitted') {
            return redirect()->route('assessment.portal.result', $target->id);
        }

        if (
            ! in_array($initialMeta['status'], ['ready', 'in_progress'], true) &&
            ! $this->attemptLifecycleService->isPastDeadline($target)
        ) {
            return redirect()
                ->route('assessment.portal.dashboard')
                ->withErrors([
                    'portal' => $initialMeta['description'],
                ]);
        }

        $attempt = $this->attemptLifecycleService->ensureAttempt($target, false);

        if ($attempt->status === 'submitted') {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'submitted',
                    'message' => $attempt->disqualification_reason ?: 'Assessment ini sudah selesai diproses.',
                    'redirect_url' => route('assessment.portal.result', $target->id),
                ]);
            }

            return redirect()->route('assessment.portal.result', $target->id);
        }

        if ($this->usesStageFlow($target, $attempt)) {
            $stageIndex = $this->resolveRequestedStageIndex($request, $attempt);
            $progress = $this->resolveStageProgress($attempt);

            if (! AssessmentStageProgress::canAccessStage($progress, $stageIndex)) {
                return redirect()
                    ->route('assessment.portal.show', ['id' => $target->id])
                    ->withErrors([
                        'portal' => AssessmentStageProgress::lockReason($progress, $stageIndex)
                            ?: 'Tahap yang diminta belum tersedia.',
                    ]);
            }

            $stage = AssessmentStageProgress::stage($progress, $stageIndex);
            $stageConfig = AssessmentStageProgress::stageConfig($progress, $stageIndex);

            if (
                $stage
                && ($stage['status'] ?? null) === AssessmentStageProgress::STATUS_READY
                && ($stageConfig['entry_mode'] ?? null) === AssessmentStageConfig::ENTRY_START_BUTTON
            ) {
                return redirect()
                    ->route('assessment.portal.show', ['id' => $target->id, 'stage' => $stageIndex])
                    ->withErrors([
                        'portal' => 'Klik tombol Mulai pada tahap ini terlebih dahulu.',
                    ]);
            }

            if (
                $stage
                && ($stage['status'] ?? null) === AssessmentStageProgress::STATUS_READY
                && ($stageConfig['entry_mode'] ?? null) === AssessmentStageConfig::ENTRY_DIRECT
            ) {
                $attempt = $this->attemptService->markStageStarted($attempt, $stageIndex);
            }

            if ($this->attemptSecurityService->hasReachedSeriousLimit($attempt, $stageIndex) && ! $attempt->disqualified_at) {
                $this->attemptSecurityService->disqualify(
                    $attempt,
                    [
                        'reason' => 'Assessment dihentikan karena batas pelanggaran guard ujian telah tercapai.',
                        'record_trigger' => false,
                        'metadata' => [
                            'source' => 'submit_enforcement',
                        ],
                    ],
                    $request->input('answers', []),
                    $request->file('answers', []),
                    $request->input('flagged_field_ids', []),
                    $request->input('field_ids', [])
                );

                if ($request->expectsJson()) {
                    return response()->json([
                        'status' => 'disqualified',
                        'message' => 'Assessment dihentikan karena batas pelanggaran guard ujian telah tercapai.',
                        'redirect_url' => route('assessment.portal.result', $target->id),
                    ]);
                }

                return redirect()
                    ->route('assessment.portal.result', $target->id)
                    ->with('assessment_portal_warning', 'Assessment dihentikan karena batas pelanggaran guard ujian telah tercapai.');
            }

            if ($this->attemptLifecycleService->isPastDeadline($target)) {
                $expiredAttempt = $this->attemptService->submitExpiredStage(
                    $attempt,
                    $stageIndex,
                    $request->input('answers', []),
                    $request->file('answers', []),
                    $request->input('flagged_field_ids', [])
                );

                $redirectUrl = $expiredAttempt->status === 'submitted'
                    ? route('assessment.portal.result', $target->id)
                    : route('assessment.portal.show', $target->id);

                if ($request->expectsJson()) {
                    return response()->json([
                        'status' => 'expired_submitted',
                        'message' => 'Batas waktu berakhir. Jawaban terakhir langsung diproses dan soal kosong diberi skor 0.',
                        'redirect_url' => $redirectUrl,
                    ]);
                }

                return redirect($redirectUrl)
                    ->with('assessment_portal_warning', 'Batas waktu tahap berakhir. Jawaban terakhir diproses otomatis.');
            }

            $submittedAttempt = $this->attemptService->submitStage(
                $attempt,
                $stageIndex,
                $request->input('answers', []),
                $request->file('answers', []),
                $request->input('flagged_field_ids', [])
            );

            if ($submittedAttempt->status === 'submitted') {
                return redirect()
                    ->route('assessment.portal.result', $target->id)
                    ->with('assessment_portal_success', 'Semua tahap assessment berhasil dikirim.');
            }

            return redirect()
                ->route('assessment.portal.show', $target->id)
                ->with('assessment_portal_success', 'Tahap assessment berhasil disimpan permanen.');
        }

        $attempt = $this->attemptLifecycleService->ensureAttempt($target, true);

        if ($this->attemptSecurityService->hasReachedSeriousLimit($attempt) && ! $attempt->disqualified_at) {
            $this->attemptSecurityService->disqualify(
                $attempt,
                [
                    'reason' => 'Assessment dihentikan karena batas pelanggaran guard ujian telah tercapai.',
                    'record_trigger' => false,
                    'metadata' => [
                        'source' => 'submit_enforcement',
                    ],
                ],
                $request->input('answers', []),
                $request->file('answers', []),
                $request->input('flagged_field_ids', [])
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'disqualified',
                    'message' => 'Assessment dihentikan karena batas pelanggaran guard ujian telah tercapai.',
                    'redirect_url' => route('assessment.portal.result', $target->id),
                ]);
            }

            return redirect()
                ->route('assessment.portal.result', $target->id)
                ->with('assessment_portal_warning', 'Assessment dihentikan karena batas pelanggaran guard ujian telah tercapai.');
        }

        if ($this->attemptLifecycleService->isPastDeadline($target)) {
            $this->attemptService->submitExpired(
                $attempt,
                $request->input('answers', []),
                $request->file('answers', []),
                $request->input('flagged_field_ids', []),
                $request->has('field_ids') ? $request->input('field_ids', []) : null
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'expired_submitted',
                    'message' => 'Batas waktu berakhir. Jawaban terakhir langsung diproses dan soal kosong diberi skor 0.',
                    'redirect_url' => route('assessment.portal.result', $target->id),
                ]);
            }

            return redirect()
                ->route('assessment.portal.result', $target->id)
                ->with('assessment_portal_warning', 'Batas waktu berakhir. Jawaban terakhir langsung diproses dan soal kosong diberi skor 0.');
        }

        $meta = $this->portalService->buildTargetMeta($target->fresh([
            'assignment.assessments.forms.fields',
            'assignment.combination',
            'combination',
            'session',
            'attempt',
        ]));

        if (! in_array($meta['status'], ['ready', 'in_progress'], true)) {
            return redirect()
                ->route('assessment.portal.dashboard')
                ->withErrors([
                    'portal' => $meta['description'],
                ]);
        }

        $this->attemptService->submit(
            $attempt,
            $request->input('answers', []),
            $request->file('answers', []),
            $request->input('flagged_field_ids', [])
        );

        return redirect()
            ->route('assessment.portal.result', $target->id)
            ->with('assessment_portal_success', 'Jawaban assessment berhasil dikirim.');
    }

    public function securityViolation(Request $request, string $id): JsonResponse
    {
        $guru = $this->requireGuru();
        $target = $this->portalService->findTargetForGuru($guru, (int) $id);
        $meta = $this->portalService->buildTargetMeta($target);

        if ($meta['status'] === 'submitted') {
            return response()->json([
                'status' => 'submitted',
                'message' => optional($target->attempt)->disqualification_reason ?: 'Assessment ini sudah selesai diproses.',
                'redirect_url' => route('assessment.portal.result', $target->id),
            ]);
        }

        if (
            ! in_array($meta['status'], ['ready', 'in_progress'], true) &&
            ! $this->attemptLifecycleService->isPastDeadline($target)
        ) {
            return response()->json([
                'message' => $meta['description'],
            ], 422);
        }

        $attempt = $this->attemptLifecycleService->ensureAttempt($target, false);

        if ($attempt->status === 'submitted') {
            return response()->json([
                'status' => 'submitted',
                'message' => $attempt->disqualification_reason ?: 'Assessment ini sudah selesai diproses.',
                'redirect_url' => route('assessment.portal.result', $target->id),
            ]);
        }

        $stageIndex = $this->usesStageFlow($target, $attempt)
            ? $this->resolveRequestedStageIndex($request, $attempt)
            : null;

        if ($this->attemptLifecycleService->isPastDeadline($target)) {
            if ($stageIndex !== null) {
                $expiredAttempt = $this->attemptService->submitExpiredStage($attempt, $stageIndex);
                $redirectUrl = $expiredAttempt->status === 'submitted'
                    ? route('assessment.portal.result', $target->id)
                    : route('assessment.portal.show', $target->id);
            } else {
                $this->attemptService->submitExpired($attempt);
                $redirectUrl = route('assessment.portal.result', $target->id);
            }

            return response()->json([
                'status' => 'expired_submitted',
                'message' => 'Batas waktu berakhir. Jawaban terakhir langsung diproses dan soal kosong diberi skor 0.',
                'redirect_url' => $redirectUrl,
            ]);
        }

        $validated = $request->validate([
            'event_key' => 'required|string|max:100',
            'message' => 'required|string|max:2000',
            'type' => 'required|string|in:intentional,unintentional,system',
            'mode' => 'nullable|string|max:32',
            'client_occurred_at' => 'nullable|date',
            'metadata' => 'nullable|array',
        ]);

        $state = $this->attemptSecurityService->registerViolation($attempt, array_merge(
            $validated,
            [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        ), $stageIndex);

        return response()->json(array_merge($state, [
            'redirect_url' => $state['status'] === 'submitted'
                ? route('assessment.portal.result', $target->id)
                : null,
        ]));
    }

    public function securityDisqualify(Request $request, string $id): JsonResponse
    {
        $guru = $this->requireGuru();
        $target = $this->portalService->findTargetForGuru($guru, (int) $id);
        $meta = $this->portalService->buildTargetMeta($target);

        if ($meta['status'] === 'submitted') {
            return response()->json([
                'status' => 'submitted',
                'message' => optional($target->attempt)->disqualification_reason ?: 'Assessment ini sudah selesai diproses.',
                'redirect_url' => route('assessment.portal.result', $target->id),
            ]);
        }

        if (
            ! in_array($meta['status'], ['ready', 'in_progress'], true) &&
            ! $this->attemptLifecycleService->isPastDeadline($target)
        ) {
            return response()->json([
                'message' => $meta['description'],
            ], 422);
        }

        $attempt = $this->attemptLifecycleService->ensureAttempt($target, false);

        $stageIndex = $this->usesStageFlow($target, $attempt)
            ? $this->resolveRequestedStageIndex($request, $attempt)
            : null;

        if ($this->attemptLifecycleService->isPastDeadline($target)) {
            if ($stageIndex !== null) {
                $expiredAttempt = $this->attemptService->submitExpiredStage(
                    $attempt,
                    $stageIndex,
                    $request->input('answers', []),
                    $request->file('answers', []),
                    $request->input('flagged_field_ids', [])
                );
                $redirectUrl = $expiredAttempt->status === 'submitted'
                    ? route('assessment.portal.result', $target->id)
                    : route('assessment.portal.show', $target->id);
            } else {
                $this->attemptService->submitExpired(
                    $attempt,
                    $request->input('answers', []),
                    $request->file('answers', []),
                    $request->input('flagged_field_ids', [])
                );
                $redirectUrl = route('assessment.portal.result', $target->id);
            }

            return response()->json([
                'status' => 'expired_submitted',
                'message' => 'Batas waktu berakhir. Jawaban terakhir langsung diproses dan soal kosong diberi skor 0.',
                'redirect_url' => $redirectUrl,
            ]);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:2000',
            'record_trigger' => 'nullable|boolean',
            'client_occurred_at' => 'nullable|date',
            'metadata' => 'nullable|array',
            'trigger_event' => 'nullable|array',
            'trigger_event.event_key' => 'required_with:trigger_event|string|max:100',
            'trigger_event.message' => 'required_with:trigger_event|string|max:2000',
            'trigger_event.type' => 'required_with:trigger_event|string|in:intentional,unintentional,system',
            'trigger_event.mode' => 'nullable|string|max:32',
            'trigger_event.client_occurred_at' => 'nullable|date',
            'trigger_event.metadata' => 'nullable|array',
        ]);

        $disqualifiedAttempt = $this->attemptSecurityService->disqualify(
            $attempt,
            array_merge(
                $validated,
                [
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]
            ),
            $request->input('answers', []),
            $request->file('answers', []),
            $request->input('flagged_field_ids', []),
            $request->has('field_ids') ? $request->input('field_ids', []) : null
        );

        return response()->json([
            'status' => 'disqualified',
            'message' => $disqualifiedAttempt->disqualification_reason
                ?: 'Assessment dihentikan oleh sistem guard karena pelanggaran aturan ujian.',
            'redirect_url' => route('assessment.portal.result', $target->id),
            'security' => $this->attemptSecurityService->buildClientPayload($disqualifiedAttempt, $stageIndex),
        ]);
    }

    public function result(Request $request, string $id)
    {
        $resultContext = $this->resolveResultContext($request, (int) $id);

        if ($resultContext instanceof RedirectResponse) {
            return $resultContext;
        }

        $viewerMode = $resultContext['viewerMode'];
        $guru = $resultContext['guru'];
        $target = $resultContext['target'];
        $attempt = $resultContext['attempt'];
        $isStakeholderDownloadAvailable = $this->canDownloadStakeholderResult($target);
        $answerLookup = $this->attemptService->buildAnswerLookup($attempt);

        return view('assessment.result.result', [
            'menu' => $viewerMode === 'admin' ? 'assessment-monitoring' : 'assessment-portal',
            'guru' => $guru,
            'target' => $target,
            'attempt' => $attempt,
            'meta' => $this->portalService->buildTargetMeta($target),
            'summary' => $this->attemptService->buildResultSummary($attempt),
            'scoringSummary' => $this->attemptService->buildScoringSummary($attempt),
            'answerLookup' => $answerLookup,
            'trainingSummary' => AssessmentTrainingSummaryHelper::buildAttemptSummaryFromSnapshot(
                is_array($attempt->structure_snapshot ?? null) ? $attempt->structure_snapshot : [],
                $answerLookup
            ),
            'viewerMode' => $viewerMode,
            'backUrl' => $viewerMode === 'admin'
                ? $this->resolveAdminResultBackUrl($request, $target)
                : route('assessment.portal.dashboard'),
            'backLabel' => $viewerMode === 'admin'
                ? 'Kembali ke Monitoring'
                : 'Kembali ke Dashboard',
            'isStakeholderDownloadAvailable' => $isStakeholderDownloadAvailable,
            'stakeholderResultDownloadUrl' => $isStakeholderDownloadAvailable
                ? route('assessment.portal.result.download', $target->id)
                : null,
        ]);
    }

    public function downloadResultPdf(Request $request, string $id)
    {
        $resultContext = $this->resolveResultContext($request, (int) $id);

        if ($resultContext instanceof RedirectResponse) {
            return $resultContext;
        }

        $guru = $resultContext['guru'];
        $target = $resultContext['target'];
        $attempt = $resultContext['attempt'];

        abort_unless($this->canDownloadStakeholderResult($target), 404);

        $answerLookup = $this->attemptService->buildAnswerLookup($attempt);
        $pdf = Pdf::loadView('assessment.result.pdf.stakeholder', [
            'guru' => $guru,
            'target' => $target,
            'attempt' => $attempt,
            'summary' => $this->attemptService->buildResultSummary($attempt),
            'scoringSummary' => $this->attemptService->buildScoringSummary($attempt),
            'generatedAt' => now(),
            'assessmentSections' => $this->buildStakeholderPdfSections($attempt, $answerLookup),
            'targetKetenagaanLabel' => $target->assignment->target_ketenagaan_label ?: 'Stakeholder',
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf->download($this->buildStakeholderPdfFilename($target, $guru));
    }

    private function requireGuru(): Guru
    {
        $guru = $this->authService->currentGuru();

        abort_unless($guru, 403);

        return $guru;
    }

    private function decodeClientSnapshotBucket(mixed $rawBucket): array
    {
        if (is_array($rawBucket)) {
            return $rawBucket;
        }

        if (! is_string($rawBucket) || trim($rawBucket) === '') {
            return [];
        }

        $decodedBucket = json_decode($rawBucket, true);

        return is_array($decodedBucket) ? $decodedBucket : [];
    }

    private function usesStageFlow(
        AssessmentAssignmentTarget $target,
        ?AssessmentAttempt $attempt = null
    ): bool {
        $attempt = $attempt ?: $target->attempt;

        if ($attempt) {
            $snapshot = is_array($attempt->structure_snapshot ?? null) ? $attempt->structure_snapshot : [];
            $progress = is_array($attempt->progress_snapshot ?? null) ? $attempt->progress_snapshot : null;

            if (AssessmentStageProgress::usesStageFlow($snapshot, $progress)) {
                return true;
            }
        }

        $target->loadMissing('assignment.assessments');

        return $target->assignment->assessments
            ->values()
            ->contains(function ($assessment, int $index) {
                return AssessmentStageConfig::isEnabled(
                    AssessmentStageConfig::normalize(
                        is_array($assessment->pivot?->stage_config ?? null) ? $assessment->pivot->stage_config : [],
                        AssessmentStageConfig::defaultForAssessment($assessment->instrument_type, $index)
                    )
                );
            });
    }

    private function resolveStageProgress(AssessmentAttempt $attempt): array
    {
        return AssessmentStageProgress::normalize(
            $attempt->progress_snapshot,
            is_array($attempt->structure_snapshot ?? null) ? $attempt->structure_snapshot : []
        );
    }

    private function resolveRequestedStageIndex(Request $request, AssessmentAttempt $attempt): int
    {
        $progress = $this->resolveStageProgress($attempt);
        $rawStageIndex = $request->query(
            'stage',
            $request->input('stage_index', $request->input('active_assessment_index', -1))
        );

        return AssessmentStageProgress::resolveCurrentStageIndex(
            $progress,
            is_numeric($rawStageIndex) ? (int) $rawStageIndex : -1
        );
    }

    private function hasRequestedStageSelection(Request $request): bool
    {
        return $request->query->has('stage');
    }

    /**
     * @return array<string, mixed>|\Illuminate\Http\RedirectResponse
     */
    private function resolveResultContext(Request $request, int $targetId): array|RedirectResponse
    {
        $viewerMode = 'participant';

        if ($this->canAdminViewParticipantResult()) {
            $viewerMode = 'admin';
            $target = $this->findTargetForAdminResult($targetId);
            $guru = $target->guru;

            abort_unless($guru, 404);
        } elseif ($this->authService->isAuthenticated()) {
            $guru = $this->requireGuru();
            $target = $this->portalService->findTargetForGuru($guru, $targetId);
        } else {
            return redirect()
                ->route('assessment.portal.auth')
                ->with('assessment_portal_notice', 'Silakan login terlebih dahulu untuk melihat hasil assessment.');
        }

        $target = $this->attemptLifecycleService->syncExpiredTarget($target);
        $attempt = $target->attempt;

        if (! $attempt) {
            if ($viewerMode === 'admin') {
                return redirect()
                    ->to($this->resolveAdminResultBackUrl($request, $target))
                    ->withErrors([
                        'portal' => 'Peserta ini belum memulai assessment.',
                    ]);
            }

            return redirect()->route('assessment.portal.show', $target->id);
        }

        if ($attempt->status !== 'submitted') {
            if ($viewerMode === 'admin') {
                return redirect()
                    ->to($this->resolveAdminResultBackUrl($request, $target))
                    ->withErrors([
                        'portal' => 'Assessment peserta ini belum selesai dikirim.',
                    ]);
            }

            return redirect()
                ->route('assessment.portal.show', $target->id)
                ->withErrors([
                    'portal' => 'Assessment ini belum selesai dikirim.',
                ]);
        }

        return [
            'viewerMode' => $viewerMode,
            'guru' => $guru,
            'target' => $target,
            'attempt' => $attempt,
        ];
    }

    private function canAdminViewParticipantResult(): bool
    {
        return session('cek') && in_array(session('role'), self::ADMIN_RESULT_ROLES, true);
    }

    private function findTargetForAdminResult(int $targetId): AssessmentAssignmentTarget
    {
        return AssessmentAssignmentTarget::with([
            'assignment.assessments.forms.fields',
            'assignment.combination',
            'combination',
            'session',
            'guru',
            'attempt.answers',
            'attempt.securityEvents',
        ])->findOrFail($targetId);
    }

    private function resolveAdminResultBackUrl(Request $request, AssessmentAssignmentTarget $target): string
    {
        $referer = (string) $request->headers->get('referer', '');

        if (
            $referer !== ''
            && $referer !== $request->fullUrl()
            && str_contains($referer, '/dashboard/assessment/')
        ) {
            return $referer;
        }

        return route('assessment.assignment.show', $target->assessment_assignment_id).'#monitoring-explorer';
    }

    private function canDownloadStakeholderResult(AssessmentAssignmentTarget $target): bool
    {
        return AssessmentKetenagaanType::tryFromMixed($target->assignment->target_ketenagaan)
            === AssessmentKetenagaanType::STAKEHOLDER;
    }

    private function buildStakeholderPdfFilename(AssessmentAssignmentTarget $target, Guru $guru): string
    {
        $assignmentCode = Str::slug((string) ($target->assignment->kode_penugasan ?: 'assignment'));
        $participantName = Str::slug((string) ($guru->nama_lengkap ?: 'peserta'));

        return sprintf('hasil-assessment-stakeholder-%s-%s.pdf', $assignmentCode, $participantName);
    }

    private function buildStakeholderPdfSections(AssessmentAttempt $attempt, array $answerLookup): array
    {
        $snapshot = $attempt->structure_snapshot ?? [];

        return collect($snapshot['assessments'] ?? [])
            ->values()
            ->map(function (array $assessment) use ($answerLookup) {
                $isMultipleChoiceAssessment = ($assessment['instrument_type'] ?? null)
                    === AssessmentInstrumentType::PILIHAN_GANDA_KOMPLEKS->value;
                $questionNumber = 1;

                $forms = collect($assessment['forms'] ?? [])
                    ->values()
                    ->map(function (array $form) use (
                        &$questionNumber,
                        $answerLookup,
                        $isMultipleChoiceAssessment
                    ) {
                        $questions = collect($form['fields'] ?? [])
                            ->values()
                            ->map(function (array $field) use (
                                &$questionNumber,
                                $answerLookup,
                                $isMultipleChoiceAssessment
                            ) {
                                $fieldId = (int) ($field['id'] ?? 0);

                                if ($fieldId <= 0) {
                                    return null;
                                }

                                $answer = $answerLookup[$fieldId] ?? null;
                                $questionItem = [
                                    'field_id' => $fieldId,
                                    'type' => $field['tipe_field'] ?? 'text',
                                    'label' => $this->buildDisplayFieldLabel(
                                        $field,
                                        $questionNumber,
                                        $isMultipleChoiceAssessment ? 'Soal' : null
                                    ),
                                    'description' => trim((string) ($field['deskripsi'] ?? '')),
                                    'help' => trim((string) ($field['bantuan'] ?? '')),
                                    'is_required' => (bool) ($field['is_required'] ?? false),
                                    'has_answer' => \App\Support\Assessment\AssessmentAnswerViewHelper::hasAnswer(
                                        $field,
                                        $answer
                                    ),
                                    'answer_text' => \App\Support\Assessment\AssessmentAnswerViewHelper::resolveAnswerText(
                                        $field,
                                        $answer
                                    ),
                                    'answered_at' => data_get($answer, 'answered_at'),
                                    'repeater_columns' => \App\Support\Assessment\AssessmentAnswerViewHelper::resolveRepeaterColumns(
                                        $field,
                                        $answer
                                    ),
                                    'repeater_rows' => \App\Support\Assessment\AssessmentAnswerViewHelper::resolveRepeaterRows(
                                        $answer
                                    ),
                                    'file_name' => trim((string) (data_get($answer, 'payload.original_name')
                                        ?: data_get($answer, 'text', ''))),
                                    'file_url' => data_get($answer, 'file_url'),
                                    'file_preview_data_uri' => $this->buildPdfImageDataUri(data_get($answer, 'file_path')),
                                ];

                                $questionNumber++;

                                return $questionItem;
                            })
                            ->filter()
                            ->values()
                            ->all();

                        if ($questions === []) {
                            return null;
                        }

                        return [
                            'title' => $form['judul_form'] ?? 'Form Tanpa Judul',
                            'code' => $form['kode_form'] ?? null,
                            'description' => trim((string) ($form['deskripsi'] ?? '')),
                            'competency_label' => $form['kompetensi_label'] ?? null,
                            'indicator_code' => $form['indikator_kode'] ?? null,
                            'indicator_label' => $form['indikator_label'] ?? null,
                            'questions' => $questions,
                        ];
                    })
                    ->filter()
                    ->values()
                    ->all();

                if ($forms === []) {
                    return null;
                }

                return [
                    'title' => $assessment['judul'] ?? 'Assessment',
                    'code' => $assessment['kode_assessment'] ?? null,
                    'description' => trim((string) ($assessment['deskripsi'] ?? '')),
                    'instrument_label' => $assessment['instrument_label'] ?? null,
                    'forms' => $forms,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function buildDisplayFieldLabel(
        array $field,
        ?int $displayQuestionNumber = null,
        ?string $displayQuestionPrefix = null
    ): string {
        $normalizedLabel = $this->normalizeFieldLabel($field);

        if (! $displayQuestionNumber || $normalizedLabel === '') {
            return $normalizedLabel;
        }

        $displayLead = filled($displayQuestionPrefix)
            ? trim($displayQuestionPrefix).' '.$displayQuestionNumber
            : (string) $displayQuestionNumber;

        return trim($displayLead.'. '.$normalizedLabel);
    }

    private function normalizeFieldLabel(array $field): string
    {
        $fieldLabel = trim((string) ($field['label'] ?? ''));

        if ($fieldLabel === '') {
            return $fieldLabel;
        }

        return preg_replace(
            '/^\s*(?:soal\s*)?\d+\s*[\.\)\-:]?\s*/iu',
            '',
            $fieldLabel,
            1
        ) ?? $fieldLabel;
    }

    private function buildPdfImageDataUri(?string $filePath): ?string
    {
        $filePath = trim((string) $filePath);

        if ($filePath === '') {
            return null;
        }

        if (! Storage::disk('public')->exists($filePath)) {
            return null;
        }

        $extension = Str::lower(pathinfo($filePath, PATHINFO_EXTENSION));

        if (! in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'webp'], true)) {
            return null;
        }

        try {
            $mimeType = Storage::disk('public')->mimeType($filePath) ?: match ($extension) {
                'jpg', 'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                default => 'image/png',
            };
            $binaryContents = Storage::disk('public')->get($filePath);
            $normalizedDataUri = $this->buildNormalizedPdfImageDataUri($binaryContents);

            if ($normalizedDataUri !== null) {
                return $normalizedDataUri;
            }

            return sprintf('data:%s;base64,%s', $mimeType, base64_encode($binaryContents));
        } catch (\Throwable $exception) {
            return null;
        }
    }

    private function buildNormalizedPdfImageDataUri(string $binaryContents): ?string
    {
        if (
            ! function_exists('imagecreatefromstring')
            || ! function_exists('imagecreatetruecolor')
            || ! function_exists('imagepng')
        ) {
            return null;
        }

        $image = @imagecreatefromstring($binaryContents);

        if ($image === false) {
            return null;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $canvas = imagecreatetruecolor($width, $height);

        if ($canvas === false) {
            imagedestroy($image);

            return null;
        }

        imagealphablending($canvas, false);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, $width, $height, $transparent);
        imagesavealpha($canvas, true);
        imagecopy($canvas, $image, 0, 0, 0, 0, $width, $height);

        ob_start();
        $written = imagepng($canvas);
        $pngContents = ob_get_clean();

        imagedestroy($canvas);
        imagedestroy($image);

        if (! $written || ! is_string($pngContents) || $pngContents === '') {
            return null;
        }

        return sprintf(
            'data:%s;base64,%s',
            $this->normalizePdfPreviewMimeType(),
            base64_encode($pngContents)
        );
    }

    private function normalizePdfPreviewMimeType(): string
    {
        return 'image/png';
    }
}
