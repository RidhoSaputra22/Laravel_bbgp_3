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
        Schema::create('penomoran_kegiatans', function (Blueprint $table) {
            $table->increments('id');
            $table->string('no_surat')->nullable();
            $table->date('tgl_surat')->nullable();
            $table->string('kode_anggaran')->nullable();
            $table->string('kegiatan_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penomoran_kegiatans');
    }
};
