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
        Schema::create('pegawaiPpnpns', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100)->nullable();
            $table->string('jabatan', 100)->nullable();
            $table->string('nip')->nullable();
            $table->string('nik')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pegawaiPpnpns');
    }
};
