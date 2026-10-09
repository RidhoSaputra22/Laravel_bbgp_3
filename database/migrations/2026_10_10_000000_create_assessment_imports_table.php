<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('assessment_imports')) {
            return;
        }

        Schema::create('assessment_imports', function (Blueprint $table) {
            $table->id();
            $table->string('disk', 50)->default('assessment_private');
            $table->string('path');
            $table->string('original_name');
            $table->string('status', 20)->default('queued')->index();
            $table->json('summary')->nullable();
            $table->text('error_message')->nullable();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_imports');
    }
};
