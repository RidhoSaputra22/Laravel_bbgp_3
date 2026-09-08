<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValidatorAssignmentResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'validator_assignment_id',
        'validator_form_field_id',
        'answer_text',
        'answer_payload',
        'score',
    ];

    protected $casts = [
        'answer_payload' => 'array',
        'score' => 'float',
    ];

    public function assignment()
    {
        return $this->belongsTo(ValidatorAssignment::class, 'validator_assignment_id');
    }

    public function field()
    {
        return $this->belongsTo(ValidatorFormField::class, 'validator_form_field_id');
    }
}
