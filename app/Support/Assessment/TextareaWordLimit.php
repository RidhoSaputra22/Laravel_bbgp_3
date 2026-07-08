<?php

namespace App\Support\Assessment;

class TextareaWordLimit
{
    public const MIN_WORDS = 25;

    public const MAX_WORDS = 100;

    public static function minWords(): int
    {
        return self::MIN_WORDS;
    }

    public static function maxWords(): int
    {
        return self::MAX_WORDS;
    }

    public static function count(?string $value): int
    {
        $value = trim((string) $value);

        if ($value === '') {
            return 0;
        }

        preg_match_all('/\S+/u', $value, $matches);

        return count($matches[0] ?? []);
    }

    public static function tooShort(?string $value): bool
    {
        $wordCount = self::count($value);

        return $wordCount > 0 && $wordCount < self::minWords();
    }

    public static function tooLong(?string $value): bool
    {
        return self::count($value) > self::maxWords();
    }

    public static function isValid(?string $value): bool
    {
        $wordCount = self::count($value);

        if ($wordCount === 0) {
            return false;
        }

        return $wordCount >= self::minWords() && $wordCount <= self::maxWords();
    }

    public static function helperText(): string
    {
        return 'Minimal '.self::minWords().' kata, maksimal '.self::maxWords().' kata.';
    }
}
