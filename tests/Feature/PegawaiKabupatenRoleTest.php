<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Guru;
use App\Models\Kabupaten;
use App\Models\Kegiatan;
use App\Models\PesertaKegiatan;
use App\Models\User;
use Database\Seeders\PegawaiKabupatenSeeder;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PegawaiKabupatenRoleTest extends TestCase
{
    private const OWN_COUNTY = 'Kabupaten Soppeng';
    private const OTHER_COUNTY = 'Kabupaten Gowa';

    public function test_seeder_creates_county_credentials_for_each_county(): void
    {
        Kabupaten::query()->create(['name' => self::OWN_COUNTY]);
        Kabupaten::query()->create(['name' => self::OTHER_COUNTY]);

        $this->seed(PegawaiKabupatenSeeder::class);

        $users = User::query()->where('role', 'pegawai-kabupaten')->orderBy('kabupaten')->get();

        $this->assertCount(2, $users);
        $this->assertSame([
            self::OTHER_COUNTY,
            self::OWN_COUNTY,
        ], $users->pluck('kabupaten')->all());

        foreach ($users as $user) {
            $this->assertMatchesRegularExpression(
                '/^' . strtoupper(str_replace(' ', '-', $user->kabupaten)) . '-[A-Z]{5}$/',
                $user->username
            );
            $this->assertTrue(Hash::check('12345', $user->password));
            $this->assertDatabaseHas('admins', [
                'username' => $user->username,
                'role' => 'pegawai-kabupaten',
            ]);
        }

        $this->seed(PegawaiKabupatenSeeder::class);

        $this->assertSame(2, User::query()->where('role', 'pegawai-kabupaten')->count());
        $this->assertSame(2, Admin::query()->where('role', 'pegawai-kabupaten')->count());
    }

    public function test_admin_can_assign_county_and_other_roles_are_stored_without_county(): void
    {
        Kabupaten::query()->create(['name' => self::OWN_COUNTY]);
        $admin = $this->makeUser('admin', 'admin-account-test');

        $countyUsername = 'soppeng-account-' . uniqid();
        $this->actingAs($admin)->withSession($this->adminSession($admin))
            ->post(route('akun.store'), [
                'name' => 'Pegawai Soppeng',
                'username' => $countyUsername,
                'role' => 'pegawai-kabupaten',
                'password' => 'password123',
                'kabupaten' => self::OWN_COUNTY,
            ])
            ->assertRedirect(route('akun.index'));

        $this->assertDatabaseHas('users', [
            'username' => $countyUsername,
            'role' => 'pegawai-kabupaten',
            'kabupaten' => self::OWN_COUNTY,
        ]);

        $otherUsername = 'pegawai-account-test-' . uniqid();
        $this->actingAs($admin)->withSession($this->adminSession($admin))
            ->post(route('akun.store'), [
                'name' => 'Pegawai BBGTK',
                'username' => $otherUsername,
                'role' => 'pegawai',
                'password' => 'password123',
                'kabupaten' => self::OWN_COUNTY,
            ])
            ->assertRedirect(route('akun.index'));

        $this->assertDatabaseHas('users', [
            'username' => $otherUsername,
            'role' => 'pegawai',
            'kabupaten' => null,
        ]);

        $countyAdmin = Admin::query()->where('username', $countyUsername)->firstOrFail();
        $this->actingAs($admin)->withSession($this->adminSession($admin))
            ->get(route('akun.edit', $countyAdmin->id))
            ->assertOk()
            ->assertViewHas('selectedKabupaten', self::OWN_COUNTY);
    }

    public function test_county_login_and_menu_are_limited_to_allowed_modules(): void
    {
        $countyUser = $this->makeUser('pegawai-kabupaten', 'soppeng-login', self::OWN_COUNTY);
        $countyUser->update(['password' => '12345']);

        $this->post(route('login_action_admin'), [
            'username' => $countyUser->username,
            'password' => '12345',
        ])->assertRedirect(route('kegiatan.index'));

        $this->assertAuthenticatedAs($countyUser);
        $this->assertSame(self::OWN_COUNTY, session('kabupaten'));

        $this->get(route('dashboard'))->assertForbidden();
        $this->get(route('akun.index'))->assertForbidden();
        $this->get(route('guru.index'))->assertOk();
        $this->get(route('kegiatan.index'))->assertOk();
        $this->get(route('peserta.index'))->assertOk();
    }

    public function test_county_activity_and_external_crud_cannot_cross_county(): void
    {
        $countyUser = $this->makeUser('pegawai-kabupaten', 'soppeng-crud', self::OWN_COUNTY);
        $ownActivity = $this->makeActivity('Kegiatan Soppeng', self::OWN_COUNTY);
        $otherActivity = $this->makeActivity('Kegiatan Gowa', self::OTHER_COUNTY);
        $ownGuru = $this->makeGuru('Guru Soppeng', self::OWN_COUNTY);
        $otherGuru = $this->makeGuru('Guru Gowa', self::OTHER_COUNTY);

        $this->actingAs($countyUser)->withSession($this->countySession($countyUser));

        $this->get(route('kegiatan.index'))
            ->assertOk()
            ->assertViewHas('datas', function ($activities) use ($ownActivity, $otherActivity): bool {
                return $activities->pluck('id')->all() === [$ownActivity->id]
                    && ! $activities->contains('id', $otherActivity->id);
            });

        $createdActivityName = 'Kegiatan Baru Soppeng ' . uniqid();
        $this->post(route('kegiatan.store'), [
            'nama_kegiatan' => $createdActivityName,
            'tempat_kegiatan' => 'Soppeng',
            'mulai_kegiatan' => '2026-10-01 08:00',
            'selesai_kegiatan' => '2026-10-01 16:00',
            'deskripsi_kegiatan' => 'Kegiatan kabupaten.',
            'kabupaten' => self::OTHER_COUNTY,
            'status' => 'true',
        ])->assertRedirect(route('kegiatan.index'));

        $createdActivity = Kegiatan::query()->where('nama_kegiatan', $createdActivityName)->firstOrFail();
        $this->assertSame(self::OWN_COUNTY, $createdActivity->kabupaten);

        $this->put(route('kegiatan.update'), [
            'id' => $ownActivity->id,
            'nama_kegiatan' => 'Kegiatan Soppeng Diubah',
            'tempat_kegiatan' => 'Soppeng',
            'mulai_kegiatan' => '2026-10-02 08:00',
            'selesai_kegiatan' => '2026-10-02 16:00',
            'deskripsi_kegiatan' => 'Kegiatan diperbarui.',
            'kabupaten' => self::OTHER_COUNTY,
            'status' => 'true',
        ])->assertRedirect(route('kegiatan.index'));

        $this->assertDatabaseHas('kegiatans', [
            'id' => $ownActivity->id,
            'kabupaten' => self::OWN_COUNTY,
            'nama_kegiatan' => 'Kegiatan Soppeng Diubah',
        ]);
        $this->get(route('kegiatan.edit', $otherActivity->id))->assertNotFound();
        $this->post(route('kegiatan.hapus', $otherActivity->id))->assertNotFound();

        $this->get(route('guru.cari', ['draw' => 1, 'start' => 0, 'length' => 10]))
            ->assertOk()
            ->assertJsonPath('recordsTotal', 1)
            ->assertJsonPath('data.0.id', $ownGuru->id)
            ->assertJsonMissing(['id' => $otherGuru->id]);

        $createdGuruKtp = '91' . str_pad((string) random_int(1, 99999999999999), 14, '0', STR_PAD_LEFT);
        $this->post(route('guru.store'), [
            'nama_lengkap' => 'Guru Baru Soppeng',
            'no_ktp' => $createdGuruKtp,
            'jenisJabatan' => 'Tenaga Pendidik',
            'jabJenis' => 'Guru',
            'kabupaten' => self::OTHER_COUNTY,
        ])->assertRedirect(route('guru.index'));

        $this->assertDatabaseHas('gurus', [
            'no_ktp' => $createdGuruKtp,
            'kabupaten' => self::OWN_COUNTY,
        ]);
        $this->get(route('admin.eksternal.detail', ['id' => $otherGuru->id]))->assertNotFound();
        $this->get(route('guru.edit', $otherGuru->id))->assertNotFound();
        $this->post(route('guru.hapus', $otherGuru->id))->assertNotFound();
    }

    public function test_county_participant_crud_export_and_print_are_scoped(): void
    {
        $countyUser = $this->makeUser('pegawai-kabupaten', 'soppeng-participant', self::OWN_COUNTY);
        $ownActivity = $this->makeActivity('Kegiatan Peserta Soppeng', self::OWN_COUNTY);
        $otherActivity = $this->makeActivity('Kegiatan Peserta Gowa', self::OTHER_COUNTY);
        $ownParticipant = $this->makeParticipant($ownActivity, 'Peserta Soppeng', self::OWN_COUNTY);
        $otherParticipant = $this->makeParticipant($otherActivity, 'Peserta Gowa', self::OTHER_COUNTY);

        $this->actingAs($countyUser)->withSession($this->countySession($countyUser));

        $this->get(route('peserta.index', [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
        ]))
            ->assertOk()
            ->assertJsonPath('recordsTotal', 1)
            ->assertJsonPath('data.0.id', $ownParticipant->id)
            ->assertJsonMissing(['id' => $otherParticipant->id]);

        $createdParticipantNik = '92' . str_pad((string) random_int(1, 99999999999999), 14, '0', STR_PAD_LEFT);
        $this->post(route('peserta.store'), [
            'id_kegiatan' => $ownActivity->id,
            'nama' => 'Peserta Baru Soppeng',
            'no_ktp' => $createdParticipantNik,
            'instansi' => 'Instansi Soppeng',
            'kabupaten' => self::OTHER_COUNTY,
            'status_keikutpesertaan' => 'peserta',
            'jkl' => 'Laki-laki',
            'jenis_gol' => 'PNS',
            'golongan_pns' => 'III/a',
        ])->assertRedirect(route('peserta.index'));

        $createdParticipant = PesertaKegiatan::query()->where('no_ktp', $createdParticipantNik)->firstOrFail();
        $this->assertSame(self::OWN_COUNTY, $createdParticipant->kabupaten);

        $this->put(route('peserta.update'), [
            'id' => $ownParticipant->id,
            'id_kegiatan' => $ownActivity->id,
            'nama' => 'Peserta Soppeng Diubah',
            'no_ktp' => $ownParticipant->no_ktp,
            'instansi' => 'Instansi Soppeng',
            'kabupaten' => self::OTHER_COUNTY,
            'status_keikutpesertaan' => 'peserta',
            'jkl' => 'Laki-laki',
            'jenis_gol' => 'PNS',
            'golongan_pns' => 'III/a',
        ])->assertRedirect(route('peserta.index'));

        $this->assertDatabaseHas('peserta_kegiatans', [
            'id' => $ownParticipant->id,
            'nama' => 'Peserta Soppeng Diubah',
            'kabupaten' => self::OWN_COUNTY,
        ]);
        $this->get(route('peserta.edit', $otherParticipant->id))->assertNotFound();
        $this->get(route('peserta.export', ['id_kegiatan' => $otherActivity->id]))->assertNotFound();
        $this->get(route('print.absensi.peserta', ['kegiatan_id' => $otherActivity->id]))->assertNotFound();
    }

    private function makeUser(string $role, string $username, ?string $kabupaten = null): User
    {
        return User::query()->create([
            'name' => ucfirst(str_replace('-', ' ', $username)),
            'username' => $username . '-' . uniqid(),
            'password' => 'password123',
            'role' => $role,
            'kabupaten' => $kabupaten,
        ]);
    }

    private function adminSession(User $admin): array
    {
        return [
            'cek' => true,
            'role' => 'admin',
            'user_id' => $admin->id,
            'name' => $admin->name,
        ];
    }

    private function countySession(User $user): array
    {
        return [
            'cek' => true,
            'role' => 'pegawai-kabupaten',
            'user_id' => $user->id,
            'name' => $user->name,
            'kabupaten' => $user->kabupaten,
        ];
    }

    private function makeActivity(string $name, string $kabupaten): Kegiatan
    {
        return Kegiatan::query()->create([
            'nama_kegiatan' => $name,
            'tempat_kegiatan' => $kabupaten,
            'tgl_kegiatan' => '2026-10-01',
            'tgl_selesai' => '2026-10-01',
            'jam_mulai' => '08:00',
            'jam_selesai' => '16:00',
            'deskripsi_kegiatan' => 'Test kegiatan kabupaten.',
            'kabupaten' => $kabupaten,
            'status' => 'true',
        ]);
    }

    private function makeGuru(string $name, string $kabupaten): Guru
    {
        return Guru::query()->create([
            'nama_lengkap' => $name,
            'no_ktp' => '93' . str_pad((string) random_int(1, 99999999999999), 14, '0', STR_PAD_LEFT),
            'kabupaten' => $kabupaten,
            'is_verif' => 'belum',
        ]);
    }

    private function makeParticipant(Kegiatan $activity, string $name, string $kabupaten): PesertaKegiatan
    {
        return PesertaKegiatan::query()->create([
            'id_kegiatan' => $activity->id,
            'nama' => $name,
            'no_ktp' => '94' . str_pad((string) random_int(1, 99999999999999), 14, '0', STR_PAD_LEFT),
            'instansi' => 'Instansi Test',
            'kabupaten' => $kabupaten,
            'status_keikutpesertaan' => 'peserta',
            'jkl' => 'Laki-laki',
            'jenis_gol' => 'PNS',
            'golongan' => 'III/a',
        ]);
    }
}
