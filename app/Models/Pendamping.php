<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pendamping extends Model
{
    use HasFactory;
    protected $fillable = [
        'nama',
        'kota',
        'transport_pergi',
        'transport_pulang',
        'hotel',
        'hari_1',
        'hari_2',
        'hari_3',
        'is_verif'
    ];
}
