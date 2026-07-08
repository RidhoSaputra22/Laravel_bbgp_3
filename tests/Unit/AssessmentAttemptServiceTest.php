<?php

namespace Tests\Unit;

use App\Models\Assessment;
use App\Models\AssessmentAssignment;
use App\Models\AssessmentAssignmentTarget;
use App\Models\AssessmentAttempt;
use App\Models\AssessmentForm;
use App\Models\AssessmentFormField;
use App\Services\Assessment\AssessmentAttemptService;
use App\Services\Assessment\AssessmentAutoScoringService;
use App\Services\Assessment\AssessmentScoringService;
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

    private function makeService(): AssessmentAttemptService
    {
        return new AssessmentAttemptService(
            Mockery::mock(AssessmentScoringService::class),
            Mockery::mock(AssessmentAutoScoringService::class)
        );
    }

    private function createAttemptScenario(): array
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
        $field = AssessmentFormField::query()->create([
            'assessment_form_id' => $form->id,
            'label' => 'Pertanyaan Reflektif',
            'tipe_field' => 'text',
            'opsi_field' => [],
            'is_required' => true,
            'is_active' => true,
        ]);

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

        $attempt = AssessmentAttempt::query()->create([
            'assessment_assignment_target_id' => $target->id,
            'status' => 'in_progress',
            'started_at' => now(),
            'total_questions' => 1,
            'required_questions' => 1,
            'structure_snapshot' => [
                'meta' => [
                    'total_questions' => 1,
                    'required_questions' => 1,
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
                                'fields' => [
                                    [
                                        'id' => $field->id,
                                        'assessment_id' => $assessment->id,
                                        'assessment_form_id' => $form->id,
                                        'label' => 'Pertanyaan Reflektif',
                                        'tipe_field' => 'text',
                                        'opsi_field' => [],
                                        'is_required' => true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        return [
            'attempt' => $attempt,
            'field' => $field,
        ];
    }
}
