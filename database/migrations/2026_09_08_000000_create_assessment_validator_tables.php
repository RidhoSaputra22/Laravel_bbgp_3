<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('validator_forms', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->string('status', 20)->default('draft');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('validator_form_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('validator_form_id')->constrained('validator_forms')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();

            $table->index(['validator_form_id', 'sort_order']);
        });

        Schema::create('validator_form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('validator_form_section_id')->constrained('validator_form_sections')->cascadeOnDelete();
            $table->string('label');
            $table->text('description')->nullable();
            $table->string('field_type', 30)->default('likert');
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(true);
            $table->boolean('is_scored')->default(false);
            $table->decimal('max_score', 10, 2)->nullable();
            $table->unsignedInteger('sort_order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['validator_form_section_id', 'sort_order'], 'validator_fields_section_order_idx');
        });

        Schema::create('validator_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('title');
            $table->foreignId('validator_form_id')->constrained('validator_forms')->restrictOnDelete();
            $table->foreignId('assessment_id')->nullable()->constrained('assessments')->nullOnDelete();
            $table->foreignId('validator_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->json('assessment_snapshot');
            $table->json('validator_snapshot');
            $table->string('status', 20)->default('assigned');
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->decimal('score_total', 12, 2)->nullable();
            $table->decimal('score_max', 12, 2)->nullable();
            $table->decimal('score_percentage', 6, 2)->nullable();
            $table->string('recommendation', 30)->nullable();
            $table->text('final_notes')->nullable();
            $table->timestamps();

            $table->index(['validator_user_id', 'status'], 'validator_assignments_user_status_idx');
            $table->index(['assessment_id', 'status'], 'validator_assignments_assessment_status_idx');
        });

        Schema::create('validator_assignment_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('validator_assignment_id')->constrained('validator_assignments')->cascadeOnDelete();
            $table->foreignId('validator_form_field_id')->constrained('validator_form_fields')->restrictOnDelete();
            $table->longText('answer_text')->nullable();
            $table->json('answer_payload')->nullable();
            $table->decimal('score', 10, 2)->nullable();
            $table->timestamps();

            $table->unique(
                ['validator_assignment_id', 'validator_form_field_id'],
                'validator_assignment_field_unique'
            );
        });

        if (Schema::hasTable('jabatan_stake_holders')) {
            if (! DB::table('jabatan_stake_holders')->where('name', 'Validator')->exists()) {
                DB::table('jabatan_stake_holders')->insert([
                    'name' => 'Validator',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('validator_assignment_responses');
        Schema::dropIfExists('validator_assignments');
        Schema::dropIfExists('validator_form_fields');
        Schema::dropIfExists('validator_form_sections');
        Schema::dropIfExists('validator_forms');
    }
};
