<?php

namespace Tests\Feature;

use App\Http\Controllers\AssessmentCombinationController;
use App\Services\Assessment\AssessmentCombinationGenerationService;
use App\Services\Assessment\AssessmentCombinationService;
use App\Services\AssessmentAssignmentService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\View;
use Mockery;
use Tests\TestCase;

class AssessmentCombinationMonitoringViewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::connection('sqlite')->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_combination_generations', function (Blueprint $table) {
            $table->id();
            $table->string('kode_generate')->nullable();
            $table->string('target_ketenagaan')->nullable();
            $table->unsignedInteger('total_kombinasi')->default(0);
            $table->text('selection_config')->nullable();
            $table->string('status')->default('diproses');
            $table->string('job_batch_id')->nullable();
            $table->unsignedBigInteger('generated_by')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_combinations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_combination_generation_id')->nullable();
            $table->unsignedInteger('generation_sequence')->nullable();
            $table->string('kode_kombinasi')->nullable();
            $table->string('judul')->nullable();
            $table->string('target_ketenagaan')->nullable();
            $table->text('selection_config')->nullable();
            $table->json('structure_snapshot')->nullable();
            $table->unsignedInteger('total_assessments')->default(0);
            $table->unsignedInteger('total_forms')->default(0);
            $table->unsignedInteger('total_questions')->default(0);
            $table->unsignedBigInteger('generated_by')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_combination_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_combination_id');
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_combination_id')->nullable();
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_assignment_targets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_combination_id')->nullable();
            $table->timestamps();
        });

        session()->put('role', 'admin');
        session()->put('user_id', 1);
        session()->put('name', 'Admin Test');
        view()->share('errors', new ViewErrorBag());
    }

    protected function tearDown(): void
    {
        Mockery::close();

        Schema::connection('sqlite')->dropIfExists('assessment_assignment_targets');
        Schema::connection('sqlite')->dropIfExists('assessment_assignments');
        Schema::connection('sqlite')->dropIfExists('assessment_combination_items');
        Schema::connection('sqlite')->dropIfExists('assessment_combinations');
        Schema::connection('sqlite')->dropIfExists('assessment_combination_generations');
        Schema::connection('sqlite')->dropIfExists('users');

        parent::tearDown();
    }

    public function test_generation_show_lists_combinations_without_loading_structure_snapshot(): void
    {
        $generationId = $this->insertGeneration();
        $combinationId = $this->insertCombination($generationId, 1, 'KMB-001');

        DB::table('assessment_combination_items')->insert([
            'assessment_combination_id' => $combinationId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->sql;
        });

        $controller = $this->buildController(
            monitoring: [
                'generated_total' => 1,
                'missing_total' => 0,
                'queue_progress' => 100,
                'retry_available' => false,
                'batch' => [],
                'failed_jobs' => [],
            ]
        );

        $view = $controller->generationShow((string) $generationId);

        $this->assertInstanceOf(View::class, $view);
        $this->assertSame('pages.admin.assessment.combination.generation-show', $view->getName());
        $this->assertStringContainsString('Edit & Reset', $view->render());

        $generation = $view->getData()['generation'];
        $combination = $generation->combinations->first();

        $this->assertNotNull($combination);
        $this->assertFalse(array_key_exists('structure_snapshot', $combination->getAttributes()));
        $this->assertFalse($this->containsLargeJsonColumnReference($queries));
    }

    public function test_index_lists_monitoring_tables_without_loading_large_json_columns(): void
    {
        $generationId = $this->insertGeneration();
        $combinationId = $this->insertCombination($generationId, 1, 'KMB-INDEX-001');

        DB::table('assessment_combination_items')->insert([
            'assessment_combination_id' => $combinationId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->sql;
        });

        $controller = $this->buildController(
            monitoring: [
                'generated_total' => 1,
                'missing_total' => 0,
                'queue_progress' => 100,
                'retry_available' => false,
                'batch' => [],
                'failed_jobs' => [],
            ],
            assignmentUsageCount: 0
        );

        $view = $controller->index();

        $this->assertInstanceOf(View::class, $view);
        $this->assertSame('pages.admin.assessment.combination.index', $view->getName());
        $this->assertStringContainsString('Edit & Reset', $view->render());

        $datas = $view->getData()['datas'];
        $generations = $view->getData()['generations'];
        $data = $datas->first();
        $generation = $generations->first();

        $this->assertNotNull($data);
        $this->assertNotNull($generation);
        $this->assertFalse(array_key_exists('structure_snapshot', $data->getAttributes()));
        $this->assertFalse(array_key_exists('selection_config', $generation->getAttributes()));
        $this->assertFalse($this->containsLargeJsonColumnReference($queries));
    }

    private function buildController(
        array $monitoring,
        int $assignmentUsageCount = 0
    ): AssessmentCombinationController {
        $combinationService = Mockery::mock(AssessmentCombinationService::class);

        $generationService = Mockery::mock(AssessmentCombinationGenerationService::class);
        $generationService->shouldReceive('buildGenerationMonitoring')
            ->andReturn($monitoring);

        $assignmentService = Mockery::mock(AssessmentAssignmentService::class);
        $assignmentService->shouldReceive('countAssignmentsForCombinationGeneration')
            ->andReturn($assignmentUsageCount);

        return new AssessmentCombinationController(
            $combinationService,
            $generationService,
            $assignmentService
        );
    }

    private function insertGeneration(): int
    {
        return (int) DB::table('assessment_combination_generations')->insertGetId([
            'kode_generate' => 'GEN-001',
            'target_ketenagaan' => 'tenaga_pendidik',
            'total_kombinasi' => 1,
            'selection_config' => json_encode([
                'huge' => str_repeat('selection-config-', 400),
            ]),
            'status' => 'selesai',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function insertCombination(int $generationId, int $sequence, string $code): int
    {
        return (int) DB::table('assessment_combinations')->insertGetId([
            'assessment_combination_generation_id' => $generationId,
            'generation_sequence' => $sequence,
            'kode_kombinasi' => $code,
            'judul' => $code,
            'target_ketenagaan' => 'tenaga_pendidik',
            'selection_config' => json_encode([
                'meta' => str_repeat('selection-', 300),
            ]),
            'structure_snapshot' => json_encode([
                'assessments' => [
                    [
                        'id' => 1,
                        'kode_assessment' => 'ASM-001',
                        'judul' => 'Assessment 1',
                        'forms' => [
                            [
                                'id' => 11,
                                'fields' => array_fill(0, 25, [
                                    'id' => 101,
                                    'label' => str_repeat('Field Snapshot ', 20),
                                ]),
                            ],
                        ],
                    ],
                ],
            ]),
            'total_assessments' => 1,
            'total_forms' => 1,
            'total_questions' => 25,
            'generated_at' => now(),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function containsLargeJsonColumnReference(array $queries): bool
    {
        return collect($queries)->contains(function (string $sql) {
            if (! str_contains($sql, 'assessment_combination')) {
                return false;
            }

            return str_contains($sql, 'structure_snapshot')
                || str_contains($sql, 'selection_config');
        });
    }
}
