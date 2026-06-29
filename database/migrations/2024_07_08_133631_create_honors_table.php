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
            $table->string('golongan');
            $table->string('jenis_gol');
            $table->integer('jp_realisasi');
            $table->integer('jumlah');
            $table->integer('jumlah_honor');
            $table->integer('potongan');
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
