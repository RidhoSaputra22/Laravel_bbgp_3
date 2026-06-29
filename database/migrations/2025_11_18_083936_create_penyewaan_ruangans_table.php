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
        Schema::create('penyewaan_ruangans', function (Blueprint $table) {
            $table->id();
            $table->enum('tipe_ruangan', ['asrama', 'aula', 'kelas', 'laboratorium']);
            $table->string('nama_ruangan');
            // $table->text('deskripsi')->nullable(); // Khusus untuk asrama
            // // $table->integer('kapasitas')->nullable();
            // // $table->string('lokasi')->nullable();
            
            $table->decimal('harga_per_malam', 15, 2)->nullable();
            // $table->decimal('harga_per_minggu', 15, 2)->nullable();
            // $table->decimal('harga_per_bulan', 15, 2)->nullable();
            // $table->json('fasilitas')->nullable(); // Array fasilitas
            $table->text('rincian_harga')->nullable(); // Detail breakdown harga
            // $table->text('syarat_ketentuan')->nullable();
            
            // $table->boolean('ac')->default(false);
            // $table->boolean('proyektor')->default(false);
            // $table->boolean('wifi')->default(false);
            // $table->boolean('sound_system')->default(false);
            // $table->boolean('whiteboard')->default(false);
            
            $table->string('foto_utama')->nullable();
            // $table->json('galeri_foto')->nullable(); // Multiple photos
            // $table->string('denah_ruangan')->nullable();
            
            $table->enum('status', ['tersedia', 'tidak_tersedia', 'maintenance'])->default('tersedia');
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penyewaan_ruangans');
    }
};
