<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Internal extends Model
{
    use HasFactory;
    protected $fillable = [
        'nama',
        'nip',
        'nik',
        'jenis',
        'golongan',
        'jabatan',
        'kegiatan',
        'tempat',
        'tgl_kegiatan',
        'tgl_selesai_kegiatan',
        'jam_mulai',
        'jam_selesai',
        'is_verif',
        'transport_pulang',
        'transport_pergi',
        'bukti_bill',
        'kota',
        'hotel',
        'hari_1',
        'hari_2',
        'hari_3',
        'hari_4',
        'hari_5',
        'hari_6',
        'hari_7',
        'bill_penginapan',
        'deskripsi',
    ];
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'nik', 'no_ktp');
    }
}
