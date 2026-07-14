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

class PortalAttemptController extends Controller
{
    public function __construct(
        private readonly AssessmentPortalAuthService $authService,
        private readonly AssessmentPortalService $portalService,
        private readonly AssessmentAttemptLifecycleService $attemptLifecycleService,
        private readonly AssessmentAttemptService $attemptService,
        private readonly AssessmentAttemptSecurityService $attemptSecurityService,
        private readonly AssessmentPortalStageService $stageService
    ) {}

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
                'message' => $attempt->disqualification_reason ?: 'Assessment ini sudah selesai diproses.',
                'redirect_url' => route('assessment.portal.result', $target->id),
            ]);
        }

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

        $stageContext = $this->stageService->resolveMutationContext($request, $target, $attempt);
        $attempt = $stageContext['attempt'];
        $stageIndex = $stageContext['stage_index'];

        if ($stageContext['uses_stage_flow']) {
            if ($stageContext['error'] !== null) {
                return redirect()
                    ->route('assessment.portal.show', $stageContext['redirect_params'])
                    ->withErrors([
                        'portal' => $stageContext['error'],
                    ]);
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
}
