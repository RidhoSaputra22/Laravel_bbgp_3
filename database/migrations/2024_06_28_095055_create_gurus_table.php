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
            $table->string('nama_lengkap')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->string('no_ktp')->nullable();
            $table->string('nip')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->enum('gender', ['Laki-laki', 'Perempuan'])->nullable();
            $table->string('jabatan')->nullable();
            $table->enum('status', ['Kawin', 'Belum Kawin'])->nullable();
            $table->string('status_kepegawaian')->nullable();
            $table->enum('agama', ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Budha', 'Konghucu'])->nullable();
            $table->string('pendidikan')->nullable();
            $table->string('kabupaten')->nullable();
            $table->string('satuan_pendidikan')->nullable();
            $table->string('alamat_satuan')->nullable();
            $table->string('alamat_rumah')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('no_wa')->nullable();
            $table->string('pas_foto')->nullable();
            $table->string('no_rek')->nullable();
            $table->enum('jenis_bank', [
                'Bank BCA', 'Bank BRI', 'Bank BNI', 'Bank Mandiri',
                'Bank BTN', 'Bank Syariah Indonesia', 'Bank SulSelBar', 'Tidak ada',
            ])->nullable();
            $table->string('npsn_sekolah')->nullable();
            $table->string('npwp')->nullable();
            $table->string('nuptk')->nullable();
            $table->string('eksternal_jabatan')->nullable();
            $table->string('jenis_jabatan')->nullable();
            $table->string('kategori_jabatan')->nullable();
            $table->string('tugas_jabatan')->nullable();
            $table->string('latar_jabatan')->nullable();
            $table->enum('is_verif', ['sudah', 'belum'])->nullable()->default('belum');
            $table->string('jenis_data')->nullable();


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
