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
        return route('user.rtl.document', $this->id);
    }
}
