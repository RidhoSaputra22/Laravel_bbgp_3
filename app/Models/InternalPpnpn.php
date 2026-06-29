<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalPpnpn extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_pegawai',
        'nik',
        'jabatan',
        'kegiatan',
        'tempat',
        'kabupaten',
        'tgl_kegiatan',
        'tgl_selesai_kegiatan',
        'jam_mulai',
        'jam_selesai',
        'deskripsi',
    ];


    public function pegawai() {
        return $this->hasOne(PegawaiPpnpn::class, 'id', 'id_pegawai' );
    }
    
}
