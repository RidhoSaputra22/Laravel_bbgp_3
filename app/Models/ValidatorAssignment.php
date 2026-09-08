<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValidatorAssignment extends Model
{
    use HasFactory;

    public const RECOMMENDATIONS = [
        'approved' => 'Layak tanpa revisi',
        'minor_revision' => 'Layak dengan revisi kecil',
        'major_revision' => 'Layak dengan revisi besar',
        'rejected' => 'Belum layak digunakan',
    ];

    protected $fillable = [
        'code',
        'title',
        'validator_form_id',
        'assessment_id',
        'validator_user_id',
        'assigned_by',
        'notes',
        'assessment_snapshot',
        'validator_snapshot',
        'status',
        'start_date',
        'due_date',
        'started_at',
        'submitted_at',
        'score_total',
        'score_max',
        'score_percentage',
        'recommendation',
        'final_notes',
    ];

    protected $casts = [
        'assessment_snapshot' => 'array',
        'validator_snapshot' => 'array',
        'start_date' => 'date',
        'due_date' => 'date',
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'score_total' => 'float',
        'score_max' => 'float',
        'score_percentage' => 'float',
    ];

    public function scopeNewestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('created_at')->orderByDesc('id');
    }

    public function validatorForm()
    {
        return $this->belongsTo(ValidatorForm::class);
    }

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validator_user_id');
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function responses()
    {
        return $this->hasMany(ValidatorAssignmentResponse::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'in_progress' => 'Sedang Dikerjakan',
            'submitted' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => 'Ditugaskan',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'in_progress' => 'warning',
            'submitted' => 'success',
            'cancelled' => 'secondary',
            default => 'primary',
        };
    }

    public function getRecommendationLabelAttribute(): ?string
    {
        return self::RECOMMENDATIONS[$this->recommendation] ?? null;
    }
}
