<?php

namespace App\Services;

use App\Enum\AssessmentInstrumentType;
use App\Enum\AssessmentKetenagaanType;
use App\Jobs\ProcessAssessmentAssignmentTargetsJob;
use App\Models\Assessment;
use App\Models\AssessmentAssignment;
use App\Models\AssessmentAssignmentSession;
use App\Models\AssessmentAssignmentTarget;
use App\Models\AssessmentAttempt;
use App\Models\AssessmentAttemptAnswer;
use App\Models\AssessmentCombination;
use App\Models\AssessmentCombinationGeneration;
use App\Models\Guru;
use App\Support\Assessment\AssessmentSecurityConfig;
use App\Support\Assessment\AssessmentSchoolTargetKey;
use App\Support\Assessment\AssessmentStageConfig;
use App\Support\Assessment\AssessmentStageProgress;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class AssessmentAssignmentService
{
    public const QUEUE_CONNECTION = 'database';

    public const QUEUE_NAME = 'default';

    public const BATCH_THRESHOLD = 25;

    public const CHUNK_SIZE = 50;

    public const TARGETS_PER_SESSION = 41;

    public const DEFAULT_SESSION_DURATION_HOURS = 3;

    public const SESSION_DURATION_OPTIONS = [1, 2, 3, 4, 5, 6, 7, 8];

    public function getAvailableCombinationsForKetenagaan(
        AssessmentKetenagaanType $targetKetenagaan
    ): Collection {
        if (! Schema::hasTable('assessment_combinations')) {
            return collect();
        }

        $latestGenerationId = $this->resolveLatestFinishedCombinationGenerationId($targetKetenagaan);

        if ($latestGenerationId) {
            $generatedCombinations = $this->buildCombinationPoolBaseQuery($targetKetenagaan)
                ->where('assessment_combination_generation_id', $latestGenerationId)
                ->reorder()
                ->orderBy('generation_sequence')
                ->orderByDesc('id')
                ->get();

            if ($generatedCombinations->isNotEmpty()) {
                return $generatedCombinations->values();
            }
        }

        return $this->buildCombinationPoolBaseQuery($targetKetenagaan)->get()->values();
    }

    public function getAvailableCombinationOptionSummariesForKetenagaan(
        AssessmentKetenagaanType $targetKetenagaan
    ): Collection {
        if (! Schema::hasTable('assessment_combinations')) {
            return collect();
        }

        $selectColumns = [
            'id',
            'kode_kombinasi',
            'total_assessments',
            'total_forms',
            'total_questions',
            'assessment_combination_generation_id',
            'generation_sequence',
        ];
        $latestGenerationId = $this->resolveLatestFinishedCombinationGenerationId($targetKetenagaan);

        if ($latestGenerationId) {
            $generatedCombinations = $this->buildCombinationPoolBaseQuery($targetKetenagaan)
                ->select($selectColumns)
                ->where('assessment_combination_generation_id', $latestGenerationId)
                ->reorder()
                ->orderBy('generation_sequence')
                ->orderByDesc('id')
                ->get();

            if ($generatedCombinations->isNotEmpty()) {
                return $generatedCombinations->values();
            }
        }

        return $this->buildCombinationPoolBaseQuery($targetKetenagaan)
            ->select($selectColumns)
            ->reorder()
            ->orderByDesc('id')
            ->get()
            ->values();
    }

    public function countAvailableCombinationsForKetenagaan(
        AssessmentKetenagaanType $targetKetenagaan
    ): int {
        if (! Schema::hasTable('assessment_combinations')) {
            return 0;
        }

        $latestGenerationId = $this->resolveLatestFinishedCombinationGenerationId($targetKetenagaan);

        if ($latestGenerationId) {
            $latestGenerationCount = (clone $this->buildCombinationPoolBaseQuery($targetKetenagaan))
                ->reorder()
                ->where('assessment_combination_generation_id', $latestGenerationId)
                ->count();

            if ($latestGenerationCount > 0) {
                return $latestGenerationCount;
            }
        }

        return (clone $this->buildCombinationPoolBaseQuery($targetKetenagaan))
            ->reorder()
            ->count();
    }

    public function buildAssignableParticipantQueryForAssignment(AssessmentAssignment $assignment)
    {
        $targetKetenagaan = AssessmentKetenagaanType::tryFromMixed($assignment->target_ketenagaan);

        if (! $targetKetenagaan) {
            return Guru::query()->whereRaw('1 = 0');
        }

        return Guru::query()
            ->where('eksternal_jabatan', $targetKetenagaan->guruValue());
    }

    public function createAssignment(array $payload, ?int $assignedBy = null): AssessmentAssignment
    {
        $context = $this->prepareAssignmentContext($payload);
        $shouldBatch = count($context['guru_ids']) > self::BATCH_THRESHOLD;

        $assignmentData = DB::transaction(function () use ($payload, $context, $assignedBy, $shouldBatch) {
            $assessmentSyncData = $this->buildAssessmentSyncData(
                $context['assessment_ids'],
                ! $context['uses_combination'],
                $context['stage_configs']
            );
            $totalSessions = $context['session_enabled']
                ? $this->calculateTotalSessions(count($context['guru_ids']))
                : 0;

            $assignment = AssessmentAssignment::create([
                'kode_penugasan' => $this->generateUniqueCode(),
                'judul_penugasan' => $payload['judul_penugasan'],
                'session_enabled' => $context['session_enabled'],
                'target_ketenagaan' => $context['target_ketenagaan']?->value,
                'assessment_combination_id' => $context['primary_assessment_combination']?->id,
                'target_jabatan' => $this->normalizeTargetJabatanSelections($payload['target_jabatan'] ?? []),
                'target_kabupaten' => $this->normalizeTargetKabupatenSelections($payload['target_kabupaten'] ?? []),
                'target_satuan_pendidikan' => $this->normalizeTargetSatuanPendidikanSelections($payload['target_satuan_pendidikan'] ?? []),
                'deskripsi' => $payload['deskripsi'] ?? null,
                'tanggal_mulai' => $payload['tanggal_mulai'] ?? null,
                'jam_mulai' => $context['start_time'],
                'tanggal_selesai' => $payload['tanggal_selesai'] ?? null,
                'kapasitas_per_sesi' => $context['session_enabled'] ? self::TARGETS_PER_SESSION : 0,
                'durasi_sesi_jam' => $context['session_duration_hours'],
                'security_config' => AssessmentSecurityConfig::normalize($payload['security_config'] ?? []),
                'total_sesi' => $totalSessions,
                'status_distribusi' => $shouldBatch ? 'diproses' : 'draft',
                'total_target' => count($context['guru_ids']),
                'total_ditugaskan' => 0,
                'assigned_by' => $assignedBy ?: null,
            ]);

            $assignment->assessments()->sync($assessmentSyncData);

            $sessionRows = $context['session_enabled']
                ? $this->createSessions(
                    $assignment,
                    count($context['guru_ids']),
                    $context['session_duration_hours'],
                    $context['first_session_start_at']
                )
                : [];

            $targetRows = $this->buildTargetRows(
                $assignment,
                $context['guru_ids'],
                $sessionRows,
                $context['assessment_combinations']
            );

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

        return $assignment->load(['assessments', 'creator', 'sessions', 'combination'])->loadCount('targets');
    }

    public function updateAssignment(
        AssessmentAssignment $assignment,
        array $payload,
        ?int $assignedBy = null
    ): array {
        $context = $this->prepareAssignmentContext($payload);
        $shouldBatch = count($context['guru_ids']) > self::BATCH_THRESHOLD;
        $cleanupSummary = $this->collectAssignmentCleanupSummary($assignment->id);

        $assignmentData = DB::transaction(function () use (
            $assignment,
            $payload,
            $context,
            $assignedBy,
            $shouldBatch
        ) {
            $assessmentSyncData = $this->buildAssessmentSyncData(
                $context['assessment_ids'],
                ! $context['uses_combination'],
                $context['stage_configs']
            );
            $totalSessions = $context['session_enabled']
                ? $this->calculateTotalSessions(count($context['guru_ids']))
                : 0;

            $this->cancelAssignmentBatch($assignment->job_batch_id);
            $this->purgeAssignmentQueueArtifacts($assignment->id);
            $this->purgeAssignmentHistory($assignment->id);

            $assignment->forceFill([
                'judul_penugasan' => $payload['judul_penugasan'],
                'session_enabled' => $context['session_enabled'],
                'target_ketenagaan' => $context['target_ketenagaan']?->value,
                'assessment_combination_id' => $context['primary_assessment_combination']?->id,
                'target_jabatan' => $this->normalizeTargetJabatanSelections($payload['target_jabatan'] ?? []),
                'target_kabupaten' => $this->normalizeTargetKabupatenSelections($payload['target_kabupaten'] ?? []),
                'target_satuan_pendidikan' => $this->normalizeTargetSatuanPendidikanSelections($payload['target_satuan_pendidikan'] ?? []),
                'deskripsi' => $payload['deskripsi'] ?? null,
                'tanggal_mulai' => $payload['tanggal_mulai'] ?? null,
                'jam_mulai' => $context['start_time'],
                'tanggal_selesai' => $payload['tanggal_selesai'] ?? null,
                'kapasitas_per_sesi' => $context['session_enabled'] ? self::TARGETS_PER_SESSION : 0,
                'durasi_sesi_jam' => $context['session_duration_hours'],
                'security_config' => AssessmentSecurityConfig::normalize($payload['security_config'] ?? []),
                'total_sesi' => $totalSessions,
                'status_distribusi' => $shouldBatch ? 'diproses' : 'draft',
                'total_target' => count($context['guru_ids']),
                'total_ditugaskan' => 0,
                'assigned_by' => $assignedBy ?: $assignment->assigned_by,
                'job_batch_id' => null,
                'processed_at' => null,
            ])->save();

            $assignment->assessments()->sync($assessmentSyncData);

            $sessions = $context['session_enabled']
                ? $this->createSessions(
                    $assignment,
                    count($context['guru_ids']),
                    $context['session_duration_hours'],
                    $context['first_session_start_at']
                )
                : [];

            $targetRows = $this->buildTargetRows(
                $assignment,
                $context['guru_ids'],
                $sessions,
                $context['assessment_combinations']
            );

            if (! $shouldBatch) {
                $this->storeTargetRows($targetRows);
                $this->refreshAssignmentSummary($assignment->id);
            }

            return [
                'target_rows' => $targetRows,
            ];
        });

        $this->deleteStoredAnswerFiles($cleanupSummary['file_paths']);

        if ($shouldBatch) {
            $freshAssignment = $assignment->fresh();
            $this->dispatchBatch($freshAssignment, $assignmentData['target_rows']);
            $freshAssignment = $freshAssignment->fresh();
        } else {
            $freshAssignment = $assignment->fresh();
        }

        return [
            'assignment' => $freshAssignment->load(['assessments', 'creator', 'sessions', 'combination'])->loadCount('targets'),
            'reset_target_count' => $cleanupSummary['target_count'],
            'deleted_attempt_count' => $cleanupSummary['attempt_count'],
            'deleted_answer_count' => $cleanupSummary['answer_count'],
            'deleted_file_count' => $cleanupSummary['file_count'],
            'new_target_count' => count($context['guru_ids']),
            'queued' => $shouldBatch,
        ];
    }

    public function addParticipants(AssessmentAssignment $assignment, array $guruIds): array
    {
        $requestedGuruIds = $this->normalizeGuruIds($guruIds);

        if ($requestedGuruIds === []) {
            throw ValidationException::withMessages([
                'guru_ids' => 'Pilih minimal satu peserta tambahan.',
            ]);
        }

        return DB::transaction(function () use ($assignment, $requestedGuruIds) {
            /** @var \App\Models\AssessmentAssignment $lockedAssignment */
            $lockedAssignment = AssessmentAssignment::query()
                ->whereKey($assignment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (in_array($lockedAssignment->status_distribusi, ['diproses', 'gagal'], true)) {
                throw ValidationException::withMessages([
                    'guru_ids' => 'Penugasan masih diproses atau bermasalah. Selesaikan distribusi sebelumnya terlebih dahulu.',
                ]);
            }

            $eligibleGuruIds = $this->buildAssignableParticipantQueryForAssignment($lockedAssignment)
                ->whereIn('id', $requestedGuruIds)
                ->pluck('id')
                ->map(fn ($guruId) => (int) $guruId)
                ->all();

            $invalidGuruIds = array_values(array_diff($requestedGuruIds, $eligibleGuruIds));

            if ($invalidGuruIds !== []) {
                throw ValidationException::withMessages([
                    'guru_ids' => 'Sebagian peserta tidak sesuai dengan ketenagaan penugasan ini atau datanya sudah tidak valid.',
                ]);
            }

            $existingGuruIds = AssessmentAssignmentTarget::query()
                ->where('assessment_assignment_id', $lockedAssignment->id)
                ->whereIn('guru_id', $requestedGuruIds)
                ->pluck('guru_id')
                ->map(fn ($guruId) => (int) $guruId)
                ->all();

            if ($existingGuruIds !== []) {
                throw ValidationException::withMessages([
                    'guru_ids' => 'Sebagian peserta sudah ada di penugasan ini. Hanya peserta yang belum ditugaskan yang bisa ditambahkan.',
                ]);
            }

            $orderedGuruIds = $this->buildAssignableParticipantQueryForAssignment($lockedAssignment)
                ->whereIn('id', $requestedGuruIds)
                ->orderBy('nama_lengkap')
                ->pluck('id')
                ->map(fn ($guruId) => (int) $guruId)
                ->all();

            $sessionAllocation = $this->allocateAdditionalSessionSlots(
                $lockedAssignment,
                $orderedGuruIds
            );
            $combinationLookup = $this->buildIncrementalKabupatenCombinationLookup(
                $lockedAssignment,
                $orderedGuruIds
            );
            $targetRows = $this->buildAdditionalTargetRows(
                $lockedAssignment,
                $orderedGuruIds,
                $sessionAllocation['session_id_by_guru'],
                $combinationLookup
            );

            $this->storeTargetRows($targetRows);

            $lockedAssignment->forceFill([
                'total_target' => (int) $lockedAssignment->targets()
                    ->where('status', '!=', 'dibatalkan')
                    ->count(),
                'total_sesi' => $lockedAssignment->usesSessionScheduling()
                    ? (int) $lockedAssignment->sessions()->count()
                    : 0,
            ])->save();

            $this->refreshAssignmentSummary($lockedAssignment->id);

            return [
                'assignment' => $lockedAssignment->fresh(['assessments', 'creator', 'sessions', 'combination'])->loadCount('targets'),
                'added_count' => count($targetRows),
                'created_session_count' => $sessionAllocation['created_session_count'],
            ];
        });
    }

    public function deleteAssignment(AssessmentAssignment $assignment): array
    {
        $cleanupSummary = $this->collectAssignmentCleanupSummary($assignment->id);

        DB::transaction(function () use ($assignment) {
            $this->cancelAssignmentBatch($assignment->job_batch_id);
            $this->purgeAssignmentQueueArtifacts($assignment->id);
            $this->purgeAssignmentHistory($assignment->id);
            $this->purgeAssignmentAssessmentLinks($assignment->id);

            AssessmentAssignment::query()
                ->whereKey($assignment->id)
                ->delete();
        });

        $this->deleteStoredAnswerFiles($cleanupSummary['file_paths']);

        return [
            'deleted_target_count' => $cleanupSummary['target_count'],
            'deleted_attempt_count' => $cleanupSummary['attempt_count'],
            'deleted_answer_count' => $cleanupSummary['answer_count'],
            'deleted_file_count' => $cleanupSummary['file_count'],
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
                'assignment' => $assignment->fresh(['assessments', 'creator', 'sessions', 'combination'])->loadCount('targets'),
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
                'assignment' => $freshAssignment->fresh(['assessments', 'creator', 'sessions', 'combination'])->loadCount('targets'),
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
            'assignment' => $assignment->fresh(['assessments', 'creator', 'sessions', 'combination'])->loadCount('targets'),
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

    public function buildStageAccessSummary(AssessmentAssignment $assignment): array
    {
        $stages = $this->resolveAssignmentStageStates($assignment);
        $stageFlowEnabled = $stages->contains(fn (array $stage) => $stage['enabled']);
        $lockedStages = $stages
            ->filter(fn (array $stage) => $stage['requires_admin_open'])
            ->values();
        $nextLockedStage = $lockedStages->first();
        $lockedStageTotal = (int) $lockedStages->count();
        $lockedStageLabels = $lockedStages
            ->map(function (array $stage): ?string {
                $stageNumber = (int) ($stage['stage_number'] ?? 0);

                return $stageNumber > 0 ? 'Tahap '.$stageNumber : null;
            })
            ->filter()
            ->implode(', ');

        return [
            'stage_flow_enabled' => $stageFlowEnabled,
            'total_stages' => (int) $stages->count(),
            'locked_stage_total' => $lockedStageTotal,
            'opened_stage_total' => (int) $stages
                ->filter(fn (array $stage) => ! $stage['requires_admin_open'])
                ->count(),
            'has_pending_admin_open' => $nextLockedStage !== null,
            'next_locked_stage' => $nextLockedStage,
            'locked_stages' => $lockedStages->all(),
            'status_label' => $nextLockedStage
                ? ($lockedStageTotal === 1
                    ? 'Tahap '.$nextLockedStage['stage_number'].' menunggu dibuka admin'
                    : $lockedStageTotal.' tahap menunggu dibuka admin')
                : ($stageFlowEnabled ? 'Semua tahap terbuka' : 'Tanpa penguncian tahap'),
            'status_tone' => $nextLockedStage ? 'warning' : 'success',
            'action_label' => $nextLockedStage
                ? 'Buka Semua Tahap'
                : null,
            'action_description' => $nextLockedStage
                ? ($lockedStageTotal === 1
                    ? 'Tahap '.$nextLockedStage['stage_number'].' - '.$nextLockedStage['title']
                        .' masih dikunci dan akan dibuka untuk peserta.'
                    : $lockedStageLabels.' masih dikunci dan akan dibuka sekaligus untuk peserta.')
                : null,
        ];
    }

    public function openAllLockedStages(AssessmentAssignment $assignment): array
    {
        return DB::transaction(function () use ($assignment) {
            /** @var \App\Models\AssessmentAssignment $lockedAssignment */
            $lockedAssignment = AssessmentAssignment::query()
                ->with('assessments')
                ->whereKey($assignment->id)
                ->lockForUpdate()
                ->firstOrFail();

            $summary = $this->buildStageAccessSummary($lockedAssignment);
            $lockedStages = collect($summary['locked_stages'] ?? [])
                ->filter(fn ($stage) => is_array($stage))
                ->values();

            if ($lockedStages->isEmpty()) {
                throw ValidationException::withMessages([
                    'assignment' => 'Semua tahap pada penugasan ini sudah terbuka.',
                ]);
            }

            $configByAssessmentId = [];

            foreach ($lockedStages as $lockedStage) {
                $assessment = $lockedAssignment->assessments
                    ->firstWhere('id', (int) ($lockedStage['assessment_id'] ?? 0));

                if (! $assessment) {
                    throw ValidationException::withMessages([
                        'assignment' => 'Assessment tahap yang akan dibuka tidak ditemukan.',
                    ]);
                }

                $stageIndex = (int) ($lockedStage['stage_index'] ?? 0);
                $fallbackConfig = AssessmentStageConfig::defaultForAssessment(
                    $assessment->instrument_type,
                    $stageIndex
                );
                $openedConfig = AssessmentStageConfig::markOpenedByAdmin(
                    is_array($assessment->pivot?->stage_config ?? null) ? $assessment->pivot->stage_config : [],
                    $fallbackConfig
                );

                $lockedAssignment->assessments()->updateExistingPivot($assessment->id, [
                    'stage_config' => $openedConfig,
                    'updated_at' => now(),
                ]);

                $configByAssessmentId[(int) $assessment->id] = $openedConfig;
            }

            $syncedAttemptCount = $this->syncAttemptStageConfigsForAssignment(
                $lockedAssignment->id,
                $configByAssessmentId
            );

            return [
                'assignment' => $lockedAssignment
                    ->fresh(['assessments', 'creator', 'sessions', 'combination'])
                    ->loadCount('targets'),
                'opened_stages' => $lockedStages->all(),
                'synced_attempt_count' => $syncedAttemptCount,
            ];
        });
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

    private function resolveAssignmentStageStates(AssessmentAssignment $assignment): Collection
    {
        $assignment->loadMissing('assessments');

        return $assignment->assessments
            ->values()
            ->map(function (Assessment $assessment, int $index) {
                $config = AssessmentStageConfig::normalizeForAssessment(
                    $assessment->instrument_type,
                    $index,
                    is_array($assessment->pivot?->stage_config ?? null)
                        ? $assessment->pivot->stage_config
                        : []
                );

                return [
                    'assessment_id' => (int) $assessment->id,
                    'stage_index' => $index,
                    'stage_number' => $index + 1,
                    'title' => trim((string) ($assessment->judul ?? '')) ?: 'Assessment '.($index + 1),
                    'enabled' => AssessmentStageConfig::isEnabled($config),
                    'requires_admin_open' => AssessmentStageConfig::isEnabled($config)
                        && AssessmentStageConfig::requiresManualOpening($config, $index),
                    'config' => $config,
                ];
            });
    }

    private function syncAttemptStageConfigsForAssignment(int $assignmentId, array $configByAssessmentId): int
    {
        if ($configByAssessmentId === []) {
            return 0;
        }

        $syncedAttemptCount = 0;

        AssessmentAttempt::query()
            ->with('target')
            ->whereHas('target', function ($query) use ($assignmentId) {
                $query->where('assessment_assignment_id', $assignmentId);
            })
            ->orderBy('id')
            ->chunkById(self::CHUNK_SIZE, function ($attempts) use ($configByAssessmentId, &$syncedAttemptCount) {
                foreach ($attempts as $attempt) {
                    if ($this->syncAttemptStageConfigSnapshot($attempt, $configByAssessmentId)) {
                        $syncedAttemptCount++;
                    }
                }
            });

        return $syncedAttemptCount;
    }

    private function syncAttemptStageConfigSnapshot(
        AssessmentAttempt $attempt,
        array $configByAssessmentId
    ): bool {
        $snapshot = is_array($attempt->structure_snapshot ?? null) ? $attempt->structure_snapshot : [];
        $snapshotAssessments = collect($snapshot['assessments'] ?? [])
            ->filter(fn ($assessment) => is_array($assessment))
            ->values();

        if ($snapshotAssessments->isEmpty()) {
            return false;
        }

        $wasUpdated = false;
        $snapshot['assessments'] = $snapshotAssessments
            ->map(function (array $assessmentMeta, int $index) use ($configByAssessmentId, &$wasUpdated) {
                $assessmentId = (int) ($assessmentMeta['id'] ?? 0);

                if ($assessmentId < 1 || ! array_key_exists($assessmentId, $configByAssessmentId)) {
                    return $assessmentMeta;
                }

                $currentConfig = AssessmentStageConfig::normalizeForAssessment(
                    $assessmentMeta['instrument_type'] ?? null,
                    $index,
                    is_array($assessmentMeta['stage_config'] ?? null) ? $assessmentMeta['stage_config'] : []
                );
                $resolvedConfig = AssessmentStageConfig::normalizeForAssessment(
                    $assessmentMeta['instrument_type'] ?? null,
                    $index,
                    is_array($configByAssessmentId[$assessmentId] ?? null)
                        ? $configByAssessmentId[$assessmentId]
                        : []
                );

                if ($currentConfig !== $resolvedConfig) {
                    $assessmentMeta['stage_config'] = $resolvedConfig;
                    $wasUpdated = true;
                }

                return $assessmentMeta;
            })
            ->all();

        if (! $wasUpdated) {
            return false;
        }

        $progressSnapshot = AssessmentStageProgress::usesStageFlow($snapshot)
            ? AssessmentStageProgress::normalize($attempt->progress_snapshot, $snapshot)
            : null;
        $activeDeadlineAt = is_array($progressSnapshot)
            ? AssessmentStageProgress::activeDeadlineAt($progressSnapshot)
            : null;

        $attempt->forceFill([
            'structure_snapshot' => $snapshot,
            'progress_snapshot' => $progressSnapshot,
            'deadline_at' => $activeDeadlineAt,
        ])->save();

        $target = $attempt->target;

        if ($target && $attempt->status !== 'submitted') {
            $target->forceFill([
                'deadline_at' => $activeDeadlineAt,
            ])->save();
        }

        return true;
    }

    private function prepareAssignmentContext(array $payload): array
    {
        $targetKetenagaan = $this->resolveTargetKetenagaan($payload);
        $assessmentCombinations = $this->resolveAssessmentCombinations($payload, $targetKetenagaan);
        $sessionEnabled = $this->resolveSessionEnabled($payload);
        $startTime = $this->normalizeStartTime($payload['jam_mulai'] ?? null);

        return [
            'session_enabled' => $sessionEnabled,
            'target_ketenagaan' => $targetKetenagaan,
            'assessment_combinations' => $assessmentCombinations,
            'primary_assessment_combination' => $assessmentCombinations->count() === 1
                ? $assessmentCombinations->first()
                : null,
            'uses_combination' => $assessmentCombinations->isNotEmpty(),
            'assessment_ids' => $assessmentIds = $this->resolveAssessmentIds(
                $payload,
                $targetKetenagaan,
                $assessmentCombinations
            ),
            'stage_configs' => $this->resolveStageConfigs($assessmentIds, $payload['stage_configs'] ?? []),
            'guru_ids' => $this->resolveGuruIds($payload, $targetKetenagaan),
            'session_duration_hours' => (int) ($payload['durasi_sesi_jam'] ?? self::DEFAULT_SESSION_DURATION_HOURS),
            'start_time' => $startTime,
            'first_session_start_at' => $sessionEnabled
                ? $this->resolveFirstSessionStartAt($payload, $startTime)
                : null,
        ];
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
            $resumeRows = $this->buildTargetRows(
                $assignment,
                $expectedGuruIds,
                $sessions,
                $this->resolveAssessmentCombinationsFromAssignment($assignment)
            );
        }

        return $this->filterMissingTargetRows($assignment, $resumeRows);
    }

    private function buildRetryRowsFromQueuePayloads(AssessmentAssignment $assignment): array
    {
        $payloadRows = [];

        if (Schema::hasTable('jobs')) {
            $pendingRows = DB::table('jobs')
                ->select(['payload'])
                ->where('queue', self::QUEUE_NAME)
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
                ->where('queue', self::QUEUE_NAME)
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
                $row['assessment_combination_id'] = filled($row['assessment_combination_id'] ?? null)
                    ? (int) $row['assessment_combination_id']
                    : null;
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
            'target_satuan_pendidikan' => $assignment->target_satuan_pendidikan ?? [],
        ], $targetKetenagaan);
    }

    private function ensureSessionsForAssignment(AssessmentAssignment $assignment, int $totalTargets): array
    {
        if (! $assignment->usesSessionScheduling()) {
            return [];
        }

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
                ->onConnection(self::QUEUE_CONNECTION)
                ->onQueue(self::QUEUE_NAME)
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
                'assessment_combination_id',
                'status',
                'assigned_at',
                'updated_at',
            ]
        );
    }

    private function collectAssignmentCleanupSummary(int $assignmentId): array
    {
        if (! Schema::hasTable('assessment_assignment_targets')) {
            return [
                'target_count' => 0,
                'attempt_count' => 0,
                'answer_count' => 0,
                'file_count' => 0,
                'file_paths' => [],
            ];
        }

        $targetIds = DB::table('assessment_assignment_targets')
            ->where('assessment_assignment_id', $assignmentId)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($targetIds === []) {
            return [
                'target_count' => 0,
                'attempt_count' => 0,
                'answer_count' => 0,
                'file_count' => 0,
                'file_paths' => [],
            ];
        }

        $attemptIds = [];
        $answerCount = 0;
        $filePaths = [];

        if (Schema::hasTable('assessment_attempts')) {
            $attemptIds = DB::table('assessment_attempts')
                ->whereIn('assessment_assignment_target_id', $targetIds)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        if ($attemptIds !== [] && Schema::hasTable('assessment_attempt_answers')) {
            $answerCount = (int) DB::table('assessment_attempt_answers')
                ->whereIn('assessment_attempt_id', $attemptIds)
                ->count();

            $filePaths = DB::table('assessment_attempt_answers')
                ->whereIn('assessment_attempt_id', $attemptIds)
                ->whereNotNull('answer_file_path')
                ->pluck('answer_file_path')
                ->filter(fn ($path) => filled($path))
                ->map(fn ($path) => (string) $path)
                ->unique()
                ->values()
                ->all();
        }

        return [
            'target_count' => count($targetIds),
            'attempt_count' => count($attemptIds),
            'answer_count' => $answerCount,
            'file_count' => count($filePaths),
            'file_paths' => $filePaths,
        ];
    }

    private function purgeAssignmentHistory(int $assignmentId): void
    {
        if (! Schema::hasTable('assessment_assignment_targets')) {
            return;
        }

        $targetIds = DB::table('assessment_assignment_targets')
            ->where('assessment_assignment_id', $assignmentId)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($targetIds !== [] && Schema::hasTable('assessment_attempts')) {
            $attemptIds = DB::table('assessment_attempts')
                ->whereIn('assessment_assignment_target_id', $targetIds)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            if ($attemptIds !== [] && Schema::hasTable('assessment_attempt_answers')) {
                AssessmentAttemptAnswer::query()
                    ->whereIn('assessment_attempt_id', $attemptIds)
                    ->delete();
            }

            AssessmentAttempt::query()
                ->whereIn('assessment_assignment_target_id', $targetIds)
                ->delete();
        }

        AssessmentAssignmentTarget::query()
            ->where('assessment_assignment_id', $assignmentId)
            ->delete();

        if (Schema::hasTable('assessment_assignment_sessions')) {
            DB::table('assessment_assignment_sessions')
                ->where('assessment_assignment_id', $assignmentId)
                ->delete();
        }
    }

    private function purgeAssignmentAssessmentLinks(int $assignmentId): void
    {
        if (! Schema::hasTable('assessment_assignment_assessments')) {
            return;
        }

        DB::table('assessment_assignment_assessments')
            ->where('assessment_assignment_id', $assignmentId)
            ->delete();
    }

    private function cancelAssignmentBatch(?string $batchId): void
    {
        if (! $batchId || ! Schema::hasTable('job_batches')) {
            return;
        }

        $batch = Bus::findBatch($batchId);

        if ($batch && ! $batch->cancelled()) {
            $batch->cancel();
        }
    }

    private function purgeAssignmentQueueArtifacts(int $assignmentId): void
    {
        $this->purgeAssignmentQueueTable('jobs', $assignmentId);
        $this->purgeAssignmentQueueTable('failed_jobs', $assignmentId);
    }

    private function purgeAssignmentQueueTable(string $table, int $assignmentId): void
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
            $payload = $this->extractAssignmentQueuePayload((string) ($row->payload ?? ''));

            if (! $payload || $payload['assignment_id'] !== $assignmentId) {
                continue;
            }

            if (isset($row->id)) {
                $idsToDelete[] = $row->id;

                continue;
            }

            if (isset($row->uuid)) {
                $uuidsToDelete[] = (string) $row->uuid;
            }
        }

        if ($idsToDelete !== [] && Schema::hasColumn($table, 'id')) {
            DB::table($table)
                ->whereIn('id', $idsToDelete)
                ->delete();
        }

        if ($uuidsToDelete !== [] && Schema::hasColumn($table, 'uuid')) {
            DB::table($table)
                ->whereIn('uuid', $uuidsToDelete)
                ->delete();
        }
    }

    private function deleteStoredAnswerFiles(array $filePaths): void
    {
        $normalizedPaths = collect($filePaths)
            ->filter(fn ($path) => filled($path))
            ->map(fn ($path) => (string) $path)
            ->unique()
            ->values()
            ->all();

        if ($normalizedPaths === []) {
            return;
        }

        try {
            Storage::disk('public')->delete($normalizedPaths);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function resolveTargetKetenagaan(array $payload): ?AssessmentKetenagaanType
    {
        return AssessmentKetenagaanType::tryFromMixed($payload['target_ketenagaan'] ?? null);
    }

    private function resolveAssessmentIds(
        array $payload,
        ?AssessmentKetenagaanType $targetKetenagaan = null,
        ?Collection $assessmentCombinations = null
    ): array {
        if ($assessmentCombinations && $assessmentCombinations->isNotEmpty()) {
            return $this->orderAssessmentIdsForStageFlow(
                $this->extractAssessmentIdsFromCombinations($assessmentCombinations)
            );
        }

        if ($targetKetenagaan) {
            return $this->orderAssessmentIdsForStageFlow(
                Assessment::query()
                    ->where('is_active', true)
                    ->where('status', 'publish')
                    ->where('target_ketenagaan', $targetKetenagaan->value)
                    ->pluck('id')
                    ->map(fn ($assessmentId) => (int) $assessmentId)
                    ->all()
            );
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

    private function normalizeTargetSatuanPendidikanSelections(mixed $targetSatuanPendidikan): array
    {
        return collect(is_array($targetSatuanPendidikan) ? $targetSatuanPendidikan : [$targetSatuanPendidikan])
            ->filter(fn ($selectionKey) => filled($selectionKey))
            ->map(fn ($selectionKey) => trim((string) $selectionKey))
            ->filter(fn (string $selectionKey) => $selectionKey !== '')
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
            $selectedSatuanPendidikan = $this->normalizeTargetSatuanPendidikanSelections(
                $payload['target_satuan_pendidikan'] ?? []
            );
            $query = Guru::query()
                ->where('eksternal_jabatan', $targetKetenagaan->guruValue());

            if ($selectedJabatan !== []) {
                $query->whereIn('jenis_jabatan', $selectedJabatan);
            }

            if ($selectedKabupaten !== []) {
                $query->whereIn('kabupaten', $selectedKabupaten);
            }

            $this->applyTargetSatuanPendidikanFilter($query, $selectedSatuanPendidikan);

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

    private function orderAssessmentIdsForStageFlow(array $assessmentIds): array
    {
        $normalizedAssessmentIds = $this->normalizeAssessmentIds($assessmentIds);

        if ($normalizedAssessmentIds === []) {
            return [];
        }

        $assessmentLookup = Assessment::query()
            ->whereIn('id', $normalizedAssessmentIds)
            ->get(['id', 'instrument_type', 'judul'])
            ->keyBy('id');

        return collect($normalizedAssessmentIds)
            ->values()
            ->map(function (int $assessmentId, int $index) use ($assessmentLookup) {
                $assessment = $assessmentLookup->get($assessmentId);

                return [
                    'id' => $assessmentId,
                    'instrument_order' => AssessmentInstrumentType::assignmentStageOrderFor(
                        $assessment?->instrument_type
                    ),
                    'title' => strtolower(trim((string) ($assessment?->judul ?? ''))),
                    'source_index' => $index,
                ];
            })
            ->sort(function (array $left, array $right) {
                return [
                    $left['instrument_order'],
                    $left['title'],
                    $left['source_index'],
                    $left['id'],
                ] <=> [
                    $right['instrument_order'],
                    $right['title'],
                    $right['source_index'],
                    $right['id'],
                ];
            })
            ->pluck('id')
            ->all();
    }

    private function resolveAssessmentCombinations(
        array $payload,
        ?AssessmentKetenagaanType $targetKetenagaan = null
    ): Collection {
        if ($targetKetenagaan) {
            $combinationPool = $this->getAvailableCombinationsForKetenagaan($targetKetenagaan);

            if ($combinationPool->isNotEmpty()) {
                return $combinationPool;
            }
        }

        $legacyCombination = $this->resolveLegacySelectedCombination($payload, $targetKetenagaan);

        return $legacyCombination ? collect([$legacyCombination]) : collect();
    }

    private function resolveAssessmentCombinationsFromAssignment(
        AssessmentAssignment $assignment
    ): Collection {
        $targetKetenagaan = AssessmentKetenagaanType::tryFromMixed($assignment->target_ketenagaan);

        if ($targetKetenagaan) {
            $combinationPool = $this->getAvailableCombinationsForKetenagaan($targetKetenagaan);

            if ($combinationPool->isNotEmpty()) {
                return $combinationPool;
            }
        }

        if (
            Schema::hasTable('assessment_combinations') &&
            filled($assignment->assessment_combination_id)
        ) {
            $legacyCombination = AssessmentCombination::query()
                ->whereKey((int) $assignment->assessment_combination_id)
                ->where('is_active', true)
                ->first();

            if ($legacyCombination) {
                return collect([$legacyCombination]);
            }
        }

        return collect();
    }

    private function resolveLegacySelectedCombination(
        array $payload,
        ?AssessmentKetenagaanType $targetKetenagaan = null
    ): ?AssessmentCombination {
        if (! Schema::hasTable('assessment_combinations')) {
            return null;
        }

        $combinationId = (int) ($payload['assessment_combination_id'] ?? 0);

        if ($combinationId < 1) {
            return null;
        }

        $query = AssessmentCombination::query()
            ->whereKey($combinationId)
            ->where('is_active', true);

        if ($targetKetenagaan) {
            $query->where('target_ketenagaan', $targetKetenagaan->value);
        }

        return $query->firstOrFail();
    }

    private function extractAssessmentIdsFromCombinations(Collection $assessmentCombinations): array
    {
        return $assessmentCombinations
            ->flatMap(fn (AssessmentCombination $assessmentCombination) => $this->extractAssessmentIdsFromCombination($assessmentCombination))
            ->unique()
            ->values()
            ->all();
    }

    private function extractAssessmentIdsFromCombination(AssessmentCombination $assessmentCombination): array
    {
        return collect(data_get($assessmentCombination->structure_snapshot, 'assessments', []))
            ->pluck('id')
            ->map(fn ($assessmentId) => (int) $assessmentId)
            ->filter(fn (int $assessmentId) => $assessmentId > 0)
            ->values()
            ->all();
    }

    private function buildCombinationPoolBaseQuery(
        AssessmentKetenagaanType $targetKetenagaan
    ) {
        $query = AssessmentCombination::query()
            ->where('is_active', true)
            ->where('target_ketenagaan', $targetKetenagaan->value);

        if (Schema::hasColumn('assessment_combinations', 'generated_at')) {
            $query->orderByDesc('generated_at');
        }

        return $query->orderByDesc('id');
    }

    private function resolveLatestFinishedCombinationGenerationId(
        AssessmentKetenagaanType $targetKetenagaan
    ): ?int {
        if (
            ! Schema::hasTable('assessment_combination_generations') ||
            ! Schema::hasColumn('assessment_combinations', 'assessment_combination_generation_id')
        ) {
            return null;
        }

        $generation = AssessmentCombinationGeneration::query()
            ->where('target_ketenagaan', $targetKetenagaan->value)
            ->where('status', 'selesai')
            ->whereHas('combinations', function ($query) {
                $query->where('is_active', true);
            })
            ->orderByDesc('processed_at')
            ->orderByDesc('id')
            ->first();

        return $generation ? (int) $generation->id : null;
    }

    private function buildCombinationSeedKey(
        ?string $title,
        ?string $targetKetenagaan,
        array $selectedJabatan = [],
        array $selectedKabupaten = [],
        array $selectedSatuanPendidikan = []
    ): string {
        return implode('|', [
            trim((string) $title),
            trim((string) $targetKetenagaan),
            implode(',', $this->normalizeSeedSelectionList($selectedJabatan)),
            implode(',', $this->normalizeSeedSelectionList($selectedKabupaten)),
            implode(',', $this->normalizeSeedSelectionList($selectedSatuanPendidikan)),
        ]);
    }

    private function buildCombinationSeedKeyFromAssignment(AssessmentAssignment $assignment): string
    {
        return $this->buildCombinationSeedKey(
            $assignment->judul_penugasan,
            $assignment->target_ketenagaan,
            $assignment->target_jabatan ?? [],
            $assignment->target_kabupaten ?? [],
            $assignment->target_satuan_pendidikan ?? []
        );
    }

    private function applyTargetSatuanPendidikanFilter($query, array $selectedSatuanPendidikan): void
    {
        $groups = $this->groupSatuanPendidikanSelectionsByKabupaten($selectedSatuanPendidikan);

        if ($groups === []) {
            return;
        }

        $query->where(function ($builder) use ($groups) {
            foreach ($groups as $kabupaten => $schools) {
                $builder->orWhere(function ($nestedQuery) use ($kabupaten, $schools) {
                    if ($kabupaten !== '__ANY__') {
                        $nestedQuery->where('kabupaten', $kabupaten);
                    }

                    $nestedQuery->whereIn('satuan_pendidikan', $schools);
                });
            }
        });
    }

    private function groupSatuanPendidikanSelectionsByKabupaten(array $selectedSatuanPendidikan): array
    {
        return collect($selectedSatuanPendidikan)
            ->map(fn ($selectionKey) => AssessmentSchoolTargetKey::decode($selectionKey))
            ->filter(fn (array $selection) => $selection['satuan_pendidikan'] !== '')
            ->groupBy(fn (array $selection) => $selection['kabupaten'] ?? '__ANY__')
            ->map(function (Collection $rows) {
                return $rows
                    ->pluck('satuan_pendidikan')
                    ->map(fn ($school) => trim((string) $school))
                    ->filter(fn (string $school) => $school !== '')
                    ->unique()
                    ->values()
                    ->all();
            })
            ->filter(fn (array $schools) => $schools !== [])
            ->all();
    }

    private function normalizeSeedSelectionList(array $values): array
    {
        $normalized = collect($values)
            ->filter(fn ($value) => filled($value))
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn (string $value) => $value !== '')
            ->unique()
            ->values()
            ->all();

        natcasesort($normalized);

        return array_values($normalized);
    }

    private function sortCombinationsForRoundRobin(
        Collection $assessmentCombinations,
        string $seedKey
    ): Collection {
        return $assessmentCombinations
            ->sort(function (AssessmentCombination $left, AssessmentCombination $right) use ($seedKey) {
                $leftHash = $this->stableHash($seedKey.'|'.(int) $left->id);
                $rightHash = $this->stableHash($seedKey.'|'.(int) $right->id);

                if ($leftHash === $rightHash) {
                    return (int) $left->id <=> (int) $right->id;
                }

                return $leftHash <=> $rightHash;
            })
            ->values();
    }

    private function stableHash(string $value): int
    {
        $hash = 5381;
        $length = strlen($value);

        for ($index = 0; $index < $length; $index++) {
            $hash = (($hash << 5) + $hash + ord($value[$index])) & 0x7FFFFFFF;
        }

        return $hash;
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = 'TGS-ASM-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(4));
        } while (AssessmentAssignment::where('kode_penugasan', $code)->exists());

        return $code;
    }

    private function allocateAdditionalSessionSlots(
        AssessmentAssignment $assignment,
        array $guruIds
    ): array {
        if (! $assignment->usesSessionScheduling() || $guruIds === []) {
            return [
                'session_id_by_guru' => [],
                'created_session_count' => 0,
            ];
        }

        $sessions = $assignment->sessions()
            ->orderBy('nomor_sesi')
            ->lockForUpdate()
            ->get()
            ->values();
        $occupancyBySessionId = AssessmentAssignmentTarget::query()
            ->selectRaw('assessment_assignment_session_id, count(*) as aggregate')
            ->where('assessment_assignment_id', $assignment->id)
            ->whereNotNull('assessment_assignment_session_id')
            ->where('status', '!=', 'dibatalkan')
            ->groupBy('assessment_assignment_session_id')
            ->pluck('aggregate', 'assessment_assignment_session_id')
            ->mapWithKeys(fn ($count, $sessionId) => [(int) $sessionId => (int) $count])
            ->all();
        $createdSessionIds = [];
        $sessionIdByGuru = [];
        $nextSessionNumber = ((int) $sessions->max('nomor_sesi')) + 1;
        $tailSession = $sessions->last();

        foreach ($guruIds as $guruId) {
            if (! $tailSession || ! $this->canAppendParticipantToSession(
                $tailSession,
                (int) ($occupancyBySessionId[$tailSession->id] ?? 0)
            )) {
                $tailSession = $this->createAdditionalSession(
                    $assignment,
                    $tailSession,
                    max($nextSessionNumber, 1)
                );
                $sessions->push($tailSession);
                $occupancyBySessionId[$tailSession->id] = 0;
                $createdSessionIds[$tailSession->id] = true;
                $nextSessionNumber = $tailSession->nomor_sesi + 1;
            }

            $sessionIdByGuru[$guruId] = $tailSession->id;
            $occupancyBySessionId[$tailSession->id] = (int) ($occupancyBySessionId[$tailSession->id] ?? 0) + 1;
        }

        foreach ($sessions as $session) {
            $resolvedTotal = (int) ($occupancyBySessionId[$session->id] ?? 0);

            if ((int) $session->total_peserta === $resolvedTotal) {
                continue;
            }

            $session->forceFill([
                'total_peserta' => $resolvedTotal,
            ])->save();
        }

        return [
            'session_id_by_guru' => $sessionIdByGuru,
            'created_session_count' => count($createdSessionIds),
        ];
    }

    private function canAppendParticipantToSession(
        AssessmentAssignmentSession $session,
        int $occupiedParticipants
    ): bool {
        $capacity = max((int) ($session->kapasitas_peserta ?: self::TARGETS_PER_SESSION), 1);

        if ($occupiedParticipants >= $capacity) {
            return false;
        }

        if ($session->waktu_mulai && now()->greaterThanOrEqualTo($session->waktu_mulai)) {
            return false;
        }

        return true;
    }

    private function createAdditionalSession(
        AssessmentAssignment $assignment,
        ?AssessmentAssignmentSession $previousSession,
        int $sessionNumber
    ): AssessmentAssignmentSession {
        $durationHours = (int) ($assignment->durasi_sesi_jam ?: self::DEFAULT_SESSION_DURATION_HOURS);
        $sessionStartAt = $this->resolveAdditionalSessionStartAt(
            $assignment,
            $previousSession,
            $sessionNumber,
            $durationHours
        );
        $sessionEndAt = $sessionStartAt
            ? $sessionStartAt->copy()->addHours($durationHours)
            : null;

        return $assignment->sessions()->create([
            'nomor_sesi' => $sessionNumber,
            'label_sesi' => 'Sesi '.$sessionNumber,
            'waktu_mulai' => $sessionStartAt,
            'waktu_selesai' => $sessionEndAt,
            'kapasitas_peserta' => self::TARGETS_PER_SESSION,
            'total_peserta' => 0,
            'durasi_sesi_jam' => $durationHours,
        ]);
    }

    private function resolveAdditionalSessionStartAt(
        AssessmentAssignment $assignment,
        ?AssessmentAssignmentSession $previousSession,
        int $sessionNumber,
        int $durationHours
    ): ?Carbon {
        if ($previousSession?->waktu_selesai) {
            return $previousSession->waktu_selesai->copy();
        }

        if ($previousSession?->waktu_mulai) {
            $previousDuration = (int) ($previousSession->durasi_sesi_jam ?: $durationHours);

            return $previousSession->waktu_mulai->copy()->addHours($previousDuration);
        }

        $firstSessionStartAt = $this->resolveFirstSessionStartAtFromAssignment($assignment);

        if (! $firstSessionStartAt) {
            return null;
        }

        return $firstSessionStartAt->copy()->addHours(max($sessionNumber - 1, 0) * $durationHours);
    }

    private function buildIncrementalKabupatenCombinationLookup(
        AssessmentAssignment $assignment,
        array $guruIds
    ): array {
        $existingLookup = $this->resolveExistingKabupatenCombinationLookup($assignment);
        $orderedCombinations = $this->sortCombinationsForRoundRobin(
            $this->resolveAssessmentCombinationsFromAssignment($assignment),
            $this->buildCombinationSeedKeyFromAssignment($assignment)
        );

        if ($orderedCombinations->isEmpty()) {
            return $existingLookup;
        }

        $newKabupatenKeys = collect($this->resolveGuruKabupatenLookup($guruIds))
            ->map(fn ($kabupatenKey) => $kabupatenKey !== '' ? $kabupatenKey : '__EMPTY__')
            ->unique()
            ->filter(fn (string $kabupatenKey) => ! array_key_exists($kabupatenKey, $existingLookup))
            ->values()
            ->all();

        natcasesort($newKabupatenKeys);
        $newKabupatenKeys = array_values($newKabupatenKeys);
        $offset = count($existingLookup);

        foreach ($newKabupatenKeys as $index => $kabupatenKey) {
            $combination = $orderedCombinations[($offset + $index) % $orderedCombinations->count()] ?? null;

            if (! $combination) {
                continue;
            }

            $existingLookup[$kabupatenKey] = (int) $combination->id;
        }

        return $existingLookup;
    }

    private function resolveExistingKabupatenCombinationLookup(
        AssessmentAssignment $assignment
    ): array {
        return DB::table('assessment_assignment_targets as target')
            ->join('gurus as guru', 'guru.id', '=', 'target.guru_id')
            ->where('target.assessment_assignment_id', $assignment->id)
            ->where('target.status', '!=', 'dibatalkan')
            ->whereNotNull('target.assessment_combination_id')
            ->orderBy('target.id')
            ->get([
                'guru.kabupaten',
                'target.assessment_combination_id',
            ])
            ->reduce(function (array $carry, $row) {
                $kabupatenKey = $this->normalizeKabupatenKey($row->kabupaten ?? null);
                $lookupKey = $kabupatenKey !== '' ? $kabupatenKey : '__EMPTY__';

                if (! array_key_exists($lookupKey, $carry)) {
                    $carry[$lookupKey] = (int) $row->assessment_combination_id;
                }

                return $carry;
            }, []);
    }

    private function buildAdditionalTargetRows(
        AssessmentAssignment $assignment,
        array $guruIds,
        array $sessionIdByGuru,
        array $kabupatenCombinationLookup
    ): array {
        if ($guruIds === []) {
            return [];
        }

        $now = now();
        $guruKabupatenLookup = $this->resolveGuruKabupatenLookup($guruIds);
        $defaultCombinationId = collect($kabupatenCombinationLookup)
            ->filter(fn ($combinationId) => filled($combinationId))
            ->map(fn ($combinationId) => (int) $combinationId)
            ->first();

        return collect($guruIds)
            ->values()
            ->map(function (int $guruId) use (
                $assignment,
                $sessionIdByGuru,
                $kabupatenCombinationLookup,
                $guruKabupatenLookup,
                $defaultCombinationId,
                $now
            ) {
                $kabupatenKey = $guruKabupatenLookup[$guruId] ?? '';
                $lookupKey = $kabupatenKey !== '' ? $kabupatenKey : '__EMPTY__';

                return [
                    'assessment_assignment_id' => $assignment->id,
                    'assessment_assignment_session_id' => $sessionIdByGuru[$guruId] ?? null,
                    'assessment_combination_id' => $kabupatenCombinationLookup[$lookupKey] ?? $defaultCombinationId,
                    'guru_id' => $guruId,
                    'status' => 'ditugaskan',
                    'assigned_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })
            ->all();
    }

    private function createSessions(
        AssessmentAssignment $assignment,
        int $totalTargets,
        int $sessionDurationHours,
        ?Carbon $firstSessionStartAt = null
    ): array {
        if (! $assignment->usesSessionScheduling()) {
            return [];
        }

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
        AssessmentAssignment $assignment,
        array $guruIds,
        array $sessions,
        ?Collection $assessmentCombinations = null
    ): array {
        if ($guruIds === []) {
            return [];
        }

        $now = now();
        $assessmentCombinations = $assessmentCombinations ?? collect();
        $orderedCombinations = $this->sortCombinationsForRoundRobin(
            $assessmentCombinations,
            $this->buildCombinationSeedKeyFromAssignment($assignment)
        );
        $guruKabupatenLookup = $this->resolveGuruKabupatenLookup($guruIds);
        $kabupatenCombinationLookup = $this->buildKabupatenCombinationLookup(
            $guruKabupatenLookup,
            $orderedCombinations
        );
        $fallbackCombinationId = $orderedCombinations->isNotEmpty()
            ? (int) $orderedCombinations->first()->id
            : null;

        return collect($guruIds)
            ->values()
            ->map(function (int $guruId, int $index) use (
                $assignment,
                $sessions,
                $now,
                $guruKabupatenLookup,
                $kabupatenCombinationLookup,
                $fallbackCombinationId
            ) {
                $sessionIndex = intdiv($index, self::TARGETS_PER_SESSION);
                $kabupatenKey = $guruKabupatenLookup[$guruId] ?? '';

                return [
                    'assessment_assignment_id' => $assignment->id,
                    'assessment_assignment_session_id' => $sessions[$sessionIndex]->id ?? null,
                    'assessment_combination_id' => $kabupatenCombinationLookup[$kabupatenKey] ?? $fallbackCombinationId,
                    'guru_id' => $guruId,
                    'status' => 'ditugaskan',
                    'assigned_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })
            ->all();
    }

    private function resolveGuruKabupatenLookup(array $guruIds): array
    {
        if ($guruIds === []) {
            return [];
        }

        return Guru::query()
            ->whereIn('id', $guruIds)
            ->pluck('kabupaten', 'id')
            ->mapWithKeys(function ($kabupaten, $guruId) {
                return [
                    (int) $guruId => $this->normalizeKabupatenKey($kabupaten),
                ];
            })
            ->all();
    }

    private function buildKabupatenCombinationLookup(
        array $guruKabupatenLookup,
        Collection $orderedCombinations
    ): array {
        if ($orderedCombinations->isEmpty()) {
            return [];
        }

        $kabupatenKeys = collect($guruKabupatenLookup)
            ->filter(fn (string $kabupatenKey) => $kabupatenKey !== '')
            ->unique()
            ->values()
            ->all();

        natcasesort($kabupatenKeys);
        $kabupatenKeys = array_values($kabupatenKeys);

        return collect($kabupatenKeys)
            ->mapWithKeys(function (string $kabupatenKey, int $index) use ($orderedCombinations) {
                $combination = $orderedCombinations[$index % $orderedCombinations->count()] ?? null;

                return [
                    $kabupatenKey => $combination ? (int) $combination->id : null,
                ];
            })
            ->all();
    }

    private function normalizeKabupatenKey(mixed $kabupaten): string
    {
        return trim((string) ($kabupaten ?? ''));
    }

    private function calculateTotalSessions(int $totalTargets): int
    {
        if ($totalTargets <= 0) {
            return 0;
        }

        return (int) ceil($totalTargets / self::TARGETS_PER_SESSION);
    }

    private function buildAssessmentSyncData(
        array $assessmentIds,
        bool $strict = true,
        array $stageConfigs = []
    ): array
    {
        if ($assessmentIds === []) {
            return [];
        }

        if ($strict) {
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

            $usableAssessmentIds = $validAssessmentIds;
        } else {
            $usableAssessmentIds = Assessment::query()
                ->whereKey($assessmentIds)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        $assessmentConfigLookup = Assessment::query()
            ->whereIn('id', $usableAssessmentIds)
            ->get(['id', 'instrument_type'])
            ->keyBy('id');

        return collect($assessmentIds)
            ->values()
            ->filter(fn (int $assessmentId) => in_array($assessmentId, $usableAssessmentIds, true))
            ->mapWithKeys(fn (int $assessmentId, int $index) => [
                $assessmentId => [
                    'urutan' => $index + 1,
                    'stage_config' => AssessmentStageConfig::normalizeForAssessment(
                        $assessmentConfigLookup->get($assessmentId)?->instrument_type,
                        $index,
                        is_array($stageConfigs[$assessmentId] ?? null) ? $stageConfigs[$assessmentId] : []
                    ),
                ],
            ])
            ->all();
    }

    private function resolveStageConfigs(array $assessmentIds, mixed $rawStageConfigs): array
    {
        $normalizedRawConfigs = is_array($rawStageConfigs) ? $rawStageConfigs : [];

        if ($assessmentIds === []) {
            return [];
        }

        $assessmentLookup = Assessment::query()
            ->whereIn('id', $assessmentIds)
            ->get(['id', 'instrument_type'])
            ->keyBy('id');

        return collect($assessmentIds)
            ->values()
            ->mapWithKeys(function (int $assessmentId, int $index) use ($assessmentLookup, $normalizedRawConfigs) {
                $rawConfig = is_array($normalizedRawConfigs[$assessmentId] ?? null)
                    ? $normalizedRawConfigs[$assessmentId]
                    : [];

                if (array_key_exists('security_enabled', $rawConfig) || array_key_exists('security_require_fullscreen', $rawConfig)) {
                    $rawConfig['security'] = AssessmentSecurityConfig::fromRequest($rawConfig);
                }

                return [
                    $assessmentId => AssessmentStageConfig::normalizeForAssessment(
                        $assessmentLookup->get($assessmentId)?->instrument_type,
                        $index,
                        $rawConfig
                    ),
                ];
            })
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

    private function resolveSessionEnabled(array $payload): bool
    {
        return filter_var(
            $payload['session_enabled'] ?? true,
            FILTER_VALIDATE_BOOLEAN
        );
    }
}
