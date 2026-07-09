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

        return view('pages.admin.assessment.monitoring.index', [
            'menu' => $this->menu,
            'monitoringPanel' => $this->assessmentMonitoringService->buildGlobalDashboard($perPage, $page),
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
