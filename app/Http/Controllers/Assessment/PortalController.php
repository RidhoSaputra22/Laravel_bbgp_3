<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Services\Assessment\AssessmentAttemptLifecycleService;
use App\Services\Assessment\AssessmentAttemptService;
use App\Services\Assessment\AssessmentPortalAuthService;
use App\Services\Assessment\AssessmentPortalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortalController extends Controller
{
    public function __construct(
        private readonly AssessmentPortalAuthService $authService,
        private readonly AssessmentPortalService $portalService,
        private readonly AssessmentAttemptLifecycleService $attemptLifecycleService,
        private readonly AssessmentAttemptService $attemptService
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

    public function show(string $id)
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

        $attempt = $this->attemptLifecycleService->ensureAttempt($target, true);
        $freshTarget = $target->fresh(['assignment.assessments.forms.fields', 'session', 'attempt.answers']);

        return view('assessment.show.show', [
            'menu' => 'assessment-portal',
            'guru' => $guru,
            'target' => $freshTarget,
            'attempt' => $attempt,
            'meta' => $this->portalService->buildTargetMeta($freshTarget),
            'answerLookup' => $this->attemptService->buildAnswerLookup($attempt),
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

        if ($this->attemptLifecycleService->isPastDeadline($target)) {
            $this->attemptService->submitExpired(
                $attempt,
                $request->input('answers', []),
                $request->file('answers', [])
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
            $request->input('field_ids', [])
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

        if ($this->attemptLifecycleService->isPastDeadline($target)) {
            $this->attemptService->submitExpired(
                $attempt,
                $request->input('answers', []),
                $request->file('answers', [])
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

        $meta = $this->portalService->buildTargetMeta($target->fresh(['assignment.assessments.forms.fields', 'session', 'attempt']));

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
            $request->file('answers', [])
        );

        return redirect()
            ->route('assessment.portal.result', $target->id)
            ->with('assessment_portal_success', 'Jawaban assessment berhasil dikirim.');
    }

    public function result(string $id)
    {
        $guru = $this->requireGuru();
        $target = $this->portalService->findTargetForGuru($guru, (int) $id);
        $target = $this->attemptLifecycleService->syncExpiredTarget($target);
        $attempt = $target->attempt;

        if (! $attempt) {
            return redirect()->route('assessment.portal.show', $target->id);
        }

        if ($attempt->status !== 'submitted') {
            return redirect()
                ->route('assessment.portal.show', $target->id)
                ->withErrors([
                    'portal' => 'Assessment ini belum selesai dikirim.',
                ]);
        }

        return view('assessment.result.result', [
            'menu' => 'assessment-portal',
            'guru' => $guru,
            'target' => $target,
            'attempt' => $attempt,
            'meta' => $this->portalService->buildTargetMeta($target),
            'summary' => $this->attemptService->buildResultSummary($attempt),
            'scoringSummary' => $this->attemptService->buildScoringSummary($attempt),
            'answerLookup' => $this->attemptService->buildAnswerLookup($attempt),
        ]);
    }

    private function requireGuru(): Guru
    {
        $guru = $this->authService->currentGuru();

        abort_unless($guru, 403);

        return $guru;
    }
}
