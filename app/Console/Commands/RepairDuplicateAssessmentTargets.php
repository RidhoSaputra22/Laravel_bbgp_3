<?php

namespace App\Console\Commands;

use App\Jobs\SyncAssessmentTargetsToMongoJob;
use App\Models\AssessmentAssignmentTarget;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RepairDuplicateAssessmentTargets extends Command
{
    protected $signature = 'assessment:repair-duplicate-targets
                            {--assignment= : Batasi ke ID penugasan tertentu}
                            {--dry-run : Hanya tampilkan kandidat tanpa menghapus}
                            {--force : Hapus target duplikat yang aman dihapus}';

    protected $description = 'Menghapus target penugasan ganda berdasarkan NIK yang belum memiliki attempt.';

    public function handle(): int
    {
        if (! Schema::hasColumn('gurus', 'no_ktp')) {
            $this->error('Kolom gurus.no_ktp tidak ditemukan.');

            return self::FAILURE;
        }

        $groups = [];
        $query = DB::table('assessment_assignment_targets as target')
            ->join('gurus as guru', 'guru.id', '=', 'target.guru_id')
            ->where('target.is_validator', false)
            ->whereNotNull('guru.no_ktp')
            ->where('guru.no_ktp', '<>', '')
            ->orderBy('target.assessment_assignment_id')
            ->orderBy('target.id');

        if (filled($this->option('assignment'))) {
            $query->where('target.assessment_assignment_id', (int) $this->option('assignment'));
        }

        foreach ($query->get(['target.id', 'target.assessment_assignment_id', 'guru.no_ktp']) as $row) {
            $identity = $this->normalizeIdentity($row->no_ktp);
            if ($identity === '') {
                continue;
            }

            $key = (int) $row->assessment_assignment_id.'|'.$identity;
            $groups[$key][] = (int) $row->id;
        }

        $duplicateGroups = collect($groups)
            ->filter(fn (array $targetIds) => count($targetIds) > 1);

        if ($duplicateGroups->isEmpty()) {
            $this->info('Tidak ditemukan target duplikat berdasarkan NIK.');

            return self::SUCCESS;
        }

        $candidateIds = $duplicateGroups
            ->flatMap(fn (array $targetIds) => array_slice($targetIds, 1))
            ->values();
        $protectedIds = collect();

        if (Schema::hasTable('assessment_attempts')) {
            $protectedIds = DB::table('assessment_attempts')
                ->whereIn('assessment_assignment_target_id', $candidateIds->all())
                ->pluck('assessment_assignment_target_id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();
        }

        $deletableIds = $candidateIds->diff($protectedIds)->values();
        $this->line('Grup duplikat: '.$duplicateGroups->count());
        $this->line('Kandidat target tambahan: '.$candidateIds->count());
        $this->line('Dilindungi karena memiliki attempt: '.$protectedIds->count());
        $this->line('Aman dihapus: '.$deletableIds->count());

        if ($deletableIds->isEmpty()) {
            $this->warn('Tidak ada target yang aman dihapus.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run') || ! $this->option('force')) {
            $this->warn('Tidak ada perubahan. Jalankan ulang dengan --force untuk memperbaiki data.');

            return self::SUCCESS;
        }

        $assignmentIds = $duplicateGroups
            ->flatMap(function (array $targetIds, string $key) use ($deletableIds) {
                if (! $deletableIds->intersect(array_slice($targetIds, 1))->isNotEmpty()) {
                    return [];
                }

                return [(int) str($key)->before('|')->toString()];
            })
            ->unique()
            ->values();

        DB::transaction(function () use ($deletableIds, $assignmentIds): void {
            AssessmentAssignmentTarget::query()
                ->whereIn('id', $deletableIds->all())
                ->get()
                ->each
                ->delete();

            foreach ($assignmentIds as $assignmentId) {
                $count = DB::table('assessment_assignment_targets')
                    ->where('assessment_assignment_id', $assignmentId)
                    ->where('is_validator', false)
                    ->where('status', '!=', 'dibatalkan')
                    ->count();

                DB::table('assessment_assignments')
                    ->where('id', $assignmentId)
                    ->update([
                        'total_target' => $count,
                        'total_ditugaskan' => $count,
                        'updated_at' => now(),
                    ]);
            }
        });

        SyncAssessmentTargetsToMongoJob::dispatchIds($deletableIds->all());
        $this->info('Perbaikan selesai. Target dihapus: '.$deletableIds->count());

        return self::SUCCESS;
    }

    private function normalizeIdentity(mixed $identity): string
    {
        return (string) preg_replace('/\s+/', '', trim((string) $identity));
    }
}
