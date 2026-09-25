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
            $table->string('provinsi')->nullable()->default('-');
            $table->string('kecamatan');
            $table->string('kabupaten');
            $table->text('alamat')->nullable();
            $table->string('akreditasi')->nullable()->default('-');
            $table->string('no_telepon')->nullable()->default('-');
            $table->string('email')->nullable()->default('-');
            $table->string('website_url')->nullable()->default('-');
            $table->string('tahun_berdiri')->nullable()->default('-');
            $table->string('koordinat')->nullable()->default('-');

            // Data Kepala Sekolah
            $table->string('nama_kepsek')->default('-');
            $table->enum('asn_opsi', ['ya', 'tidak', '-'])->default('-');
            $table->string('nip_kepsek')->nullable()->default('-');
            $table->string('no_sk')->nullable();
            $table->string('no_telp_kepsek')->default('-');
            $table->string('email_kepsek')->nullable();

            // Data Guru
            $table->integer('jumlah_guru')->nullable()->default(0);
            $table->integer('jumlah_guru_pns')->nullable()->default(0);
            $table->integer('jumlah_honorer')->nullable()->default(0);
            $table->integer('jumlah_kependidikan')->nullable()->default(0);
            $table->text('bidang_studi')->nullable();

            // Data Siswa
            $table->integer('jumlah_siswa')->nullable()->default(0);
            $table->integer('jumlah_siswa_pria')->nullable()->default(0);
            $table->integer('jumlah_siswa_perempuan')->nullable()->default(0);
            $table->text('jumlah_siswa_per_kelas')->nullable();

            // Fasilitas
            $table->integer('jumlah_kelas')->nullable()->default(0);
            $table->string('laboratorium')->default('-');
            $table->string('perpustakaan')->default('-');
            $table->string('ruang_guru')->default('-');
            $table->integer('jumlah_toilet')->default(0);
            $table->string('lapangan_olahraga')->default('-');
            $table->json('fasilitas_it')->nullable();
            $table->string('fasilitas_it_tambahan')->nullable();
            $table->string('akses_internet')->default('-');

            // Program
            $table->text('ekstrakurikuler')->nullable();
            $table->text('program_unggulan')->nullable();
            $table->string('jam_belajar')->default('-');

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
