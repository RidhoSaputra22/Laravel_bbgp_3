<?php

namespace Tests\Unit;

use App\Support\Assessment\ScoringGuidanceAssistant;
use Tests\TestCase;

class ScoringGuidanceAssistantTest extends TestCase
{
    public function test_it_builds_keyword_and_synonym_suggestions_from_plain_guidance_text(): void
    {
        $assistant = new ScoringGuidanceAssistant;

        $suggestion = $assistant->suggest(
            'Riwayat pendidikan menunjukkan relevansi program studi, sertifikat, lembaga, dan bukti dukung untuk kebutuhan mengajar.',
            'textarea'
        );

        $this->assertStringContainsString('riwayat pendidikan', (string) $suggestion['keyword_groups_text']);
        $this->assertStringContainsString('program studi', (string) $suggestion['keyword_groups_text']);
        $this->assertStringNotContainsString('|', (string) $suggestion['keyword_groups_text']);
        $this->assertStringContainsString('bukti dukung: dokumen pendukung', (string) $suggestion['synonym_map_text']);
        $this->assertSame(18, $suggestion['min_words']);
        $this->assertContains('kebutuhan mengajar', $suggestion['advanced_rules']['signal_keywords']);
    }

    public function test_it_adds_target_rows_for_repeater_guidance(): void
    {
        $assistant = new ScoringGuidanceAssistant;

        $suggestion = $assistant->suggest(
            'Lampirkan bukti kegiatan, hasil monitoring, dan catatan refleksi pada setiap baris tabel.',
            'repeater',
            ['target_rows' => 3]
        );

        $this->assertSame(3, $suggestion['advanced_rules']['target_rows']);
        $this->assertGreaterThanOrEqual(12, $suggestion['min_words']);
    }
}
