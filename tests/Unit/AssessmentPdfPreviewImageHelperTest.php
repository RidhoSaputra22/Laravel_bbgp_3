<?php

namespace Tests\Unit;

use App\Support\Assessment\AssessmentPdfPreviewImageHelper;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AssessmentPdfPreviewImageHelperTest extends TestCase
{
    public function test_converts_webp_upload_to_png_data_uri(): void
    {
        if (! function_exists('imagewebp')) {
            $this->markTestSkipped('Ekstensi GD tanpa dukungan WEBP tidak tersedia.');
        }

        Storage::fake('public');

        $image = imagecreatetruecolor(24, 24);
        $white = imagecolorallocate($image, 255, 255, 255);
        $blue = imagecolorallocate($image, 19, 118, 189);
        imagefill($image, 0, 0, $white);
        imagefilledellipse($image, 12, 12, 16, 16, $blue);

        ob_start();
        imagewebp($image);
        $webpContents = ob_get_clean();
        imagedestroy($image);

        Storage::disk('public')->put('assessment/attempts/test/tanda-tangan.webp', $webpContents);

        $dataUri = AssessmentPdfPreviewImageHelper::buildDataUri(
            'assessment/attempts/test/tanda-tangan.webp'
        );

        $this->assertIsString($dataUri);
        $this->assertStringStartsWith('data:image/png;base64,', $dataUri);
    }
}
