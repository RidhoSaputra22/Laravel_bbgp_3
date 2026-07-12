<?php

namespace App\Models;

use App\Enum\AssessmentKetenagaanType;
use App\Models\Pivots\AssessmentAssignmentAssessment;
use App\Support\Assessment\AssessmentSchoolTargetKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_penugasan',
        'judul_penugasan',
        'session_enabled',
        'target_ketenagaan',
        'assessment_combination_id',
        'target_jabatan',
        'target_kabupaten',
        'target_satuan_pendidikan',
        'deskripsi',
        'tanggal_mulai',
        'jam_mulai',
        'tanggal_selesai',
        'kapasitas_per_sesi',
        'durasi_sesi_jam',
        'security_config',
        'total_sesi',
        'status_distribusi',
        'total_target',
        'total_ditugaskan',
        'assigned_by',
        'job_batch_id',
        'processed_at',
    ];

    protected $casts = [
        'session_enabled' => 'boolean',
        'target_jabatan' => 'array',
        'target_kabupaten' => 'array',
        'target_satuan_pendidikan' => 'array',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'security_config' => 'array',
        'processed_at' => 'datetime',
        'kapasitas_per_sesi' => 'integer',
        'durasi_sesi_jam' => 'integer',
        'total_sesi' => 'integer',
        'total_target' => 'integer',
        'total_ditugaskan' => 'integer',
    ];

    public function assessments()
    {
        return $this->belongsToMany(Assessment::class, 'assessment_assignment_assessments')
            ->using(AssessmentAssignmentAssessment::class)
            ->withPivot('urutan', 'stage_config')
            ->withTimestamps()
            ->orderBy('assessment_assignment_assessments.urutan');
    }

    public function combination()
    {
        return $this->belongsTo(AssessmentCombination::class, 'assessment_combination_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function targets()
    {
        return $this->hasMany(AssessmentAssignmentTarget::class)
            ->orderBy('assessment_assignment_session_id')
            ->orderBy('id');
    }

    public function sessions()
    {
        return $this->hasMany(AssessmentAssignmentSession::class)
            ->orderBy('nomor_sesi');
    }

    public function getJamMulaiLabelAttribute(): ?string
    {
        if (! $this->jam_mulai) {
            return null;
        }

        return substr((string) $this->jam_mulai, 0, 5);
    }

    public function usesSessionScheduling(): bool
    {
        return (bool) ($this->session_enabled ?? true);
    }

    public function getSessionModeLabelAttribute(): string
    {
        return $this->usesSessionScheduling()
            ? 'Terjadwal per sesi'
            : 'Tanpa sesi';
    }

    public function getTargetKetenagaanLabelAttribute(): ?string
    {
        return AssessmentKetenagaanType::tryFromMixed($this->target_ketenagaan)?->label();
    }

    public function getTargetKetenagaanBadgeClassAttribute(): string
    {
        return AssessmentKetenagaanType::tryFromMixed($this->target_ketenagaan)?->badgeClass() ?? 'secondary';
    }

    public function getTargetJabatanLabelsAttribute(): array
    {
        return collect($this->target_jabatan ?? [])
            ->filter(fn ($jabatan) => filled($jabatan))
            ->map(fn ($jabatan) => (string) $jabatan)
            ->values()
            ->all();
    }

    public function getTargetKabupatenLabelsAttribute(): array
    {
        return collect($this->target_kabupaten ?? [])
            ->filter(fn ($kabupaten) => filled($kabupaten))
            ->map(fn ($kabupaten) => (string) $kabupaten)
            ->values()
            ->all();
    }

    public function getTargetSatuanPendidikanLabelsAttribute(): array
    {
        return collect($this->target_satuan_pendidikan ?? [])
            ->filter(fn ($selectionKey) => filled($selectionKey))
            ->map(fn ($selectionKey) => AssessmentSchoolTargetKey::label($selectionKey))
            ->filter(fn (string $label) => $label !== '')
            ->values()
            ->all();
    }
}
