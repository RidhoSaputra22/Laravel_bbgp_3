<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KuitansiLoka extends Model
{
    use HasFactory;
    protected $fillable = [
        'internal_id',
        'no_surat_tugas',
        'tgl_surat_tugas',
        'kode_anggaran',
        'tahun_anggaran',
        'no_bukti',
    ];

    public function internal() {
        return $this->hasOne(Internal::class, 'id', 'internal_id');
    }

    public function internalMany() {
        return $this->hasMany(Internal::class, 'id', 'internal_id');
    }
    
}
