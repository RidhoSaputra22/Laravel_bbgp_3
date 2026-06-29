<?php

namespace Tests\Feature;

use App\Models\Guru;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AssessmentAssignmentGuruOptionsFilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

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

        parent::tearDown();
    }

    public function test_guru_options_can_be_filtered_by_ketenagaan_and_jabatan(): void
    {
        $guruPendidik = $this->createGuru([
            'nama_lengkap' => 'Guru Pendidik',
            'email' => 'guru.pendidik@example.test',
            'eksternal_jabatan' => 'Tenaga Pendidik',
            'jenis_jabatan' => 'Guru',
        ]);

        $this->createGuru([
            'nama_lengkap' => 'Pengawas Sekolah',
            'email' => 'pengawas@example.test',
            'eksternal_jabatan' => 'Tenaga Kependidikan',
            'jenis_jabatan' => 'Pengawas',
        ]);

        $response = $this
            ->withSession([
                'cek' => true,
                'role' => 'admin',
            ])
            ->getJson(route('assessment.assignment.guru-options', [
                'q' => 'Guru Pendidik',
                'eksternal_jabatan' => 'Tenaga Pendidik',
                'jenis_jabatan' => 'Guru',
            ]));

        $response->assertOk();
        $response->assertJsonCount(1, 'items');
        $response->assertJsonPath('items.0.id', (string) $guruPendidik->id);
        $response->assertJsonPath('items.0.payload.eksternal_jabatan', 'Tenaga Pendidik');
        $response->assertJsonPath('items.0.payload.jenis_jabatan', 'Guru');
    }

    public function test_guru_options_search_matches_ketenagaan_and_jabatan_columns(): void
    {
        $stakeholder = $this->createGuru([
            'nama_lengkap' => 'Stakeholder Sulsel',
            'email' => 'stakeholder@example.test',
            'eksternal_jabatan' => 'Stakeholder',
            'jenis_jabatan' => 'Kepala Dinas',
        ]);

        $this->createGuru([
            'nama_lengkap' => 'Guru Makassar',
            'email' => 'guru.makassar@example.test',
            'eksternal_jabatan' => 'Tenaga Pendidik',
            'jenis_jabatan' => 'Guru',
        ]);

        $response = $this
            ->withSession([
                'cek' => true,
                'role' => 'admin',
            ])
            ->getJson(route('assessment.assignment.guru-options', [
                'q' => 'Kepala Dinas',
            ]));

        $response->assertOk();
        $response->assertJsonCount(1, 'items');
        $response->assertJsonPath('items.0.id', (string) $stakeholder->id);
        $response->assertJsonPath('items.0.payload.eksternal_jabatan', 'Stakeholder');
        $response->assertJsonPath('items.0.payload.jenis_jabatan', 'Kepala Dinas');
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
