<?php

namespace App\Services\Assessment;

use App\Models\ValidatorAssignment;
use App\Support\Assessment\ValidatorAccess;
use Illuminate\Http\Request;

class ValidatorPortalWidgetService
{
    private const SELECTED_TASK_SESSION_PREFIX = 'assessment_portal.validator_task_id';

    public function __construct(
        private readonly AssessmentPortalAuthService $authService
    ) {}

    /**
     * Build the validator widget data shared by every portal page.
     *
     * @return array<string, mixed>
     */
    public function build(Request $request): array
    {
        $emptyData = [
            'isValidator' => false,
            'validatorTasks' => collect(),
            'validatorTaskCount' => 0,
            'pendingValidatorTaskCount' => 0,
            'selectedValidatorTask' => null,
            'validatorResponseLookup' => collect(),
            'validatorRecommendations' => ValidatorAssignment::RECOMMENDATIONS,
        ];

        $portalUser = $this->authService->currentUser()?->loadMissing('guru');

        if (! ValidatorAccess::isEligibleUser($portalUser)) {
            return $emptyData;
        }

        $validatorBaseQuery = ValidatorAssignment::query()
            ->where('validator_user_id', $portalUser->id)
            ->where('status', '!=', 'cancelled');

        $validatorTaskCount = (clone $validatorBaseQuery)->count();
        $pendingValidatorTaskCount = (clone $validatorBaseQuery)
            ->whereIn('status', ['assigned', 'in_progress'])
            ->count();
        $validatorTasks = $validatorBaseQuery
            ->with(['validatorForm', 'assessment', 'assessmentAssignments'])
            ->withSummaryColumns()
            ->newestFirst()
            ->limit(10)
            ->get();

        $selectedValidatorTask = $this->resolveSelectedTask($request, $portalUser->id);
        $validatorResponseLookup = $selectedValidatorTask
            ? $selectedValidatorTask->responses->keyBy('validator_form_field_id')
            : collect();

        return [
            'isValidator' => true,
            'validatorTasks' => $validatorTasks,
            'validatorTaskCount' => $validatorTaskCount,
            'pendingValidatorTaskCount' => $pendingValidatorTaskCount,
            'selectedValidatorTask' => $selectedValidatorTask,
            'validatorResponseLookup' => $validatorResponseLookup,
            'validatorRecommendations' => ValidatorAssignment::RECOMMENDATIONS,
        ];
    }

    private function resolveSelectedTask(Request $request, int $validatorUserId): ?ValidatorAssignment
    {
        $sessionKey = $this->selectedTaskSessionKey($validatorUserId);

        if ($request->has('validator_task')) {
            $requestedTaskId = $request->integer('validator_task');

            if ($requestedTaskId > 0) {
                session()->put($sessionKey, $requestedTaskId);
            } else {
                session()->forget($sessionKey);
            }
        }

        $selectedTaskId = (int) session()->get($sessionKey, 0);

        if ($selectedTaskId < 1) {
            return null;
        }

        $selectedTask = ValidatorAssignment::query()
            ->whereKey($selectedTaskId)
            ->where('validator_user_id', $validatorUserId)
            ->where('status', '!=', 'cancelled')
            ->with([
                'validatorForm.sections.fields',
                'responses.field',
            ])
            ->first();

        if (! $selectedTask) {
            session()->forget($sessionKey);
        }

        return $selectedTask;
    }

    private function selectedTaskSessionKey(int $validatorUserId): string
    {
        return self::SELECTED_TASK_SESSION_PREFIX.'.'.$validatorUserId;
    }
}
