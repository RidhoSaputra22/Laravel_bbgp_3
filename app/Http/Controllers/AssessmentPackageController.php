<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessAssessmentImportJob;
use App\Models\Assessment;
use App\Models\AssessmentImport;
use App\Services\Assessment\AssessmentPackageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AssessmentPackageController extends Controller
{
    public function __construct(private readonly AssessmentPackageService $service) {}

    public function export(Assessment $assessment)
    {
        $this->authorizeAccess();

        return $this->service->export($assessment);
    }

    public function import(Request $request)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'files' => ['required_without:file', 'array', 'min:1'],
            'files.*' => ['file', 'mimes:json,txt', 'max:524288'],
            // Keep accepting the old field name for existing clients.
            'file' => ['nullable', 'file', 'mimes:json,txt', 'max:524288'],
        ], [
            'files.required_without' => 'Minimal satu file JSON assessment wajib dipilih.',
            'files.min' => 'Minimal satu file JSON assessment wajib dipilih.',
            'files.*.mimes' => 'File import harus berformat JSON.',
            'files.*.max' => 'Ukuran setiap file import maksimal 512 MB.',
            'file.mimes' => 'File import harus berformat JSON.',
            'file.max' => 'Ukuran file import maksimal 512 MB.',
        ]);

        $files = $validated['files'] ?? [];
        if (isset($validated['file'])) {
            $files[] = $validated['file'];
        }

        $disk = 'assessment_private';
        $storedFiles = [];

        foreach ($files as $file) {
            $path = $file->storeAs(
                'assessment-imports',
                Str::uuid()->toString().'.json',
                $disk
            );

            if (! $path) {
                Storage::disk($disk)->delete(array_column($storedFiles, 'path'));

                return back()->withErrors(['file' => 'File JSON gagal disimpan sementara.']);
            }

            $storedFiles[] = [
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
            ];
        }

        $validations = [];
        $validationErrors = [];

        foreach ($storedFiles as $storedFile) {
            $validation = $this->service->validateFile(
                Storage::disk($disk)->path($storedFile['path'])
            );
            $validations[] = $validation;

            if (! $validation['valid']) {
                foreach ($validation['errors'] as $error) {
                    $validationErrors[] = $storedFile['original_name'].': '.$error;
                }
            }
        }

        if ($validationErrors !== []) {
            Storage::disk($disk)->delete(array_column($storedFiles, 'path'));

            return back()
                ->withInput()
                ->withErrors(['file' => implode(' ', $validationErrors)]);
        }

        $imports = [];
        foreach ($storedFiles as $index => $storedFile) {
            $imports[] = AssessmentImport::query()->create([
                'disk' => $disk,
                'path' => $storedFile['path'],
                'original_name' => $storedFile['original_name'],
                'status' => 'queued',
                'summary' => $validations[$index]['summary'],
                'requested_by' => session('user_id') ?: null,
            ]);
        }

        Bus::chain(array_map(
            static fn (AssessmentImport $import): ProcessAssessmentImportJob => new ProcessAssessmentImportJob($import->id),
            $imports
        ))->dispatch();

        $summary = count($imports) === 1
            ? [...$validations[0]['summary'], 'files' => 1]
            : [
                'assessment_code' => null,
                'assessment_title' => null,
                'files' => count($imports),
                'forms' => array_sum(array_map(static fn (AssessmentImport $import): int => (int) ($import->summary['forms'] ?? 0), $imports)),
                'fields' => array_sum(array_map(static fn (AssessmentImport $import): int => (int) ($import->summary['fields'] ?? 0), $imports)),
                'assignments' => array_sum(array_map(static fn (AssessmentImport $import): int => (int) ($import->summary['assignments'] ?? 0), $imports)),
            ];

        return back()->with('assessment_import_notice', [
            'import_id' => count($imports) === 1 ? $imports[0]->id : null,
            'import_ids' => array_map(static fn (AssessmentImport $import): int => $import->id, $imports),
            'message' => count($imports).' file JSON valid dan akan diimport satu per satu melalui queue default.',
            'summary' => $summary,
        ]);
    }

    public function status(AssessmentImport $import): JsonResponse
    {
        $this->authorizeAccess();

        return response()->json([
            'data' => $import,
            'meta' => ['schema' => 'assessment-import-status-v1'],
        ]);
    }

    private function authorizeAccess(): void
    {
        abort_unless(
            in_array(session('role'), ['admin', 'superadmin', 'kepala', 'database'], true),
            403
        );
    }
}
