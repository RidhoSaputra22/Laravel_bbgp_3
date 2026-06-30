<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentAttemptSecurityEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_attempt_id',
        'event_key',
        'violation_type',
        'lock_mode',
        'message',
        'counts_toward_disqualify',
        'client_occurred_at',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'counts_toward_disqualify' => 'boolean',
        'client_occurred_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function attempt()
    {
        return $this->belongsTo(AssessmentAttempt::class, 'assessment_attempt_id');
    }
}
