<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentAssignmentTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_assignment_id',
        'assessment_assignment_session_id',
        'assessment_combination_id',
        'guru_id',
        'status',
        'assigned_at',
        'started_at',
        'deadline_at',
        'submitted_at',
        'completion_mode',
        'timed_out_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'deadline_at' => 'datetime',
        'submitted_at' => 'datetime',
        'timed_out_at' => 'datetime',
    ];

    public function scopeLatestAssignmentFirst(Builder $query): Builder
    {
        return $query
            ->orderByDesc($this->qualifyColumn('assessment_assignment_id'))
            ->orderByDesc($this->qualifyColumn('assigned_at'))
            ->orderByDesc($this->qualifyColumn('id'));
    }

    public function assignment()
    {
        return $this->belongsTo(AssessmentAssignment::class, 'assessment_assignment_id');
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function session()
    {
        return $this->belongsTo(AssessmentAssignmentSession::class, 'assessment_assignment_session_id');
    }

    public function combination()
    {
        return $this->belongsTo(AssessmentCombination::class, 'assessment_combination_id');
    }

    public function attempt()
    {
        return $this->hasOne(AssessmentAttempt::class, 'assessment_assignment_target_id');
    }
}
