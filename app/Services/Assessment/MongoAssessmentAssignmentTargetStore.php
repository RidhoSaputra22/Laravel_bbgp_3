<?php

namespace App\Services\Assessment;

use RuntimeException;

class MongoAssessmentAssignmentTargetStore
{
    private mixed $collection = null;

    public function available(): bool
    {
        return (bool) config('assessment_mongodb.enabled')
            && extension_loaded('mongodb')
            && class_exists('MongoDB\\Client')
            && filled(config('assessment_mongodb.uri'));
    }

    public function assertAvailable(): void
    {
        if (! $this->available()) {
            throw new RuntimeException(
                'MongoDB sync is not available. Enable MONGODB_SYNC_ENABLED and install ext-mongodb.'
            );
        }
    }

    public function bulkUpsert(array $documents): void
    {
        if ($documents === []) {
            return;
        }

        $this->assertAvailable();
        $operations = [];

        foreach ($documents as $document) {
            $id = (string) ($document['_id'] ?? '');

            if ($id === '') {
                continue;
            }

            $operations[] = [
                'replaceOne' => [
                    ['_id' => $id],
                    $document,
                    ['upsert' => true],
                ],
            ];
        }

        if ($operations !== []) {
            $this->collection()->bulkWrite($operations, ['ordered' => false]);
        }
    }

    public function upsert(array $document): void
    {
        $this->bulkUpsert([$document]);
    }

    /**
     * Count synchronized target documents in one aggregation for the panel.
     *
     * @param  array<int, int|string>  $assignmentIds
     * @return array<int, int>
     */
    public function countByAssignmentIds(array $assignmentIds): array
    {
        $ids = collect($assignmentIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if ($ids === []) {
            return [];
        }

        $this->assertAvailable();
        $counts = array_fill_keys($ids, 0);
        $rows = $this->collection()->aggregate([
            [
                '$match' => [
                    'assignment.id' => ['$in' => $ids],
                ],
            ],
            [
                '$group' => [
                    '_id' => '$assignment.id',
                    'total' => ['$sum' => 1],
                ],
            ],
        ]);

        foreach ($rows as $row) {
            $assignmentId = (int) ($row['_id'] ?? 0);
            if ($assignmentId > 0) {
                $counts[$assignmentId] = (int) ($row['total'] ?? 0);
            }
        }

        return $counts;
    }

    /**
     * Remove all target projections while keeping the collection and indexes.
     *
     * This is intentionally exposed for an explicit full backfill/reset only.
     * Queue jobs must never call it because they process individual targets.
     */
    public function deleteAll(): int
    {
        $this->assertAvailable();

        return (int) $this->collection()->deleteMany([])->getDeletedCount();
    }

    public function markDeleted(array $targetIds, AssessmentAssignmentTargetDocumentBuilder $builder): void
    {
        $documents = collect($targetIds)
            ->map(fn ($targetId) => (int) $targetId)
            ->filter(fn (int $targetId) => $targetId > 0)
            ->unique()
            ->map(fn (int $targetId) => $builder->tombstone($targetId))
            ->values()
            ->all();

        $this->bulkUpsert($documents);
    }

    public function ensureIndexes(): void
    {
        $this->assertAvailable();

        $this->collection()->createIndexes([
            ['key' => ['assignment_target_id' => 1], 'name' => 'assignment_target_id_unique', 'unique' => true],
            ['key' => ['assignment.id' => 1], 'name' => 'assignment_id'],
            ['key' => ['user.id' => 1], 'name' => 'guru_id'],
            ['key' => ['target.status' => 1], 'name' => 'target_status'],
            ['key' => ['assignment.id' => 1, 'target.status' => 1], 'name' => 'assignment_status'],
            ['key' => ['user.id' => 1, 'target.status' => 1], 'name' => 'guru_status'],
        ]);
    }

    private function collection(): mixed
    {
        if ($this->collection) {
            return $this->collection;
        }

        $this->assertAvailable();
        $client = new \MongoDB\Client(config('assessment_mongodb.uri'), [
            'serverSelectionTimeoutMS' => 5000,
        ]);

        return $this->collection = $client
            ->selectDatabase((string) config('assessment_mongodb.database'))
            ->selectCollection((string) config('assessment_mongodb.collection'));
    }
}
