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
            foreach ($generations as $generation) {
                $generationService->cancelGenerationProcessing($generation);
                $assignmentCount += $assignmentService->countAssignmentsForCombinationGeneration($generation);
                $generation = $generationService->prepareInPlaceReset($generation);
                $generationService->dispatchInPlaceReset($generation);
            }
        } catch (Throwable $exception) {
            report($exception);

            $this->error('Terjadi kesalahan saat mereset semua kombinasi soal.');

            return self::FAILURE;
        }

        $this->info(
            'Reset semua kombinasi untuk '.$generations->count().
            ' proses generate berhasil dikirim ke antrean.'
        );
        $this->line('Row kombinasi lama dipakai kembali sehingga jumlah kombinasi tidak bertambah.');

        if ($assignmentCount > 0) {
            $this->line($assignmentCount.' penugasan menunggu proses reset.');
        }

        return self::SUCCESS;
    }
}
