<?php

namespace Tests\Unit;

use App\Jobs\SyncValidatorAssignmentsToMongoJob;
use App\Models\ValidatorAssignment;
use App\Models\ValidatorAssignmentResponse;
use App\Models\ValidatorForm;
use App\Models\ValidatorFormField;
use App\Models\ValidatorFormSection;
use App\Services\Assessment\ValidatorAssignmentDocumentBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ValidatorAssignmentDocumentBuilderTest extends TestCase
{
    public function test_source_assessment_metadata_is_score_free_and_validator_scoring_is_kept(): void
    {
        $field = new ValidatorFormField([
            'label' => 'Instrumen mudah dipahami',
            'field_type' => 'likert',
            'options' => ['1', '2', '3', '4', '5'],
            'is_required' => true,
            'is_scored' => true,
            'max_score' => 5,
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $field->id = 100;

        $section = new ValidatorFormSection([
            'title' => 'Kualitas Instrumen',
            'sort_order' => 1,
        ]);
        $section->id = 10;
        $section->setRelation('fields', new Collection([$field]));

        $form = new ValidatorForm([
            'code' => 'FORM-VALIDATOR-001',
            'title' => 'Form Validator',
            'status' => 'published',
        ]);
        $form->id = 5;
        $form->setRelation('sections', new Collection([$section]));

        $response = new ValidatorAssignmentResponse([
            'validator_form_field_id' => 100,
            'answer_text' => '5',
            'score' => 5,
        ]);
        $response->id = 200;

        $assignment = new ValidatorAssignment([
            'code' => 'VAL-20261002-ABC123',
            'title' => 'Validasi Assessment',
            'validator_form_id' => 5,
            'status' => 'submitted',
            'assessment_assignment_snapshots' => [[
                'id' => 50,
                'code' => 'ASM-001',
                'title' => 'Assessment Guru',
                'target_ketenagaan' => 'tenaga_pendidik',
                'assessments' => [[
                    'id' => 20,
                    'code' => 'ASM-GURU-001',
                    'title' => 'Instrumen Guru',
                    'scoring_config' => ['hidden' => true],
                    'forms' => [[
                        'id' => 30,
                        'code' => 'FORM-001',
                        'title' => 'Kompetensi',
                        'is_scoreable' => true,
                        'scoring_config' => ['weight' => 1],
                        'fields' => [[
                            'id' => 300,
                            'label' => 'Pertanyaan',
                            'type' => 'radio',
                            'options' => ['Ya', 'Tidak'],
                            'required' => true,
                            'scoring_config' => ['score' => 1],
                        ]],
                    ]],
                ]],
            ]],
            'validator_snapshot' => [
                'user_id' => 10,
                'guru_id' => 25,
                'name' => 'Validator Test',
            ],
            'score_total' => 5,
            'score_max' => 5,
            'score_percentage' => 100,
            'recommendation' => 'approved',
        ]);
        $assignment->id = 123;
        $assignment->setRelation('validatorForm', $form);
        $assignment->setRelation('responses', new Collection([$response]));

        $document = (new ValidatorAssignmentDocumentBuilder)->document($assignment);

        $sourceAssessment = $document['assessment_assignments'][0]['assessments'][0];
        $sourceForm = $sourceAssessment['forms'][0];

        $this->assertArrayNotHasKey('scoring_config', $sourceAssessment);
        $this->assertArrayNotHasKey('is_scoreable', $sourceForm);
        $this->assertArrayNotHasKey('scoring_config', $sourceForm);
        $this->assertArrayNotHasKey('scoring_config', $sourceForm['fields'][0]);
        $this->assertTrue($document['validator_form']['sections'][0]['fields'][0]['is_scored']);
        $this->assertSame(5.0, $document['responses'][0]['score']);
        $this->assertSame('validator_form_only', $document['meta']['scoring_scope']);
    }

    public function test_sync_dispatcher_splits_validator_assignment_ids_into_batches(): void
    {
        config()->set('assessment_mongodb.enabled', true);
        config()->set('assessment_mongodb.batch_size', 50);
        Queue::fake();

        SyncValidatorAssignmentsToMongoJob::dispatchIds(range(1, 101));

        Queue::assertPushed(SyncValidatorAssignmentsToMongoJob::class, 3);
        Queue::assertPushed(SyncValidatorAssignmentsToMongoJob::class, function ($job) {
            return count($job->assignmentIds) <= 50;
        });
    }
}
