<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('assessment_assignments') || Schema::hasColumn('assessment_assignments', 'target_jabatan')) {
            return;
        }

        Schema::table('assessment_assignments', function (Blueprint $table) {
            $table->json('target_jabatan')->nullable()->after('target_ketenagaan');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('assessment_assignments') || ! Schema::hasColumn('assessment_assignments', 'target_jabatan')) {
            return;
        }

        Schema::table('assessment_assignments', function (Blueprint $table) {
            $table->dropColumn('target_jabatan');
        });
    }
};
