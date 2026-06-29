<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentAssignmentTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_assignment_id',
        'assessment_assignment_session_id',
        'guru_id',
        'status',
        'assigned_at',
        'started_at',
        'submitted_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

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

    public function attempt()
    {
        return $this->hasOne(AssessmentAttempt::class, 'assessment_assignment_target_id');
    }
}
