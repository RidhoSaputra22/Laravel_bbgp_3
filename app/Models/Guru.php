<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_lengkap',
        'email',
        'no_ktp',
        'nip',
        'tempat_lahir',
        'tgl_lahir',
        'gender',
        'jabatan',
        'status',
        'status_kepegawaian',
        'agama',
        'pendidikan',
        'kabupaten',
        'satuan_pendidikan',
        'alamat_satuan',
        'alamat_rumah',
        'no_hp',
        'no_wa',
        'pas_foto',
        'no_rek',
        'npsn_sekolah',
        'npwp',
        'nuptk',
        'eksternal_jabatan',
        'jenis_jabatan',
        'kategori_jabatan',
        'tugas_jabatan',
        'latar_jabatan',
        'jenis_bank',
        'is_verif',
    ];

    public function sekolah()
    {
        return $this->hasOne(Sekolah::class, 'npsn_sekolah', 'npsn_sekolah');
    }

    public function assessmentAssignmentTargets()
    {
        return $this->hasMany(AssessmentAssignmentTarget::class);
    }

    public function assessmentAttempts()
    {
        return $this->hasManyThrough(
            AssessmentAttempt::class,
            AssessmentAssignmentTarget::class,
            'guru_id',
            'assessment_assignment_target_id'
        );
    }
}
