<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KodeHonor extends Model
{
    use HasFactory;

    protected $table = 'penomoran_kegiatans';

    protected $fillable = [
        'no_surat',
        'tgl_surat',
        'kode_anggaran',
        'kegiatan_id',
    ];
}
