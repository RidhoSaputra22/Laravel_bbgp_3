<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessAssessmentImportJob;
use App\Models\Assessment;
use App\Models\AssessmentImport;
use App\Services\Assessment\AssessmentPackageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
            'file' => ['required', 'file', 'mimes:json,txt', 'max:524288'],
        ], [
            'file.required' => 'File JSON assessment wajib dipilih.',
            'file.mimes' => 'File import harus berformat JSON.',
            'file.max' => 'Ukuran file import maksimal 512 MB.',
        ]);

        $file = $validated['file'];
        $disk = 'assessment_private';
        $path = $file->storeAs(
            'assessment-imports',
            Str::uuid()->toString().'.json',
            $disk
        );

        if (! $path) {
            return back()->withErrors(['file' => 'File JSON gagal disimpan sementara.']);
        }

        $absolutePath = Storage::disk($disk)->path($path);
        $validation = $this->service->validateFile($absolutePath);

        if (! $validation['valid']) {
            Storage::disk($disk)->delete($path);

            return back()
                ->withInput()
                ->withErrors(['file' => implode(' ', $validation['errors'])]);
        }

        $import = AssessmentImport::query()->create([
            'disk' => $disk,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'status' => 'queued',
            'summary' => $validation['summary'],
            'requested_by' => session('user_id') ?: null,
        ]);

        ProcessAssessmentImportJob::dispatch($import->id);

        return back()->with('assessment_import_notice', [
            'import_id' => $import->id,
            'message' => 'JSON valid dan import masuk antrean queue default.',
            'summary' => $validation['summary'],
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
