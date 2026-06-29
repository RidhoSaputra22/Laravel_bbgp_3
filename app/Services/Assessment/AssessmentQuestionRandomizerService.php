<?php

namespace App\Services\Assessment;

use App\Enum\AssessmentInstrumentType;
use App\Models\AssessmentAssignmentTarget;
use App\Models\AssessmentFormField;
use App\Support\Assessment\AssessmentStructureMetadataResolver;
use App\Support\Assessment\ChoiceOptionNormalizer;

class AssessmentQuestionRandomizerService
{
    public function __construct(
        private readonly AssessmentStructureMetadataResolver $metadataResolver
    ) {}

    public function buildSnapshot(AssessmentAssignmentTarget $target): array
    {
        $assignment = $target->assignment;
        $targetId = (int) ($target->getKey() ?? 0);

        $assessments = $assignment->assessments
            ->filter(fn ($assessment) => (bool) $assessment->is_active)
            ->values()
            ->map(function ($assessment) use ($targetId) {
                $instrumentType = AssessmentInstrumentType::tryFromMixed($assessment->instrument_type);
                $assessmentMeta = $this->metadataResolver->decorateAssessment([
                    'id' => $assessment->id,
                    'kode_assessment' => $assessment->kode_assessment,
                    'judul' => $assessment->judul,
                    'deskripsi' => $assessment->deskripsi,
                    'petunjuk' => $assessment->petunjuk,
                    'instrument_type' => $assessment->instrument_type,
                    'scoring_config' => $assessment->scoring_config,
                ]);
                $forms = $assessment->forms
                    ->filter(fn ($form) => (bool) $form->is_active)
                    ->values()
                    ->map(function ($form) use ($assessment, $assessmentMeta, $instrumentType, $targetId) {
                        $formMeta = $this->metadataResolver->decorateForm([
                            'id' => $form->id,
                            'judul_form' => $form->judul_form,
                            'kode_form' => $form->kode_form,
                            'deskripsi' => $form->deskripsi,
                            'kompetensi' => $form->kompetensi,
                            'indikator_kode' => $form->indikator_kode,
                            'indikator_label' => $form->indikator_label,
                            'is_scoreable' => $form->is_scoreable,
                            'scoring_config' => $form->scoring_config,
                            'fields' => $form->fields->map(fn ($field) => [
                                'label' => $field->label,
                                'deskripsi' => $field->deskripsi,
                                'bantuan' => $field->bantuan,
                            ])->all(),
                        ], $assessmentMeta);
                        $fields = $form->fields
                            ->filter(fn ($field) => (bool) $field->is_active)
                            ->values()
                            ->map(fn ($field) => $this->mapField(
                                $field,
                                $assessment->id,
                                $form->id,
                                $instrumentType,
                                $targetId
                            ))
                            ->all();

                        if ($fields === []) {
                            return null;
                        }

                        return [
                            'id' => $formMeta['id'],
                            'assessment_id' => $assessment->id,
                            'judul_form' => $formMeta['judul_form'],
                            'kode_form' => $formMeta['kode_form'],
                            'deskripsi' => $formMeta['deskripsi'],
                            'kompetensi' => $formMeta['kompetensi'],
                            'kompetensi_label' => $formMeta['kompetensi_label'],
                            'indikator_kode' => $formMeta['indikator_kode'],
                            'indikator_label' => $formMeta['indikator_label'],
                            'is_scoreable' => (bool) ($formMeta['is_scoreable'] ?? false),
                            'scoring_config' => $form->scoring_config,
                            'fields' => $fields,
                        ];
                    })
                    ->filter()
                    ->values()
                    ->all();

                if ($forms === []) {
                    return null;
                }

                return [
                    'id' => $assessmentMeta['id'],
                    'kode_assessment' => $assessmentMeta['kode_assessment'],
                    'judul' => $assessmentMeta['judul'],
                    'deskripsi' => $assessmentMeta['deskripsi'],
                    'petunjuk' => $assessmentMeta['petunjuk'],
                    'instrument_type' => $assessmentMeta['instrument_type'],
                    'instrument_label' => $assessmentMeta['instrument_label'],
                    'scoring_config' => $assessment->scoring_config,
                    'forms' => $forms,
                ];
            })
            ->filter()
            ->values()
            ->all();

        $allFields = collect($assessments)
            ->flatMap(fn ($assessment) => $assessment['forms'] ?? [])
            ->flatMap(fn ($form) => $form['fields'] ?? []);

        return [
            'generated_at' => now()->toIso8601String(),
            'assignment' => [
                'id' => $assignment->id,
                'kode_penugasan' => $assignment->kode_penugasan,
                'judul_penugasan' => $assignment->judul_penugasan,
            ],
            'assessments' => $assessments,
            'meta' => [
                'total_questions' => $allFields->count(),
                'required_questions' => $allFields->where('is_required', true)->count(),
                'randomization' => [
                    'version' => 2,
                    'question_order' => 'fixed',
                    'choice_order' => 'radio_options_for_pilihan_ganda_kompleks',
                ],
            ],
        ];
    }

