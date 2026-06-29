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
        Schema::create('gurus', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lengkap');
            $table->string('email');
            $table->string('no_ktp');
            $table->string('nip');
            $table->string('tempat_lahir');
            $table->date('tgl_lahir');
            $table->enum('gender', ['Laki-laki', 'Perempuan']);
            $table->string('jabatan');
            $table->enum('status', ['Kawin', 'Belum Kawin']);
            $table->string('status_kepegawaian');
            $table->enum('agama', ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Budha', 'Konghucu']);
            $table->string('pendidikan');
            $table->string('kabupaten');
            $table->string('satuan_pendidikan');
            $table->string('alamat_satuan');
            $table->string('alamat_rumah');
            $table->string('no_hp');
            $table->string('no_wa');
            $table->string('pas_foto');
            $table->string('no_rek');
            $table->string('jenis_bank')->nullable();
            $table->string('npsn_sekolah');
            $table->string('npwp');
            $table->string('nuptk');
            $table->string('eksternal_jabatan');
            $table->string('jenis_jabatan');
            $table->string('kategori_jabatan');
            $table->string('tugas_jabatan');
            $table->string('latar_jabatan')->nullable();
            $table->enum('is_verif', ['sudah', 'belum']);


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gurus');
    }
};
