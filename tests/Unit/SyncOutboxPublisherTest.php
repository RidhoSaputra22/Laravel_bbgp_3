<?php

namespace Tests\Unit;

use App\Services\SyncOutboxPublisher;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SyncOutboxPublisherTest extends TestCase
{
    /** @var array<string, mixed> */
    private array $originalSqliteConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalSqliteConnection = (array) config('database.connections.sqlite', []);
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        DB::purge('sqlite');
        DB::reconnect('sqlite');
        config()->set('assessment_mongodb.outbox_connection', 'sqlite');
        Schema::connection('sqlite')->create('sync_outbox', function (Blueprint $table) {
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

        config()->set('assessment_mongodb.enabled', true);
        config()->set('assessment_mongodb.sync_driver', 'go');
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');
        config()->set('database.connections.sqlite', $this->originalSqliteConnection);
        config()->set('assessment_mongodb.outbox_connection', null);
        parent::tearDown();
    }

    public function test_events_are_coalesced_and_increment_version(): void
    {
        $publisher = app(SyncOutboxPublisher::class);
        $publisher->enqueueTargets([10, 10]);
        $publisher->enqueueTargets([10, 11]);

        $database = DB::connection('sqlite');
        $this->assertSame(2, (int) $database->table('sync_outbox')->count());
        $this->assertSame(2, (int) $database->table('sync_outbox')->where('entity_id', 10)->value('version'));
        $this->assertSame('pending', $database->table('sync_outbox')->where('entity_id', 10)->value('status'));
    }

    public function test_outbox_event_rolls_back_with_source_transaction(): void
    {
        try {
            DB::connection('sqlite')->transaction(function (): void {
                app(SyncOutboxPublisher::class)->enqueueTargets([99]);
                throw new \RuntimeException('rollback');
            });
        } catch (\RuntimeException) {
        }

        $this->assertSame(0, (int) DB::connection('sqlite')->table('sync_outbox')->count());
    }
}
