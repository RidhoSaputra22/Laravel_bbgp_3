<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValidatorFormSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'validator_form_id',
        'title',
        'description',
        'sort_order',
    ];

    public function validatorForm()
    {
        return $this->belongsTo(ValidatorForm::class);
    }

    public function fields()
    {
        return $this->hasMany(ValidatorFormField::class)->orderBy('sort_order')->orderBy('id');
    }
}
