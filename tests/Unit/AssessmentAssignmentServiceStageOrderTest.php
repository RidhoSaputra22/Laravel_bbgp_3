<?php

namespace Tests\Unit;

use App\Enum\AssessmentKetenagaanType;
use App\Models\Assessment;
use App\Services\AssessmentAssignmentService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class AssessmentAssignmentServiceStageOrderTest extends TestCase
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
    }

    protected function tearDown(): void
    {
        Schema::connection('sqlite')->dropIfExists('assessments');

        parent::tearDown();
    }

    public function test_resolve_assessment_ids_uses_default_stage_order_instead_of_title_order(): void
    {
        $pilihanGanda = Assessment::query()->create([
            'kode_assessment' => 'ASM-PGK',
            'judul' => 'A. Tes Pilihan Ganda Kompleks Kompetensi Guru',
            'status' => 'publish',
            'target_ketenagaan' => 'tenaga_pendidik',
            'instrument_type' => 'pilihan_ganda_kompleks',
            'is_active' => true,
        ]);

        $studiKasus = Assessment::query()->create([
            'kode_assessment' => 'ASM-SK',
            'judul' => 'B. Studi Kasus Pemetaan Kompetensi Guru',
            'status' => 'publish',
            'target_ketenagaan' => 'tenaga_pendidik',
            'instrument_type' => 'studi_kasus',
            'is_active' => true,
        ]);

        $portofolio = Assessment::query()->create([
            'kode_assessment' => 'ASM-PORT',
            'judul' => 'Z. Instrumen Portofolio Kompetensi Guru',
            'status' => 'publish',
            'target_ketenagaan' => 'tenaga_pendidik',
            'instrument_type' => 'portofolio',
            'is_active' => true,
        ]);

        $method = new ReflectionMethod(AssessmentAssignmentService::class, 'resolveAssessmentIds');
        $method->setAccessible(true);

        $assessmentIds = $method->invoke(
            app(AssessmentAssignmentService::class),
            [],
            AssessmentKetenagaanType::TENAGA_PENDIDIK,
            collect()
        );

        $this->assertSame(
            [$portofolio->id, $studiKasus->id, $pilihanGanda->id],
            $assessmentIds
        );
    }
}
