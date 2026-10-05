<?php

namespace App\Jobs;

use App\Models\ValidatorAssignment;
use App\Services\Assessment\MongoValidatorAssignmentStore;
use App\Services\Assessment\ValidatorAssignmentDocumentBuilder;
use App\Services\AssessmentAssignmentService;
use App\Services\SyncOutboxPublisher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncValidatorAssignmentsToMongoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function __construct(public array $assignmentIds)
    {
        $this->onConnection(AssessmentAssignmentService::QUEUE_CONNECTION);
        $this->onQueue(AssessmentAssignmentService::SYNC_QUEUE_NAME);
    }

    public static function dispatchIds(array $assignmentIds): void
    {
        if (! (bool) config('assessment_mongodb.enabled')) {
            return;
        }

        $driver = (string) config('assessment_mongodb.sync_driver', 'php');
        if (in_array($driver, ['dual', 'go'], true)) {
            app(SyncOutboxPublisher::class)->enqueueValidatorAssignments($assignmentIds);
        }

        if ($driver === 'go') {
            return;
        }

        $chunkSize = max((int) config('assessment_mongodb.batch_size', 50), 1);
        $ids = collect($assignmentIds)
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
        MongoValidatorAssignmentStore $store,
        ValidatorAssignmentDocumentBuilder $builder
    ): void {
        if (! (bool) config('assessment_mongodb.enabled')
            || (string) config('assessment_mongodb.sync_driver', 'php') === 'go') {
            return;
        }

        $store->assertAvailable();
        $ids = collect($this->assignmentIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if ($ids === []) {
            return;
        }

        $assignments = ValidatorAssignment::query()
            ->whereIn('id', $ids)
            ->with($builder->relations())
            ->get()
            ->keyBy('id');
        $documents = $assignments
            ->map(fn (ValidatorAssignment $assignment) => $builder->document($assignment))
            ->values()
            ->all();

        $store->bulkUpsert($documents);

        $missingIds = array_values(array_diff(
            $ids,
            $assignments->keys()->map(fn ($id) => (int) $id)->all()
        ));

        if ($missingIds !== []) {
            $store->markDeleted($missingIds, $builder);
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Validator assignment MongoDB sync failed.', [
            'validator_assignment_ids' => $this->assignmentIds,
            'error' => $exception->getMessage(),
        ]);
    }
}
