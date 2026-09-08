<?php

namespace Tests\Feature;

use App\Http\Controllers\ValidatorPanelController;
use App\Http\Controllers\ValidatorTaskController;
use App\Models\Assessment;
use App\Models\Guru;
use App\Models\User;
use App\Models\ValidatorForm;
use App\Services\Assessment\ValidatorAssignmentService;
use App\Support\Assessment\ValidatorAccess;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class AssessmentValidatorModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createCoreTables();
        $migration = require database_path('migrations/2026_09_08_000000_create_assessment_validator_tables.php');
        $migration->up();
    }

    protected function tearDown(): void
    {
        DB::purge('sqlite');
        parent::tearDown();
    }

    public function test_only_stakeholder_user_with_validator_position_is_eligible(): void
    {
        $eligible = $this->createUserWithGuru('stakeholder', 'Stakeholder', 'Validator', '111');
        $this->createUserWithGuru('stakeholder', 'Stakeholder', 'Kepala Dinas', '222');
        $this->createUserWithGuru('tenaga pendidik', 'Stakeholder', 'Validator', '333');
        $this->createUserWithGuru('stakeholder', 'Tenaga Pendidik', 'Validator', '444');

        $this->assertSame(
            [$eligible->id],
            ValidatorAccess::eligibleUsersQuery()->pluck('id')->all()
        );

        session()->put(['user_id' => $eligible->id, 'role' => 'stakeholder']);
        $this->assertSame($eligible->id, ValidatorAccess::currentValidator()?->id);
    }

    public function test_assignment_uses_separate_tables_and_keeps_assessment_snapshot(): void
    {
        $validator = $this->createUserWithGuru('stakeholder', 'Stakeholder', 'Validator', '555');
        $assessment = $this->createAssessment();
        [$form, $scoredField, $noteField] = $this->createValidatorForm();
        $service = app(ValidatorAssignmentService::class);

        $assignment = $service->create([
            'title' => 'QA Assessment Guru',
            'validator_form_id' => $form->id,
            'assessment_id' => $assessment->id,
            'validator_user_id' => $validator->id,
            'start_date' => now()->toDateString(),
            'due_date' => now()->addWeek()->toDateString(),
        ], null);

        $this->assertDatabaseCount('assessment_assignments', 0);
        $this->assertDatabaseCount('validator_assignments', 1);
        $this->assertSame('Assessment Utama', data_get($assignment->assessment_snapshot, 'title'));
        $this->assertSame('Validator Test', data_get($assignment->validator_snapshot, 'name'));

        $assignment->load('validatorForm.sections.fields');
        $service->submit($assignment, [
            $scoredField->id => '4',
            $noteField->id => 'Instrumen sudah jelas.',
        ], 'approved', null);

        $assignment->refresh();
        $this->assertSame('submitted', $assignment->status);
        $this->assertSame(4.0, $assignment->score_total);
        $this->assertSame(5.0, $assignment->score_max);
        $this->assertSame(80.0, $assignment->score_percentage);
        $this->assertDatabaseCount('validator_assignment_responses', 2);
        $this->assertDatabaseCount('assessment_assignment_targets', 0);
    }

    public function test_assignment_rejects_user_who_is_not_an_eligible_validator(): void
    {
        $notValidator = $this->createUserWithGuru('stakeholder', 'Stakeholder', 'Kepala Dinas', '666');
        $assessment = $this->createAssessment();
        [$form] = $this->createValidatorForm();

        $this->expectException(ValidationException::class);

        app(ValidatorAssignmentService::class)->create([
            'title' => 'QA Tidak Valid',
            'validator_form_id' => $form->id,
            'assessment_id' => $assessment->id,
            'validator_user_id' => $notValidator->id,
        ], null);
    }

    public function test_admin_panel_and_validator_workspace_have_separate_access_rules(): void
    {
        $eligible = $this->createUserWithGuru('stakeholder', 'Stakeholder', 'Validator', '777');

        session()->put(['role' => 'admin', 'user_id' => 999]);
        $panelView = app(ValidatorPanelController::class)->index();
        $this->assertSame('pages.admin.assessment.validator.panel', $panelView->getName());

        session()->put(['role' => 'stakeholder', 'user_id' => $eligible->id]);
        $taskView = app(ValidatorTaskController::class)->index();
        $this->assertSame('pages.admin.assessment.validator.task.index', $taskView->getName());

        try {
            app(ValidatorPanelController::class)->index();
            $this->fail('Validator seharusnya tidak dapat membuka panel administrasi.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }

    private function createCoreTables(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username');
            $table->string('no_ktp')->nullable();
            $table->string('password');
            $table->string('role');
            $table->timestamps();
        });

        Schema::create('gurus', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lengkap');
            $table->string('email')->nullable();
            $table->string('no_ktp');
            $table->string('eksternal_jabatan');
            $table->string('jenis_jabatan');
            $table->timestamps();
        });

        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->string('kode_assessment');
            $table->string('judul');
            $table->string('slug');
            $table->text('deskripsi')->nullable();
            $table->text('petunjuk')->nullable();
            $table->string('instrument_type')->nullable();
            $table->string('target_ketenagaan')->nullable();
            $table->string('status')->default('draft');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('assessment_forms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_id');
            $table->string('judul_form');
            $table->string('kode_form')->nullable();
            $table->text('deskripsi')->nullable();
            $table->unsignedInteger('urutan')->default(1);
            $table->timestamps();
        });

        Schema::create('assessment_form_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_form_id');
            $table->string('label');
            $table->text('deskripsi')->nullable();
            $table->string('nama_field');
            $table->string('tipe_field');
            $table->json('opsi_field')->nullable();
            $table->unsignedInteger('urutan')->default(1);
            $table->boolean('is_required')->default(false);
            $table->timestamps();
        });

        Schema::create('assessment_assignments', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('assessment_assignment_targets', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }

    private function createUserWithGuru(
        string $role,
        string $ketenagaan,
        string $jabatan,
        string $nik
    ): User {
        $user = User::create([
            'name' => 'User '.$nik,
            'username' => 'user'.$nik,
            'no_ktp' => $nik,
            'password' => 'secret',
            'role' => $role,
        ]);

        Guru::create([
            'nama_lengkap' => $jabatan === 'Validator' ? 'Validator Test' : 'User Test',
            'email' => 'user'.$nik.'@example.test',
            'no_ktp' => $nik,
            'eksternal_jabatan' => $ketenagaan,
            'jenis_jabatan' => $jabatan,
        ]);

        return $user;
    }

    private function createAssessment(): Assessment
    {
        $assessment = Assessment::create([
            'kode_assessment' => 'ASM-TEST',
            'judul' => 'Assessment Utama',
            'slug' => 'assessment-utama',
            'deskripsi' => 'Instrumen yang akan divalidasi.',
            'petunjuk' => 'Periksa setiap butir.',
            'instrument_type' => 'skala_likert',
            'target_ketenagaan' => 'tenaga_pendidik',
            'status' => 'publish',
            'is_active' => true,
        ]);
        $form = $assessment->forms()->create([
            'judul_form' => 'Kompetensi',
            'kode_form' => 'KOM',
            'deskripsi' => 'Bagian kompetensi.',
            'urutan' => 1,
        ]);
        $form->fields()->create([
            'label' => 'Butir Assessment',
            'deskripsi' => 'Butir yang diperiksa validator.',
            'nama_field' => 'butir_assessment',
            'tipe_field' => 'text',
            'opsi_field' => null,
            'urutan' => 1,
            'is_required' => true,
        ]);

        return $assessment;
    }

    private function createValidatorForm(): array
    {
        $form = ValidatorForm::create([
            'code' => 'VF-TEST',
            'title' => 'Form QA Test',
            'status' => 'published',
            'is_active' => true,
        ]);
        $section = $form->sections()->create([
            'title' => 'Kualitas Instrumen',
            'sort_order' => 1,
        ]);
        $scoredField = $section->fields()->create([
            'label' => 'Kejelasan butir',
            'field_type' => 'likert',
            'options' => ['1', '2', '3', '4', '5'],
            'is_required' => true,
            'is_scored' => true,
            'max_score' => 5,
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $noteField = $section->fields()->create([
            'label' => 'Catatan',
            'field_type' => 'textarea',
            'is_required' => false,
            'is_scored' => false,
            'sort_order' => 2,
            'is_active' => true,
        ]);

        return [$form, $scoredField, $noteField];
    }
}
