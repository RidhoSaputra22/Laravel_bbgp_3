<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasTable('assessment_assignments')
            && ! Schema::hasColumn('assessment_assignments', 'is_active')
        ) {
            Schema::table('assessment_assignments', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('judul_penugasan');
                $table->index('is_active');
            });
        }

        if (Schema::hasTable('assessment_assignments') && Schema::hasColumn('assessment_assignments', 'is_active')) {
            DB::table('assessment_assignments')
                ->whereNull('is_active')
                ->update(['is_active' => true]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('assessment_assignments') && Schema::hasColumn('assessment_assignments', 'is_active')) {
            Schema::table('assessment_assignments', function (Blueprint $table) {
                $table->dropIndex(['is_active']);
                $table->dropColumn('is_active');
            });
        }
    }
};
