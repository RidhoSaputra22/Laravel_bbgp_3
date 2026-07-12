<?php

namespace Tests\Feature;

use App\Models\AssessmentAssignment;
use App\Models\AssessmentAssignmentTarget;
use App\Models\AssessmentAttempt;
use App\Models\Guru;
use Tests\TestCase;

class AssessmentOverviewViewTest extends TestCase
{
    public function test_assessment_overview_view_renders_stage_statuses_and_actions(): void
    {
        $guru = new Guru([
            'nama_lengkap' => 'Siti Saputra Saputra',
            'satuan_pendidikan' => 'SD Negeri 1 Lalabata',
            'no_ktp' => '1111111111111111',
        ]);

        $assignment = new AssessmentAssignment([
            'kode_penugasan' => 'TGS-ASM-20260712-132259-EXGW',
            'judul_penugasan' => 'Assessment Nasional Batch 1',
            'deskripsi' => 'Ringkasan penugasan assessment peserta.',
        ]);

        $target = new AssessmentAssignmentTarget([
            'status' => 'dikerjakan',
        ]);
        $target->id = 609;
        $target->setRelation('assignment', $assignment);

        $attempt = new AssessmentAttempt([
            'status' => 'in_progress',
        ]);

        $response = $this->view('assessment.show.overview', [
            'menu' => 'assessment-portal',
            'guru' => $guru,
            'target' => $target,
            'attempt' => $attempt,
            'meta' => [
                'badge' => 'warning',
                'label' => 'Tahap 2 Sedang Dikerjakan',
                'date_text' => '12 Jul 2026 - 13 Jul 2026',
                'form_total' => 4,
                'question_total' => 18,
                'session_label' => 'Tanpa sesi',
                'session_schedule_text' => 'Akses fleksibel kapan saja',
                'duration_minutes' => 180,
            ],
            'stageOverview' => [
                'stage_total' => 3,
                'submitted_total' => 1,
                'in_progress_total' => 1,
                'draft_total' => 1,
                'ready_total' => 0,
                'available_total' => 1,
                'locked_total' => 0,
                'completion_percent' => 33,
                'stages' => [
                    [
                        'index' => 0,
                        'number' => 1,
                        'code' => 'ASM-1',
                        'title' => 'Portofolio',
                        'description' => 'Tahap pertama.',
                        'instruction' => '',
                        'instrument_label' => 'Portofolio',
                        'form_total' => 1,
                        'question_total' => 6,
                        'required_question_total' => 3,
                        'status' => 'submitted',
                        'status_label' => 'Selesai',
                        'status_tone' => 'success',
                        'status_description' => 'Portofolio sudah selesai.',
                        'started_at_label' => '12 Jul 2026 08:00 WITA',
                        'deadline_at_label' => 'Tanpa batas waktu',
                        'submitted_at_label' => '12 Jul 2026 09:00 WITA',
                        'entry_mode_label' => 'Langsung isi',
                        'finalize_mode_label' => 'Manual / permanen',
                        'time_limit_label' => 'Tanpa timer',
                        'security_label' => 'Guard nonaktif',
                        'allow_draft' => true,
                        'requires_start_button' => false,
                        'is_current' => false,
                        'is_locked' => false,
                        'is_submitted' => true,
                        'can_open' => true,
                        'action_mode' => 'open',
                        'action_label' => 'Lihat Tahap',
                    ],
                    [
                        'index' => 1,
                        'number' => 2,
                        'code' => 'ASM-2',
                        'title' => 'Studi Kasus',
                        'description' => 'Tahap kedua.',
                        'instruction' => 'Baca kasus dengan teliti.',
                        'instrument_label' => 'Studi Kasus',
                        'form_total' => 2,
                        'question_total' => 8,
                        'required_question_total' => 8,
                        'status' => 'in_progress',
                        'status_label' => 'Sedang Dikerjakan',
                        'status_tone' => 'warning',
                        'status_description' => 'Studi Kasus sedang berjalan.',
                        'started_at_label' => '12 Jul 2026 09:15 WITA',
                        'deadline_at_label' => '12 Jul 2026 11:15 WITA',
                        'submitted_at_label' => '-',
                        'entry_mode_label' => 'Tombol mulai',
                        'finalize_mode_label' => 'Auto submit',
                        'time_limit_label' => '120 menit',
                        'security_label' => 'Guard aktif, fullscreen wajib',
                        'allow_draft' => false,
                        'requires_start_button' => true,
                        'is_current' => true,
                        'is_locked' => false,
                        'is_submitted' => false,
                        'can_open' => true,
                        'action_mode' => 'open',
                        'action_label' => 'Lanjutkan Tahap',
                    ],
                    [
                        'index' => 2,
                        'number' => 3,
                        'code' => 'ASM-3',
                        'title' => 'Observasi',
                        'description' => 'Tahap ketiga.',
                        'instruction' => '',
                        'instrument_label' => 'Monitoring / Observasi / Eviden',
                        'form_total' => 1,
                        'question_total' => 4,
                        'required_question_total' => 1,
                        'status' => 'draft',
                        'status_label' => 'Draft',
                        'status_tone' => 'secondary',
                        'status_description' => 'Observasi belum dimulai.',
                        'started_at_label' => '-',
                        'deadline_at_label' => 'Tanpa batas waktu',
                        'submitted_at_label' => '-',
                        'entry_mode_label' => 'Langsung isi',
                        'finalize_mode_label' => 'Manual / permanen',
                        'time_limit_label' => 'Tanpa timer',
                        'security_label' => 'Guard nonaktif',
                        'allow_draft' => true,
                        'requires_start_button' => false,
                        'is_current' => false,
                        'is_locked' => false,
                        'is_submitted' => false,
                        'can_open' => true,
                        'action_mode' => 'open',
                        'action_label' => 'Lanjutkan Tahap',
                    ],
                ],
            ],
        ]);

        $response->assertSee('Tahap Penugasan Assessment');
        $response->assertSee('Assessment Nasional Batch 1');
        $response->assertSee('Portofolio');
        $response->assertSee('Studi Kasus');
        $response->assertSee('Draft');
        $response->assertSee('Sedang Dikerjakan');
        $response->assertSee('Lanjutkan Tahap');
        $response->assertSee(route('assessment.portal.show', ['id' => 609, 'stage' => 1], absolute: false), false);
    }
}
