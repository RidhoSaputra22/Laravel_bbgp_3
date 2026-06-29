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
        Schema::create('kuitansis', function (Blueprint $table) {
            $table->id();
            $table->string('pegawai_id');
            $table->string('no_bukti');
            $table->string('no_MAK');
            $table->string('no_surat_tugas');
            $table->date('tgl_surat_tugas');
            $table->string('tahun_anggaran');
            $table->string('lokasi_asal');
            $table->string('lokasi_tujuan');
            $table->string('jenis_angkutan');
            $table->integer('biaya_pergi');
            $table->integer('biaya_pulang');
            $table->integer('total_pp');
            $table->integer('pajak_bandara');
            $table->integer('biaya_asal');
            $table->integer('bea_jarak');
            $table->integer('biaya_tujuan');
            $table->integer('total_transport');
            // $table->integer('biaya_penginapan');
            // $table->integer('uang_harian');
            $table->integer('potongan');
            $table->integer('total_penginapan');
            $table->integer('total_harian');
            $table->integer('jumlah_hari');
            $table->integer('total_terima');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kuitansis');
    }
};
