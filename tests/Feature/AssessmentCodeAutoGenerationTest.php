<?php

namespace Tests\Feature;

use App\Models\Assessment;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AssessmentCodeAutoGenerationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();

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

    public function test_store_generates_assessment_code_when_input_is_empty(): void
    {
        $response = $this
            ->withSession(['role' => 'admin'])
            ->post(route('assessment.store'), $this->validPayload([
                'judul' => 'Assessment Studi Kasus Baru',
                'instrument_type' => 'studi_kasus',
            ]));

        $response->assertRedirect(route('assessment.index'));

        $assessment = Assessment::query()->firstOrFail();

        $this->assertSame('ASM-STUDI-KASUS-001', $assessment->kode_assessment);
        $this->assertSame('Assessment Studi Kasus Baru', $assessment->judul);
    }

    public function test_update_preserves_existing_assessment_code_when_input_is_empty(): void
    {
        $assessment = Assessment::query()->create([
            'kode_assessment' => 'ASM-PG-007',
            'judul' => 'Assessment Lama',
            'slug' => 'assessment-lama',
            'instrument_type' => 'pilihan_ganda_kompleks',
            'status' => 'draft',
            'is_active' => true,
        ]);

        $response = $this
            ->withSession(['role' => 'admin'])
            ->put(route('assessment.update', $assessment->id), $this->validPayload([
                'judul' => 'Assessment Pilihan Ganda Diperbarui',
                'instrument_type' => 'pilihan_ganda_kompleks',
            ]));

        $response->assertRedirect(route('assessment.index'));

        $assessment->refresh();

        $this->assertSame('ASM-PG-007', $assessment->kode_assessment);
        $this->assertSame('Assessment Pilihan Ganda Diperbarui', $assessment->judul);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_replace_recursive([
            'judul' => 'Assessment Otomatis',
            'status' => 'draft',
            'instrument_type' => 'portofolio',
            'is_active' => '1',
            'forms' => [
                [
                    'judul_form' => 'Form Utama',
                    'kode_form' => '',
                    'deskripsi' => '',
                    'kompetensi' => '',
                    'indikator_kode' => '',
                    'indikator_label' => '',
                    'is_scoreable' => '0',
                    'urutan' => 1,
                    'is_active' => '1',
                    'scoring' => [
                        'profile' => '',
                        'weight' => '',
                    ],
                    'fields' => [
                        [
                            'label' => 'Pertanyaan Pertama',
                            'deskripsi' => '',
                            'tipe_field' => 'text',
                            'placeholder' => '',
                            'bantuan' => '',
                            'opsi_field_text' => '',
                            'opsi_score_text' => '',
                            'repeater_config_text' => '',
                            'radio_options' => [],
                            'scoring' => [
                                'enabled' => '0',
                                'profile' => '',
                                'method' => 'presence',
                            ],
                            'lebar_kolom' => 'col-md-12',
                            'urutan' => 1,
                            'is_required' => '0',
                            'is_active' => '1',
                        ],
                    ],
                ],
            ],
        ], $overrides);
    }
}
