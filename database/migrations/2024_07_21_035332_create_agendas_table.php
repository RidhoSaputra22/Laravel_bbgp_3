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
        Schema::create('agendas', function (Blueprint $table) {
            $table->id();
            $table->string('thumbnail');
            $table->string('nama_kegiatan');
            $table->string('tempat_kegiatan');
            $table->date('tgl_kegiatan');
            $table->date('tgl_selesai');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->date('tgl_publish');
            $table->text('deskripsi_kegiatan');
            $table->string('author');
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agendas');
    }
};
