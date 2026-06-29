<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rtl extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_ktp',
        'id_kegiatan',
        'status',
        'admin_notes',
        'certificate_file',
    ];

    protected $appends = ['cert_url'];

    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class, 'id_kegiatan');
    }

    public function documents()
    {
        return $this->hasMany(RtlDocument::class);
    }

    public function user()
    {
        // Many external users are in Guru table
        return $this->belongsTo(Guru::class, 'no_ktp', 'no_ktp');
    }

    public function getCertUrlAttribute()
    {
        if (!$this->certificate_file) return null;

        // Semua file sertifikat sekarang diakses via prefix 'upload'
        return asset('upload/' . $this->certificate_file);
    }
}
