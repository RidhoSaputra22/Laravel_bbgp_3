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
        Schema::create('internal_ppnpns', function (Blueprint $table) {
            $table->id();
            $table->string('id_pegawai');
            $table->string('jabatan');
            $table->string('kegiatan');
            $table->string('tempat');
            $table->string('kabupaten');
            $table->string('tgl_kegiatan');
            $table->string('tgl_selesai_kegiatan');
            $table->string('jam_mulai');
            $table->string('jam_selesai');
            $table->text('deskripsi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internal_ppnpns');
    }
};
