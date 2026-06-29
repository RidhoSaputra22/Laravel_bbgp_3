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
        if (Schema::hasTable('assessments') && ! Schema::hasColumn('assessments', 'scoring_config')) {
            Schema::table('assessments', function (Blueprint $table) {
                $table->json('scoring_config')->nullable();
            });
        }

        if (Schema::hasTable('assessment_forms') && ! Schema::hasColumn('assessment_forms', 'scoring_config')) {
            Schema::table('assessment_forms', function (Blueprint $table) {
                $table->json('scoring_config')->nullable();
            });
        }

        if (Schema::hasTable('assessment_form_fields') && ! Schema::hasColumn('assessment_form_fields', 'scoring_config')) {
            Schema::table('assessment_form_fields', function (Blueprint $table) {
                $table->json('scoring_config')->nullable();
            });
        }

        if (Schema::hasTable('assessment_attempt_answers') && ! Schema::hasColumn('assessment_attempt_answers', 'auto_score')) {
            Schema::table('assessment_attempt_answers', function (Blueprint $table) {
                $table->decimal('auto_score', 5, 2)->nullable();
            });
        }

        if (Schema::hasTable('assessment_attempt_answers') && ! Schema::hasColumn('assessment_attempt_answers', 'auto_score_reason')) {
            Schema::table('assessment_attempt_answers', function (Blueprint $table) {
                $table->text('auto_score_reason')->nullable();
            });
        }

        if (Schema::hasTable('assessment_attempt_answers') && ! Schema::hasColumn('assessment_attempt_answers', 'auto_score_metadata')) {
            Schema::table('assessment_attempt_answers', function (Blueprint $table) {
                $table->json('auto_score_metadata')->nullable();
            });
        }

        if (Schema::hasTable('assessment_attempt_answers') && ! Schema::hasColumn('assessment_attempt_answers', 'auto_scored_at')) {
            Schema::table('assessment_attempt_answers', function (Blueprint $table) {
                $table->timestamp('auto_scored_at')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('assessment_attempt_answers')) {
            $columnsToDrop = [];

            foreach (['auto_score', 'auto_score_reason', 'auto_score_metadata', 'auto_scored_at'] as $column) {
                if (Schema::hasColumn('assessment_attempt_answers', $column)) {
                    $columnsToDrop[] = $column;
                }
            }

            if ($columnsToDrop !== []) {
                Schema::table('assessment_attempt_answers', function (Blueprint $table) use ($columnsToDrop) {
                    $table->dropColumn($columnsToDrop);
                });
            }
        }

        if (Schema::hasTable('assessment_form_fields') && Schema::hasColumn('assessment_form_fields', 'scoring_config')) {
            Schema::table('assessment_form_fields', function (Blueprint $table) {
                $table->dropColumn('scoring_config');
            });
        }

        if (Schema::hasTable('assessment_forms') && Schema::hasColumn('assessment_forms', 'scoring_config')) {
            Schema::table('assessment_forms', function (Blueprint $table) {
                $table->dropColumn('scoring_config');
            });
        }

        if (Schema::hasTable('assessments') && Schema::hasColumn('assessments', 'scoring_config')) {
            Schema::table('assessments', function (Blueprint $table) {
                $table->dropColumn('scoring_config');
            });
        }
    }
};
