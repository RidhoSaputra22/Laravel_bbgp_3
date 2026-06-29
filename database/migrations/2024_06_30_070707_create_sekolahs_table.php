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
        Schema::create('sekolahs', function (Blueprint $table) {
            $table->id();

            $table->integer('user_id')->nullable();

            $table->string('nama_sekolah');
            $table->string('npsn_sekolah');
            $table->string('bp_sekolah');
            $table->string('status_sekolah');
            $table->string('provinsi')->default('-');
            $table->string('kecamatan');
            $table->string('kabupaten');
            $table->text('alamat')->nullable();
            $table->string('akreditasi')->default('-');
            $table->string('no_telepon')->default('-');
            $table->string('email')->default('-');
            $table->string('website_url')->default('-');
            $table->string('tahun_berdiri')->default('-');
            $table->string('koordinat')->default('-');

            // Data Kepala Sekolah
            $table->string('nama_kepsek');
            $table->enum('asn_opsi', ['ya', 'tidak']);
            $table->string('nip_kepsek')->nullable();
            $table->string('no_sk')->nullable();
            $table->string('no_telp_kepsek');
            $table->string('email_kepsek')->nullable();

            // Data Guru
            $table->integer('jumlah_guru')->default(0);
            $table->integer('jumlah_guru_pns')->default(0);
            $table->integer('jumlah_honorer')->default(0);
            $table->integer('jumlah_kependidikan')->default(0);
            $table->text('bidang_studi')->nullable();

            // Data Siswa
            $table->integer('jumlah_siswa')->default(0);
            $table->integer('jumlah_siswa_pria')->default(0);
            $table->integer('jumlah_siswa_perempuan')->default(0);
            $table->text('jumlah_siswa_per_kelas')->nullable();

            // Fasilitas
            $table->integer('jumlah_kelas')->default(0);
            $table->string('laboratorium');
            $table->string('perpustakaan');
            $table->string('ruang_guru');
            $table->integer('jumlah_toilet');
            $table->string('lapangan_olahraga');
            $table->json('fasilitas_it')->nullable();
            $table->string('akses_internet');

            // Program
            $table->text('ekstrakurikuler');
            $table->text('program_unggulan');
            $table->string('jam_belajar');

            // Dokumen
            $table->string('foto_depan')->nullable();
            $table->string('logo_sekolah')->nullable();
            $table->string('denah_lokasi')->nullable();
            $table->string('struktur_organisasi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sekolahs');
    }
};
