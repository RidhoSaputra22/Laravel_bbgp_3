<?php

namespace App\Enum;

enum AssessmentKetenagaanType: string
{
    case TENAGA_PENDIDIK = 'tenaga_pendidik';
    case TENAGA_KEPENDIDIKAN = 'tenaga_kependidikan';
    case STAKEHOLDER = 'stakeholder';

    public function label(): string
    {
        return match ($this) {
            self::TENAGA_PENDIDIK => 'Tenaga Pendidik',
            self::TENAGA_KEPENDIDIKAN => 'Tenaga Kependidikan',
            self::STAKEHOLDER => 'Stakeholder',
        };
    }

    public function guruValue(): string
    {
        return $this->label();
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::TENAGA_PENDIDIK => 'primary',
            self::TENAGA_KEPENDIDIKAN => 'info',
            self::STAKEHOLDER => 'warning',
        };
    }

    public function iconClass(): string
    {
        return match ($this) {
            self::TENAGA_PENDIDIK => 'fas fa-chalkboard-teacher',
            self::TENAGA_KEPENDIDIKAN => 'fas fa-school',
            self::STAKEHOLDER => 'fas fa-layer-group',
        };
    }

    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    public static function tryFromMixed(mixed $value): ?self
    {
        if ($value instanceof self) {
            return $value;
        }

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return self::tryFrom(trim($value));
    }
}
