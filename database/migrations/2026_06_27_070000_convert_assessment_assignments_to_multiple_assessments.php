<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private const ASSIGNMENT_ASSESSMENT_TABLE = 'assessment_assignment_assessments';

    private const LEGACY_ASSESSMENT_FOREIGN = 'assessment_assignments_assessment_id_foreign';

    private const LEGACY_ASSESSMENT_STATUS_INDEX = 'assessment_assignments_assessment_id_status_distribusi_index';

    private const ASSIGNMENT_ASSESSMENT_UNIQUE = 'assignment_assessment_unique';

    private const ASSIGNMENT_LINK_FOREIGN = 'aaa_assignment_fk';

    private const ASSESSMENT_LINK_FOREIGN = 'aaa_assessment_fk';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('assessment_assignments')) {
            return;
        }

        $this->ensureAssignmentAssessmentTable();

        $this->backfillLegacyAssessmentAssignments();

        if (!Schema::hasColumn('assessment_assignments', 'assessment_id')) {
            return;
        }

        $this->dropForeignKeysForColumn('assessment_assignments', 'assessment_id');

        if ($this->indexExists('assessment_assignments', self::LEGACY_ASSESSMENT_STATUS_INDEX)) {
            Schema::table('assessment_assignments', function (Blueprint $table) {
                $table->dropIndex(self::LEGACY_ASSESSMENT_STATUS_INDEX);
            });
        }

        Schema::table('assessment_assignments', function (Blueprint $table) {
            $table->dropColumn('assessment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('assessment_assignments') && !Schema::hasColumn('assessment_assignments', 'assessment_id')) {
            Schema::table('assessment_assignments', function (Blueprint $table) {
                $table->foreignId('assessment_id')
                    ->nullable()
                    ->constrained('assessments')
                    ->cascadeOnDelete();
                $table->index(
                    ['assessment_id', 'status_distribusi'],
                    self::LEGACY_ASSESSMENT_STATUS_INDEX
                );
            });

            $this->restoreLegacyAssessmentAssignments();
        }

        Schema::dropIfExists(self::ASSIGNMENT_ASSESSMENT_TABLE);
    }

    private function ensureAssignmentAssessmentTable(): void
    {
        if (!Schema::hasTable(self::ASSIGNMENT_ASSESSMENT_TABLE)) {
            Schema::create(self::ASSIGNMENT_ASSESSMENT_TABLE, function (Blueprint $table) {
                $table->id();
                $table->foreignId('assessment_assignment_id');
                $table->foreignId('assessment_id');
                $table->unsignedInteger('urutan')->default(1);
                $table->timestamps();

                $table->unique(
                    ['assessment_assignment_id', 'assessment_id'],
                    self::ASSIGNMENT_ASSESSMENT_UNIQUE
                );
            });
        } else {
            $this->addColumnIfMissing(
                self::ASSIGNMENT_ASSESSMENT_TABLE,
                'assessment_assignment_id',
                fn (Blueprint $table) => $table->unsignedBigInteger('assessment_assignment_id')->nullable()
            );
            $this->addColumnIfMissing(
                self::ASSIGNMENT_ASSESSMENT_TABLE,
                'assessment_id',
                fn (Blueprint $table) => $table->unsignedBigInteger('assessment_id')->nullable()
            );
            $this->addColumnIfMissing(
                self::ASSIGNMENT_ASSESSMENT_TABLE,
                'urutan',
                fn (Blueprint $table) => $table->unsignedInteger('urutan')->default(1)
            );
            $this->addColumnIfMissing(
                self::ASSIGNMENT_ASSESSMENT_TABLE,
                'created_at',
                fn (Blueprint $table) => $table->timestamp('created_at')->nullable()
            );
            $this->addColumnIfMissing(
                self::ASSIGNMENT_ASSESSMENT_TABLE,
                'updated_at',
                fn (Blueprint $table) => $table->timestamp('updated_at')->nullable()
            );
        }

        if (
            Schema::hasTable('assessment_assignments') &&
            Schema::hasColumn(self::ASSIGNMENT_ASSESSMENT_TABLE, 'assessment_assignment_id') &&
            !$this->foreignKeyExists(self::ASSIGNMENT_ASSESSMENT_TABLE, self::ASSIGNMENT_LINK_FOREIGN)
        ) {
            Schema::table(self::ASSIGNMENT_ASSESSMENT_TABLE, function (Blueprint $table) {
                $table->foreign('assessment_assignment_id', self::ASSIGNMENT_LINK_FOREIGN)
                    ->references('id')
                    ->on('assessment_assignments')
                    ->cascadeOnDelete();
            });
        }

        if (
            Schema::hasTable('assessments') &&
            Schema::hasColumn(self::ASSIGNMENT_ASSESSMENT_TABLE, 'assessment_id') &&
            !$this->foreignKeyExists(self::ASSIGNMENT_ASSESSMENT_TABLE, self::ASSESSMENT_LINK_FOREIGN)
        ) {
            Schema::table(self::ASSIGNMENT_ASSESSMENT_TABLE, function (Blueprint $table) {
                $table->foreign('assessment_id', self::ASSESSMENT_LINK_FOREIGN)
                    ->references('id')
                    ->on('assessments')
                    ->cascadeOnDelete();
            });
        }
    }

    private function backfillLegacyAssessmentAssignments(): void
    {
        if (
            !Schema::hasTable(self::ASSIGNMENT_ASSESSMENT_TABLE) ||
            !Schema::hasColumn('assessment_assignments', 'assessment_id') ||
            ! $this->hasColumns('assessment_assignments', ['id', 'assessment_id', 'created_at', 'updated_at']) ||
            ! $this->hasColumns(self::ASSIGNMENT_ASSESSMENT_TABLE, [
                'assessment_assignment_id',
                'assessment_id',
                'urutan',
                'created_at',
                'updated_at',
            ])
        ) {
            return;
        }

        $assignments = DB::table('assessment_assignments')
            ->whereNotNull('assessment_id')
            ->orderBy('id')
            ->get([
                'id',
                'assessment_id',
                'created_at',
                'updated_at',
            ]);

        if ($assignments->isEmpty()) {
            return;
        }

        DB::table(self::ASSIGNMENT_ASSESSMENT_TABLE)->upsert(
            $assignments
                ->map(function ($assignment) {
                    $timestamp = $assignment->updated_at ?? $assignment->created_at ?? now();

                    return [
                        'assessment_assignment_id' => $assignment->id,
                        'assessment_id' => $assignment->assessment_id,
                        'urutan' => 1,
                        'created_at' => $assignment->created_at ?? $timestamp,
                        'updated_at' => $timestamp,
                    ];
                })
                ->all(),
            ['assessment_assignment_id', 'assessment_id'],
            ['urutan', 'updated_at']
        );
    }

    private function restoreLegacyAssessmentAssignments(): void
    {
        if (
            !Schema::hasTable(self::ASSIGNMENT_ASSESSMENT_TABLE) ||
            !Schema::hasColumn('assessment_assignments', 'assessment_id') ||
            ! $this->hasColumns(self::ASSIGNMENT_ASSESSMENT_TABLE, ['assessment_assignment_id', 'assessment_id']) ||
            ! Schema::hasColumn('assessment_assignments', 'id')
        ) {
            return;
        }

        $rows = DB::table(self::ASSIGNMENT_ASSESSMENT_TABLE)
            ->orderBy('assessment_assignment_id')
            ->orderBy('urutan')
            ->orderBy('id')
            ->get(['assessment_assignment_id', 'assessment_id']);

        foreach ($rows->groupBy('assessment_assignment_id') as $assignmentId => $assignmentRows) {
            $firstAssessmentId = $assignmentRows->first()->assessment_id ?? null;

            if (!$firstAssessmentId) {
                continue;
            }

            DB::table('assessment_assignments')
                ->where('id', $assignmentId)
                ->update([
                    'assessment_id' => $firstAssessmentId,
                ]);
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

    private function dropForeignKeysForColumn(string $table, string $column): void
    {
        $rows = DB::select("
        SELECT CONSTRAINT_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = ?
          AND TABLE_NAME = ?
          AND COLUMN_NAME = ?
          AND REFERENCED_TABLE_NAME IS NOT NULL
    ", [
            DB::getDatabaseName(),
            $table,
            $column,
        ]);

        $constraintNames = collect($rows)
            ->map(fn($row) => $row->CONSTRAINT_NAME ?? $row->constraint_name ?? null)
            ->filter()
            ->unique()
            ->values();

        foreach ($constraintNames as $constraintName) {
            Schema::table($table, function (Blueprint $table) use ($constraintName) {
                $table->dropForeign($constraintName);
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->exists();
    }

    private function addColumnIfMissing(string $tableName, string $column, callable $definition): void
    {
        if (Schema::hasColumn($tableName, $column)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($definition) {
            $definition($table);
        });
    }

    private function hasColumns(string $tableName, array $columns): bool
    {
        foreach ($columns as $column) {
            if (! Schema::hasColumn($tableName, $column)) {
                return false;
            }
        }

        return true;
    }
};
