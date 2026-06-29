<?php

namespace App\Services\Assessment;

use App\Models\AssessmentAssignmentTarget;
use App\Models\AssessmentAttempt;
use App\Support\Assessment\AssessmentTargetTiming;
use Illuminate\Support\Collection;

class AssessmentAttemptLifecycleService
{
    public function __construct(
        private readonly AssessmentQuestionRandomizerService $randomizer,
        private readonly AssessmentAttemptService $attemptService
    ) {}

    public function ensureAttempt(
        AssessmentAssignmentTarget $target,
        bool $markStarted = true
    ): AssessmentAttempt {
        $attempt = $target->attempt;
        $now = now();

        if (! $attempt) {
            $snapshot = $this->randomizer->buildSnapshot($target);

            $attempt = $target->attempt()->create([
                'status' => $markStarted ? 'in_progress' : 'draft',
                'structure_snapshot' => $snapshot,
                'total_questions' => (int) data_get($snapshot, 'meta.total_questions', 0),
                'required_questions' => (int) data_get($snapshot, 'meta.required_questions', 0),
                'started_at' => $markStarted ? $now : null,
                'last_answered_at' => $markStarted ? $now : null,
            ]);
        } else {
            if (empty($attempt->structure_snapshot)) {
                $snapshot = $this->randomizer->buildSnapshot($target);

                $attempt->forceFill([
                    'structure_snapshot' => $snapshot,
                    'total_questions' => (int) data_get($snapshot, 'meta.total_questions', 0),
                    'required_questions' => (int) data_get($snapshot, 'meta.required_questions', 0),
                ])->save();
            }

            if ($markStarted && $attempt->status !== 'submitted') {
                $attempt->forceFill([
                    'status' => 'in_progress',
                    'started_at' => $attempt->started_at ?: $now,
                ])->save();
            }
        }

        if ($markStarted) {
            $target->forceFill([
                'status' => $target->status === 'selesai' ? 'selesai' : 'dikerjakan',
                'started_at' => $target->started_at ?: $now,
            ])->save();
        }

        return $attempt->fresh([
            'answers',
            'target.assignment.assessments.forms.fields',
            'target.session',
            'target.guru',
        ]);
    }

    public function syncExpiredTarget(AssessmentAssignmentTarget $target): AssessmentAssignmentTarget
    {
        if (! $this->shouldAutoFinalize($target)) {
            return $target;
        }

        $attempt = $this->ensureAttempt($target, false);
        $attempt = $this->attemptService->submitExpired($attempt);

        return $attempt->target->load([
            'assignment.assessments.forms.fields',
            'session',
            'attempt.answers',
            'guru',
        ]);
    }

    public function syncExpiredTargets(Collection $targets): Collection
    {
        return $targets->map(fn (AssessmentAssignmentTarget $target) => $this->syncExpiredTarget($target));
    }

    public function isPastDeadline(AssessmentAssignmentTarget $target): bool
    {
        $deadlineAt = AssessmentTargetTiming::resolveDeadlineAt($target);

        return $deadlineAt ? now()->greaterThanOrEqualTo($deadlineAt) : false;
    }

    private function shouldAutoFinalize(AssessmentAssignmentTarget $target): bool
    {
        if ($target->status === 'dibatalkan') {
            return false;
        }

        if ($target->attempt && $target->attempt->status === 'submitted') {
            return false;
        }

        return $this->isPastDeadline($target);
    }
}
