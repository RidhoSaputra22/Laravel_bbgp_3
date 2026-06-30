<?php

namespace App\Support\Assessment;

use App\Models\AssessmentAssignment;
use App\Models\AssessmentAssignmentTarget;
use Illuminate\Support\Carbon;

class AssessmentTargetTiming
{
    public static function resolveDurationMinutes(AssessmentAssignmentTarget $target): ?int
    {
        $sessionDurationHours = (int) (optional($target->session)->durasi_sesi_jam ?: 0);

        if ($sessionDurationHours > 0) {
            return $sessionDurationHours * 60;
        }

        $assignmentDurationHours = (int) ($target->assignment->durasi_sesi_jam ?: 0);

        return $assignmentDurationHours > 0
            ? $assignmentDurationHours * 60
            : null;
    }

    public static function resolveAssignmentStartAt(AssessmentAssignment $assignment): ?Carbon
    {
        if (! $assignment->tanggal_mulai) {
            return null;
        }

        if ($assignment->jam_mulai_label) {
            return Carbon::parse(
                $assignment->tanggal_mulai->format('Y-m-d').' '.$assignment->jam_mulai_label
            );
        }

        return $assignment->tanggal_mulai->copy()->startOfDay();
    }

    public static function resolveAssignmentDeadlineAt(AssessmentAssignment $assignment): ?Carbon
    {
        return $assignment->tanggal_selesai?->copy()->endOfDay();
    }

    public static function resolveScheduledDeadlineAt(AssessmentAssignmentTarget $target): ?Carbon
    {
        $sessionDeadlineAt = optional($target->session)->waktu_selesai?->copy();
        $assignmentDeadlineAt = self::resolveAssignmentDeadlineAt($target->assignment);

        if ($sessionDeadlineAt && $assignmentDeadlineAt) {
            return $sessionDeadlineAt->lessThan($assignmentDeadlineAt)
                ? $sessionDeadlineAt
                : $assignmentDeadlineAt;
        }

        return $sessionDeadlineAt ?: $assignmentDeadlineAt;
    }

    public static function resolveStartedAt(AssessmentAssignmentTarget $target): ?Carbon
    {
        if ($target->started_at) {
            return $target->started_at->copy();
        }

        return optional(self::resolveLoadedAttempt($target))->started_at?->copy();
    }

    public static function resolveRelativeDeadlineAt(
        AssessmentAssignmentTarget $target,
        ?Carbon $startedAt = null
    ): ?Carbon {
        $resolvedStartedAt = $startedAt ?: self::resolveStartedAt($target);
        $durationMinutes = self::resolveDurationMinutes($target);

        if (! $resolvedStartedAt || ! $durationMinutes) {
            return null;
        }

        return $resolvedStartedAt->copy()->addMinutes($durationMinutes);
    }

    public static function resolveDeadlineAt(
        AssessmentAssignmentTarget $target,
        ?Carbon $startedAt = null
    ): ?Carbon {
        $storedDeadlineAt = optional(self::resolveLoadedAttempt($target))->deadline_at?->copy()
            ?: $target->deadline_at?->copy();

        if ($storedDeadlineAt) {
            return $storedDeadlineAt;
        }

        $relativeDeadlineAt = self::resolveRelativeDeadlineAt($target, $startedAt);
        $scheduledDeadlineAt = self::resolveScheduledDeadlineAt($target);

        if ($relativeDeadlineAt && $scheduledDeadlineAt) {
            return $relativeDeadlineAt->lessThan($scheduledDeadlineAt)
                ? $relativeDeadlineAt
                : $scheduledDeadlineAt;
        }

        return $relativeDeadlineAt ?: $scheduledDeadlineAt;
    }

    private static function resolveLoadedAttempt(AssessmentAssignmentTarget $target): mixed
    {
        if (! $target->relationLoaded('attempt')) {
            return null;
        }

        return $target->getRelation('attempt');
    }
}
