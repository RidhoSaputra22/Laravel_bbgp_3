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
        'assessment_assignment_snapshots',
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
        'assessment_assignment_snapshots' => 'array',
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
        return $query->orderByDesc($this->qualifyColumn('id'));
    }

    public function scopeWithSummaryColumns(Builder $query): Builder
    {
        return $query->select([
            'validator_assignments.id',
            'validator_assignments.code',
            'validator_assignments.title',
            'validator_assignments.validator_form_id',
            'validator_assignments.assessment_id',
            'validator_assignments.validator_user_id',
            'validator_assignments.status',
            'validator_assignments.start_date',
            'validator_assignments.due_date',
            'validator_assignments.submitted_at',
            'validator_assignments.score_percentage',
            'validator_assignments.recommendation',
            'validator_assignments.created_at',
        ]);
    }

    public function validatorForm()
    {
        return $this->belongsTo(ValidatorForm::class);
    }

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function assessmentAssignments()
    {
        return $this->belongsToMany(AssessmentAssignment::class, 'validator_assignment_assessment_assignments')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderBy('validator_assignment_assessment_assignments.sort_order');
    }

    public function getResolvedAssignmentSnapshotsAttribute(): array
    {
        if (! empty($this->assessment_assignment_snapshots)) {
            return $this->assessment_assignment_snapshots;
        }

        return [[
            'id' => null,
            'code' => null,
            'title' => data_get($this->assessment_snapshot, 'title', 'Penugasan lama'),
            'target_ketenagaan_label' => data_get($this->assessment_snapshot, 'target_ketenagaan'),
            'assessments' => [$this->assessment_snapshot],
            'captured_at' => data_get($this->assessment_snapshot, 'captured_at'),
        ]];
    }

    public function getAssessmentAssignmentsLabelAttribute(): string
    {
        if ($this->relationLoaded('assessmentAssignments') && $this->assessmentAssignments->isNotEmpty()) {
            $titles = $this->assessmentAssignments->pluck('judul_penugasan')->filter();

            return $titles->take(2)->implode(', ').($titles->count() > 2 ? ' +'.($titles->count() - 2).' lainnya' : '');
        }

        if ($this->relationLoaded('assessment') && $this->assessment) {
            return $this->assessment->judul;
        }

        $snapshots = collect($this->resolved_assignment_snapshots);
        $titles = $snapshots->pluck('title')->filter()->take(2)->implode(', ');
        $remaining = max(0, $snapshots->count() - 2);

        return $titles.($remaining > 0 ? ' +'.$remaining.' lainnya' : '');
    }

    public function getAssessmentAssignmentsTotalAttribute(): int
    {
        if ($this->relationLoaded('assessmentAssignments') && $this->assessmentAssignments->isNotEmpty()) {
            return $this->assessmentAssignments->count();
        }

        return count($this->resolved_assignment_snapshots);
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
