<?php

namespace App\Console\Commands;

use App\Models\AssessmentCombinationGeneration;
use App\Services\Assessment\AssessmentCombinationGenerationService;
use App\Services\AssessmentAssignmentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ResetAllAssessmentCombinations extends Command
{
    protected $signature = 'assessment:reset-all-combinations {--force : Lewati konfirmasi}';

    protected $description = 'Reset semua kombinasi soal dengan membuat ulang seluruh proses generate';

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

        if (! $this->option('force') && ! $this->confirm(
            'Reset semua kombinasi soal dan buat ulang seluruh proses generate?',
            false
        )) {
            $this->warn('Reset dibatalkan. Tidak ada data yang diubah.');

            return self::SUCCESS;
        }

        $replacementGenerations = [];
        $assignmentCount = 0;

        try {
            foreach ($generations as $generation) {
                $generationService->cancelGenerationProcessing($generation);
                $assignmentCount += $assignmentService->countAssignmentsForCombinationGeneration($generation);

                $replacementGeneration = $generationService->createReplacementGeneration($generation);
                $replacementGenerations[] = $replacementGeneration;
                $generationService->dispatchGeneration($replacementGeneration);
            }
        } catch (Throwable $exception) {
            report($exception);

            foreach ($replacementGenerations as $replacementGeneration) {
                try {
                    $generationService->deleteGenerationHistory($replacementGeneration->fresh());
                } catch (Throwable $cleanupException) {
                    report($cleanupException);
                }
            }

            $this->error('Terjadi kesalahan saat mereset semua kombinasi soal.');

            return self::FAILURE;
        }

        $this->info(
            'Reset semua kombinasi untuk '.$generations->count().
            ' proses generate berhasil dikirim ke antrean.'
        );
        $this->line('Penugasan tetap dipertahankan dan akan dialihkan ke kombinasi baru oleh job.');

        if ($assignmentCount > 0) {
            $this->line($assignmentCount.' penugasan menunggu proses reset.');
        }

        return self::SUCCESS;
    }
}
