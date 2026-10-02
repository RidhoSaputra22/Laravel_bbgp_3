<?php

namespace App\Services\Assessment;

use RuntimeException;

class MongoValidatorAssignmentStore
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

    public function markDeleted(array $assignmentIds, ValidatorAssignmentDocumentBuilder $builder): void
    {
        $documents = collect($assignmentIds)
            ->map(fn ($assignmentId) => (int) $assignmentId)
            ->filter(fn (int $assignmentId) => $assignmentId > 0)
            ->unique()
            ->map(fn (int $assignmentId) => $builder->tombstone($assignmentId))
            ->values()
            ->all();

        $this->bulkUpsert($documents);
    }

    public function deleteAll(): int
    {
        $this->assertAvailable();

        return (int) $this->collection()->deleteMany([])->getDeletedCount();
    }

    public function ensureIndexes(): void
    {
        $this->assertAvailable();

        $this->collection()->createIndexes([
            [
                'key' => ['validator_assignment_id' => 1],
                'name' => 'validator_assignment_id_unique',
                'unique' => true,
            ],
            ['key' => ['assignment.status' => 1], 'name' => 'validator_assignment_status'],
            ['key' => ['validator.user_id' => 1], 'name' => 'validator_user_id'],
            ['key' => ['validator.user_id' => 1, 'assignment.status' => 1], 'name' => 'validator_status'],
            ['key' => ['assessment_assignments.id' => 1], 'name' => 'source_assessment_assignment_id'],
            ['key' => ['sync.is_active' => 1], 'name' => 'validator_sync_active'],
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
            ->selectCollection((string) config('assessment_mongodb.validator_collection'));
    }
}
