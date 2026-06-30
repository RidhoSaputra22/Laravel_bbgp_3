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
        if (
            Schema::hasTable('assessment_assignment_targets') &&
            ! Schema::hasColumn('assessment_assignment_targets', 'assessment_combination_id')
        ) {
            Schema::table('assessment_assignment_targets', function (Blueprint $table) {
                $table->foreignId('assessment_combination_id')
                    ->nullable()
                    ->after('assessment_assignment_session_id')
                    ->constrained('assessment_combinations')
                    ->restrictOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (
            Schema::hasTable('assessment_assignment_targets') &&
            Schema::hasColumn('assessment_assignment_targets', 'assessment_combination_id')
        ) {
            Schema::table('assessment_assignment_targets', function (Blueprint $table) {
                $table->dropConstrainedForeignId('assessment_combination_id');
            });
        }
    }
};
