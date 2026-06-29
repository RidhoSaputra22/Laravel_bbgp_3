<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Berita extends Model
{
    use HasFactory;
    protected $fillable = [
        'judul',
        'isi',
        'thumbnail',
        'status',
        'author',
        'tgl_publish',
        'kategori_id',
    ];

    public function kategori_post () {
        return $this->hasOne(KategoriPost::class);
    } 

}
