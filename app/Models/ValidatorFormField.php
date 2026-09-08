<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValidatorFormField extends Model
{
    use HasFactory;

    public const TYPES = [
        'text' => 'Teks Singkat',
        'textarea' => 'Teks Panjang',
        'number' => 'Angka',
        'date' => 'Tanggal',
        'select' => 'Daftar Pilihan',
        'radio' => 'Pilihan Tunggal',
        'checkbox' => 'Pilihan Jamak',
        'likert' => 'Skala Likert',
    ];

    protected $fillable = [
        'validator_form_section_id',
        'label',
        'description',
        'field_type',
        'options',
        'is_required',
        'is_scored',
        'max_score',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'is_scored' => 'boolean',
        'is_active' => 'boolean',
        'max_score' => 'float',
    ];

    public function section()
    {
        return $this->belongsTo(ValidatorFormSection::class, 'validator_form_section_id');
    }

    public function responses()
    {
        return $this->hasMany(ValidatorAssignmentResponse::class);
    }

    public function resolvedOptions(): array
    {
        if ($this->field_type === 'likert' && empty($this->options)) {
            return ['1', '2', '3', '4', '5'];
        }

        return collect($this->options ?? [])
            ->map(fn ($option) => trim((string) $option))
            ->filter()
            ->values()
            ->all();
    }
}
