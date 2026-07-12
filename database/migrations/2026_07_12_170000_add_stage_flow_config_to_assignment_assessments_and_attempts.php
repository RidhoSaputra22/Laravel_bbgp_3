<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasTable('assessment_assignment_assessments') &&
            ! Schema::hasColumn('assessment_assignment_assessments', 'stage_config')
        ) {
            Schema::table('assessment_assignment_assessments', function (Blueprint $table) {
                $table->json('stage_config')->nullable()->after('urutan');
            });
        }

        if (Schema::hasTable('assessment_attempts') && ! Schema::hasColumn('assessment_attempts', 'progress_snapshot')) {
            Schema::table('assessment_attempts', function (Blueprint $table) {
                $table->json('progress_snapshot')->nullable()->after('security_config_snapshot');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('assessment_attempts') && Schema::hasColumn('assessment_attempts', 'progress_snapshot')) {
            Schema::table('assessment_attempts', function (Blueprint $table) {
                $table->dropColumn('progress_snapshot');
            });
        }

        if (
            Schema::hasTable('assessment_assignment_assessments') &&
            Schema::hasColumn('assessment_assignment_assessments', 'stage_config')
        ) {
            Schema::table('assessment_assignment_assessments', function (Blueprint $table) {
                $table->dropColumn('stage_config');
            });
        }
    }
};
