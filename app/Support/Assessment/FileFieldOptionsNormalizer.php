<?php

namespace App\Support\Assessment;

final class FileFieldOptionsNormalizer
{
    /** @var array<int, string> */
    private const DEFAULT_ACCEPT = ['image/jpeg', 'image/png'];

    /** @var array<string, string> */
    private const EXTENSION_MIME_TYPES = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'pdf' => 'application/pdf',
    ];

    /**
     * Preserve explicit file options and fill legacy file fields that only
     * documented their formats in validation/help text.
     *
     * @return array<string, mixed>
     */
    public static function normalize(
        mixed $options,
        mixed $validation = null,
        ?string $help = null
    ): array {
        $normalized = is_array($options) ? $options : [];

        if (array_key_exists('accept', $normalized)) {
            $accept = self::normalizeAccept($normalized['accept']);
        } else {
            $accept = self::inferAccept($validation, $help);

            if ($accept === []) {
                $accept = self::DEFAULT_ACCEPT;
            }
        }

        if ($accept !== []) {
            $normalized['accept'] = $accept;
        }

        return $normalized;
    }

    /** @return array<int, string> */
    private static function inferAccept(mixed $validation, ?string $help): array
    {
        $validation = is_array($validation) ? $validation : [];
        $fromValidation = self::normalizeAccept($validation['mimes'] ?? []);

        if ($fromValidation !== []) {
            return $fromValidation;
        }

        if (! is_string($help) || ! preg_match('/\bformat\b\s*[:\-]?\s*([^.;\n]+)/iu', $help, $matches)) {
            return [];
        }

        preg_match_all(
            '/(?:\.[a-z0-9]+|(?:image|application|text|audio|video)\/[a-z0-9.+-]+|\b(?:jpg|jpeg|png|gif|webp|pdf|docx?|xlsx?|pptx?|odt|ods|odp|zip|rar)\b)/iu',
            $matches[1],
            $formatMatches
        );

        return self::normalizeAccept($formatMatches[0] ?? [], true);
    }

    /** @return array<int, string> */
    private static function normalizeAccept(mixed $value, bool $mapExtensions = false): array
    {
        $values = is_array($value) ? $value : preg_split('/[,;\r\n]+/', (string) $value);
        $normalized = [];

        foreach ($values ?: [] as $item) {
            $item = strtolower(trim((string) $item));

            if ($item === '') {
                continue;
            }

            if ($mapExtensions && isset(self::EXTENSION_MIME_TYPES[ltrim($item, '.')])) {
                $item = self::EXTENSION_MIME_TYPES[ltrim($item, '.')];
            } elseif (! str_contains($item, '/') && ! str_starts_with($item, '.')) {
                $item = '.'.$item;
            }

            if (! in_array($item, $normalized, true)) {
                $normalized[] = $item;
            }
        }

        return $normalized;
    }
}
