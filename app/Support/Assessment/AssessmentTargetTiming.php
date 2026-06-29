<?php

namespace App\Support\Assessment;

use App\Models\AssessmentAssignment;
use App\Models\AssessmentAssignmentTarget;
use Illuminate\Support\Carbon;

class AssessmentTargetTiming
{
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

    public static function resolveDeadlineAt(AssessmentAssignmentTarget $target): ?Carbon
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
}
