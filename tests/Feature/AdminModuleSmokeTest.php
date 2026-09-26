<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminModuleSmokeTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::query()->create([
            'name' => 'Admin Smoke Test',
            'username' => 'admin-smoke-'.uniqid(),
            'password' => 'password',
            'role' => 'admin',
        ]);

        $this->actingAs($this->admin);
    }

    public static function adminLandingRoutes(): array
    {
        return [
            ['dashboard'],
            ['agenda.index'],
            ['akun.index'],
            ['artikel.index'],
            ['assessment.index'],
            ['assessment.combination.index'],
            ['assessment.assignment.index'],
            ['assessment.validator.index'],
            ['assessment.validator.form.index'],
            ['assessment.validator.assignment.index'],
            ['assessment.validator.task.index'],
            ['berita.index'],
            ['berkas.index'],
            ['guru.index'],
            ['admin.data-sekolah.index'],
            ['evaluasi.pelaksanaan.bank-soal.index'],
            ['evaluasi.pelaksanaan.hasil.index'],
            ['honor.index'],
            ['internal.index'],
            ['internal.calendar'],
            ['kegiatan.index'],
            ['kepegawaian.index'],
            ['kependidikan.index'],
            ['kuitansi.index'],
            ['kuitansiLoka.index'],
            ['pegawai.index'],
            ['peserta.index'],
            ['pendamping.index'],
            ['penyewaan.index'],
            ['rtl.index'],
        ];
    }

    /**
     * @dataProvider adminLandingRoutes
     */
    public function test_each_admin_menu_landing_route_is_registered_and_does_not_crash(string $routeName, mixed $parameter = null): void
    {
        $this->assertTrue(Route::has($routeName), "Route {$routeName} belum terdaftar.");

        $response = $this->withSession([
            'cek' => true,
            'role' => 'admin',
            'user_id' => $this->admin->id,
        ])->get(route($routeName, $routeName === 'profile.index' ? $this->admin->id : $parameter));

        $this->assertLessThan(
            500,
            $response->getStatusCode(),
            "Route {$routeName} mengembalikan server error."
        );
        $this->assertNotSame(404, $response->getStatusCode(), "Route {$routeName} tidak ditemukan.");
    }

    public function test_admin_crud_route_families_are_present_for_each_menu(): void
    {
        $routeFamilies = [
            ['agenda.index', 'agenda.create', 'agenda.store', 'agenda.update', 'agenda.hapus'],
            ['akun.index', 'akun.create', 'akun.store', 'akun.update', 'akun.hapus'],
            ['artikel.index', 'artikel.create', 'artikel.store', 'artikel.update', 'artikel.hapus'],
            ['assessment.index', 'assessment.create', 'assessment.store', 'assessment.update', 'assessment.hapus'],
            ['assessment.combination.index', 'assessment.combination.create', 'assessment.combination.store'],
            ['assessment.assignment.index', 'assessment.assignment.create', 'assessment.assignment.store', 'assessment.assignment.update'],
            ['assessment.validator.form.index', 'assessment.validator.form.create', 'assessment.validator.form.store'],
            ['assessment.validator.assignment.index', 'assessment.validator.assignment.create', 'assessment.validator.assignment.store'],
            ['berita.index', 'berita.create', 'berita.store', 'berita.update', 'berita.hapus'],
            ['berkas.index', 'berkas.create', 'berkas.store', 'berkas.update', 'berkas.hapus', 'berkas.verify'],
            ['guru.index', 'guru.create', 'guru.store', 'guru.update', 'guru.hapus', 'guru.verifikasi'],
            ['kegiatan.index', 'kegiatan.create', 'kegiatan.store', 'kegiatan.update', 'kegiatan.hapus'],
            ['kepegawaian.index', 'kepegawaian.create', 'kepegawaian.store', 'kepegawaian.update', 'kepegawaian.hapus'],
            ['kependidikan.index', 'kependidikan.create', 'kependidikan.store', 'kependidikan.update', 'kependidikan.hapus'],
            ['pegawai.index', 'pegawai.create', 'pegawai.store', 'pegawai.update', 'pegawai.hapus', 'pegawai.verifikasi'],
            ['peserta.index', 'peserta.create', 'peserta.store', 'peserta.update', 'peserta.hapus'],
            ['penyewaan.index', 'penyewaan.create', 'penyewaan.store', 'penyewaan.update', 'penyewaan.hapus'],
            ['rtl.index', 'rtl.update'],
            ['profile.update'],
        ];

        foreach ($routeFamilies as $family) {
            foreach ($family as $routeName) {
                $this->assertTrue(Route::has($routeName), "Route {$routeName} belum terdaftar.");
            }
        }
    }
}
