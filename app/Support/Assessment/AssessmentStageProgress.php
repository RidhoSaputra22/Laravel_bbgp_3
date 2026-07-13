<?php

namespace App\Support\Assessment;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class AssessmentStageProgress
{
    public const STATUS_LOCKED = 'locked';

    public const STATUS_READY = 'ready';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_SUBMITTED = 'submitted';

    public static function usesStageFlow(array $snapshot, ?array $progress = null): bool
    {
        $assessments = self::normalizeAssessments($snapshot);

        if ($assessments === []) {
            return false;
        }

        return collect($assessments)->contains(function (array $assessment) {
            return AssessmentStageConfig::isEnabled($assessment['stage_config'] ?? []);
        });
    }

    public static function buildInitial(array $snapshot): array
    {
        $stages = [];

        foreach (self::normalizeAssessments($snapshot) as $index => $assessment) {
            $config = AssessmentStageConfig::normalize(
                $assessment['stage_config'] ?? [],
                AssessmentStageConfig::defaultForAssessment(
                    $assessment['instrument_type'] ?? null,
                    $index
                )
            );
            $stageFieldIds = self::stageFieldIdsFromAssessment($assessment);
            $isLocked = AssessmentStageConfig::requiresManualOpening($config, $index);

            $stages[] = [
                'assessment_id' => (int) ($assessment['id'] ?? 0),
                'title' => trim((string) ($assessment['judul'] ?? '')) ?: 'Assessment '.($index + 1),
                'stage_index' => $index,
                'field_ids' => $stageFieldIds,
                'status' => $isLocked ? self::STATUS_LOCKED : self::STATUS_READY,
                'started_at' => null,
                'deadline_at' => null,
                'submitted_at' => null,
                'completion_mode' => null,
                'config' => $config,
            ];
        }

        return [
            'stage_flow_enabled' => self::usesStageFlow($snapshot),
            'current_stage_index' => self::firstActionableStageIndex(['stages' => $stages]) ?? 0,
            'stages' => $stages,
            'updated_at' => now()->toIso8601String(),
        ];
    }

    public static function normalize(?array $progress, array $snapshot): array
    {
        $initial = self::buildInitial($snapshot);

        if (! is_array($progress) || ! ($initial['stage_flow_enabled'] ?? false)) {
            return $initial;
        }

        $storedStages = collect($progress['stages'] ?? [])
            ->filter(fn ($stage) => is_array($stage))
            ->keyBy(fn ($stage) => (int) ($stage['stage_index'] ?? -1));
        $stages = [];

        foreach ($initial['stages'] as $stage) {
            $stageIndex = (int) ($stage['stage_index'] ?? 0);
            $storedStage = $storedStages->get($stageIndex, []);
            $status = self::normalizeStageStatus($storedStage['status'] ?? $stage['status']);

            $stages[] = [
                'assessment_id' => (int) ($stage['assessment_id'] ?? 0),
                'title' => $stage['title'],
                'stage_index' => $stageIndex,
                'field_ids' => self::normalizeFieldIds($stage['field_ids'] ?? []),
                'status' => $status,
                'started_at' => self::normalizeIsoDateTime($storedStage['started_at'] ?? null),
                'deadline_at' => self::normalizeIsoDateTime($storedStage['deadline_at'] ?? null),
                'submitted_at' => self::normalizeIsoDateTime($storedStage['submitted_at'] ?? null),
                'completion_mode' => filled($storedStage['completion_mode'] ?? null)
                    ? (string) $storedStage['completion_mode']
                    : null,
                'config' => AssessmentStageConfig::normalize(
                    is_array($storedStage['config'] ?? null) ? $storedStage['config'] : ($stage['config'] ?? []),
                    $stage['config'] ?? []
                ),
            ];
        }

        $normalized = [
            'stage_flow_enabled' => true,
            'current_stage_index' => self::resolveCurrentStageIndex(
                $stages,
                (int) ($progress['current_stage_index'] ?? -1)
            ),
            'stages' => $stages,
            'updated_at' => self::normalizeIsoDateTime($progress['updated_at'] ?? null) ?? now()->toIso8601String(),
        ];

        return self::unlockReadyStages($normalized);
    }

