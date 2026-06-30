<?php

namespace App\Http\Controllers;

use App\Services\Assessment\AssessmentMonitoringService;

class AssessmentMonitoringController extends Controller
{
    private string $menu = 'assessment-monitoring';

    public function __construct(
        private readonly AssessmentMonitoringService $assessmentMonitoringService
    ) {}

    public function index()
    {
        $this->authorizeAccess();

        return view('pages.admin.assessment.monitoring.index', [
            'menu' => $this->menu,
            'monitoringPanel' => $this->assessmentMonitoringService->buildGlobalDashboard(),
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
