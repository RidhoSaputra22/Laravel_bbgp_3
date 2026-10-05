<?php

namespace Tests\Unit;

use App\Services\Assessment\AssessmentTargetSyncDispatcher;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AssessmentTargetSyncDispatcherTest extends TestCase
{
    private mixed $originalDefaultConnection;

    private array $originalSqliteConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalDefaultConnection = config('database.default');
        $this->originalSqliteConnection = (array) config('database.connections.sqlite', []);
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        config()->set('assessment_mongodb.enabled', true);
        config()->set('assessment_mongodb.sync_driver', 'go');
        config()->set('assessment_mongodb.outbox_connection', 'sqlite');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('sync_outbox', function (Blueprint $table): void {
            $table->id();
            $table->string('entity_type', 64);
            $table->unsignedBigInteger('entity_id');
            $table->unsignedBigInteger('version')->default(1);
            $table->string('status', 20)->default('pending');
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('available_at')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->string('locked_by', 100)->nullable();
            $table->text('last_error')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->unique(['entity_type', 'entity_id']);
        });

        Schema::create('assessment_forms', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('assessment_id');
        });
        Schema::create('assessment_form_fields', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('assessment_form_id');
        });
        Schema::create('assessment_assignment_assessments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('assessment_assignment_id');
            $table->unsignedBigInteger('assessment_id');
        });
        Schema::create('assessment_assignment_targets', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('assessment_assignment_id');
            $table->boolean('is_validator')->default(false);
        });
        Schema::create('validator_assignments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('assessment_id')->nullable();
        });
        Schema::create('validator_assignment_assessment_assignments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('validator_assignment_id');
            $table->unsignedBigInteger('assessment_assignment_id');
        });
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');
        config()->set('database.default', $this->originalDefaultConnection);
        config()->set('database.connections.sqlite', $this->originalSqliteConnection);
        config()->set('assessment_mongodb.outbox_connection', null);
        parent::tearDown();
    }

    public function test_assessment_field_change_queues_related_validator_assignments(): void
    {
        DB::table('assessment_forms')->insert(['id' => 10, 'assessment_id' => 20]);
        DB::table('assessment_form_fields')->insert(['id' => 1, 'assessment_form_id' => 10]);
        DB::table('assessment_assignment_assessments')->insert([
            'id' => 30,
            'assessment_assignment_id' => 40,
            'assessment_id' => 20,
        ]);
        DB::table('assessment_assignment_targets')->insert([
            'id' => 50,
            'assessment_assignment_id' => 40,
            'is_validator' => false,
        ]);
        DB::table('validator_assignments')->insert([
            ['id' => 60, 'assessment_id' => 20],
            ['id' => 61, 'assessment_id' => 999],
        ]);
        DB::table('validator_assignment_assessment_assignments')->insert([
            'validator_assignment_id' => 61,
            'assessment_assignment_id' => 40,
        ]);

        app(AssessmentTargetSyncDispatcher::class)->fields([1]);

        $events = DB::table('sync_outbox')
            ->orderBy('entity_type')
            ->orderBy('entity_id')
            ->get(['entity_type', 'entity_id'])
            ->map(fn ($event) => [$event->entity_type, (int) $event->entity_id])
            ->all();

        $this->assertSame([
            ['assessment_target', 50],
            ['validator_assignment', 60],
            ['validator_assignment', 61],
        ], $events);
    }
}
