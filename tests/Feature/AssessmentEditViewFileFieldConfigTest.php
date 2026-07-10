<?php

namespace Tests\Feature;

use App\Http\Middleware\ValidasiUser;
use App\Models\Assessment;
use App\Models\AssessmentForm;
use App\Models\AssessmentFormField;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AssessmentEditViewFileFieldConfigTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidasiUser::class);

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::connection('sqlite')->create('assessments', function (Blueprint $table) {
            $table->id();
            $table->string('kode_assessment')->unique();
            $table->string('judul');
            $table->string('slug')->unique();
            $table->text('deskripsi')->nullable();
            $table->text('petunjuk')->nullable();
            $table->string('instrument_type')->nullable();
            $table->string('target_ketenagaan')->nullable();
            $table->json('scoring_config')->nullable();
            $table->string('status')->default('draft');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_forms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_id');
            $table->string('judul_form');
            $table->string('kode_form')->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('kompetensi')->nullable();
            $table->string('indikator_kode')->nullable();
            $table->string('indikator_label')->nullable();
            $table->boolean('is_scoreable')->default(true);
            $table->json('scoring_config')->nullable();
            $table->unsignedInteger('urutan')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_form_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_form_id');
            $table->string('label');
            $table->text('deskripsi')->nullable();
            $table->string('nama_field');
            $table->string('tipe_field');
            $table->string('placeholder')->nullable();
            $table->text('bantuan')->nullable();
            $table->json('opsi_field')->nullable();
            $table->text('nilai_default')->nullable();
            $table->json('validasi')->nullable();
            $table->json('scoring_config')->nullable();
            $table->string('lebar_kolom')->default('col-md-12');
            $table->unsignedInteger('urutan')->default(1);
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::connection('sqlite')->dropIfExists('assessment_form_fields');
        Schema::connection('sqlite')->dropIfExists('assessment_forms');
        Schema::connection('sqlite')->dropIfExists('assessments');

        parent::tearDown();
    }

    public function test_edit_view_renders_when_file_field_has_associative_option_config(): void
    {
        $assessment = Assessment::query()->create([
            'kode_assessment' => 'ASM-VALIDASI-001',
            'judul' => 'Assessment Validasi Ahli',
            'slug' => 'assessment-validasi-ahli',
            'instrument_type' => 'validasi_ahli',
            'target_ketenagaan' => 'stakeholder',
            'status' => 'publish',
            'is_active' => true,
        ]);

        $form = AssessmentForm::query()->create([
            'assessment_id' => $assessment->id,
            'judul_form' => 'Pengesahan',
            'kode_form' => 'FORM-PENGESAHAN',
            'urutan' => 1,
            'is_active' => true,
        ]);

        AssessmentFormField::query()->create([
            'assessment_form_id' => $form->id,
            'label' => 'Tanda Tangan Validator',
            'deskripsi' => 'Unggah tanda tangan validator.',
            'nama_field' => 'tanda_tangan_validator',
            'tipe_field' => 'file',
            'bantuan' => 'Format yang disarankan: PNG, JPG, atau JPEG.',
            'opsi_field' => [
                'accept' => ['image/png', 'image/jpeg'],
                'max_size_kb' => 2048,
                'max_files' => 1,
            ],
            'validasi' => [
                'required' => true,
                'mimes' => ['png', 'jpg', 'jpeg'],
                'max' => 2048,
            ],
            'urutan' => 1,
            'is_required' => true,
            'is_active' => true,
        ]);

        $response = $this
            ->withSession([
                'role' => 'admin',
                'user_id' => 1,
                'name' => 'Admin Test',
            ])
            ->get(route('assessment.edit', $assessment->id));

        $response->assertOk();
        $response->assertSee('Edit Assessment');
        $response->assertSee('Tanda Tangan Validator');
    }
}
