<?php

namespace App\Console\Commands;

use App\Models\Assessment;
use App\Services\Assessment\AssessmentPackageService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ExportAssessmentBankSoal extends Command
{
    protected $signature = 'assessment:export-bank-soal
        {--path=docs/bank-soal : Folder tujuan file JSON}
        {--force : Lewati konfirmasi}';

    protected $description = 'Export semua bank soal assessment ke docs/bank-soal dan replace file yang sama';

    public function handle(AssessmentPackageService $service): int
    {
        $directory = (string) $this->option('path');
        $directory = str_starts_with($directory, '/') ? $directory : base_path($directory);

        if (! $this->option('force') && ! $this->confirm(
            "Aksi ini akan mengambil semua soal dari database dan menyimpannya ke folder {$directory}. File JSON yang sama akan ditimpa. Lanjutkan?",
            false
        )) {
            $this->warn('Export dibatalkan. Tidak ada file yang diubah.');

            return self::SUCCESS;
        }

        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            $this->error("Folder tujuan tidak dapat dibuat: {$directory}");

            return self::FAILURE;
        }

        $exported = 0;

        foreach (Assessment::query()->orderBy('id')->cursor() as $assessment) {
            $filename = Str::slug($assessment->kode_assessment ?: $assessment->judul).'.json';
            $target = $directory.DIRECTORY_SEPARATOR.$filename;
            $temporary = tempnam($directory, '.assessment-');

            if ($temporary === false) {
                $this->error("File sementara tidak dapat dibuat untuk {$filename}.");

                return self::FAILURE;
            }

            try {
                $this->writeExport($service, $assessment, $temporary);

                if (! rename($temporary, $target)) {
                    throw new RuntimeException("File {$filename} tidak dapat diganti.");
                }
            } catch (Throwable $exception) {
                @unlink($temporary);
                $this->error("Gagal export {$filename}: {$exception->getMessage()}");

                return self::FAILURE;
            }

            $exported++;
            $this->line("✓ {$filename}");
        }

        $this->info("Selesai. {$exported} file JSON disimpan di {$directory}.");

        return self::SUCCESS;
    }

    private function writeExport(
        AssessmentPackageService $service,
        Assessment $assessment,
        string $path
    ): void {
        $stream = fopen($path, 'wb');

        if ($stream === false) {
            throw new RuntimeException("File {$path} tidak dapat dibuka.");
        }

        $buffering = false;

        try {
            ob_start(static function (string $chunk) use ($stream): string {
                if (fwrite($stream, $chunk) === false) {
                    throw new RuntimeException('Gagal menulis hasil export.');
                }

                return '';
            }, 8192);
            $buffering = true;

            $service->export($assessment)->sendContent();
            ob_end_flush();
            $buffering = false;
        } finally {
            if ($buffering) {
                ob_end_clean();
            }

            fclose($stream);
        }
    }
}
