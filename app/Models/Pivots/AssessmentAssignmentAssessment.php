<?php

namespace App\Models\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

class AssessmentAssignmentAssessment extends Pivot
{
    protected $table = 'assessment_assignment_assessments';

    protected $casts = [
        'stage_config' => 'array',
        'urutan' => 'integer',
    ];
}
