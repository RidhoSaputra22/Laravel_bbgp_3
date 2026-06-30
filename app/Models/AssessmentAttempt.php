<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_assignment_target_id',
        'status',
        'structure_snapshot',
        'security_config_snapshot',
        'result_summary',
        'scoring_summary',
        'total_questions',
        'required_questions',
        'answered_questions',
        'answered_required_questions',
        'serious_violation_count',
        'warning_violation_count',
        'started_at',
        'deadline_at',
        'submitted_at',
        'completion_mode',
        'timed_out_at',
        'last_answered_at',
        'last_violation_at',
        'disqualified_at',
        'disqualification_reason',
    ];

    protected $casts = [
        'structure_snapshot' => 'array',
        'security_config_snapshot' => 'array',
        'result_summary' => 'array',
        'scoring_summary' => 'array',
        'started_at' => 'datetime',
        'deadline_at' => 'datetime',
        'submitted_at' => 'datetime',
        'timed_out_at' => 'datetime',
        'last_answered_at' => 'datetime',
        'last_violation_at' => 'datetime',
        'disqualified_at' => 'datetime',
        'total_questions' => 'integer',
        'required_questions' => 'integer',
        'answered_questions' => 'integer',
        'answered_required_questions' => 'integer',
        'serious_violation_count' => 'integer',
        'warning_violation_count' => 'integer',
    ];

    public function target()
    {
        return $this->belongsTo(AssessmentAssignmentTarget::class, 'assessment_assignment_target_id');
    }

    public function answers()
    {
        return $this->hasMany(AssessmentAttemptAnswer::class)->orderBy('assessment_form_id')->orderBy('id');
    }

    public function securityEvents()
    {
        return $this->hasMany(AssessmentAttemptSecurityEvent::class)
            ->orderByDesc('client_occurred_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }
}
