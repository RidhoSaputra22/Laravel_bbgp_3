<?php

namespace Tests\Unit;

use App\Support\Assessment\AssessmentUrlValidationHelper;
use Tests\TestCase;

class AssessmentUrlValidationHelperTest extends TestCase
{
    public function test_allowed_domains_defaults_to_supported_google_domains(): void
    {
        $definition = [
            'label' => 'Link Google Drive Sertifikat / SK',
            'nama_field' => 'link_google_drive_sertifikat_sk',
            'placeholder' => 'https://drive.google.com/...',
        ];

        $this->assertSame([
            'drive.google.com',
            'docs.google.com',
            'sheets.google.com',
            'slides.google.com',
        ], AssessmentUrlValidationHelper::allowedDomains($definition));
    }

    public function test_matches_definition_accepts_supported_google_domains_only(): void
    {
        $definition = [
            'label' => 'Link Google Drive Sertifikat / SK',
            'nama_field' => 'link_google_drive_sertifikat_sk',
            'placeholder' => 'https://drive.google.com/...',
        ];

        $validUrls = [
            'https://drive.google.com/file/d/123/view',
            'https://docs.google.com/document/d/123/edit',
            'https://sheets.google.com/sheet/d/123/view',
            'https://slides.google.com/presentation/d/123/edit',
        ];

        foreach ($validUrls as $url) {
            $this->assertTrue(AssessmentUrlValidationHelper::matchesDefinition($url, $definition), $url);
        }

        $this->assertFalse(
            AssessmentUrlValidationHelper::matchesDefinition('https://example.com/file/d/123/view', $definition)
        );
    }
}
