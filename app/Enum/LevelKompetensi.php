<?php

namespace App\Enum;

enum LevelKompetensi: int
{
    case LEVEL_1 = 1;
    case LEVEL_2 = 2;
    case LEVEL_3 = 3;
    case LEVEL_4 = 4;
    case LEVEL_5 = 5;

    public function label(): string
    {
        return match ($this) {
            self::LEVEL_1 => 'Level 1: Paham',
            self::LEVEL_2 => 'Level 2: Dasar',
            self::LEVEL_3 => 'Level 3: Menengah',
            self::LEVEL_4 => 'Level 4: Mumpuni',
            self::LEVEL_5 => 'Level 5: Ahli',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::LEVEL_1 => 'Paham',
            self::LEVEL_2 => 'Dasar',
            self::LEVEL_3 => 'Menengah',
            self::LEVEL_4 => 'Mumpuni',
            self::LEVEL_5 => 'Ahli',
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

    public static function values(): array
    {
        return array_map(
            static fn (self $case) => $case->value,
            self::cases()
        );
    }

    public static function tryFromMixed(mixed $value): ?self
    {
        if ($value instanceof self) {
            return $value;
        }

        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        return self::tryFrom((int) $value);
    }

    public static function tryFromSequence(int $sequence): ?self
    {
        return self::tryFrom($sequence);
    }

    public static function tryFromChoiceCode(?string $code): ?self
    {
        $normalizedCode = strtoupper(trim((string) $code));

        return match ($normalizedCode) {
            'A' => self::LEVEL_1,
            'B' => self::LEVEL_2,
            'C' => self::LEVEL_3,
            'D' => self::LEVEL_4,
            'E' => self::LEVEL_5,
            default => null,
        };
    }

    public static function fromScore(float|int|string|null $score): ?self
    {
        if (! is_numeric($score)) {
            return null;
        }

        $numericScore = round((float) $score, 2);

        if ($numericScore < 1.00) {
            return null;
        }

        return match (true) {
            $numericScore >= 1.00 && $numericScore < 1.80 => self::LEVEL_1,
            $numericScore < 2.60 => self::LEVEL_2,
            $numericScore < 3.40 => self::LEVEL_3,
            $numericScore < 4.20 => self::LEVEL_4,
            $numericScore <= 5.00 => self::LEVEL_5,
            default => null,
        };
    }
}
