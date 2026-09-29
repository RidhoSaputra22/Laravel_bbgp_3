<?php

namespace Tests\Feature;

use App\Models\Assessment;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AssessmentConfigurationApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->string('kode_assessment');
            $table->string('judul');
            $table->string('slug');
            $table->text('deskripsi')->nullable();
            $table->text('petunjuk')->nullable();
            $table->string('instrument_type')->nullable();
            $table->string('kategori')->nullable();
            $table->string('target_ketenagaan')->nullable();
            $table->json('scoring_config')->nullable();
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
            $table->string('kompetensi')->nullable();
            $table->string('indikator_kode')->nullable();
            $table->string('indikator_label')->nullable();
            $table->boolean('is_scoreable')->default(true);
            $table->json('scoring_config')->nullable();
            $table->unsignedInteger('urutan')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('assessment_form_fields', function (Blueprint $table) {
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
            $table->string('autofill_source')->nullable();
            $table->string('lookup_source')->nullable();
            $table->json('dependency_config')->nullable();
            $table->json('validasi')->nullable();
            $table->json('scoring_config')->nullable();
            $table->unsignedInteger('urutan')->default(1);
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('assessment_form_fields');
        Schema::dropIfExists('assessment_forms');
        Schema::dropIfExists('assessments');

        parent::tearDown();
    }

    public function test_api_returns_advanced_rules_text_as_json_at_each_assessment_level(): void
    {
        $rules = ['signal_keywords' => ['masukkan nomor unik']];

        $assessment = Assessment::query()->create([
            'kode_assessment' => 'ASM-API-001',
            'judul' => 'Assessment API',
            'slug' => 'assessment-api',
            'instrument_type' => 'generic',
            'scoring_config' => ['advanced_rules_text' => json_encode($rules)],
            'status' => 'publish',
            'is_active' => true,
        ]);

        $form = $assessment->forms()->create([
            'judul_form' => 'Form API',
            'scoring_config' => ['advanced_rules_text' => json_encode($rules)],
            'is_active' => true,
        ]);

        $form->fields()->create([
            'label' => 'Nomor unik',
            'nama_field' => 'nomor_unik',
            'tipe_field' => 'text',
            'scoring_config' => ['advanced_rules_text' => json_encode($rules)],
            'is_active' => true,
        ]);

        $response = $this->getJson(route('api.v1.assessments.show', $assessment->slug));

        $response->assertOk()
            ->assertJsonPath('data.scoring_config.advanced_rules_text.signal_keywords.0', 'masukkan nomor unik')
            ->assertJsonPath('data.forms.0.scoring_config.advanced_rules_text.signal_keywords.0', 'masukkan nomor unik')
            ->assertJsonPath('data.forms.0.fields.0.scoring_config.advanced_rules_text.signal_keywords.0', 'masukkan nomor unik');
    }
}
