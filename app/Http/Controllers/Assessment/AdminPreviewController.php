<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentAssignmentTarget;
use App\Models\User;
use App\Services\Assessment\AssessmentAdminPreviewService;
use App\Services\Assessment\AssessmentAttemptLifecycleService;
use App\Services\Assessment\AssessmentAttemptSecurityService;
use App\Services\Assessment\AssessmentAttemptService;
use App\Services\Assessment\AssessmentPortalService;
use App\Services\Assessment\AssessmentPortalStageService;
use App\Support\Assessment\AssessmentCertificateLinkHelper;
use App\Support\Assessment\AssessmentFileAttachmentHelper;
use App\Support\Assessment\AssessmentTrainingSummaryHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPreviewController extends Controller
{
    public function __construct(
        private readonly AssessmentAdminPreviewService $previewService,
        private readonly AssessmentPortalService $portalService,
        private readonly AssessmentAttemptLifecycleService $attemptLifecycleService,
        private readonly AssessmentAttemptService $attemptService,
        private readonly AssessmentAttemptSecurityService $attemptSecurityService,
        private readonly AssessmentPortalStageService $stageService
    ) {}

    public function launch(string $assessmentId)
    {
        $this->authorizeAccess();

        $assessment = Assessment::findOrFail($assessmentId);
        $adminUser = $this->currentAdminUser();
        $target = $this->previewService->launch($assessment, $adminUser);

        $this->attemptLifecycleService->ensureAttempt($target, true);

        return redirect()
            ->route('assessment.preview.show', $target->id)
            ->with('assessment_portal_success', 'Preview pengerjaan assessment dimulai.');
    }

    public function start(Request $request, string $id)
    {
        $this->authorizeAccess();

        $target = $this->previewTarget((int) $id);
        $target = $this->attemptLifecycleService->syncExpiredTarget($target);
        $meta = $this->portalService->buildTargetMeta($target);

        if ($meta['status'] === 'submitted') {
            return redirect()->route('assessment.preview.result', $target->id);
        }

        if (! in_array($meta['status'], ['ready', 'in_progress'], true)) {
            return $this->redirectBackToBuilder($target, $meta['description']);
        }

        $attempt = $this->attemptLifecycleService->ensureAttempt($target, false);
        $stageContext = $this->stageService->resolveStartContext($request, $target, $attempt);

        if ($stageContext['uses_stage_flow']) {
            if ($stageContext['error'] !== null) {
                return redirect()
                    ->route('assessment.preview.show', $stageContext['redirect_params'])
                    ->withErrors([
                        'portal' => $stageContext['error'],
                    ]);
            }

            return redirect()
                ->route('assessment.preview.show', $stageContext['redirect_params'])
                ->with('assessment_portal_success', 'Tahap preview assessment dimulai.');
        }

        $this->attemptLifecycleService->ensureAttempt($target, true);

        return redirect()
            ->route('assessment.preview.show', $target->id)
            ->with('assessment_portal_success', 'Preview assessment dimulai.');
    }

    public function show(Request $request, string $id)
    {
        $this->authorizeAccess();

        $target = $this->previewTarget((int) $id);
        $target = $this->attemptLifecycleService->syncExpiredTarget($target);
        $meta = $this->portalService->buildTargetMeta($target);

        if ($meta['status'] === 'submitted') {
            return redirect()->route('assessment.preview.result', $target->id);
        }

        $attempt = $this->attemptLifecycleService->ensureAttempt($target, false);
        $stageState = $this->stageService->resolveShowState($request, $target, $attempt);
        $attempt = $stageState['attempt'];
        $stageFlowEnabled = $stageState['stage_flow_enabled'];
        $renderStageOverview = $stageState['render_stage_overview'];

        if (! $stageFlowEnabled) {
            $attempt = $this->attemptLifecycleService->ensureAttempt($target, true);
            $target = $attempt->target ?: $target;
            $meta = $this->portalService->buildTargetMeta($target);
        }

        if (! in_array($meta['status'], ['ready', 'in_progress'], true)) {
            return $this->redirectBackToBuilder($target, $meta['description']);
        }

        if (
            $this->attemptSecurityService->hasReachedSeriousLimit($attempt, $stageState['current_stage_index'])
            && ! $attempt->disqualified_at
        ) {
            $this->attemptSecurityService->disqualify($attempt, [
                'reason' => 'Preview assessment dihentikan karena batas pelanggaran guard ujian telah tercapai.',
                'record_trigger' => false,
                'metadata' => [
                    'source' => 'admin_preview_show_enforcement',
                ],
            ]);

            return redirect()
                ->route('assessment.preview.result', $target->id)
                ->with('assessment_portal_warning', 'Preview assessment dihentikan karena batas pelanggaran guard ujian telah tercapai.');
        }

        $freshTarget = $this->previewTarget((int) $target->id);
        $freshAttempt = $freshTarget->attempt ?: $attempt->fresh([
            'answers',
            'securityEvents',
            'target.assignment.assessments.forms.fields',
            'target.assignment.combination',
            'target.combination',
            'target.session',
            'target.guru',
        ]);

        $viewData = [
            'menu' => 'assessment-portal',
            'guru' => $freshTarget->guru,
            'target' => $freshTarget,
            'attempt' => $freshAttempt,
            'meta' => $this->portalService->buildTargetMeta($freshTarget),
            'portalUrls' => $this->portalUrls($freshTarget),
            'dashboardLabel' => 'Kembali ke Builder',
            'participantAction' => [
                'method' => 'GET',
                'url' => $this->builderUrl($freshTarget),
                'label' => 'Kembali ke Builder',
                'icon' => 'fas fa-arrow-left',
                'variant' => 'outline',
            ],
            'viewerMode' => 'admin_preview',
        ];

        if ($renderStageOverview) {
            return view('assessment.show.overview', $viewData + [
                'stageOverview' => $this->portalService->buildStageOverview($freshTarget, $freshAttempt),
            ]);
        }

        return view('assessment.show.show', $viewData + [
            'selectedStageIndex' => $stageState['current_stage_index'],
            'answerLookup' => $this->attemptService->buildAnswerLookup($freshAttempt),
            'securityPayload' => $this->attemptSecurityService->buildClientPayload(
                $freshAttempt,
                $stageState['current_stage_index']
            ),
        ]);
    }

    public function autosave(Request $request, string $id): JsonResponse
    {
        $this->authorizeAccess();

        $target = $this->previewTarget((int) $id);
        $meta = $this->portalService->buildTargetMeta($target);

        if ($meta['status'] === 'submitted') {
            return response()->json([
                'status' => 'submitted',
                'message' => 'Preview assessment ini sudah selesai diproses.',
                'redirect_url' => route('assessment.preview.result', $target->id),
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
        $stageContext = $this->stageService->resolveMutationContext($request, $target, $attempt);
        $attempt = $stageContext['attempt'];
        $stageIndex = $stageContext['stage_index'];

        if ($stageContext['error'] !== null) {
            return response()->json([
                'message' => $stageContext['error'],
            ], 422);
        }

        if (! $stageContext['uses_stage_flow']) {
            $attempt = $this->attemptLifecycleService->ensureAttempt($target, true);
        }

        if ($attempt->status === 'submitted') {
            return response()->json([
                'status' => 'submitted',
                'message' => $attempt->disqualification_reason ?: 'Preview assessment ini sudah selesai diproses.',
                'redirect_url' => route('assessment.preview.result', $target->id),
            ]);
        }

        if ($this->attemptSecurityService->hasReachedSeriousLimit($attempt, $stageIndex) && ! $attempt->disqualified_at) {
            $this->attemptSecurityService->disqualify(
                $attempt,
                [
                    'reason' => 'Preview assessment dihentikan karena batas pelanggaran guard ujian telah tercapai.',
                    'record_trigger' => false,
                    'metadata' => [
                        'source' => 'admin_preview_autosave_enforcement',
                    ],
                ],
                $request->input('answers', []),
                $request->file('answers', []),
                $request->input('flagged_field_ids', []),
                $request->input('field_ids', [])
            );

            return response()->json([
                'status' => 'disqualified',
                'message' => 'Preview assessment dihentikan karena batas pelanggaran guard ujian telah tercapai.',
                'redirect_url' => route('assessment.preview.result', $target->id),
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
                    ? route('assessment.preview.result', $target->id)
                    : route('assessment.preview.show', $target->id);
            } else {
                $this->attemptService->submitExpired(
                    $attempt,
                    $request->input('answers', []),
                    $request->file('answers', []),
                    $request->input('flagged_field_ids', []),
                    $request->input('field_ids', [])
                );
                $redirectUrl = route('assessment.preview.result', $target->id);
            }

            return response()->json([
                'status' => 'expired_submitted',
                'message' => 'Batas waktu preview berakhir. Jawaban terakhir langsung diproses dan soal kosong diberi skor 0.',
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
                ? 'Draft tahap preview berhasil disimpan.'
                : 'Snapshot jawaban preview berhasil disimpan.',
            'answered_questions' => $savedAttempt->answered_questions,
            'answered_required_questions' => $savedAttempt->answered_required_questions,
            'saved_at' => optional($savedAttempt->last_answered_at)->toIso8601String(),
            'redirect_url' => $isManualStageDraftSave
                ? route('assessment.preview.show', $target->id)
                : null,
        ]);
    }

    public function submit(Request $request, string $id)
    {
        $this->authorizeAccess();

        $target = $this->previewTarget((int) $id);
        $initialMeta = $this->portalService->buildTargetMeta($target);

        if ($target->attempt && $target->attempt->status === 'submitted') {
            return redirect()->route('assessment.preview.result', $target->id);
        }

        if (
            ! in_array($initialMeta['status'], ['ready', 'in_progress'], true) &&
            ! $this->attemptLifecycleService->isPastDeadline($target)
        ) {
            return $this->redirectBackToBuilder($target, $initialMeta['description']);
        }

        $attempt = $this->attemptLifecycleService->ensureAttempt($target, false);

        if ($attempt->status === 'submitted') {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'submitted',
                    'message' => $attempt->disqualification_reason ?: 'Preview assessment ini sudah selesai diproses.',
                    'redirect_url' => route('assessment.preview.result', $target->id),
                ]);
            }

            return redirect()->route('assessment.preview.result', $target->id);
        }

        $stageContext = $this->stageService->resolveMutationContext($request, $target, $attempt);
        $attempt = $stageContext['attempt'];
        $stageIndex = $stageContext['stage_index'];

        if ($stageContext['uses_stage_flow']) {
            if ($stageContext['error'] !== null) {
                return redirect()
                    ->route('assessment.preview.show', $stageContext['redirect_params'])
                    ->withErrors([
                        'portal' => $stageContext['error'],
                    ]);
            }

            if ($this->attemptSecurityService->hasReachedSeriousLimit($attempt, $stageIndex) && ! $attempt->disqualified_at) {
                $this->attemptSecurityService->disqualify(
                    $attempt,
                    [
                        'reason' => 'Preview assessment dihentikan karena batas pelanggaran guard ujian telah tercapai.',
                        'record_trigger' => false,
                        'metadata' => [
                            'source' => 'admin_preview_submit_enforcement',
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
                        'message' => 'Preview assessment dihentikan karena batas pelanggaran guard ujian telah tercapai.',
                        'redirect_url' => route('assessment.preview.result', $target->id),
                    ]);
                }

                return redirect()
                    ->route('assessment.preview.result', $target->id)
                    ->with('assessment_portal_warning', 'Preview assessment dihentikan karena batas pelanggaran guard ujian telah tercapai.');
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
                    ? route('assessment.preview.result', $target->id)
                    : route('assessment.preview.show', $target->id);

                if ($request->expectsJson()) {
                    return response()->json([
                        'status' => 'expired_submitted',
                        'message' => 'Batas waktu tahap preview berakhir. Jawaban terakhir langsung diproses dan soal kosong diberi skor 0.',
                        'redirect_url' => $redirectUrl,
                    ]);
                }

                return redirect($redirectUrl)
                    ->with('assessment_portal_warning', 'Batas waktu tahap preview berakhir. Jawaban terakhir diproses otomatis.');
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
                    ->route('assessment.preview.result', $target->id)
                    ->with('assessment_portal_success', 'Semua tahap preview assessment berhasil dikirim.');
            }

            return redirect()
                ->route('assessment.preview.show', $target->id)
                ->with('assessment_portal_success', 'Tahap preview assessment berhasil disimpan permanen.');
        }

        $attempt = $this->attemptLifecycleService->ensureAttempt($target, true);

        if ($this->attemptSecurityService->hasReachedSeriousLimit($attempt) && ! $attempt->disqualified_at) {
            $this->attemptSecurityService->disqualify(
                $attempt,
                [
                    'reason' => 'Preview assessment dihentikan karena batas pelanggaran guard ujian telah tercapai.',
                    'record_trigger' => false,
                    'metadata' => [
                        'source' => 'admin_preview_submit_enforcement',
                    ],
                ],
                $request->input('answers', []),
                $request->file('answers', []),
                $request->input('flagged_field_ids', [])
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'disqualified',
                    'message' => 'Preview assessment dihentikan karena batas pelanggaran guard ujian telah tercapai.',
                    'redirect_url' => route('assessment.preview.result', $target->id),
                ]);
            }

            return redirect()
                ->route('assessment.preview.result', $target->id)
                ->with('assessment_portal_warning', 'Preview assessment dihentikan karena batas pelanggaran guard ujian telah tercapai.');
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
                    'message' => 'Batas waktu preview berakhir. Jawaban terakhir langsung diproses dan soal kosong diberi skor 0.',
                    'redirect_url' => route('assessment.preview.result', $target->id),
                ]);
            }

            return redirect()
                ->route('assessment.preview.result', $target->id)
                ->with('assessment_portal_warning', 'Batas waktu preview berakhir. Jawaban terakhir langsung diproses dan soal kosong diberi skor 0.');
        }

        $meta = $this->portalService->buildTargetMeta($this->previewTarget((int) $target->id));

        if (! in_array($meta['status'], ['ready', 'in_progress'], true)) {
            return $this->redirectBackToBuilder($target, $meta['description']);
        }

        $this->attemptService->submit(
            $attempt,
            $request->input('answers', []),
            $request->file('answers', []),
            $request->input('flagged_field_ids', [])
        );

        return redirect()
            ->route('assessment.preview.result', $target->id)
            ->with('assessment_portal_success', 'Jawaban preview assessment berhasil dikirim.');
    }

    public function securityViolation(Request $request, string $id): JsonResponse
    {
        $this->authorizeAccess();

        $target = $this->previewTarget((int) $id);
        $meta = $this->portalService->buildTargetMeta($target);

        if ($meta['status'] === 'submitted') {
            return response()->json([
                'status' => 'submitted',
                'message' => optional($target->attempt)->disqualification_reason ?: 'Preview assessment ini sudah selesai diproses.',
                'redirect_url' => route('assessment.preview.result', $target->id),
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
                'message' => $attempt->disqualification_reason ?: 'Preview assessment ini sudah selesai diproses.',
                'redirect_url' => route('assessment.preview.result', $target->id),
            ]);
        }

        $stageIndex = $this->stageService->resolveStageIndex($request, $target, $attempt);

        if ($this->attemptLifecycleService->isPastDeadline($target)) {
            if ($stageIndex !== null) {
                $expiredAttempt = $this->attemptService->submitExpiredStage($attempt, $stageIndex);
                $redirectUrl = $expiredAttempt->status === 'submitted'
                    ? route('assessment.preview.result', $target->id)
                    : route('assessment.preview.show', $target->id);
            } else {
                $this->attemptService->submitExpired($attempt);
                $redirectUrl = route('assessment.preview.result', $target->id);
            }

            return response()->json([
                'status' => 'expired_submitted',
                'message' => 'Batas waktu preview berakhir. Jawaban terakhir langsung diproses dan soal kosong diberi skor 0.',
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
                ? route('assessment.preview.result', $target->id)
                : null,
        ]));
    }

    public function securityDisqualify(Request $request, string $id): JsonResponse
    {
        $this->authorizeAccess();

        $target = $this->previewTarget((int) $id);
        $meta = $this->portalService->buildTargetMeta($target);

        if ($meta['status'] === 'submitted') {
            return response()->json([
                'status' => 'submitted',
                'message' => optional($target->attempt)->disqualification_reason ?: 'Preview assessment ini sudah selesai diproses.',
                'redirect_url' => route('assessment.preview.result', $target->id),
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
        $stageIndex = $this->stageService->resolveStageIndex($request, $target, $attempt);

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
                    ? route('assessment.preview.result', $target->id)
                    : route('assessment.preview.show', $target->id);
            } else {
                $this->attemptService->submitExpired(
                    $attempt,
                    $request->input('answers', []),
                    $request->file('answers', []),
                    $request->input('flagged_field_ids', [])
                );
                $redirectUrl = route('assessment.preview.result', $target->id);
            }

            return response()->json([
                'status' => 'expired_submitted',
                'message' => 'Batas waktu preview berakhir. Jawaban terakhir langsung diproses dan soal kosong diberi skor 0.',
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
                ?: 'Preview assessment dihentikan oleh sistem guard karena pelanggaran aturan ujian.',
            'redirect_url' => route('assessment.preview.result', $target->id),
            'security' => $this->attemptSecurityService->buildClientPayload($disqualifiedAttempt, $stageIndex),
        ]);
    }

    public function result(string $id)
    {
        $this->authorizeAccess();

        $target = $this->previewTarget((int) $id);
        $attempt = $target->attempt;

        if (! $attempt) {
            return redirect()
                ->route('assessment.preview.show', $target->id)
                ->withErrors([
                    'portal' => 'Preview assessment belum dimulai.',
                ]);
        }

        if ($attempt->status !== 'submitted') {
            return redirect()
                ->route('assessment.preview.show', $target->id)
                ->withErrors([
                    'portal' => 'Preview assessment ini belum selesai dikirim.',
                ]);
        }

        $answerLookup = $this->attemptService->buildAnswerLookup($attempt);
        $structureSnapshot = is_array($attempt->structure_snapshot ?? null) ? $attempt->structure_snapshot : [];

        return view('assessment.result.result', [
            'menu' => 'assessment-portal',
            'guru' => $target->guru,
            'target' => $target,
            'attempt' => $attempt,
            'meta' => $this->portalService->buildTargetMeta($target),
            'summary' => $this->attemptService->buildResultSummary($attempt),
            'scoringSummary' => $this->attemptService->buildScoringSummary($attempt),
            'answerLookup' => $answerLookup,
            'trainingSummary' => AssessmentTrainingSummaryHelper::buildAttemptSummaryFromSnapshot(
                $structureSnapshot,
                $answerLookup
            ),
            'fileAttachments' => AssessmentFileAttachmentHelper::collectFromSnapshot(
                $structureSnapshot,
                $answerLookup
            ),
            'certificateLinks' => AssessmentCertificateLinkHelper::collectFromSnapshot(
                $structureSnapshot,
                $answerLookup
            ),
            'viewerMode' => 'admin_preview',
            'backUrl' => $this->builderUrl($target),
            'backLabel' => 'Kembali ke Builder',
            'isStakeholderDownloadAvailable' => false,
            'stakeholderResultDownloadUrl' => null,
        ]);
    }

    private function authorizeAccess(): void
    {
        abort_unless(
            in_array(session('role'), ['admin', 'superadmin', 'kepala', 'database'], true),
            403
        );
    }

    private function currentAdminUser(): User
    {
        $userId = (int) session('user_id');

        return User::findOrFail($userId);
    }

    private function previewTarget(int $targetId): AssessmentAssignmentTarget
    {
        return $this->previewService->findPreviewTargetForUser(
            $targetId,
            (int) $this->currentAdminUser()->id
        );
    }

    private function builderUrl(AssessmentAssignmentTarget $target): string
    {
        return route('assessment.edit', $this->previewService->resolvePrimaryAssessmentId($target));
    }

    private function portalUrls(AssessmentAssignmentTarget $target): array
    {
        return [
            'dashboard' => $this->builderUrl($target),
            'show' => route('assessment.preview.show', $target->id),
            'start' => route('assessment.preview.start', $target->id),
            'autosave' => route('assessment.preview.autosave', $target->id),
            'submit' => route('assessment.preview.submit', $target->id),
            'violation' => route('assessment.preview.security.violation', $target->id),
            'disqualify' => route('assessment.preview.security.disqualify', $target->id),
            'result' => route('assessment.preview.result', $target->id),
        ];
    }

    private function redirectBackToBuilder(AssessmentAssignmentTarget $target, string $message)
    {
        return redirect()
            ->to($this->builderUrl($target))
            ->withErrors([
                'portal' => $message,
            ]);
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
}
