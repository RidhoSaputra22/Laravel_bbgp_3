<?php

namespace App\Services\Assessment;

use App\Jobs\SyncAssessmentTargetsToMongoJob;
use App\Jobs\SyncValidatorAssignmentsToMongoJob;
use App\Models\AssessmentAssignmentTarget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AssessmentTargetSyncDispatcher
{
    public function assignments(array $assignmentIds): void
    {
        if (! $this->active()) {
            return;
        }

        $this->dispatch(
            AssessmentAssignmentTarget::query()
                ->whereIn('assessment_assignment_id', $this->ids($assignmentIds))
                ->where('is_validator', false)
                ->pluck('id')
                ->all()
        );
    }

    public function assessments(array $assessmentIds): void
    {
        $ids = $this->ids($assessmentIds);
        if ($ids === [] || (! $this->active() && ! (bool) config('assessment_mongodb.enabled'))) {
            return;
        }

        if ($this->active()) {
            $this->dispatch($this->pivotTargets('assessment_id', $ids));
        }
        $this->dispatchValidatorsForAssessments($ids);
    }

    public function forms(array $formIds): void
    {
        $ids = $this->ids($formIds);
        if ($ids === [] || (! $this->active() && ! (bool) config('assessment_mongodb.enabled'))) {
            return;
        }

        if ($this->active()) {
            $this->dispatch(DB::table('assessment_assignment_targets as target')
                ->join('assessment_assignment_assessments as stage', 'stage.assessment_assignment_id', '=', 'target.assessment_assignment_id')
                ->join('assessment_forms as form', 'form.assessment_id', '=', 'stage.assessment_id')
                ->whereIn('form.id', $ids)
                ->where('target.is_validator', false)
                ->distinct()
                ->pluck('target.id')
                ->all());
        }

        $assessmentIds = DB::table('assessment_forms')
            ->whereIn('id', $ids)
            ->distinct()
            ->pluck('assessment_id')
            ->all();

        $this->dispatchValidatorsForAssessments($assessmentIds);
    }

    public function fields(array $fieldIds): void
    {
        $ids = $this->ids($fieldIds);
        if ($ids === [] || (! $this->active() && ! (bool) config('assessment_mongodb.enabled'))) {
            return;
        }

        if ($this->active()) {
            $this->dispatch(DB::table('assessment_assignment_targets as target')
                ->join('assessment_assignment_assessments as stage', 'stage.assessment_assignment_id', '=', 'target.assessment_assignment_id')
                ->join('assessment_forms as form', 'form.assessment_id', '=', 'stage.assessment_id')
                ->join('assessment_form_fields as field', 'field.assessment_form_id', '=', 'form.id')
                ->whereIn('field.id', $ids)
                ->where('target.is_validator', false)
                ->distinct()
                ->pluck('target.id')
                ->all());
        }

        $assessmentIds = DB::table('assessment_form_fields as field')
            ->join('assessment_forms as form', 'form.id', '=', 'field.assessment_form_id')
            ->whereIn('field.id', $ids)
            ->distinct()
            ->pluck('form.assessment_id')
            ->all();

        $this->dispatchValidatorsForAssessments($assessmentIds);
    }

    public function combinations(array $combinationIds): void
    {
        if (! $this->active()) {
            return;
        }

        $ids = $this->ids($combinationIds);
        if ($ids === []) {
            return;
        }

        $this->dispatch(AssessmentAssignmentTarget::query()
            ->where('is_validator', false)
            ->where(function ($query) use ($ids): void {
                $query->whereIn('assessment_combination_id', $ids)
                    ->orWhereHas('assignment', fn ($assignment) => $assignment->whereIn('assessment_combination_id', $ids));
            })
            ->pluck('id')
            ->all());
    }

    private function pivotTargets(string $column, array $ids): array
    {
        $ids = $this->ids($ids);
        if ($ids === []) {
            return [];
        }

        return DB::table('assessment_assignment_targets as target')
            ->join('assessment_assignment_assessments as stage', 'stage.assessment_assignment_id', '=', 'target.assessment_assignment_id')
            ->whereIn('stage.'.$column, $ids)
            ->where('target.is_validator', false)
            ->distinct()
            ->pluck('target.id')
            ->all();
    }

    private function dispatch(array $targetIds): void
    {
        SyncAssessmentTargetsToMongoJob::dispatchIds(array_map('intval', $targetIds));
    }

    private function dispatchValidatorsForAssessments(array $assessmentIds): void
    {
        $ids = $this->ids($assessmentIds);
        if ($ids === [] || ! (bool) config('assessment_mongodb.enabled')) {
            return;
        }

        $query = DB::table('validator_assignments as validator')
            ->whereIn('validator.assessment_id', $ids);

        if (Schema::hasTable('validator_assignment_assessment_assignments')) {
            $query->orWhereExists(function ($subquery) use ($ids): void {
                $subquery
                    ->selectRaw('1')
                    ->from('validator_assignment_assessment_assignments as source')
                    ->join(
                        'assessment_assignment_assessments as stage',
                        'stage.assessment_assignment_id',
                        '=',
                        'source.assessment_assignment_id'
                    )
                    ->whereColumn('source.validator_assignment_id', 'validator.id')
                    ->whereIn('stage.assessment_id', $ids);
            });
        }

        SyncValidatorAssignmentsToMongoJob::dispatchIds(
            $query->distinct()->pluck('validator.id')->all()
        );
    }

    private function active(): bool
    {
        return (bool) config('assessment_mongodb.enabled')
            && in_array((string) config('assessment_mongodb.sync_driver', 'php'), ['dual', 'go'], true);
    }

    private function ids(array $ids): array
    {
        return collect($ids)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }
}
