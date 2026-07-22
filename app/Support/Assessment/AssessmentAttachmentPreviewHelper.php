<?php

namespace App\Support\Assessment;

class AssessmentAttachmentPreviewHelper
{
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];

    public static function resolve(?string $url, ?string $name = null, ?string $mime = null, ?string $source = null): array
    {
        $url = trim((string) $url);
        $name = trim((string) $name);
        $mime = strtolower(trim((string) $mime));
        $extension = static::resolveExtension($name, $url);
        $googlePreviewUrl = $url !== '' ? static::resolveGooglePreviewUrl($url) : null;

        if (static::isImage($mime, $extension)) {
            $previewType = 'image';
            $previewUrl = $url;
        } elseif (static::isPdf($mime, $extension)) {
            $previewType = 'pdf';
            $previewUrl = $url;
        } elseif ($googlePreviewUrl !== null) {
            $previewType = 'embed';
            $previewUrl = $googlePreviewUrl;
        } elseif ($url !== '') {
            $previewType = static::isExternalHttpUrl($url) ? 'link' : 'file';
            $previewUrl = null;
        } else {
            $previewType = 'file';
            $previewUrl = null;
        }

        return [
            'preview_type' => $previewType,
            'preview_url' => $previewUrl,
            'is_embeddable' => in_array($previewType, ['image', 'pdf', 'embed'], true) && $previewUrl !== '',
            'icon_class' => static::iconClass($previewType),
            'host_label' => static::hostLabel($url),
            'extension' => $extension,
            'source_label' => static::sourceLabel($source, $url),
        ];
    }

    public static function formatBytes(mixed $bytes): string
    {
        $bytes = (int) $bytes;

        if ($bytes <= 0) {
            return '';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = (float) $bytes;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return number_format($size, $unitIndex === 0 ? 0 : 1).' '.$units[$unitIndex];
    }

    private static function resolveExtension(string $name, string $url): string
    {
        $candidate = $name;

        if ($candidate === '' && $url !== '') {
            $candidate = trim((string) parse_url($url, PHP_URL_PATH));
        }

        return strtolower(pathinfo($candidate, PATHINFO_EXTENSION));
    }

    private static function isImage(string $mime, string $extension): bool
    {
        return str_starts_with($mime, 'image/') || in_array($extension, self::IMAGE_EXTENSIONS, true);
    }

    private static function isPdf(string $mime, string $extension): bool
    {
        return $mime === 'application/pdf' || $extension === 'pdf';
    }

    private static function isExternalHttpUrl(string $url): bool
    {
        $scheme = strtolower(trim((string) parse_url($url, PHP_URL_SCHEME)));

        return in_array($scheme, ['http', 'https'], true);
    }

    private static function resolveGooglePreviewUrl(string $url): ?string
    {
        $host = strtolower(trim((string) parse_url($url, PHP_URL_HOST)));
        $path = trim((string) parse_url($url, PHP_URL_PATH));
        $query = trim((string) parse_url($url, PHP_URL_QUERY));

        if ($host === '') {
            return null;
        }

        $queryValues = [];
        parse_str($query, $queryValues);

        if (str_ends_with($host, 'drive.google.com')) {
            if (preg_match('#/file/d/([^/]+)#', $path, $matches)) {
                return 'https://drive.google.com/file/d/'.static::sanitizeGoogleId($matches[1]).'/preview';
            }

            if (preg_match('#/drive/(?:u/\d+/)?folders/([^/]+)#', $path, $matches)) {
                return 'https://drive.google.com/embeddedfolderview?id='.static::sanitizeGoogleId($matches[1]).'#grid';
            }

            $id = trim((string) ($queryValues['id'] ?? ''));

            if ($id !== '') {
                return 'https://drive.google.com/file/d/'.static::sanitizeGoogleId($id).'/preview';
            }
        }

        if (str_ends_with($host, 'docs.google.com')) {
            if (preg_match('#/(document|spreadsheets|presentation|drawings)/d/([^/]+)#', $path, $matches)) {
                return 'https://docs.google.com/'.$matches[1].'/d/'.static::sanitizeGoogleId($matches[2]).'/preview';
            }
        }

        if (str_ends_with($host, 'sheets.google.com') && preg_match('#/d/([^/]+)#', $path, $matches)) {
            return 'https://docs.google.com/spreadsheets/d/'.static::sanitizeGoogleId($matches[1]).'/preview';
        }

        if (str_ends_with($host, 'slides.google.com') && preg_match('#/d/([^/]+)#', $path, $matches)) {
            return 'https://docs.google.com/presentation/d/'.static::sanitizeGoogleId($matches[1]).'/preview';
        }

        return null;
    }

    private static function sanitizeGoogleId(string $id): string
    {
        return preg_replace('/[^A-Za-z0-9_-]/', '', $id) ?? '';
    }

    private static function iconClass(string $previewType): string
    {
        return match ($previewType) {
            'image' => 'fa-regular fa-file-image',
            'pdf' => 'fa-regular fa-file-pdf',
            'embed', 'link' => 'fa-solid fa-link',
            default => 'fa-regular fa-file',
        };
    }

    private static function hostLabel(string $url): string
    {
        $host = strtolower(trim((string) parse_url($url, PHP_URL_HOST)));

        if ($host === '') {
            return '';
        }

        return preg_replace('/^www\./', '', $host) ?? $host;
    }

    private static function sourceLabel(?string $source, string $url): string
    {
        return match (trim((string) $source)) {
            'uploaded_file' => 'File upload',
            'external_link' => 'Link file',
            default => static::isExternalHttpUrl($url) ? 'Link file' : 'File',
        };
    }
}
