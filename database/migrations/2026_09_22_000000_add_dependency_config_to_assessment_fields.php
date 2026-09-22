<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasTable('assessment_form_fields')
            && ! Schema::hasColumn('assessment_form_fields', 'dependency_config')
        ) {
            Schema::table('assessment_form_fields', function (Blueprint $table) {
                $table->json('dependency_config')->nullable()->after('lookup_source');
            });
        }

        if (
            Schema::hasTable('assessment_combination_items')
            && ! Schema::hasColumn('assessment_combination_items', 'field_dependency_config')
        ) {
            Schema::table('assessment_combination_items', function (Blueprint $table) {
                $table->json('field_dependency_config')->nullable()->after('field_lookup_source');
            });
        }
    }

    public function down(): void
    {
        if (
            Schema::hasTable('assessment_combination_items')
            && Schema::hasColumn('assessment_combination_items', 'field_dependency_config')
        ) {
            Schema::table('assessment_combination_items', function (Blueprint $table) {
                $table->dropColumn('field_dependency_config');
            });
        }

        if (
            Schema::hasTable('assessment_form_fields')
            && Schema::hasColumn('assessment_form_fields', 'dependency_config')
        ) {
            Schema::table('assessment_form_fields', function (Blueprint $table) {
                $table->dropColumn('dependency_config');
            });
        }
    }
};
