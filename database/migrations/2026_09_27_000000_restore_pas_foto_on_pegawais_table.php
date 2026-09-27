<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pegawais') && ! Schema::hasColumn('pegawais', 'pas_foto')) {
            Schema::table('pegawais', function (Blueprint $table) {
                $table->string('pas_foto')->nullable()->after('no_wa');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pegawais') && Schema::hasColumn('pegawais', 'pas_foto')) {
            Schema::table('pegawais', function (Blueprint $table) {
                $table->dropColumn('pas_foto');
            });
        }
    }
};
