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
        Schema::create('peserta_kegiatans', function (Blueprint $table) {
            $table->id();
            $table->string('id_kegiatan');
            $table->string('nama')->nullable();
            $table->string('no_ktp')->nullable();
            $table->string('nip')->nullable();
            $table->string('alamat')->nullable();
            $table->string('email')->nullable();
            $table->string('mata_pelajaran')->nullable();
            $table->string('signature')->nullable();
            $table->string('status')->nullable();
            $table->enum('status_keikutpesertaan', ['peserta', 'panitia', 'narasumber']);
            $table->string('instansi')->nullable();
            $table->string('golongan')->nullable();
            $table->string('jenis_gol')->nullable();
            $table->string('diluar_gol')->nullable();
            $table->enum('jkl', ['Laki-laki', 'Perempuan'])->nullable();
            $table->string('kelengkapan_peserta_transport')->nullable();
            $table->string('kelengkapan_peserta_biodata')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('no_wa')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->string('agama')->nullable();
            $table->string('pendidikan')->nullable();
            $table->string('alamat_rumah')->nullable();
            $table->string('kabupaten_rumah')->nullable();
            $table->string('npwp')->nullable();
            $table->string('kabupaten');
            $table->string('no_surat_tugas')->nullable();
            $table->date('tgl_surat_tugas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peserta_kegiatans');
    }
};
