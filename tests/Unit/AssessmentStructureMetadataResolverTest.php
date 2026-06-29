<?php

namespace Tests\Unit;

use App\Enum\AssessmentInstrumentType;
use App\Enum\KompetensiGuru;
use App\Support\Assessment\AssessmentStructureMetadataResolver;
use Tests\TestCase;

class AssessmentStructureMetadataResolverTest extends TestCase
{
    public function test_it_infers_instrument_competency_and_indicator_from_existing_assessment_structure(): void
    {
        $resolver = new AssessmentStructureMetadataResolver;

        $assessment = $resolver->decorateAssessment([
            'kode_assessment' => 'ASM-KOMP-GURU-003',
            'judul' => 'Tes Pilihan Ganda Kompleks Kompetensi Guru',
            'deskripsi' => 'Instrumen pemetaan kompetensi guru.',
        ]);

        $form = $resolver->decorateForm([
            'judul_form' => '1.1.1 Pengelolaan Perilaku Peserta Didik yang Sulit',
            'kode_form' => 'FORM-PED-111',
            'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
            'fields' => [],
        ], $assessment);

        $this->assertSame(AssessmentInstrumentType::PILIHAN_GANDA_KOMPLEKS->value, $assessment['instrument_type']);
        $this->assertSame('Pilihan Ganda Kompleks', $assessment['instrument_label']);
        $this->assertSame(KompetensiGuru::PEDAGOGIK->value, $form['kompetensi']);
        $this->assertSame('Pedagogik', $form['kompetensi_label']);
        $this->assertSame('1.1', $form['indikator_kode']);
        $this->assertSame('Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik', $form['indikator_label']);
        $this->assertTrue($form['is_scoreable']);
    }

    public function test_it_respects_explicit_scoreable_flag_for_non_scored_forms(): void
    {
        $resolver = new AssessmentStructureMetadataResolver;

        $form = $resolver->decorateForm([
            'judul_form' => 'Identitas Responden',
            'kode_form' => 'FORM-IDENTITAS',
            'deskripsi' => 'Data identitas dasar responden.',
            'is_scoreable' => false,
            'fields' => [],
        ]);

        $this->assertFalse($form['is_scoreable']);
        $this->assertNull($form['kompetensi']);
    }
}
