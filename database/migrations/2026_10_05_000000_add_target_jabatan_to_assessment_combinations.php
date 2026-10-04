<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['assessment_combination_generations', 'assessment_combinations'] as $tableName) {
            if (! Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'target_jabatan')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->json('target_jabatan')->nullable()->after('target_ketenagaan');
            });
        }
    }

    public function down(): void
    {
        foreach (['assessment_combination_generations', 'assessment_combinations'] as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'target_jabatan')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('target_jabatan');
            });
        }
    }
};
