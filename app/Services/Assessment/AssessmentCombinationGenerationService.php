<?php

namespace App\Services\Assessment;

use App\Jobs\ProcessAssessmentCombinationGenerationJob;
use App\Models\AssessmentCombination;
use App\Models\AssessmentCombinationGeneration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class AssessmentCombinationGenerationService
{
    public const QUEUE_CONNECTION = 'database';

    public const QUEUE_NAME = 'default';

    public function __construct(
        private readonly AssessmentCombinationService $combinationService
    ) {}

    public function createGeneration(array $payload, ?int $generatedBy = null): AssessmentCombinationGeneration
    {
        $generation = DB::transaction(function () use ($payload, $generatedBy) {
            return AssessmentCombinationGeneration::query()->create([
                'kode_generate' => $this->generateUniqueCode(),
                'target_ketenagaan' => (string) ($payload['target_ketenagaan'] ?? ''),
                'total_kombinasi' => max((int) ($payload['total_kombinasi'] ?? 1), 1),
                'selection_config' => $this->buildSelectionConfig($payload),
                'status' => 'diproses',
                'generated_by' => $generatedBy ?: null,
                'processed_at' => null,
            ]);
        });

        $this->dispatchBatch($generation, $this->resolveSequenceNumbers($generation));

        return $generation->fresh(['generator'])->loadCount('combinations');
    }

    public function retryGeneration(AssessmentCombinationGeneration $generation): array
    {
        $monitoring = $this->buildGenerationMonitoring($generation, false);
        $missingSequences = $this->resolveMissingSequenceNumbers($generation->fresh());
        $resumeCount = count($missingSequences);

        if ($resumeCount === 0) {
            $generation->forceFill([
                'status' => 'selesai',
                'processed_at' => now(),
            ])->save();

            return [
                'generation' => $generation->fresh(['generator'])->loadCount('combinations'),
                'resumed_count' => 0,
                'queued' => false,
                'already_complete' => true,
                'all_failed' => false,
            ];
        }

        if (! ($monitoring['retry_available'] ?? false)) {
            throw new InvalidArgumentException('Batch generate kombinasi masih berjalan. Tunggu sampai antrean selesai.');
        }

        $this->cancelGenerationBatch($generation->job_batch_id);
        $this->purgeGenerationQueueArtifacts($generation->id);

        $generation->forceFill([
            'status' => 'diproses',
            'job_batch_id' => null,
            'processed_at' => null,
        ])->save();

        $freshGeneration = $generation->fresh();
        $this->dispatchBatch($freshGeneration, $missingSequences);

        return [
            'generation' => $freshGeneration->fresh(['generator'])->loadCount('combinations'),
            'resumed_count' => $resumeCount,
            'queued' => true,
            'already_complete' => false,
            'all_failed' => ($monitoring['generated_total'] ?? 0) < 1,
        ];
    }

    public function buildGenerationMonitoring(
        AssessmentCombinationGeneration $generation,
        bool $includeFailedJobs = true
    ): array {
        $generation->loadCount('combinations');
        $generatedTotal = (int) ($generation->combinations_count ?? 0);
        $targetTotal = (int) $generation->total_kombinasi;
        $missingTotal = max($targetTotal - $generatedTotal, 0);
        $batch = $this->buildBatchMonitoring($generation);
        $batchPendingJobs = (int) ($batch['pending_jobs'] ?? 0);
        $batchFinished = $batch === null
            || ! ($batch['found'] ?? true)
            || (bool) ($batch['finished'] ?? false)
            || $batchPendingJobs === 0;
        $retryAvailable = $generation->status === 'gagal'
            && $missingTotal > 0
            && $batchFinished;

        return [
            'target_total' => $targetTotal,
            'generated_total' => $generatedTotal,
            'missing_total' => $missingTotal,
            'retry_available' => $retryAvailable,
            'all_failed' => $generatedTotal < 1 && $missingTotal > 0,
            'action_label' => $generatedTotal > 0 ? 'Resume Sisa Gagal' : 'Retry Semua Gagal',
            'queue_progress' => $batch['progress'] ?? ($targetTotal > 0
                ? (int) round(($generatedTotal / $targetTotal) * 100)
                : 0),
            'batch' => $batch,
            'failed_jobs' => $includeFailedJobs
                ? $this->buildFailedJobMonitoring($generation, $batch['failed_job_ids'] ?? [])
                : [],
        ];
    }

    public function processSequence(int $generationId, int $sequence): ?AssessmentCombination
    {
        return DB::transaction(function () use ($generationId, $sequence) {
            $generation = AssessmentCombinationGeneration::query()
                ->lockForUpdate()
                ->find($generationId);

            if (! $generation) {
                return null;
            }

            $existingCombination = $generation->combinations()
                ->where('generation_sequence', $sequence)
                ->first();

            if ($existingCombination) {
                return $existingCombination;
            }

            $payload = $this->buildPayloadFromGeneration($generation);
            $combination = $this->combinationService->createCombination(
                $payload,
                $generation->generated_by ? (int) $generation->generated_by : null
            );

            $combination->forceFill([
                'assessment_combination_generation_id' => $generation->id,
                'generation_sequence' => $sequence,
            ])->save();

            $this->refreshGenerationSummary($generation->id);

            return $combination->fresh(['items', 'generator']);
        });
    }

    public function markAsFailed(int $generationId): void
    {
        AssessmentCombinationGeneration::query()
            ->whereKey($generationId)
            ->update([
                'status' => 'gagal',
                'processed_at' => null,
            ]);
    }

    public function refreshGenerationSummary(int $generationId): void
    {
        $generation = AssessmentCombinationGeneration::query()
            ->withCount('combinations')
            ->find($generationId);

        if (! $generation) {
            return;
        }

        $generatedTotal = (int) ($generation->combinations_count ?? 0);
        $targetTotal = (int) $generation->total_kombinasi;
        $currentStatus = (string) $generation->status;
        $isComplete = $targetTotal > 0 && $generatedTotal >= $targetTotal;

        $resolvedStatus = match (true) {
            $currentStatus === 'gagal' => 'gagal',
            $isComplete => 'selesai',
            default => 'diproses',
        };

        $generation->forceFill([
            'status' => $resolvedStatus,
            'processed_at' => $resolvedStatus === 'selesai'
                ? now()
                : ($resolvedStatus === 'gagal' ? $generation->processed_at : null),
        ])->save();
    }

    private function buildSelectionConfig(array $payload): array
    {
        return [
            'target_ketenagaan' => $payload['target_ketenagaan'] ?? null,
            'total_kombinasi' => max((int) ($payload['total_kombinasi'] ?? 1), 1),
            'competency_selection_modes' => $payload['competency_selection_modes'] ?? [],
            'competency_take_counts' => $payload['competency_take_counts'] ?? [],
        ];
    }

    private function buildPayloadFromGeneration(AssessmentCombinationGeneration $generation): array
    {
        $selectionConfig = (array) ($generation->selection_config ?? []);

        return [
            'target_ketenagaan' => $selectionConfig['target_ketenagaan'] ?? $generation->target_ketenagaan,
            'competency_selection_modes' => $selectionConfig['competency_selection_modes'] ?? [],
            'competency_take_counts' => $selectionConfig['competency_take_counts'] ?? [],
        ];
    }

    private function resolveSequenceNumbers(AssessmentCombinationGeneration $generation): array
    {
        return range(1, max((int) $generation->total_kombinasi, 1));
    }

    private function resolveMissingSequenceNumbers(AssessmentCombinationGeneration $generation): array
    {
        $existingSequences = $generation->combinations()
            ->whereNotNull('generation_sequence')
            ->pluck('generation_sequence')
            ->map(fn ($sequence) => (int) $sequence)
            ->all();
        $lookup = array_fill_keys($existingSequences, true);

        return collect(range(1, max((int) $generation->total_kombinasi, 1)))
            ->reject(fn (int $sequence) => isset($lookup[$sequence]))
            ->values()
            ->all();
    }

    private function dispatchBatch(AssessmentCombinationGeneration $generation, array $sequences): void
    {
        $jobs = collect($sequences)
            ->map(fn (int $sequence) => new ProcessAssessmentCombinationGenerationJob($generation->id, $sequence))
            ->all();

        try {
            $batch = Bus::batch($jobs)
                ->name('Generate Kombinasi '.$generation->kode_generate)
                ->onConnection(self::QUEUE_CONNECTION)
                ->onQueue(self::QUEUE_NAME)
                ->allowFailures()
                ->dispatch();

            $generation->forceFill([
                'job_batch_id' => $batch->id,
                'status' => 'diproses',
                'processed_at' => null,
            ])->save();

            $this->refreshGenerationSummary($generation->id);
        } catch (Throwable $exception) {
            $generation->forceFill([
                'status' => 'gagal',
                'processed_at' => null,
            ])->save();

            throw $exception;
        }
    }

    private function buildBatchMonitoring(AssessmentCombinationGeneration $generation): ?array
    {
        if (! $generation->job_batch_id || ! Schema::hasTable('job_batches')) {
            return null;
        }

        $batch = Bus::findBatch($generation->job_batch_id);

        if (! $batch) {
            return [
                'id' => $generation->job_batch_id,
                'found' => false,
                'failed_job_ids' => [],
            ];
        }

        return [
            'id' => $batch->id,
            'found' => true,
            'name' => $batch->name,
            'total_jobs' => $batch->totalJobs,
            'pending_jobs' => $batch->pendingJobs,
            'processed_jobs' => $batch->processedJobs(),
            'failed_jobs' => $batch->failedJobs,
            'progress' => $batch->progress(),
            'finished' => $batch->finished(),
            'cancelled' => $batch->cancelled(),
            'created_at' => $batch->createdAt,
            'finished_at' => $batch->finishedAt,
            'cancelled_at' => $batch->cancelledAt,
            'failed_job_ids' => $batch->failedJobIds ?? [],
        ];
    }

    private function buildFailedJobMonitoring(
        AssessmentCombinationGeneration $generation,
        array $batchFailedJobIds = []
    ): array {
        if (! Schema::hasTable('failed_jobs')) {
            return [];
        }

        $query = DB::table('failed_jobs')
            ->select(['uuid', 'queue', 'payload', 'exception', 'failed_at'])
            ->orderByDesc('failed_at');

        if ($batchFailedJobIds !== []) {
            $query->whereIn('uuid', $batchFailedJobIds);
        }

        return $query
            ->get()
            ->map(function ($row) use ($generation) {
                $payload = $this->extractGenerationQueuePayload((string) $row->payload);

                if (! $payload || $payload['generation_id'] !== (int) $generation->id) {
                    return null;
                }

                return [
                    'uuid' => (string) $row->uuid,
                    'queue' => (string) $row->queue,
                    'failed_at' => $row->failed_at ? Carbon::parse($row->failed_at) : null,
                    'sequence' => (int) $payload['sequence'],
                    'message' => $this->extractExceptionMessage((string) $row->exception),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function extractGenerationQueuePayload(string $payload): ?array
    {
        $decoded = json_decode($payload, true);

        if (! is_array($decoded)) {
            return null;
        }

        $displayName = (string) ($decoded['displayName'] ?? data_get($decoded, 'data.commandName', ''));

        if ($displayName !== ProcessAssessmentCombinationGenerationJob::class) {
            return null;
        }

        $serializedCommand = data_get($decoded, 'data.command');

        if (! is_string($serializedCommand) || $serializedCommand === '') {
            return null;
        }

        $job = @unserialize($serializedCommand, [
            'allowed_classes' => [ProcessAssessmentCombinationGenerationJob::class],
        ]);

        if (! $job instanceof ProcessAssessmentCombinationGenerationJob) {
            return null;
        }

        return [
            'generation_id' => (int) $job->generationId,
            'sequence' => (int) $job->sequence,
        ];
    }

    private function extractExceptionMessage(string $exception): string
    {
        $firstLine = trim(Str::of($exception)->before("\n")->toString());

        if ($firstLine !== '') {
            return $firstLine;
        }

        return Str::limit(trim($exception), 160);
    }

    private function cancelGenerationBatch(?string $batchId): void
    {
        if (! $batchId || ! Schema::hasTable('job_batches')) {
            return;
        }

        $batch = Bus::findBatch($batchId);

        if ($batch && ! $batch->cancelled()) {
            $batch->cancel();
        }
    }

    private function purgeGenerationQueueArtifacts(int $generationId): void
    {
        $this->purgeGenerationQueueTable('jobs', $generationId);
        $this->purgeGenerationQueueTable('failed_jobs', $generationId);
    }

    private function purgeGenerationQueueTable(string $table, int $generationId): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'payload')) {
            return;
        }

        $columns = ['payload'];

        if (Schema::hasColumn($table, 'id')) {
            $columns[] = 'id';
        }

        if (Schema::hasColumn($table, 'uuid')) {
            $columns[] = 'uuid';
        }

        $rows = DB::table($table)->get($columns);
        $idsToDelete = [];
        $uuidsToDelete = [];

        foreach ($rows as $row) {
            $payload = $this->extractGenerationQueuePayload((string) ($row->payload ?? ''));

            if (! $payload || $payload['generation_id'] !== $generationId) {
                continue;
            }

            if (isset($row->id)) {
                $idsToDelete[] = (int) $row->id;
            }

            if (isset($row->uuid)) {
                $uuidsToDelete[] = (string) $row->uuid;
            }
        }

        if ($idsToDelete !== [] && Schema::hasColumn($table, 'id')) {
            DB::table($table)
                ->whereIn('id', array_values(array_unique($idsToDelete)))
                ->delete();
        }

        if ($uuidsToDelete !== [] && Schema::hasColumn($table, 'uuid')) {
            DB::table($table)
                ->whereIn('uuid', array_values(array_unique($uuidsToDelete)))
                ->delete();
        }
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = 'KBG-ASM-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(4));
        } while (AssessmentCombinationGeneration::query()->where('kode_generate', $code)->exists());

        return $code;
    }
}
