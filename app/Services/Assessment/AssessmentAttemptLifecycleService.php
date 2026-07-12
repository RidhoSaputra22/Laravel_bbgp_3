<?php

namespace App\Services\Assessment;

use App\Models\AssessmentAssignmentTarget;
use App\Models\AssessmentAttempt;
use App\Models\AssessmentFormField;
use App\Support\Assessment\AssessmentSecurityConfig;
use App\Support\Assessment\AssessmentTargetTiming;
use Illuminate\Support\Collection;

class AssessmentAttemptLifecycleService
{
    public function __construct(
        private readonly AssessmentQuestionRandomizerService $randomizer,
        private readonly AssessmentAttemptService $attemptService
    ) {}

    public function ensureAttempt(
        AssessmentAssignmentTarget $target,
        bool $markStarted = true
    ): AssessmentAttempt {
        $attempt = $target->attempt;
        $now = now();
        $targetStartedAt = $target->started_at;
        $shouldCarryStartedAt = $markStarted || (bool) $targetStartedAt;
        $resolvedStartedAt = $markStarted ? $now : $targetStartedAt;
        $target->loadMissing('assignment');
        $securityConfigSnapshot = AssessmentSecurityConfig::normalize(
            $target->assignment?->security_config
        );

        if (! $attempt) {
            $snapshot = $this->randomizer->buildSnapshot($target);
            $target->setRelation('attempt', null);
            $initialDeadlineAt = $resolvedStartedAt
                ? AssessmentTargetTiming::resolveDeadlineAt($target, $resolvedStartedAt->copy())
                : null;

            $attempt = $target->attempt()->create([
                'status' => $shouldCarryStartedAt ? 'in_progress' : 'draft',
                'structure_snapshot' => $snapshot,
                'security_config_snapshot' => $securityConfigSnapshot,
                'total_questions' => (int) data_get($snapshot, 'meta.total_questions', 0),
                'required_questions' => (int) data_get($snapshot, 'meta.required_questions', 0),
                'started_at' => $resolvedStartedAt,
                'deadline_at' => $initialDeadlineAt,
                'last_answered_at' => $shouldCarryStartedAt ? $now : null,
            ]);
        } else {
            if (empty($attempt->structure_snapshot)) {
                $snapshot = $this->randomizer->buildSnapshot($target);

                $attempt->forceFill([
                    'structure_snapshot' => $snapshot,
                    'security_config_snapshot' => $attempt->security_config_snapshot ?: $securityConfigSnapshot,
                    'total_questions' => (int) data_get($snapshot, 'meta.total_questions', 0),
                    'required_questions' => (int) data_get($snapshot, 'meta.required_questions', 0),
                ])->save();
            } elseif (empty($attempt->security_config_snapshot)) {
                $attempt->forceFill([
                    'security_config_snapshot' => $securityConfigSnapshot,
                ])->save();
            }

            if ($shouldCarryStartedAt && $attempt->status !== 'submitted') {
                $attempt->forceFill([
                    'status' => 'in_progress',
                    'started_at' => $attempt->started_at ?: $resolvedStartedAt,
                ])->save();
            }
        }

        $attempt = $this->syncSnapshotFieldMetadata($attempt);

        if ($shouldCarryStartedAt) {
            $target->setRelation('attempt', $attempt);
            $startedAt = $attempt->started_at ?: $target->started_at ?: $resolvedStartedAt ?: $now;
            $deadlineAt = $attempt->deadline_at
                ?: $target->deadline_at
                ?: AssessmentTargetTiming::resolveDeadlineAt($target, $startedAt->copy());

            if ($attempt->status !== 'submitted') {
                $attempt->forceFill([
                    'status' => 'in_progress',
                    'started_at' => $startedAt,
                    'deadline_at' => $deadlineAt,
                ])->save();
            }

            $target->forceFill([
                'status' => $target->status === 'selesai' ? 'selesai' : 'dikerjakan',
                'started_at' => $target->started_at ?: $startedAt,
                'deadline_at' => $target->deadline_at ?: $deadlineAt,
            ])->save();
        }

        return $attempt->fresh([
            'answers',
            'target.assignment.assessments.forms.fields',
            'target.assignment.combination',
            'target.combination',
            'target.session',
            'target.guru',
        ]);
    }

