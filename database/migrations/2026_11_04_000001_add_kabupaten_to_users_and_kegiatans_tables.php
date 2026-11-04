<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('kabupaten')->nullable()->after('role');
        });

        Schema::table('kegiatans', function (Blueprint $table): void {
            $table->string('kabupaten')->nullable()->after('deskripsi_kegiatan');
        });

        DB::table('users')->update(['kabupaten' => null]);
    }

    public function down(): void
    {
        Schema::table('kegiatans', function (Blueprint $table): void {
            $table->dropColumn('kabupaten');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('kabupaten');
        });
    }
};
