<?php

namespace Tests\Feature;

use App\Models\Guru;
use Tests\TestCase;

class PegawaiDataRoutesSecurityTest extends TestCase
{
    public function test_guest_is_redirected_to_login_for_pegawai_data_endpoints(): void
    {
        foreach ([
            route('user.pegawai.all'),
            route('user.pegawai.detail', ['id' => 1]),
            route('user.pegawai.detail.loka', ['id' => 1]),
            route('user.pegawai.detail.eksternal', ['id' => 1]),
        ] as $endpoint) {
            $this->get($endpoint)->assertRedirect(route('login'));
        }
    }

    public function test_guest_can_view_public_external_detail(): void
    {
        $guru = Guru::query()->create([
            'nama_lengkap' => 'Guru Publik Test',
            'no_ktp' => '7887878787878790',
            'gender' => 'Laki-laki',
            'status_kepegawaian' => 'PNS',
            'kabupaten' => 'Makassar',
            'eksternal_jabatan' => 'Tenaga Pendidik',
            'jenis_jabatan' => 'Guru',
        ]);

        $this->get(route('public.guru.detail', ['id' => $guru->id]))
            ->assertOk()
            ->assertJsonPath('data.nama_lengkap', 'Guru Publik Test')
            ->assertJsonPath('data.status_kepegawaian', 'PNS')
            ->assertJsonPath('data.kabupaten', 'Makassar');
    }
}
