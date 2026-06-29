<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class transportasi extends Model
{
    use HasFactory;
    protected $fillable = [
        'asal_transport',
        'tujuan_transport',
        'transportasi',
        'keterangan',
        'biaya_transport',
    ];

    
    
}
