<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use App\Services\Assessment\AssessmentPortalResultService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PortalResultController extends Controller
{
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
}
