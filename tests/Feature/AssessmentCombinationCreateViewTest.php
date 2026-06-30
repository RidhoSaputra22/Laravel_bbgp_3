<?php

namespace Tests\Feature;

use Tests\TestCase;

class AssessmentCombinationCreateViewTest extends TestCase
{
    public function test_combination_create_view_renders_competency_based_inputs(): void
    {
        $response = $this
            ->withSession([
                'role' => 'admin',
                'user_id' => 1,
                'name' => 'Admin Test',
            ])
            ->withViewErrors([])
            ->view('pages.admin.assessment.combination.create', [
                'menu' => 'assessment-kombinasi',
                'ketenagaanOptions' => [
                    'tenaga_pendidik' => 'Tenaga Pendidik',
                    'tenaga_kependidikan' => 'Tenaga Kependidikan',
                    'stakeholder' => 'Stakeholder',
                ],
                'assessmentCatalogByKetenagaan' => [
                    'tenaga_pendidik' => [
                        [
                            'assessment_id' => 5,
                            'assessment_code' => 'ASM-001',
                            'assessment_title' => 'Assessment Kompetensi Guru',
                            'assessment_order' => 1,
                            'instrument_type' => 'portofolio',
                            'instrument_label' => 'Portofolio',
                            'competencies' => [
                                [
                                    'kompetensi' => 'pedagogik',
                                    'kompetensi_label' => 'Pedagogik',
                                    'available_form_count' => 2,
                                    'available_question_count' => 10,
                                    'form_titles' => ['Pedagogik A', 'Pedagogik B'],
                                    'form_codes' => ['FORM-PED-A', 'FORM-PED-B'],
                                    'indikator_codes' => ['1.1', '1.2'],
                                ],
                                [
                                    'kompetensi' => 'kepribadian',
                                    'kompetensi_label' => 'Kepribadian',
                                    'available_form_count' => 1,
                                    'available_question_count' => 8,
                                    'form_titles' => ['Refleksi Diri'],
                                    'form_codes' => ['FORM-KEP'],
                                    'indikator_codes' => ['2.1'],
                                ],
                                [
                                    'kompetensi' => 'sosial',
                                    'kompetensi_label' => 'Sosial',
                                    'available_form_count' => 0,
                                    'available_question_count' => 0,
                                    'form_titles' => [],
                                    'form_codes' => [],
                                    'indikator_codes' => [],
                                ],
                                [
                                    'kompetensi' => 'profesional',
                                    'kompetensi_label' => 'Profesional',
                                    'available_form_count' => 1,
                                    'available_question_count' => 7,
                                    'form_titles' => ['Penguasaan Materi'],
                                    'form_codes' => ['FORM-PRO'],
                                    'indikator_codes' => ['4.1'],
                                ],
                            ],
                            'auto_included_forms' => [
                                [
                                    'form_id' => 9,
                                    'form_code' => 'FORM-ID',
                                    'form_title' => 'Identitas Responden',
                                    'form_description' => null,
                                    'available_question_count' => 2,
                                    'indikator_kode' => 'FORM-ID',
                                    'indikator_label' => 'Identitas Responden',
                                    'is_scoreable' => false,
                                ],
                            ],
                            'auto_included_form_count' => 1,
                            'auto_included_question_count' => 2,
                            'total_forms' => 4,
                            'total_questions' => 27,
                        ],
                    ],
                    'tenaga_kependidikan' => [],
                    'stakeholder' => [],
                ],
            ]);

        $response->assertSee('const assessmentCatalogByKetenagaan =', false);
        $response->assertSee('Input Soal Per Assessment');
        $response->assertSee('Gunakan Semua Soal');
        $response->assertSee('Form Tanpa Kompetensi');
        $response->assertSee('Otomatis saat simpan');
        $response->assertSee('name="target_ketenagaan"', false);
        $response->assertSee('competency_selection_modes[${assessmentId}][${competencyKey}]', false);
        $response->assertSee('competency_take_counts[${assessmentId}][${competencyKey}]', false);
        $response->assertDontSee('name="judul"', false);
        $response->assertDontSee('name="deskripsi"', false);
        $response->assertDontSee('form_take_counts[', false);
    }
}
