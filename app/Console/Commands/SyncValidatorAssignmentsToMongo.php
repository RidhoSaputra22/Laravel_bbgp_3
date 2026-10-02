<?php

namespace App\Console\Commands;

use App\Models\ValidatorAssignment;
use App\Services\Assessment\MongoValidatorAssignmentStore;
use App\Services\Assessment\ValidatorAssignmentDocumentBuilder;
use Illuminate\Console\Command;
use Throwable;

class SyncValidatorAssignmentsToMongo extends Command
{
    protected $signature = 'assessment:sync-validator-assignments-mongodb
        {--assignment= : Only sync one validator assignment ID}
        {--chunk=100 : Number of assignments processed per batch}
        {--dry-run : Count assignments without writing to MongoDB}
        {--reset : Delete every existing validator document before a full backfill}
        {--force : Skip the confirmation prompt for --reset}';

    protected $description = 'Backfill validator assignments into MongoDB';

    public function handle(
        MongoValidatorAssignmentStore $store,
        ValidatorAssignmentDocumentBuilder $builder
    ): int {
        $query = ValidatorAssignment::query()
            ->when(
                $this->option('assignment'),
                fn ($query, $assignmentId) => $query->whereKey((int) $assignmentId)
            );
        $chunkSize = max((int) $this->option('chunk'), 1);

        if ($this->option('reset') && $this->option('assignment')) {
            $this->error('--reset hanya boleh digunakan untuk sinkronisasi seluruh penugasan validator; hapus --assignment.');

            return self::INVALID;
        }

        if ($this->option('dry-run')) {
            $this->info('Penugasan validator cocok: '.$query->count());

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
                    'Hapus seluruh dokumen lama dari collection validator_assignment sebelum sinkronisasi?',
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
            $this->info('Selesai. Tidak ada penugasan validator yang perlu disinkronkan.');

            return self::SUCCESS;
        }

        $synced = 0;
        $failed = 0;
        $progressBar = $this->output->createProgressBar($total);
        $progressBar->setFormat('%current%/%max% [%bar%] %percent:3s%%');
        $progressBar->start();

        $query
            ->with($builder->relations())
            ->chunkById($chunkSize, function ($assignments) use (
                $store,
                $builder,
                $progressBar,
                &$synced,
                &$failed
            ) {
                try {
                    $documents = $assignments
                        ->map(fn (ValidatorAssignment $assignment) => $builder->document($assignment))
                        ->values()
                        ->all();
                    $store->bulkUpsert($documents);
                    $synced += count($documents);
                } catch (Throwable $exception) {
                    $failed += $assignments->count();
                    $this->output->writeln('');
                    $this->warn('Batch gagal: '.$exception->getMessage());
                }

                $progressBar->advance($assignments->count());
            });

        $progressBar->finish();
        $this->newLine();
        $this->info("Selesai. Synced: {$synced}; gagal: {$failed}.");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