    private function mapField(
        AssessmentFormField $field,
        int $assessmentId,
        int $formId,
        ?AssessmentInstrumentType $instrumentType,
        int $targetId
    ): array {
        return [
            'id' => $field->id,
            'assessment_id' => $assessmentId,
            'assessment_form_id' => $formId,
            'label' => $field->label,
            'deskripsi' => $field->deskripsi,
            'nama_field' => $field->nama_field,
            'tipe_field' => $field->tipe_field,
            'placeholder' => $field->placeholder,
            'bantuan' => $field->bantuan,
            'opsi_field' => $this->mapFieldOptions($field, $instrumentType, $targetId, $assessmentId, $formId),
            'validasi' => $field->validasi,
            'scoring_config' => $field->scoring_config,
            'is_required' => (bool) $field->is_required,
        ];
    }

    private function mapFieldOptions(
        AssessmentFormField $field,
        ?AssessmentInstrumentType $instrumentType,
        int $targetId,
        int $assessmentId,
        int $formId
    ): array {
        if ($field->tipe_field === 'repeater') {
            return is_array($field->opsi_field) ? $field->opsi_field : [];
        }

        $options = $this->normalizeOptions($field->opsi_field);

        if (! $this->shouldRandomizeChoiceOptions($field, $instrumentType)) {
            return $options;
        }

        return collect($options)
            ->shuffle($this->resolveChoiceOptionSeed($targetId, $assessmentId, $formId, (int) $field->id))
            ->values()
            ->all();
    }

    private function normalizeOptions(?array $options): array
    {
        return collect(ChoiceOptionNormalizer::normalizeMany($options ?? []))
            ->map(fn ($option) => [
                'label' => $option['label'],
                'value' => $option['value'],
                'score' => $option['score'],
                'level_kompetensi' => $option['level_kompetensi'],
                'level_kompetensi_label' => $option['level_kompetensi_label'],
            ])
            ->filter(fn ($option) => $option['value'] !== '')
            ->values()
            ->all();
    }

    private function shouldRandomizeChoiceOptions(
        AssessmentFormField $field,
        ?AssessmentInstrumentType $instrumentType
    ): bool {
        return $instrumentType === AssessmentInstrumentType::PILIHAN_GANDA_KOMPLEKS
            && $field->tipe_field === 'radio';
    }

    private function resolveChoiceOptionSeed(
        int $targetId,
        int $assessmentId,
        int $formId,
        int $fieldId
    ): int {
        return (int) sprintf(
            '%u',
            crc32(implode('|', [
                'assessment-choice',
                $targetId,
                $assessmentId,
                $formId,
                $fieldId,
            ]))
        );
    }
}
