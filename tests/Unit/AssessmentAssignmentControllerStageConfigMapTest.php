<?php

namespace Tests\Unit;

use App\Http\Controllers\AssessmentAssignmentController;
use App\Models\Assessment;
use App\Models\AssessmentAssignment;
use App\Services\Assessment\AssessmentAttemptService;
use App\Services\Assessment\AssessmentMonitoringService;
use App\Services\AssessmentAssignmentService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class AssessmentAssignmentControllerStageConfigMapTest extends TestCase
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
            $table->string('kode_penugasan')->nullable();
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessments', function (Blueprint $table) {
            $table->id();
            $table->string('kode_assessment')->nullable();
            $table->string('judul')->nullable();
            $table->string('status')->nullable();
            $table->string('instrument_type')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_assignment_assessments', function (Blueprint $table) {
            $table->unsignedBigInteger('assessment_assignment_id');
            $table->unsignedBigInteger('assessment_id');
            $table->unsignedInteger('urutan')->default(1);
            $table->json('stage_config')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::connection('sqlite')->dropIfExists('assessment_assignment_assessments');
        Schema::connection('sqlite')->dropIfExists('assessments');
        Schema::connection('sqlite')->dropIfExists('assessment_assignments');

        parent::tearDown();
    }

    public function test_build_assignment_stage_config_map_preserves_opened_stage_lock_state_in_edit_mode(): void
    {
        $assignment = AssessmentAssignment::query()->create([
            'kode_penugasan' => 'ASG-EDIT-001',
        ]);
        $firstStage = Assessment::query()->create([
            'kode_assessment' => 'ASM-000',
            'judul' => 'Tahap 1',
            'status' => 'publish',
            'instrument_type' => 'portofolio',
            'is_active' => true,
        ]);
        $assessment = Assessment::query()->create([
            'kode_assessment' => 'ASM-001',
            'judul' => 'Tahap 2',
            'status' => 'publish',
            'instrument_type' => 'pilihan_ganda_kompleks',
            'is_active' => true,
        ]);

        DB::table('assessment_assignment_assessments')->insert([
            'assessment_assignment_id' => $assignment->id,
            'assessment_id' => $firstStage->id,
            'urutan' => 1,
            'stage_config' => json_encode([
                'enabled' => true,
                'entry_mode' => 'direct',
                'allow_draft' => true,
                'finalize_mode' => 'manual',
                'admin_gate_enabled' => false,
                'lock_until_previous_stages_completed' => false,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('assessment_assignment_assessments')->insert([
            'assessment_assignment_id' => $assignment->id,
            'assessment_id' => $assessment->id,
            'urutan' => 2,
            'stage_config' => json_encode([
                'enabled' => true,
                'entry_mode' => 'direct',
                'allow_draft' => false,
                'finalize_mode' => 'auto',
                'admin_gate_enabled' => true,
                'lock_until_previous_stages_completed' => false,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $controller = new AssessmentAssignmentController(
            $this->createMock(AssessmentAssignmentService::class),
            $this->createMock(AssessmentMonitoringService::class),
            $this->createMock(AssessmentAttemptService::class),
        );

        $method = new ReflectionMethod(AssessmentAssignmentController::class, 'buildAssignmentStageConfigMap');
        $method->setAccessible(true);

        $configMap = $method->invoke($controller, $assignment->fresh('assessments'));

        $this->assertTrue((bool) data_get($configMap, $assessment->id . '.admin_gate_enabled'));
        $this->assertFalse((bool) data_get($configMap, $assessment->id . '.lock_until_previous_stages_completed'));
    }

    public function test_build_assignment_stage_config_map_falls_back_to_default_gate_for_legacy_opened_stage(): void
    {
        $assignment = AssessmentAssignment::query()->create([
            'kode_penugasan' => 'ASG-EDIT-LEGACY',
        ]);
        $firstStage = Assessment::query()->create([
            'kode_assessment' => 'ASM-100',
            'judul' => 'Tahap 1 Legacy',
            'status' => 'publish',
            'instrument_type' => 'portofolio',
            'is_active' => true,
        ]);
        $assessment = Assessment::query()->create([
            'kode_assessment' => 'ASM-002',
            'judul' => 'Tahap 2 Legacy',
            'status' => 'publish',
            'instrument_type' => 'pilihan_ganda_kompleks',
            'is_active' => true,
        ]);

        DB::table('assessment_assignment_assessments')->insert([
            'assessment_assignment_id' => $assignment->id,
            'assessment_id' => $firstStage->id,
            'urutan' => 1,
            'stage_config' => json_encode([
                'enabled' => true,
                'entry_mode' => 'direct',
                'allow_draft' => true,
                'finalize_mode' => 'manual',
                'lock_until_previous_stages_completed' => false,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('assessment_assignment_assessments')->insert([
            'assessment_assignment_id' => $assignment->id,
            'assessment_id' => $assessment->id,
            'urutan' => 2,
            'stage_config' => json_encode([
                'enabled' => true,
                'entry_mode' => 'direct',
                'allow_draft' => false,
                'finalize_mode' => 'auto',
                'lock_until_previous_stages_completed' => false,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $controller = new AssessmentAssignmentController(
            $this->createMock(AssessmentAssignmentService::class),
            $this->createMock(AssessmentMonitoringService::class),
            $this->createMock(AssessmentAttemptService::class),
        );

        $method = new ReflectionMethod(AssessmentAssignmentController::class, 'buildAssignmentStageConfigMap');
        $method->setAccessible(true);

        $configMap = $method->invoke($controller, $assignment->fresh('assessments'));

        $this->assertTrue((bool) data_get($configMap, $assessment->id . '.admin_gate_enabled'));
        $this->assertTrue((bool) data_get($configMap, $assessment->id . '.lock_until_previous_stages_completed'));
    }
}
