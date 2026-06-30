<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const GENERATION_FOREIGN_KEY = 'assessment_comb_generation_fk';
    private const GENERATION_SEQUENCE_UNIQUE = 'assessment_combination_generation_sequence_unique';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('assessment_combinations')) {
            return;
        }

        if (
            ! Schema::hasColumn('assessment_combinations', 'assessment_combination_generation_id') ||
            ! Schema::hasColumn('assessment_combinations', 'generation_sequence')
        ) {
            Schema::table('assessment_combinations', function (Blueprint $table) {
                if (! Schema::hasColumn('assessment_combinations', 'assessment_combination_generation_id')) {
                    $table->foreignId('assessment_combination_generation_id')
                        ->nullable()
                        ->after('id');
                }

                if (! Schema::hasColumn('assessment_combinations', 'generation_sequence')) {
                    $table->unsignedInteger('generation_sequence')
                        ->nullable()
                        ->after('assessment_combination_generation_id');
                }
            });
        }

        if (! $this->hasForeignKey('assessment_combinations', self::GENERATION_FOREIGN_KEY)) {
            Schema::table('assessment_combinations', function (Blueprint $table) {
                $table->foreign('assessment_combination_generation_id', self::GENERATION_FOREIGN_KEY)
                    ->references('id')
                    ->on('assessment_combination_generations')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasIndex('assessment_combinations', self::GENERATION_SEQUENCE_UNIQUE)) {
            Schema::table('assessment_combinations', function (Blueprint $table) {
                $table->unique(
                    ['assessment_combination_generation_id', 'generation_sequence'],
                    self::GENERATION_SEQUENCE_UNIQUE
                );
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('assessment_combinations')) {
            return;
        }

        if (Schema::hasIndex('assessment_combinations', self::GENERATION_SEQUENCE_UNIQUE)) {
            Schema::table('assessment_combinations', function (Blueprint $table) {
                $table->dropUnique(self::GENERATION_SEQUENCE_UNIQUE);
            });
        }

        if ($this->hasForeignKey('assessment_combinations', self::GENERATION_FOREIGN_KEY)) {
            Schema::table('assessment_combinations', function (Blueprint $table) {
                $table->dropForeign(self::GENERATION_FOREIGN_KEY);
            });
        }

        if (
            Schema::hasColumn('assessment_combinations', 'generation_sequence') ||
            Schema::hasColumn('assessment_combinations', 'assessment_combination_generation_id')
        ) {
            Schema::table('assessment_combinations', function (Blueprint $table) {
                if (Schema::hasColumn('assessment_combinations', 'generation_sequence')) {
                    $table->dropColumn('generation_sequence');
                }

                if (Schema::hasColumn('assessment_combinations', 'assessment_combination_generation_id')) {
                    $table->dropColumn('assessment_combination_generation_id');
                }
            });
        }
    }

    private function hasForeignKey(string $table, string $name): bool
    {
        return collect(Schema::getForeignKeys($table))
            ->contains(fn (array $foreignKey) => ($foreignKey['name'] ?? null) === $name);
    }
};
