<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Agenda;
use App\Models\Berkas;
use App\Models\Guru;
use App\Models\Kegiatan;
use App\Models\Kepegawaian;
use App\Models\Pegawai;
use App\Models\PenyewaanRuangan;
use App\Models\Rtl;
use App\Models\SatuanPendidikan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCoreFeaturesTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::query()->create([
            'name' => 'Admin Feature Test',
            'username' => 'admin-feature-'.uniqid(),
            'password' => 'password',
            'role' => 'admin',
        ]);

        $this->actingAs($this->admin);
    }

    private function adminSession(array $extra = []): array
    {
        return array_merge([
            'cek' => true,
            'role' => 'admin',
            'user_id' => $this->admin->id,
            'name' => $this->admin->name,
        ], $extra);
    }

    public function test_guest_and_non_admin_cannot_open_admin_dashboard(): void
    {
        auth()->logout();

        $this->get(route('dashboard'))->assertRedirect(route('login'));

        $pegawai = User::query()->create([
            'name' => 'Pegawai Feature Test',
            'username' => 'pegawai-feature-'.uniqid(),
            'password' => 'password',
            'role' => 'pegawai',
        ]);

        $this->actingAs($pegawai)
            ->withSession(['role' => 'pegawai', 'cek' => true])
            ->get(route('dashboard'))
            ->assertForbidden();
    }

    public function test_admin_profile_is_owner_scoped_and_updates_credentials(): void
    {
        Admin::query()->create([
            'name' => 'Admin Profile Test',
            'username' => $this->admin->username,
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $this->withSession($this->adminSession())
            ->get(route('profile.index', $this->admin->id))
            ->assertOk();

        $newUsername = 'admin-profile-updated-'.uniqid();
        $this->withSession($this->adminSession())
            ->put(route('profile.update'), [
                'id' => Admin::query()->where('username', $this->admin->username)->value('id'),
                'name' => 'Admin Profile Updated',
                'username' => $newUsername,
            ])
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('admins', [
            'username' => $newUsername,
            'name' => 'Admin Profile Updated',
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'username' => $newUsername,
        ]);
    }

    public function test_kepegawaian_crud_and_validation(): void
    {
        $this->withSession($this->adminSession())
            ->post(route('kepegawaian.store'), [])
            ->assertSessionHasErrors(['name']);

        $name = 'ASN Test '.uniqid();
        $this->withSession($this->adminSession())
            ->post(route('kepegawaian.store'), ['name' => $name])
            ->assertRedirect(route('kepegawaian.index'));

        $data = Kepegawaian::query()->where('name', $name)->firstOrFail();

        $this->withSession($this->adminSession())
            ->put(route('kepegawaian.update'), ['id' => $data->id, 'name' => $name.' Updated'])
            ->assertRedirect(route('kepegawaian.index'));

        $this->assertDatabaseHas('kepegawaians', ['id' => $data->id, 'name' => $name.' Updated']);

        $this->withSession($this->adminSession())
            ->post(route('kepegawaian.hapus', $data->id))
            ->assertOk()
            ->assertJsonPath('id', $data->id);

        $this->assertDatabaseMissing('kepegawaians', ['id' => $data->id]);
    }

    public function test_kependidikan_crud_and_validation(): void
    {
        $this->withSession($this->adminSession())
            ->post(route('kependidikan.store'), ['name' => str_repeat('x', 256)])
            ->assertSessionHasErrors(['name']);

        $name = 'Satuan Test '.uniqid();
        $this->withSession($this->adminSession())
            ->post(route('kependidikan.store'), ['name' => $name])
            ->assertRedirect(route('kependidikan.index'));

        $data = SatuanPendidikan::query()->where('name', $name)->firstOrFail();

        $this->withSession($this->adminSession())
            ->put(route('kependidikan.update'), ['id' => $data->id, 'name' => $name.' Updated'])
            ->assertRedirect(route('kependidikan.index'));

        $this->withSession($this->adminSession())
            ->post(route('kependidikan.hapus', $data->id))
            ->assertOk()
            ->assertJsonPath('id', $data->id);
    }

    public function test_room_rental_crud_validates_enum_and_numeric_inputs(): void
    {
        $this->withSession($this->adminSession())
            ->post(route('penyewaan.store'), [
                'tipe_ruangan' => 'gudang',
                'nama_ruangan' => '',
                'harga_per_malam' => -1,
                'status' => 'aktif',
            ])
            ->assertSessionHasErrors(['tipe_ruangan', 'nama_ruangan', 'harga_per_malam', 'status']);

        Storage::fake('public');
        $name = 'Aula Test '.uniqid();

        $this->withSession($this->adminSession())
            ->post(route('penyewaan.store'), [
                'tipe_ruangan' => 'aula',
                'nama_ruangan' => $name,
                'harga_per_malam' => '125000.50',
                'rincian_harga' => 'Harga untuk satu malam.',
                'status' => 'tersedia',
                'is_active' => '1',
            ])
            ->assertRedirect(route('penyewaan.index'));

        $room = PenyewaanRuangan::query()->where('nama_ruangan', $name)->firstOrFail();
        $this->assertSame('aula', $room->tipe_ruangan);
        $this->assertSame(1, (int) $room->is_active);

        $this->withSession($this->adminSession())
            ->put(route('penyewaan.update', $room->id), [
                'tipe_ruangan' => 'kelas',
                'nama_ruangan' => $name.' Updated',
                'harga_per_malam' => '0',
                'status' => 'maintenance',
            ])
            ->assertRedirect(route('penyewaan.index'));

        $this->assertDatabaseHas('penyewaan_ruangans', [
            'id' => $room->id,
            'tipe_ruangan' => 'kelas',
            'status' => 'maintenance',
        ]);

        $this->withSession($this->adminSession())
            ->post(route('penyewaan.hapus', $room->id))
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_activity_input_is_split_into_date_and_time_columns(): void
    {
        $name = 'Kegiatan Test '.uniqid();
        $payload = [
            'nama_kegiatan' => $name,
            'tempat_kegiatan' => 'Makassar',
            'mulai_kegiatan' => '2026-10-01 08:00',
            'selesai_kegiatan' => '2026-10-02 16:30',
            'deskripsi_kegiatan' => 'Kegiatan feature test.',
            'status' => 'true',
        ];

        $this->withSession($this->adminSession())
            ->post(route('kegiatan.store'), $payload)
            ->assertRedirect(route('kegiatan.index'));

        $activity = Kegiatan::query()->where('nama_kegiatan', $name)->firstOrFail();
        $this->assertSame('2026-10-01', (string) $activity->tgl_kegiatan);
        $this->assertSame('08:00:00', (string) $activity->jam_mulai);
        $this->assertSame('2026-10-02', (string) $activity->tgl_selesai);

        $this->withSession($this->adminSession())
            ->put(route('kegiatan.update'), $payload + [
                'id' => $activity->id,
                'nama_kegiatan' => $name.' Updated',
            ])
            ->assertRedirect(route('kegiatan.index'));

        $this->withSession($this->adminSession())
            ->post(route('kegiatan.hapus', $activity->id))
            ->assertOk()
            ->assertJsonPath('id', $activity->id);
    }

    public function test_content_modules_require_safe_image_uploads_and_existing_ids(): void
    {
        foreach (['berita.store', 'artikel.store', 'agenda.store'] as $routeName) {
            $this->withSession($this->adminSession())
                ->post(route($routeName), [])
                ->assertSessionHasErrors(['thumbnail']);
        }

        $this->withSession($this->adminSession())
            ->post(route('berita.store'), [
                'thumbnail' => UploadedFile::fake()->create('invalid.pdf', 10, 'application/pdf'),
            ])
            ->assertSessionHasErrors(['thumbnail']);

        $this->withSession($this->adminSession())
            ->put(route('agenda.update'), ['id' => PHP_INT_MAX])
            ->assertSessionHasErrors(['id']);
    }

    public function test_account_store_and_auto_registration_validate_and_persist_both_guards(): void
    {
        $this->withSession($this->adminSession())
            ->post(route('akun.store'), [])
            ->assertSessionHasErrors(['name', 'username', 'role', 'password']);

        $username = 'akun-feature-'.uniqid();
        $this->withSession($this->adminSession())
            ->post(route('akun.store'), [
                'name' => 'Akun Feature',
                'username' => $username,
                'role' => 'pegawai',
                'password' => 'password123',
            ])
            ->assertRedirect(route('akun.index'));

        $this->assertDatabaseHas('admins', ['username' => $username, 'role' => 'pegawai']);
        $this->assertDatabaseHas('users', ['username' => $username, 'role' => 'pegawai']);

        $this->withSession($this->adminSession())
            ->post(route('akun.regis'), [
                'name' => 'Invalid Role',
                'username' => 'invalid-role',
                'role' => 'role-tidak-valid',
            ])
            ->assertStatus(422);

        $this->withSession($this->adminSession())
            ->post(route('akun.regis'), [
                'name' => 'Auto Registered',
                'username' => 'Auto Registered',
                'role' => 'pegawai',
            ])
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.username', 'autoregistered');

        $this->assertDatabaseHas('admins', ['username' => 'autoregistered', 'role' => 'pegawai']);
    }

    public function test_external_and_employee_records_can_be_created_verified_and_deleted(): void
    {
        $guruKtp = '91'.str_pad((string) random_int(1, 99999999999999), 14, '0', STR_PAD_LEFT);
        $this->withSession($this->adminSession())
            ->post(route('guru.store'), [
                'nama_lengkap' => 'Guru Feature Test',
                'no_ktp' => $guruKtp,
                'jenisJabatan' => 'Tenaga Pendidik',
                'jabJenis' => 'Guru',
                'jabKategori' => 'GP (Guru Penggerak)',
                'jabTugas' => 'GP (Guru Penggerak)',
                'gender' => 'Laki-laki',
                'status' => 'Belum Kawin',
                'agama' => 'Islam',
            ])
            ->assertRedirect(route('guru.index'));

        $guru = Guru::query()->where('no_ktp', $guruKtp)->firstOrFail();
        $this->assertSame('belum', $guru->is_verif);

        $this->withSession($this->adminSession())
            ->post(route('guru.verifikasi', $guru->id))
            ->assertOk()
            ->assertJsonPath('status.is_verif', 'sudah');

        $pegawaiKtp = '92'.str_pad((string) random_int(1, 99999999999999), 14, '0', STR_PAD_LEFT);
        $this->withSession($this->adminSession())
            ->post(route('pegawai.store'), [
                'nama_lengkap' => 'Pegawai Feature Test',
                'no_ktp' => $pegawaiKtp,
                'jenis_pegawai' => 'PPNPN',
                'gender' => 'Perempuan',
                'agama' => 'Islam',
            ])
            ->assertRedirect(route('pegawai.index'));

        $pegawai = Pegawai::query()->where('no_ktp', $pegawaiKtp)->firstOrFail();
        $this->withSession($this->adminSession())
            ->post(route('pegawai.verifikasi', $pegawai->id))
            ->assertOk()
            ->assertJsonPath('status.is_verif', 'sudah');

        $this->withSession($this->adminSession())
            ->post(route('guru.hapus', $guru->id))
            ->assertOk();
        $this->withSession($this->adminSession())
            ->post(route('pegawai.hapus', $pegawai->id))
            ->assertOk();
    }

    public function test_report_file_input_requires_link_or_supported_file_and_admin_can_verify(): void
    {
        $this->withSession($this->adminSession(['no_ktp' => '9988776655443322']))
            ->post(route('berkas.store'), [
                'nama_kegiatan' => 'Kegiatan Berkas',
                'metode_upload' => 'link',
            ])
            ->assertStatus(500);

        $this->withSession($this->adminSession(['no_ktp' => '9988776655443322']))
            ->post(route('berkas.store'), [
                'nama_link' => 'https://example.test/laporan',
                'nama_kegiatan' => 'Kegiatan Berkas',
                'metode_upload' => 'link',
            ])
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $berkas = Berkas::query()->where('nik', '9988776655443322')->latest('id')->firstOrFail();
        $this->withSession($this->adminSession())
            ->post(route('berkas.verify', $berkas->id))
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('berkas', ['id' => $berkas->id, 'status' => 'selesai']);
    }

    public function test_rtl_admin_status_update_validates_allowed_statuses(): void
    {
        $activity = Kegiatan::query()->create([
            'nama_kegiatan' => 'RTL Activity '.uniqid(),
            'tempat_kegiatan' => 'Makassar',
            'tgl_kegiatan' => '2026-10-01',
            'tgl_selesai' => '2026-10-01',
            'jam_mulai' => '08:00',
            'jam_selesai' => '16:00',
            'status' => 'true',
        ]);
        $rtl = Rtl::query()->create([
            'no_ktp' => '8877665544332211',
            'id_kegiatan' => $activity->id,
            'status' => 'pending',
        ]);

        $this->withSession($this->adminSession())
            ->post(route('rtl.update', $rtl->id), ['status' => 'invalid'])
            ->assertSessionHasErrors(['status']);

        $this->withSession($this->adminSession())
            ->post(route('rtl.update', $rtl->id), [
                'status' => 'approved',
                'admin_notes' => 'Dokumen telah diperiksa.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('rtls', [
            'id' => $rtl->id,
            'status' => 'approved',
            'admin_notes' => 'Dokumen telah diperiksa.',
        ]);
    }
}
