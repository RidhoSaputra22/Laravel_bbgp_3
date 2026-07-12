<?php

namespace Tests\Unit;

use App\Models\AssessmentAssignment;
use App\Models\AssessmentAssignmentTarget;
use App\Models\AssessmentAttempt;
use App\Models\AssessmentFormField;
use App\Services\Assessment\AssessmentAttemptLifecycleService;
use App\Services\Assessment\AssessmentAttemptService;
use App\Services\Assessment\AssessmentQuestionRandomizerService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class AssessmentAttemptLifecycleServiceTest extends TestCase
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
            $table->json('structure_snapshot')->nullable();
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_combination_id')->nullable();
            $table->json('security_config')->nullable();
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
            $table->string('judul')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_forms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_id');
            $table->string('judul_form')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_form_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_form_id');
            $table->string('label');
            $table->string('tipe_field')->default('text');
            $table->string('autofill_source')->nullable();
            $table->string('lookup_source')->nullable();
            $table->json('validasi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_assignment_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_assignment_id')->nullable();
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_assignment_targets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_assignment_id');
            $table->unsignedBigInteger('assessment_assignment_session_id')->nullable();
            $table->unsignedBigInteger('assessment_combination_id')->nullable();
            $table->unsignedBigInteger('guru_id')->nullable();
            $table->string('status')->default('ditugaskan');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('deadline_at')->nullable();
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_assignment_target_id');
            $table->string('status')->default('draft');
            $table->json('structure_snapshot')->nullable();
            $table->json('security_config_snapshot')->nullable();
            $table->unsignedInteger('total_questions')->default(0);
            $table->unsignedInteger('required_questions')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('deadline_at')->nullable();
            $table->timestamp('last_answered_at')->nullable();
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_attempt_id');
            $table->unsignedBigInteger('assessment_form_field_id')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Mockery::close();

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

    public function test_it_refreshes_stale_field_metadata_in_existing_attempt_snapshot(): void
    {
        $assignment = AssessmentAssignment::query()->create();
        $target = AssessmentAssignmentTarget::query()->create([
            'assessment_assignment_id' => $assignment->id,
            'status' => 'ditugaskan',
        ]);

        $field = AssessmentFormField::query()->create([
            'assessment_form_id' => 1,
            'label' => 'Jabatan',
            'tipe_field' => 'select',
            'autofill_source' => 'jabatan',
            'lookup_source' => 'master_jabatan_pendidik',
            'validasi' => [
                'allow_other_input' => true,
            ],
            'is_active' => true,
        ]);

        $attempt = AssessmentAttempt::query()->create([
            'assessment_assignment_target_id' => $target->id,
            'status' => 'draft',
            'structure_snapshot' => [
                'meta' => [
                    'total_questions' => 1,
                    'required_questions' => 1,
                ],
                'assessments' => [
                    [
                        'forms' => [
                            [
                                'fields' => [
                                    [
                                        'id' => $field->id,
                                        'label' => 'Jabatan',
                                        'tipe_field' => 'select',
                                        'opsi_field' => [
                                            ['label' => 'Guru', 'value' => 'Guru'],
                                        ],
                                        'autofill_source' => null,
                                        'lookup_source' => null,
                                        'is_required' => true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'total_questions' => 1,
            'required_questions' => 1,
        ]);

        $target->setRelation('attempt', $attempt);

        $randomizer = Mockery::mock(AssessmentQuestionRandomizerService::class);
        $randomizer->shouldNotReceive('buildSnapshot');

        $attemptService = Mockery::mock(AssessmentAttemptService::class);
        $service = new AssessmentAttemptLifecycleService($randomizer, $attemptService);

        $resolvedAttempt = $service->ensureAttempt($target, false);

        $this->assertSame(
            'jabatan',
            data_get($resolvedAttempt->structure_snapshot, 'assessments.0.forms.0.fields.0.autofill_source')
        );
        $this->assertSame(
            'master_jabatan_pendidik',
            data_get($resolvedAttempt->structure_snapshot, 'assessments.0.forms.0.fields.0.lookup_source')
        );
        $this->assertTrue(
            (bool) data_get($resolvedAttempt->structure_snapshot, 'assessments.0.forms.0.fields.0.validasi.allow_other_input')
        );

        $this->assertSame(
            'jabatan',
            data_get($attempt->fresh()->structure_snapshot, 'assessments.0.forms.0.fields.0.autofill_source')
        );
        $this->assertSame(
            'master_jabatan_pendidik',
            data_get($attempt->fresh()->structure_snapshot, 'assessments.0.forms.0.fields.0.lookup_source')
        );
        $this->assertTrue(
            (bool) data_get($attempt->fresh()->structure_snapshot, 'assessments.0.forms.0.fields.0.validasi.allow_other_input')
        );
    }
}