    public function syncExpiredTarget(AssessmentAssignmentTarget $target): AssessmentAssignmentTarget
    {
        if (! $this->shouldAutoFinalize($target)) {
            return $target;
        }

        $attempt = $this->ensureAttempt($target, false);
        $attempt = $this->attemptService->submitExpired($attempt);

        return $attempt->target->load([
            'assignment.assessments.forms.fields',
            'assignment.combination',
            'combination',
            'session',
            'attempt.answers',
            'guru',
        ]);
    }

    public function syncExpiredTargets(Collection $targets): Collection
    {
        return $targets->map(fn (AssessmentAssignmentTarget $target) => $this->syncExpiredTarget($target));
    }

    public function isPastDeadline(AssessmentAssignmentTarget $target): bool
    {
        $deadlineAt = AssessmentTargetTiming::resolveDeadlineAt($target);

        return $deadlineAt ? now()->greaterThanOrEqualTo($deadlineAt) : false;
    }

    private function shouldAutoFinalize(AssessmentAssignmentTarget $target): bool
    {
        if ($target->status === 'dibatalkan') {
            return false;
        }

        if ($target->attempt && $target->attempt->status === 'submitted') {
            return false;
        }

        if (! $target->started_at && ! optional($target->attempt)->started_at) {
            return false;
        }

        return $this->isPastDeadline($target);
    }

    private function syncSnapshotFieldMetadata(AssessmentAttempt $attempt): AssessmentAttempt
    {
        $snapshot = $attempt->structure_snapshot ?? [];
        $fieldIds = collect(data_get($snapshot, 'assessments', []))
            ->flatMap(fn ($assessment) => is_array($assessment) ? ($assessment['forms'] ?? []) : [])
            ->flatMap(fn ($form) => is_array($form) ? ($form['fields'] ?? []) : [])
            ->map(fn ($field) => (int) (is_array($field) ? ($field['id'] ?? 0) : 0))
            ->filter(fn ($fieldId) => $fieldId > 0)
            ->unique()
            ->values()
            ->all();

        if ($fieldIds === []) {
            return $attempt;
        }

        $fieldMetadata = AssessmentFormField::query()
            ->whereIn('id', $fieldIds)
            ->get(['id', 'autofill_source', 'lookup_source', 'validasi'])
            ->keyBy('id');

        if ($fieldMetadata->isEmpty()) {
            return $attempt;
        }

        $wasUpdated = false;
        $snapshot['assessments'] = collect(data_get($snapshot, 'assessments', []))
            ->map(function ($assessment) use ($fieldMetadata, &$wasUpdated) {
                if (! is_array($assessment)) {
                    return $assessment;
                }

                $assessment['forms'] = collect($assessment['forms'] ?? [])
                    ->map(function ($form) use ($fieldMetadata, &$wasUpdated) {
                        if (! is_array($form)) {
                            return $form;
                        }

                        $form['fields'] = collect($form['fields'] ?? [])
                            ->map(function ($field) use ($fieldMetadata, &$wasUpdated) {
                                if (! is_array($field)) {
                                    return $field;
                                }

                                $sourceField = $fieldMetadata->get((int) ($field['id'] ?? 0));

                                if (! $sourceField) {
                                    return $field;
                                }

                                if (($field['autofill_source'] ?? null) !== $sourceField->autofill_source) {
                                    $field['autofill_source'] = $sourceField->autofill_source;
                                    $wasUpdated = true;
                                }

                                if (($field['lookup_source'] ?? null) !== $sourceField->lookup_source) {
                                    $field['lookup_source'] = $sourceField->lookup_source;
                                    $wasUpdated = true;
                                }

                                $currentAllowOtherInput = (bool) data_get($field, 'validasi.allow_other_input', false);
                                $sourceAllowOtherInput = (bool) data_get($sourceField->validasi ?? [], 'allow_other_input', false);

                                if ($currentAllowOtherInput !== $sourceAllowOtherInput) {
                                    data_set($field, 'validasi.allow_other_input', $sourceAllowOtherInput);
                                    $wasUpdated = true;
                                }

                                return $field;
                            })
                            ->all();

                        return $form;
                    })
                    ->all();

                return $assessment;
            })
            ->all();

        if (! $wasUpdated) {
            return $attempt;
        }

        $attempt->forceFill([
            'structure_snapshot' => $snapshot,
        ])->save();

        $attempt->structure_snapshot = $snapshot;

        return $attempt;
    }
}
