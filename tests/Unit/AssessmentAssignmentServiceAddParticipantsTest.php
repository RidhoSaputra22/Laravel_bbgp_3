<?php

namespace Tests\Unit;

use App\Models\AssessmentAssignment;
use App\Models\AssessmentAssignmentSession;
use App\Models\AssessmentAssignmentTarget;
use App\Models\Guru;
use App\Services\AssessmentAssignmentService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AssessmentAssignmentServiceAddParticipantsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::connection('sqlite')->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessments', function (Blueprint $table) {
            $table->id();
            $table->string('kode_assessment')->nullable();
            $table->string('judul')->nullable();
            $table->string('status')->default('publish');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_combinations', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kombinasi')->nullable();
            $table->string('judul')->nullable();
            $table->string('target_ketenagaan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_assignment_assessments', function (Blueprint $table) {
            $table->unsignedBigInteger('assessment_assignment_id');
            $table->unsignedBigInteger('assessment_id');
            $table->unsignedInteger('urutan')->default(1);
            $table->text('stage_config')->nullable();
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('kode_penugasan')->unique();
            $table->string('judul_penugasan');
            $table->boolean('session_enabled')->default(true);
            $table->string('target_ketenagaan')->nullable();
            $table->unsignedBigInteger('assessment_combination_id')->nullable();
            $table->text('target_jabatan')->nullable();
            $table->text('target_kabupaten')->nullable();
            $table->text('target_satuan_pendidikan')->nullable();
            $table->text('deskripsi')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->time('jam_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->unsignedInteger('kapasitas_per_sesi')->default(41);
            $table->unsignedInteger('durasi_sesi_jam')->default(3);
            $table->text('security_config')->nullable();
            $table->unsignedInteger('total_sesi')->default(0);
            $table->string('status_distribusi')->default('selesai');
            $table->unsignedInteger('total_target')->default(0);
            $table->unsignedInteger('total_ditugaskan')->default(0);
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->string('job_batch_id')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_assignment_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_assignment_id');
            $table->unsignedInteger('nomor_sesi');
            $table->string('label_sesi');
            $table->timestamp('waktu_mulai')->nullable();
            $table->timestamp('waktu_selesai')->nullable();
            $table->unsignedInteger('kapasitas_peserta')->default(41);
            $table->unsignedInteger('total_peserta')->default(0);
            $table->unsignedInteger('durasi_sesi_jam')->default(3);
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

        Schema::connection('sqlite')->create('assessment_assignment_targets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_assignment_id');
            $table->unsignedBigInteger('assessment_assignment_session_id')->nullable();
            $table->unsignedBigInteger('assessment_combination_id')->nullable();
            $table->unsignedBigInteger('guru_id');
            $table->string('status')->default('ditugaskan');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('deadline_at')->nullable();
            $table->string('completion_mode')->nullable();
            $table->timestamp('timed_out_at')->nullable();
            $table->timestamps();

            $table->unique(['assessment_assignment_id', 'guru_id']);
        });
    }

    protected function tearDown(): void
    {
        Schema::connection('sqlite')->dropIfExists('assessment_assignment_targets');
        Schema::connection('sqlite')->dropIfExists('gurus');
        Schema::connection('sqlite')->dropIfExists('assessment_assignment_sessions');
        Schema::connection('sqlite')->dropIfExists('assessment_assignments');
        Schema::connection('sqlite')->dropIfExists('assessment_assignment_assessments');
        Schema::connection('sqlite')->dropIfExists('assessment_combinations');
        Schema::connection('sqlite')->dropIfExists('assessments');
        Schema::connection('sqlite')->dropIfExists('users');

        parent::tearDown();
    }

    public function test_add_participants_accepts_any_participant_in_same_ketenagaan_and_keeps_existing_target_untouched(): void
    {
        $existingGuru = $this->createGuru([
            'nama_lengkap' => 'Peserta Lama',
            'email' => 'lama@example.test',
        ]);
        $newGuru = $this->createGuru([
            'nama_lengkap' => 'Peserta Baru',
            'email' => 'baru@example.test',
            'satuan_pendidikan' => 'SMP Negeri 1 Gowa',
            'kabupaten' => 'Kabupaten Gowa',
            'jenis_jabatan' => 'Kepala Sekolah',
        ]);

        $assignment = AssessmentAssignment::query()->create([
            'kode_penugasan' => 'TGS-ADD-001',
            'judul_penugasan' => 'Penugasan Tambah Peserta',
            'session_enabled' => true,
            'target_ketenagaan' => 'tenaga_pendidik',
            'target_jabatan' => ['Guru'],
            'target_kabupaten' => ['Kota Makassar'],
            'target_satuan_pendidikan' => [],
            'tanggal_mulai' => now()->toDateString(),
            'jam_mulai' => '08:00:00',
            'tanggal_selesai' => now()->addDay()->toDateString(),
            'kapasitas_per_sesi' => 41,
            'durasi_sesi_jam' => 3,
            'total_sesi' => 1,
            'status_distribusi' => 'selesai',
            'total_target' => 1,
            'total_ditugaskan' => 1,
            'processed_at' => now(),
        ]);

        $startedSession = AssessmentAssignmentSession::query()->create([
            'assessment_assignment_id' => $assignment->id,
            'nomor_sesi' => 1,
            'label_sesi' => 'Sesi 1',
            'waktu_mulai' => now()->subHour(),
            'waktu_selesai' => now()->addHours(2),
            'kapasitas_peserta' => 41,
            'total_peserta' => 1,
            'durasi_sesi_jam' => 3,
        ]);

        $existingTarget = AssessmentAssignmentTarget::query()->create([
            'assessment_assignment_id' => $assignment->id,
            'assessment_assignment_session_id' => $startedSession->id,
            'guru_id' => $existingGuru->id,
            'status' => 'dikerjakan',
            'assigned_at' => now()->subHour(),
            'started_at' => now()->subMinutes(45),
        ]);

        $result = app(AssessmentAssignmentService::class)->addParticipants($assignment, [$newGuru->id]);

        $assignment->refresh();
        $existingTarget->refresh();
        $newTarget = AssessmentAssignmentTarget::query()
            ->where('assessment_assignment_id', $assignment->id)
            ->where('guru_id', $newGuru->id)
            ->first();
        $newSession = AssessmentAssignmentSession::query()
            ->where('assessment_assignment_id', $assignment->id)
            ->where('nomor_sesi', 2)
            ->first();

        $this->assertSame(1, $result['added_count']);
        $this->assertSame(1, $result['created_session_count']);
        $this->assertSame('dikerjakan', $existingTarget->status);
        $this->assertSame($startedSession->id, $existingTarget->assessment_assignment_session_id);
        $this->assertNotNull($existingTarget->started_at);
        $this->assertNotNull($newTarget);
        $this->assertNotNull($newSession);
        $this->assertSame($newSession->id, $newTarget->assessment_assignment_session_id);
        $this->assertNotSame($startedSession->id, $newTarget->assessment_assignment_session_id);
        $this->assertSame(1, (int) $startedSession->fresh()->total_peserta);
        $this->assertSame(1, (int) $newSession->total_peserta);
        $this->assertSame(2, (int) $assignment->total_target);
        $this->assertSame(2, (int) $assignment->total_ditugaskan);
        $this->assertSame(2, (int) $assignment->total_sesi);
        $this->assertSame('selesai', $assignment->status_distribusi);
    }

    private function createGuru(array $overrides = []): Guru
    {
        return Guru::query()->create(array_merge([
            'nama_lengkap' => 'Guru Testing',
            'email' => 'guru.testing@example.test',
            'satuan_pendidikan' => 'SD Negeri 1 Makassar',
            'kabupaten' => 'Kota Makassar',
            'eksternal_jabatan' => 'Tenaga Pendidik',
            'jenis_jabatan' => 'Guru',
            'status_kepegawaian' => 'ASN',
            'is_verif' => 'sudah',
        ], $overrides));
    }
}
