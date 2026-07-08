<?php

namespace App\Services\Assessment;

use App\Models\AssessmentAttempt;
use App\Support\Assessment\AssessmentSecurityConfig;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AssessmentAttemptSecurityService
{
    public function __construct(
        private readonly AssessmentAttemptService $attemptService
    ) {}

    public function buildClientPayload(AssessmentAttempt $attempt): array
    {
        $config = $this->resolveConfig($attempt);
        $seriousViolationCount = (int) ($attempt->serious_violation_count ?? 0);
        $warningViolationCount = (int) ($attempt->warning_violation_count ?? 0);
        $maxSeriousViolations = (int) ($config['max_serious_violations'] ?? 0);

        return [
            'enabled' => (bool) ($config['enabled'] ?? false),
            'requireFullscreen' => (bool) ($config['require_fullscreen'] ?? false),
            'maxSeriousViolations' => $maxSeriousViolations,
            'temporaryLockSeconds' => (int) ($config['temporary_lock_seconds'] ?? 0),
            'fullscreenGraceSeconds' => (int) ($config['fullscreen_grace_seconds'] ?? 0),
            'seriousViolationCount' => $seriousViolationCount,
            'warningViolationCount' => $warningViolationCount,
            'remainingSeriousChances' => max(0, $maxSeriousViolations - $seriousViolationCount),
            'disqualified' => $attempt->disqualified_at !== null,
            'disqualifiedAt' => optional($attempt->disqualified_at)->toIso8601String(),
            'disqualificationReason' => $attempt->disqualification_reason,
            'attemptId' => (int) $attempt->id,
        ];
    }

    public function resolveConfig(AssessmentAttempt $attempt): array
    {
        $snapshotConfig = $attempt->security_config_snapshot;

        if (is_array($snapshotConfig) && $snapshotConfig !== []) {
            return AssessmentSecurityConfig::normalize($snapshotConfig);
        }

        $attempt->loadMissing('target.assignment');

        return AssessmentSecurityConfig::normalize(
            $attempt->target?->assignment?->security_config
        );
    }

    public function hasReachedSeriousLimit(AssessmentAttempt $attempt): bool
    {
        $config = $this->resolveConfig($attempt);

        if (! ($config['enabled'] ?? false)) {
            return false;
        }

        return (int) ($attempt->serious_violation_count ?? 0) >= (int) ($config['max_serious_violations'] ?? 0);
    }

    public function registerViolation(AssessmentAttempt $attempt, array $payload): array
    {
        $attempt->loadMissing('target.assignment');
        $config = $this->resolveConfig($attempt);

        if (! ($config['enabled'] ?? false)) {
            return array_merge($this->buildClientPayload($attempt), [
                'status' => 'disabled',
                'requires_disqualification' => false,
                'reason' => null,
            ]);
        }

        if ($attempt->status === 'submitted') {
            return array_merge($this->buildClientPayload($attempt), [
                'status' => 'submitted',
                'requires_disqualification' => false,
                'reason' => $attempt->disqualification_reason,
            ]);
        }

        DB::transaction(function () use (&$attempt, $payload) {
            /** @var \App\Models\AssessmentAttempt $lockedAttempt */
            $lockedAttempt = AssessmentAttempt::query()
                ->with('target.assignment')
                ->whereKey($attempt->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->storeSecurityEvent($lockedAttempt, $payload, true);

            $attempt = $lockedAttempt;
        });

        $attempt = $attempt->fresh(['target.assignment', 'securityEvents']);

        $config = $this->resolveConfig($attempt);
        $state = $this->buildClientPayload($attempt);
        $requiresDisqualification = $state['seriousViolationCount'] >= (int) ($config['max_serious_violations'] ?? 0);

        return array_merge($state, [
            'status' => $requiresDisqualification ? 'disqualify_required' : 'recorded',
            'requires_disqualification' => $requiresDisqualification,
            'reason' => $requiresDisqualification
                ? 'Anda didiskualifikasi karena telah mencapai batas pelanggaran serius selama ujian.'
                : null,
        ]);
    }

    public function disqualify(
        AssessmentAttempt $attempt,
        array $payload,
        array $answers = [],
        array $files = [],
        ?array $flaggedFieldIds = null,
        ?array $fieldIds = null
    ): AssessmentAttempt {
        $reason = trim((string) ($payload['reason'] ?? ''));
        $reason = $reason !== ''
            ? $reason
            : 'Assessment dihentikan oleh sistem karena terdeteksi pelanggaran aturan ujian.';
        $recordTrigger = (bool) ($payload['record_trigger'] ?? false);
        $triggerEvent = $payload['trigger_event'] ?? null;

        return DB::transaction(function () use (
            $attempt,
            $reason,
            $recordTrigger,
            $triggerEvent,
            $answers,
            $files,
            $flaggedFieldIds,
            $fieldIds
        ) {
            /** @var \App\Models\AssessmentAttempt $lockedAttempt */
            $lockedAttempt = AssessmentAttempt::query()
                ->with('target.assignment')
                ->whereKey($attempt->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($recordTrigger && is_array($triggerEvent)) {
                $this->storeSecurityEvent($lockedAttempt, $triggerEvent, true);
            }

            if ($lockedAttempt->status !== 'submitted') {
                $lockedAttempt = $this->attemptService->submitDisqualified(
                    $lockedAttempt,
                    $answers,
                    $files,
                    $reason,
                    $flaggedFieldIds,
                    $fieldIds
                );
            } elseif (! $lockedAttempt->disqualified_at) {
                $lockedAttempt->forceFill([
                    'disqualified_at' => now(),
                    'disqualification_reason' => $reason,
                ])->save();
            }

            $lockedAttempt->securityEvents()->create([
                'event_key' => 'security_disqualification',
                'violation_type' => 'system',
                'lock_mode' => 'disqualified',
                'message' => $reason,
                'counts_toward_disqualify' => false,
                'client_occurred_at' => $this->parseClientOccurredAt($payload['client_occurred_at'] ?? null),
                'ip_address' => $payload['ip_address'] ?? null,
                'user_agent' => $payload['user_agent'] ?? null,
                'metadata' => is_array($payload['metadata'] ?? null) ? $payload['metadata'] : [],
            ]);

            return $lockedAttempt->fresh([
                'answers',
                'securityEvents',
                'target.assignment.assessments.forms.fields',
                'target.assignment.combination',
                'target.combination',
                'target.session',
                'target.guru',
            ]);
        });
    }

    private function storeSecurityEvent(
        AssessmentAttempt $attempt,
        array $payload,
        bool $incrementCounters
    ): void {
        $violationType = $this->normalizeViolationType($payload['type'] ?? null);
        $countsTowardDisqualify = $violationType === 'intentional';
        $seriousIncrement = $violationType === 'intentional' ? 1 : 0;
        $warningIncrement = $violationType === 'unintentional' ? 1 : 0;
        $metadata = is_array($payload['metadata'] ?? null) ? $payload['metadata'] : [];

        $attempt->securityEvents()->create([
            'event_key' => (string) ($payload['event_key'] ?? 'security_violation'),
            'violation_type' => $violationType,
            'lock_mode' => filled($payload['mode'] ?? null) ? (string) $payload['mode'] : null,
            'message' => trim((string) ($payload['message'] ?? 'Pelanggaran keamanan ujian terdeteksi.')),
            'counts_toward_disqualify' => $countsTowardDisqualify,
            'client_occurred_at' => $this->parseClientOccurredAt($payload['client_occurred_at'] ?? null),
            'ip_address' => $payload['ip_address'] ?? null,
            'user_agent' => $payload['user_agent'] ?? null,
            'metadata' => $metadata,
        ]);

        if (! $incrementCounters) {
            return;
        }

        $attempt->forceFill([
            'serious_violation_count' => max(0, (int) ($attempt->serious_violation_count ?? 0))
                + $seriousIncrement,
            'warning_violation_count' => max(0, (int) ($attempt->warning_violation_count ?? 0))
                + $warningIncrement,
            'last_violation_at' => now(),
        ])->save();
    }

    private function normalizeViolationType(mixed $value): string
    {
        $type = strtolower(trim((string) ($value ?? 'unintentional')));

        return in_array($type, ['intentional', 'unintentional', 'system'], true)
            ? $type
            : 'unintentional';
    }

    private function parseClientOccurredAt(mixed $value): ?Carbon
    {
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
