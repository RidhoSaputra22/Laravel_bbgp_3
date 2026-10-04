<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('assessments') || Schema::hasColumn('assessments', 'target_jabatan')) {
            return;
        }

        Schema::table('assessments', function (Blueprint $table) {
            $table->json('target_jabatan')->nullable()->after('target_ketenagaan');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('assessments') || ! Schema::hasColumn('assessments', 'target_jabatan')) {
            return;
        }

        Schema::table('assessments', function (Blueprint $table) {
            $table->dropColumn('target_jabatan');
        });
    }
};
