<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('assessment_assignment_targets')) {
            return;
        }

        if (! Schema::hasColumn('assessment_assignment_targets', 'is_validator')) {
            Schema::table('assessment_assignment_targets', function (Blueprint $table) {
                $table->boolean('is_validator')->default(false)->after('guru_id');
                $table->index(
                    ['assessment_assignment_id', 'is_validator'],
                    'assessment_assignment_target_validator_idx'
                );
            });
        }

        $requiredTables = [
            'users',
            'gurus',
            'validator_assignments',
            'validator_assignment_assessment_assignments',
        ];

        if (collect($requiredTables)->contains(fn (string $table) => ! Schema::hasTable($table))) {
            return;
        }

        $validatorTargetIds = DB::table('assessment_assignment_targets as target')
            ->join('gurus as guru', 'guru.id', '=', 'target.guru_id')
            ->join('users as user', 'user.no_ktp', '=', 'guru.no_ktp')
            ->join(
                'validator_assignment_assessment_assignments as source',
                function ($join) {
                    $join
                        ->on('source.assessment_assignment_id', '=', 'target.assessment_assignment_id');
                }
            )
            ->join(
                'validator_assignments as validator_assignment',
                'validator_assignment.id',
                '=',
                'source.validator_assignment_id'
            )
            ->whereColumn('validator_assignment.validator_user_id', 'user.id')
            ->whereRaw('LOWER(TRIM(user.role)) = ?', ['stakeholder'])
            ->whereRaw('LOWER(TRIM(guru.eksternal_jabatan)) = ?', ['stakeholder'])
            ->whereRaw('LOWER(TRIM(guru.jenis_jabatan)) = ?', ['validator'])
            ->distinct()
            ->pluck('target.id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        foreach (array_chunk($validatorTargetIds, 500) as $targetIds) {
            DB::table('assessment_assignment_targets')
                ->whereIn('id', $targetIds)
                ->update(['is_validator' => true]);
        }

        if ($validatorTargetIds === []) {
            return;
        }

        $assignmentIds = DB::table('assessment_assignment_targets')
            ->whereIn('id', $validatorTargetIds)
            ->distinct()
            ->pluck('assessment_assignment_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        foreach ($assignmentIds as $assignmentId) {
            $normalTargetCount = (int) DB::table('assessment_assignment_targets')
                ->where('assessment_assignment_id', $assignmentId)
                ->where('is_validator', false)
                ->where('status', '!=', 'dibatalkan')
                ->count();
            $validatorTargetCount = (int) DB::table('assessment_assignment_targets')
                ->where('assessment_assignment_id', $assignmentId)
                ->where('is_validator', true)
                ->count();
            $currentTotal = (int) DB::table('assessment_assignments')
                ->where('id', $assignmentId)
                ->value('total_target');

            DB::table('assessment_assignments')
                ->where('id', $assignmentId)
                ->update([
                    'total_target' => max($normalTargetCount, $currentTotal - $validatorTargetCount),
                    'total_ditugaskan' => $normalTargetCount,
                ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('assessment_assignment_targets')
            || ! Schema::hasColumn('assessment_assignment_targets', 'is_validator')) {
            return;
        }

        Schema::table('assessment_assignment_targets', function (Blueprint $table) {
            $table->dropIndex('assessment_assignment_target_validator_idx');
            $table->dropColumn('is_validator');
        });
    }
};
