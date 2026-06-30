<?php

namespace App\Support\Assessment;

class AssessmentSecurityConfig
{
    private const DEFAULTS = [
        'enabled' => true,
        'require_fullscreen' => true,
        'max_serious_violations' => 3,
        'temporary_lock_seconds' => 2,
        'fullscreen_grace_seconds' => 10,
    ];

    public static function defaults(): array
    {
        return self::DEFAULTS;
    }

    public static function normalize(?array $config = null): array
    {
        $config = is_array($config) ? $config : [];

        return [
            'enabled' => self::toBool($config['enabled'] ?? self::DEFAULTS['enabled'], self::DEFAULTS['enabled']),
            'require_fullscreen' => self::toBool(
                $config['require_fullscreen'] ?? self::DEFAULTS['require_fullscreen'],
                self::DEFAULTS['require_fullscreen']
            ),
            'max_serious_violations' => self::toInt(
                $config['max_serious_violations'] ?? self::DEFAULTS['max_serious_violations'],
                self::DEFAULTS['max_serious_violations'],
                1,
                10
            ),
            'temporary_lock_seconds' => self::toInt(
                $config['temporary_lock_seconds'] ?? self::DEFAULTS['temporary_lock_seconds'],
                self::DEFAULTS['temporary_lock_seconds'],
                1,
                30
            ),
            'fullscreen_grace_seconds' => self::toInt(
                $config['fullscreen_grace_seconds'] ?? self::DEFAULTS['fullscreen_grace_seconds'],
                self::DEFAULTS['fullscreen_grace_seconds'],
                3,
                60
            ),
        ];
    }

    public static function fromRequest(array $payload): array
    {
        return self::normalize([
            'enabled' => $payload['security_enabled'] ?? self::DEFAULTS['enabled'],
            'require_fullscreen' => $payload['security_require_fullscreen'] ?? self::DEFAULTS['require_fullscreen'],
            'max_serious_violations' => $payload['security_max_serious_violations']
                ?? self::DEFAULTS['max_serious_violations'],
            'temporary_lock_seconds' => $payload['security_temporary_lock_seconds']
                ?? self::DEFAULTS['temporary_lock_seconds'],
            'fullscreen_grace_seconds' => $payload['security_fullscreen_grace_seconds']
                ?? self::DEFAULTS['fullscreen_grace_seconds'],
        ]);
    }

    private static function toBool(mixed $value, bool $default): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        if (is_string($value)) {
            $normalizedValue = strtolower(trim($value));

            if (in_array($normalizedValue, ['1', 'true', 'yes', 'on'], true)) {
                return true;
            }

            if (in_array($normalizedValue, ['0', 'false', 'no', 'off', ''], true)) {
                return false;
            }
        }

        return $default;
    }

    private static function toInt(mixed $value, int $default, int $min, int $max): int
    {
        if (! is_numeric($value)) {
            return $default;
        }

        return max($min, min($max, (int) $value));
    }
}
