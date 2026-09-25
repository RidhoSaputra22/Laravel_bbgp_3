<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use Tests\TestCase;

class SekolahRegistrationTest extends TestCase
{
    private const HEAD_PASSWORD = '12345';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createTables();
    }

    protected function tearDown(): void
    {
        foreach (['sekolahs', 'gurus', 'admins', 'users'] as $table) {
            Schema::connection('sqlite')->dropIfExists($table);
        }

        parent::tearDown();
    }

    public function test_school_form_displays_the_expected_inputs(): void
    {
        $response = $this->get(route('user.data-sekolah'));

        $response->assertOk();

        foreach (array_keys($this->schoolData()) as $field) {
            $name = $field === 'fasilitas_it' ? 'fasilitas_it[]' : $field;
            $response->assertSee('name="'.$name.'"', false);
        }
    }

    public function test_school_registration_rejects_missing_required_fields(): void
    {
        $requiredFields = [
            'nama_sekolah',
            'npsn_sekolah',
            'bp_sekolah',
            'status_sekolah',
            'akreditasi',
            'alamat',
            'provinsi',
            'kabupaten',
            'kecamatan',
            'no_telepon',
            'nama_kepsek',
            'asn_opsi',
            'no_telp_kepsek',
            'jumlah_guru',
            'jumlah_guru_pns',
            'jumlah_honorer',
            'jumlah_kependidikan',
            'jumlah_siswa',
            'jumlah_siswa_pria',
            'jumlah_siswa_perempuan',
            'jumlah_kelas',
            'laboratorium',
            'perpustakaan',
            'ruang_guru',
            'jumlah_toilet',
            'lapangan_olahraga',
            'akses_internet',
            'jam_belajar',
        ];

        $response = $this
            ->from(route('user.data-sekolah'))
            ->post(route('user.store.data-sekolah'), []);

        $response
            ->assertRedirect(route('user.data-sekolah'))
            ->assertSessionHasErrors($requiredFields);
    }

    public function test_school_registration_rejects_invalid_field_values(): void
    {
        $data = array_merge($this->schoolData(), [
            'npsn_sekolah' => 'bukan-angka',
            'bp_sekolah' => 'MA',
            'status_sekolah' => 'Publik',
            'akreditasi' => 'D',
            'tahun_berdiri' => date('Y') + 1,
            'jumlah_guru' => -1,
            'jumlah_toilet' => -1,
            'fasilitas_it' => 'bukan-array',
            'ekstrakurikuler' => [],
            'program_unggulan' => [],
        ]);

        $response = $this
            ->from(route('user.data-sekolah'))
            ->post(route('user.store.data-sekolah'), $data);

        $response
            ->assertRedirect(route('user.data-sekolah'))
            ->assertSessionHasErrors([
                'npsn_sekolah',
                'bp_sekolah',
                'status_sekolah',
                'akreditasi',
                'tahun_berdiri',
                'jumlah_guru',
                'jumlah_toilet',
                'fasilitas_it',
                'ekstrakurikuler',
                'program_unggulan',
            ]);
    }

    public function test_created_school_head_account_can_log_in(): void
    {
        $data = $this->schoolData();

        $response = $this->post(route('user.store.data-sekolah'), $data);

        $response
            ->assertRedirect(route('user.index'))
            ->assertSessionHas('message', 'sukses daftar sekolah')
            ->assertSessionMissing('registration_credentials');

        $user = User::where('no_ktp', $data['nik_kepsek'])->firstOrFail();

        $this->assertDatabaseHas('sekolahs', [
            'nama_sekolah' => $data['nama_sekolah'],
            'npsn_sekolah' => $data['npsn_sekolah'],
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('gurus', [
            'nama_lengkap' => $data['nama_kepsek'],
            'no_ktp' => $data['nik_kepsek'],
        ]);

        $this->assertDatabaseHas('admins', [
            'name' => $data['nama_kepsek'],
            'no_ktp' => $data['nik_kepsek'],
            'role' => 'tenaga kependidikan',
        ]);

        $this->assertSame('tenaga kependidikan', $user->role);
        $this->assertTrue(Hash::check(self::HEAD_PASSWORD, $user->password));

        $loginResponse = $this->post(route('login_action'), [
            'nik' => $data['nik_kepsek'],
            'password' => self::HEAD_PASSWORD,
            'role' => 'tenaga kependidikan',
        ]);

        $loginResponse
            ->assertRedirect(route('guru.show', $data['nik_kepsek']))
            ->assertSessionHas('message', 'sukses login');

        $this->assertAuthenticatedAs($user);
    }

    private function schoolData(): array
    {
        return [
            'nama_sekolah' => 'SMA Negeri Pengujian',
            'npsn_sekolah' => '98765432',
            'bp_sekolah' => 'SMA/SMK',
            'status_sekolah' => 'Negeri',
            'akreditasi' => 'A',
            'alamat' => 'Jl. Pengujian No. 1, Makassar',
            'provinsi' => 'Sulawesi Selatan',
            'kabupaten' => 'Kota Makassar',
            'kecamatan' => 'Panakkukang',
            'no_telepon' => '0411123456',
            'email' => 'sekolah@example.test',
            'website_url' => 'https://sekolah.example.test',
            'tahun_berdiri' => 2000,
            'koordinat' => '-5.147665,119.432732',
            'nama_kepsek' => 'Budi Santoso',
            'asn_opsi' => 'tidak',
            'nip_kepsek' => '',
            'nik_kepsek' => '7371010101010001',
            'no_sk' => 'SK-001/2026',
            'no_telp_kepsek' => '081234567890',
            'email_kepsek' => 'kepsek@example.test',
            'jumlah_guru' => 20,
            'jumlah_guru_pns' => 10,
            'jumlah_honorer' => 10,
            'jumlah_kependidikan' => 8,
            'bidang_studi' => 'Bahasa dan Sains',
            'jumlah_siswa' => 300,
            'jumlah_siswa_pria' => 150,
            'jumlah_siswa_perempuan' => 150,
            'jumlah_siswa_per_kelas' => '30',
            'jumlah_kelas' => 10,
            'laboratorium' => 'komputer',
            'perpustakaan' => 'ada',
            'ruang_guru' => 'ada',
            'jumlah_toilet' => 8,
            'lapangan_olahraga' => 'ada',
            'fasilitas_it' => ['komputer', 'internet'],
            'fasilitas_it_tambahan' => 'Smart TV',
            'akses_internet' => 'ada',
            'jam_belajar' => 'pagi',
            'ekstrakurikuler' => 'Pramuka dan olahraga',
            'program_unggulan' => 'Sekolah digital',
        ];
    }

    private function createTables(): void
    {
        Schema::connection('sqlite')->create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('username');
            $table->string('no_ktp')->nullable();
            $table->string('password')->nullable();
            $table->string('role');
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('admins', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('username');
            $table->string('no_ktp')->nullable();
            $table->string('password')->nullable();
            $table->string('role');
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('gurus', function (Blueprint $table): void {
            $table->id();
            $table->string('nama_lengkap')->nullable();
            $table->string('email')->nullable();
            $table->string('no_ktp')->nullable();
            $table->string('nip')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('no_wa')->nullable();
            $table->string('is_verif')->nullable();
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('sekolahs', function (Blueprint $table): void {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->string('nama_sekolah');
            $table->string('npsn_sekolah');
            $table->string('bp_sekolah');
            $table->string('status_sekolah');
            $table->string('provinsi');
            $table->string('kabupaten');
            $table->string('kecamatan');
            $table->text('alamat');
            $table->string('akreditasi');
            $table->string('no_telepon');
            $table->string('email')->nullable();
            $table->string('website_url')->nullable();
            $table->string('tahun_berdiri')->nullable();
            $table->string('koordinat')->nullable();
            $table->string('nama_kepsek');
            $table->string('asn_opsi');
            $table->string('nip_kepsek')->nullable();
            $table->string('no_sk')->nullable();
            $table->string('no_telp_kepsek');
            $table->string('email_kepsek')->nullable();
            $table->integer('jumlah_guru');
            $table->integer('jumlah_guru_pns');
            $table->integer('jumlah_honorer');
            $table->integer('jumlah_kependidikan');
            $table->text('bidang_studi')->nullable();
            $table->integer('jumlah_siswa');
            $table->integer('jumlah_siswa_pria');
            $table->integer('jumlah_siswa_perempuan');
            $table->text('jumlah_siswa_per_kelas')->nullable();
            $table->integer('jumlah_kelas');
            $table->string('laboratorium');
            $table->string('perpustakaan');
            $table->string('ruang_guru');
            $table->integer('jumlah_toilet');
            $table->string('lapangan_olahraga');
            $table->json('fasilitas_it')->nullable();
            $table->string('akses_internet');
            $table->string('jam_belajar');
            $table->text('ekstrakurikuler')->nullable();
            $table->text('program_unggulan')->nullable();
            $table->timestamps();
        });
    }
}
