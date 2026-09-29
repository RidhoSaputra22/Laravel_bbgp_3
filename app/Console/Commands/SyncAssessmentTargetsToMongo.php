<?php

namespace App\Console\Commands;

use App\Models\AssessmentAssignmentTarget;
use App\Services\Assessment\AssessmentAssignmentTargetDocumentBuilder;
use App\Services\Assessment\MongoAssessmentAssignmentTargetStore;
use Illuminate\Console\Command;
use Throwable;

class SyncAssessmentTargetsToMongo extends Command
{
    protected $signature = 'assessment:sync-targets-mongodb
        {--assignment= : Only sync targets for one assignment ID}
        {--chunk=100 : Number of targets processed per batch}
        {--dry-run : Count matching targets without writing to MongoDB}
        {--reset : Delete every existing target document before a full backfill}
        {--force : Skip the confirmation prompt for --reset}';

    protected $description = 'Backfill assessment assignment targets into MongoDB';

    public function handle(
        MongoAssessmentAssignmentTargetStore $store,
        AssessmentAssignmentTargetDocumentBuilder $builder
    ): int {
        $query = AssessmentAssignmentTarget::query()
            ->whereHas('assignment', fn ($query) => $query->withoutPreview())
            ->when(
                $this->option('assignment'),
                fn ($query, $assignmentId) => $query->where('assessment_assignment_id', (int) $assignmentId)
            );
        $chunkSize = max((int) $this->option('chunk'), 1);

        if ($this->option('reset') && $this->option('assignment')) {
            $this->error('--reset hanya boleh digunakan untuk sinkronisasi seluruh assignment; hapus --assignment.');

            return self::INVALID;
        }

        if ($this->option('dry-run')) {
            $this->info('Target cocok: '.$query->count());

            if ($this->option('reset')) {
                $this->comment('--reset diabaikan pada mode --dry-run.');
            }

            return self::SUCCESS;
        }

        if (! (bool) config('assessment_mongodb.enabled')) {
            $this->error('MongoDB sync disabled. Set MONGODB_SYNC_ENABLED=true terlebih dahulu.');

            return self::FAILURE;
        }

        try {
            $store->assertAvailable();
            $store->ensureIndexes();

            if ($this->option('reset')) {
                if (! $this->option('force') && ! $this->confirm(
                    'Hapus seluruh dokumen lama dari collection MongoDB sebelum sinkronisasi?',
                    false
                )) {
                    $this->warn('Reset dibatalkan. Tidak ada data yang diubah.');

                    return self::SUCCESS;
                }

                $deleted = $store->deleteAll();
                $this->info("Dokumen lama dihapus: {$deleted}.");
            }
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $total = (int) (clone $query)->count();
        if ($total === 0) {
            $this->info('Selesai. Tidak ada target yang perlu disinkronkan.');

            return self::SUCCESS;
        }

        $synced = 0;
        $failed = 0;
        $progressBar = $this->output->createProgressBar($total);
        $progressBar->setFormat('%current%/%max% [%bar%] %percent:3s%%');
        $progressBar->start();
        $query
            ->select(AssessmentAssignmentTargetDocumentBuilder::targetColumns())
            ->with($builder->targetRelations())
            ->chunkById($chunkSize, function ($targets) use (
                $store,
                $builder,
                $progressBar,
                &$synced,
                &$failed
            ) {
                try {
                    $builder->hydrateAssignments($targets);
                    $documents = $targets
                        ->map(fn (AssessmentAssignmentTarget $target) => $builder->document($target, $target->assignment))
                        ->values()
                        ->all();
                    $store->bulkUpsert($documents);
                    $synced += count($documents);
                } catch (Throwable $exception) {
                    $failed += $targets->count();
                    $this->output->writeln('');
                    $this->warn('Batch gagal: '.$exception->getMessage());
                }

                $progressBar->advance($targets->count());
            });

        $progressBar->finish();
        $this->newLine();
        $this->info("Selesai. Synced: {$synced}; gagal: {$failed}.");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
