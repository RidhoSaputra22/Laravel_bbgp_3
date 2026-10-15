<?php

namespace App\Console\Commands;

use App\Models\AssessmentCombinationGeneration;
use App\Services\Assessment\AssessmentCombinationGenerationService;
use App\Services\AssessmentAssignmentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Throwable;

class ResetAllAssessmentCombinations extends Command
{
    protected $signature = 'assessment:reset-all-combinations {--force : Lewati konfirmasi}';

    protected $description = 'Reset isi semua kombinasi soal tanpa menambah row';

    public function handle(
        AssessmentCombinationGenerationService $generationService,
        AssessmentAssignmentService $assignmentService
    ): int {
        if (! Schema::hasColumn('assessment_combination_generations', 'reset_source_generation_id')) {
            $this->error('Migration reset kombinasi belum dijalankan. Jalankan php artisan migrate terlebih dahulu.');

            return self::FAILURE;
        }

        $pendingResetCount = AssessmentCombinationGeneration::query()
            ->whereNotNull('reset_source_generation_id')
            ->where('status', '!=', 'gagal')
            ->count();

        if ($pendingResetCount > 0) {
            $this->error('Reset tidak bisa dijalankan karena masih ada reset penugasan yang diproses job.');

            return self::FAILURE;
        }

        $generations = AssessmentCombinationGeneration::query()
            ->orderBy('id')
            ->get();

        if ($generations->isEmpty()) {
            $this->info('Belum ada kombinasi soal yang perlu direset.');

            return self::SUCCESS;
        }

        $validationErrors = [];

        foreach ($generations as $generation) {
            try {
                $generationService->validateGeneration($generation);

                if ($generation->combinations()->count() !== max((int) $generation->total_kombinasi, 1)) {
                    throw new InvalidArgumentException(
                        'Jumlah kombinasi tersimpan tidak sama dengan jumlah kombinasi yang diminta.'
                    );
                }
            } catch (Throwable $exception) {
                if (! $exception instanceof InvalidArgumentException) {
                    report($exception);
                }

                $validationErrors[] = [
                    'generation' => $generation,
                    'message' => $exception->getMessage(),
                ];
            }
        }

        if ($validationErrors !== []) {
            $this->error('Preflight reset gagal. Tidak ada data atau queue yang dibuat.');

            foreach ($validationErrors as $validationError) {
                $generation = $validationError['generation'];
                $this->line(sprintf(
                    '- %s: %s',
                    $generation->kode_generate ?: 'Generation #'.$generation->id,
                    $validationError['message']
                ));
            }

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm(
            'Reset isi semua kombinasi soal pada row yang sama?',
            false
        )) {
            $this->warn('Reset dibatalkan. Tidak ada data yang diubah.');

            return self::SUCCESS;
        }

        $assignmentCount = 0;

        try {
            $this->info(
                'Menjalankan reset '.$generations->count().
                ' proses generate langsung tanpa job queue.'
            );

            foreach ($generations as $generationIndex => $generation) {
                $generationService->cancelGenerationProcessing($generation);
                $assignmentCountForGeneration = $assignmentService
                    ->countAssignmentsForCombinationGeneration($generation);
                $assignmentCount += $assignmentCountForGeneration;
                $generation = $generationService->prepareInPlaceReset($generation);
                $combinationCount = $generation->combinations()->count();
                $workCount = $combinationCount + $assignmentCountForGeneration;

                $this->line(sprintf(
                    '[%d/%d] %s - %d kombinasi, %d penugasan',
                    $generationIndex + 1,
                    $generations->count(),
                    $generation->kode_generate ?: 'Generation #'.$generation->id,
                    $combinationCount,
                    $assignmentCountForGeneration
                ));

                $progressBar = $this->output->createProgressBar($workCount);
                $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%%');
                $progressBar->start();

                $generationService->processInPlaceReset(
                    $generation,
                    $assignmentService,
                    fn (int $processed, int $total) => $progressBar->advance(),
                    fn (int $processed, int $total) => $progressBar->advance(),
                    true
                );

                $progressBar->finish();
                $this->newLine();
            }
        } catch (Throwable $exception) {
            report($exception);

            $this->error('Terjadi kesalahan saat mereset semua kombinasi soal.');

            return self::FAILURE;
        }

        $this->info(
            'Reset semua kombinasi untuk '.$generations->count().
            ' proses generate berhasil diproses langsung.'
        );
        $this->line('Row kombinasi lama dipakai kembali sehingga jumlah kombinasi tidak bertambah.');

        if ($assignmentCount > 0) {
            $this->line($assignmentCount.' penugasan berhasil direset.');
        }

        return self::SUCCESS;
    }
}