    public static function resolveCurrentStageIndex(array $stagesOrProgress, int $requested = -1): int
    {
        $stages = self::resolveStages($stagesOrProgress);

        if ($stages === []) {
            return 0;
        }

        if (isset($stages[$requested])) {
            return $requested;
        }

        $actionable = self::firstActionableStageIndex(['stages' => $stages]);

        return $actionable ?? 0;
    }

    public static function firstActionableStageIndex(array $progress): ?int
    {
        foreach (self::resolveStages($progress) as $stage) {
            if (in_array(
                $stage['status'] ?? null,
                [self::STATUS_READY, self::STATUS_DRAFT, self::STATUS_IN_PROGRESS],
                true
            )) {
                return (int) ($stage['stage_index'] ?? 0);
            }
        }

        return null;
    }

    public static function nextStageIndexAfterSubmission(array $progress): ?int
    {
        foreach (self::resolveStages($progress) as $stage) {
            if (($stage['status'] ?? null) !== self::STATUS_SUBMITTED) {
                return (int) ($stage['stage_index'] ?? 0);
            }
        }

        return null;
    }

    public static function canAccessStage(array $progress, int $stageIndex): bool
    {
        $stage = self::stage($progress, $stageIndex);

        if (! $stage) {
            return false;
        }

        return ($stage['status'] ?? null) !== self::STATUS_LOCKED;
    }

    public static function stage(array $progress, int $stageIndex): ?array
    {
        foreach (self::resolveStages($progress) as $stage) {
            if ((int) ($stage['stage_index'] ?? -1) === $stageIndex) {
                return $stage;
            }
        }

        return null;
    }

    public static function stageConfig(array $progress, int $stageIndex): array
    {
        $stage = self::stage($progress, $stageIndex);

        return AssessmentStageConfig::normalize(is_array($stage['config'] ?? null) ? $stage['config'] : []);
    }

    public static function stageFieldIds(array $progress, int $stageIndex): array
    {
        $stage = self::stage($progress, $stageIndex);

        return self::normalizeFieldIds($stage['field_ids'] ?? []);
    }

    public static function activeDeadlineAt(array $progress): ?Carbon
    {
        $currentStage = self::stage($progress, (int) ($progress['current_stage_index'] ?? 0));

        if (
            ! $currentStage
            || ! in_array($currentStage['status'] ?? null, [self::STATUS_IN_PROGRESS, self::STATUS_DRAFT], true)
        ) {
            return null;
        }

        return self::parseCarbon($currentStage['deadline_at'] ?? null);
    }

    public static function activeDurationMinutes(array $progress): ?int
    {
        $currentConfig = self::stageConfig($progress, (int) ($progress['current_stage_index'] ?? 0));

        return is_numeric($currentConfig['time_limit_minutes'] ?? null)
            ? (int) $currentConfig['time_limit_minutes']
            : null;
    }

    public static function markStageStarted(
        array $progress,
        int $stageIndex,
        CarbonInterface|string|null $startedAt = null
    ): array {
        $progress = self::unlockReadyStages($progress);
        $startedAtCarbon = self::parseCarbon($startedAt) ?: now();

        $progress['stages'] = collect(self::resolveStages($progress))
            ->map(function (array $stage) use ($stageIndex, $startedAtCarbon) {
                if ((int) ($stage['stage_index'] ?? -1) !== $stageIndex) {
                    return $stage;
                }

                if (($stage['status'] ?? null) === self::STATUS_SUBMITTED) {
                    return $stage;
                }

                $config = AssessmentStageConfig::normalize(is_array($stage['config'] ?? null) ? $stage['config'] : []);
                $deadlineAt = $stage['deadline_at'] ?? null;

                if (! $deadlineAt && is_numeric($config['time_limit_minutes'] ?? null)) {
                    $deadlineAt = $startedAtCarbon->copy()
                        ->addMinutes((int) $config['time_limit_minutes'])
                        ->toIso8601String();
                }

                $stage['status'] = self::STATUS_IN_PROGRESS;
                $stage['started_at'] = $stage['started_at'] ?: $startedAtCarbon->toIso8601String();
                $stage['deadline_at'] = $deadlineAt;

                return $stage;
            })
            ->values()
            ->all();

        $progress['current_stage_index'] = $stageIndex;
        $progress['updated_at'] = $startedAtCarbon->toIso8601String();

        return $progress;
    }

