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
        Schema::create('transportasis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kuitansi_id')->nullable();
            $table->string('asal_transport')->nullable();
            $table->string('tujuan_transport')->nullable();
            $table->string('transportasi')->nullable();
            $table->string('keterangan')->nullable();
            $table->integer('biaya_transport')->nullable();
            $table->timestamps();
    
            $table->foreign('kuitansi_id')->references('id')->on('kuitansis')->onUpdate('cascade')->onDelete('cascade');
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transportasis');
    }
};
