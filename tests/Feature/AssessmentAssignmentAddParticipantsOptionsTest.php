<?php

namespace Tests\Feature;

use App\Models\AssessmentAssignment;
use App\Models\AssessmentAssignmentTarget;
use App\Models\Guru;
use App\Support\Assessment\AssessmentSchoolTargetKey;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AssessmentAssignmentAddParticipantsOptionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::connection('sqlite')->create('assessment_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('kode_penugasan')->unique();
            $table->string('judul_penugasan');
            $table->boolean('session_enabled')->default(true);
            $table->string('target_ketenagaan')->nullable();
            $table->text('target_jabatan')->nullable();
            $table->text('target_kabupaten')->nullable();
            $table->text('target_satuan_pendidikan')->nullable();
            $table->string('status_distribusi')->default('selesai');
            $table->unsignedInteger('total_target')->default(0);
            $table->unsignedInteger('total_ditugaskan')->default(0);
            $table->unsignedInteger('durasi_sesi_jam')->default(3);
            $table->unsignedInteger('total_sesi')->default(0);
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_assignment_targets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_assignment_id');
            $table->unsignedBigInteger('guru_id');
            $table->string('status')->default('ditugaskan');
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
    }

    protected function tearDown(): void
    {
        Schema::connection('sqlite')->dropIfExists('gurus');
        Schema::connection('sqlite')->dropIfExists('assessment_assignment_targets');
        Schema::connection('sqlite')->dropIfExists('assessment_assignments');

        parent::tearDown();
    }

    public function test_add_participant_options_only_return_unassigned_participants_within_stored_assignment_target_scope(): void
    {
        $assignment = AssessmentAssignment::query()->create([
            'kode_penugasan' => 'TGS-OPT-001',
            'judul_penugasan' => 'Penugasan Opsi Tambahan',
            'target_ketenagaan' => 'tenaga_pendidik',
            'target_jabatan' => ['Guru'],
            'target_kabupaten' => ['Kota Makassar'],
            'target_satuan_pendidikan' => [
                AssessmentSchoolTargetKey::encode('Kota Makassar', 'SD Negeri 1 Makassar'),
            ],
            'status_distribusi' => 'selesai',
            'total_target' => 1,
            'total_ditugaskan' => 1,
            'durasi_sesi_jam' => 3,
        ]);

        $alreadyAssigned = $this->createGuru([
            'nama_lengkap' => 'Peserta Sudah Ditugaskan',
            'email' => 'assigned@example.test',
        ]);
        $eligible = $this->createGuru([
            'nama_lengkap' => 'Peserta Baru Makassar',
            'email' => 'baru@example.test',
        ]);
        $differentSchool = $this->createGuru([
            'nama_lengkap' => 'Peserta Sekolah Lain',
            'email' => 'lain@example.test',
            'satuan_pendidikan' => 'SD Negeri 2 Makassar',
        ]);
        $differentKabupaten = $this->createGuru([
            'nama_lengkap' => 'Peserta Gowa',
            'email' => 'gowa@example.test',
            'kabupaten' => 'Kabupaten Gowa',
        ]);
        $differentJabatan = $this->createGuru([
            'nama_lengkap' => 'Kepala Sekolah Baru',
            'email' => 'kepsek@example.test',
            'jenis_jabatan' => 'Kepala Sekolah',
        ]);
        $differentKetenagaan = $this->createGuru([
            'nama_lengkap' => 'Peserta Stakeholder',
            'email' => 'stakeholder@example.test',
            'eksternal_jabatan' => 'Stakeholder',
            'jenis_jabatan' => 'Kepala Dinas',
        ]);

        AssessmentAssignmentTarget::query()->create([
            'assessment_assignment_id' => $assignment->id,
            'guru_id' => $alreadyAssigned->id,
            'status' => 'ditugaskan',
        ]);

        $response = $this
            ->withSession([
                'cek' => true,
                'role' => 'admin',
            ])
            ->getJson(route('assessment.assignment.add-participants-options', [
                'id' => $assignment->id,
            ]));

        $response->assertOk();
        $response->assertJsonCount(1, 'items');
        $response->assertJsonFragment(['id' => (string) $eligible->id]);
        $response->assertJsonMissing(['id' => (string) $alreadyAssigned->id]);
        $response->assertJsonMissing(['id' => (string) $differentSchool->id]);
        $response->assertJsonMissing(['id' => (string) $differentKabupaten->id]);
        $response->assertJsonMissing(['id' => (string) $differentJabatan->id]);
        $response->assertJsonMissing(['id' => (string) $differentKetenagaan->id]);
    }

    public function test_add_participant_options_can_be_narrowed_further_by_modal_target_filters(): void
    {
        $assignment = AssessmentAssignment::query()->create([
            'kode_penugasan' => 'TGS-OPT-002',
            'judul_penugasan' => 'Penugasan Filter Modal',
            'target_ketenagaan' => 'tenaga_pendidik',
            'target_jabatan' => ['Guru', 'Kepala Sekolah'],
            'target_kabupaten' => ['Kota Makassar', 'Kabupaten Gowa'],
            'target_satuan_pendidikan' => [
                AssessmentSchoolTargetKey::encode('Kota Makassar', 'SD Negeri 1 Makassar'),
                AssessmentSchoolTargetKey::encode('Kabupaten Gowa', 'SMP Negeri 1 Gowa'),
            ],
            'status_distribusi' => 'selesai',
            'total_target' => 0,
            'total_ditugaskan' => 0,
            'durasi_sesi_jam' => 3,
        ]);

        $this->createGuru([
            'nama_lengkap' => 'Guru Makassar',
            'email' => 'guru.makassar@example.test',
            'satuan_pendidikan' => 'SD Negeri 1 Makassar',
            'kabupaten' => 'Kota Makassar',
            'jenis_jabatan' => 'Guru',
        ]);

        $matchingGuru = $this->createGuru([
            'nama_lengkap' => 'Kepala Sekolah Gowa',
            'email' => 'kepsek.gowa@example.test',
            'satuan_pendidikan' => 'SMP Negeri 1 Gowa',
            'kabupaten' => 'Kabupaten Gowa',
            'jenis_jabatan' => 'Kepala Sekolah',
        ]);

        $filteredOutBySchool = $this->createGuru([
            'nama_lengkap' => 'Kepala Sekolah Gowa Sekolah Lain',
            'email' => 'kepsek.gowa.lain@example.test',
            'satuan_pendidikan' => 'SMP Negeri 2 Gowa',
            'kabupaten' => 'Kabupaten Gowa',
            'jenis_jabatan' => 'Kepala Sekolah',
        ]);

        $response = $this
            ->withSession([
                'cek' => true,
                'role' => 'admin',
            ])
            ->getJson(
                route('assessment.assignment.add-participants-options', ['id' => $assignment->id]).'?'.http_build_query([
                    'target_jabatan' => ['Kepala Sekolah'],
                    'target_kabupaten' => ['Kabupaten Gowa'],
                    'target_satuan_pendidikan' => [
                        AssessmentSchoolTargetKey::encode('Kabupaten Gowa', 'SMP Negeri 1 Gowa'),
                    ],
                ])
            );

        $response->assertOk();
        $response->assertJsonCount(1, 'items');
        $response->assertJsonPath('items.0.id', (string) $matchingGuru->id);
        $response->assertJsonMissing(['id' => (string) $filteredOutBySchool->id]);
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
