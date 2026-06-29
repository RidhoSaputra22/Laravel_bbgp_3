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
        Schema::create('pendampings', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('kota');
            $table->string('hotel');
            $table->bigInteger('transport_pulang');
            $table->bigInteger('transport_pergi');
            $table->bigInteger('hari_1');
            $table->bigInteger('hari_2');
            $table->bigInteger('hari_3');
            $table->enum('is_verif', ['sudah', 'belum']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendampings');
    }
};
