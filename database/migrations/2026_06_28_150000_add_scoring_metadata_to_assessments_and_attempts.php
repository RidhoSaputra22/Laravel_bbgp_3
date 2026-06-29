<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const ASSESSOR_USER_FOREIGN_KEY = 'assessment_attempt_answers_assessor_user_id_foreign';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('assessments') && ! Schema::hasColumn('assessments', 'instrument_type')) {
            Schema::table('assessments', function (Blueprint $table) {
                $table->string('instrument_type')->nullable();
            });
        }

        if (Schema::hasTable('assessment_forms') && ! Schema::hasColumn('assessment_forms', 'kompetensi')) {
            Schema::table('assessment_forms', function (Blueprint $table) {
                $table->string('kompetensi')->nullable();
            });
        }

        if (Schema::hasTable('assessment_forms') && ! Schema::hasColumn('assessment_forms', 'indikator_kode')) {
            Schema::table('assessment_forms', function (Blueprint $table) {
                $table->string('indikator_kode')->nullable();
            });
        }

        if (Schema::hasTable('assessment_forms') && ! Schema::hasColumn('assessment_forms', 'indikator_label')) {
            Schema::table('assessment_forms', function (Blueprint $table) {
                $table->string('indikator_label')->nullable();
            });
        }

        if (Schema::hasTable('assessment_forms') && ! Schema::hasColumn('assessment_forms', 'is_scoreable')) {
            Schema::table('assessment_forms', function (Blueprint $table) {
                $table->boolean('is_scoreable')->default(true);
            });
        }

        if (Schema::hasTable('assessment_attempts') && ! Schema::hasColumn('assessment_attempts', 'scoring_summary')) {
            Schema::table('assessment_attempts', function (Blueprint $table) {
                $table->json('scoring_summary')->nullable();
            });
        }

        if (Schema::hasTable('assessment_attempt_answers') && ! Schema::hasColumn('assessment_attempt_answers', 'assessor_score')) {
            Schema::table('assessment_attempt_answers', function (Blueprint $table) {
                $table->unsignedTinyInteger('assessor_score')->nullable();
            });
        }

        if (Schema::hasTable('assessment_attempt_answers') && ! Schema::hasColumn('assessment_attempt_answers', 'assessor_notes')) {
            Schema::table('assessment_attempt_answers', function (Blueprint $table) {
                $table->text('assessor_notes')->nullable();
            });
        }

        if (Schema::hasTable('assessment_attempt_answers') && ! Schema::hasColumn('assessment_attempt_answers', 'assessor_user_id')) {
            Schema::table('assessment_attempt_answers', function (Blueprint $table) {
                $table->unsignedBigInteger('assessor_user_id')->nullable();
            });
        }

        if (
            Schema::hasTable('assessment_attempt_answers') &&
            Schema::hasTable('users') &&
            Schema::hasColumn('assessment_attempt_answers', 'assessor_user_id') &&
            ! $this->foreignKeyExists('assessment_attempt_answers', self::ASSESSOR_USER_FOREIGN_KEY)
        ) {
            Schema::table('assessment_attempt_answers', function (Blueprint $table) {
                $table->foreign('assessor_user_id', self::ASSESSOR_USER_FOREIGN_KEY)
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasTable('assessment_attempt_answers') && ! Schema::hasColumn('assessment_attempt_answers', 'assessor_scored_at')) {
            Schema::table('assessment_attempt_answers', function (Blueprint $table) {
                $table->timestamp('assessor_scored_at')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('assessment_attempt_answers')) {
            $hasAssessorUserForeign = $this->foreignKeyExists(
                'assessment_attempt_answers',
                self::ASSESSOR_USER_FOREIGN_KEY
            );

            $columnsToDrop = [];

            foreach ([
                'assessor_score',
                'assessor_notes',
                'assessor_user_id',
                'assessor_scored_at',
            ] as $column) {
                if (Schema::hasColumn('assessment_attempt_answers', $column)) {
                    $columnsToDrop[] = $column;
                }
            }

            if ($hasAssessorUserForeign || $columnsToDrop !== []) {
                Schema::table('assessment_attempt_answers', function (Blueprint $table) use ($hasAssessorUserForeign, $columnsToDrop) {
                    if ($hasAssessorUserForeign) {
                        $table->dropForeign(self::ASSESSOR_USER_FOREIGN_KEY);
                    }

                    if ($columnsToDrop !== []) {
                        $table->dropColumn($columnsToDrop);
                    }
                });
            }
        }

        if (Schema::hasTable('assessment_attempts') && Schema::hasColumn('assessment_attempts', 'scoring_summary')) {
            Schema::table('assessment_attempts', function (Blueprint $table) {
                $table->dropColumn('scoring_summary');
            });
        }

        if (Schema::hasTable('assessment_forms')) {
            $columnsToDrop = [];

            foreach (['kompetensi', 'indikator_kode', 'indikator_label', 'is_scoreable'] as $column) {
                if (Schema::hasColumn('assessment_forms', $column)) {
                    $columnsToDrop[] = $column;
                }
            }

            if ($columnsToDrop !== []) {
                Schema::table('assessment_forms', function (Blueprint $table) use ($columnsToDrop) {
                    $table->dropColumn($columnsToDrop);
                });
            }
        }

        if (Schema::hasTable('assessments') && Schema::hasColumn('assessments', 'instrument_type')) {
            Schema::table('assessments', function (Blueprint $table) {
                $table->dropColumn('instrument_type');
            });
        }
    }

    private function foreignKeyExists(string $table, string $constraintName): bool
    {
        return DB::table('information_schema.table_constraints')
            ->where('constraint_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('constraint_name', $constraintName)
            ->where('constraint_type', 'FOREIGN KEY')
            ->exists();
    }
};
