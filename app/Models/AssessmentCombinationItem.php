<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentCombinationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_combination_id',
        'assessment_id',
        'assessment_form_id',
        'assessment_form_field_id',
        'assessment_code',
        'assessment_title',
        'instrument_type',
        'form_code',
        'form_title',
        'form_description',
        'kompetensi',
        'indikator_kode',
        'indikator_label',
        'form_is_scoreable',
        'form_scoring_config',
        'field_label',
        'field_description',
        'field_name',
        'field_type',
        'field_placeholder',
        'field_help',
        'field_options',
        'field_validation',
        'field_scoring_config',
        'field_width',
        'field_is_required',
        'assessment_order',
        'form_order',
        'field_order',
    ];

    protected $casts = [
        'form_is_scoreable' => 'boolean',
        'form_scoring_config' => 'array',
        'field_options' => 'array',
        'field_validation' => 'array',
        'field_scoring_config' => 'array',
        'field_is_required' => 'boolean',
        'assessment_order' => 'integer',
        'form_order' => 'integer',
        'field_order' => 'integer',
    ];

    public function combination()
    {
        return $this->belongsTo(AssessmentCombination::class, 'assessment_combination_id');
    }

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function form()
    {
        return $this->belongsTo(AssessmentForm::class, 'assessment_form_id');
    }

    public function field()
    {
        return $this->belongsTo(AssessmentFormField::class, 'assessment_form_field_id');
    }
}
