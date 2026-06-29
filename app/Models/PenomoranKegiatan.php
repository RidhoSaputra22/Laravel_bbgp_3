<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenomoranKegiatan extends Model
{
    use HasFactory;
    protected $fillable = [
        'no_surat',
        'tgl_surat',
        'kode_anggaran',
        'kegiatan_id',
    ];


    public function kegiatan () {
        return $this->hasOne(Kegiatan::class, 'id', 'kegiatan_id');
    }
}
