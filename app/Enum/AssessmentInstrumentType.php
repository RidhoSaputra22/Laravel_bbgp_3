<?php

namespace App\Enum;

enum AssessmentInstrumentType: string
{
    case PORTOFOLIO = 'portofolio';
    case PILIHAN_GANDA_KOMPLEKS = 'pilihan_ganda_kompleks';
    case STUDI_KASUS = 'studi_kasus';
    case MONITORING_OBSERVASI_EVIDEN = 'monitoring_observasi_eviden';

    public function label(): string
    {
        return match ($this) {
            self::PORTOFOLIO => 'Portofolio',
            self::PILIHAN_GANDA_KOMPLEKS => 'Pilihan Ganda Kompleks',
            self::STUDI_KASUS => 'Studi Kasus',
            self::MONITORING_OBSERVASI_EVIDEN => 'Monitoring / Observasi / Eviden',
        };
    }

    public function weight(): float
    {
        return match ($this) {
            self::PORTOFOLIO => 0.30,
            self::PILIHAN_GANDA_KOMPLEKS => 0.40,
            self::STUDI_KASUS => 0.30,
            self::MONITORING_OBSERVASI_EVIDEN => 0.20,
        };
    }

    public function requiresManualReview(): bool
    {
        return $this !== self::PILIHAN_GANDA_KOMPLEKS;
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
