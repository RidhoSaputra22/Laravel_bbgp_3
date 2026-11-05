<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('gurus')) {
            return;
        }

        Schema::table('gurus', function (Blueprint $table): void {
            if (! Schema::hasColumn('gurus', 'kabupaten_rumah')) {
                $table->string('kabupaten_rumah')->nullable()->after('alamat_rumah');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('gurus') || ! Schema::hasColumn('gurus', 'kabupaten_rumah')) {
            return;
        }

        Schema::table('gurus', function (Blueprint $table): void {
            $table->dropColumn('kabupaten_rumah');
        });
    }
};
