<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Honor extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_peserta',
        'golongan',
        'jenis_gol',
        'jp_realisasi',
        'jumlah',
        'jumlah_honor',
        'potongan',
    ];

    public function peserta() {
        return $this->hasOne(PesertaKegiatan::class, 'id', 'id_peserta');
    }


    
}
