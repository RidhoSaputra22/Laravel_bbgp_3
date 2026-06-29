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
        Schema::create('internals', function (Blueprint $table) {
            $table->id();
            $table->string('nip');
            $table->string('nik')->nullable();
            $table->string('nama');
            $table->string('jenis');
            $table->string('kegiatan');
            $table->string('tempat')->nullable();
            $table->string('kota')->nullable();
            $table->string('jabatan')->nullable();
            $table->string('golongan')->nullable();
            $table->date('tgl_kegiatan');
            $table->date('tgl_selesai_kegiatan')->nullable();
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('hotel')->nullable();
            $table->unsignedBigInteger('transport_pergi')->default(0);
            $table->unsignedBigInteger('transport_pulang')->default(0);
            $table->unsignedBigInteger('bill_penginapan')->default(0);
            $table->unsignedBigInteger('hari_1')->default(0);
            $table->unsignedBigInteger('hari_2')->default(0);
            $table->unsignedBigInteger('hari_3')->default(0);
            $table->unsignedBigInteger('hari_4')->default(0);
            $table->unsignedBigInteger('hari_5')->default(0);
            $table->unsignedBigInteger('hari_6')->default(0);
            $table->unsignedBigInteger('hari_7')->default(0);
            $table->string('bukti_bill')->nullable();
            $table->enum('is_verif', ['sudah', 'belum'])->default('belum');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internals');
    }
};
