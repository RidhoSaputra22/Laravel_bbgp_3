<?php

namespace Tests\Unit;

use App\Jobs\ProcessAssessmentAssignmentTargetsJob;
use App\Models\Assessment;
use App\Models\AssessmentAssignment;
use App\Models\AssessmentAssignmentTarget;
use App\Models\Guru;
use App\Services\AssessmentAssignmentService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
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

        Schema::connection('sqlite')->create('assessment_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_assignment_target_id');
            $table->string('status')->default('draft');
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at')->default(0);
            $table->unsignedInteger('created_at')->default(0);
        });

        Schema::connection('sqlite')->create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection')->nullable();
            $table->text('queue')->nullable();
            $table->longText('payload');
            $table->longText('exception')->nullable();
            $table->timestamp('failed_at')->nullable();
        });
    }

    protected function tearDown(): void
    {
        Schema::connection('sqlite')->dropIfExists('failed_jobs');
        Schema::connection('sqlite')->dropIfExists('jobs');
        Schema::connection('sqlite')->dropIfExists('assessment_attempts');
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

    public function test_update_assignment_preserves_started_targets_and_cancels_removed_pending_targets(): void
    {
        Assessment::query()->create([
            'kode_assessment' => 'ASM-030',
            'judul' => 'Assessment Update',
            'status' => 'publish',
            'target_ketenagaan' => 'tenaga_pendidik',
            'is_active' => true,
        ]);

        $startedGuru = $this->createGuru([
            'nama_lengkap' => 'Guru Mulai Makassar',
            'email' => 'started@example.test',
            'kabupaten' => 'Kota Makassar',
        ]);

        $pendingGuru = $this->createGuru([
            'nama_lengkap' => 'Guru Pending Makassar',
            'email' => 'pending@example.test',
            'kabupaten' => 'Kota Makassar',
        ]);

        $newGuru = $this->createGuru([
            'nama_lengkap' => 'Guru Baru Gowa',
            'email' => 'new@example.test',
            'kabupaten' => 'Kabupaten Gowa',
        ]);

        $assignment = app(AssessmentAssignmentService::class)->createAssignment([
            'judul_penugasan' => 'Penugasan Update',
            'target_ketenagaan' => 'tenaga_pendidik',
            'target_jabatan' => ['Guru'],
            'target_kabupaten' => ['Kota Makassar'],
            'durasi_sesi_jam' => 3,
        ]);

        AssessmentAssignmentTarget::query()
            ->where('assessment_assignment_id', $assignment->id)
            ->where('guru_id', $startedGuru->id)
            ->update([
                'status' => 'dikerjakan',
                'started_at' => now(),
            ]);

        $result = app(AssessmentAssignmentService::class)->updateAssignment($assignment->fresh(), [
            'judul_penugasan' => 'Penugasan Update Revisi',
            'target_ketenagaan' => 'tenaga_pendidik',
            'target_jabatan' => ['Guru'],
            'target_kabupaten' => ['Kabupaten Gowa'],
            'durasi_sesi_jam' => 3,
        ]);

        /** @var \App\Models\AssessmentAssignment $updatedAssignment */
        $updatedAssignment = $result['assignment'];
        $targetsByGuru = AssessmentAssignmentTarget::query()
            ->where('assessment_assignment_id', $updatedAssignment->id)
            ->get()
            ->keyBy('guru_id');

        $this->assertSame(1, $result['preserved_locked_count']);
        $this->assertSame(1, $result['cancelled_target_count']);
        $this->assertSame(1, $result['new_target_count']);
        $this->assertSame(2, $updatedAssignment->total_target);
        $this->assertSame(2, $updatedAssignment->total_ditugaskan);
        $this->assertSame('selesai', $updatedAssignment->status_distribusi);
        $this->assertSame('dikerjakan', $targetsByGuru[$startedGuru->id]->status);
        $this->assertSame('dibatalkan', $targetsByGuru[$pendingGuru->id]->status);
        $this->assertSame('ditugaskan', $targetsByGuru[$newGuru->id]->status);
    }

    public function test_retry_assignment_resumes_only_missing_targets_from_failed_job_payload(): void
    {
        $assessment = Assessment::query()->create([
            'kode_assessment' => 'ASM-040',
            'judul' => 'Assessment Retry',
            'status' => 'publish',
            'target_ketenagaan' => 'tenaga_pendidik',
            'is_active' => true,
        ]);

        $storedGuru = $this->createGuru([
            'nama_lengkap' => 'Guru Sudah Tersimpan',
            'email' => 'stored@example.test',
        ]);

        $missingGuru = $this->createGuru([
            'nama_lengkap' => 'Guru Belum Tersimpan',
            'email' => 'missing@example.test',
        ]);

        $assignment = AssessmentAssignment::query()->create([
            'kode_penugasan' => 'TGS-ASM-RETRY-001',
            'judul_penugasan' => 'Penugasan Retry',
            'target_ketenagaan' => 'tenaga_pendidik',
            'target_jabatan' => ['Guru'],
            'target_kabupaten' => ['Kota Makassar'],
            'kapasitas_per_sesi' => 41,
            'durasi_sesi_jam' => 3,
            'total_sesi' => 1,
            'status_distribusi' => 'gagal',
            'total_target' => 2,
            'total_ditugaskan' => 1,
            'job_batch_id' => 'batch-retry-001',
        ]);

        $assignment->assessments()->sync([
            $assessment->id => ['urutan' => 1],
        ]);

        $session = $assignment->sessions()->create([
            'nomor_sesi' => 1,
            'label_sesi' => 'Sesi 1',
            'kapasitas_peserta' => 41,
            'total_peserta' => 2,
            'durasi_sesi_jam' => 3,
        ]);

        AssessmentAssignmentTarget::query()->create([
            'assessment_assignment_id' => $assignment->id,
            'assessment_assignment_session_id' => $session->id,
            'guru_id' => $storedGuru->id,
            'status' => 'ditugaskan',
            'assigned_at' => now(),
        ]);

        $payloadRows = [
            [
                'assessment_assignment_id' => $assignment->id,
                'assessment_assignment_session_id' => $session->id,
                'guru_id' => $storedGuru->id,
                'status' => 'ditugaskan',
                'assigned_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'assessment_assignment_id' => $assignment->id,
                'assessment_assignment_session_id' => $session->id,
                'guru_id' => $missingGuru->id,
                'status' => 'ditugaskan',
                'assigned_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        $job = new ProcessAssessmentAssignmentTargetsJob($assignment->id, $payloadRows);

        DB::table('failed_jobs')->insert([
            'uuid' => (string) Str::uuid(),
            'connection' => 'database',
            'queue' => 'assessment-assignment',
            'payload' => json_encode([
                'displayName' => ProcessAssessmentAssignmentTargetsJob::class,
                'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
                'data' => [
                    'commandName' => ProcessAssessmentAssignmentTargetsJob::class,
                    'command' => serialize($job),
                ],
            ]),
            'exception' => 'RuntimeException: Simulasi gagal',
            'failed_at' => now(),
        ]);

        $result = app(AssessmentAssignmentService::class)->retryAssignment($assignment->fresh());

        /** @var \App\Models\AssessmentAssignment $retriedAssignment */
        $retriedAssignment = $result['assignment'];

        $this->assertFalse($result['queued']);
        $this->assertFalse($result['already_complete']);
        $this->assertSame(1, $result['resumed_count']);
        $this->assertSame(2, (int) $retriedAssignment->targets()->count());
        $this->assertSame('selesai', $retriedAssignment->status_distribusi);
        $this->assertNull($retriedAssignment->job_batch_id);
        $this->assertDatabaseHas('assessment_assignment_targets', [
            'assessment_assignment_id' => $assignment->id,
            'guru_id' => $missingGuru->id,
        ]);
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
