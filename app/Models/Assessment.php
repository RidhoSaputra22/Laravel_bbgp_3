<?php

namespace App\Models;

use App\Enum\AssessmentKetenagaanType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_assessment',
        'judul',
        'slug',
        'deskripsi',
        'petunjuk',
        'instrument_type',
        'target_ketenagaan',
        'scoring_config',
        'status',
        'is_active',
    ];

    protected $casts = [
        'scoring_config' => 'array',
        'is_active' => 'boolean',
    ];

    public function forms()
    {
        return $this->hasMany(AssessmentForm::class)->orderBy('urutan');
    }

    public function assignments()
    {
        return $this->belongsToMany(AssessmentAssignment::class, 'assessment_assignment_assessments')
            ->withPivot('urutan')
            ->withTimestamps()
            ->orderByDesc('assessment_assignments.id');
    }

    public function getTargetKetenagaanLabelAttribute(): ?string
    {
        return AssessmentKetenagaanType::tryFromMixed($this->target_ketenagaan)?->label();
    }

    public function getTargetKetenagaanBadgeClassAttribute(): string
    {
        return AssessmentKetenagaanType::tryFromMixed($this->target_ketenagaan)?->badgeClass() ?? 'secondary';
    }
}
