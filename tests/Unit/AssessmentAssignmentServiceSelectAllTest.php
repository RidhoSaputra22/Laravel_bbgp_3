<?php

namespace Tests\Unit;

use App\Models\Assessment;
use App\Models\AssessmentAssignmentTarget;
use App\Models\Guru;
use App\Services\AssessmentAssignmentService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AssessmentAssignmentServiceSelectAllTest extends TestCase
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
            $table->string('status');
            $table->string('target_ketenagaan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('gurus', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lengkap');
            $table->string('email')->nullable();
            $table->string('satuan_pendidikan')->nullable();
            $table->string('kabupaten')->nullable();
            $table->string('eksternal_jabatan')->nullable();
            $table->string('jenis_jabatan')->nullable();
            $table->string('status_kepegawaian')->nullable();
            $table->string('is_verif')->default('belum');
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('kode_penugasan')->unique();
            $table->string('judul_penugasan');
            $table->string('target_ketenagaan')->nullable();
            $table->text('target_jabatan')->nullable();
            $table->text('target_kabupaten')->nullable();
            $table->text('deskripsi')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->time('jam_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->unsignedInteger('kapasitas_per_sesi')->default(0);
            $table->unsignedInteger('durasi_sesi_jam')->default(0);
            $table->unsignedInteger('total_sesi')->default(0);
            $table->string('status_distribusi')->default('draft');
            $table->unsignedInteger('total_target')->default(0);
            $table->unsignedInteger('total_ditugaskan')->default(0);
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->string('job_batch_id')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_assignment_assessments', function (Blueprint $table) {
            $table->unsignedBigInteger('assessment_assignment_id');
            $table->unsignedBigInteger('assessment_id');
            $table->unsignedInteger('urutan')->default(1);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::connection('sqlite')->create('assessment_assignment_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_assignment_id');
            $table->unsignedInteger('nomor_sesi');
            $table->string('label_sesi');
            $table->timestamp('waktu_mulai')->nullable();
            $table->timestamp('waktu_selesai')->nullable();
            $table->unsignedInteger('kapasitas_peserta')->default(0);
            $table->unsignedInteger('total_peserta')->default(0);
            $table->unsignedInteger('durasi_sesi_jam')->default(0);
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_assignment_targets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_assignment_id');
            $table->unsignedBigInteger('assessment_assignment_session_id')->nullable();
            $table->unsignedBigInteger('guru_id');
            $table->string('status')->default('ditugaskan');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['assessment_assignment_id', 'guru_id']);
        });
    }

    protected function tearDown(): void
    {
        Schema::connection('sqlite')->dropIfExists('assessment_assignment_targets');
        Schema::connection('sqlite')->dropIfExists('assessment_assignment_sessions');
        Schema::connection('sqlite')->dropIfExists('assessment_assignment_assessments');
        Schema::connection('sqlite')->dropIfExists('assessment_assignments');
        Schema::connection('sqlite')->dropIfExists('gurus');
        Schema::connection('sqlite')->dropIfExists('assessments');

        parent::tearDown();
    }

    public function test_create_assignment_resolves_select_all_scope_without_sending_all_ids(): void
    {
        $assessment = Assessment::query()->create([
            'kode_assessment' => 'ASM-001',
            'judul' => 'Assessment Monitoring',
            'status' => 'publish',
            'target_ketenagaan' => 'tenaga_pendidik',
            'is_active' => true,
        ]);

        $includedFirst = $this->createGuru([
            'nama_lengkap' => 'Guru Pendidik A',
            'email' => 'guru.a@example.test',
            'eksternal_jabatan' => 'Tenaga Pendidik',
            'jenis_jabatan' => 'Guru',
        ]);

        $includedSecond = $this->createGuru([
            'nama_lengkap' => 'Guru Pendidik B',
            'email' => 'guru.b@example.test',
            'eksternal_jabatan' => 'Tenaga Pendidik',
            'jenis_jabatan' => 'Guru',
        ]);

        $excluded = $this->createGuru([
            'nama_lengkap' => 'Guru Pendidik C',
            'email' => 'guru.c@example.test',
            'eksternal_jabatan' => 'Tenaga Pendidik',
            'jenis_jabatan' => 'Guru',
        ]);

        $this->createGuru([
            'nama_lengkap' => 'Pengawas Sekolah',
            'email' => 'pengawas@example.test',
            'eksternal_jabatan' => 'Tenaga Kependidikan',
            'jenis_jabatan' => 'Pengawas',
        ]);

        $assignment = app(AssessmentAssignmentService::class)->createAssignment([
            'judul_penugasan' => 'Penugasan Select All',
            'assessment_ids' => [$assessment->id],
            'durasi_sesi_jam' => 3,
            'guru_selection_mode' => 'select_all',
            'guru_selection_scope' => [
                'q' => 'Guru',
                'filters' => [
                    'eksternal_jabatan' => 'Tenaga Pendidik',
                    'jenis_jabatan' => 'Guru',
                ],
            ],
            'guru_excluded_ids' => [$excluded->id],
        ]);

        $this->assertSame(2, $assignment->total_target);
        $this->assertSame(2, (int) $assignment->targets()->count());
        $this->assertSame(
            [$includedFirst->id, $includedSecond->id],
            AssessmentAssignmentTarget::query()
                ->where('assessment_assignment_id', $assignment->id)
                ->orderBy('guru_id')
                ->pluck('guru_id')
                ->map(fn ($guruId) => (int) $guruId)
                ->all()
        );
    }

    public function test_create_assignment_resolves_assessments_and_users_from_target_ketenagaan(): void
    {
        $assessmentA = Assessment::query()->create([
            'kode_assessment' => 'ASM-001',
            'judul' => 'Assessment A',
            'status' => 'publish',
            'target_ketenagaan' => 'tenaga_pendidik',
            'is_active' => true,
        ]);

        $assessmentB = Assessment::query()->create([
            'kode_assessment' => 'ASM-002',
            'judul' => 'Assessment B',
            'status' => 'draft',
            'target_ketenagaan' => 'tenaga_pendidik',
            'is_active' => true,
        ]);

        Assessment::query()->create([
            'kode_assessment' => 'ASM-003',
            'judul' => 'Assessment Stakeholder',
            'status' => 'publish',
            'target_ketenagaan' => 'stakeholder',
            'is_active' => true,
        ]);

        $userA = $this->createGuru([
            'nama_lengkap' => 'User Pendidik A',
            'email' => 'pendidik.a@example.test',
            'eksternal_jabatan' => 'Tenaga Pendidik',
            'jenis_jabatan' => 'Guru',
        ]);

        $userB = $this->createGuru([
            'nama_lengkap' => 'User Pendidik B',
            'email' => 'pendidik.b@example.test',
            'eksternal_jabatan' => 'Tenaga Pendidik',
            'jenis_jabatan' => 'Guru',
        ]);

        $this->createGuru([
            'nama_lengkap' => 'User Stakeholder',
            'email' => 'stakeholder@example.test',
            'eksternal_jabatan' => 'Stakeholder',
            'jenis_jabatan' => 'Kepala Dinas',
        ]);

        $assignment = app(AssessmentAssignmentService::class)->createAssignment([
            'judul_penugasan' => 'Penugasan Tenaga Pendidik',
            'target_ketenagaan' => 'tenaga_pendidik',
            'durasi_sesi_jam' => 3,
        ]);

        $this->assertSame('tenaga_pendidik', $assignment->target_ketenagaan);
        $this->assertSame(2, $assignment->total_target);
        $this->assertSame(
            [$assessmentA->id, $assessmentB->id],
            $assignment->assessments()
                ->orderBy('assessment_assignment_assessments.urutan')
                ->pluck('assessments.id')
                ->map(fn ($assessmentId) => (int) $assessmentId)
                ->all()
        );
        $this->assertSame(
            [$userA->id, $userB->id],
            AssessmentAssignmentTarget::query()
                ->where('assessment_assignment_id', $assignment->id)
                ->orderBy('guru_id')
                ->pluck('guru_id')
                ->map(fn ($guruId) => (int) $guruId)
                ->all()
        );
    }

    public function test_create_assignment_can_filter_target_users_by_selected_jabatan(): void
    {
        Assessment::query()->create([
            'kode_assessment' => 'ASM-010',
            'judul' => 'Assessment Jabatan Pendidik',
            'status' => 'publish',
            'target_ketenagaan' => 'tenaga_pendidik',
            'is_active' => true,
        ]);

        $guru = $this->createGuru([
            'nama_lengkap' => 'Guru Mata Pelajaran',
            'email' => 'guru.mapel@example.test',
            'eksternal_jabatan' => 'Tenaga Pendidik',
            'jenis_jabatan' => 'Guru',
        ]);

        $kepalaSekolah = $this->createGuru([
            'nama_lengkap' => 'Kepala Sekolah BBGTK',
            'email' => 'kepsek@example.test',
            'eksternal_jabatan' => 'Tenaga Pendidik',
            'jenis_jabatan' => 'Kepala Sekolah',
        ]);

        $this->createGuru([
            'nama_lengkap' => 'Pengawas Pendidikan',
            'email' => 'pengawas@example.test',
            'eksternal_jabatan' => 'Tenaga Kependidikan',
            'jenis_jabatan' => 'Pengawas',
        ]);

        $assignment = app(AssessmentAssignmentService::class)->createAssignment([
            'judul_penugasan' => 'Penugasan Jabatan Kepala Sekolah',
            'target_ketenagaan' => 'tenaga_pendidik',
            'target_jabatan' => ['Kepala Sekolah'],
            'durasi_sesi_jam' => 3,
        ]);

        $assignedGuruIds = AssessmentAssignmentTarget::query()
            ->where('assessment_assignment_id', $assignment->id)
            ->orderBy('guru_id')
            ->pluck('guru_id')
            ->map(fn ($guruId) => (int) $guruId)
            ->all();

        $this->assertSame(['Kepala Sekolah'], $assignment->fresh()->target_jabatan);
        $this->assertSame(1, $assignment->total_target);
        $this->assertSame([$kepalaSekolah->id], $assignedGuruIds);
        $this->assertNotContains($guru->id, $assignedGuruIds);
    }

    public function test_create_assignment_can_filter_target_users_by_selected_jabatan_and_kabupaten(): void
    {
        Assessment::query()->create([
            'kode_assessment' => 'ASM-020',
            'judul' => 'Assessment Kabupaten Pendidik',
            'status' => 'publish',
            'target_ketenagaan' => 'tenaga_pendidik',
            'is_active' => true,
        ]);

        $makassarGuru = $this->createGuru([
            'nama_lengkap' => 'Guru Makassar',
            'email' => 'guru.makassar@example.test',
            'eksternal_jabatan' => 'Tenaga Pendidik',
            'jenis_jabatan' => 'Guru',
            'kabupaten' => 'Kota Makassar',
        ]);

        $gowaGuru = $this->createGuru([
            'nama_lengkap' => 'Guru Gowa',
            'email' => 'guru.gowa@example.test',
            'eksternal_jabatan' => 'Tenaga Pendidik',
            'jenis_jabatan' => 'Guru',
            'kabupaten' => 'Kabupaten Gowa',
        ]);

        $this->createGuru([
            'nama_lengkap' => 'Kepala Sekolah Makassar',
            'email' => 'kepsek.mks@example.test',
            'eksternal_jabatan' => 'Tenaga Pendidik',
            'jenis_jabatan' => 'Kepala Sekolah',
            'kabupaten' => 'Kota Makassar',
        ]);

        $assignment = app(AssessmentAssignmentService::class)->createAssignment([
            'judul_penugasan' => 'Penugasan Guru Makassar',
            'target_ketenagaan' => 'tenaga_pendidik',
            'target_jabatan' => ['Guru'],
            'target_kabupaten' => ['Kota Makassar'],
            'durasi_sesi_jam' => 3,
        ]);

        $assignedGuruIds = AssessmentAssignmentTarget::query()
            ->where('assessment_assignment_id', $assignment->id)
            ->orderBy('guru_id')
            ->pluck('guru_id')
            ->map(fn ($guruId) => (int) $guruId)
            ->all();

        $freshAssignment = $assignment->fresh();

        $this->assertSame(['Guru'], $freshAssignment->target_jabatan);
        $this->assertSame(['Kota Makassar'], $freshAssignment->target_kabupaten);
        $this->assertSame(1, $assignment->total_target);
        $this->assertSame([$makassarGuru->id], $assignedGuruIds);
        $this->assertNotContains($gowaGuru->id, $assignedGuruIds);
    }

    private function createGuru(array $overrides = []): Guru
    {
        return Guru::query()->create(array_merge([
            'nama_lengkap' => 'Guru Testing',
            'email' => 'guru.testing@example.test',
            'satuan_pendidikan' => 'BBGTK Sulsel',
            'kabupaten' => 'Kota Makassar',
            'eksternal_jabatan' => 'Tenaga Pendidik',
            'jenis_jabatan' => 'Guru',
            'status_kepegawaian' => 'ASN',
            'is_verif' => 'sudah',
        ], $overrides));
    }
}
