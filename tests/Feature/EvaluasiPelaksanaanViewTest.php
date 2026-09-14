<?php

namespace Tests\Feature;

use App\Models\Assessment;
use Illuminate\Support\Collection;
use Tests\TestCase;

class EvaluasiPelaksanaanViewTest extends TestCase
{
    public function test_bank_soal_view_uses_evaluasi_context_and_menu_routes(): void
    {
        $response = $this
            ->withSession(['role' => 'admin', 'name' => 'Admin Test', 'user_id' => 1])
            ->withViewErrors([])
            ->view('pages.admin.assessment.index', [
                'menu' => 'evaluasi-bank-soal',
                'datas' => new Collection(),
                'isEvaluationPelaksanaan' => true,
                'assessmentRoutePrefix' => 'evaluasi.pelaksanaan.bank-soal',
            ]);

        $response->assertSee('Evaluasi Pelaksanaan');
        $response->assertSee('Bank Soal Evaluasi Pelaksanaan');
        $response->assertSee('Hasil Evaluasi');
        $response->assertSee(route('evaluasi.pelaksanaan.bank-soal.create'), false);
        $response->assertSee(route('evaluasi.pelaksanaan.hasil.index'), false);
    }

    public function test_bank_soal_form_uses_soal_label_without_ketenagaan_target(): void
    {
        $response = $this
            ->withSession(['role' => 'admin', 'name' => 'Admin Test', 'user_id' => 1])
            ->withViewErrors([])
            ->view('pages.admin.assessment.create', [
                'menu' => 'evaluasi-bank-soal',
                'assessment' => new Assessment([
                    'status' => 'draft',
                    'is_active' => true,
                    'target_ketenagaan' => null,
                ]),
                'fieldTypes' => [
                    'text' => 'Teks',
                    'textarea' => 'Area Teks',
                    'select' => 'Daftar Pilihan',
                ],
                'formBuilderData' => [],
                'ketenagaanOptions' => [
                    'tenaga_pendidik' => 'Tenaga Pendidik',
                    'tenaga_kependidikan' => 'Tenaga Kependidikan',
                    'stakeholder' => 'Stakeholder',
                ],
                'isEvaluationPelaksanaan' => true,
                'assessmentRoutePrefix' => 'evaluasi.pelaksanaan.bank-soal',
            ]);

        $response->assertSee('Judul Soal');
        $response->assertSee('Rekapan Soal');
        $response->assertSee('Masukkan judul soal');
        $response->assertDontSee('Ketenagaan Assessment');
        $response->assertDontSee('Pilih ketenagaan tujuan assessment');
    }

    public function test_result_view_renders_dashboard_sections(): void
    {
        $response = $this
            ->withSession(['role' => 'admin', 'name' => 'Admin Test', 'user_id' => 1])
            ->withViewErrors([])
            ->view('pages.admin.evaluasi-pelaksanaan.hasil.index', [
                'menu' => 'evaluasi-hasil',
                'evaluations' => new Collection(),
                'selectedEvaluationId' => 0,
                'stats' => [
                    'bank_total' => 2,
                    'form_total' => 4,
                    'question_total' => 12,
                    'assignment_total' => 3,
                    'participant_total' => 20,
                    'submitted_total' => 15,
                    'in_progress_total' => 2,
                    'not_started_total' => 3,
                    'average_score' => 4.25,
                    'completion_rate' => 75,
                ],
                'assignmentRows' => new Collection(),
                'recentResults' => new Collection(),
                'scoreDistribution' => [],
            ]);

        $response->assertSee('Hasil Evaluasi Pelaksanaan');
        $response->assertSee('Ringkasan Penugasan');
        $response->assertSee('Distribusi Nilai');
        $response->assertSee('Hasil Per Peserta');
        $response->assertSee('Skala penilaian 1–4');
        $response->assertDontSee('Skala penilaian 1–5');
        $response->assertSee('4.25');
    }
}