    public static function markStageSubmitted(
        array $progress,
        int $stageIndex,
        CarbonInterface|string|null $submittedAt = null,
        ?string $completionMode = 'manual'
    ): array {
        $submittedAtCarbon = self::parseCarbon($submittedAt) ?: now();

        $progress['stages'] = collect(self::resolveStages($progress))
            ->map(function (array $stage) use ($stageIndex, $submittedAtCarbon, $completionMode) {
                if ((int) ($stage['stage_index'] ?? -1) !== $stageIndex) {
                    return $stage;
                }

                $stage['status'] = self::STATUS_SUBMITTED;
                $stage['started_at'] = $stage['started_at'] ?: $submittedAtCarbon->toIso8601String();
                $stage['submitted_at'] = $submittedAtCarbon->toIso8601String();
                $stage['completion_mode'] = $completionMode;

                return $stage;
            })
            ->values()
            ->all();

        $progress['updated_at'] = $submittedAtCarbon->toIso8601String();
        $progress = self::unlockReadyStages($progress);
        $progress['current_stage_index'] = self::resolveCurrentStageIndex(
            $progress,
            self::nextStageIndexAfterSubmission($progress) ?? $stageIndex
        );

        return $progress;
    }

    public static function markStageDraft(
        array $progress,
        int $stageIndex,
        CarbonInterface|string|null $savedAt = null
    ): array {
        $savedAtCarbon = self::parseCarbon($savedAt) ?: now();

        $progress['stages'] = collect(self::resolveStages($progress))
            ->map(function (array $stage) use ($stageIndex, $savedAtCarbon) {
                if ((int) ($stage['stage_index'] ?? -1) !== $stageIndex) {
                    return $stage;
                }

                if (($stage['status'] ?? null) === self::STATUS_SUBMITTED) {
                    return $stage;
                }

                $stage['status'] = self::STATUS_DRAFT;
                $stage['started_at'] = $stage['started_at'] ?: $savedAtCarbon->toIso8601String();
                $stage['submitted_at'] = null;
                $stage['completion_mode'] = null;

                return $stage;
            })
            ->values()
            ->all();

        $progress['current_stage_index'] = $stageIndex;
        $progress['updated_at'] = $savedAtCarbon->toIso8601String();

        return $progress;
    }

    public static function reopenStage(
        array $progress,
        int $stageIndex,
        CarbonInterface|string|null $reopenedAt = null
    ): array {
        $reopenedAtCarbon = self::parseCarbon($reopenedAt) ?: now();

        $progress['stages'] = collect(self::resolveStages($progress))
            ->map(function (array $stage) use ($stageIndex) {
                $currentStageIndex = (int) ($stage['stage_index'] ?? -1);
                $status = $stage['status'] ?? self::STATUS_READY;

                if ($currentStageIndex === $stageIndex) {
                    $stage['status'] = self::STATUS_READY;
                    $stage['started_at'] = null;
                    $stage['deadline_at'] = null;
                    $stage['submitted_at'] = null;
                    $stage['completion_mode'] = null;

                    return $stage;
                }

                if ($currentStageIndex > $stageIndex && $status !== self::STATUS_SUBMITTED) {
                    $stage['started_at'] = null;
                    $stage['deadline_at'] = null;
                    $stage['submitted_at'] = null;
                    $stage['completion_mode'] = null;
                }

                return $stage;
            })
            ->values()
            ->all();

        $progress = self::synchronizeStageLocks($progress);
        $progress['current_stage_index'] = self::resolveCurrentStageIndex($progress, $stageIndex);
        $progress['updated_at'] = $reopenedAtCarbon->toIso8601String();

        return $progress;
    }

