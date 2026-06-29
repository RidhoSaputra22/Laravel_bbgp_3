<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agenda extends Model
{
    use HasFactory;
    protected $fillable = [
        'thumbnail',
        'nama_kegiatan',
        'tempat_kegiatan',
        'tgl_kegiatan',
        'tgl_selesai',
        'jam_mulai',
        'jam_selesai',
        'deskripsi_kegiatan',
        'tgl_publish',
        'author',
        'status' // aktif tidak nya kegiatan itu
    ];
}
