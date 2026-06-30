<?php

use App\Enum\AssessmentKetenagaanType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('assessments') && ! Schema::hasColumn('assessments', 'target_ketenagaan')) {
            Schema::table('assessments', function (Blueprint $table) {
                $table->string('target_ketenagaan')->nullable()->after('instrument_type');
                $table->index('target_ketenagaan');
            });
        }

        if (Schema::hasTable('assessment_assignments') && ! Schema::hasColumn('assessment_assignments', 'target_ketenagaan')) {
            Schema::table('assessment_assignments', function (Blueprint $table) {
                $table->string('target_ketenagaan')->nullable()->after('judul_penugasan');
                $table->index('target_ketenagaan');
            });
        }

        $this->backfillAssessmentTargets();
        $this->backfillAssignmentTargets();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('assessment_assignments') && Schema::hasColumn('assessment_assignments', 'target_ketenagaan')) {
            Schema::table('assessment_assignments', function (Blueprint $table) {
                $table->dropIndex(['target_ketenagaan']);
                $table->dropColumn('target_ketenagaan');
            });
        }

        if (Schema::hasTable('assessments') && Schema::hasColumn('assessments', 'target_ketenagaan')) {
            Schema::table('assessments', function (Blueprint $table) {
                $table->dropIndex(['target_ketenagaan']);
                $table->dropColumn('target_ketenagaan');
            });
        }
    }

    private function backfillAssessmentTargets(): void
    {
        if (! Schema::hasTable('assessments') || ! Schema::hasColumn('assessments', 'target_ketenagaan')) {
            return;
        }

        DB::table('assessments')
            ->where(function ($query) {
                $query->whereNull('target_ketenagaan')
                    ->orWhere('target_ketenagaan', '');
            })
            ->update([
                'target_ketenagaan' => AssessmentKetenagaanType::TENAGA_PENDIDIK->value,
            ]);
    }

    private function backfillAssignmentTargets(): void
    {
        if (
            ! Schema::hasTable('assessment_assignments') ||
            ! Schema::hasColumn('assessment_assignments', 'target_ketenagaan') ||
            ! Schema::hasTable('assessments') ||
            ! Schema::hasColumn('assessments', 'target_ketenagaan')
        ) {
            return;
        }

        if (Schema::hasTable('assessment_assignment_assessments')) {
            $assignments = DB::table('assessment_assignments')
                ->where(function ($query) {
                    $query->whereNull('target_ketenagaan')
                        ->orWhere('target_ketenagaan', '');
                })
                ->orderBy('id')
                ->get(['id']);

            foreach ($assignments as $assignment) {
                $targetKetenagaan = DB::table('assessment_assignment_assessments')
                    ->join('assessments', 'assessments.id', '=', 'assessment_assignment_assessments.assessment_id')
                    ->where('assessment_assignment_assessments.assessment_assignment_id', $assignment->id)
                    ->orderBy('assessment_assignment_assessments.urutan')
                    ->value('assessments.target_ketenagaan');

                if (! $targetKetenagaan) {
                    continue;
                }

                DB::table('assessment_assignments')
                    ->where('id', $assignment->id)
                    ->update([
                        'target_ketenagaan' => $targetKetenagaan,
                    ]);
            }

            return;
        }

        if (! Schema::hasColumn('assessment_assignments', 'assessment_id')) {
            return;
        }

        $rows = DB::table('assessment_assignments')
            ->join('assessments', 'assessments.id', '=', 'assessment_assignments.assessment_id')
            ->where(function ($query) {
                $query->whereNull('assessment_assignments.target_ketenagaan')
                    ->orWhere('assessment_assignments.target_ketenagaan', '');
            })
            ->get([
                'assessment_assignments.id',
                'assessments.target_ketenagaan',
            ]);

        foreach ($rows as $row) {
            if (! $row->target_ketenagaan) {
                continue;
            }

            DB::table('assessment_assignments')
                ->where('id', $row->id)
                ->update([
                    'target_ketenagaan' => $row->target_ketenagaan,
                ]);
        }
    }
};
