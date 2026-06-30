<?php

namespace App\Http\Controllers;

use App\Enum\LevelKompetensi;
use App\Models\AssessmentAssignmentTarget;
use App\Services\Assessment\AssessmentAttemptLifecycleService;
use App\Services\Assessment\AssessmentAttemptReviewService;
use App\Services\Assessment\AssessmentAttemptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AssessmentAttemptReviewController extends Controller
{
    public function __construct(
        private readonly AssessmentAttemptLifecycleService $attemptLifecycleService,
        private readonly AssessmentAttemptService $attemptService,
        private readonly AssessmentAttemptReviewService $reviewService
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
            'manualReviewFields' => $this->reviewService->buildManualReviewFields($attempt),
        ]);
    }

    public function update(Request $request, string $targetId)
    {
        $this->authorizeAccess();

        $target = $this->findSubmittedTarget((int) $targetId);
        $attempt = $target->attempt;
        $manualReviewFields = $this->reviewService->buildManualReviewFields($attempt);
        $allowedFieldIds = array_map('strval', array_keys($manualReviewFields));

        $validated = Validator::make(
            $request->all(),
            [
                'scores' => 'nullable|array',
                'scores.*' => [
                    'nullable',
                    'integer',
                    Rule::in(LevelKompetensi::values()),
                ],
                'notes' => 'nullable|array',
                'notes.*' => 'nullable|string',
            ]
        )->after(function ($validator) use ($request, $allowedFieldIds) {
            foreach (array_keys($request->input('scores', [])) as $fieldId) {
                if (! in_array((string) $fieldId, $allowedFieldIds, true)) {
                    $validator->errors()->add('scores', 'Ada field penilaian manual yang tidak valid.');
                    break;
                }
            }
        })->validate();

        $this->reviewService->scoreAttempt(
            $attempt,
            $validated['scores'] ?? [],
            $validated['notes'] ?? [],
            session('user_id') ? (int) session('user_id') : null
        );

        return redirect()
            ->route('assessment.assignment.review.show', $target->id)
            ->with('message', 'update');
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
