<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kegiatans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kegiatan');
            $table->string('tempat_kegiatan');
            $table->date('tgl_kegiatan');
            $table->date('tgl_selesai')->nullable();
            $table->time('jam_mulai');
            $table->time('jam_selesai')->nullable();
            $table->text('deskripsi_kegiatan')->nullable();
            $table->enum('status', ['true', 'false'])->default('false');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kegiatans');
    }
};
