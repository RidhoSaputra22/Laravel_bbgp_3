<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Services\Assessment\AssessmentAttemptLifecycleService;
use App\Services\Assessment\AssessmentAttemptSecurityService;
use App\Services\Assessment\AssessmentAttemptService;
use App\Services\Assessment\AssessmentPortalAuthService;
use App\Services\Assessment\AssessmentPortalService;
use App\Services\Assessment\AssessmentPortalStageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortalSecurityController extends Controller
{
    public function __construct(
        private readonly AssessmentPortalAuthService $authService,
        private readonly AssessmentPortalService $portalService,
        private readonly AssessmentAttemptLifecycleService $attemptLifecycleService,
        private readonly AssessmentAttemptService $attemptService,
        private readonly AssessmentAttemptSecurityService $attemptSecurityService,
        private readonly AssessmentPortalStageService $stageService
    ) {}

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

        $stageIndex = $this->stageService->resolveStageIndex($request, $target, $attempt);

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

    private function requireGuru(): Guru
    {
        $guru = $this->authService->currentGuru();

        abort_unless($guru, 403);

        return $guru;
    }
}
