<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentAssignmentSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_assignment_id',
        'nomor_sesi',
        'label_sesi',
        'waktu_mulai',
        'waktu_selesai',
        'kapasitas_peserta',
        'total_peserta',
        'durasi_sesi_jam',
    ];

    protected $casts = [
        'nomor_sesi' => 'integer',
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'kapasitas_peserta' => 'integer',
        'total_peserta' => 'integer',
        'durasi_sesi_jam' => 'integer',
    ];

    public function assignment()
    {
        return $this->belongsTo(AssessmentAssignment::class, 'assessment_assignment_id');
    }

    public function targets()
    {
        return $this->hasMany(AssessmentAssignmentTarget::class)->orderBy('id');
    }

    public function getRentangJamLabelAttribute(): ?string
    {
        if (! $this->waktu_mulai) {
            return null;
        }

        if (! $this->waktu_selesai) {
            return $this->waktu_mulai->format('H:i').' WITA';
        }

        return $this->waktu_mulai->format('H:i').' - '.$this->waktu_selesai->format('H:i').' WITA';
    }

    public function getJadwalSesiLabelAttribute(): ?string
    {
        if (! $this->waktu_mulai) {
            return null;
        }

        if (! $this->waktu_selesai) {
            return $this->waktu_mulai->format('d M Y H:i').' WITA';
        }

        if ($this->waktu_mulai->isSameDay($this->waktu_selesai)) {
            return $this->waktu_mulai->format('d M Y').', '.$this->rentang_jam_label;
        }

        return $this->waktu_mulai->format('d M Y H:i').' - '.$this->waktu_selesai->format('d M Y H:i').' WITA';
    }
}
