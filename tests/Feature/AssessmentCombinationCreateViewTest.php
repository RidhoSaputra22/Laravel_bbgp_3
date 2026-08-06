<?php

namespace Tests\Feature;

use App\Models\AssessmentCombinationGeneration;
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
        $response->assertSee('Gunakan toggle pada setiap assessment');
        $response->assertSee('Banyak Kombinasi Yang Ingin Dibuat');
        $response->assertSee('Otomatis saat masuk antrean');
        $response->assertSee('Kirim ke Antrean Generate');
        $response->assertSee('name="target_ketenagaan"', false);
        $response->assertSee('name="total_kombinasi"', false);
        $response->assertSee('included_assessment_ids[]', false);
        $response->assertSee('js-assessment-toggle', false);
        $response->assertSee('competency_selection_modes[${assessmentId}][${competencyKey}]', false);
        $response->assertSee('competency_take_counts[${assessmentId}][${competencyKey}]', false);
        $response->assertDontSee('name="judul"', false);
        $response->assertDontSee('name="deskripsi"', false);
        $response->assertDontSee('form_take_counts[', false);
    }

    public function test_combination_edit_reset_view_reuses_form_with_existing_generation_settings(): void
    {
        $generation = new AssessmentCombinationGeneration([
            'id' => 99,
            'kode_generate' => 'KBG-ASM-20260806-0001-ABCD',
            'target_ketenagaan' => 'tenaga_pendidik',
            'total_kombinasi' => 3,
            'selection_config' => [
                'target_ketenagaan' => 'tenaga_pendidik',
                'total_kombinasi' => 3,
                'included_assessment_ids' => [5],
                'competency_selection_modes' => [
                    5 => [
                        'pedagogik' => 'all',
                    ],
                ],
                'competency_take_counts' => [
                    5 => [
                        'pedagogik' => 10,
                    ],
                ],
            ],
        ]);

        $response = $this
            ->withSession([
                'role' => 'admin',
                'user_id' => 1,
                'name' => 'Admin Test',
            ])
            ->withViewErrors([])
            ->view('pages.admin.assessment.combination.create', [
                'menu' => 'assessment-kombinasi',
                'generation' => $generation,
                'isEditMode' => true,
                'pageTitle' => 'Edit & Reset Generate Kombinasi',
                'formAction' => route('assessment.combination.generation.reset', 99),
                'formMethod' => 'POST',
                'submitLabel' => 'Reset dan Generate Ulang',
                'relatedAssignmentUsageCount' => 2,
                'initialFormData' => [
                    'target_ketenagaan' => 'tenaga_pendidik',
                    'total_kombinasi' => 3,
                    'included_assessment_ids' => [5],
                    'competency_selection_modes' => [
                        5 => [
                            'pedagogik' => 'all',
                        ],
                    ],
                    'competency_take_counts' => [
                        5 => [
                            'pedagogik' => 10,
                        ],
                    ],
                ],
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
                            ],
                            'auto_included_forms' => [],
                            'auto_included_form_count' => 0,
                            'auto_included_question_count' => 0,
                            'total_forms' => 2,
                            'total_questions' => 10,
                        ],
                    ],
                    'tenaga_kependidikan' => [],
                    'stakeholder' => [],
                ],
            ]);

        $response->assertSee('Edit & Reset Generate Kombinasi');
        $response->assertSee('Mode edit/reset untuk proses KBG-ASM-20260806-0001-ABCD');
        $response->assertSee('Reset dan Generate Ulang');
        $response->assertSee('Ya, Reset dan Generate Ulang');
        $response->assertSee('id="combinationEditWarningModal"', false);
        $response->assertSee('action="'.route('assessment.combination.generation.reset', 99).'"', false);
        $response->assertDontSee('action="'.route('assessment.combination.store').'"', false);
        $response->assertSee('"tenaga_pendidik":[5]', false);
    }
}
