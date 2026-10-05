<?php

namespace App\Observers;

use App\Models\Assessment;
use App\Services\Assessment\AssessmentTargetSyncDispatcher;

class AssessmentObserver
{
    public function saved(Assessment $assessment): void
    {
        app(AssessmentTargetSyncDispatcher::class)->assessments([(int) $assessment->getKey()]);
    }

    public function deleting(Assessment $assessment): void
    {
        app(AssessmentTargetSyncDispatcher::class)->assessments([(int) $assessment->getKey()]);
    }
}
