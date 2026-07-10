<?php

namespace Tests\Feature;

use App\Support\Assessment\AssessmentSchoolTargetKey;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AssessmentAssignmentStoreValidationTest extends TestCase
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

        Schema::connection('sqlite')->create('assessment_combinations', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kombinasi')->nullable();
            $table->string('judul')->nullable();
            $table->string('target_ketenagaan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::connection('sqlite')->dropIfExists('assessment_combinations');
        Schema::connection('sqlite')->dropIfExists('gurus');
        Schema::connection('sqlite')->dropIfExists('assessments');

        parent::tearDown();
    }

    public function test_store_rejects_kabupaten_that_do_not_match_selected_ketenagaan_and_jabatan(): void
    {
        DB::table('assessments')->insert([
            'kode_assessment' => 'ASM-001',
            'judul' => 'Assessment Monitoring',
            'status' => 'publish',
            'target_ketenagaan' => 'tenaga_pendidik',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('gurus')->insert([
            'nama_lengkap' => 'Guru Makassar',
            'email' => 'guru.makassar@example.test',
            'satuan_pendidikan' => 'SD Negeri 1 Makassar',
            'kabupaten' => 'Kota Makassar',
            'eksternal_jabatan' => 'Tenaga Pendidik',
            'jenis_jabatan' => 'Guru',
            'status_kepegawaian' => 'ASN',
            'is_verif' => 'sudah',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('assessment_combinations')->insert([
            'kode_kombinasi' => 'KMB-001',
            'judul' => 'Kombinasi Pendidik',
            'target_ketenagaan' => 'tenaga_pendidik',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this
            ->from(route('assessment.assignment.create'))
            ->withSession([
                'cek' => true,
                'role' => 'admin',
            ])
            ->post(route('assessment.assignment.store'), [
                'judul_penugasan' => 'Penugasan Tidak Valid',
                'target_ketenagaan' => 'tenaga_pendidik',
                'target_jabatan' => ['Guru'],
                'target_kabupaten' => ['Kabupaten Gowa'],
                'target_satuan_pendidikan' => [
                    AssessmentSchoolTargetKey::encode('Kabupaten Gowa', 'SD Negeri 1 Gowa'),
                ],
                'durasi_sesi_jam' => 3,
            ]);

        $response->assertRedirect(route('assessment.assignment.create'));
        $response->assertSessionHasErrors('target_kabupaten');
    }

    public function test_store_rejects_penugasan_when_no_combination_is_available_for_target_ketenagaan(): void
    {
        DB::table('assessments')->insert([
            'kode_assessment' => 'ASM-002',
            'judul' => 'Assessment Draft',
            'status' => 'draft',
            'target_ketenagaan' => 'tenaga_pendidik',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('gurus')->insert([
            'nama_lengkap' => 'Guru Makassar',
            'email' => 'guru.makassar@example.test',
            'satuan_pendidikan' => 'SD Negeri 1 Makassar',
            'kabupaten' => 'Kota Makassar',
            'eksternal_jabatan' => 'Tenaga Pendidik',
            'jenis_jabatan' => 'Guru',
            'status_kepegawaian' => 'ASN',
            'is_verif' => 'sudah',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this
            ->from(route('assessment.assignment.create'))
            ->withSession([
                'cek' => true,
                'role' => 'admin',
            ])
            ->post(route('assessment.assignment.store'), [
                'judul_penugasan' => 'Penugasan Draft',
                'target_ketenagaan' => 'tenaga_pendidik',
                'target_jabatan' => ['Guru'],
                'target_kabupaten' => ['Kota Makassar'],
                'target_satuan_pendidikan' => [
                    AssessmentSchoolTargetKey::encode('Kota Makassar', 'SD Negeri 1 Makassar'),
                ],
                'durasi_sesi_jam' => 3,
            ]);

        $response->assertRedirect(route('assessment.assignment.create'));
        $response->assertSessionHasErrors('target_ketenagaan');
    }

    public function test_store_rejects_satuan_pendidikan_that_do_not_match_selected_scope(): void
    {
        DB::table('assessments')->insert([
            'kode_assessment' => 'ASM-003',
            'judul' => 'Assessment Sekolah',
            'status' => 'publish',
            'target_ketenagaan' => 'tenaga_pendidik',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('gurus')->insert([
            'nama_lengkap' => 'Guru Makassar',
            'email' => 'guru.makassar@example.test',
            'satuan_pendidikan' => 'SD Negeri 1 Makassar',
            'kabupaten' => 'Kota Makassar',
            'eksternal_jabatan' => 'Tenaga Pendidik',
            'jenis_jabatan' => 'Guru',
            'status_kepegawaian' => 'ASN',
            'is_verif' => 'sudah',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('assessment_combinations')->insert([
            'kode_kombinasi' => 'KMB-003',
            'judul' => 'Kombinasi Sekolah',
            'target_ketenagaan' => 'tenaga_pendidik',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this
            ->from(route('assessment.assignment.create'))
            ->withSession([
                'cek' => true,
                'role' => 'admin',
            ])
            ->post(route('assessment.assignment.store'), [
                'judul_penugasan' => 'Penugasan Sekolah Tidak Valid',
                'target_ketenagaan' => 'tenaga_pendidik',
                'target_jabatan' => ['Guru'],
                'target_kabupaten' => ['Kota Makassar'],
                'target_satuan_pendidikan' => [
                    AssessmentSchoolTargetKey::encode('Kota Makassar', 'SD Negeri 99 Makassar'),
                ],
                'durasi_sesi_jam' => 3,
            ]);

        $response->assertRedirect(route('assessment.assignment.create'));
        $response->assertSessionHasErrors('target_satuan_pendidikan');
    }
}
