<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            ! Schema::hasTable('assessment_assignments') ||
            Schema::hasColumn('assessment_assignments', 'target_satuan_pendidikan')
        ) {
            return;
        }

        Schema::table('assessment_assignments', function (Blueprint $table) {
            $table->json('target_satuan_pendidikan')->nullable()->after('target_kabupaten');
        });
    }

    public function down(): void
    {
        if (
            ! Schema::hasTable('assessment_assignments') ||
            ! Schema::hasColumn('assessment_assignments', 'target_satuan_pendidikan')
        ) {
            return;
        }

        Schema::table('assessment_assignments', function (Blueprint $table) {
            $table->dropColumn('target_satuan_pendidikan');
        });
    }
};
