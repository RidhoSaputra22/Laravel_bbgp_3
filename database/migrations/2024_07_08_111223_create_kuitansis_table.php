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
            $table->integer('biaya_pergi')->default(0)->nullable();
            $table->integer('biaya_pulang')->default(0)->nullable();
            $table->integer('total_pp')->default(0);
            $table->integer('pajak_bandara')->default(0)->nullable();
            $table->integer('biaya_asal')->default(0)->nullable();
            $table->integer('bea_jarak')->default(0)->nullable();
            $table->integer('biaya_tujuan')->default(0)->nullable();
            $table->integer('total_transport')->default(0);
            $table->integer('potongan')->default(0)->nullable();
            $table->integer('total_penginapan')->default(0);
            $table->integer('total_harian')->default(0);
            $table->integer('jumlah_hari')->nullable();
            $table->integer('total_terima')->default(0);
            $table->integer('biaya_penginapan')->default(0)->nullable();
            $table->integer('uang_harian')->default(0)->nullable();
            $table->integer('jumlah_malam')->nullable();
            $table->integer('bill_malam')->default(0)->nullable();
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
