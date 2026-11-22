<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('rtls') && !Schema::hasColumn('rtls', 'certificate_data')) {
            Schema::table('rtls', function (Blueprint $table) {
                $table->json('certificate_data')->nullable()->after('certificate_file');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('rtls') && Schema::hasColumn('rtls', 'certificate_data')) {
            Schema::table('rtls', function (Blueprint $table) {
                $table->dropColumn('certificate_data');
            });
        }
    }
};
