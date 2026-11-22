<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\PesertaKegiatan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GuruActivityFilterTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guru_activity_count_filter_matches_exact_count(): void
    {
        $admin = User::query()->create([
            'name' => 'Activity Filter Admin',
            'username' => 'activity-filter-'.uniqid(),
            'password' => 'password',
            'role' => 'admin',
        ]);

        $matchingGuru = Guru::query()->create([
            'nama_lengkap' => 'Guru Dua Kegiatan',
            'no_ktp' => 'FILTER-2-'.uniqid(),
        ]);
        Guru::query()->create([
            'nama_lengkap' => 'Guru Satu Kegiatan',
            'no_ktp' => 'FILTER-1-'.uniqid(),
        ]);

        foreach (range(1, 2) as $activity) {
            PesertaKegiatan::query()->create([
                'id_kegiatan' => $activity,
                'nama' => $matchingGuru->nama_lengkap,
                'no_ktp' => $matchingGuru->no_ktp,
                'instansi' => 'Instansi Test',
                'status_keikutpesertaan' => 'peserta',
                'jkl' => 'Laki-laki',
            ]);
        }

        $response = $this->actingAs($admin)
            ->withSession(['cek' => true, 'role' => 'admin', 'user_id' => $admin->id])
            ->getJson(route('guru.cari', [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
                'jumlah_kegiatan' => 2,
            ]));

        $response->assertOk()
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonPath('data.0.nama_lengkap', $matchingGuru->nama_lengkap);
    }

    public function test_guru_activity_filter_keeps_pagination_limit(): void
    {
        $admin = User::query()->create([
            'name' => 'Pagination Filter Admin',
            'username' => 'pagination-filter-'.uniqid(),
            'password' => 'password',
            'role' => 'admin',
        ]);

        foreach (range(1, 3) as $index) {
            Guru::query()->create([
                'nama_lengkap' => 'Guru Pagination '.$index,
                'no_ktp' => 'FILTER-PAGE-'.$index.'-'.uniqid(),
            ]);
        }

        $response = $this->actingAs($admin)
            ->withSession(['cek' => true, 'role' => 'admin', 'user_id' => $admin->id])
            ->getJson(route('guru.cari', [
                'draw' => 1,
                'start' => 0,
                'length' => 2,
                'jumlah_kegiatan' => 0,
            ]));

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('recordsFiltered', 3);
    }
}
