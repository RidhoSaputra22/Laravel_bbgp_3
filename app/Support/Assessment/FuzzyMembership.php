<?php

namespace App\Support\Assessment;

class FuzzyMembership
{
    public static function low(float $value, float $start = 0.2, float $end = 0.45): float
    {

        if ($value <= $start) {
            return 1.0;
        }

        if ($value >= $end) {
            return 0.0;
        }

        return round(($end - $value) / max($end - $start, 0.0001), 4);
    }

    public static function medium(float $value, float $start = 0.25, float $peak = 0.55, float $end = 0.8): float
    {
        if ($value <= $start || $value >= $end) {
            return 0.0;
        }

        if ($value === $peak) {
            return 1.0;
        }

        if ($value < $peak) {
            return round(($value - $start) / max($peak - $start, 0.0001), 4);
        }

        return round(($end - $value) / max($end - $peak, 0.0001), 4);
    }

    public static function high(float $value, float $start = 0.55, float $peak = 0.8): float
    {
        if ($value <= $start) {
            return 0.0;
        }

        if ($value >= $peak) {
            return 1.0;
        }

        return round(($value - $start) / max($peak - $start, 0.0001), 4);
    }
}
