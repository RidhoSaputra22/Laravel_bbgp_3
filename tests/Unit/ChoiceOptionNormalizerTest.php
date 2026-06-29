<?php

namespace Tests\Unit;

use App\Support\Assessment\ChoiceOptionNormalizer;
use Tests\TestCase;

class ChoiceOptionNormalizerTest extends TestCase
{
    public function test_it_infers_competency_level_from_legacy_choice_code(): void
    {
        $normalized = ChoiceOptionNormalizer::normalize([
            'label' => 'A',
            'value' => 'Mengenali faktor yang memengaruhi perilaku peserta didik.',
        ]);

        $this->assertSame('Mengenali faktor yang memengaruhi perilaku peserta didik.', $normalized['label']);
        $this->assertSame('A', $normalized['value']);
        $this->assertSame(1, $normalized['level_kompetensi']);
        $this->assertSame('Level 1: Paham', $normalized['level_kompetensi_label']);
    }

    public function test_it_preserves_explicit_competency_level_metadata(): void
    {
        $normalized = ChoiceOptionNormalizer::normalize([
            'label' => 'Mengembangkan ekosistem belajar yang berkelanjutan.',
            'value' => 'E',
            'level_kompetensi' => '5',
        ]);

        $this->assertSame('Mengembangkan ekosistem belajar yang berkelanjutan.', $normalized['label']);
        $this->assertSame('E', $normalized['value']);
        $this->assertSame(5, $normalized['level_kompetensi']);
        $this->assertSame('Level 5: Ahli', $normalized['level_kompetensi_label']);
    }
}
