<?php

namespace App\Jobs;

use App\Models\AssessmentAssignment;
use App\Models\AssessmentAssignmentTarget;
use App\Models\Guru;
use App\Services\AssessmentAssignmentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SyncExternalGuruToAssessmentAssignmentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(public int $guruId)
    {
        $this->onQueue(AssessmentAssignmentService::QUEUE_NAME);
    }

    public function handle(AssessmentAssignmentService $service): void
    {
        if (! Schema::hasTable('assessment_assignments')
            || ! Schema::hasTable('assessment_assignment_targets')) {
            return;
        }

        $guru = Guru::query()->find($this->guruId);

        if (! $guru) {
            return;
        }

        AssessmentAssignment::query()
            ->withoutPreview()
            ->where(function ($query) {
                $query->whereNull('status_distribusi')
                    ->orWhereNotIn('status_distribusi', ['diproses', 'gagal']);
            })
            ->orderBy('id')
            ->get()
            ->each(function (AssessmentAssignment $assignment) use ($guru, $service): void {
                $alreadyAssigned = AssessmentAssignmentTarget::query()
                    ->where('assessment_assignment_id', $assignment->id)
                    ->where('guru_id', $guru->id)
                    ->exists();

                if ($alreadyAssigned) {
                    return;
                }

                $matchesTarget = $service
                    ->buildAssignableParticipantQueryForAssignment($assignment)
                    ->whereKey($guru->id)
                    ->exists();

                if ($matchesTarget) {
                    $service->addParticipants($assignment, [$guru->id]);
                }
            });
    }

    public function failed(Throwable $exception): void
    {
        Log::error('External guru assignment sync failed.', [
            'guru_id' => $this->guruId,
            'error' => $exception->getMessage(),
        ]);
    }
}
