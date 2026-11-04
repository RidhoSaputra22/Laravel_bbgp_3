<?php

namespace Tests\Unit;

use App\Support\Assessment\FileFieldOptionsNormalizer;
use PHPUnit\Framework\TestCase;

class FileFieldOptionsNormalizerTest extends TestCase
{
    public function test_it_infers_image_accept_from_legacy_help_text(): void
    {
        $options = FileFieldOptionsNormalizer::normalize([
            'input_mode' => 'file',
            'max_size_kb' => 1024,
        ], null, 'Unggah foto. Format JPG/JPEG/PNG; ukuran maksimal 1 MB.');

        $this->assertSame(['image/jpeg', 'image/png'], $options['accept']);
        $this->assertSame(1024, $options['max_size_kb']);
    }

    public function test_it_preserves_explicit_accept_configuration(): void
    {
        $options = FileFieldOptionsNormalizer::normalize([
            'accept' => ['application/pdf'],
            'max_size_kb' => 2048,
        ], null, 'Format JPG/JPEG/PNG.');

        $this->assertSame(['application/pdf'], $options['accept']);
    }

    public function test_it_uses_validation_mimes_before_default_formats(): void
    {
        $options = FileFieldOptionsNormalizer::normalize(
            ['max_size_kb' => 1024],
            ['mimes' => ['pdf', 'docx']]
        );

        $this->assertSame(['.pdf', '.docx'], $options['accept']);
    }
}
