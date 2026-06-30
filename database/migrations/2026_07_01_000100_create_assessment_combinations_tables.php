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
        if (! Schema::hasTable('assessment_combinations')) {
            Schema::create('assessment_combinations', function (Blueprint $table) {
                $table->id();
                $table->string('kode_kombinasi')->unique();
                $table->string('judul');
                $table->text('deskripsi')->nullable();
                $table->string('target_ketenagaan')->index();
                $table->string('random_seed')->nullable();
                $table->string('signature_hash', 64)->nullable()->index();
                $table->json('selection_config')->nullable();
                $table->json('structure_snapshot')->nullable();
                $table->unsignedInteger('total_assessments')->default(0);
                $table->unsignedInteger('total_forms')->default(0);
                $table->unsignedInteger('total_questions')->default(0);
                $table->foreignId('generated_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
                $table->timestamp('generated_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('assessment_combination_items')) {
            Schema::create('assessment_combination_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('assessment_combination_id')
                    ->constrained('assessment_combinations')
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
                $table->string('assessment_code')->nullable();
                $table->string('assessment_title')->nullable();
                $table->string('instrument_type')->nullable();
                $table->string('form_code')->nullable();
                $table->string('form_title');
                $table->text('form_description')->nullable();
                $table->string('kompetensi')->nullable();
                $table->string('indikator_kode')->nullable();
                $table->string('indikator_label')->nullable();
                $table->boolean('form_is_scoreable')->default(true);
                $table->json('form_scoring_config')->nullable();
                $table->string('field_label');
                $table->text('field_description')->nullable();
                $table->string('field_name');
                $table->string('field_type');
                $table->string('field_placeholder')->nullable();
                $table->text('field_help')->nullable();
                $table->json('field_options')->nullable();
                $table->json('field_validation')->nullable();
                $table->json('field_scoring_config')->nullable();
                $table->string('field_width')->default('col-md-12');
                $table->boolean('field_is_required')->default(false);
                $table->unsignedInteger('assessment_order')->default(1);
                $table->unsignedInteger('form_order')->default(1);
                $table->unsignedInteger('field_order')->default(1);
                $table->timestamps();

                $table->unique(
                    ['assessment_combination_id', 'assessment_form_field_id'],
                    'assessment_combination_item_field_unique'
                );
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_combination_items');
        Schema::dropIfExists('assessment_combinations');
    }
};
