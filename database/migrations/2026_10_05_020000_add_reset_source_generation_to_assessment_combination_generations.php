<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const RESET_SOURCE_INDEX = 'acg_reset_source_idx';

    public function up(): void
    {
        if (! Schema::hasTable('assessment_combination_generations')) {
            return;
        }

        if (! Schema::hasColumn('assessment_combination_generations', 'reset_source_generation_id')) {
            Schema::table('assessment_combination_generations', function (Blueprint $table) {
                $table->unsignedBigInteger('reset_source_generation_id')
                    ->nullable()
                    ->after('job_batch_id');
            });
        }

        if (! Schema::hasIndex('assessment_combination_generations', self::RESET_SOURCE_INDEX)) {
            Schema::table('assessment_combination_generations', function (Blueprint $table) {
                $table->index('reset_source_generation_id', self::RESET_SOURCE_INDEX);
            });
        }
    }

    public function down(): void
    {
        if (
            Schema::hasTable('assessment_combination_generations')
            && Schema::hasColumn('assessment_combination_generations', 'reset_source_generation_id')
        ) {
            Schema::table('assessment_combination_generations', function (Blueprint $table) {
                if (Schema::hasIndex('assessment_combination_generations', self::RESET_SOURCE_INDEX)) {
                    $table->dropIndex(self::RESET_SOURCE_INDEX);
                }
                $table->dropColumn('reset_source_generation_id');
            });
        }
    }
};
