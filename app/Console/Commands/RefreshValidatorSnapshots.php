<?php

namespace App\Console\Commands;

use App\Models\ValidatorAssignment;
use App\Services\Assessment\ValidatorAssignmentService;
use Illuminate\Console\Command;
use Throwable;

class RefreshValidatorSnapshots extends Command
{
    protected $signature = 'assessment:refresh-validator-snapshots
        {--assignment= : Only refresh one validator assignment ID}
        {--force : Skip the confirmation prompt}';

    protected $description = 'Refresh validator assignment snapshots from current assessment assignments';

    public function handle(ValidatorAssignmentService $service): int
    {
        $query = ValidatorAssignment::query()
            ->when(
                $this->option('assignment'),
                fn ($query, $assignmentId) => $query->whereKey((int) $assignmentId)
            );
        $total = (int) (clone $query)->count();

        if ($total === 0) {
            $this->info('Tidak ada snapshot penugasan validator yang perlu diperbarui.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm(
            "Perbarui snapshot {$total} penugasan validator? Progres dan hasil validasi tetap dipertahankan.",
            false
        )) {
            $this->warn('Refresh dibatalkan. Tidak ada data yang diubah.');

            return self::SUCCESS;
        }

        $refreshed = 0;
        $failed = [];
        $progressBar = $this->output->createProgressBar($total);
        $progressBar->setFormat('%current%/%max% [%bar%] %percent:3s%%');
        $progressBar->start();

        // ponytail: one row per chunk keeps large snapshot graphs under 128 MB; increase only with a larger CLI memory limit.
        $query->chunkById(1, function ($assignments) use ($service, &$refreshed, &$failed, $progressBar): void {
            foreach ($assignments as $assignment) {
                try {
                    $service->refreshQuestions($assignment);
                    $refreshed++;
                } catch (Throwable $exception) {
                    $failed[] = [
                        'id' => (int) $assignment->getKey(),
                        'message' => $exception->getMessage(),
                    ];
                } finally {
                    $progressBar->advance();
                    unset($assignment);
                    gc_collect_cycles();
                }
            }
        });

        $progressBar->finish();
        $this->newLine();
        $this->info("Selesai. Diperbarui: {$refreshed}; gagal: ".count($failed).'.');

        foreach ($failed as $failure) {
            $this->warn("Validator #{$failure['id']}: {$failure['message']}");
        }

        return $failed === [] ? self::SUCCESS : self::FAILURE;
    }
}
