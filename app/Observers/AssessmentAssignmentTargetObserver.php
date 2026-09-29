<?php

namespace App\Observers;

use App\Jobs\SyncAssessmentTargetsToMongoJob;
use App\Models\AssessmentAssignmentTarget;

class AssessmentAssignmentTargetObserver
{
    public function saved(AssessmentAssignmentTarget $target): void
    {
        SyncAssessmentTargetsToMongoJob::dispatchIds([(int) $target->getKey()]);
    }

    public function deleted(AssessmentAssignmentTarget $target): void
    {
        SyncAssessmentTargetsToMongoJob::dispatchIds([(int) $target->getKey()]);
    }
}
