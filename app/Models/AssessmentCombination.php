<?php

namespace App\Models;

use App\Enum\AssessmentKetenagaanType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentCombination extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_combination_generation_id',
        'generation_sequence',
        'kode_kombinasi',
        'judul',
        'deskripsi',
        'target_ketenagaan',
        'random_seed',
        'signature_hash',
        'selection_config',
        'structure_snapshot',
        'total_assessments',
        'total_forms',
        'total_questions',
        'generated_by',
        'generated_at',
        'is_active',
    ];

    protected $casts = [
        'generation_sequence' => 'integer',
        'selection_config' => 'array',
        'structure_snapshot' => 'array',
        'total_assessments' => 'integer',
        'total_forms' => 'integer',
        'total_questions' => 'integer',
        'generated_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function items()
    {
        return $this->hasMany(AssessmentCombinationItem::class)
            ->orderBy('assessment_order')
            ->orderBy('form_order')
            ->orderBy('field_order')
            ->orderBy('id');
    }

    public function assignments()
    {
        return $this->hasMany(AssessmentAssignment::class, 'assessment_combination_id');
    }

    public function assignmentTargets()
    {
        return $this->hasMany(AssessmentAssignmentTarget::class, 'assessment_combination_id');
    }

    public function generation()
    {
        return $this->belongsTo(AssessmentCombinationGeneration::class, 'assessment_combination_generation_id');
    }

    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by');
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
