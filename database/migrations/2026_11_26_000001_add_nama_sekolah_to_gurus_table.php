<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('gurus', function (Blueprint $table): void {
            $table->string('nama_sekolah')->nullable()->after('npsn_sekolah');
        });
    }

    public function down(): void
    {
        Schema::table('gurus', function (Blueprint $table): void {
            $table->dropColumn('nama_sekolah');
        });
    }
};
