<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Berkas extends Model
{
    use HasFactory;
    protected $fillable = [
        'nik',
        'nama_berkas',
        'nama_kegiatan',
        'metode_upload',
        'status',
    ];

    public function pegawai() {
        return $this->hasOne(Pegawai::class, 'nik', 'no_ktp');
    }
}
