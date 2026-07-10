<?php

namespace App\Http\Controllers;

use App\Services\Assessment\AssessmentMonitoringService;
use Illuminate\Http\Request;

class AssessmentMonitoringController extends Controller
{
    private string $menu = 'assessment-monitoring';

    public function __construct(
        private readonly AssessmentMonitoringService $assessmentMonitoringService
    ) {}

    public function index(Request $request)
    {
        $this->authorizeAccess();

        $perPage = max(5, min((int) $request->input('assignment_per_page', 10), 50));
        $page = max((int) $request->input('assignment_page', 1), 1);
        $monitoringExplorerMode = $this->resolveMonitoringExplorerMode($request);
        $monitoringExplorerPerPage = max(10, min((int) $request->input('monitor_per_page', 25), 50));

        return view('pages.admin.assessment.monitoring.index', [
            'menu' => $this->menu,
            'monitoringPanel' => $this->assessmentMonitoringService->buildGlobalDashboard($perPage, $page),
            'monitoringExplorer' => $this->assessmentMonitoringService->buildGlobalExplorer(
                $this->resolveMonitoringExplorerFilters($request),
                $monitoringExplorerMode,
                $monitoringExplorerPerPage,
                max((int) $request->input('monitor_page', 1), 1)
            ),
        ]);
    }

    private function authorizeAccess(): void
    {
        abort_unless(
            in_array(session('role'), ['admin', 'superadmin', 'kepala', 'database'], true),
            403
        );
    }

    private function resolveMonitoringExplorerFilters(Request $request): array
    {
        return [
            'kabupaten' => $this->normalizeMonitoringExplorerFilterValue(
                $request->input('monitor_kabupaten')
            ),
            'jabatan' => $this->normalizeMonitoringExplorerFilterValue(
                $request->input('monitor_jabatan')
            ),
            'satuan_pendidikan' => $this->normalizeMonitoringExplorerFilterValue(
                $request->input('monitor_satuan_pendidikan')
            ),
        ];
    }

    private function resolveMonitoringExplorerMode(Request $request): string
    {
        $mode = trim((string) $request->input('monitor_view', 'individual'));

        return in_array($mode, ['individual', 'summary'], true)
            ? $mode
            : 'individual';
    }

    private function normalizeMonitoringExplorerFilterValue(mixed $value): ?string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : null;
    }
}
