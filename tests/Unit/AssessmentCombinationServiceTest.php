<?php

namespace Tests\Unit;

use App\Models\Assessment;
use App\Models\AssessmentCombination;
use App\Models\AssessmentForm;
use App\Models\AssessmentFormField;
use App\Services\Assessment\AssessmentCombinationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AssessmentCombinationServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::connection('sqlite')->create('assessments', function (Blueprint $table) {
            $table->id();
            $table->string('kode_assessment');
            $table->string('judul');
            $table->string('slug')->nullable();
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
            $table->boolean('is_scoreable')->default(false);
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
            $table->string('nama_field')->nullable();
            $table->string('tipe_field')->default('text');
            $table->string('placeholder')->nullable();
            $table->text('bantuan')->nullable();
            $table->json('opsi_field')->nullable();
            $table->text('nilai_default')->nullable();
            $table->json('validasi')->nullable();
            $table->json('scoring_config')->nullable();
            $table->string('lebar_kolom')->nullable();
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
            $table->unsignedBigInteger('assessment_id');
            $table->unsignedBigInteger('assessment_form_id');
            $table->unsignedBigInteger('assessment_form_field_id');
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
    }

    protected function tearDown(): void
    {
        Schema::connection('sqlite')->dropIfExists('assessment_combination_items');
        Schema::connection('sqlite')->dropIfExists('assessment_combinations');
        Schema::connection('sqlite')->dropIfExists('assessment_form_fields');
        Schema::connection('sqlite')->dropIfExists('assessment_forms');
        Schema::connection('sqlite')->dropIfExists('assessments');

        parent::tearDown();
    }

    public function test_it_builds_assessment_catalog_with_competency_and_auto_form_sections(): void
    {
        $assessment = $this->createAssessmentFixture();

        $catalog = app(AssessmentCombinationService::class)->buildAssessmentCatalogByKetenagaan();
        $assessmentCatalog = collect($catalog['tenaga_pendidik'] ?? [])->firstWhere('assessment_id', $assessment->id);

        $this->assertNotNull($assessmentCatalog);
        $this->assertSame('Assessment Kompetensi Guru', $assessmentCatalog['assessment_title']);
        $this->assertSame(4, $assessmentCatalog['total_forms']);
        $this->assertSame(6, $assessmentCatalog['total_questions']);
        $this->assertSame(1, $assessmentCatalog['auto_included_form_count']);
        $this->assertSame(2, $assessmentCatalog['auto_included_question_count']);

        $pedagogik = collect($assessmentCatalog['competencies'])->firstWhere('kompetensi', 'pedagogik');
        $kepribadian = collect($assessmentCatalog['competencies'])->firstWhere('kompetensi', 'kepribadian');
        $sosial = collect($assessmentCatalog['competencies'])->firstWhere('kompetensi', 'sosial');

        $this->assertSame(2, $pedagogik['available_form_count']);
        $this->assertSame(3, $pedagogik['available_question_count']);
        $this->assertSame(1, $kepribadian['available_form_count']);
        $this->assertSame(1, $kepribadian['available_question_count']);
        $this->assertSame(0, $sosial['available_question_count']);
    }

    public function test_it_creates_combination_by_competency_and_auto_includes_forms_without_competency(): void
    {
        $assessment = $this->createAssessmentFixture();

        /** @var AssessmentCombination $combination */
        $combination = app(AssessmentCombinationService::class)->createCombination([
            'target_ketenagaan' => 'tenaga_pendidik',
            'competency_selection_modes' => [
                $assessment->id => [
                    'pedagogik' => 'count',
                    'kepribadian' => 'all',
                ],
            ],
            'competency_take_counts' => [
                $assessment->id => [
                    'pedagogik' => 2,
                    'kepribadian' => 1,
                ],
            ],
        ]);

        $this->assertStringStartsWith('KMB-ASM-', $combination->kode_kombinasi);
        $this->assertSame($combination->kode_kombinasi, $combination->judul);
        $this->assertNull($combination->deskripsi);
        $this->assertSame(1, $combination->total_assessments);
        $this->assertSame(5, $combination->total_questions);

        $pedagogikItems = $combination->items->where('kompetensi', 'pedagogik')->values();
        $kepribadianItems = $combination->items->where('kompetensi', 'kepribadian')->values();
        $autoIncludedItems = $combination->items->whereNull('kompetensi')->values();

        $this->assertCount(2, $pedagogikItems);
        $this->assertCount(1, $kepribadianItems);
        $this->assertCount(2, $autoIncludedItems);
        $this->assertSame(
            2,
            $pedagogikItems->pluck('assessment_form_field_id')->unique()->count()
        );

        $selectionAssessment = collect(data_get($combination->selection_config, 'assessments', []))
            ->firstWhere('assessment_id', $assessment->id);
        $pedagogikSelection = collect($selectionAssessment['competencies'] ?? [])
            ->firstWhere('kompetensi', 'pedagogik');
        $kepribadianSelection = collect($selectionAssessment['competencies'] ?? [])
            ->firstWhere('kompetensi', 'kepribadian');

        $this->assertSame('count', $pedagogikSelection['selection_mode']);
        $this->assertSame(2, $pedagogikSelection['selected_question_count']);
        $this->assertSame('all', $kepribadianSelection['selection_mode']);
        $this->assertSame(1, $kepribadianSelection['selected_question_count']);
        $this->assertSame(
            'fixed_all',
            data_get($selectionAssessment, 'auto_included_forms.0.selection_mode')
        );

        $snapshotIdentityForm = collect(data_get($combination->structure_snapshot, 'assessments.0.forms', []))
            ->firstWhere('kode_form', 'FORM-ID');

        $this->assertNotNull($snapshotIdentityForm);
        $this->assertSame(2, count($snapshotIdentityForm['fields'] ?? []));
    }

    private function createAssessmentFixture(): Assessment
    {
        $assessment = Assessment::query()->create([
            'kode_assessment' => 'ASM-001',
            'judul' => 'Assessment Kompetensi Guru',
            'deskripsi' => 'Assessment untuk pemetaan kompetensi guru.',
            'petunjuk' => 'Isi seluruh bagian.',
            'instrument_type' => 'portofolio',
            'target_ketenagaan' => 'tenaga_pendidik',
            'status' => 'publish',
            'is_active' => true,
        ]);

        $pedagogikA = AssessmentForm::query()->create([
            'assessment_id' => $assessment->id,
            'judul_form' => 'Pedagogik A',
            'kode_form' => 'FORM-PED-A',
            'kompetensi' => 'pedagogik',
            'is_scoreable' => true,
            'urutan' => 1,
            'is_active' => true,
        ]);

        $pedagogikB = AssessmentForm::query()->create([
            'assessment_id' => $assessment->id,
            'judul_form' => 'Pedagogik B',
            'kode_form' => 'FORM-PED-B',
            'kompetensi' => 'pedagogik',
            'is_scoreable' => true,
            'urutan' => 2,
            'is_active' => true,
        ]);

        $kepribadian = AssessmentForm::query()->create([
            'assessment_id' => $assessment->id,
            'judul_form' => 'Refleksi Kepribadian',
            'kode_form' => 'FORM-KEP',
            'kompetensi' => 'kepribadian',
            'is_scoreable' => true,
            'urutan' => 3,
            'is_active' => true,
        ]);

        $identity = AssessmentForm::query()->create([
            'assessment_id' => $assessment->id,
            'judul_form' => 'Identitas Responden',
            'kode_form' => 'FORM-ID',
            'kompetensi' => null,
            'is_scoreable' => false,
            'urutan' => 4,
            'is_active' => true,
        ]);

        $this->createField($pedagogikA->id, 'Pedagogik 1', 'pedagogik_1', 1);
        $this->createField($pedagogikA->id, 'Pedagogik 2', 'pedagogik_2', 2);
        $this->createField($pedagogikB->id, 'Pedagogik 3', 'pedagogik_3', 1);
        $this->createField($kepribadian->id, 'Kepribadian 1', 'kepribadian_1', 1);
        $this->createField($identity->id, 'Nama Lengkap', 'nama_lengkap', 1);
        $this->createField($identity->id, 'Nomor Induk', 'nomor_induk', 2);

        return $assessment;
    }

    private function createField(int $formId, string $label, string $name, int $order): AssessmentFormField
    {
        return AssessmentFormField::query()->create([
            'assessment_form_id' => $formId,
            'label' => $label,
            'nama_field' => $name,
            'tipe_field' => 'text',
            'urutan' => $order,
            'is_required' => true,
            'is_active' => true,
        ]);
    }
}
