<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RtlDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'rtl_id',
        'file_path',
        'original_name',
    ];

    protected $appends = ['url'];

    public function rtl()
    {
        return $this->belongsTo(Rtl::class);
    }

    public function getUrlAttribute()
    {
        $path = $this->file_path;
        
        // Sekarang kita selalu menggunakan prefix 'upload' karena folder 'public_html/upload' 
        // sudah kita hubungkan (symlink) ke folder 'storage/app/public'
        return asset('upload/' . $path);
    }
}
