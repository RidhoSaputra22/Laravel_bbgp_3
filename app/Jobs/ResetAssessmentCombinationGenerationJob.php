<?php

namespace App\Jobs;

use App\Models\AssessmentCombinationGeneration;
use App\Services\Assessment\AssessmentCombinationGenerationService;
use App\Services\AssessmentAssignmentService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ResetAssessmentCombinationGenerationJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 1800;

    public function __construct(public int $generationId)
    {
        $this->onQueue(AssessmentCombinationGenerationService::QUEUE_NAME);
    }

    public function handle(
        AssessmentCombinationGenerationService $generationService,
        AssessmentAssignmentService $assignmentService
    ): void {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $generation = AssessmentCombinationGeneration::query()->findOrFail($this->generationId);
        $generationService->processInPlaceReset($generation, $assignmentService);
    }

    public function failed(Throwable $exception): void
    {
        report($exception);

        AssessmentCombinationGeneration::query()
            ->whereKey($this->generationId)
            ->update([
                'status' => 'gagal',
                'processed_at' => null,
                ...(
                    Schema::hasColumn('assessment_combination_generations', 'reset_source_generation_id')
                        ? ['reset_source_generation_id' => $this->generationId]
                        : []
                ),
            ]);
    }
}
