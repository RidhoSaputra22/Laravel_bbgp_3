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
        if (Schema::hasTable('berkas')) {
            if (!Schema::hasColumn('berkas', 'metode_upload')) {
                if (Schema::hasColumn('berkas', 'metode_uploS')) {
                    Schema::table('berkas', function (Blueprint $table) {
                        $table->renameColumn('metode_uploS', 'metode_upload');
                    });
                } else {
                    Schema::table('berkas', function (Blueprint $table) {
                        $table->string('metode_upload')->nullable();
                    });
                }
            }

            return;
        }

        Schema::create('berkas', function (Blueprint $table) {
            $table->id();
            $table->string('nik');
            $table->string('nama_berkas');
            $table->string('nama_kegiatan')->nullable();
            $table->string('metode_upload')->nullable();
            $table->string('status', 60)->nullable()->default('proses');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('berkas');
    }
};
