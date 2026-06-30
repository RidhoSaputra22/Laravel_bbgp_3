<?php

namespace Tests\Feature;

use App\Models\Assessment;
use Tests\TestCase;

class AssessmentBuilderSidebarViewTest extends TestCase
{
    public function test_assessment_create_view_renders_summary_sidebar_with_create_action(): void
    {
        $response = $this
            ->withSession([
                'role' => 'admin',
                'user_id' => 1,
                'name' => 'Admin Test',
            ])
            ->withViewErrors([])
            ->view('pages.admin.assessment.create', [
                'menu' => 'assessment',
                'assessment' => new Assessment([
                    'status' => 'draft',
                    'is_active' => true,
                    'target_ketenagaan' => 'tenaga_pendidik',
                ]),
                'fieldTypes' => $this->fieldTypes(),
                'formBuilderData' => [],
                'ketenagaanOptions' => $this->ketenagaanOptions(),
            ]);

        $response->assertSee('id="assessment-builder-summary"', false);
        $response->assertSee('id="summary-total-questions"', false);
        $response->assertSee('id="btn-sidebar-add-form"', false);
        $response->assertSee('Rekapan Assessment');
        $response->assertSee('Buat Assessment');
        $response->assertDontSee('Lihat Preview');
    }

    public function test_assessment_edit_view_renders_summary_sidebar_with_edit_and_preview_actions(): void
    {
        $assessment = new Assessment([
            'kode_assessment' => 'ASM-001',
            'judul' => 'Assessment Uji Coba',
            'status' => 'publish',
            'is_active' => true,
            'target_ketenagaan' => 'tenaga_kependidikan',
            'instrument_type' => 'studi_kasus',
            'deskripsi' => 'Deskripsi assessment.',
            'petunjuk' => 'Petunjuk assessment.',
        ]);
        $assessment->id = 99;

        $response = $this
            ->withSession([
                'role' => 'admin',
                'user_id' => 1,
                'name' => 'Admin Test',
            ])
            ->withViewErrors([])
            ->view('pages.admin.assessment.edit', [
                'menu' => 'assessment',
                'assessment' => $assessment,
                'fieldTypes' => $this->fieldTypes(),
                'formBuilderData' => [
                    [
                        'judul_form' => 'Form Profil',
                        'kode_form' => 'FORM-001',
                        'urutan' => 1,
                        'is_active' => true,
                        'is_scoreable' => true,
                        'fields' => [
                            [
                                'label' => 'Nama Lengkap',
                                'tipe_field' => 'text',
                                'placeholder' => 'Tulis nama lengkap',
                                'urutan' => 1,
                                'is_active' => true,
                                'is_required' => true,
                                'scoring' => [
                                    'enabled' => true,
                                ],
                            ],
                        ],
                    ],
                ],
                'ketenagaanOptions' => $this->ketenagaanOptions(),
            ]);

        $response->assertSee('id="assessment-builder-summary"', false);
        $response->assertSee('Edit Assessment');
        $response->assertSee(route('assessment.show', 99), false);
        $response->assertSee('Lihat Preview');
        $response->assertSee('Pantau jumlah soal, status, dan kesiapan tampil saat Anda memperbarui struktur assessment.');
        $response->assertSee('id="summary-total-forms">1</span>', false);
        $response->assertSee('id="summary-total-questions">1</span>', false);
        $response->assertSee('id="summary-active-forms">1</span>', false);
        $response->assertSee('id="summary-auto-scoring-questions">1</span>', false);
        $response->assertSee('id="summary-status-label">Publish</strong>', false);
        $response->assertSee('id="summary-activation-label">Aktif</strong>', false);
        $response->assertSee('id="summary-target-label">Tenaga Kependidikan</strong>', false);
        $response->assertSee('id="summary-instrument-label">Studi Kasus</strong>', false);
        $response->assertSee('id="summary-scoreable-label">1 form</strong>', false);
        $response->assertSee('id="summary-display-label">1 form / 1 soal</strong>', false);
        $response->assertSee('const instrumentTypes =', false);
    }

    private function fieldTypes(): array
    {
        return [
            'text' => 'Teks',
            'textarea' => 'Area Teks',
            'number' => 'Angka',
            'select' => 'Daftar Pilihan',
            'radio' => 'Pilihan Ganda',
        ];
    }

    private function ketenagaanOptions(): array
    {
        return [
            'tenaga_pendidik' => 'Tenaga Pendidik',
            'tenaga_kependidikan' => 'Tenaga Kependidikan',
            'stakeholder' => 'Stakeholder',
        ];
    }
}
