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
            $table->string('nama')->nullable();
            $table->string('nip')->nullable();
            $table->string('nik');
            $table->string('kegiatan')->nullable();
            $table->string('tempat')->nullable();
            $table->date('tgl_kegiatan')->nullable();
            $table->date('tgl_selesai_kegiatan')->nullable();
            $table->string('jenis')->nullable();
            $table->string('golongan')->nullable();
            $table->string('jabatan')->nullable();
            $table->enum('is_verif', ['sudah', 'belum'])->nullable()->default('sudah');
            $table->string('jenis_data')->nullable();
            $table->string('kota')->nullable();
            $table->integer('transport_pulang')->nullable();
            $table->integer('transport_pergi')->nullable();
            $table->string('hotel')->nullable();
            $table->integer('hari_1')->default(0);
            $table->integer('hari_2')->default(0);
            $table->integer('hari_3')->default(0);
            $table->integer('hari_4')->default(0);
            $table->integer('hari_5')->default(0);
            $table->integer('hari_6')->default(0);
            $table->integer('hari_7')->default(0);
            $table->integer('bill_penginapan')->default(0);
            $table->time('jam_mulai')->nullable()->default('00:00:00');
            $table->time('jam_selesai')->nullable()->default('00:00:00');
            $table->text('deskripsi')->nullable();
            $table->string('bukti_bill')->nullable();
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
