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
        if (! Schema::hasTable('assessments')) {
            Schema::create('assessments', function (Blueprint $table) {
                $table->id();
                $table->string('kode_assessment')->unique();
                $table->string('judul');
                $table->string('slug')->unique();
                $table->text('deskripsi')->nullable();
                $table->text('petunjuk')->nullable();
                $table->enum('status', ['draft', 'publish', 'nonaktif'])->default('draft');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('assessment_forms')) {
            Schema::create('assessment_forms', function (Blueprint $table) {
                $table->id();
                $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
                $table->string('judul_form');
                $table->string('kode_form')->nullable();
                $table->text('deskripsi')->nullable();
                $table->unsignedInteger('urutan')->default(1);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('assessment_form_fields')) {
            Schema::create('assessment_form_fields', function (Blueprint $table) {
                $table->id();
                $table->foreignId('assessment_form_id')->constrained('assessment_forms')->cascadeOnDelete();
                $table->string('label');
                $table->string('nama_field');
                $table->string('tipe_field');
                $table->string('placeholder')->nullable();
                $table->text('bantuan')->nullable();
                $table->json('opsi_field')->nullable();
                $table->text('nilai_default')->nullable();
                $table->json('validasi')->nullable();
                $table->string('lebar_kolom')->default('col-md-6');
                $table->unsignedInteger('urutan')->default(1);
                $table->boolean('is_required')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['assessment_form_id', 'nama_field']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_form_fields');
        Schema::dropIfExists('assessment_forms');
        Schema::dropIfExists('assessments');
    }
};
