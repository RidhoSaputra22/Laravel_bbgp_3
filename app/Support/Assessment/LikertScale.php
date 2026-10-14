<?php

namespace App\Support\Assessment;

final class LikertScale
{
    public const FIELD_TYPE = 'likert';

    public const SCORING_METHOD = 'likert_scale';

    public const SCALE_MIN = 1;

    public const SCALE_MAX = 4;

    public static function defaultOptions(): array
    {
        return [
            [
                'label' => 'Sangat Setuju',
                'value' => '4',
                'score' => 4,
            ],
            [
                'label' => 'Setuju',
                'value' => '3',
                'score' => 3,
            ],
            [
                'label' => 'Tidak Setuju',
                'value' => '2',
                'score' => 2,
            ],
            [
                'label' => 'Sangat Tidak Setuju',
                'value' => '1',
                'score' => 1,
            ],
        ];
    }

    public static function reverseFormula(): string
    {
        return (self::SCALE_MAX + self::SCALE_MIN).' - X';
    }

    public static function correctedScore(float|int|string|null $rawScore, bool $isNegativeStatement = false): ?float
    {
        if (! is_numeric($rawScore)) {
            return null;
        }

        $score = min(max((float) $rawScore, self::SCALE_MIN), self::SCALE_MAX);

        return $isNegativeStatement ? (self::SCALE_MAX + self::SCALE_MIN) - $score : $score;
    }

    public static function indexFromMean(float|int|string|null $mean): ?float
    {
        if (! is_numeric($mean)) {
            return null;
        }

        $score = min(max((float) $mean, self::SCALE_MIN), self::SCALE_MAX);

        return round((($score - self::SCALE_MIN) / (self::SCALE_MAX - self::SCALE_MIN)) * 100, 2);
    }

    public static function categoryFromMean(float|int|string|null $mean): ?array
    {
        if (! is_numeric($mean)) {
            return null;
        }

        $score = round((float) $mean, 2);

        return match (true) {
            $score <= 1.75 => [
                'label' => 'Sangat rendah',
                'index_range' => '0-25',
                'recommendation' => 'Prioritas utama pengembangan',
            ],
            $score <= 2.50 => [
                'label' => 'Rendah',
                'index_range' => '>25-50',
                'recommendation' => 'Prioritas tinggi pengembangan',
            ],
            $score <= 3.25 => [
                'label' => 'Tinggi',
                'index_range' => '>50-75',
                'recommendation' => 'Dipertahankan dan dikembangkan',
            ],
            default => [
                'label' => 'Sangat tinggi',
                'index_range' => '>75-100',
                'recommendation' => 'Pengayaan dan berbagi praktik baik',
            ],
        };
    }
}
