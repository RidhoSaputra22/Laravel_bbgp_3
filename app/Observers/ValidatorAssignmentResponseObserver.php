<?php

namespace App\Observers;

use App\Jobs\SyncValidatorAssignmentsToMongoJob;
use App\Models\ValidatorAssignmentResponse;

class ValidatorAssignmentResponseObserver
{
    public function saved(ValidatorAssignmentResponse $response): void
    {
        SyncValidatorAssignmentsToMongoJob::dispatchIds([
            (int) $response->validator_assignment_id,
        ]);
    }

    public function deleted(ValidatorAssignmentResponse $response): void
    {
        SyncValidatorAssignmentsToMongoJob::dispatchIds([
            (int) $response->validator_assignment_id,
        ]);
    }
}
