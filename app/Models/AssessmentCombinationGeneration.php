<?php

namespace App\Models;

use App\Enum\AssessmentKetenagaanType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentCombinationGeneration extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_generate',
        'target_ketenagaan',
        'total_kombinasi',
        'selection_config',
        'status',
        'job_batch_id',
        'generated_by',
        'processed_at',
    ];

    protected $casts = [
        'selection_config' => 'array',
        'total_kombinasi' => 'integer',
        'processed_at' => 'datetime',
    ];

    public function combinations()
    {
        return $this->hasMany(AssessmentCombination::class, 'assessment_combination_generation_id')
            ->orderBy('generation_sequence')
            ->orderBy('id');
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

    public function getStatusMetaAttribute(): array
    {
        $completedCount = (int) ($this->combinations_count ?? 0);
        $totalRequested = (int) $this->total_kombinasi;

        if ($this->status === 'selesai') {
            return [
                'label' => 'Selesai',
                'badge_class' => 'success',
            ];
        }

        if ($this->status === 'gagal') {
            return [
                'label' => $completedCount > 0 && $completedCount < $totalRequested ? 'Gagal Sebagian' : 'Gagal',
                'badge_class' => $completedCount > 0 ? 'warning' : 'danger',
            ];
        }

        return [
            'label' => 'Diproses',
            'badge_class' => 'info',
        ];
    }
}
