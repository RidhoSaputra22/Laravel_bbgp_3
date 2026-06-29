<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Artikel extends Model
{
    use HasFactory;
    protected $fillable = [
        'judul',
        'isi',
        'thumbnail',
        'author',
        'status',
        'tgl_publish',
        'kategori_id'
    ];

    public function kategori_post () {
        return $this->hasOne(KategoriPost::class);
    } 
}
