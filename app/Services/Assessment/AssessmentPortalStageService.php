<?php

namespace App\Services\Assessment;

use App\Models\AssessmentAssignmentTarget;
use App\Models\AssessmentAttempt;
use App\Support\Assessment\AssessmentStageConfig;
use App\Support\Assessment\AssessmentStageProgress;
use Illuminate\Http\Request;

class AssessmentPortalStageService
{
    public function __construct(
        private readonly AssessmentAttemptService $attemptService
    ) {}

    public function resolveStartContext(
        Request $request,
        AssessmentAssignmentTarget $target,
        AssessmentAttempt $attempt
    ): array {
        if (! $this->usesStageFlow($target, $attempt)) {
            return [
                'uses_stage_flow' => false,
                'attempt' => $attempt,
                'stage_index' => null,
                'error' => null,
                'redirect_params' => ['id' => $target->id],
            ];
        }

        $stageIndex = $this->resolveRequestedStageIndex($request, $attempt);
        $progress = $this->resolveProgress($attempt);

        if (! AssessmentStageProgress::canAccessStage($progress, $stageIndex)) {
            return [
                'uses_stage_flow' => true,
                'attempt' => $attempt,
                'stage_index' => $stageIndex,
                'error' => AssessmentStageProgress::lockReason($progress, $stageIndex)
                    ?: 'Tahap yang diminta belum bisa diakses.',
                'redirect_params' => ['id' => $target->id],
            ];
        }

        $stage = AssessmentStageProgress::stage($progress, $stageIndex);

        if (($stage['status'] ?? null) !== AssessmentStageProgress::STATUS_SUBMITTED) {
            $attempt = $this->attemptService->markStageStarted($attempt, $stageIndex);
        }

        return [
            'uses_stage_flow' => true,
            'attempt' => $attempt,
            'stage_index' => $stageIndex,
            'error' => null,
            'redirect_params' => ['id' => $target->id, 'stage' => $stageIndex],
        ];
    }

    public function resolveShowState(
        Request $request,
        AssessmentAssignmentTarget $target,
        AssessmentAttempt $attempt
    ): array {
        $stageFlowEnabled = $this->usesStageFlow($target, $attempt);
        $renderStageOverview = $stageFlowEnabled && ! $this->hasRequestedStageSelection($request);
        $currentStageIndex = null;

        if (! $stageFlowEnabled) {
            return [
                'attempt' => $attempt,
                'stage_flow_enabled' => false,
                'render_stage_overview' => false,
                'current_stage_index' => null,
            ];
        }

        $progress = $this->resolveProgress($attempt);

        if ($renderStageOverview) {
            $currentStageIndex = AssessmentStageProgress::resolveCurrentStageIndex($progress);
        } else {
            $stageIndex = $this->resolveRequestedStageIndex($request, $attempt);

            if (! AssessmentStageProgress::canAccessStage($progress, $stageIndex)) {
                $stageIndex = AssessmentStageProgress::resolveCurrentStageIndex($progress);
            }

            $stage = AssessmentStageProgress::stage($progress, $stageIndex);
            $stageConfig = AssessmentStageProgress::stageConfig($progress, $stageIndex);

            if (
                $stage
                && (
                    ($stage['status'] ?? null) === AssessmentStageProgress::STATUS_DRAFT
                    || (
                        ($stage['status'] ?? null) === AssessmentStageProgress::STATUS_READY
                        && ($stageConfig['entry_mode'] ?? null) === AssessmentStageConfig::ENTRY_DIRECT
                    )
                )
            ) {
                $attempt = $this->attemptService->markStageStarted($attempt, $stageIndex);
            }

            $currentStageIndex = $stageIndex;
        }

        return [
            'attempt' => $attempt,
            'stage_flow_enabled' => true,
            'render_stage_overview' => $renderStageOverview,
            'current_stage_index' => $currentStageIndex,
        ];
    }

