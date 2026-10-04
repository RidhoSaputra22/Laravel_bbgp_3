<?php

namespace App\Jobs;

use App\Models\AssessmentAssignmentTarget;
use App\Services\Assessment\AssessmentAssignmentTargetDocumentBuilder;
use App\Services\Assessment\MongoAssessmentAssignmentTargetStore;
use App\Services\AssessmentAssignmentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncAssessmentTargetsToMongoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function __construct(public array $targetIds)
    {
        $this->onConnection(AssessmentAssignmentService::QUEUE_CONNECTION);
        $this->onQueue(AssessmentAssignmentService::SYNC_QUEUE_NAME);
    }

    public static function dispatchIds(array $targetIds): void
    {
        if (! (bool) config('assessment_mongodb.enabled')) {
            return;
        }

        $chunkSize = max((int) config('assessment_mongodb.batch_size', 50), 1);
        $ids = collect($targetIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        foreach (array_chunk($ids, $chunkSize) as $chunk) {
            static::dispatch($chunk)->afterCommit();
        }
    }

    public function handle(
        MongoAssessmentAssignmentTargetStore $store,
        AssessmentAssignmentTargetDocumentBuilder $builder
    ): void {
        if (! (bool) config('assessment_mongodb.enabled')) {
            return;
        }

        $store->assertAvailable();
        $ids = collect($this->targetIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if ($ids === []) {
            return;
        }

        $targets = AssessmentAssignmentTarget::query()
            ->select(AssessmentAssignmentTargetDocumentBuilder::targetColumns())
            ->whereIn('id', $ids)
            ->whereHas('assignment', fn ($query) => $query->withoutPreview())
            ->where('is_validator', false)
            ->with($builder->targetRelations())
            ->get()
            ->keyBy('id');

        $builder->hydrateAssignments($targets->values());

        $documents = $targets
            ->map(fn (AssessmentAssignmentTarget $target) => $builder->document($target, $target->assignment))
            ->values()
            ->all();
        $store->bulkUpsert($documents);

        $missingIds = array_values(array_diff($ids, $targets->keys()->map(fn ($id) => (int) $id)->all()));

        if ($missingIds !== []) {
            $store->markDeleted($missingIds, $builder);
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Assessment target MongoDB sync failed.', [
            'target_ids' => $this->targetIds,
            'error' => $exception->getMessage(),
        ]);
    }
}
