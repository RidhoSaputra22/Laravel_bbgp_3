<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyncOutboxPublisher
{
    public const TARGET = 'assessment_target';
    public const VALIDATOR_ASSIGNMENT = 'validator_assignment';

    public function enabled(): bool
    {
        return (bool) config('assessment_mongodb.enabled')
            && in_array($this->driver(), ['dual', 'go'], true)
            && $this->schema()->hasTable('sync_outbox');
    }

    public function driver(): string
    {
        return (string) config('assessment_mongodb.sync_driver', 'php');
    }

    public function enqueueTargets(array $ids): void
    {
        $this->enqueue(self::TARGET, $ids);
    }

    public function enqueueValidatorAssignments(array $ids): void
    {
        $this->enqueue(self::VALIDATOR_ASSIGNMENT, $ids);
    }

    private function enqueue(string $entityType, array $ids): void
    {
        if (! $this->enabled()) {
            return;
        }

        $ids = collect($ids)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return;
        }

        $now = now();

        foreach ($ids->chunk(500) as $chunk) {
            $rows = $chunk->map(fn (int $id) => [
                'entity_type' => $entityType,
                'entity_id' => $id,
                'version' => 1,
                'status' => 'pending',
                'attempts' => 0,
                'available_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();

            $database = $this->database();
            $database
                ->table('sync_outbox')
                ->where('entity_type', $entityType)
                ->whereIn('entity_id', $chunk->all())
                ->update([
                    'version' => $database->raw('version + 1'),
                    'status' => $database->raw("CASE WHEN status IN ('done', 'failed') THEN 'pending' ELSE status END"),
                    'attempts' => $database->raw("CASE WHEN status IN ('done', 'failed') THEN 0 ELSE attempts END"),
                    'available_at' => $now,
                    'processed_at' => $database->raw("CASE WHEN status IN ('done', 'failed') THEN NULL ELSE processed_at END"),
                    'last_error' => $database->raw("CASE WHEN status IN ('done', 'failed') THEN NULL ELSE last_error END"),
                    'updated_at' => $now,
                ]);

            $database->table('sync_outbox')->insertOrIgnore($rows);
        }
    }

    private function database()
    {
        $connection = config('assessment_mongodb.outbox_connection');

        return $connection ? DB::connection($connection) : DB::connection();
    }

    private function schema()
    {
        $connection = config('assessment_mongodb.outbox_connection');

        return Schema::connection($connection ?: config('database.default'));
    }
}
