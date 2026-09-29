<?php

namespace App\Observers;

use App\Jobs\SyncAssessmentTargetsToMongoJob;
use App\Models\AssessmentAttempt;

class AssessmentAttemptObserver
{
    public function saved(AssessmentAttempt $attempt): void
    {
        $targetId = (int) $attempt->assessment_assignment_target_id;

        if ($targetId > 0) {
            SyncAssessmentTargetsToMongoJob::dispatchIds([$targetId]);
        }
    }
}
