<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Services\Assessment\AssessmentAttemptLifecycleService;
use App\Services\Assessment\AssessmentAttemptSecurityService;
use App\Services\Assessment\AssessmentAttemptService;
use App\Services\Assessment\AssessmentPortalAuthService;
use App\Services\Assessment\AssessmentPortalService;
use App\Services\Assessment\AssessmentPortalStageService;
use Illuminate\Http\Request;

class PortalController extends Controller
{
    public function __construct(
        private readonly AssessmentPortalAuthService $authService,
        private readonly AssessmentPortalService $portalService,
        private readonly AssessmentAttemptLifecycleService $attemptLifecycleService,
        private readonly AssessmentAttemptService $attemptService,
        private readonly AssessmentAttemptSecurityService $attemptSecurityService,
        private readonly AssessmentPortalStageService $stageService
    ) {}

    public function landing()
    {
        return $this->authService->isAuthenticated()
            ? redirect()->route('assessment.portal.dashboard')
            : redirect()->route('assessment.portal.auth');
    }

    public function dashboard()
    {
        $guru = $this->requireGuru();
        $dashboardCards = collect($this->portalService->getDashboardTargets($guru))
            ->map(function (array $item) {
                $target = $this->attemptLifecycleService->syncExpiredTarget($item['target']);

                return [
                    'target' => $target,
                    'meta' => $this->portalService->buildTargetMeta($target),
                ];
            })
            ->values()
            ->all();

        return view('assessment.index', [
            'menu' => 'assessment-portal',
            'guru' => $guru,
            'dashboardCards' => $dashboardCards,
        ]);
    }

    public function start(Request $request, string $id)
    {
        $guru = $this->requireGuru();
        $target = $this->portalService->findTargetForGuru($guru, (int) $id);
        $target = $this->attemptLifecycleService->syncExpiredTarget($target);
        $meta = $this->portalService->buildTargetMeta($target);

        if ($meta['status'] === 'submitted') {
            return redirect()->route('assessment.portal.result', $target->id);
        }

        if (! in_array($meta['status'], ['ready', 'in_progress'], true)) {
            return redirect()
                ->route('assessment.portal.dashboard')
                ->withErrors([
                    'portal' => $meta['description'],
                ]);
        }

        $attempt = $this->attemptLifecycleService->ensureAttempt($target, false);
        $stageContext = $this->stageService->resolveStartContext($request, $target, $attempt);

        if ($stageContext['uses_stage_flow']) {
            if ($stageContext['error'] !== null) {
                return redirect()
                    ->route('assessment.portal.show', $stageContext['redirect_params'])
                    ->withErrors([
                        'portal' => $stageContext['error'],
                    ]);
            }

            return redirect()
                ->route('assessment.portal.show', $stageContext['redirect_params'])
                ->with('assessment_portal_success', 'Tahap assessment dimulai.');
        }

        $this->attemptLifecycleService->ensureAttempt($target, true);

        return redirect()
            ->route('assessment.portal.show', $target->id)
            ->with('assessment_portal_success', 'Assessment dimulai. Timer pengerjaan sudah berjalan.');
    }

    public function show(Request $request, string $id)
    {
        $guru = $this->requireGuru();
        $target = $this->portalService->findTargetForGuru($guru, (int) $id);
        $target = $this->attemptLifecycleService->syncExpiredTarget($target);
        $meta = $this->portalService->buildTargetMeta($target);

        if ($meta['status'] === 'submitted') {
            return redirect()->route('assessment.portal.result', $target->id);
        }

        $attempt = $this->attemptLifecycleService->ensureAttempt($target, false);
        $stageState = $this->stageService->resolveShowState($request, $target, $attempt);
        $attempt = $stageState['attempt'];
        $stageFlowEnabled = $stageState['stage_flow_enabled'];
        $renderStageOverview = $stageState['render_stage_overview'];

        if (! $stageFlowEnabled && $meta['status'] === 'ready') {
            return redirect()
                ->route('assessment.portal.dashboard')
                ->withErrors([
                    'portal' => 'Klik tombol Mulai Ujian terlebih dahulu agar waktu mulai dan timer assessment tercatat.',
                ]);
        }

        if ($meta['status'] !== 'in_progress') {
            if (! $stageFlowEnabled || $meta['status'] !== 'ready') {
                return redirect()
                    ->route('assessment.portal.dashboard')
                    ->withErrors([
                        'portal' => $meta['description'],
                    ]);
            }
        }

        if (! $stageFlowEnabled) {
            $attempt = $this->attemptLifecycleService->ensureAttempt($target, true);
        }

        $currentStageIndex = $stageState['current_stage_index'];

        if ($this->attemptSecurityService->hasReachedSeriousLimit($attempt, $currentStageIndex) && ! $attempt->disqualified_at) {
            $this->attemptSecurityService->disqualify($attempt, [
                'reason' => 'Assessment dihentikan karena batas pelanggaran guard ujian telah tercapai.',
                'record_trigger' => false,
                'metadata' => [
                    'source' => 'portal_show_enforcement',
                ],
            ]);

            return redirect()
                ->route('assessment.portal.result', $target->id)
                ->with('assessment_portal_warning', 'Assessment dihentikan karena batas pelanggaran guard ujian telah tercapai.');
        }

        $freshTarget = $target->fresh([
            'assignment.assessments.forms.fields',
            'assignment.combination',
            'combination',
            'session',
            'attempt.answers',
            'attempt.securityEvents',
        ]);
        $freshAttempt = $freshTarget->attempt ?: $attempt->fresh([
            'answers',
            'securityEvents',
            'target.assignment.assessments.forms.fields',
            'target.assignment.combination',
            'target.combination',
            'target.session',
            'target.guru',
        ]);

        if ($renderStageOverview) {
            return view('assessment.show.overview', [
                'menu' => 'assessment-portal',
                'guru' => $guru,
                'target' => $freshTarget,
                'attempt' => $freshAttempt,
                'meta' => $this->portalService->buildTargetMeta($freshTarget),
                'stageOverview' => $this->portalService->buildStageOverview($freshTarget, $freshAttempt),
            ]);
        }

        return view('assessment.show.show', [
            'menu' => 'assessment-portal',
            'guru' => $guru,
            'target' => $freshTarget,
            'attempt' => $freshAttempt,
            'meta' => $this->portalService->buildTargetMeta($freshTarget),
            'selectedStageIndex' => $currentStageIndex,
            'answerLookup' => $this->attemptService->buildAnswerLookup($freshAttempt),
            'securityPayload' => $this->attemptSecurityService->buildClientPayload($freshAttempt, $currentStageIndex),
        ]);
    }

    private function requireGuru(): Guru
    {
        $guru = $this->authService->currentGuru();

        abort_unless($guru, 403);

        return $guru;
    }
}
