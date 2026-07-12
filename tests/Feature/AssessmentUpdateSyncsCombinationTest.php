<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\AssessmentForm;
use App\Models\AssessmentFormField;
use App\Services\Assessment\AssessmentCombinationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AssessmentUpdateSyncsCombinationTest extends TestCase
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
            $table->string('autofill_source')->nullable();
            $table->string('lookup_source')->nullable();
            $table->json('validasi')->nullable();
            $table->json('scoring_config')->nullable();
            $table->string('lebar_kolom')->default('col-md-12');
            $table->unsignedInteger('urutan')->default(1);
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_combinations', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kombinasi')->unique();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->string('target_ketenagaan');
            $table->string('random_seed')->nullable();
            $table->string('signature_hash')->nullable();
            $table->json('selection_config')->nullable();
            $table->json('structure_snapshot')->nullable();
            $table->unsignedInteger('total_assessments')->default(0);
            $table->unsignedInteger('total_forms')->default(0);
            $table->unsignedInteger('total_questions')->default(0);
            $table->unsignedBigInteger('generated_by')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_combination_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_combination_id');
            $table->unsignedBigInteger('assessment_id')->nullable();
            $table->unsignedBigInteger('assessment_form_id')->nullable();
            $table->unsignedBigInteger('assessment_form_field_id')->nullable();
            $table->string('assessment_code')->nullable();
            $table->string('assessment_title')->nullable();
            $table->string('instrument_type')->nullable();
            $table->string('form_code')->nullable();
            $table->string('form_title')->nullable();
            $table->text('form_description')->nullable();
            $table->string('kompetensi')->nullable();
            $table->string('indikator_kode')->nullable();
            $table->string('indikator_label')->nullable();
            $table->boolean('form_is_scoreable')->default(false);
            $table->json('form_scoring_config')->nullable();
            $table->string('field_label');
            $table->text('field_description')->nullable();
            $table->string('field_name')->nullable();
            $table->string('field_type')->default('text');
            $table->string('field_placeholder')->nullable();
            $table->text('field_help')->nullable();
            $table->string('field_autofill_source')->nullable();
            $table->string('field_lookup_source')->nullable();
            $table->json('field_options')->nullable();
            $table->json('field_validation')->nullable();
            $table->json('field_scoring_config')->nullable();
            $table->string('field_width')->nullable();
            $table->boolean('field_is_required')->default(false);
            $table->unsignedInteger('assessment_order')->default(1);
            $table->unsignedInteger('form_order')->default(1);
            $table->unsignedInteger('field_order')->default(1);
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('jabatan_penugasan_golongans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('golongan_p3ks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::connection('sqlite')->dropIfExists('golongan_p3ks');
        Schema::connection('sqlite')->dropIfExists('jabatan_penugasan_golongans');
        Schema::connection('sqlite')->dropIfExists('assessment_combination_items');
        Schema::connection('sqlite')->dropIfExists('assessment_combinations');
        Schema::connection('sqlite')->dropIfExists('assessment_form_fields');
        Schema::connection('sqlite')->dropIfExists('assessment_forms');
        Schema::connection('sqlite')->dropIfExists('assessments');

        parent::tearDown();
    }

    public function test_update_preserves_existing_form_and_field_ids_and_refreshes_combination_snapshot(): void
    {
        $assessment = Assessment::query()->create([
            'kode_assessment' => 'ASM-PORTOFOLIO-001',
            'judul' => 'Assessment Portofolio Awal',
            'slug' => 'assessment-portofolio-awal',
            'deskripsi' => 'Deskripsi lama.',
            'petunjuk' => 'Petunjuk lama.',
            'instrument_type' => 'portofolio',
            'target_ketenagaan' => 'tenaga_pendidik',
            'scoring_config' => [
                'profile' => 'portofolio',
                'weight' => 30,
            ],
            'status' => 'publish',
            'is_active' => true,
        ]);

        $form = AssessmentForm::query()->create([
            'assessment_id' => $assessment->id,
            'judul_form' => 'Form Profil Awal',
            'kode_form' => 'FORM-PROFIL',
            'deskripsi' => 'Deskripsi form lama.',
            'is_scoreable' => false,
            'urutan' => 1,
            'is_active' => true,
        ]);

        $field = AssessmentFormField::query()->create([
            'assessment_form_id' => $form->id,
            'label' => 'Label Lama',
            'deskripsi' => 'Deskripsi field lama.',
            'nama_field' => 'label_lama',
            'tipe_field' => 'text',
            'placeholder' => 'Placeholder lama',
            'bantuan' => 'Bantuan lama',
            'opsi_field' => null,
            'validasi' => [
                'required' => false,
                'tipe_field' => 'text',
            ],
            'scoring_config' => [
                'enabled' => false,
                'method' => 'presence',
            ],
            'lebar_kolom' => 'col-md-12',
            'urutan' => 1,
            'is_required' => false,
            'is_active' => true,
        ]);

        $combination = app(AssessmentCombinationService::class)->createCombination([
            'target_ketenagaan' => 'tenaga_pendidik',
            'competency_selection_modes' => [],
            'competency_take_counts' => [],
        ]);

        $combination->items()->update([
            'assessment_form_id' => null,
            'assessment_form_field_id' => null,
        ]);

        DB::table('jabatan_penugasan_golongans')->insert([
            ['name' => 'III/a', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'IV/a', 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('golongan_p3ks')->insert([
            ['name' => 'Ahli Pertama', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $response = $this
            ->withSession(['role' => 'admin'])
            ->put(route('assessment.update', $assessment->id), $this->validPayload($assessment, $form, $field));

        $response->assertRedirect(route('assessment.index'));

        $updatedForm = AssessmentForm::query()->findOrFail($form->id);
        $updatedField = AssessmentFormField::query()->findOrFail($field->id);

        $this->assertSame('Form Profil Diperbarui', $updatedForm->judul_form);
        $this->assertSame('Label Pertanyaan Diperbarui', $updatedField->label);
        $this->assertSame('label_pertanyaan_diperbarui', $updatedField->nama_field);
        $this->assertSame('master_golongan', $updatedField->lookup_source);
        $this->assertSame('select', $updatedField->tipe_field);
        $this->assertSame(
            ['Ahli Pertama', 'III/a', 'IV/a'],
            collect($updatedField->opsi_field ?? [])->pluck('label')->all()
        );

        $combination->refresh()->load('items');

        $this->assertTrue((bool) $combination->is_active);
        $this->assertSame(1, $combination->items->count());

        $item = $combination->items->first();

        $this->assertSame($assessment->id, (int) $item->assessment_id);
        $this->assertSame($form->id, (int) $item->assessment_form_id);
        $this->assertSame($field->id, (int) $item->assessment_form_field_id);
        $this->assertSame('Assessment Portofolio Diperbarui', $item->assessment_title);
        $this->assertSame('Form Profil Diperbarui', $item->form_title);
        $this->assertSame('Label Pertanyaan Diperbarui', $item->field_label);
        $this->assertSame('Placeholder diperbarui', $item->field_placeholder);
        $this->assertSame('label_pertanyaan_diperbarui', $item->field_name);
        $this->assertSame('master_golongan', $item->field_lookup_source);
        $this->assertSame(
            ['Ahli Pertama', 'III/a', 'IV/a'],
            collect($item->field_options ?? [])->pluck('label')->all()
        );

        $snapshotAssessment = collect(data_get($combination->structure_snapshot, 'assessments', []))->first();
        $snapshotForm = collect($snapshotAssessment['forms'] ?? [])->first();
        $snapshotField = collect($snapshotForm['fields'] ?? [])->first();

        $this->assertSame('Assessment Portofolio Diperbarui', $snapshotAssessment['judul'] ?? null);
        $this->assertSame('Form Profil Diperbarui', $snapshotForm['judul_form'] ?? null);
        $this->assertSame('Label Pertanyaan Diperbarui', $snapshotField['label'] ?? null);
        $this->assertSame('Placeholder diperbarui', $snapshotField['placeholder'] ?? null);
        $this->assertSame('master_golongan', $snapshotField['lookup_source'] ?? null);
    }

    private function validPayload(
        Assessment $assessment,
        AssessmentForm $form,
        AssessmentFormField $field
    ): array {
        return [
            'kode_assessment' => '',
            'judul' => 'Assessment Portofolio Diperbarui',
            'deskripsi' => 'Deskripsi baru.',
            'petunjuk' => 'Petunjuk baru.',
            'instrument_type' => 'portofolio',
            'target_ketenagaan' => 'tenaga_pendidik',
            'status' => 'publish',
            'is_active' => '1',
            'forms' => [
                [
                    'id' => $form->id,
                    'judul_form' => 'Form Profil Diperbarui',
                    'kode_form' => 'FORM-PROFIL',
                    'deskripsi' => 'Deskripsi form baru.',
                    'kompetensi' => '',
                    'indikator_kode' => '',
                    'indikator_label' => '',
                    'is_scoreable' => '0',
                    'urutan' => 1,
                    'is_active' => '1',
                    'scoring' => [
                        'profile' => '',
                        'weight' => '',
                        'exclude_from_competency' => '0',
                        'advanced_rules_text' => '',
                    ],
                    'fields' => [
                        [
                            'id' => $field->id,
                            'label' => 'Label Pertanyaan Diperbarui',
                            'deskripsi' => 'Deskripsi field baru.',
                            'tipe_field' => 'select',
                            'placeholder' => 'Placeholder diperbarui',
                            'bantuan' => 'Bantuan diperbarui',
                            'lookup_source' => 'master_golongan',
                            'opsi_field_text' => '',
                            'opsi_score_text' => '',
                            'repeater_config_text' => '',
                            'radio_options' => [],
                            'scoring' => [
                                'enabled' => '0',
                                'profile' => '',
                                'method' => 'presence',
                                'rubric_code' => '',
                                'weight' => '',
                                'score_if_answered' => '',
                                'scale_min' => '',
                                'scale_max' => '',
                                'reference_answer' => '',
                                'keyword_groups_text' => '',
                                'synonym_map_text' => '',
                                'min_words' => '',
                                'confidence_threshold' => '',
                                'manual_review_below_confidence' => '0',
                                'numeric_direction' => '',
                                'min_threshold' => '',
                                'target_threshold' => '',
                                'max_threshold' => '',
                                'min_score' => '',
                                'target_score' => '',
                                'max_score' => '',
                                'advanced_rules_text' => '',
                            ],
                            'lebar_kolom' => 'col-md-12',
                            'urutan' => 1,
                            'is_required' => '0',
                            'is_active' => '1',
                        ],
                    ],
                ],
            ],
        ];
    }
}