    public function resolveMutationContext(
        Request $request,
        AssessmentAssignmentTarget $target,
        AssessmentAttempt $attempt
    ): array {
        if (! $this->usesStageFlow($target, $attempt)) {
            return [
                'uses_stage_flow' => false,
                'attempt' => $attempt,
                'stage_index' => null,
                'error' => null,
                'redirect_params' => ['id' => $target->id],
            ];
        }

        $stageIndex = $this->resolveRequestedStageIndex($request, $attempt);
        $progress = $this->resolveProgress($attempt);

        if (! AssessmentStageProgress::canAccessStage($progress, $stageIndex)) {
            return [
                'uses_stage_flow' => true,
                'attempt' => $attempt,
                'stage_index' => $stageIndex,
                'error' => AssessmentStageProgress::lockReason($progress, $stageIndex)
                    ?: 'Tahap yang diminta belum tersedia.',
                'redirect_params' => ['id' => $target->id],
            ];
        }

        $stage = AssessmentStageProgress::stage($progress, $stageIndex);
        $stageConfig = AssessmentStageProgress::stageConfig($progress, $stageIndex);

        if (
            $stage
            && ($stage['status'] ?? null) === AssessmentStageProgress::STATUS_READY
            && ($stageConfig['entry_mode'] ?? null) === AssessmentStageConfig::ENTRY_START_BUTTON
        ) {
            return [
                'uses_stage_flow' => true,
                'attempt' => $attempt,
                'stage_index' => $stageIndex,
                'error' => 'Klik tombol Mulai pada tahap ini terlebih dahulu.',
                'redirect_params' => ['id' => $target->id, 'stage' => $stageIndex],
            ];
        }

        if (
            $stage
            && ($stage['status'] ?? null) === AssessmentStageProgress::STATUS_READY
            && ($stageConfig['entry_mode'] ?? null) === AssessmentStageConfig::ENTRY_DIRECT
        ) {
            $attempt = $this->attemptService->markStageStarted($attempt, $stageIndex);
        }

        return [
            'uses_stage_flow' => true,
            'attempt' => $attempt,
            'stage_index' => $stageIndex,
            'error' => null,
            'redirect_params' => ['id' => $target->id, 'stage' => $stageIndex],
        ];
    }

    public function resolveStageIndex(
        Request $request,
        AssessmentAssignmentTarget $target,
        AssessmentAttempt $attempt
    ): ?int {
        return $this->usesStageFlow($target, $attempt)
            ? $this->resolveRequestedStageIndex($request, $attempt)
            : null;
    }

    public function usesStageFlow(
        AssessmentAssignmentTarget $target,
        ?AssessmentAttempt $attempt = null
    ): bool {
        $attempt = $attempt ?: $target->attempt;

        if ($attempt) {
            $snapshot = is_array($attempt->structure_snapshot ?? null) ? $attempt->structure_snapshot : [];
            $progress = is_array($attempt->progress_snapshot ?? null) ? $attempt->progress_snapshot : null;

            if (AssessmentStageProgress::usesStageFlow($snapshot, $progress)) {
                return true;
            }
        }

        $target->loadMissing('assignment.assessments');

        return $target->assignment->assessments
            ->values()
            ->contains(function ($assessment, int $index) {
                return AssessmentStageConfig::isEnabled(
                    AssessmentStageConfig::normalize(
                        is_array($assessment->pivot?->stage_config ?? null) ? $assessment->pivot->stage_config : [],
                        AssessmentStageConfig::defaultForAssessment($assessment->instrument_type, $index)
                    )
                );
            });
    }

    public function resolveProgress(AssessmentAttempt $attempt): array
    {
        return AssessmentStageProgress::normalize(
            $attempt->progress_snapshot,
            is_array($attempt->structure_snapshot ?? null) ? $attempt->structure_snapshot : []
        );
    }

    public function resolveRequestedStageIndex(Request $request, AssessmentAttempt $attempt): int
    {
        $progress = $this->resolveProgress($attempt);
        $rawStageIndex = $request->query(
            'stage',
            $request->input('stage_index', $request->input('active_assessment_index', -1))
        );

        return AssessmentStageProgress::resolveCurrentStageIndex(
            $progress,
            is_numeric($rawStageIndex) ? (int) $rawStageIndex : -1
        );
    }

    public function hasRequestedStageSelection(Request $request): bool
    {
        return $request->query->has('stage');
    }
}
