<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use App\Models\AssessmentAssignmentTarget;
use App\Models\Guru;
use App\Services\Assessment\AssessmentAttemptLifecycleService;
use App\Services\Assessment\AssessmentAttemptSecurityService;
use App\Services\Assessment\AssessmentAttemptService;
use App\Services\Assessment\AssessmentPortalAuthService;
use App\Services\Assessment\AssessmentPortalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    public function start(string $id)
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

        $this->attemptLifecycleService->ensureAttempt($target, true);

        return redirect()
            ->route('assessment.portal.show', $target->id)
            ->with('assessment_portal_success', 'Assessment dimulai. Timer pengerjaan sudah berjalan.');
    }

    public function show(string $id)
    {
        $guru = $this->requireGuru();
        $target = $this->portalService->findTargetForGuru($guru, (int) $id);
        $target = $this->attemptLifecycleService->syncExpiredTarget($target);
        $meta = $this->portalService->buildTargetMeta($target);

        if ($meta['status'] === 'submitted') {
            return redirect()->route('assessment.portal.result', $target->id);
        }

        if ($meta['status'] === 'ready') {
            return redirect()
                ->route('assessment.portal.dashboard')
                ->withErrors([
                    'portal' => 'Klik tombol Mulai Ujian terlebih dahulu agar waktu mulai dan timer assessment tercatat.',
                ]);
        }

        if ($meta['status'] !== 'in_progress') {
            return redirect()
                ->route('assessment.portal.dashboard')
                ->withErrors([
                    'portal' => $meta['description'],
                ]);
        }

        $attempt = $this->attemptLifecycleService->ensureAttempt($target, true);

        if ($this->attemptSecurityService->hasReachedSeriousLimit($attempt) && ! $attempt->disqualified_at) {
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

        return view('assessment.show.show', [
            'menu' => 'assessment-portal',
            'guru' => $guru,
            'target' => $freshTarget,
            'attempt' => $freshAttempt,
            'meta' => $this->portalService->buildTargetMeta($freshTarget),
            'answerLookup' => $this->attemptService->buildAnswerLookup($freshAttempt),
            'securityPayload' => $this->attemptSecurityService->buildClientPayload($freshAttempt),
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

        $attempt = $this->attemptLifecycleService->ensureAttempt($target, true);

        if ($attempt->status === 'submitted') {
            return response()->json([
                'status' => 'submitted',
                'message' => $attempt->disqualification_reason ?: 'Assessment ini sudah selesai diproses.',
                'redirect_url' => route('assessment.portal.result', $target->id),
            ]);
        }

        if ($this->attemptSecurityService->hasReachedSeriousLimit($attempt) && ! $attempt->disqualified_at) {
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
            $this->attemptService->submitExpired(
                $attempt,
                $request->input('answers', []),
                $request->file('answers', []),
                $request->input('flagged_field_ids', []),
                $request->input('field_ids', [])
            );

            return response()->json([
                'status' => 'expired_submitted',
                'message' => 'Batas waktu berakhir. Jawaban terakhir langsung diproses dan soal kosong diberi skor 0.',
                'redirect_url' => route('assessment.portal.result', $target->id),
            ]);
        }

        $savedAttempt = $this->attemptService->saveSnapshot(
            $attempt,
            $request->input('answers', []),
            $request->file('answers', []),
            $request->input('field_ids', []),
            $request->input('flagged_field_ids', []),
            $this->decodeClientSnapshotBucket($request->input('client_snapshot_bucket'))
        );

        return response()->json([
            'status' => 'saved',
            'message' => 'Snapshot jawaban berhasil disimpan.',
            'answered_questions' => $savedAttempt->answered_questions,
            'answered_required_questions' => $savedAttempt->answered_required_questions,
            'saved_at' => optional($savedAttempt->last_answered_at)->toIso8601String(),
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

        $attempt = $this->attemptLifecycleService->ensureAttempt($target, true);

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

        $attempt = $this->attemptLifecycleService->ensureAttempt($target, true);

        if ($attempt->status === 'submitted') {
            return response()->json([
                'status' => 'submitted',
                'message' => $attempt->disqualification_reason ?: 'Assessment ini sudah selesai diproses.',
                'redirect_url' => route('assessment.portal.result', $target->id),
            ]);
        }

        if ($this->attemptLifecycleService->isPastDeadline($target)) {
            $this->attemptService->submitExpired($attempt);

            return response()->json([
                'status' => 'expired_submitted',
                'message' => 'Batas waktu berakhir. Jawaban terakhir langsung diproses dan soal kosong diberi skor 0.',
                'redirect_url' => route('assessment.portal.result', $target->id),
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
        ));

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

        $attempt = $this->attemptLifecycleService->ensureAttempt($target, true);

        if ($this->attemptLifecycleService->isPastDeadline($target)) {
            $this->attemptService->submitExpired(
                $attempt,
                $request->input('answers', []),
                $request->file('answers', []),
                $request->input('flagged_field_ids', [])
            );

            return response()->json([
                'status' => 'expired_submitted',
                'message' => 'Batas waktu berakhir. Jawaban terakhir langsung diproses dan soal kosong diberi skor 0.',
                'redirect_url' => route('assessment.portal.result', $target->id),
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
            'security' => $this->attemptSecurityService->buildClientPayload($disqualifiedAttempt),
        ]);
    }

    public function result(Request $request, string $id)
    {
        $viewerMode = 'participant';

        if ($this->canAdminViewParticipantResult()) {
            $viewerMode = 'admin';
            $target = $this->findTargetForAdminResult((int) $id);
            $guru = $target->guru;

            abort_unless($guru, 404);
        } elseif ($this->authService->isAuthenticated()) {
            $guru = $this->requireGuru();
            $target = $this->portalService->findTargetForGuru($guru, (int) $id);
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

        return view('assessment.result.result', [
            'menu' => $viewerMode === 'admin' ? 'assessment-monitoring' : 'assessment-portal',
            'guru' => $guru,
            'target' => $target,
            'attempt' => $attempt,
            'meta' => $this->portalService->buildTargetMeta($target),
            'summary' => $this->attemptService->buildResultSummary($attempt),
            'scoringSummary' => $this->attemptService->buildScoringSummary($attempt),
            'answerLookup' => $this->attemptService->buildAnswerLookup($attempt),
            'viewerMode' => $viewerMode,
            'backUrl' => $viewerMode === 'admin'
                ? $this->resolveAdminResultBackUrl($request, $target)
                : route('assessment.portal.dashboard'),
            'backLabel' => $viewerMode === 'admin'
                ? 'Kembali ke Monitoring'
                : 'Kembali ke Dashboard',
        ]);
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
}
