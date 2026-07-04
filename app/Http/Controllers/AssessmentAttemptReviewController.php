<?php

namespace App\Http\Controllers;

use App\Models\AssessmentAssignmentTarget;
use App\Services\Assessment\AssessmentAttemptLifecycleService;
use App\Services\Assessment\AssessmentAttemptService;

class AssessmentAttemptReviewController extends Controller
{
    public function __construct(
        private readonly AssessmentAttemptLifecycleService $attemptLifecycleService,
        private readonly AssessmentAttemptService $attemptService
    ) {}

    public function show(string $targetId)
    {
        $this->authorizeAccess();

        $target = $this->findSubmittedTarget((int) $targetId);
        $attempt = $target->attempt;

        return view('pages.admin.assessment.assignment.review', [
            'menu' => 'assessment-penugasan',
            'target' => $target,
            'attempt' => $attempt,
            'summary' => $this->attemptService->buildResultSummary($attempt),
            'scoringSummary' => $this->attemptService->buildScoringSummary($attempt),
            'answerLookup' => $this->attemptService->buildAnswerLookup($attempt),
        ]);
    }

    public function update(string $targetId)
    {
        $this->authorizeAccess();

        $target = $this->findSubmittedTarget((int) $targetId);
        $attempt = $target->attempt;
        $this->attemptService->refreshScoringSummary($attempt);

        return redirect()
            ->route('assessment.assignment.review.show', $target->id)
            ->with('message', 'auto_scoring_only');
    }

    private function authorizeAccess(): void
    {
        abort_unless(
            in_array(session('role'), ['admin', 'superadmin', 'kepala', 'database'], true),
            403
        );
    }

    private function findSubmittedTarget(int $targetId): AssessmentAssignmentTarget
    {
        $target = AssessmentAssignmentTarget::with([
            'assignment.assessments.forms.fields',
            'assignment.combination',
            'session',
            'guru',
            'attempt.answers',
            'attempt.securityEvents',
        ])->findOrFail($targetId);

        $target = $this->attemptLifecycleService->syncExpiredTarget($target);

        abort_unless($target->attempt && $target->attempt->status === 'submitted', 404);

        return $target;
    }
}
