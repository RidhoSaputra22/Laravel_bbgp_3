<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sync_outbox')) {
            return;
        }

        Schema::create('sync_outbox', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 64);
            $table->unsignedBigInteger('entity_id');
            $table->unsignedBigInteger('version')->default(1);
            $table->string('status', 20)->default('pending');
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('available_at')->nullable()->index();
            $table->timestamp('locked_at')->nullable();
            $table->string('locked_by', 100)->nullable();
            $table->text('last_error')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->unique(['entity_type', 'entity_id'], 'sync_outbox_entity_unique');
            $table->index(['status', 'available_at', 'id'], 'sync_outbox_claim_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_outbox');
    }
};
