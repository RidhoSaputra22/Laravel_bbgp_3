<?php

namespace App\Observers;

use App\Jobs\SyncValidatorAssignmentsToMongoJob;
use App\Models\ValidatorAssignment;

class ValidatorAssignmentObserver
{
    public function saved(ValidatorAssignment $assignment): void
    {
        SyncValidatorAssignmentsToMongoJob::dispatchIds([(int) $assignment->getKey()]);
    }

    public function deleted(ValidatorAssignment $assignment): void
    {
        SyncValidatorAssignmentsToMongoJob::dispatchIds([(int) $assignment->getKey()]);
    }
}
