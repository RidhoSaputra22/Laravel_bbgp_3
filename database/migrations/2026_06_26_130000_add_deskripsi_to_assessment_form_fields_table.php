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
            ! Schema::hasColumn('assessment_form_fields', 'deskripsi')
        ) {
            Schema::table('assessment_form_fields', function (Blueprint $table) {
                $table->text('deskripsi')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (
            Schema::hasTable('assessment_form_fields') &&
            Schema::hasColumn('assessment_form_fields', 'deskripsi')
        ) {
            Schema::table('assessment_form_fields', function (Blueprint $table) {
                $table->dropColumn('deskripsi');
            });
        }
    }
};
