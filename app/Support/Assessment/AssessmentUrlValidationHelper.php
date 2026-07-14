<?php

namespace App\Support\Assessment;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AssessmentUrlValidationHelper
{
    private const SUPPORTED_GOOGLE_DOMAINS = [
        'drive.google.com',
        'docs.google.com',
        'sheets.google.com',
        'slides.google.com',
    ];

    /**
     * @param  array<string, mixed>  $definition
     * @return array<int, string>
     */
    public static function allowedDomains(array $definition): array
    {
        $allowedDomains = collect([
            data_get($definition, 'validasi.allowed_domains', []),
            data_get($definition, 'opsi_field.allowed_domains', []),
            data_get($definition, 'allowed_domains', []),
        ])
            ->flatMap(fn ($value) => Arr::wrap($value))
            ->map(fn ($domain) => static::normalizeDomain($domain))
            ->filter(fn ($domain) => in_array($domain, self::SUPPORTED_GOOGLE_DOMAINS, true))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($allowedDomains !== []) {
            return $allowedDomains;
        }

        return self::SUPPORTED_GOOGLE_DOMAINS;
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    public static function buildInvalidMessage(string $context, array $definition): string
    {
        $allowedDomains = static::allowedDomains($definition);

        if ($allowedDomains === []) {
            return "{$context} harus berupa URL yang valid.";
        }

        return "{$context} harus menggunakan URL yang valid pada domain ".implode(', ', $allowedDomains).'.';
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    public static function buildInputTitle(array $definition): ?string
    {
        $allowedDomains = static::allowedDomains($definition);

        if ($allowedDomains === []) {
            return null;
        }

        return 'Gunakan URL pada domain: '.implode(', ', $allowedDomains).'.';
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    public static function matchesDefinition(?string $url, array $definition): bool
    {
        if (! static::isValidHttpUrl($url)) {
            return false;
        }

        $allowedDomains = static::allowedDomains($definition);

        if ($allowedDomains === []) {
            return true;
        }

        $host = static::resolveHost($url);

        return $host !== null && in_array($host, $allowedDomains, true);
    }

    public static function isValidHttpUrl(?string $url): bool
    {
        $url = trim((string) $url);

        if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        return in_array(Str::lower((string) $scheme), ['http', 'https'], true);
    }

    /**
     * @return array<int, string>
     */
    public static function supportedGoogleDomains(): array
    {
        return self::SUPPORTED_GOOGLE_DOMAINS;
    }

    private static function resolveHost(?string $url): ?string
    {
        $host = parse_url(trim((string) $url), PHP_URL_HOST);
        $normalizedHost = static::normalizeDomain($host);

        return $normalizedHost !== '' ? $normalizedHost : null;
    }

    private static function normalizeDomain(mixed $domain): string
    {
        return trim(Str::lower(trim((string) $domain)), '.');
    }
}
