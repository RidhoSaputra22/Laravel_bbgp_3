<?php

namespace App\Services\Assessment;

use App\Models\AssessmentAttempt;

class AssessmentAttemptReviewService
{
    public function __construct(
        private readonly AssessmentAttemptService $attemptService
    ) {}

    public function buildManualReviewFields(AssessmentAttempt $attempt): array
    {
        return [];
    }

    public function scoreAttempt(
        AssessmentAttempt $attempt,
        array $scores,
        array $notes,
        ?int $assessorUserId = null
    ): AssessmentAttempt {
        $attempt = $attempt->fresh([
            'answers',
            'target.assignment.assessments.forms.fields',
            'target.assignment.combination',
            'target.combination',
            'target.session',
            'target.guru',
        ]);

        $this->attemptService->refreshScoringSummary($attempt);

        return $attempt->fresh([
            'answers',
            'target.assignment.assessments.forms.fields',
            'target.assignment.combination',
            'target.combination',
            'target.session',
            'target.guru',
        ]);
    }
}
