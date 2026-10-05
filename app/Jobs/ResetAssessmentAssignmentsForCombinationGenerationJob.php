<?php

namespace App\Jobs;

use App\Models\AssessmentCombinationGeneration;
use App\Services\Assessment\AssessmentCombinationGenerationService;
use App\Services\AssessmentAssignmentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class ResetAssessmentAssignmentsForCombinationGenerationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 1800;

    public function __construct(
        public int $sourceGenerationId,
        public int $replacementGenerationId
    ) {
        $this->onQueue(AssessmentAssignmentService::QUEUE_NAME);
    }

    public function handle(
        AssessmentAssignmentService $assignmentService,
        AssessmentCombinationGenerationService $generationService
    ): void {
        $sourceGeneration = AssessmentCombinationGeneration::query()
            ->with([
                'combinations' => fn ($query) => $query->select([
                    'id',
                    'assessment_combination_generation_id',
                ]),
            ])
            ->find($this->sourceGenerationId);

        if (! $sourceGeneration) {
            return;
        }

        $replacementGeneration = AssessmentCombinationGeneration::query()
            ->with([
                'combinations' => fn ($query) => $query->select([
                    'id',
                    'assessment_combination_generation_id',
                ]),
            ])
            ->findOrFail($this->replacementGenerationId);

        if (
            $replacementGeneration->combinations->count() < max((int) $replacementGeneration->total_kombinasi, 1)
        ) {
            throw new RuntimeException('Generate kombinasi baru belum lengkap.');
        }

        $assignmentService->resetAssignmentsForCombinationGeneration(
            $sourceGeneration,
            $replacementGeneration
        );

        if (Schema::hasColumn('assessment_combination_generations', 'reset_source_generation_id')) {
            $replacementGeneration->forceFill([
                'reset_source_generation_id' => null,
            ])->save();
        }
        $generationService->deleteGenerationHistory($sourceGeneration);
    }

    public function failed(Throwable $exception): void
    {
        report($exception);
    }
}
