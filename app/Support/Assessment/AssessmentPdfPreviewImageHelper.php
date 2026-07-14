<?php

namespace App\Support\Assessment;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AssessmentPdfPreviewImageHelper
{
    public static function buildDataUri(?string $filePath): ?string
    {
        $filePath = trim((string) $filePath);

        if ($filePath === '') {
            return null;
        }

        if (! Storage::disk('public')->exists($filePath)) {
            return null;
        }

        $extension = Str::lower(pathinfo($filePath, PATHINFO_EXTENSION));

        if (! in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'webp'], true)) {
            return null;
        }

        try {
            $mimeType = Storage::disk('public')->mimeType($filePath) ?: match ($extension) {
                'jpg', 'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                default => 'image/png',
            };
            $binaryContents = Storage::disk('public')->get($filePath);
            $normalizedDataUri = static::buildNormalizedDataUri($binaryContents);

            if ($normalizedDataUri !== null) {
                return $normalizedDataUri;
            }

            return sprintf('data:%s;base64,%s', $mimeType, base64_encode($binaryContents));
        } catch (\Throwable $exception) {
            return null;
        }
    }

    private static function buildNormalizedDataUri(string $binaryContents): ?string
    {
        if (
            ! function_exists('imagecreatefromstring')
            || ! function_exists('imagecreatetruecolor')
            || ! function_exists('imagepng')
        ) {
            return null;
        }

        $image = @imagecreatefromstring($binaryContents);

        if ($image === false) {
            return null;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $canvas = imagecreatetruecolor($width, $height);

        if ($canvas === false) {
            imagedestroy($image);

            return null;
        }

        imagealphablending($canvas, false);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, $width, $height, $transparent);
        imagesavealpha($canvas, true);
        imagecopy($canvas, $image, 0, 0, 0, 0, $width, $height);

        ob_start();
        $written = imagepng($canvas);
        $pngContents = ob_get_clean();

        imagedestroy($canvas);
        imagedestroy($image);

        if (! $written || ! is_string($pngContents) || $pngContents === '') {
            return null;
        }

        return sprintf(
            'data:%s;base64,%s',
            static::pdfPreviewMimeType(),
            base64_encode($pngContents)
        );
    }

    private static function pdfPreviewMimeType(): string
    {
        return 'image/png';
    }
}
