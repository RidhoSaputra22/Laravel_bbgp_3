<?php

use App\Support\Assessment\AssessmentStageConfig;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            ! Schema::hasTable('assessment_assignment_assessments') ||
            ! Schema::hasTable('assessments') ||
            ! Schema::hasColumn('assessment_assignment_assessments', 'id') ||
            ! Schema::hasColumn('assessment_assignment_assessments', 'assessment_id') ||
            ! Schema::hasColumn('assessment_assignment_assessments', 'urutan') ||
            ! Schema::hasColumn('assessment_assignment_assessments', 'stage_config') ||
            ! Schema::hasColumn('assessments', 'instrument_type')
        ) {
            return;
        }

        $rows = DB::table('assessment_assignment_assessments as assignment_stages')
            ->join('assessments', 'assessments.id', '=', 'assignment_stages.assessment_id')
            ->where('assignment_stages.urutan', '>', 1)
            ->orderBy('assignment_stages.id')
            ->select([
                'assignment_stages.id',
                'assignment_stages.urutan',
                'assignment_stages.stage_config',
                'assessments.instrument_type',
            ])
            ->get();

        foreach ($rows as $row) {
            $currentConfig = json_decode($row->stage_config ?? '[]', true);
            $normalizedConfig = AssessmentStageConfig::normalizeForAssessment(
                $row->instrument_type,
                max(0, ((int) $row->urutan) - 1),
                is_array($currentConfig) ? $currentConfig : []
            );

            if ($normalizedConfig === $currentConfig) {
                continue;
            }

            DB::table('assessment_assignment_assessments')
                ->where('id', $row->id)
                ->update([
                    'stage_config' => json_encode($normalizedConfig),
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        // Backfill data is intentionally not reverted.
    }
};
