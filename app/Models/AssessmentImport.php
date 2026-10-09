<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentImport extends Model
{
    use HasFactory;

    protected $fillable = [
        'disk',
        'path',
        'original_name',
        'status',
        'summary',
        'error_message',
        'requested_by',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'summary' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
