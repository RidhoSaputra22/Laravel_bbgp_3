<?php

namespace Tests\Unit;

use App\Http\Controllers\AssessmentAssignmentController;
use App\Services\Assessment\AssessmentAttemptService;
use App\Services\Assessment\AssessmentMonitoringService;
use App\Services\AssessmentAssignmentService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class AssessmentAssignmentControllerStageSummaryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

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

        Schema::connection('sqlite')->create('assessment_forms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_id');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_form_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_form_id');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('gurus', function (Blueprint $table) {
            $table->id();
            $table->string('eksternal_jabatan')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::connection('sqlite')->dropIfExists('gurus');
        Schema::connection('sqlite')->dropIfExists('assessment_form_fields');
        Schema::connection('sqlite')->dropIfExists('assessment_forms');
        Schema::connection('sqlite')->dropIfExists('assessments');

        parent::tearDown();
    }

    public function test_build_ketenagaan_summaries_uses_instrument_defaults_in_stage_order(): void
    {
        DB::table('assessments')->insert([
            [
                'id' => 31,
                'kode_assessment' => 'ASM-PGK',
                'judul' => 'A. Tes Pilihan Ganda Kompleks Kompetensi Guru',
                'status' => 'publish',
                'target_ketenagaan' => 'tenaga_pendidik',
                'instrument_type' => 'pilihan_ganda_kompleks',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 32,
                'kode_assessment' => 'ASM-SK',
                'judul' => 'B. Studi Kasus Pemetaan Kompetensi Guru',
                'status' => 'publish',
                'target_ketenagaan' => 'tenaga_pendidik',
                'instrument_type' => 'studi_kasus',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 33,
                'kode_assessment' => 'ASM-PORT',
                'judul' => 'Z. Instrumen Portofolio Kompetensi Guru',
                'status' => 'publish',
                'target_ketenagaan' => 'tenaga_pendidik',
                'instrument_type' => 'portofolio',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('assessment_forms')->insert([
            ['assessment_id' => 31, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['assessment_id' => 32, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['assessment_id' => 33, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('assessment_form_fields')->insert([
            ['assessment_form_id' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['assessment_form_id' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['assessment_form_id' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('gurus')->insert([
            ['eksternal_jabatan' => 'Tenaga Pendidik', 'created_at' => now(), 'updated_at' => now()],
            ['eksternal_jabatan' => 'Tenaga Pendidik', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $controller = new AssessmentAssignmentController(
            $this->createMock(AssessmentAssignmentService::class),
            $this->createMock(AssessmentMonitoringService::class),
            $this->createMock(AssessmentAttemptService::class),
        );

        $method = new ReflectionMethod(AssessmentAssignmentController::class, 'buildKetenagaanSummaries');
        $method->setAccessible(true);

        $summaries = $method->invoke($controller);
        $items = $summaries['tenaga_pendidik']['assessment_items'] ?? [];

        $this->assertSame([33, 32, 31], array_column($items, 'id'));
        $this->assertTrue((bool) data_get($items, '0.default_stage_config.allow_draft'));
        $this->assertTrue((bool) data_get($items, '1.default_stage_config.lock_until_previous_stages_completed'));
        $this->assertSame('start_button', data_get($items, '1.default_stage_config.entry_mode'));
        $this->assertTrue((bool) data_get($items, '2.default_stage_config.lock_until_previous_stages_completed'));
        $this->assertSame(90, data_get($items, '2.default_stage_config.time_limit_minutes'));
    }
}
