<?php

namespace App\Observers;

use App\Jobs\SyncAssessmentTargetsToMongoJob;
use App\Models\Guru;
use Illuminate\Support\Facades\Schema;

class GuruObserver
{
    public function saved(Guru $guru): void
    {
        if (! (bool) config('assessment_mongodb.enabled')
            || ! Schema::hasTable('assessment_assignment_targets')) {
            return;
        }

        SyncAssessmentTargetsToMongoJob::dispatchIds(
            $guru->assessmentAssignmentTargets()->pluck('id')->map(fn ($id) => (int) $id)->all()
        );
    }
}
