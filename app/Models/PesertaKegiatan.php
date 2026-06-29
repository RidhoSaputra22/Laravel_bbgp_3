<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesertaKegiatan extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_kegiatan',
        'nama',
        'no_ktp',
        'nip',
        'alamat', // alamat kerja
        'kabupaten', // kabupaten kerja
        'status_keikutpesertaan',
        'instansi',
        'golongan',
        'jenis_gol',
        'diluar_gol',
        'jkl',
        'kelengkapan_peserta_transport',
        'kelengkapan_peserta_biodata',
        'no_hp',
        'no_wa',
        'no_surat_tugas',
        'tgl_surat_tugas',
        'email',
        'mata_pelajaran',
        'status',
        'tempat_lahir',
        'tgl_lahir',
        'agama',
        'pendidikan',
        'alamat_rumah',
        'kabupaten_rumah',
        'npwp'
    ];

    public function kegiatan()
    {
        return $this->hasOne(Kegiatan::class, 'id', 'id_kegiatan');
    }

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'no_ktp', 'no_ktp');
    }

    public function eksternal()
    {
        return $this->hasOne(Guru::class, 'no_ktp', 'no_ktp');
    }

    public function getEksternal()
    {
        return $this->hasMany(Guru::class, 'no_ktp', 'no_ktp');
    }

    public function getKegiatan()
    {
        return $this->hasMany(Kegiatan::class, 'id', 'id_kegiatan');
    }
}
