<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transportasi extends Model
{
    use HasFactory;
    protected $fillable = [
        'kuitansi_id',
        'asal_transport',
        'tujuan_transport',
        'transportasi',
        'keterangan',
        'biaya_transport',
    ];

    
    
}
