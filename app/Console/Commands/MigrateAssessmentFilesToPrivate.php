<?php

namespace App\Console\Commands;

use App\Models\AssessmentAttemptAnswer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateAssessmentFilesToPrivate extends Command
{
    protected $signature = 'assessment:secure-files';

    protected $description = 'Pindahkan file assessment dari disk public ke storage private';

    public function handle(): int
    {
        $public = Storage::disk('public');
        $private = Storage::disk('assessment_private');
        $migrated = 0;
        $skipped = 0;

        AssessmentAttemptAnswer::query()
            ->whereNotNull('answer_file_path')
            ->select(['id', 'answer_file_path'])
            ->orderBy('id')
            ->each(function (AssessmentAttemptAnswer $answer) use ($public, $private, &$migrated, &$skipped): void {
                $path = trim((string) $answer->answer_file_path);

                if ($path === '' || ! str_starts_with($path, 'assessment/attempts/') || str_contains($path, '..')) {
                    $skipped++;

                    return;
                }

                if ($private->exists($path)) {
                    $skipped++;

                    return;
                }

                if (! $public->exists($path)) {
                    $this->warn("File tidak ditemukan: {$path}");
                    $skipped++;

                    return;
                }

                $stream = $public->readStream($path);

                if (! is_resource($stream) || ! $private->writeStream($path, $stream)) {
                    if (is_resource($stream)) {
                        fclose($stream);
                    }

                    $this->error("Gagal memindahkan: {$path}");
                    $skipped++;

                    return;
                }

                fclose($stream);
                $public->delete($path);
                $migrated++;
            });

        $this->info("Selesai. Dipindahkan: {$migrated}; dilewati: {$skipped}.");

        return self::SUCCESS;
    }
}
