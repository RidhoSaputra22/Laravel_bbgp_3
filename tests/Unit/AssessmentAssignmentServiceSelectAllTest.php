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
