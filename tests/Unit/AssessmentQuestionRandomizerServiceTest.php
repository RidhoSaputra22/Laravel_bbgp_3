<?php

namespace Tests\Unit;

use App\Enum\AssessmentInstrumentType;
use App\Models\Assessment;
use App\Models\AssessmentAssignment;
use App\Models\AssessmentAssignmentTarget;
use App\Models\AssessmentForm;
use App\Models\AssessmentFormField;
use App\Services\Assessment\AssessmentQuestionRandomizerService;
use Illuminate\Support\Collection;
use Tests\TestCase;

class AssessmentQuestionRandomizerServiceTest extends TestCase
{
    public function test_it_keeps_competency_level_metadata_in_snapshot_options(): void
    {
        $field = $this->makeField(
            id: 301,
            label: 'Soal 1',
            type: 'radio',
            options: [
                [
                    'label' => 'A',
                    'value' => 'Mengenali kondisi belajar peserta didik.',
                    'score' => 1,
                    'level_kompetensi' => 1,
                ],
                [
                    'label' => 'B',
                    'value' => 'Mengembangkan strategi belajar yang adaptif.',
                    'score' => 5,
                    'level_kompetensi' => 5,
                ],
            ],
            extra: [
                'deskripsi' => 'Deskripsi soal',
                'bantuan' => 'Pilih satu jawaban',
                'scoring_config' => [
                    'enabled' => true,
                    'method' => 'choice_option_score',
                ],
            ],
        );
        $target = $this->makeTarget(21, [
            $this->makeAssessment(
                id: 101,
                instrumentType: null,
                forms: [
                    $this->makeForm(201, [$field], [
                        'judul_form' => 'Form Kompetensi',
                        'kode_form' => 'FORM-1',
                        'deskripsi' => 'Deskripsi form',
                        'scoring_config' => ['profile' => 'pilihan_ganda_kompleks'],
                    ]),
                ],
                extra: [
                    'kode_assessment' => 'ASM-1',
                    'judul' => 'Assessment Kompetensi',
                    'deskripsi' => 'Deskripsi assessment',
                    'petunjuk' => 'Petunjuk',
                    'scoring_config' => ['weight' => 0.40],
                ],
            ),
        ]);

        $snapshot = app(AssessmentQuestionRandomizerService::class)->buildSnapshot($target);
        $options = data_get($snapshot, 'assessments.0.forms.0.fields.0.opsi_field');

        $this->assertSame(1, $options[0]['level_kompetensi']);
        $this->assertSame(1.0, $options[0]['score']);
        $this->assertSame('Level 1: Paham', $options[0]['level_kompetensi_label']);
        $this->assertSame('Mengenali kondisi belajar peserta didik.', $options[0]['label']);
        $this->assertSame('A', $options[0]['value']);

        $this->assertSame(5, $options[1]['level_kompetensi']);
        $this->assertSame(5.0, $options[1]['score']);
        $this->assertSame('Level 5: Ahli', $options[1]['level_kompetensi_label']);
        $this->assertSame('B', $options[1]['value']);
        $this->assertTrue((bool) data_get($snapshot, 'assessments.0.forms.0.fields.0.scoring_config.enabled'));
        $this->assertSame('choice_option_score', data_get($snapshot, 'assessments.0.forms.0.fields.0.scoring_config.method'));
    }

    public function test_it_keeps_question_order_and_only_randomizes_radio_choices_for_multiple_choice_assessment(): void
    {
        $service = app(AssessmentQuestionRandomizerService::class);
        $originalRadioOrder = ['A', 'B', 'C', 'D', 'E'];

        $firstSnapshot = $service->buildSnapshot($this->makeRandomizationTarget(21));
        $secondSnapshot = $service->buildSnapshot($this->makeRandomizationTarget(21));
        $otherSnapshot = $service->buildSnapshot($this->makeRandomizationTarget(22));

        $this->assertSame(
            [301, 302],
            collect(data_get($firstSnapshot, 'assessments.0.forms.0.fields', []))->pluck('id')->all()
        );
        $this->assertSame(
            [401],
            collect(data_get($firstSnapshot, 'assessments.1.forms.0.fields', []))->pluck('id')->all()
        );

        $firstMultipleChoiceOptionOrder = collect(data_get(
            $firstSnapshot,
            'assessments.0.forms.0.fields.0.opsi_field',
            []
        ))->pluck('value')->all();
        $secondMultipleChoiceOptionOrder = collect(data_get(
            $secondSnapshot,
            'assessments.0.forms.0.fields.0.opsi_field',
            []
        ))->pluck('value')->all();
        $otherMultipleChoiceOptionOrder = collect(data_get(
            $otherSnapshot,
            'assessments.0.forms.0.fields.0.opsi_field',
            []
        ))->pluck('value')->all();
        $portfolioOptionOrder = collect(data_get(
            $firstSnapshot,
            'assessments.1.forms.0.fields.0.opsi_field',
            []
        ))->pluck('value')->all();

        $this->assertSame($firstMultipleChoiceOptionOrder, $secondMultipleChoiceOptionOrder);
        $this->assertNotSame($originalRadioOrder, $firstMultipleChoiceOptionOrder);
        $this->assertNotSame($firstMultipleChoiceOptionOrder, $otherMultipleChoiceOptionOrder);
        $this->assertSame($originalRadioOrder, $portfolioOptionOrder);
        $this->assertSame(2, data_get($firstSnapshot, 'meta.randomization.version'));
        $this->assertSame('fixed', data_get($firstSnapshot, 'meta.randomization.question_order'));
        $this->assertSame(
            'radio_options_for_pilihan_ganda_kompleks',
            data_get($firstSnapshot, 'meta.randomization.choice_order')
        );
    }