    public static function isAllSubmitted(array $progress): bool
    {
        $stages = self::resolveStages($progress);

        return $stages !== []
            && collect($stages)->every(fn (array $stage) => ($stage['status'] ?? null) === self::STATUS_SUBMITTED);
    }

    public static function lockReason(array $progress, int $stageIndex): ?string
    {
        $stage = self::stage($progress, $stageIndex);

        if (! $stage || ($stage['status'] ?? null) !== self::STATUS_LOCKED) {
            return null;
        }

        return 'Tahap ini masih dikunci admin. Lanjutkan setelah admin membuka tahap ini.';
    }

    private static function unlockReadyStages(array $progress): array
    {
        return self::synchronizeStageLocks($progress);
    }

    private static function synchronizeStageLocks(array $progress): array
    {
        $stages = collect(self::resolveStages($progress))
            ->values()
            ->all();

        foreach ($stages as $index => $stage) {
            $status = $stage['status'] ?? self::STATUS_READY;

            if ($status === self::STATUS_SUBMITTED) {
                continue;
            }

            $config = AssessmentStageConfig::normalize(is_array($stage['config'] ?? null) ? $stage['config'] : []);
            $requiresManualOpening = AssessmentStageConfig::requiresManualOpening($config, $index);

            if (! $requiresManualOpening) {
                if ($status === self::STATUS_LOCKED) {
                    $stages[$index]['status'] = self::STATUS_READY;
                }

                continue;
            }

            if (in_array($status, [self::STATUS_DRAFT, self::STATUS_IN_PROGRESS], true)) {
                continue;
            }

            $stages[$index]['status'] = self::STATUS_LOCKED;
            $stages[$index]['started_at'] = null;
            $stages[$index]['deadline_at'] = null;
            $stages[$index]['submitted_at'] = null;
            $stages[$index]['completion_mode'] = null;
        }

        $progress['stages'] = $stages;

        return $progress;
    }

    private static function normalizeAssessments(array $snapshot): array
    {
        return collect($snapshot['assessments'] ?? [])
            ->map(function ($assessment) {
                return is_array($assessment) ? $assessment : [];
            })
            ->values()
            ->all();
    }

    private static function stageFieldIdsFromAssessment(array $assessment): array
    {
        return collect($assessment['forms'] ?? [])
            ->flatMap(fn ($form) => is_array($form) ? ($form['fields'] ?? []) : [])
            ->map(fn ($field) => (int) (is_array($field) ? ($field['id'] ?? 0) : 0))
            ->filter(fn (int $fieldId) => $fieldId > 0)
            ->values()
            ->all();
    }

    private static function resolveStages(array $stagesOrProgress): array
    {
        $stages = $stagesOrProgress['stages'] ?? $stagesOrProgress;

        return collect($stages)
            ->filter(fn ($stage) => is_array($stage))
            ->values()
            ->all();
    }

    private static function normalizeFieldIds(mixed $fieldIds): array
    {
        return collect(is_array($fieldIds) ? $fieldIds : [$fieldIds])
            ->map(fn ($fieldId) => (int) $fieldId)
            ->filter(fn (int $fieldId) => $fieldId > 0)
            ->unique()
            ->values()
            ->all();
    }

    private static function normalizeStageStatus(mixed $status): string
    {
        $normalized = trim((string) ($status ?? self::STATUS_READY));

        return in_array(
            $normalized,
            [
                self::STATUS_LOCKED,
                self::STATUS_READY,
                self::STATUS_DRAFT,
                self::STATUS_IN_PROGRESS,
                self::STATUS_SUBMITTED,
            ],
            true
        ) ? $normalized : self::STATUS_READY;
    }

    private static function normalizeIsoDateTime(mixed $value): ?string
    {
        return self::parseCarbon($value)?->toIso8601String();
    }

    private static function parseCarbon(CarbonInterface|string|null $value): ?Carbon
    {
        if ($value instanceof CarbonInterface) {
            return Carbon::instance($value);
        }

        if (! filled($value)) {
            return null;
        }

        try {
            return Carbon::parse((string) $value);
        } catch (\Throwable $exception) {
            return null;
        }
    }
}
