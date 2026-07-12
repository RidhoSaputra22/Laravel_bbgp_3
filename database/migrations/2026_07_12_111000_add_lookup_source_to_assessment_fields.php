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
            Schema::hasTable('assessment_form_fields') &&
            ! Schema::hasColumn('assessment_form_fields', 'lookup_source')
        ) {
            Schema::table('assessment_form_fields', function (Blueprint $table) {
                $table->string('lookup_source')->nullable()->after('autofill_source');
            });
        }

        if (
            Schema::hasTable('assessment_combination_items') &&
            ! Schema::hasColumn('assessment_combination_items', 'field_lookup_source')
        ) {
            Schema::table('assessment_combination_items', function (Blueprint $table) {
                $table->string('field_lookup_source')->nullable()->after('field_autofill_source');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (
            Schema::hasTable('assessment_combination_items') &&
            Schema::hasColumn('assessment_combination_items', 'field_lookup_source')
        ) {
            Schema::table('assessment_combination_items', function (Blueprint $table) {
                $table->dropColumn('field_lookup_source');
            });
        }

        if (
            Schema::hasTable('assessment_form_fields') &&
            Schema::hasColumn('assessment_form_fields', 'lookup_source')
        ) {
            Schema::table('assessment_form_fields', function (Blueprint $table) {
                $table->dropColumn('lookup_source');
            });
        }
    }
};
