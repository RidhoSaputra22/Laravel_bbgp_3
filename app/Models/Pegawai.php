<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    use HasFactory;
    protected $fillable = [
        'username',
        'nama_lengkap',
        'email',
        'no_ktp',
        'nip',
        'tempat_lahir',
        'tgl_lahir',
        'gender',
        'jabatan',
        'jenis_pegawai',
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
        'instansi',
        'no_rek',
        'jenis_bank',
        'golongan',
        'is_verif',
    ];

    public function internals()
    {
        return $this->hasMany(Internal::class, 'nik', 'no_ktp');
    }
    public function berkas() {
        return $this->hasMany(Berkas::class, 'nik', 'no_ktp');
    }
    public function user() {
        return $this->hasOne(Berkas::class, 'no_ktp', 'no_ktp');
    }
}
