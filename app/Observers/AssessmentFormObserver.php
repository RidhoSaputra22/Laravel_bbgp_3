<?php

namespace App\Observers;

use App\Models\AssessmentForm;
use App\Services\Assessment\AssessmentTargetSyncDispatcher;

class AssessmentFormObserver
{
    public function saved(AssessmentForm $form): void
    {
        app(AssessmentTargetSyncDispatcher::class)->forms([(int) $form->getKey()]);
    }

    public function deleting(AssessmentForm $form): void
    {
        app(AssessmentTargetSyncDispatcher::class)->forms([(int) $form->getKey()]);
    }
}
