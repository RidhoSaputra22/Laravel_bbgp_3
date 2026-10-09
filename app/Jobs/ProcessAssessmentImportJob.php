<?php

namespace App\Jobs;

use App\Models\AssessmentImport;
use App\Services\Assessment\AssessmentPackageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessAssessmentImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 3600;

    public function __construct(public int $importId)
    {
    }

    public function handle(AssessmentPackageService $service): void
    {
        $import = AssessmentImport::query()->findOrFail($this->importId);
        $import->forceFill([
            'status' => 'processing',
            'started_at' => now(),
            'error_message' => null,
        ])->save();

        try {
            $summary = $service->importFile(
                Storage::disk($import->disk)->path($import->path)
            );

            $import->forceFill([
                'status' => 'completed',
                'summary' => $summary,
                'completed_at' => now(),
            ])->save();
            Storage::disk($import->disk)->delete($import->path);
        } catch (Throwable $exception) {
            $import->forceFill([
                'error_message' => $exception->getMessage(),
            ])->save();

            throw $exception;
        }
    }

    public function failed(Throwable $exception): void
    {
        $import = AssessmentImport::query()->find($this->importId);

        if ($import && $import->status !== 'failed') {
            $import->forceFill([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'completed_at' => now(),
            ])->save();
        }

        if ($import) {
            Storage::disk($import->disk)->delete($import->path);
        }
    }
}
