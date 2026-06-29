<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenomoranKegiatan extends Model
{
    use HasFactory;
    protected $fillable = [
        'surat_tugas',
        'tgl_surat',
        'kode_anggaran',
        'kegiatan_id',
    ];
}
