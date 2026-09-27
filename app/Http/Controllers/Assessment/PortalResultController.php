<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use App\Models\AssessmentAttemptAnswer;
use App\Services\Assessment\AssessmentPortalAuthService;
use App\Services\Assessment\AssessmentPortalResultService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PortalResultController extends Controller
{
    private const ADMIN_ROLES = ['admin', 'superadmin', 'kepala', 'database'];

    public function __construct(
        private readonly AssessmentPortalResultService $resultService
    ) {}

    public function result(Request $request, string $id)
    {
        $resultContext = $this->resultService->resolveContext($request, (int) $id);

        if ($resultContext instanceof RedirectResponse) {
            return $resultContext;
        }

        return view('assessment.result.result', $this->resultService->buildResultViewData($request, $resultContext));
    }

    public function downloadResultPdf(Request $request, string $id)
    {
        $resultContext = $this->resultService->resolveContext($request, (int) $id);

        if ($resultContext instanceof RedirectResponse) {
            return $resultContext;
        }

        abort_unless($this->resultService->canDownloadStakeholderResult($resultContext['target']), 404);

        $pdf = Pdf::loadView(
            'assessment.result.pdf.stakeholder',
            $this->resultService->buildStakeholderPdfViewData($resultContext)
        );

        $pdf->setPaper('a4', 'portrait');

        return $pdf->download(
            $this->resultService->buildStakeholderPdfFilename(
                $resultContext['target'],
                $resultContext['guru']
            )
        );
    }

    public function file(AssessmentAttemptAnswer $answer)
    {
        $answer->loadMissing('attempt.target');

        abort_unless($this->canViewFile($answer), 403);

        $path = trim((string) $answer->answer_file_path);
        abort_unless(
            $path !== '' && str_starts_with($path, 'assessment/attempts/') && ! str_contains($path, '..'),
            404
        );

        $disk = Storage::disk('assessment_private');

        if (! $disk->exists($path)) {
            $disk = Storage::disk('public');
        }

        abort_unless($disk->exists($path), 404);

        $stream = $disk->readStream($path);
        abort_unless(is_resource($stream), 404);

        $fileName = trim((string) data_get($answer->answer_payload, 'original_name')) ?: basename($path);
        $fileName = preg_replace('/[^A-Za-z0-9._ -]/', '_', Str::ascii($fileName)) ?: 'assessment-file';
        $fileName = trim($fileName, " .\t\n\r\0\x0B") ?: 'assessment-file';
        $mimeType = $disk->mimeType($path) ?: 'application/octet-stream';
        $contentDisposition = in_array($mimeType, [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
        ], true) ? 'inline' : 'attachment';

        return response()->stream(function () use ($stream): void {
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type' => $mimeType,
            'Content-Length' => (string) $disk->size($path),
            'Content-Disposition' => $contentDisposition.'; filename="'.addcslashes($fileName, "\\\"").'"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    private function canViewFile(AssessmentAttemptAnswer $answer): bool
    {
        if (session('cek') && in_array(strtolower((string) session('role')), self::ADMIN_ROLES, true)) {
            return true;
        }

        $guruId = app(AssessmentPortalAuthService::class)->currentGuruId();

        return $guruId !== null && (int) $answer->attempt?->target?->guru_id === $guruId;
    }
}
