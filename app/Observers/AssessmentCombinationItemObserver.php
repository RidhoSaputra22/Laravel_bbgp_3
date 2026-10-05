<?php

namespace App\Observers;

use App\Models\AssessmentCombinationItem;
use App\Services\Assessment\AssessmentTargetSyncDispatcher;

class AssessmentCombinationItemObserver
{
    public function saved(AssessmentCombinationItem $item): void
    {
        app(AssessmentTargetSyncDispatcher::class)->combinations([(int) $item->assessment_combination_id]);
    }

    public function deleting(AssessmentCombinationItem $item): void
    {
        app(AssessmentTargetSyncDispatcher::class)->combinations([(int) $item->assessment_combination_id]);
    }
}
