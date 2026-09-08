<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValidatorForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'description',
        'instructions',
        'status',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function sections()
    {
        return $this->hasMany(ValidatorFormSection::class)->orderBy('sort_order')->orderBy('id');
    }

    public function assignments()
    {
        return $this->hasMany(ValidatorAssignment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'published' => 'Dipublikasikan',
            'inactive' => 'Nonaktif',
            default => 'Draft',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'published' => 'success',
            'inactive' => 'secondary',
            default => 'warning',
        };
    }
}
