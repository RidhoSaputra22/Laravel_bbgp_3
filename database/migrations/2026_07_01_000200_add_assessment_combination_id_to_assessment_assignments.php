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
            Schema::hasTable('assessment_assignments') &&
            ! Schema::hasColumn('assessment_assignments', 'assessment_combination_id')
        ) {
            Schema::table('assessment_assignments', function (Blueprint $table) {
                $table->foreignId('assessment_combination_id')
                    ->nullable()
                    ->after('target_ketenagaan')
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
            Schema::hasTable('assessment_assignments') &&
            Schema::hasColumn('assessment_assignments', 'assessment_combination_id')
        ) {
            Schema::table('assessment_assignments', function (Blueprint $table) {
                $table->dropConstrainedForeignId('assessment_combination_id');
            });
        }
    }
};
