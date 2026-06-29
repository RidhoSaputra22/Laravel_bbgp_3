<?php

namespace App\Enum;

enum KompetensiGuru: string
{
    case PEDAGOGIK = 'pedagogik';
    case KEPRIBADIAN = 'kepribadian';
    case SOSIAL = 'sosial';
    case PROFESIONAL = 'profesional';

    public function label(): string
    {
        return match ($this) {
            self::PEDAGOGIK => 'Pedagogik',
            self::KEPRIBADIAN => 'Kepribadian',
            self::SOSIAL => 'Sosial',
            self::PROFESIONAL => 'Profesional',
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
