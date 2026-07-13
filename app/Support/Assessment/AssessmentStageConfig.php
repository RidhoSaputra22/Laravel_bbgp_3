<?php

namespace App\Support\Assessment;

use App\Enum\AssessmentInstrumentType;

class AssessmentStageConfig
{
    public const ENTRY_DIRECT = 'direct';

    public const ENTRY_START_BUTTON = 'start_button';

    public const FINALIZE_MANUAL = 'manual';

    public const FINALIZE_AUTO = 'auto';

    private const DEFAULTS = [
        'enabled' => false,
        'entry_mode' => self::ENTRY_DIRECT,
        'allow_draft' => false,
        'finalize_mode' => self::FINALIZE_MANUAL,
        'lock_until_previous_stages_completed' => false,
        'time_limit_minutes' => null,
        'security' => [
            'enabled' => false,
            'require_fullscreen' => false,
            'max_serious_violations' => 3,
            'temporary_lock_seconds' => 2,
            'fullscreen_grace_seconds' => 10,
        ],
    ];

    public static function defaults(): array
    {
        return self::normalize(self::DEFAULTS);
    }

    public static function defaultForAssessment(mixed $instrumentType, int $stageIndex = 0): array
    {
        $resolvedType = AssessmentInstrumentType::tryFromMixed($instrumentType);

        if ($resolvedType === AssessmentInstrumentType::PORTOFOLIO) {
            return self::normalize([
                'enabled' => true,
                'entry_mode' => self::ENTRY_DIRECT,
                'allow_draft' => true,
                'finalize_mode' => self::FINALIZE_MANUAL,
                'lock_until_previous_stages_completed' => false,
                'time_limit_minutes' => null,
                'security' => [
                    'enabled' => false,
                    'require_fullscreen' => false,
                ],
            ]);
        }

        if ($resolvedType === AssessmentInstrumentType::STUDI_KASUS) {
            return self::normalize([
                'enabled' => true,
                'entry_mode' => self::ENTRY_START_BUTTON,
                'allow_draft' => false,
                'finalize_mode' => self::FINALIZE_AUTO,
                'lock_until_previous_stages_completed' => false,
                'time_limit_minutes' => null,
                'security' => [
                    'enabled' => true,
                    'require_fullscreen' => true,
                ],
            ]);
        }

        if ($resolvedType === AssessmentInstrumentType::PILIHAN_GANDA_KOMPLEKS) {
            return self::normalize([
                'enabled' => true,
                'entry_mode' => self::ENTRY_DIRECT,
                'allow_draft' => false,
                'finalize_mode' => self::FINALIZE_AUTO,
                'lock_until_previous_stages_completed' => $stageIndex > 0,
                'time_limit_minutes' => 90,
                'security' => [
                    'enabled' => true,
                    'require_fullscreen' => true,
                ],
            ]);
        }

        return self::normalize([
            'enabled' => $stageIndex < 3,
        ]);
    }

    public static function normalize(?array $config = null, ?array $fallback = null): array
    {
        $base = is_array($fallback) ? $fallback : self::DEFAULTS;
        $config = is_array($config) ? $config : [];

        return [
            'enabled' => self::toBool($config['enabled'] ?? $base['enabled'] ?? self::DEFAULTS['enabled']),
            'entry_mode' => self::normalizeEntryMode($config['entry_mode'] ?? $base['entry_mode'] ?? null),
            'allow_draft' => self::toBool(
                $config['allow_draft'] ?? $base['allow_draft'] ?? self::DEFAULTS['allow_draft']
            ),
            'finalize_mode' => self::normalizeFinalizeMode(
                $config['finalize_mode'] ?? $base['finalize_mode'] ?? null
            ),
            'lock_until_previous_stages_completed' => self::toBool(
                $config['lock_until_previous_stages_completed']
                    ?? $base['lock_until_previous_stages_completed']
                    ?? self::DEFAULTS['lock_until_previous_stages_completed']
            ),
            'time_limit_minutes' => self::toNullableInt(
                $config['time_limit_minutes'] ?? $base['time_limit_minutes'] ?? null,
                1,
                600
            ),
            'security' => AssessmentSecurityConfig::normalize(
                array_merge(
                    self::DEFAULTS['security'],
                    is_array($base['security'] ?? null) ? $base['security'] : [],
                    is_array($config['security'] ?? null) ? $config['security'] : [],
                )
            ),
        ];
    }

    public static function isEnabled(?array $config = null): bool
    {
        return (bool) (self::normalize($config)['enabled'] ?? false);
    }

    public static function requiresManualOpening(?array $config = null, int $stageIndex = 0): bool
    {
        if ($stageIndex < 1) {
            return false;
        }

        return (bool) (self::normalize($config)['lock_until_previous_stages_completed'] ?? false);
    }

    public static function markOpenedByAdmin(?array $config = null, ?array $fallback = null): array
    {
        $normalized = self::normalize($config, $fallback);
        $normalized['lock_until_previous_stages_completed'] = false;

        return $normalized;
    }

    public static function entryModeOptions(): array
    {
        return [
            self::ENTRY_DIRECT => 'Langsung isi',
            self::ENTRY_START_BUTTON => 'Tombol mulai',
        ];
    }

    public static function finalizeModeOptions(): array
    {
        return [
            self::FINALIZE_MANUAL => 'Manual / simpan permanen',
            self::FINALIZE_AUTO => 'Auto submit saat selesai',
        ];
    }

    private static function normalizeEntryMode(mixed $value): string
    {
        $normalized = trim((string) ($value ?? self::ENTRY_DIRECT));

        return in_array($normalized, [self::ENTRY_DIRECT, self::ENTRY_START_BUTTON], true)
            ? $normalized
            : self::ENTRY_DIRECT;
    }

    private static function normalizeFinalizeMode(mixed $value): string
    {
        $normalized = trim((string) ($value ?? self::FINALIZE_MANUAL));

        return in_array($normalized, [self::FINALIZE_MANUAL, self::FINALIZE_AUTO], true)
            ? $normalized
            : self::FINALIZE_MANUAL;
    }

    private static function toBool(mixed $value, bool $default = false): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));

            if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
                return true;
            }

            if (in_array($normalized, ['0', 'false', 'no', 'off', ''], true)) {
                return false;
            }
        }

        return $default;
    }

    private static function toNullableInt(mixed $value, int $min, int $max): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        return max($min, min($max, (int) $value));
    }
}
