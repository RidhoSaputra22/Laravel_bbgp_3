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
        Schema::create('kuitansi_lokas', function (Blueprint $table) {
            $table->id();
            $table->string('internal_id');
            $table->string('no_surat_tugas');
            $table->string('tgl_surat_tugas');
            $table->string('kode_anggaran');
            $table->string('tahun_anggaran');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kuitansi_lokas');
    }
};
