<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('validator_assignments', function (Blueprint $table) {
            $table->json('assessment_assignment_snapshots')->nullable()->after('assessment_snapshot');
        });

        Schema::create('validator_assignment_assessment_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('validator_assignment_id');
            $table->foreignId('assessment_assignment_id');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->foreign('validator_assignment_id', 'va_source_validator_fk')
                ->references('id')->on('validator_assignments')->cascadeOnDelete();
            $table->foreign('assessment_assignment_id', 'va_source_assignment_fk')
                ->references('id')->on('assessment_assignments')->cascadeOnDelete();
            $table->unique(['validator_assignment_id', 'assessment_assignment_id'], 'validator_assignment_source_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('validator_assignment_assessment_assignments');
        Schema::table('validator_assignments', function (Blueprint $table) {
            $table->dropColumn('assessment_assignment_snapshots');
        });
    }
};
