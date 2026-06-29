<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id',
        'judul_form',
        'kode_form',
        'deskripsi',
        'kompetensi',
        'indikator_kode',
        'indikator_label',
        'is_scoreable',
        'scoring_config',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'is_scoreable' => 'boolean',
        'scoring_config' => 'array',
        'is_active' => 'boolean',
    ];

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function fields()
    {
        return $this->hasMany(AssessmentFormField::class)->orderBy('urutan');
    }
}
