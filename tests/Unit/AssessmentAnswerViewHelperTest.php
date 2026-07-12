<?php

namespace Tests\Unit;

use App\Support\Assessment\AssessmentAnswerViewHelper;
use App\Support\Assessment\ChoiceFieldOtherOption;
use Tests\TestCase;

class AssessmentAnswerViewHelperTest extends TestCase
{
    public function test_it_resolves_custom_text_for_select_other_answer(): void
    {
        $field = [
            'tipe_field' => 'select',
            'validasi' => [
                'allow_other_input' => true,
            ],
            'opsi_field' => [
                ['label' => 'Guru', 'value' => 'Guru'],
                ['label' => 'Kepala Sekolah', 'value' => 'Kepala Sekolah'],
            ],
        ];
        $answer = [
            'text' => 'Pengawas Madrasah',
            'payload' => [
                'type' => 'select',
                'value' => ChoiceFieldOtherOption::VALUE,
                'label' => ChoiceFieldOtherOption::LABEL,
                'other_text' => 'Pengawas Madrasah',
                'is_other' => true,
            ],
        ];

        $this->assertSame(
            [ChoiceFieldOtherOption::VALUE],
            AssessmentAnswerViewHelper::resolveSelectedValues($field, $answer)
        );
        $this->assertSame('Pengawas Madrasah', AssessmentAnswerViewHelper::resolveAnswerText($field, $answer));
        $this->assertTrue(AssessmentAnswerViewHelper::hasAnswer($field, $answer));
    }
}
