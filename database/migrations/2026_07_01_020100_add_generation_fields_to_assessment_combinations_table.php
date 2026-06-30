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
            return;
        }

        Schema::table('assessment_combinations', function (Blueprint $table) {
            if (! Schema::hasColumn('assessment_combinations', 'assessment_combination_generation_id')) {
                $table->foreignId('assessment_combination_generation_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('assessment_combination_generations')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('assessment_combinations', 'generation_sequence')) {
                $table->unsignedInteger('generation_sequence')
                    ->nullable()
                    ->after('assessment_combination_generation_id');
            }

            $table->unique(
                ['assessment_combination_generation_id', 'generation_sequence'],
                'assessment_combination_generation_sequence_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('assessment_combinations')) {
            return;
        }

        Schema::table('assessment_combinations', function (Blueprint $table) {
            $table->dropUnique('assessment_combination_generation_sequence_unique');

            if (Schema::hasColumn('assessment_combinations', 'generation_sequence')) {
                $table->dropColumn('generation_sequence');
            }

            if (Schema::hasColumn('assessment_combinations', 'assessment_combination_generation_id')) {
                $table->dropConstrainedForeignId('assessment_combination_generation_id');
            }
        });
    }
};
