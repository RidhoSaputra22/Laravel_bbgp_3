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
            ! Schema::hasColumn('assessment_form_fields', 'autofill_source')
        ) {
            Schema::table('assessment_form_fields', function (Blueprint $table) {
                $table->string('autofill_source')->nullable()->after('nilai_default');
            });
        }

        if (
            Schema::hasTable('assessment_combination_items') &&
            ! Schema::hasColumn('assessment_combination_items', 'field_autofill_source')
        ) {
            Schema::table('assessment_combination_items', function (Blueprint $table) {
                $table->string('field_autofill_source')->nullable()->after('field_help');
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
            Schema::hasColumn('assessment_combination_items', 'field_autofill_source')
        ) {
            Schema::table('assessment_combination_items', function (Blueprint $table) {
                $table->dropColumn('field_autofill_source');
            });
        }

        if (
            Schema::hasTable('assessment_form_fields') &&
            Schema::hasColumn('assessment_form_fields', 'autofill_source')
        ) {
            Schema::table('assessment_form_fields', function (Blueprint $table) {
                $table->dropColumn('autofill_source');
            });
        }
    }
};
