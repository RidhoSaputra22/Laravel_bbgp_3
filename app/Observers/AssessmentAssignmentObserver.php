<?php

namespace App\Observers;

use App\Jobs\SyncAssessmentTargetsToMongoJob;
use App\Models\AssessmentAssignment;
use App\Services\Assessment\AssessmentTargetSyncDispatcher;
use Illuminate\Support\Facades\Schema;

class AssessmentAssignmentObserver
{
    public function saved(AssessmentAssignment $assignment): void
    {
        if (! (bool) config('assessment_mongodb.enabled')
            || ! Schema::hasTable('assessment_assignment_targets')) {
            return;
        }

        $relevantChanges = [
            'kode_penugasan',
            'judul_penugasan',
            'deskripsi',
            'is_active',
            'status_distribusi',
            'target_ketenagaan',
            'assessment_combination_id',
            'tanggal_mulai',
            'tanggal_selesai',
        ];

        if (array_intersect(array_keys($assignment->getChanges()), $relevantChanges) === []) {
            return;
        }

        SyncAssessmentTargetsToMongoJob::dispatchIds(
            $assignment->targets()
                ->where('is_validator', false)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all()
        );
    }

    public function deleting(AssessmentAssignment $assignment): void
    {
        app(AssessmentTargetSyncDispatcher::class)->assignments([(int) $assignment->getKey()]);
    }
}
