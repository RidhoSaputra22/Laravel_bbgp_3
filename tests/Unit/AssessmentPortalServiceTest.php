<?php

namespace Tests\Unit;

use App\Models\Assessment;
use App\Models\AssessmentAssignment;
use App\Models\AssessmentAssignmentSession;
use App\Models\AssessmentAssignmentTarget;
use App\Models\AssessmentAttempt;
use App\Models\AssessmentForm;
use App\Models\AssessmentFormField;
use App\Models\Pivots\AssessmentAssignmentAssessment;
use App\Services\Assessment\AssessmentPortalService;
use App\Services\Assessment\AssessmentQuestionRandomizerService;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AssessmentPortalServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_build_target_meta_blocks_access_until_target_session_start_time(): void
    {
        Carbon::setTestNow('2026-06-27 09:00:00');

        $service = new AssessmentPortalService(
            $this->createMock(AssessmentQuestionRandomizerService::class)
        );

        $target = $this->makeTarget([
            'assignment_start_date' => '2026-06-27',
            'assignment_start_time' => '08:00:00',
            'session_start_at' => '2026-06-27 11:00:00',
            'session_end_at' => '2026-06-27 14:00:00',
        ]);

        $meta = $service->buildTargetMeta($target);

        $this->assertSame('upcoming', $meta['status']);
        $this->assertFalse($meta['can_open']);
        $this->assertSame('Sesi 1', $meta['session_label']);
        $this->assertSame('27 Jun 2026, 11:00 - 14:00 WITA', $meta['session_schedule_text']);
        $this->assertStringContainsString('27 Jun 2026 11:00 WITA', $meta['description']);
    }

    public function test_build_target_meta_uses_assignment_start_time_in_date_text_and_gate(): void
    {
        Carbon::setTestNow('2026-06-27 07:30:00');

        $service = new AssessmentPortalService(
            $this->createMock(AssessmentQuestionRandomizerService::class)
        );

        $target = $this->makeTarget([
            'assignment_start_date' => '2026-06-27',
            'assignment_start_time' => '08:00:00',
            'session_start_at' => null,
            'session_end_at' => null,
        ]);

        $meta = $service->buildTargetMeta($target);

        $this->assertSame('upcoming', $meta['status']);
        $this->assertSame('27 Jun 2026 08:00 WITA - 28 Jun 2026', $meta['date_text']);
        $this->assertStringContainsString('27 Jun 2026 08:00 WITA', $meta['description']);
        $this->assertSame('Jadwal sesi belum ditentukan', $meta['session_schedule_text']);
    }

    public function test_build_target_meta_marks_target_as_expired_once_session_deadline_has_passed(): void
    {
        Carbon::setTestNow('2026-06-27 14:05:00');

        $service = new AssessmentPortalService(
            $this->createMock(AssessmentQuestionRandomizerService::class)
        );

        $target = $this->makeTarget([
            'assignment_start_date' => '2026-06-27',
            'assignment_start_time' => '08:00:00',
            'session_start_at' => '2026-06-27 11:00:00',
            'session_end_at' => '2026-06-27 14:00:00',
        ]);

        $meta = $service->buildTargetMeta($target);

        $this->assertSame('expired', $meta['status']);
        $this->assertFalse($meta['can_open']);
        $this->assertSame('Sudah Ditutup', $meta['label']);
    }

    public function test_build_target_meta_uses_auto_submission_label_for_deadline_finalization(): void
    {
        Carbon::setTestNow('2026-06-27 15:00:00');

        $service = new AssessmentPortalService(
            $this->createMock(AssessmentQuestionRandomizerService::class)
        );

        $target = $this->makeTarget([
            'assignment_start_date' => '2026-06-27',
            'assignment_start_time' => '08:00:00',
            'session_start_at' => '2026-06-27 11:00:00',
            'session_end_at' => '2026-06-27 14:00:00',
        ]);

        $target->setRelation('attempt', new AssessmentAttempt([
            'status' => 'submitted',
            'result_summary' => [
                'submission_mode' => 'deadline_auto',
            ],
        ]));

        $meta = $service->buildTargetMeta($target);

        $this->assertSame('submitted', $meta['status']);
        $this->assertSame('Selesai Otomatis', $meta['label']);
        $this->assertStringContainsString('skor 0', $meta['description']);
    }

    public function test_build_target_meta_uses_draft_stage_preview_when_current_stage_saved_as_draft(): void
    {
        Carbon::setTestNow('2026-07-12 10:00:00+08:00');

        $service = new AssessmentPortalService(
            $this->createMock(AssessmentQuestionRandomizerService::class)
        );

        $target = $this->makeTarget([
            'assignment_start_date' => '2026-07-12',
            'assignment_end_date' => '2026-07-13',
            'assignment_start_time' => null,
            'session_start_at' => null,
            'session_end_at' => null,
            'stage_config' => [
                'enabled' => true,
                'allow_draft' => true,
            ],
        ]);

        $target->setRelation('attempt', new AssessmentAttempt([
            'status' => 'in_progress',
            'total_questions' => 1,
            'required_questions' => 1,
            'structure_snapshot' => [
                'meta' => [
                    'total_questions' => 1,
                    'required_questions' => 1,
                ],
                'assessments' => [
                    [
                        'id' => 7,
                        'kode_assessment' => 'ASM-1',
                        'judul' => 'Assessment Tahap Draft',
                        'instrument_type' => 'portofolio',
                        'stage_config' => [
                            'enabled' => true,
                            'allow_draft' => true,
                        ],
                        'forms' => [
                            [
                                'id' => 11,
                                'fields' => [
                                    [
                                        'id' => 101,
                                        'is_required' => true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'progress_snapshot' => [
                'current_stage_index' => 0,
                'stages' => [
                    [
                        'stage_index' => 0,
                        'status' => 'draft',
                        'started_at' => '2026-07-12T09:00:00+08:00',
                        'deadline_at' => '2026-07-12T11:00:00+08:00',
                        'config' => [
                            'enabled' => true,
                            'allow_draft' => true,
                        ],
                    ],
                ],
            ],
        ]));

        $meta = $service->buildTargetMeta($target);

        $this->assertSame('in_progress', $meta['status']);
        $this->assertSame('Tahap 1 Draft', $meta['label']);
        $this->assertStringContainsString('tersimpan sebagai draft', $meta['description']);
    }

    public function test_build_stage_overview_summarizes_stage_statuses_and_actions(): void
    {
        Carbon::setTestNow('2026-07-12 10:00:00');

        $service = new AssessmentPortalService(
            $this->createMock(AssessmentQuestionRandomizerService::class)
        );

        $target = new AssessmentAssignmentTarget([
            'status' => 'dikerjakan',
        ]);
        $target->setRelation('assignment', new AssessmentAssignment([
            'judul_penugasan' => 'Assessment Nasional Batch 1',
        ]));

        $attempt = new AssessmentAttempt([
            'status' => 'in_progress',
            'structure_snapshot' => [
                'assessments' => [
                    [
                        'id' => 11,
                        'kode_assessment' => 'ASM-1',
                        'judul' => 'Portofolio Kompetensi',
                        'instrument_type' => 'portofolio',
                        'stage_config' => [
                            'enabled' => true,
                            'allow_draft' => true,
                        ],
                        'forms' => [
                            [
                                'id' => 111,
                                'fields' => [
                                    ['id' => 1111, 'is_required' => true],
                                ],
                            ],
                        ],
                    ],
                    [
                        'id' => 12,
                        'kode_assessment' => 'ASM-2',
                        'judul' => 'Studi Kasus',
                        'instrument_type' => 'studi_kasus',
                        'stage_config' => [
                            'enabled' => true,
                            'entry_mode' => 'start_button',
                            'security' => [
                                'enabled' => true,
                                'require_fullscreen' => true,
                            ],
                        ],
                        'forms' => [
                            [
                                'id' => 112,
                                'fields' => [
                                    ['id' => 1121, 'is_required' => true],
                                ],
                            ],
                        ],
                    ],
                    [
                        'id' => 13,
                        'kode_assessment' => 'ASM-3',
                        'judul' => 'Observasi Eviden',
                        'instrument_type' => 'monitoring_observasi_eviden',
                        'stage_config' => [
                            'enabled' => true,
                            'allow_draft' => true,
                        ],
                        'forms' => [
                            [
                                'id' => 113,
                                'fields' => [
                                    ['id' => 1131, 'is_required' => false],
                                ],
                            ],
                        ],
                    ],
                    [
                        'id' => 14,
                        'kode_assessment' => 'ASM-4',
                        'judul' => 'Pilihan Ganda Kompleks',
                        'instrument_type' => 'pilihan_ganda_kompleks',
                        'stage_config' => [
                            'enabled' => true,
                            'lock_until_previous_stages_completed' => true,
                        ],
                        'forms' => [
                            [
                                'id' => 114,
                                'fields' => [
                                    ['id' => 1141, 'is_required' => true],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'progress_snapshot' => [
                'current_stage_index' => 1,
                'stages' => [
                    [
                        'stage_index' => 0,
                        'status' => 'submitted',
                        'submitted_at' => '2026-07-12T09:00:00+08:00',
                        'config' => [
                            'enabled' => true,
                            'allow_draft' => true,
                        ],
                    ],
                    [
                        'stage_index' => 1,
                        'status' => 'in_progress',
                        'started_at' => '2026-07-12T09:15:00+08:00',
                        'deadline_at' => '2026-07-12T11:15:00+08:00',
                        'config' => [
                            'enabled' => true,
                            'entry_mode' => 'start_button',
                            'security' => [
                                'enabled' => true,
                                'require_fullscreen' => true,
                            ],
                        ],
                    ],
                    [
                        'stage_index' => 2,
                        'status' => 'draft',
                        'config' => [
                            'enabled' => true,
                            'allow_draft' => true,
                        ],
                    ],
                    [
                        'stage_index' => 3,
                        'status' => 'locked',
                        'config' => [
                            'enabled' => true,
                            'lock_until_previous_stages_completed' => true,
                        ],
                    ],
                ],
            ],
        ]);

        $overview = $service->buildStageOverview($target, $attempt);

        $this->assertSame(4, $overview['stage_total']);
        $this->assertSame(1, $overview['submitted_total']);
        $this->assertSame(1, $overview['in_progress_total']);
        $this->assertSame(1, $overview['draft_total']);
        $this->assertSame(0, $overview['ready_total']);
        $this->assertSame(1, $overview['available_total']);
        $this->assertSame(1, $overview['locked_total']);
        $this->assertSame(25, $overview['completion_percent']);
        $this->assertSame('Selesai', $overview['stages'][0]['status_label']);
        $this->assertSame('Lanjutkan Tahap', $overview['stages'][1]['action_label']);
        $this->assertTrue($overview['stages'][1]['is_current']);
        $this->assertSame('Draft', $overview['stages'][2]['status_label']);
        $this->assertSame('Lanjutkan Tahap', $overview['stages'][2]['action_label']);
        $this->assertSame('disabled', $overview['stages'][3]['action_mode']);
        $this->assertStringContainsString('fullscreen wajib', $overview['stages'][1]['security_label']);
    }

    private function makeTarget(array $overrides): AssessmentAssignmentTarget
    {
        $field = new AssessmentFormField([
            'id' => 101,
            'is_active' => true,
        ]);

        $form = new AssessmentForm([
            'id' => 11,
            'is_active' => true,
        ]);
        $form->setRelation('fields', collect([$field]));

        $assessment = new Assessment([
            'id' => 7,
            'is_active' => true,
        ]);
        $assessment->setRelation('pivot', new AssessmentAssignmentAssessment([
            'stage_config' => $overrides['stage_config'] ?? [],
            'urutan' => 1,
        ]));
        $assessment->setRelation('forms', collect([$form]));

        $assignment = new AssessmentAssignment([
            'status_distribusi' => 'selesai',
            'tanggal_mulai' => $overrides['assignment_start_date'],
            'tanggal_selesai' => $overrides['assignment_end_date'] ?? '2026-06-28',
            'jam_mulai' => $overrides['assignment_start_time'],
        ]);
        $assignment->setRelation('assessments', collect([$assessment]));

        $session = new AssessmentAssignmentSession([
            'label_sesi' => 'Sesi 1',
            'durasi_sesi_jam' => 3,
            'waktu_mulai' => $overrides['session_start_at'] ? Carbon::parse($overrides['session_start_at']) : null,
            'waktu_selesai' => $overrides['session_end_at'] ? Carbon::parse($overrides['session_end_at']) : null,
        ]);

        $target = new AssessmentAssignmentTarget([
            'status' => 'ditugaskan',
        ]);
        $target->setRelation('assignment', $assignment);
        $target->setRelation('session', $session);
        $target->setRelation('attempt', null);

        return $target;
    }
}
