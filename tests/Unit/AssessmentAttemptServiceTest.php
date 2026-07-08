<?php

namespace Tests\Unit;

use App\Models\Assessment;
use App\Models\AssessmentAssignment;
use App\Models\AssessmentAssignmentTarget;
use App\Models\AssessmentAttempt;
use App\Models\AssessmentAttemptAnswer;
use App\Models\AssessmentForm;
use App\Models\AssessmentFormField;
use App\Services\Assessment\AssessmentAttemptService;
use App\Services\Assessment\AssessmentAutoScoringService;
use App\Services\Assessment\AssessmentScoringService;
use App\Support\Assessment\TextareaWordLimit;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class AssessmentAttemptServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::connection('sqlite')->create('gurus', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lengkap')->nullable();
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_combinations', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kombinasi')->nullable();
            $table->string('judul')->nullable();
            $table->json('structure_snapshot')->nullable();
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_combination_id')->nullable();
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_assignment_assessments', function (Blueprint $table) {
            $table->unsignedBigInteger('assessment_assignment_id');
            $table->unsignedBigInteger('assessment_id');
            $table->unsignedInteger('urutan')->default(1);
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessments', function (Blueprint $table) {
            $table->id();
            $table->string('kode_assessment')->nullable();
            $table->string('judul')->nullable();
            $table->string('instrument_type')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_forms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_id');
            $table->string('judul_form')->nullable();
            $table->string('kode_form')->nullable();
            $table->unsignedInteger('urutan')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_form_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_form_id');
            $table->string('label');
            $table->string('tipe_field')->default('text');
            $table->json('opsi_field')->nullable();
            $table->unsignedInteger('urutan')->default(1);
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_assignment_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_assignment_id')->nullable();
            $table->unsignedInteger('nomor_sesi')->default(1);
            $table->string('label_sesi')->nullable();
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_assignment_targets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_assignment_id');
            $table->unsignedBigInteger('assessment_assignment_session_id')->nullable();
            $table->unsignedBigInteger('assessment_combination_id')->nullable();
            $table->unsignedBigInteger('guru_id')->nullable();
            $table->string('status')->default('dikerjakan');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('deadline_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->string('completion_mode')->nullable();
            $table->timestamp('timed_out_at')->nullable();
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_assignment_target_id');
            $table->string('status')->default('in_progress');
            $table->json('structure_snapshot')->nullable();
            $table->json('security_config_snapshot')->nullable();
            $table->json('result_summary')->nullable();
            $table->json('scoring_summary')->nullable();
            $table->unsignedInteger('total_questions')->default(0);
            $table->unsignedInteger('required_questions')->default(0);
            $table->unsignedInteger('answered_questions')->default(0);
            $table->unsignedInteger('answered_required_questions')->default(0);
            $table->unsignedInteger('serious_violation_count')->default(0);
            $table->unsignedInteger('warning_violation_count')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('deadline_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->string('completion_mode')->nullable();
            $table->timestamp('timed_out_at')->nullable();
            $table->timestamp('last_answered_at')->nullable();
            $table->timestamp('last_violation_at')->nullable();
            $table->timestamp('disqualified_at')->nullable();
            $table->text('disqualification_reason')->nullable();
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_attempt_id');
            $table->unsignedBigInteger('assessment_id')->nullable();
            $table->unsignedBigInteger('assessment_form_id')->nullable();
            $table->unsignedBigInteger('assessment_form_field_id')->nullable();
            $table->text('answer_text')->nullable();
            $table->json('answer_payload')->nullable();
            $table->string('answer_file_path')->nullable();
            $table->decimal('auto_score', 8, 2)->nullable();
            $table->text('auto_score_reason')->nullable();
            $table->json('auto_score_metadata')->nullable();
            $table->timestamp('auto_scored_at')->nullable();
            $table->unsignedInteger('assessor_score')->nullable();
            $table->text('assessor_notes')->nullable();
            $table->unsignedBigInteger('assessor_user_id')->nullable();
            $table->timestamp('assessor_scored_at')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_attempt_security_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_attempt_id');
            $table->string('event_key', 100);
            $table->string('violation_type', 32)->nullable();
            $table->string('lock_mode', 32)->nullable();
            $table->text('message')->nullable();
            $table->boolean('counts_toward_disqualify')->default(false);
            $table->timestamp('client_occurred_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Mockery::close();

        Schema::connection('sqlite')->dropIfExists('assessment_attempt_security_events');
        Schema::connection('sqlite')->dropIfExists('assessment_attempt_answers');
        Schema::connection('sqlite')->dropIfExists('assessment_attempts');
        Schema::connection('sqlite')->dropIfExists('assessment_assignment_targets');
        Schema::connection('sqlite')->dropIfExists('assessment_assignment_sessions');
        Schema::connection('sqlite')->dropIfExists('assessment_form_fields');
        Schema::connection('sqlite')->dropIfExists('assessment_forms');
        Schema::connection('sqlite')->dropIfExists('assessments');
        Schema::connection('sqlite')->dropIfExists('assessment_assignment_assessments');
        Schema::connection('sqlite')->dropIfExists('assessment_assignments');
        Schema::connection('sqlite')->dropIfExists('assessment_combinations');
        Schema::connection('sqlite')->dropIfExists('gurus');

        parent::tearDown();
    }

    public function test_save_snapshot_persists_flagged_field_ids_without_requiring_required_answer(): void
    {
        ['attempt' => $attempt, 'field' => $field] = $this->createAttemptScenario();

        $savedAttempt = $this->makeService()->saveSnapshot(
            $attempt,
            [],
            [],
            [$field->id],
            [$field->id]
        );

        $this->assertSame([$field->id], data_get($savedAttempt->structure_snapshot, 'meta.flagged_field_ids'));
        $this->assertDatabaseCount('assessment_attempt_answers', 0);
    }

    public function test_submit_rejects_flagged_question_that_is_still_blank(): void
    {
        ['attempt' => $attempt, 'field' => $field] = $this->createAttemptScenario();

        try {
            $this->makeService()->submit($attempt, [], [], [$field->id]);
            $this->fail('Submit should reject flagged question that is still blank.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                ['Jawaban untuk pertanyaan Pertanyaan Reflektif wajib diisi.'],
                $exception->errors()['answers.'.$field->id] ?? []
            );
            $this->assertSame(
                [$field->id],
                data_get($attempt->fresh()->structure_snapshot, 'meta.flagged_field_ids')
            );
        }
    }

    public function test_save_snapshot_rejects_textarea_answer_below_minimum_word_count(): void
    {
        ['attempt' => $attempt, 'field' => $field] = $this->createAttemptScenario([
            'tipe_field' => 'textarea',
        ]);

        $answer = implode(' ', array_fill(0, TextareaWordLimit::minWords() - 1, 'kata'));

        try {
            $this->makeService()->saveSnapshot(
                $attempt,
                [$field->id => $answer],
                [],
                [$field->id]
            );
            $this->fail('Snapshot should reject textarea answer below minimum word count.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                ['Jawaban untuk pertanyaan Pertanyaan Reflektif minimal 25 kata. Saat ini 24 kata.'],
                $exception->errors()['answers.'.$field->id] ?? []
            );
        }
    }

    public function test_save_snapshot_stores_client_bucket_metadata_without_dirty_answers(): void
    {
        ['attempt' => $attempt, 'field' => $field] = $this->createAttemptScenario();

        $savedAttempt = $this->makeService()->saveSnapshot(
            $attempt,
            [],
            [],
            [],
            [],
            [
                'flush_reason' => 'navigate_assessment',
                'threshold' => 3,
                'dirty_field_ids' => [],
                'form_data_field_ids' => [],
                'flagged_dirty' => true,
                'started_at' => '2026-07-08T22:00:00+08:00',
                'trace' => [
                    [
                        'sequence' => 1,
                        'type' => 'flag_toggle',
                        'changed' => true,
                        'field_id' => $field->id,
                        'assessment_index' => 0,
                        'client_occurred_at' => '2026-07-08T22:00:05+08:00',
                    ],
                ],
            ]
        );

        $autosaveMeta = data_get($savedAttempt->structure_snapshot, 'meta.autosave', []);

        $this->assertSame('navigate_assessment', $autosaveMeta['last_flush_reason'] ?? null);
        $this->assertSame(3, $autosaveMeta['last_threshold'] ?? null);
        $this->assertTrue((bool) ($autosaveMeta['last_flagged_dirty'] ?? false));
        $this->assertSame([], $autosaveMeta['last_dirty_field_ids'] ?? null);
        $this->assertSame(1, $autosaveMeta['last_trace_count'] ?? null);
        $this->assertSame('flag_toggle', data_get($autosaveMeta, 'last_trace.0.type'));
        $this->assertDatabaseCount('assessment_attempt_answers', 0);
    }

    public function test_save_snapshot_stores_client_bucket_metadata_with_dirty_answer(): void
    {
        ['attempt' => $attempt, 'field' => $field] = $this->createAttemptScenario();

        $savedAttempt = $this->makeService()->saveSnapshot(
            $attempt,
            [$field->id => 'Jawaban snapshot peserta'],
            [],
            [$field->id],
            [],
            [
                'flush_reason' => 'field_change',
                'threshold' => 3,
                'dirty_field_ids' => [$field->id],
                'form_data_field_ids' => [$field->id],
                'flagged_dirty' => false,
                'started_at' => '2026-07-08T22:01:00+08:00',
                'trace' => [
                    [
                        'sequence' => 1,
                        'type' => 'field_change',
                        'changed' => true,
                        'field_id' => $field->id,
                        'assessment_index' => 0,
                        'client_occurred_at' => '2026-07-08T22:01:03+08:00',
                    ],
                    [
                        'sequence' => 2,
                        'type' => 'navigate_question',
                        'changed' => false,
                        'field_id' => $field->id,
                        'from_assessment_index' => 0,
                        'to_assessment_index' => 0,
                        'client_occurred_at' => '2026-07-08T22:01:08+08:00',
                    ],
                ],
            ]
        );

        $autosaveMeta = data_get($savedAttempt->structure_snapshot, 'meta.autosave', []);

        $this->assertSame('field_change', $autosaveMeta['last_flush_reason'] ?? null);
        $this->assertSame([$field->id], $autosaveMeta['last_dirty_field_ids'] ?? null);
        $this->assertSame([$field->id], $autosaveMeta['last_form_data_field_ids'] ?? null);
        $this->assertSame(2, $autosaveMeta['last_trace_count'] ?? null);
        $this->assertSame('navigate_question', data_get($autosaveMeta, 'last_trace.1.type'));
        $this->assertSame('Jawaban snapshot peserta', optional($savedAttempt->answers->first())->answer_text);
    }

    public function test_submit_rejects_textarea_answer_above_maximum_word_count(): void
    {
        ['attempt' => $attempt, 'field' => $field] = $this->createAttemptScenario([
            'tipe_field' => 'textarea',
        ]);

        $answer = implode(' ', array_fill(0, TextareaWordLimit::maxWords() + 1, 'kata'));

        try {
            $this->makeService()->submit(
                $attempt,
                [$field->id => $answer],
                []
            );
            $this->fail('Submit should reject textarea answer above maximum word count.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                ['Jawaban untuk pertanyaan Pertanyaan Reflektif maksimal 100 kata. Saat ini 101 kata.'],
                $exception->errors()['answers.'.$field->id] ?? []
            );
        }
    }

    public function test_submit_expired_with_empty_partial_field_ids_preserves_existing_answers(): void
    {
        ['attempt' => $attempt, 'fields' => [$firstField, $secondField]] = $this->createAttemptScenarioWithFields([
            [
                'label' => 'Pertanyaan Utama',
                'is_required' => true,
            ],
            [
                'label' => 'Pertanyaan Cadangan',
                'is_required' => false,
            ],
        ]);

        AssessmentAttemptAnswer::query()->create([
            'assessment_attempt_id' => $attempt->id,
            'assessment_id' => 1,
            'assessment_form_id' => 1,
            'assessment_form_field_id' => $firstField->id,
            'answer_text' => 'Jawaban tersimpan sebelumnya',
            'answer_payload' => [
                'type' => 'text',
                'value' => 'Jawaban tersimpan sebelumnya',
            ],
            'answered_at' => now(),
        ]);

        $savedAttempt = $this->makeService()->submitExpired($attempt, [], [], [], []);
        $answersByFieldId = $savedAttempt->answers->keyBy('assessment_form_field_id');

        $this->assertSame('submitted', $savedAttempt->status);
        $this->assertSame('Jawaban tersimpan sebelumnya', $answersByFieldId[$firstField->id]->answer_text);
        $this->assertNull($answersByFieldId[$secondField->id]->answer_text);
        $this->assertSame(0.0, (float) $answersByFieldId[$secondField->id]->auto_score);
        $this->assertTrue((bool) data_get(
            $answersByFieldId[$secondField->id]->answer_payload,
            'forced_zero_for_unanswered'
        ));
    }

    public function test_submit_disqualified_with_partial_field_ids_preserves_existing_answers_and_flags(): void
    {
        ['attempt' => $attempt, 'fields' => [$firstField, $secondField, $thirdField]] = $this->createAttemptScenarioWithFields([
            [
                'label' => 'Pertanyaan Awal',
                'is_required' => true,
            ],
            [
                'label' => 'Pertanyaan Perubahan',
                'is_required' => false,
            ],
            [
                'label' => 'Pertanyaan Kosong',
                'is_required' => false,
            ],
        ]);

        AssessmentAttemptAnswer::query()->create([
            'assessment_attempt_id' => $attempt->id,
            'assessment_id' => 1,
            'assessment_form_id' => 1,
            'assessment_form_field_id' => $firstField->id,
            'answer_text' => 'Jawaban awal peserta',
            'answer_payload' => [
                'type' => 'text',
                'value' => 'Jawaban awal peserta',
            ],
            'answered_at' => now(),
        ]);

        $snapshot = $attempt->structure_snapshot ?? [];
        data_set($snapshot, 'meta.flagged_field_ids', [$firstField->id]);
        $attempt->forceFill([
            'structure_snapshot' => $snapshot,
        ])->save();

        $savedAttempt = $this->makeService()->submitDisqualified(
            $attempt,
            [$secondField->id => 'Jawaban terakhir sebelum diskualifikasi'],
            [],
            'Pelanggaran guard',
            null,
            [$secondField->id]
        );
        $answersByFieldId = $savedAttempt->answers->keyBy('assessment_form_field_id');

        $this->assertSame('submitted', $savedAttempt->status);
        $this->assertNotNull($savedAttempt->disqualified_at);
        $this->assertSame('Pelanggaran guard', $savedAttempt->disqualification_reason);
        $this->assertSame('Jawaban awal peserta', $answersByFieldId[$firstField->id]->answer_text);
        $this->assertSame(
            'Jawaban terakhir sebelum diskualifikasi',
            $answersByFieldId[$secondField->id]->answer_text
        );
        $this->assertSame(0.0, (float) $answersByFieldId[$thirdField->id]->auto_score);
        $this->assertTrue((bool) data_get(
            $answersByFieldId[$thirdField->id]->answer_payload,
            'forced_zero_for_unanswered'
        ));
        $this->assertSame([$firstField->id], data_get($savedAttempt->structure_snapshot, 'meta.flagged_field_ids'));
    }

    private function makeService(): AssessmentAttemptService
    {
        $scoringService = Mockery::mock(AssessmentScoringService::class);
        $scoringService->shouldIgnoreMissing();
        $scoringService->shouldReceive('buildSummary')
            ->zeroOrMoreTimes()
            ->andReturn([]);

        $autoScoringService = Mockery::mock(AssessmentAutoScoringService::class);
        $autoScoringService->shouldIgnoreMissing();
        $autoScoringService->shouldReceive('scoreAttempt')
            ->zeroOrMoreTimes()
            ->andReturnNull();

        return new AssessmentAttemptService(
            $scoringService,
            $autoScoringService
        );
    }

    private function createAttemptScenario(array $fieldOverrides = []): array
    {
        ['attempt' => $attempt, 'fields' => [$field]] = $this->createAttemptScenarioWithFields([
            [
                'label' => 'Pertanyaan Reflektif',
                'tipe_field' => $fieldOverrides['tipe_field'] ?? 'text',
                'opsi_field' => [],
                'is_required' => true,
            ],
        ]);

        return [
            'attempt' => $attempt,
            'field' => $field,
        ];
    }

    private function createAttemptScenarioWithFields(array $fieldDefinitions): array
    {
        $assignment = AssessmentAssignment::query()->create();
        $assessment = Assessment::query()->create([
            'kode_assessment' => 'ASM-001',
            'judul' => 'Assessment Portal',
            'is_active' => true,
        ]);
        $form = AssessmentForm::query()->create([
            'assessment_id' => $assessment->id,
            'judul_form' => 'Form Reflektif',
            'kode_form' => 'FORM-1',
            'is_active' => true,
        ]);
        $fields = collect($fieldDefinitions)
            ->values()
            ->map(function (array $fieldDefinition, int $index) use ($form) {
                return AssessmentFormField::query()->create([
                    'assessment_form_id' => $form->id,
                    'label' => $fieldDefinition['label'] ?? 'Pertanyaan '.($index + 1),
                    'tipe_field' => $fieldDefinition['tipe_field'] ?? 'text',
                    'opsi_field' => $fieldDefinition['opsi_field'] ?? [],
                    'urutan' => $index + 1,
                    'is_required' => (bool) ($fieldDefinition['is_required'] ?? false),
                    'is_active' => true,
                ]);
            })
            ->values();

        DB::table('assessment_assignment_assessments')->insert([
            'assessment_assignment_id' => $assignment->id,
            'assessment_id' => $assessment->id,
            'urutan' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $target = AssessmentAssignmentTarget::query()->create([
            'assessment_assignment_id' => $assignment->id,
            'status' => 'dikerjakan',
            'started_at' => now(),
        ]);

        $requiredQuestions = $fields->filter(fn (AssessmentFormField $field) => (bool) $field->is_required)->count();
        $snapshotFields = $fields
            ->map(function (AssessmentFormField $field) use ($assessment, $form) {
                return [
                    'id' => $field->id,
                    'assessment_id' => $assessment->id,
                    'assessment_form_id' => $form->id,
                    'label' => $field->label,
                    'tipe_field' => $field->tipe_field,
                    'opsi_field' => $field->opsi_field ?? [],
                    'is_required' => (bool) $field->is_required,
                ];
            })
            ->all();

        $attempt = AssessmentAttempt::query()->create([
            'assessment_assignment_target_id' => $target->id,
            'status' => 'in_progress',
            'started_at' => now(),
            'total_questions' => $fields->count(),
            'required_questions' => $requiredQuestions,
            'structure_snapshot' => [
                'meta' => [
                    'total_questions' => $fields->count(),
                    'required_questions' => $requiredQuestions,
                    'flagged_field_ids' => [],
                ],
                'assessments' => [
                    [
                        'id' => $assessment->id,
                        'kode_assessment' => 'ASM-001',
                        'judul' => 'Assessment Portal',
                        'forms' => [
                            [
                                'id' => $form->id,
                                'judul_form' => 'Form Reflektif',
                                'kode_form' => 'FORM-1',
                                'fields' => $snapshotFields,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        return [
            'attempt' => $attempt,
            'fields' => $fields->all(),
        ];
    }
}
