<?php

namespace App\Jobs;

use App\Services\AssessmentAssignmentService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessAssessmentAssignmentTargetsJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $assignmentId,
        public array $targetRows
    ) {
        $this->onQueue(AssessmentAssignmentService::QUEUE_NAME);
    }

    /**
     * Execute the job.
     */
    public function handle(AssessmentAssignmentService $service): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $service->processTargetChunk($this->assignmentId, $this->targetRows);
    }

    public function failed(Throwable $exception): void
    {
        app(AssessmentAssignmentService::class)->markAsFailed($this->assignmentId);
    }
}
