<?php

namespace Tests\Unit;

use App\Models\AssessmentAssignment;
use App\Models\AssessmentAssignmentTarget;
use Tests\TestCase;

class AssessmentAssignmentOrderingScopeTest extends TestCase
{
    public function test_assessment_assignment_newest_first_scope_orders_by_creation_time_then_id(): void
    {
        $orders = AssessmentAssignment::query()
            ->newestFirst()
            ->getQuery()
            ->orders;

        $this->assertSame([
            [
                'column' => 'assessment_assignments.created_at',
                'direction' => 'desc',
            ],
            [
                'column' => 'assessment_assignments.id',
                'direction' => 'desc',
            ],
        ], $orders);
    }

    public function test_assessment_assignment_target_latest_assignment_first_scope_prioritizes_newest_assignment(): void
    {
        $orders = AssessmentAssignmentTarget::query()
            ->latestAssignmentFirst()
            ->getQuery()
            ->orders;

        $this->assertSame([
            [
                'column' => 'assessment_assignment_targets.assessment_assignment_id',
                'direction' => 'desc',
            ],
            [
                'column' => 'assessment_assignment_targets.assigned_at',
                'direction' => 'desc',
            ],
            [
                'column' => 'assessment_assignment_targets.id',
                'direction' => 'desc',
            ],
        ], $orders);
    }
}
