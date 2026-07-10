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
            ! Schema::hasColumn('assessment_assignments', 'session_enabled')
        ) {
            Schema::table('assessment_assignments', function (Blueprint $table) {
                $table->boolean('session_enabled')
                    ->default(true)
                    ->after('judul_penugasan');
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
            Schema::hasColumn('assessment_assignments', 'session_enabled')
        ) {
            Schema::table('assessment_assignments', function (Blueprint $table) {
                $table->dropColumn('session_enabled');
            });
        }
    }
};