    private function makeRandomizationTarget(int $targetId): AssessmentAssignmentTarget
    {
        $multipleChoiceField = $this->makeField(301, 'Soal Pilihan Ganda 1', 'radio', [
            ['label' => 'A', 'value' => 'Pilihan 1'],
            ['label' => 'B', 'value' => 'Pilihan 2'],
            ['label' => 'C', 'value' => 'Pilihan 3'],
            ['label' => 'D', 'value' => 'Pilihan 4'],
            ['label' => 'E', 'value' => 'Pilihan 5'],
        ]);
        $multipleChoiceFollowUpField = $this->makeField(302, 'Soal Pilihan Ganda 2', 'radio', [
            ['label' => 'A', 'value' => 'Jawaban A'],
            ['label' => 'B', 'value' => 'Jawaban B'],
            ['label' => 'C', 'value' => 'Jawaban C'],
        ]);
        $portfolioRadioField = $this->makeField(401, 'Status Portofolio', 'radio', [
            ['label' => 'A', 'value' => 'Belum lengkap'],
            ['label' => 'B', 'value' => 'Perlu revisi'],
            ['label' => 'C', 'value' => 'Sudah lengkap'],
            ['label' => 'D', 'value' => 'Terverifikasi'],
            ['label' => 'E', 'value' => 'Status final'],
        ]);

        return $this->makeTarget($targetId, [
            $this->makeAssessment(101, AssessmentInstrumentType::PILIHAN_GANDA_KOMPLEKS->value, [
                $this->makeForm(201, [$multipleChoiceField, $multipleChoiceFollowUpField], [
                    'judul_form' => 'Form Pilihan Ganda',
                    'kode_form' => 'FORM-PG',
                ]),
            ], [
                'kode_assessment' => 'ASM-PG',
                'judul' => 'Assessment Pilihan Ganda',
            ]),
            $this->makeAssessment(102, AssessmentInstrumentType::PORTOFOLIO->value, [
                $this->makeForm(202, [$portfolioRadioField], [
                    'judul_form' => 'Form Portofolio',
                    'kode_form' => 'FORM-PORTO',
                ]),
            ], [
                'kode_assessment' => 'ASM-PORTO',
                'judul' => 'Assessment Portofolio',
            ]),
        ]);
    }

    private function makeTarget(int $targetId, array $assessments): AssessmentAssignmentTarget
    {
        $assignment = new AssessmentAssignment([
            'kode_penugasan' => 'TGS-ASM-01',
            'judul_penugasan' => 'Penugasan Kompetensi',
        ]);
        $assignment->id = 11;
        $assignment->setRelation('assessments', new Collection($assessments));

        $target = new AssessmentAssignmentTarget;
        $target->id = $targetId;
        $target->setRelation('assignment', $assignment);

        return $target;
    }

    private function makeAssessment(int $id, ?string $instrumentType, array $forms, array $extra = []): Assessment
    {
        $assessment = new Assessment(array_merge([
            'kode_assessment' => 'ASM-'.$id,
            'judul' => 'Assessment '.$id,
            'deskripsi' => 'Deskripsi assessment',
            'petunjuk' => 'Petunjuk assessment',
            'instrument_type' => $instrumentType,
            'scoring_config' => ['weight' => 0.40],
            'is_active' => true,
        ], $extra));
        $assessment->id = $id;
        $assessment->setRelation('forms', new Collection($forms));

        return $assessment;
    }

    private function makeForm(int $id, array $fields, array $extra = []): AssessmentForm
    {
        $form = new AssessmentForm(array_merge([
            'judul_form' => 'Form '.$id,
            'kode_form' => 'FORM-'.$id,
            'deskripsi' => 'Deskripsi form',
            'scoring_config' => ['profile' => 'pilihan_ganda_kompleks'],
            'is_active' => true,
        ], $extra));
        $form->id = $id;
        $form->setRelation('fields', new Collection($fields));

        return $form;
    }

    private function makeField(
        int $id,
        string $label,
        string $type,
        array $options = [],
        array $extra = []
    ): AssessmentFormField {
        $field = new AssessmentFormField(array_merge([
            'label' => $label,
            'deskripsi' => 'Deskripsi '.$label,
            'nama_field' => 'field_'.$id,
            'tipe_field' => $type,
            'placeholder' => null,
            'bantuan' => null,
            'opsi_field' => $options,
            'scoring_config' => null,
            'is_required' => true,
            'is_active' => true,
        ], $extra));
        $field->id = $id;

        return $field;
    }
}
