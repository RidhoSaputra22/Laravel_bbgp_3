<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    use HasFactory;
    protected $fillable = [
        'nama_kegiatan',
        'tempat_kegiatan',
        'tgl_kegiatan',
        'tgl_selesai',
        'jam_mulai',
        'jam_selesai',
        'deskripsi_kegiatan',
        'status' // aktif tidak nya kegiatan itu
    ];

    public function penomoran () {
        return $this->hasOne(PenomoranKegiatan::class);
    } 

}
