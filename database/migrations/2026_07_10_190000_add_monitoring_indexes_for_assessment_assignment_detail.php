<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('gurus')) {
            $this->addIndexIfMissing('gurus', 'gurus_kabupaten_idx', function (Blueprint $table) {
                $table->index('kabupaten', 'gurus_kabupaten_idx');
            });
            $this->addIndexIfMissing('gurus', 'gurus_eksternal_jabatan_idx', function (Blueprint $table) {
                $table->index('eksternal_jabatan', 'gurus_eksternal_jabatan_idx');
            });
            $this->addIndexIfMissing('gurus', 'gurus_satuan_pendidikan_idx', function (Blueprint $table) {
                $table->index('satuan_pendidikan', 'gurus_satuan_pendidikan_idx');
            });
        }

        if (Schema::hasTable('assessment_assignment_targets')) {
            $this->addIndexIfMissing(
                'assessment_assignment_targets',
                'assessment_assignment_targets_assignment_status_id_idx',
                function (Blueprint $table) {
                    $table->index(
                        ['assessment_assignment_id', 'status', 'id'],
                        'assessment_assignment_targets_assignment_status_id_idx'
                    );
                }
            );
            $this->addIndexIfMissing(
                'assessment_assignment_targets',
                'assessment_assignment_targets_assignment_session_idx',
                function (Blueprint $table) {
                    $table->index(
                        ['assessment_assignment_id', 'assessment_assignment_session_id'],
                        'assessment_assignment_targets_assignment_session_idx'
                    );
                }
            );
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('assessment_assignment_targets')) {
            $this->dropIndexIfExists(
                'assessment_assignment_targets',
                'assessment_assignment_targets_assignment_status_id_idx'
            );
            $this->dropIndexIfExists(
                'assessment_assignment_targets',
                'assessment_assignment_targets_assignment_session_idx'
            );
        }

        if (Schema::hasTable('gurus')) {
            $this->dropIndexIfExists('gurus', 'gurus_kabupaten_idx');
            $this->dropIndexIfExists('gurus', 'gurus_eksternal_jabatan_idx');
            $this->dropIndexIfExists('gurus', 'gurus_satuan_pendidikan_idx');
        }
    }

    private function addIndexIfMissing(string $tableName, string $indexName, \Closure $callback): void
    {
        if ($this->indexExists($tableName, $indexName)) {
            return;
        }

        Schema::table($tableName, $callback);
    }

    private function dropIndexIfExists(string $tableName, string $indexName): void
    {
        if (! $this->indexExists($tableName, $indexName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($indexName) {
            $table->dropIndex($indexName);
        });
    }

    private function indexExists(string $tableName, string $indexName): bool
    {
        $rows = DB::select(
            "SHOW INDEX FROM `{$tableName}` WHERE Key_name = ?",
            [$indexName]
        );

        return $rows !== [];
    }
};
