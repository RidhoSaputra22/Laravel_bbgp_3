<?php

namespace Tests\Feature;

use App\Models\AssessmentAssignment;
use App\Models\AssessmentAssignmentTarget;
use App\Models\AssessmentCombination;
use App\Models\Guru;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AssessmentAssignmentApiTest extends TestCase
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
            $table->string('slug')->nullable();
            $table->text('deskripsi')->nullable();
            $table->text('petunjuk')->nullable();
            $table->string('instrument_type')->nullable();
            $table->json('scoring_config')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('assessment_forms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_id');
            $table->string('judul_form');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('urutan')->default(1);
            $table->timestamps();
        });

        Schema::create('assessment_form_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_form_id');
            $table->string('label');
            $table->string('nama_field');
            $table->string('tipe_field');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('urutan')->default(1);
            $table->timestamps();
        });

        Schema::create('assessment_combinations', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kombinasi');
            $table->string('judul');
            $table->string('target_ketenagaan')->nullable();
            $table->json('structure_snapshot')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('assessment_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('kode_penugasan');
            $table->string('judul_penugasan');
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('status_distribusi')->default('selesai');
            $table->string('target_ketenagaan')->nullable();
            $table->unsignedBigInteger('assessment_combination_id')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->timestamps();
        });

        Schema::create('assessment_assignment_assessments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_assignment_id');
            $table->unsignedBigInteger('assessment_id');
            $table->unsignedInteger('urutan')->default(1);
            $table->json('stage_config')->nullable();
            $table->timestamps();
        });

        Schema::create('gurus', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lengkap')->nullable();
            $table->string('no_ktp')->nullable();
            $table->string('nip')->nullable();
            $table->string('nuptk')->nullable();
            $table->string('email')->nullable();
            $table->string('eksternal_jabatan')->nullable();
            $table->string('jenis_jabatan')->nullable();
            $table->string('jabatan')->nullable();
            $table->string('kabupaten')->nullable();
            $table->string('satuan_pendidikan')->nullable();
            $table->timestamps();
        });

        Schema::create('assessment_assignment_targets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_assignment_id');
            $table->unsignedBigInteger('assessment_combination_id')->nullable();
            $table->unsignedBigInteger('guru_id');
            $table->string('status')->default('ditugaskan');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('assessment_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_assignment_target_id');
            $table->string('status')->default('draft');
            $table->json('structure_snapshot')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('assessment_attempts');
        Schema::dropIfExists('assessment_assignment_targets');
        Schema::dropIfExists('gurus');
        Schema::dropIfExists('assessment_assignment_assessments');
        Schema::dropIfExists('assessment_assignments');
        Schema::dropIfExists('assessment_combinations');
        Schema::dropIfExists('assessment_form_fields');
        Schema::dropIfExists('assessment_forms');
        Schema::dropIfExists('assessments');

        parent::tearDown();
    }

    public function test_assignment_api_groups_assignments_and_returns_each_participants_combination_forms(): void
    {
        $defaultCombination = AssessmentCombination::query()->create([
            'kode_kombinasi' => 'KMB-DEFAULT',
            'judul' => 'Kombinasi Default',
            'target_ketenagaan' => 'tenaga_pendidik',
            'structure_snapshot' => $this->snapshot('ASM-DEFAULT', 'Assessment Default', 'portofolio'),
        ]);
        $participantCombination = AssessmentCombination::query()->create([
            'kode_kombinasi' => 'KMB-PESERTA',
            'judul' => 'Kombinasi Peserta',
            'target_ketenagaan' => 'tenaga_pendidik',
            'structure_snapshot' => $this->participantSnapshot(),
        ]);
        $assignment = AssessmentAssignment::query()->create([
            'kode_penugasan' => 'TGS-API-001',
            'judul_penugasan' => 'Penugasan API',
            'target_ketenagaan' => 'tenaga_pendidik',
            'assessment_combination_id' => $defaultCombination->id,
            'status_distribusi' => 'selesai',
            'is_active' => true,
        ]);
        AssessmentAssignment::query()->create([
            'kode_penugasan' => 'TGS-API-002',
            'judul_penugasan' => 'Penugasan Kependidikan',
            'target_ketenagaan' => 'tenaga_kependidikan',
            'status_distribusi' => 'selesai',
            'is_active' => true,
        ]);
        $guru = Guru::query()->create([
            'nama_lengkap' => 'Nur Erny, S.Pd',
            'no_ktp' => '7312345678901234',
            'nip' => '198001012010012001',
            'nuptk' => '1234567890123456',
            'email' => 'nur@example.test',
            'eksternal_jabatan' => 'Tenaga Pendidik',
            'jenis_jabatan' => 'Guru Kelas',
            'kabupaten' => 'Kabupaten Bone',
            'satuan_pendidikan' => 'SD Negeri 1',
        ]);
        AssessmentAssignmentTarget::query()->create([
            'assessment_assignment_id' => $assignment->id,
            'assessment_combination_id' => $participantCombination->id,
            'guru_id' => $guru->id,
            'status' => 'ditugaskan',
            'assigned_at' => now(),
        ]);
        $secondGuru = Guru::query()->create([
            'nama_lengkap' => 'Peserta Kedua',
            'eksternal_jabatan' => 'Tenaga Pendidik',
        ]);
        AssessmentAssignmentTarget::query()->create([
            'assessment_assignment_id' => $assignment->id,
            'assessment_combination_id' => $participantCombination->id,
            'guru_id' => $secondGuru->id,
            'status' => 'ditugaskan',
            'assigned_at' => now(),
        ]);

        $this->getJson(route('api.v1.assignments.index'))
            ->assertOk()
            ->assertJsonPath('data.0.ketenagaan.kode', 'tenaga_pendidik')
            ->assertJsonPath('data.0.assignments.0.id', $assignment->id)
            ->assertJsonPath('data.0.assignments.0.participant_count', 2)
            ->assertJsonPath('data.1.ketenagaan.kode', 'tenaga_kependidikan');

        $this->getJson(route('api.v1.assignments.show', $assignment->id).'?per_page=1')
            ->assertOk()
            ->assertJsonPath('data.id', $assignment->id)
            ->assertJsonPath('data.participants.0.user.nama', 'Nur Erny, S.Pd')
            ->assertJsonPath('data.participants.0.user.nik', '7312345678901234')
            ->assertJsonPath('data.participants.0.user.nip', '198001012010012001')
            ->assertJsonPath('data.participants.0.combination.kode_kombinasi', 'KMB-PESERTA')
            ->assertJsonPath('data.participants.0.forms.0.instrument_type', 'portofolio')
            ->assertJsonPath('data.participants.0.forms.0.assessments.0.kode_assessment', 'ASM-PORTOFOLIO')
            ->assertJsonPath('data.participants.0.forms.1.instrument_type', 'studi_kasus')
            ->assertJsonPath('data.participants.0.forms.1.assessments.0.forms.0.fields.0.nama_field', 'jawaban_kasus')
            ->assertJsonPath(
                'data.participants.0.forms.1.assessments.0.forms.0.fields.0.scoring_config.advanced_rules_text.signal_keywords.0',
                'analisis'
            )
            ->assertJsonPath('meta.pagination.total', 2)
            ->assertJsonPath('meta.pagination.per_page', 1)
            ->assertJsonPath('meta.pagination.last_page', 2);

        $this->getJson(route('api.v1.assignments.show', $assignment->id).'?per_page=1&page=2')
            ->assertOk()
            ->assertJsonPath('data.participants.0.user.nama', 'Peserta Kedua');

        $this->getJson(route('api.v1.assignment-targets.index').'?guru_id='.$guru->id.'&per_page=1')
            ->assertOk()
            ->assertJsonPath('data.0.id', 1)
            ->assertJsonPath('data.0.assignment.id', $assignment->id)
            ->assertJsonPath('data.0.user.nama', 'Nur Erny, S.Pd')
            ->assertJsonPath('data.0.forms.0.instrument_type', 'portofolio')
            ->assertJsonPath('meta.pagination.total', 1);

        $this->getJson(route('api.v1.assignment-targets.index').'?assessment_assignment_id='.$assignment->id.'&per_page=1')
            ->assertOk()
            ->assertJsonPath('meta.pagination.total', 2);
    }

    private function participantSnapshot(): array
    {
        return [
            'assessments' => [
                $this->assessment('ASM-PORTOFOLIO', 'Portofolio Peserta', 'portofolio', 'unggah_portofolio'),
                $this->assessment('ASM-STUDI-KASUS', 'Studi Kasus Peserta', 'studi_kasus', 'jawaban_kasus'),
            ],
        ];
    }

    private function snapshot(string $code, string $title, string $instrument): array
    {
        return ['assessments' => [$this->assessment($code, $title, $instrument, 'jawaban')]];
    }

    private function assessment(string $code, string $title, string $instrument, string $fieldName): array
    {
        return [
            'id' => crc32($code),
            'kode_assessment' => $code,
            'judul' => $title,
            'deskripsi' => null,
            'petunjuk' => null,
            'instrument_type' => $instrument,
            'scoring_config' => [],
            'forms' => [[
                'id' => crc32($code.'-form'),
                'judul_form' => 'Form '.$title,
                'kode_form' => 'FORM-'.$code,
                'deskripsi' => null,
                'kompetensi' => null,
                'indikator_kode' => null,
                'indikator_label' => null,
                'is_scoreable' => true,
                'scoring_config' => [],
                'fields' => [[
                    'id' => crc32($code.'-field'),
                    'label' => 'Jawaban',
                    'nama_field' => $fieldName,
                    'tipe_field' => 'textarea',
                    'opsi_field' => [],
                    'validasi' => [],
                    'scoring_config' => ['advanced_rules_text' => json_encode([
                        'signal_keywords' => ['analisis'],
                    ])],
                    'is_required' => true,
                ]],
            ]],
        ];
    }
}
