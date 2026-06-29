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
        if (! Schema::hasTable('assessment_attempts')) {
            Schema::create('assessment_attempts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('assessment_assignment_target_id')
                    ->constrained('assessment_assignment_targets')
                    ->cascadeOnDelete();
                $table->enum('status', ['draft', 'in_progress', 'submitted'])->default('draft');
                $table->json('structure_snapshot')->nullable();
                $table->json('result_summary')->nullable();
                $table->unsignedInteger('total_questions')->default(0);
                $table->unsignedInteger('required_questions')->default(0);
                $table->unsignedInteger('answered_questions')->default(0);
                $table->unsignedInteger('answered_required_questions')->default(0);
                $table->timestamp('started_at')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('last_answered_at')->nullable();
                $table->timestamps();

                $table->unique(
                    ['assessment_assignment_target_id'],
                    'assessment_attempt_target_unique'
                );
            });
        }

        if (! Schema::hasTable('assessment_attempt_answers')) {
            Schema::create('assessment_attempt_answers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('assessment_attempt_id')
                    ->constrained('assessment_attempts')
                    ->cascadeOnDelete();
                $table->foreignId('assessment_id')
                    ->nullable()
                    ->constrained('assessments')
                    ->nullOnDelete();
                $table->foreignId('assessment_form_id')
                    ->nullable()
                    ->constrained('assessment_forms')
                    ->nullOnDelete();
                $table->foreignId('assessment_form_field_id')
                    ->nullable()
                    ->constrained('assessment_form_fields')
                    ->nullOnDelete();
                $table->longText('answer_text')->nullable();
                $table->json('answer_payload')->nullable();
                $table->string('answer_file_path')->nullable();
                $table->timestamp('answered_at')->nullable();
                $table->timestamps();

                $table->unique(
                    ['assessment_attempt_id', 'assessment_form_field_id'],
                    'assessment_attempt_answers_unique'
                );
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_attempt_answers');
        Schema::dropIfExists('assessment_attempts');
    }
};
