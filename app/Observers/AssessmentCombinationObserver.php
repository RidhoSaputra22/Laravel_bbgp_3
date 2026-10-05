<?php

namespace App\Observers;

use App\Models\AssessmentCombination;
use App\Services\Assessment\AssessmentTargetSyncDispatcher;

class AssessmentCombinationObserver
{
    public function saved(AssessmentCombination $combination): void
    {
        app(AssessmentTargetSyncDispatcher::class)->combinations([(int) $combination->getKey()]);
    }

    public function deleting(AssessmentCombination $combination): void
    {
        app(AssessmentTargetSyncDispatcher::class)->combinations([(int) $combination->getKey()]);
    }
}
