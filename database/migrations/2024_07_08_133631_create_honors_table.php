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
        Schema::create('honors', function (Blueprint $table) {
            $table->id();
            $table->string('id_peserta');
            $table->string('kode_anggaran')->nullable();
            $table->string('golongan')->nullable();
            $table->string('jenis_gol')->nullable();
            $table->integer('jp_realisasi')->nullable();
            $table->integer('jumlah')->nullable();
            $table->integer('jumlah_honor')->nullable();
            $table->integer('potongan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('honors');
    }
};
