<?php

namespace App\Jobs;

use App\Services\Assessment\AssessmentCombinationGenerationService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessAssessmentCombinationGenerationJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 180;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $generationId,
        public int $sequence
    ) {
        $this->onQueue(AssessmentCombinationGenerationService::QUEUE_NAME);
    }

    /**
     * Execute the job.
     */
    public function handle(AssessmentCombinationGenerationService $service): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $service->processSequence($this->generationId, $this->sequence);
    }

    public function failed(Throwable $exception): void
    {
        app(AssessmentCombinationGenerationService::class)->markAsFailed($this->generationId);
    }
}
