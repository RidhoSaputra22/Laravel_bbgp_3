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
            
            $table->decimal('harga_per_malam', 15, 2)->nullable();
            $table->text('rincian_harga')->nullable(); // Detail breakdown harga
            
            
            $table->string('foto_utama')->nullable();
            
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
