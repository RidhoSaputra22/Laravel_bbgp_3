<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('assessment_combination_generations')) {
            Schema::create('assessment_combination_generations', function (Blueprint $table) {
                $table->id();
                $table->string('kode_generate')->unique();
                $table->string('target_ketenagaan')->index();
                $table->unsignedInteger('total_kombinasi')->default(1);
                $table->json('selection_config')->nullable();
                $table->string('status')->default('diproses')->index();
                $table->string('job_batch_id')->nullable()->index();
                $table->foreignId('generated_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_combination_generations');
    }
};
