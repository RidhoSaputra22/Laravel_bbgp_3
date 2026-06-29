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
        if (!Schema::hasTable('assessment_assignments')) {
            Schema::create('assessment_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
                $table->string('kode_penugasan')->unique();
                $table->string('judul_penugasan');
                $table->text('deskripsi')->nullable();
                $table->date('tanggal_mulai')->nullable();
                $table->date('tanggal_selesai')->nullable();
                $table->enum('status_distribusi', ['draft', 'diproses', 'selesai', 'gagal'])->default('draft');
                $table->unsignedInteger('total_target')->default(0);
                $table->unsignedInteger('total_ditugaskan')->default(0);
                $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('job_batch_id')->nullable()->index();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();

                $table->index(['assessment_id', 'status_distribusi']);
            });
        }

        if (!Schema::hasTable('assessment_assignment_targets')) {
            Schema::create('assessment_assignment_targets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('assessment_assignment_id')->constrained('assessment_assignments')->cascadeOnDelete();
                $table->foreignId('guru_id')->constrained('gurus')->cascadeOnDelete();
                $table->enum('status', ['ditugaskan', 'dikerjakan', 'selesai', 'dibatalkan'])->default('ditugaskan');
                $table->timestamp('assigned_at')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamps();

                $table->unique(
                    ['assessment_assignment_id', 'guru_id'],
                    'assessment_assignment_target_unique'
                );
                $table->index(['guru_id', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_assignment_targets');
        Schema::dropIfExists('assessment_assignments');
    }
};
