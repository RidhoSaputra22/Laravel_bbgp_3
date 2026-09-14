<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('assessment_form_fields') && Schema::hasColumn('assessment_form_fields', 'lebar_kolom')) {
            Schema::table('assessment_form_fields', function (Blueprint $table) {
                $table->dropColumn('lebar_kolom');
            });
        }

        if (Schema::hasTable('assessment_combination_items') && Schema::hasColumn('assessment_combination_items', 'field_width')) {
            Schema::table('assessment_combination_items', function (Blueprint $table) {
                $table->dropColumn('field_width');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('assessment_form_fields') && ! Schema::hasColumn('assessment_form_fields', 'lebar_kolom')) {
            Schema::table('assessment_form_fields', function (Blueprint $table) {
                $table->string('lebar_kolom')->nullable();
            });
        }

        if (Schema::hasTable('assessment_combination_items') && ! Schema::hasColumn('assessment_combination_items', 'field_width')) {
            Schema::table('assessment_combination_items', function (Blueprint $table) {
                $table->string('field_width')->nullable();
            });
        }
    }
};
