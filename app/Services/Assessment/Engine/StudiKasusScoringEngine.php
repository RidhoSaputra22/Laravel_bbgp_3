<?php

namespace App\Services\Assessment\Engine;

use App\Models\AssessmentAttemptAnswer;
use Illuminate\Support\Collection;

class StudiKasusScoringEngine extends BaseInstrumentScoringEngine
{
    protected function fieldOverrides(array $assessment, array $form, array $field, int $fieldIndex): array
    {
        $defaultWeights = [20, 25, 30, 15];
        $rubricCodes = ['K1', 'K2', 'K3', 'K4'];
        $existingConfig = is_array($field['scoring_config'] ?? null) ? $field['scoring_config'] : [];

        return [
            'scoring_config' => array_filter([
                'method' => $existingConfig['method'] ?? (($field['tipe_field'] ?? 'text') === 'textarea' ? 'semantic_similarity' : null),
                'weight' => $existingConfig['weight'] ?? ($defaultWeights[$fieldIndex] ?? null),
                'rubric_code' => $existingConfig['rubric_code'] ?? ($rubricCodes[$fieldIndex] ?? null),
            ], fn ($value) => $value !== null && $value !== ''),
        ];
    }

    protected function buildSyntheticItems(
        array $assessment,
        array $form,
        Collection $answersByFieldId,
        array $fieldItems
    ): array {
        $formConfig = $this->configNormalizer->normalizeForm($form, $assessment);
        $profile = $formConfig['profile'] ?? 'generic';

        if (! in_array($profile, ['studi_kasus', 'study_case_default'], true)) {
            return [];
        }

        $combinedAnswerText = collect($answersByFieldId)
            ->filter(fn ($answer) => $this->answerHasContent($answer))
            ->map(function (AssessmentAttemptAnswer $answer) {
                return trim((string) data_get($answer->answer_payload ?? [], 'value', $answer->answer_text));
            })
            ->filter()
            ->implode(' ');

        if ($combinedAnswerText === '') {
            return [];
        }

        $syntheticConfig = array_merge([
            'enabled' => true,
            'method' => 'semantic_similarity',
            'weight' => 10,
            'rubric_code' => 'K5',
            'reference_answer' => 'Jawaban harus logis, etis, inklusif, jelas, layak diterapkan, dan mampu membangun dukungan kolaboratif.',
            'keyword_groups_text' => implode("\n", [
                'etika|adil|integritas|objektif',
                'inklusif|aksesibel|aman|keselamatan',
                'komunikasi|kolaborasi|pemangku kepentingan|orang tua',
                'layak|realistis|sumber daya|dukungan',
            ]),
            'min_words' => 25,
        ], is_array(data_get($formConfig, 'advanced_rules.synthetic_k5', [])) ? data_get($formConfig, 'advanced_rules.synthetic_k5', []) : []);

        $virtualAnswer = new AssessmentAttemptAnswer([
            'answer_text' => $combinedAnswerText,
            'answer_payload' => [
                'type' => 'textarea',
                'value' => $combinedAnswerText,
            ],
        ]);
        $result = $this->fieldAutoScoringEngine->score([
            'id' => 'synthetic-k5-'.$form['id'],
            'label' => 'K5. Kelayakan, etika, dan komunikasi jawaban',
            'tipe_field' => 'textarea',
            'scoring_config' => $syntheticConfig,
        ], $virtualAnswer, $form, $assessment);

        return [[
            'field_id' => null,
            'field_label' => 'K5. Kelayakan, etika, dan komunikasi jawaban',
            'field_type' => 'synthetic',
            'rubric_code' => 'K5',
            'weight' => 10.0,
            'answered' => true,
            'score' => $result['score'],
            'formatted_score' => is_numeric($result['score']) ? number_format((float) $result['score'], 2) : null,
            'level' => is_numeric($result['score']) ? \App\Enum\LevelKompetensi::fromScore((float) $result['score'])?->shortLabel() : null,
            'score_source' => $result['source'] ?? 'auto_semantic_similarity',
            'manual_pending' => (bool) ($result['requires_manual_review'] ?? false),
            'assessor_score' => null,
            'auto_score' => is_numeric($result['score']) ? round((float) $result['score'], 2) : null,
            'auto_confidence' => $result['confidence'] ?? null,
            'auto_reason' => $result['reason'] ?? null,
            'auto_metadata' => $result['metadata'] ?? null,
            'scoring_config' => $syntheticConfig,
        ]];
    }
}
