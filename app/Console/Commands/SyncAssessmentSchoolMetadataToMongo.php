<?php

namespace App\Console\Commands;

use App\Models\AssessmentAssignmentTarget;
use App\Services\Assessment\MongoAssessmentAssignmentTargetStore;
use Illuminate\Console\Command;
use Throwable;

class SyncAssessmentSchoolMetadataToMongo extends Command
{
    protected $signature = 'assessment:sync-school-metadata
        {--assignment= : Hanya target dari satu assignment ID}
        {--target= : Hanya satu assessment assignment target ID}
        {--chunk=100 : Jumlah target yang diproses per batch}
        {--dry-run : Tampilkan kandidat tanpa menulis ke MongoDB}';

    protected $description = 'Synchronize nama sekolah ke user pada assessment assignment MongoDB';

    public function handle(MongoAssessmentAssignmentTargetStore $store): int
    {
        $query = AssessmentAssignmentTarget::query()
            ->select(['id', 'assessment_assignment_id', 'guru_id', 'is_validator'])
            ->where('is_validator', false)
            ->whereNotNull('guru_id')
            ->whereHas('assignment', fn ($query) => $query->withoutPreview())
            ->when(
                $this->option('assignment'),
                fn ($query, $assignmentId) => $query->where('assessment_assignment_id', (int) $assignmentId)
            )
            ->when(
                $this->option('target'),
                fn ($query, $targetId) => $query->whereKey((int) $targetId)
            )
            ->with([
                'guru' => fn ($query) => $query->select(['id', 'npsn_sekolah', 'satuan_pendidikan']),
                'guru.sekolah' => fn ($query) => $query->select(['npsn_sekolah', 'nama_sekolah']),
            ]);

        $chunkSize = max((int) $this->option('chunk'), 1);

        if (! $this->option('dry-run')) {
            if (! (bool) config('assessment_mongodb.enabled')) {
                $this->error('MongoDB sync disabled. Set MONGODB_SYNC_ENABLED=true terlebih dahulu.');

                return self::FAILURE;
            }

            try {
                $store->assertAvailable();
            } catch (Throwable $exception) {
                $this->error($exception->getMessage());

                return self::FAILURE;
            }
        }

        $stats = [
            'targets' => 0,
            'with_school' => 0,
            'without_school' => 0,
            'matched' => 0,
            'modified' => 0,
            'skipped' => 0,
            'failed' => 0,
        ];

        try {
            $total = (int) (clone $query)->count();
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $progressBar = null;
        if ($total > 0) {
            $progressBar = $this->output->createProgressBar($total);
            $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%%');
            $progressBar->start();
        }

        try {
            $query->chunkById($chunkSize, function ($targets) use ($store, &$stats, $progressBar): void {
                try {
                    $updates = [];

                    foreach ($targets as $target) {
                        $stats['targets']++;
                        $schoolName = $this->schoolName($target);

                        if ($schoolName === null) {
                            $stats['without_school']++;
                        } else {
                            $stats['with_school']++;
                        }

                        $updates[] = [
                            'target_id' => (int) $target->id,
                            'school_name' => $schoolName,
                        ];
                    }

                    if ($this->option('dry-run') || $updates === []) {
                        return;
                    }

                    try {
                        $result = $store->updateSchoolMetadata($updates);
                        $stats['matched'] += $result['matched'];
                        $stats['modified'] += $result['modified'];
                        $stats['skipped'] += count($updates) - $result['matched'];
                    } catch (Throwable $exception) {
                        $stats['failed'] += count($updates);
                        $this->output->writeln('');
                        $this->error('Batch gagal: '.$exception->getMessage());
                    }
                } finally {
                    $progressBar?->advance($targets->count());
                }
            });
        } catch (Throwable $exception) {
            $progressBar?->clear();
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        if ($progressBar) {
            $progressBar->finish();
            $this->newLine();
        }

        $unchanged = $stats['matched'] - $stats['modified'];
        $mode = $this->option('dry-run') ? 'Dry-run' : 'Selesai';

        $this->info(sprintf(
            '%s. Kandidat: %d; metadata tersedia: %d; dinullkan: %d; diubah: %d; sudah sama: %d; dilewati: %d; gagal: %d.',
            $mode,
            $stats['targets'],
            $stats['with_school'],
            $stats['without_school'],
            $stats['modified'],
            max($unchanged, 0),
            $stats['skipped'],
            $stats['failed']
        ));

        return $stats['failed'] === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function schoolName(AssessmentAssignmentTarget $target): ?string
    {
        $name = $target->guru?->sekolah?->nama_sekolah
            ?: $target->guru?->satuan_pendidikan;
        $name = trim((string) $name);

        return $name !== '' ? $name : null;
    }
}
