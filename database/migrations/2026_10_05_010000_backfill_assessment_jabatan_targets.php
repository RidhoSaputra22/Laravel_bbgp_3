<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('assessments') || ! Schema::hasColumn('assessments', 'target_jabatan')) {
            return;
        }

        $targetsByCode = [
            'ASM-KS-PG-001' => ['Kepala Sekolah'],
            'ASM-KS-PORTOFOLIO-001' => ['Kepala Sekolah'],
            'ASM-KS-STUDI-KASUS-001' => ['Kepala Sekolah'],
            'ASM-KS-LIKERT-001' => ['Kepala Sekolah'],
            'ASM-PENGAWAS-PORTOFOLIO-2026' => ['Pengawas'],
            'ASM-PENGAWAS-PGK-2026' => ['Pengawas'],
            'ASM-PENGAWAS-STUDI-KASUS-2026' => ['Pengawas'],
            'ASM-PENGAWAS-ANGKET-2026' => ['Pengawas'],
        ];

        foreach ($targetsByCode as $code => $targetJabatan) {
            DB::table('assessments')
                ->where('kode_assessment', $code)
                ->update(['target_jabatan' => json_encode($targetJabatan)]);
        }

        if (
            ! Schema::hasTable('assessment_combinations')
            || ! Schema::hasTable('assessment_combination_items')
            || ! Schema::hasColumn('assessment_combinations', 'target_jabatan')
        ) {
            return;
        }

        $decodeTargetJabatan = static function (mixed $value): array {
            if (is_array($value)) {
                return $value;
            }

            $decoded = json_decode((string) $value, true);

            return is_array($decoded) ? $decoded : [];
        };

        $assessmentTargets = DB::table('assessments')
            ->whereNotNull('target_jabatan')
            ->get(['id', 'target_jabatan'])
            ->mapWithKeys(fn ($assessment) => [
                (int) $assessment->id => $decodeTargetJabatan($assessment->target_jabatan),
            ]);

        $generationTargets = collect();

        if (
            Schema::hasTable('assessment_combination_generations')
            && Schema::hasColumn('assessment_combination_generations', 'target_jabatan')
        ) {
            DB::table('assessment_combination_generations')
                ->orderBy('id')
                ->get(['id', 'target_jabatan', 'selection_config'])
                ->each(function ($generation) use (
                    $assessmentTargets,
                    $decodeTargetJabatan,
                    $generationTargets
                ) {
                    $selectionConfig = $decodeTargetJabatan($generation->selection_config);
                    $targetJabatan = $decodeTargetJabatan($generation->target_jabatan);

                    if ($targetJabatan === [] && is_array($selectionConfig['target_jabatan'] ?? null)) {
                        $targetJabatan = $selectionConfig['target_jabatan'];
                    }

                    if ($targetJabatan === []) {
                        $targetValues = collect($selectionConfig['included_assessment_ids'] ?? [])
                            ->map(fn ($assessmentId) => $assessmentTargets->get((int) $assessmentId, []));

                        if (
                            $targetValues->isNotEmpty()
                            && ! $targetValues->contains(fn (array $target) => $target === [])
                        ) {
                            $uniqueTargets = $targetValues
                                ->map(fn (array $target) => json_encode($target))
                                ->unique()
                                ->values();

                            if ($uniqueTargets->count() === 1) {
                                $targetJabatan = $decodeTargetJabatan($uniqueTargets->first());
                                $selectionConfig['target_jabatan'] = $targetJabatan;

                                DB::table('assessment_combination_generations')
                                    ->where('id', $generation->id)
                                    ->update([
                                        'target_jabatan' => json_encode($targetJabatan),
                                        'selection_config' => json_encode($selectionConfig),
                                    ]);
                            }
                        }
                    }

                    if ($targetJabatan !== []) {
                        $generationTargets->put((int) $generation->id, $targetJabatan);
                    }
                });
        }

        DB::table('assessment_combinations')
            ->whereNull('target_jabatan')
            ->orderBy('id')
            ->get(['id', 'assessment_combination_generation_id'])
            ->each(function ($combination) use ($assessmentTargets, $generationTargets) {
                $generationTarget = $generationTargets->get(
                    (int) $combination->assessment_combination_generation_id
                );

                if (is_array($generationTarget) && $generationTarget !== []) {
                    DB::table('assessment_combinations')
                        ->where('id', $combination->id)
                        ->update(['target_jabatan' => json_encode($generationTarget)]);

                    return;
                }

                $assessmentIds = DB::table('assessment_combination_items')
                    ->where('assessment_combination_id', $combination->id)
                    ->whereNotNull('assessment_id')
                    ->pluck('assessment_id')
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values();

                if ($assessmentIds->isEmpty()) {
                    return;
                }

                $targets = $assessmentIds->map(
                    fn (int $assessmentId) => $assessmentTargets->get($assessmentId, [])
                );

                if ($targets->contains(fn (array $targetJabatan) => $targetJabatan === [])) {
                    return;
                }

                $targetValues = $targets
                    ->map(fn (array $targetJabatan) => json_encode($targetJabatan))
                    ->unique()
                    ->values();

                if ($targetValues->count() !== 1) {
                    return;
                }

                DB::table('assessment_combinations')
                    ->where('id', $combination->id)
                    ->update(['target_jabatan' => $targetValues->first()]);
            });
    }

    public function down(): void
    {
        // Keep the backfilled metadata; the column migration owns its rollback.
    }
};
