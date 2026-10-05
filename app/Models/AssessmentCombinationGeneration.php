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
        'target_jabatan',
        'total_kombinasi',
        'selection_config',
        'status',
        'job_batch_id',
        'reset_source_generation_id',
        'generated_by',
        'processed_at',
    ];

    protected $casts = [
        'selection_config' => 'array',
        'target_jabatan' => 'array',
        'total_kombinasi' => 'integer',
        'reset_source_generation_id' => 'integer',
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

    public function targetJabatanSelections(): array
    {
        $targetJabatan = $this->getAttribute('target_jabatan');

        if ($targetJabatan !== null) {
            return Assessment::normalizeTargetJabatan($targetJabatan) ?: [Assessment::TARGET_JABATAN_ALL];
        }

        return Assessment::normalizeTargetJabatan(
            data_get($this->selection_config, 'target_jabatan', [])
        ) ?: [Assessment::TARGET_JABATAN_ALL];
    }

    public function getTargetJabatanLabelsAttribute(): array
    {
        return in_array(Assessment::TARGET_JABATAN_ALL, $this->targetJabatanSelections(), true)
            ? ['Semua Jabatan']
            : $this->targetJabatanSelections();
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
