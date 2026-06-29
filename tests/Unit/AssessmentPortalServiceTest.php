<?php

namespace Tests\Unit;

use App\Models\Assessment;
use App\Models\AssessmentAssignment;
use App\Models\AssessmentAssignmentSession;
use App\Models\AssessmentAssignmentTarget;
use App\Models\AssessmentAttempt;
use App\Models\AssessmentForm;
use App\Models\AssessmentFormField;
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
        $assessment->setRelation('forms', collect([$form]));

        $assignment = new AssessmentAssignment([
            'status_distribusi' => 'selesai',
            'tanggal_mulai' => $overrides['assignment_start_date'],
            'tanggal_selesai' => '2026-06-28',
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
