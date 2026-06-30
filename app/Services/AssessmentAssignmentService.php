<?php

namespace App\Services;

use App\Enum\AssessmentKetenagaanType;
use App\Jobs\ProcessAssessmentAssignmentTargetsJob;
use App\Models\Assessment;
use App\Models\AssessmentAssignment;
use App\Models\AssessmentAssignmentTarget;
use App\Models\Guru;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class AssessmentAssignmentService
{
    public const BATCH_THRESHOLD = 25;

    public const CHUNK_SIZE = 50;

    public const TARGETS_PER_SESSION = 41;

    public const DEFAULT_SESSION_DURATION_HOURS = 3;

    public const SESSION_DURATION_OPTIONS = [1, 2, 3, 4, 5, 6, 7, 8];

    public function createAssignment(array $payload, ?int $assignedBy = null): AssessmentAssignment
    {
        $context = $this->prepareAssignmentContext($payload);
        $shouldBatch = count($context['guru_ids']) > self::BATCH_THRESHOLD;

        $assignmentData = DB::transaction(function () use ($payload, $context, $assignedBy, $shouldBatch) {
            $assessmentSyncData = $this->buildAssessmentSyncData($context['assessment_ids']);
            $totalSessions = $this->calculateTotalSessions(count($context['guru_ids']));

            $assignment = AssessmentAssignment::create([
                'kode_penugasan' => $this->generateUniqueCode(),
                'judul_penugasan' => $payload['judul_penugasan'],
                'target_ketenagaan' => $context['target_ketenagaan']?->value,
                'target_jabatan' => $this->normalizeTargetJabatanSelections($payload['target_jabatan'] ?? []),
                'target_kabupaten' => $this->normalizeTargetKabupatenSelections($payload['target_kabupaten'] ?? []),
                'deskripsi' => $payload['deskripsi'] ?? null,
                'tanggal_mulai' => $payload['tanggal_mulai'] ?? null,
                'jam_mulai' => $context['start_time'],
                'tanggal_selesai' => $payload['tanggal_selesai'] ?? null,
                'kapasitas_per_sesi' => self::TARGETS_PER_SESSION,
                'durasi_sesi_jam' => $context['session_duration_hours'],
                'total_sesi' => $totalSessions,
                'status_distribusi' => $shouldBatch ? 'diproses' : 'draft',
                'total_target' => count($context['guru_ids']),
                'total_ditugaskan' => 0,
                'assigned_by' => $assignedBy ?: null,
            ]);

            $assignment->assessments()->sync($assessmentSyncData);

            $sessionRows = $this->createSessions(
                $assignment,
                count($context['guru_ids']),
                $context['session_duration_hours'],
                $context['first_session_start_at']
            );

            $targetRows = $this->buildTargetRows($assignment->id, $context['guru_ids'], $sessionRows);

            if (! $shouldBatch) {
                $this->storeTargetRows($targetRows);
                $this->refreshAssignmentSummary($assignment->id);
            }

            return [
                'assignment' => $assignment,
                'target_rows' => $targetRows,
            ];
        });

        /** @var \App\Models\AssessmentAssignment $assignment */
        $assignment = $assignmentData['assignment'];
        $targetRows = $assignmentData['target_rows'];

        if ($shouldBatch) {
            $this->dispatchBatch($assignment, $targetRows);
            $assignment->refresh();
        }

        return $assignment->load(['assessments', 'creator', 'sessions'])->loadCount('targets');
    }

    public function updateAssignment(
        AssessmentAssignment $assignment,
        array $payload,
        ?int $assignedBy = null
    ): array {
        $context = $this->prepareAssignmentContext($payload);
        $existingTargets = $assignment->targets()
            ->with('attempt')
            ->orderBy('id')
            ->get();
        $lockedTargets = $existingTargets
            ->filter(fn (AssessmentAssignmentTarget $target) => $this->isLockedTarget($target))
            ->values();
        $lockedTargetCount = $lockedTargets->count();
        $lockedGuruIdsOutsideFilter = $lockedTargets
            ->pluck('guru_id')
            ->map(fn ($guruId) => (int) $guruId)
            ->filter(fn (int $guruId) => ! in_array($guruId, $context['guru_ids'], true))
            ->values()
            ->all();

        if (
            $lockedTargetCount > 0 &&
            $assignment->target_ketenagaan !== ($context['target_ketenagaan']?->value)
        ) {
            throw ValidationException::withMessages([
                'target_ketenagaan' => 'Ketenagaan target tidak dapat diubah karena ada peserta yang sudah mulai atau menyelesaikan assessment.',
            ]);
        }

        $assessmentIds = $this->resolveAssessmentIdsForUpdate(
            $assignment,
            $context['assessment_ids'],
            $lockedTargetCount > 0
        );

        $finalGuruIds = array_values(array_unique([
            ...$context['guru_ids'],
            ...$lockedGuruIdsOutsideFilter,
        ]));
        $totalSessions = $this->calculateTotalSessions(count($finalGuruIds));
        $cancelledTargetIds = $existingTargets
            ->filter(function (AssessmentAssignmentTarget $target) use ($finalGuruIds) {
                return ! in_array((int) $target->guru_id, $finalGuruIds, true)
                    && ! $this->isLockedTarget($target);
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
        $existingActiveGuruIds = $existingTargets
            ->filter(fn (AssessmentAssignmentTarget $target) => $target->status !== 'dibatalkan')
            ->pluck('guru_id')
            ->map(fn ($guruId) => (int) $guruId)
            ->values()
            ->all();
        $newTargetCount = count(array_diff($finalGuruIds, $existingActiveGuruIds));

        DB::transaction(function () use (
            $assignment,
            $payload,
            $context,
            $assignedBy,
            $assessmentIds,
            $finalGuruIds,
            $totalSessions,
            $existingTargets,
            $cancelledTargetIds
        ) {
            $assignment->forceFill([
                'judul_penugasan' => $payload['judul_penugasan'],
                'target_ketenagaan' => $context['target_ketenagaan']?->value,
                'target_jabatan' => $this->normalizeTargetJabatanSelections($payload['target_jabatan'] ?? []),
                'target_kabupaten' => $this->normalizeTargetKabupatenSelections($payload['target_kabupaten'] ?? []),
                'deskripsi' => $payload['deskripsi'] ?? null,
                'tanggal_mulai' => $payload['tanggal_mulai'] ?? null,
                'jam_mulai' => $context['start_time'],
                'tanggal_selesai' => $payload['tanggal_selesai'] ?? null,
                'kapasitas_per_sesi' => self::TARGETS_PER_SESSION,
                'durasi_sesi_jam' => $context['session_duration_hours'],
                'total_sesi' => $totalSessions,
                'status_distribusi' => 'draft',
                'total_target' => count($finalGuruIds),
                'assigned_by' => $assignedBy ?: $assignment->assigned_by,
                'job_batch_id' => null,
                'processed_at' => null,
            ])->save();

            $assignment->assessments()->sync($this->buildAssessmentSyncData($assessmentIds));

            $assignment->sessions()->delete();

            $sessions = $this->createSessions(
                $assignment,
                count($finalGuruIds),
                $context['session_duration_hours'],
                $context['first_session_start_at']
            );

            if ($cancelledTargetIds !== []) {
                AssessmentAssignmentTarget::query()
                    ->whereIn('id', $cancelledTargetIds)
                    ->update([
                        'assessment_assignment_session_id' => null,
                        'status' => 'dibatalkan',
                        'updated_at' => now(),
                    ]);
            }

            $targetRows = $this->buildTargetRowsForUpdate(
                $assignment->id,
                $finalGuruIds,
                $sessions,
                $existingTargets
            );

            $this->storeTargetRows($targetRows);
            $this->refreshAssignmentSummary($assignment->id);
        });

        return [
            'assignment' => $assignment->fresh(['assessments', 'creator', 'sessions'])->loadCount('targets'),
            'locked_target_count' => $lockedTargetCount,
            'preserved_locked_count' => count($lockedGuruIdsOutsideFilter),
            'cancelled_target_count' => count($cancelledTargetIds),
            'new_target_count' => $newTargetCount,
        ];
    }

    public function retryAssignment(AssessmentAssignment $assignment): array
    {
        $resumeRows = $this->resolveRetryTargetRows($assignment->fresh(['sessions']));
        $resumeCount = count($resumeRows);

        if ($resumeCount === 0) {
            AssessmentAssignment::whereKey($assignment->id)->update([
                'status_distribusi' => $assignment->total_target > 0 ? 'selesai' : 'draft',
                'job_batch_id' => null,
                'processed_at' => $assignment->total_target > 0 ? now() : null,
            ]);

            $this->refreshAssignmentSummary($assignment->id);

            return [
                'assignment' => $assignment->fresh(['assessments', 'creator', 'sessions'])->loadCount('targets'),
                'resumed_count' => 0,
                'queued' => false,
                'already_complete' => true,
            ];
        }

        $this->resetDistributionState($assignment->id);

        if ($resumeCount > self::BATCH_THRESHOLD) {
            $freshAssignment = $assignment->fresh();
            $this->dispatchBatch($freshAssignment, $resumeRows);

            return [
                'assignment' => $freshAssignment->fresh(['assessments', 'creator', 'sessions'])->loadCount('targets'),
                'resumed_count' => $resumeCount,
                'queued' => true,
                'already_complete' => false,
            ];
        }

        AssessmentAssignment::whereKey($assignment->id)->update([
            'job_batch_id' => null,
        ]);

        $this->storeTargetRows($resumeRows);
        $this->refreshAssignmentSummary($assignment->id);

        return [
            'assignment' => $assignment->fresh(['assessments', 'creator', 'sessions'])->loadCount('targets'),
            'resumed_count' => $resumeCount,
            'queued' => false,
            'already_complete' => false,
        ];
    }

    public function buildAssignmentMonitoring(
        AssessmentAssignment $assignment,
        bool $includeFailedJobs = true
    ): array {
        $batch = $this->buildBatchMonitoring($assignment);
        $retryRows = $assignment->status_distribusi === 'gagal'
            ? $this->resolveRetryTargetRows($assignment->fresh(['sessions']))
            : [];
        $missingTargetCount = $assignment->status_distribusi === 'gagal'
            ? count($retryRows)
            : max((int) $assignment->total_target - (int) $assignment->total_ditugaskan, 0);

        return [
            'distribution_type' => $assignment->job_batch_id ? 'batch' : 'langsung',
            'target_total' => (int) $assignment->total_target,
            'assigned_total' => (int) $assignment->total_ditugaskan,
            'missing_target_total' => $missingTargetCount,
            'retry_available' => $assignment->status_distribusi === 'gagal' && $missingTargetCount > 0,
            'batch' => $batch,
            'failed_jobs' => $includeFailedJobs
                ? $this->buildFailedJobMonitoring($assignment, $batch['failed_job_ids'] ?? [])
                : [],
        ];
    }

    public function processTargetChunk(int $assignmentId, array $targetRows): void
    {
        $assignment = AssessmentAssignment::find($assignmentId);

        if (! $assignment) {
            return;
        }

        $this->storeTargetRows($targetRows);
        $this->refreshAssignmentSummary($assignmentId);
    }

    public function markAsFailed(int $assignmentId): void
    {
        AssessmentAssignment::whereKey($assignmentId)->update([
            'status_distribusi' => 'gagal',
            'processed_at' => null,
        ]);
    }

    public function refreshAssignmentSummary(int $assignmentId): void
    {
        $assignment = AssessmentAssignment::find($assignmentId);

        if (! $assignment) {
            return;
        }

        $totalAssigned = (int) $assignment->targets()
            ->where('status', '!=', 'dibatalkan')
            ->count();
        $currentStatus = $assignment->status_distribusi;
        $isComplete = $assignment->total_target > 0 && $totalAssigned >= $assignment->total_target;

        $resolvedStatus = match (true) {
            $currentStatus === 'gagal' => 'gagal',
            $isComplete => 'selesai',
            $currentStatus === 'draft' => 'draft',
            default => 'diproses',
        };

        $assignment->forceFill([
            'total_ditugaskan' => $totalAssigned,
            'status_distribusi' => $resolvedStatus,
            'processed_at' => $resolvedStatus === 'selesai'
                ? now()
                : ($resolvedStatus === 'gagal' ? $assignment->processed_at : null),
        ])->save();
    }

    private function prepareAssignmentContext(array $payload): array
    {
        $targetKetenagaan = $this->resolveTargetKetenagaan($payload);
        $startTime = $this->normalizeStartTime($payload['jam_mulai'] ?? null);

        return [
            'target_ketenagaan' => $targetKetenagaan,
            'assessment_ids' => $this->resolveAssessmentIds($payload, $targetKetenagaan),
            'guru_ids' => $this->resolveGuruIds($payload, $targetKetenagaan),
            'session_duration_hours' => (int) ($payload['durasi_sesi_jam'] ?? self::DEFAULT_SESSION_DURATION_HOURS),
            'start_time' => $startTime,
            'first_session_start_at' => $this->resolveFirstSessionStartAt($payload, $startTime),
        ];
    }

    private function resolveAssessmentIdsForUpdate(
        AssessmentAssignment $assignment,
        array $resolvedAssessmentIds,
        bool $preserveExistingAssessments
    ): array {
        if (! $preserveExistingAssessments) {
            return $resolvedAssessmentIds;
        }

        return $assignment->assessments()
            ->orderBy('assessment_assignment_assessments.urutan')
            ->pluck('assessments.id')
            ->map(fn ($assessmentId) => (int) $assessmentId)
            ->all();
    }

    private function buildBatchMonitoring(AssessmentAssignment $assignment): ?array
    {
        if (! $assignment->job_batch_id || ! Schema::hasTable('job_batches')) {
            return null;
        }

        $batch = Bus::findBatch($assignment->job_batch_id);

        if (! $batch) {
            return [
                'id' => $assignment->job_batch_id,
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
        AssessmentAssignment $assignment,
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
            ->map(function ($row) use ($assignment) {
                $payload = $this->extractAssignmentQueuePayload((string) $row->payload);

                if (! $payload || $payload['assignment_id'] !== (int) $assignment->id) {
                    return null;
                }

                return [
                    'uuid' => (string) $row->uuid,
                    'queue' => (string) $row->queue,
                    'failed_at' => $row->failed_at ? Carbon::parse($row->failed_at) : null,
                    'target_count' => count($payload['target_rows']),
                    'guru_ids' => collect($payload['target_rows'])
                        ->pluck('guru_id')
                        ->map(fn ($guruId) => (int) $guruId)
                        ->values()
                        ->all(),
                    'message' => $this->extractExceptionMessage((string) $row->exception),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function resolveRetryTargetRows(AssessmentAssignment $assignment): array
    {
        $resumeRows = $this->buildRetryRowsFromQueuePayloads($assignment);

        if ($resumeRows === []) {
            $expectedGuruIds = $this->resolveExpectedGuruIdsFromAssignment($assignment);
            $sessions = $this->ensureSessionsForAssignment($assignment, count($expectedGuruIds));
            $resumeRows = $this->buildTargetRows($assignment->id, $expectedGuruIds, $sessions);
        }

        return $this->filterMissingTargetRows($assignment, $resumeRows);
    }

    private function buildRetryRowsFromQueuePayloads(AssessmentAssignment $assignment): array
    {
        $payloadRows = [];

        if (Schema::hasTable('jobs')) {
            $pendingRows = DB::table('jobs')
                ->select(['payload'])
                ->where('queue', 'assessment-assignment')
                ->orderBy('id')
                ->get();

            foreach ($pendingRows as $row) {
                $payload = $this->extractAssignmentQueuePayload((string) $row->payload);

                if (! $payload || $payload['assignment_id'] !== (int) $assignment->id) {
                    continue;
                }

                $payloadRows = array_merge($payloadRows, $payload['target_rows']);
            }
        }

        if (Schema::hasTable('failed_jobs')) {
            $failedRows = DB::table('failed_jobs')
                ->select(['payload'])
                ->where('queue', 'assessment-assignment')
                ->orderBy('id')
                ->get();

            foreach ($failedRows as $row) {
                $payload = $this->extractAssignmentQueuePayload((string) $row->payload);

                if (! $payload || $payload['assignment_id'] !== (int) $assignment->id) {
                    continue;
                }

                $payloadRows = array_merge($payloadRows, $payload['target_rows']);
            }
        }

        return collect($payloadRows)
            ->filter(fn ($row) => is_array($row) && filled($row['guru_id'] ?? null))
            ->keyBy(fn ($row) => (int) ($row['guru_id'] ?? 0))
            ->values()
            ->all();
    }

    private function filterMissingTargetRows(AssessmentAssignment $assignment, array $targetRows): array
    {
        $existingGuruIds = $assignment->targets()
            ->where('status', '!=', 'dibatalkan')
            ->pluck('guru_id')
            ->map(fn ($guruId) => (int) $guruId)
            ->all();
        $existingGuruIdLookup = array_fill_keys($existingGuruIds, true);

        return collect($targetRows)
            ->filter(fn ($row) => is_array($row) && filled($row['guru_id'] ?? null))
            ->reject(fn ($row) => isset($existingGuruIdLookup[(int) $row['guru_id']]))
            ->map(function (array $row) {
                $row['status'] = $row['status'] ?? 'ditugaskan';
                $row['assigned_at'] = $this->normalizeQueueDateTime($row['assigned_at'] ?? null) ?? now();
                $row['created_at'] = $this->normalizeQueueDateTime($row['created_at'] ?? null) ?? now();
                $row['updated_at'] = $this->normalizeQueueDateTime($row['updated_at'] ?? null) ?? now();

                return $row;
            })
            ->keyBy(fn ($row) => (int) ($row['guru_id'] ?? 0))
            ->values()
            ->all();
    }

    private function normalizeQueueDateTime(mixed $value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value);
        }

        if (is_numeric($value)) {
            return Carbon::createFromTimestamp((int) $value);
        }

        if (is_string($value) && trim($value) !== '') {
            try {
                return Carbon::parse($value);
            } catch (Throwable) {
                return null;
            }
        }

        return null;
    }

    private function extractAssignmentQueuePayload(string $payload): ?array
    {
        $decoded = json_decode($payload, true);

        if (! is_array($decoded)) {
            return null;
        }

        $displayName = (string) ($decoded['displayName'] ?? data_get($decoded, 'data.commandName', ''));

        if ($displayName !== ProcessAssessmentAssignmentTargetsJob::class) {
            return null;
        }

        $serializedCommand = data_get($decoded, 'data.command');

        if (! is_string($serializedCommand) || $serializedCommand === '') {
            return null;
        }

        $job = @unserialize($serializedCommand, [
            'allowed_classes' => [ProcessAssessmentAssignmentTargetsJob::class],
        ]);

        if (! $job instanceof ProcessAssessmentAssignmentTargetsJob) {
            return null;
        }

        return [
            'assignment_id' => (int) $job->assignmentId,
            'target_rows' => is_array($job->targetRows) ? $job->targetRows : [],
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

    private function resolveExpectedGuruIdsFromAssignment(AssessmentAssignment $assignment): array
    {
        $targetKetenagaan = AssessmentKetenagaanType::tryFromMixed($assignment->target_ketenagaan);

        if (! $targetKetenagaan) {
            return [];
        }

        return $this->resolveGuruIds([
            'target_ketenagaan' => $assignment->target_ketenagaan,
            'target_jabatan' => $assignment->target_jabatan ?? [],
            'target_kabupaten' => $assignment->target_kabupaten ?? [],
        ], $targetKetenagaan);
    }

    private function ensureSessionsForAssignment(AssessmentAssignment $assignment, int $totalTargets): array
    {
        $assignment->loadMissing('sessions');
        $expectedSessionCount = $this->calculateTotalSessions($totalTargets);

        if ($assignment->sessions->count() === $expectedSessionCount) {
            return $assignment->sessions->all();
        }

        return DB::transaction(function () use ($assignment, $totalTargets) {
            $assignment->sessions()->delete();

            return $this->createSessions(
                $assignment,
                $totalTargets,
                (int) ($assignment->durasi_sesi_jam ?: self::DEFAULT_SESSION_DURATION_HOURS),
                $this->resolveFirstSessionStartAtFromAssignment($assignment)
            );
        });
    }

    private function resolveFirstSessionStartAtFromAssignment(
        AssessmentAssignment $assignment
    ): ?Carbon {
        if (! $assignment->tanggal_mulai || ! $assignment->jam_mulai) {
            return null;
        }

        return Carbon::parse(
            $assignment->tanggal_mulai->format('Y-m-d').' '.$assignment->jam_mulai
        );
    }

    private function buildTargetRowsForUpdate(
        int $assignmentId,
        array $guruIds,
        array $sessions,
        Collection $existingTargets
    ): array {
        if ($guruIds === []) {
            return [];
        }

        $now = now();
        $existingByGuruId = $existingTargets->keyBy(fn (AssessmentAssignmentTarget $target) => (int) $target->guru_id);

        return collect($guruIds)
            ->values()
            ->map(function (int $guruId, int $index) use ($assignmentId, $sessions, $existingByGuruId, $now) {
                /** @var \App\Models\AssessmentAssignmentTarget|null $existing */
                $existing = $existingByGuruId->get($guruId);
                $sessionIndex = intdiv($index, self::TARGETS_PER_SESSION);

                return [
                    'assessment_assignment_id' => $assignmentId,
                    'assessment_assignment_session_id' => $sessions[$sessionIndex]->id ?? null,
                    'guru_id' => $guruId,
                    'status' => $this->resolveUpdatedTargetStatus($existing),
                    'assigned_at' => $existing?->assigned_at ?: $now,
                    'created_at' => $existing?->created_at ?: $now,
                    'updated_at' => $now,
                ];
            })
            ->all();
    }

    private function resolveUpdatedTargetStatus(?AssessmentAssignmentTarget $target): string
    {
        if (! $target) {
            return 'ditugaskan';
        }

        if ($this->isLockedTarget($target)) {
            return $target->status;
        }

        return $target->status === 'dibatalkan' ? 'ditugaskan' : ($target->status ?: 'ditugaskan');
    }

    private function isLockedTarget(AssessmentAssignmentTarget $target): bool
    {
        if ($target->status === 'dibatalkan') {
            return false;
        }

        if (in_array($target->status, ['dikerjakan', 'selesai'], true)) {
            return true;
        }

        if ($target->started_at || $target->submitted_at) {
            return true;
        }

        return $target->attempt !== null;
    }

    private function resetDistributionState(int $assignmentId): void
    {
        AssessmentAssignment::whereKey($assignmentId)->update([
            'status_distribusi' => 'diproses',
            'processed_at' => null,
        ]);
    }

    private function dispatchBatch(AssessmentAssignment $assignment, array $targetRows): void
    {
        $jobs = collect(array_chunk($targetRows, self::CHUNK_SIZE))
            ->map(fn (array $chunk) => new ProcessAssessmentAssignmentTargetsJob($assignment->id, $chunk))
            ->all();

        try {
            $batch = Bus::batch($jobs)
                ->name('Penugasan Assessment '.$assignment->kode_penugasan)
                ->allowFailures()
                ->dispatch();

            $assignment->update([
                'job_batch_id' => $batch->id,
                'status_distribusi' => 'diproses',
                'processed_at' => null,
            ]);

            $this->refreshAssignmentSummary($assignment->id);
        } catch (Throwable $exception) {
            $assignment->update([
                'status_distribusi' => 'gagal',
                'processed_at' => null,
            ]);

            throw $exception;
        }
    }

    private function storeTargetRows(array $targetRows): void
    {
        if ($targetRows === []) {
            return;
        }

        DB::table('assessment_assignment_targets')->upsert(
            $targetRows,
            ['assessment_assignment_id', 'guru_id'],
            [
                'assessment_assignment_session_id',
                'status',
                'assigned_at',
                'updated_at',
            ]
        );
    }

    private function resolveTargetKetenagaan(array $payload): ?AssessmentKetenagaanType
    {
        return AssessmentKetenagaanType::tryFromMixed($payload['target_ketenagaan'] ?? null);
    }

    private function resolveAssessmentIds(
        array $payload,
        ?AssessmentKetenagaanType $targetKetenagaan = null
    ): array {
        if ($targetKetenagaan) {
            return Assessment::query()
                ->where('is_active', true)
                ->where('status', 'publish')
                ->where('target_ketenagaan', $targetKetenagaan->value)
                ->orderBy('judul')
                ->pluck('id')
                ->map(fn ($assessmentId) => (int) $assessmentId)
                ->all();
        }

        return $this->normalizeAssessmentIds($payload['assessment_ids'] ?? []);
    }

    private function normalizeGuruIds(array $guruIds): array
    {
        return array_values(array_unique(array_map('intval', $guruIds)));
    }

    private function normalizeTargetJabatanSelections(mixed $targetJabatan): array
    {
        return collect(is_array($targetJabatan) ? $targetJabatan : [$targetJabatan])
            ->filter(fn ($jabatan) => filled($jabatan))
            ->map(fn ($jabatan) => trim((string) $jabatan))
            ->filter(fn (string $jabatan) => $jabatan !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function normalizeTargetKabupatenSelections(mixed $targetKabupaten): array
    {
        return collect(is_array($targetKabupaten) ? $targetKabupaten : [$targetKabupaten])
            ->filter(fn ($kabupaten) => filled($kabupaten))
            ->map(fn ($kabupaten) => trim((string) $kabupaten))
            ->filter(fn (string $kabupaten) => $kabupaten !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function resolveGuruIds(
        array $payload,
        ?AssessmentKetenagaanType $targetKetenagaan = null
    ): array {
        if ($targetKetenagaan) {
            $selectedJabatan = $this->normalizeTargetJabatanSelections($payload['target_jabatan'] ?? []);
            $selectedKabupaten = $this->normalizeTargetKabupatenSelections($payload['target_kabupaten'] ?? []);
            $query = Guru::query()
                ->where('eksternal_jabatan', $targetKetenagaan->guruValue());

            if ($selectedJabatan !== []) {
                $query->whereIn('jenis_jabatan', $selectedJabatan);
            }

            if ($selectedKabupaten !== []) {
                $query->whereIn('kabupaten', $selectedKabupaten);
            }

            return $query
                ->orderBy('nama_lengkap')
                ->pluck('id')
                ->map(fn ($guruId) => (int) $guruId)
                ->all();
        }

        if (($payload['guru_selection_mode'] ?? 'manual') !== 'select_all') {
            return $this->normalizeGuruIds($payload['guru_ids'] ?? []);
        }

        $scope = $this->normalizeGuruSelectionScope($payload['guru_selection_scope'] ?? []);
        $excludedIds = $this->normalizeGuruIds($payload['guru_excluded_ids'] ?? []);
        $query = Guru::query()->select(['id', 'nama_lengkap']);

        $this->applyGuruSelectionScope($query, $scope);

        if ($excludedIds !== []) {
            $query->whereNotIn('id', $excludedIds);
        }

        return $query
            ->orderBy('nama_lengkap')
            ->pluck('id')
            ->map(fn ($guruId) => (int) $guruId)
            ->all();
    }

    private function normalizeGuruSelectionScope(array $scope): array
    {
        $nestedFilters = data_get($scope, 'filters', []);
        $filters = array_filter([
            'eksternal_jabatan' => trim((string) data_get($nestedFilters, 'eksternal_jabatan', data_get($scope, 'eksternal_jabatan', ''))),
            'jenis_jabatan' => trim((string) data_get($nestedFilters, 'jenis_jabatan', data_get($scope, 'jenis_jabatan', ''))),
        ], fn (string $value) => $value !== '');

        return [
            'q' => trim((string) data_get($scope, 'q', '')),
            'filters' => $filters,
        ];
    }

    private function applyGuruSelectionScope($query, array $scope): void
    {
        $normalizedScope = $this->normalizeGuruSelectionScope($scope);
        $keyword = $normalizedScope['q'];

        foreach ($normalizedScope['filters'] as $column => $value) {
            $query->where($column, $value);
        }

        if ($keyword === '') {
            return;
        }

        $query->where(function ($builder) use ($keyword) {
            $builder->where('nama_lengkap', 'like', '%'.$keyword.'%')
                ->orWhere('email', 'like', '%'.$keyword.'%')
                ->orWhere('eksternal_jabatan', 'like', '%'.$keyword.'%')
                ->orWhere('jenis_jabatan', 'like', '%'.$keyword.'%')
                ->orWhere('satuan_pendidikan', 'like', '%'.$keyword.'%')
                ->orWhere('kabupaten', 'like', '%'.$keyword.'%');
        });
    }

    private function normalizeAssessmentIds(array $assessmentIds): array
    {
        return array_values(array_unique(array_map('intval', $assessmentIds)));
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = 'TGS-ASM-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(4));
        } while (AssessmentAssignment::where('kode_penugasan', $code)->exists());

        return $code;
    }

    private function createSessions(
        AssessmentAssignment $assignment,
        int $totalTargets,
        int $sessionDurationHours,
        ?Carbon $firstSessionStartAt = null
    ): array {
        $totalSessions = $this->calculateTotalSessions($totalTargets);

        if ($totalSessions === 0) {
            return [];
        }

        $remainingTargets = $totalTargets;
        $sessions = [];

        for ($sessionNumber = 1; $sessionNumber <= $totalSessions; $sessionNumber++) {
            $sessionStartAt = $firstSessionStartAt
                ? $firstSessionStartAt->copy()->addHours(($sessionNumber - 1) * $sessionDurationHours)
                : null;
            $sessionEndAt = $sessionStartAt
                ? $sessionStartAt->copy()->addHours($sessionDurationHours)
                : null;

            $sessions[] = $assignment->sessions()->create([
                'nomor_sesi' => $sessionNumber,
                'label_sesi' => 'Sesi '.$sessionNumber,
                'waktu_mulai' => $sessionStartAt,
                'waktu_selesai' => $sessionEndAt,
                'kapasitas_peserta' => self::TARGETS_PER_SESSION,
                'total_peserta' => min(self::TARGETS_PER_SESSION, $remainingTargets),
                'durasi_sesi_jam' => $sessionDurationHours,
            ]);

            $remainingTargets -= self::TARGETS_PER_SESSION;
        }

        return $sessions;
    }

    private function buildTargetRows(
        int $assignmentId,
        array $guruIds,
        array $sessions
    ): array {
        if ($guruIds === []) {
            return [];
        }

        $now = now();

        return collect($guruIds)
            ->values()
            ->map(function (int $guruId, int $index) use ($assignmentId, $sessions, $now) {
                $sessionIndex = intdiv($index, self::TARGETS_PER_SESSION);

                return [
                    'assessment_assignment_id' => $assignmentId,
                    'assessment_assignment_session_id' => $sessions[$sessionIndex]->id ?? null,
                    'guru_id' => $guruId,
                    'status' => 'ditugaskan',
                    'assigned_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })
            ->all();
    }

    private function calculateTotalSessions(int $totalTargets): int
    {
        if ($totalTargets <= 0) {
            return 0;
        }

        return (int) ceil($totalTargets / self::TARGETS_PER_SESSION);
    }

    private function buildAssessmentSyncData(array $assessmentIds): array
    {
        if ($assessmentIds === []) {
            return [];
        }

        $validAssessmentIds = Assessment::query()
            ->whereKey($assessmentIds)
            ->where('is_active', true)
            ->whereIn('status', ['draft', 'publish'])
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (count($validAssessmentIds) !== count($assessmentIds)) {
            throw (new ModelNotFoundException)->setModel(Assessment::class, $assessmentIds);
        }

        return collect($assessmentIds)
            ->values()
            ->mapWithKeys(fn (int $assessmentId, int $index) => [
                $assessmentId => [
                    'urutan' => $index + 1,
                ],
            ])
            ->all();
    }

    private function normalizeStartTime(?string $startTime): ?string
    {
        if (! $startTime) {
            return null;
        }

        return Carbon::createFromFormat('H:i', $startTime)->format('H:i:s');
    }

    private function resolveFirstSessionStartAt(array $payload, ?string $startTime): ?Carbon
    {
        $startDate = $payload['tanggal_mulai'] ?? null;

        if (! $startDate || ! $startTime) {
            return null;
        }

        return Carbon::parse($startDate.' '.$startTime);
    }
}
