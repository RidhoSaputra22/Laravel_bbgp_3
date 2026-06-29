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
        Schema::create('pegawais', function (Blueprint $table) {
            $table->id();
            $table->string('username')->nullable();
            $table->string('nama_lengkap');
            $table->string('email');
            $table->string('no_ktp');
            $table->string('nip');
            $table->string('tempat_lahir');
            $table->date('tgl_lahir');
            $table->enum('gender', ['Laki-laki', 'Perempuan']);
            $table->string('jabatan');
            $table->string('jenis_pegawai')->nullable();
            $table->string('status');
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
            $table->string('instansi')->default('Kantor BBGTK SulSel');
            $table->string('golongan')->nullable();
            $table->string('jenis_bank');
            $table->string('no_rek');
            $table->enum('is_verif', ['sudah', 'belum']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pegawais');
    }
};
