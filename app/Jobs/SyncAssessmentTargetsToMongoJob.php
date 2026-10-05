<?php

namespace App\Jobs;

use App\Models\AssessmentAssignmentTarget;
use App\Services\Assessment\AssessmentAssignmentTargetDocumentBuilder;
use App\Services\Assessment\MongoAssessmentAssignmentTargetStore;
use App\Services\AssessmentAssignmentService;
use App\Services\SyncOutboxPublisher;
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

    // Each document contains the complete assessment snapshot.
    private const MAX_JOB_BATCH_SIZE = 100;

    private const DOCUMENT_BATCH_SIZE = 10;

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

        $driver = (string) config('assessment_mongodb.sync_driver', 'php');
        if (in_array($driver, ['dual', 'go'], true)) {
            app(SyncOutboxPublisher::class)->enqueueTargets($targetIds);
        }

        if ($driver === 'go') {
            return;
        }

        $chunkSize = min(
            max((int) config('assessment_mongodb.batch_size', 50), 1),
            self::MAX_JOB_BATCH_SIZE
        );
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
        if (! (bool) config('assessment_mongodb.enabled')
            || (string) config('assessment_mongodb.sync_driver', 'php') === 'go') {
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

        if (count($ids) > self::MAX_JOB_BATCH_SIZE) {
            foreach (array_chunk($ids, self::MAX_JOB_BATCH_SIZE) as $chunk) {
                static::dispatch($chunk);
            }

            return;
        }

        $foundIds = [];

        foreach (array_chunk($ids, self::DOCUMENT_BATCH_SIZE) as $documentIds) {
            $targets = AssessmentAssignmentTarget::query()
                ->select(AssessmentAssignmentTargetDocumentBuilder::targetColumns())
                ->whereIn('id', $documentIds)
                ->whereHas('assignment', fn ($query) => $query->withoutPreview())
                ->where('is_validator', false)
                ->with($builder->targetRelations())
                ->get()
                ->keyBy('id');

            try {
                $builder->hydrateAssignments($targets->values());
                $documents = $targets
                    ->map(fn (AssessmentAssignmentTarget $target) => $builder->document($target, $target->assignment))
                    ->values()
                    ->all();
                $store->bulkUpsert($documents);

                foreach ($targets->keys() as $targetId) {
                    $foundIds[(int) $targetId] = true;
                }
            } finally {
                unset($documents, $targets);
                $builder->clearCaches();
            }
        }

        $missingIds = array_values(array_diff($ids, array_keys($foundIds)));

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
