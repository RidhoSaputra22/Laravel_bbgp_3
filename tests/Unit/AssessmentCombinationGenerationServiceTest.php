<?php

namespace Tests\Unit;

use App\Models\Assessment;
use App\Models\AssessmentCombination;
use App\Models\AssessmentCombinationGeneration;
use App\Models\AssessmentForm;
use App\Models\AssessmentFormField;
use App\Services\Assessment\AssessmentCombinationGenerationService;
use App\Services\Assessment\AssessmentCombinationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AssessmentCombinationGenerationServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        config()->set('queue.default', 'sync');

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
            $table->string('autofill_source')->nullable();
            $table->string('lookup_source')->nullable();
            $table->json('validasi')->nullable();
            $table->json('scoring_config')->nullable();
            $table->string('lebar_kolom')->nullable();
            $table->unsignedInteger('urutan')->default(1);
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_combination_generations', function (Blueprint $table) {
            $table->id();
            $table->string('kode_generate')->unique();
            $table->string('target_ketenagaan');
            $table->unsignedInteger('total_kombinasi')->default(1);
            $table->json('selection_config')->nullable();
            $table->string('status')->default('diproses');
            $table->string('job_batch_id')->nullable();
            $table->unsignedBigInteger('generated_by')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_combinations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_combination_generation_id')->nullable();
            $table->unsignedInteger('generation_sequence')->nullable();
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

            $table->unique(
                ['assessment_combination_generation_id', 'generation_sequence'],
                'assessment_combination_generation_sequence_unique'
            );
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

        Schema::connection('sqlite')->create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::connection('sqlite')->create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        Schema::connection('sqlite')->create('jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });
    }

    protected function tearDown(): void
    {
        Schema::connection('sqlite')->dropIfExists('jobs');
        Schema::connection('sqlite')->dropIfExists('failed_jobs');
        Schema::connection('sqlite')->dropIfExists('job_batches');
        Schema::connection('sqlite')->dropIfExists('assessment_combination_items');
        Schema::connection('sqlite')->dropIfExists('assessment_combinations');
        Schema::connection('sqlite')->dropIfExists('assessment_combination_generations');
        Schema::connection('sqlite')->dropIfExists('assessment_form_fields');
        Schema::connection('sqlite')->dropIfExists('assessment_forms');
        Schema::connection('sqlite')->dropIfExists('assessments');

        parent::tearDown();
    }

    public function test_it_creates_requested_generation_and_stores_all_combinations(): void
    {
        $assessment = $this->createAssessmentFixture();

        /** @var AssessmentCombinationGeneration $generation */
        $generation = app(AssessmentCombinationGenerationService::class)->createGeneration([
            'target_ketenagaan' => 'tenaga_pendidik',
            'total_kombinasi' => 2,
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

        $generation = $generation->fresh()->loadCount('combinations');
        $monitoring = app(AssessmentCombinationGenerationService::class)->buildGenerationMonitoring($generation, false);

        $this->assertStringStartsWith('KBG-ASM-', $generation->kode_generate);
        $this->assertSame('selesai', $generation->status);
        $this->assertSame(2, $generation->combinations_count);
        $this->assertSame(2, $monitoring['generated_total']);
        $this->assertSame(0, $monitoring['missing_total']);
        $this->assertFalse($monitoring['retry_available']);

        $storedSequences = AssessmentCombination::query()
            ->where('assessment_combination_generation_id', $generation->id)
            ->orderBy('generation_sequence')
            ->pluck('generation_sequence')
            ->all();

        $this->assertSame([1, 2], array_map('intval', $storedSequences));
    }

    public function test_it_retries_only_missing_generation_sequences(): void
    {
        $assessment = $this->createAssessmentFixture();
        $payload = [
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
        ];

        $generation = AssessmentCombinationGeneration::query()->create([
            'kode_generate' => 'KBG-ASM-TEST-0001',
            'target_ketenagaan' => 'tenaga_pendidik',
            'total_kombinasi' => 3,
            'selection_config' => array_merge($payload, ['total_kombinasi' => 3]),
            'status' => 'gagal',
        ]);

        $combination = app(AssessmentCombinationService::class)->createCombination($payload);
        $combination->forceFill([
            'assessment_combination_generation_id' => $generation->id,
            'generation_sequence' => 1,
        ])->save();

        $result = app(AssessmentCombinationGenerationService::class)->retryGeneration($generation);

        $this->assertTrue($result['queued']);
        $this->assertFalse($result['already_complete']);
        $this->assertSame(2, $result['resumed_count']);

        $generation = $generation->fresh()->loadCount('combinations');
        $this->assertSame('selesai', $generation->status);
        $this->assertSame(3, $generation->combinations_count);

        $storedSequences = AssessmentCombination::query()
            ->where('assessment_combination_generation_id', $generation->id)
            ->orderBy('generation_sequence')
            ->pluck('generation_sequence')
            ->all();

        $this->assertSame([1, 2, 3], array_map('intval', $storedSequences));
    }

    public function test_it_marks_failed_generation_with_partial_success_as_resumeable(): void
    {
        $assessment = $this->createAssessmentFixture();
        $payload = [
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
        ];

        $generation = AssessmentCombinationGeneration::query()->create([
            'kode_generate' => 'KBG-ASM-TEST-0002',
            'target_ketenagaan' => 'tenaga_pendidik',
            'total_kombinasi' => 3,
            'selection_config' => array_merge($payload, ['total_kombinasi' => 3]),
            'status' => 'gagal',
        ]);

        $combination = app(AssessmentCombinationService::class)->createCombination($payload);
        $combination->forceFill([
            'assessment_combination_generation_id' => $generation->id,
            'generation_sequence' => 1,
        ])->save();

        $monitoring = app(AssessmentCombinationGenerationService::class)->buildGenerationMonitoring($generation, false);

        $this->assertTrue($monitoring['retry_available']);
        $this->assertFalse($monitoring['all_failed']);
        $this->assertSame('Resume Sisa Gagal', $monitoring['action_label']);
        $this->assertSame(1, $monitoring['generated_total']);
        $this->assertSame(2, $monitoring['missing_total']);
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
            'judul_form' => 'Refleksi Diri',
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

        $this->createField($pedagogikA, 'Pedagogik A1', 1);
        $this->createField($pedagogikA, 'Pedagogik A2', 2);
        $this->createField($pedagogikB, 'Pedagogik B1', 1);
        $this->createField($kepribadian, 'Refleksi 1', 1);
        $this->createField($identity, 'Nama Lengkap', 1);
        $this->createField($identity, 'NIP', 2);

        return $assessment;
    }

    private function createField(AssessmentForm $form, string $label, int $order): AssessmentFormField
    {
        return AssessmentFormField::query()->create([
            'assessment_form_id' => $form->id,
            'label' => $label,
            'deskripsi' => null,
            'nama_field' => 'field_'.$form->id.'_'.$order,
            'tipe_field' => 'text',
            'urutan' => $order,
            'is_required' => true,
            'is_active' => true,
        ]);
    }
}
