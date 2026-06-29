<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentAttemptAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_attempt_id',
        'assessment_id',
        'assessment_form_id',
        'assessment_form_field_id',
        'answer_text',
        'answer_payload',
        'auto_score',
        'auto_score_reason',
        'auto_score_metadata',
        'auto_scored_at',
        'assessor_score',
        'assessor_notes',
        'assessor_user_id',
        'assessor_scored_at',
        'answer_file_path',
        'answered_at',
    ];

    protected $casts = [
        'answer_payload' => 'array',
        'auto_score' => 'float',
        'auto_score_metadata' => 'array',
        'auto_scored_at' => 'datetime',
        'assessor_score' => 'integer',
        'assessor_scored_at' => 'datetime',
        'answered_at' => 'datetime',
    ];

    public function attempt()
    {
        return $this->belongsTo(AssessmentAttempt::class, 'assessment_attempt_id');
    }

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function form()
    {
        return $this->belongsTo(AssessmentForm::class, 'assessment_form_id');
    }

    public function field()
    {
        return $this->belongsTo(AssessmentFormField::class, 'assessment_form_field_id');
    }

    public function assessor()
    {
        return $this->belongsTo(User::class, 'assessor_user_id');
    }
}
