<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenyewaanRuangan extends Model
{
    use HasFactory;
    protected $fillable = [
        'tipe_ruangan',
        'nama_ruangan',
        'harga_per_malam',
        'rincian_harga',
        'foto_utama',
        'status',
        'is_active',
    ];
}
