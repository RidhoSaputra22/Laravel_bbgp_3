<?php

namespace Tests\Unit;

use App\Models\Assessment;
use App\Models\AssessmentAssignment;
use App\Services\AssessmentAssignmentService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AssessmentAssignmentServiceStageAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::connection('sqlite')->create('assessment_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('kode_penugasan');
            $table->string('judul_penugasan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessments', function (Blueprint $table) {
            $table->id();
            $table->string('kode_assessment');
            $table->string('judul');
            $table->string('status');
            $table->string('target_ketenagaan')->nullable();
            $table->string('instrument_type')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_assignment_assessments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_assignment_id');
            $table->unsignedBigInteger('assessment_id');
            $table->unsignedInteger('urutan')->default(1);
            $table->json('stage_config')->nullable();
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_assignment_targets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_assignment_id')->nullable();
            $table->string('status')->nullable();
            $table->timestamp('deadline_at')->nullable();
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_assignment_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_assignment_id')->nullable();
            $table->unsignedInteger('nomor_sesi')->default(1);
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_assignment_target_id')->nullable();
            $table->string('status')->nullable();
            $table->json('structure_snapshot')->nullable();
            $table->json('progress_snapshot')->nullable();
            $table->timestamp('deadline_at')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::connection('sqlite')->dropIfExists('assessment_attempts');
        Schema::connection('sqlite')->dropIfExists('assessment_assignment_sessions');
        Schema::connection('sqlite')->dropIfExists('assessment_assignment_targets');
        Schema::connection('sqlite')->dropIfExists('assessment_assignment_assessments');
        Schema::connection('sqlite')->dropIfExists('assessments');
        Schema::connection('sqlite')->dropIfExists('assessment_assignments');

        parent::tearDown();
    }

    public function test_build_stage_access_summary_uses_open_all_action_for_locked_stages(): void
    {
        $assignment = $this->createAssignmentWithLockedStages();
        $summary = app(AssessmentAssignmentService::class)->buildStageAccessSummary($assignment);

        $this->assertSame(3, $summary['total_stages']);
        $this->assertSame(2, $summary['locked_stage_total']);
        $this->assertTrue($summary['has_pending_admin_open']);
        $this->assertSame('2 tahap menunggu dibuka admin', $summary['status_label']);
        $this->assertSame('Buka Semua Tahap', $summary['action_label']);
        $this->assertSame(
            'Tahap 2, Tahap 3 masih dikunci dan akan dibuka sekaligus untuk peserta.',
            $summary['action_description']
        );
    }

    public function test_open_all_locked_stages_unlocks_every_pending_stage(): void
    {
        $assignment = $this->createAssignmentWithLockedStages();
        $result = app(AssessmentAssignmentService::class)->openAllLockedStages($assignment);
        $reloadedAssignment = $assignment->fresh('assessments');
        $summary = app(AssessmentAssignmentService::class)->buildStageAccessSummary($reloadedAssignment);

        $this->assertCount(2, $result['opened_stages'] ?? []);
        $this->assertSame(0, $result['synced_attempt_count'] ?? null);
        $this->assertFalse($summary['has_pending_admin_open']);
        $this->assertSame('Semua tahap terbuka', $summary['status_label']);
        $this->assertSame(0, $summary['locked_stage_total']);
        $this->assertFalse((bool) data_get($reloadedAssignment->assessments, '1.pivot.stage_config.lock_until_previous_stages_completed'));
        $this->assertFalse((bool) data_get($reloadedAssignment->assessments, '2.pivot.stage_config.lock_until_previous_stages_completed'));
    }

    private function createAssignmentWithLockedStages(): AssessmentAssignment
    {
        $assignment = AssessmentAssignment::query()->create([
            'kode_penugasan' => 'ASG-001',
            'judul_penugasan' => 'Penugasan Uji Tahap',
            'is_active' => true,
        ]);

        collect([
            ['code' => 'ASM-1', 'title' => 'Tahap 1', 'order' => 1, 'locked' => false],
            ['code' => 'ASM-2', 'title' => 'Tahap 2', 'order' => 2, 'locked' => true],
            ['code' => 'ASM-3', 'title' => 'Tahap 3', 'order' => 3, 'locked' => true],
        ])->each(function (array $stage) use ($assignment) {
            $assessment = Assessment::query()->create([
                'kode_assessment' => $stage['code'],
                'judul' => $stage['title'],
                'status' => 'publish',
                'target_ketenagaan' => 'tenaga_pendidik',
                'instrument_type' => 'portofolio',
                'is_active' => true,
            ]);

            DB::table('assessment_assignment_assessments')->insert([
                'assessment_assignment_id' => $assignment->id,
                'assessment_id' => $assessment->id,
                'urutan' => $stage['order'],
                'stage_config' => json_encode([
                    'enabled' => true,
                    'entry_mode' => 'direct',
                    'allow_draft' => true,
                    'finalize_mode' => 'manual',
                    'lock_until_previous_stages_completed' => $stage['locked'],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return $assignment->fresh('assessments');
    }
}
