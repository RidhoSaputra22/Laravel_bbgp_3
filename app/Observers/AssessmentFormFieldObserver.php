<?php

namespace App\Observers;

use App\Models\AssessmentFormField;
use App\Services\Assessment\AssessmentTargetSyncDispatcher;

class AssessmentFormFieldObserver
{
    public function saved(AssessmentFormField $field): void
    {
        app(AssessmentTargetSyncDispatcher::class)->fields([(int) $field->getKey()]);
    }

    public function deleting(AssessmentFormField $field): void
    {
        app(AssessmentTargetSyncDispatcher::class)->fields([(int) $field->getKey()]);
    }
}
