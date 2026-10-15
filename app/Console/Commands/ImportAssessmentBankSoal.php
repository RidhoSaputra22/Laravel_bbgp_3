<?php

namespace App\Console\Commands;

use App\Services\Assessment\AssessmentPackageService;
use Illuminate\Console\Command;
use Throwable;

class ImportAssessmentBankSoal extends Command
{
    protected $signature = 'assessment:import-bank-soal
        {--path=docs/bank-soal : Folder sumber file JSON}
        {--force : Lewati konfirmasi}';

    protected $description = 'Import semua file JSON bank soal ke database';

    public function handle(AssessmentPackageService $service): int
    {
        $directory = (string) $this->option('path');
        $directory = str_starts_with($directory, '/') ? $directory : base_path($directory);
        $files = glob($directory.DIRECTORY_SEPARATOR.'*.json') ?: [];

        if ($files === []) {
            $this->error("Tidak ada file JSON di {$directory}.");

            return self::FAILURE;
        }

        foreach ($files as $file) {
            $validation = $service->validateFile($file);

            if (! $validation['valid']) {
                $this->error('Validasi gagal. Tidak ada file yang diimport.');
                $this->line(basename($file).': '.implode(' ', $validation['errors']));

                return self::FAILURE;
            }
        }

        if (! $this->option('force') && ! $this->confirm(
            "Aksi ini akan mengambil soal dari folder {$directory} dan mengimpornya ke database. Lanjutkan?",
            false
        )) {
            $this->warn('Import dibatalkan. Tidak ada data yang diubah.');

            return self::SUCCESS;
        }

        $imported = 0;

        foreach ($files as $file) {
            try {
                $summary = $service->importFile($file);
            } catch (Throwable $exception) {
                $this->error('Gagal import '.basename($file).': '.$exception->getMessage());

                return self::FAILURE;
            }

            $imported++;
            $this->line(sprintf(
                '✓ %s — %d form, %d pertanyaan, %d penugasan',
                basename($file),
                $summary['forms'],
                $summary['fields'],
                $summary['assignments']
            ));
        }

        $this->info("Selesai. {$imported} instrument berhasil diimport.");

        return self::SUCCESS;
    }
}
