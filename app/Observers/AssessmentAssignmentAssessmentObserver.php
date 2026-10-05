<?php

namespace App\Observers;

use App\Models\Pivots\AssessmentAssignmentAssessment;
use App\Services\Assessment\AssessmentTargetSyncDispatcher;

class AssessmentAssignmentAssessmentObserver
{
    public function saved(AssessmentAssignmentAssessment $pivot): void
    {
        app(AssessmentTargetSyncDispatcher::class)->assignments([(int) $pivot->assessment_assignment_id]);
    }

    public function deleting(AssessmentAssignmentAssessment $pivot): void
    {
        app(AssessmentTargetSyncDispatcher::class)->assignments([(int) $pivot->assessment_assignment_id]);
    }
}
