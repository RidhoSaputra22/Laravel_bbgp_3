<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Kegiatan;
use App\Models\PesertaKegiatan;
use Tests\TestCase;

class ObjectAuthorizationTest extends TestCase
{
    public function test_user_cannot_query_participant_data_for_another_nik(): void
    {
        $kegiatan = Kegiatan::query()->create([
            'nama_kegiatan' => 'Kegiatan Authorization',
            'tempat_kegiatan' => 'Makassar',
            'tgl_kegiatan' => '2026-10-01',
            'jam_mulai' => '08:00',
            'status' => 'true',
        ]);
        $ownerNik = '1111111111111111';
        $otherNik = '2222222222222222';

        foreach ([$ownerNik, $otherNik] as $nik) {
            PesertaKegiatan::query()->create([
                'id_kegiatan' => $kegiatan->id,
                'nama' => 'Peserta '.$nik,
                'no_ktp' => $nik,
                'instansi' => 'Instansi Test',
                'status_keikutpesertaan' => 'peserta',
                'jkl' => 'Laki-laki',
            ]);
        }

        $session = [
            'cek' => true,
            'role' => 'pegawai',
            'no_ktp' => $ownerNik,
        ];
        $requests = [
            route('user.kegiatan.cariPeserta', ['kegiatan_id' => $kegiatan->id, 'nik' => $otherNik]),
            route('user.cari', ['cari' => $otherNik]),
            route('user.peserta.cekData', ['nik' => $otherNik]),
            route('user.kegiatan.peserta', ['kegiatan_id' => $kegiatan->id, 'nik' => $otherNik]),
        ];

        foreach ($requests as $url) {
            $this->withSession($session)->get($url)->assertForbidden();
        }

        $this->withSession($session)
            ->get(route('user.kegiatan.cariPeserta', ['kegiatan_id' => $kegiatan->id, 'nik' => $ownerNik]))
            ->assertOk()
            ->assertJsonPath('data.0.no_ktp', $ownerNik);

        $this->withSession(['cek' => true, 'role' => 'admin'])
            ->get(route('user.kegiatan.cariPeserta', ['kegiatan_id' => $kegiatan->id, 'nik' => $otherNik]))
            ->assertOk()
            ->assertJsonPath('data.0.no_ktp', $otherNik);
    }

    public function test_only_privileged_users_can_search_all_guru_data(): void
    {
        Guru::query()->create([
            'nama_lengkap' => 'Guru Authorization',
            'no_ktp' => '3333333333333333',
            'is_verif' => 'sudah',
        ]);

        $this->withSession([
            'cek' => true,
            'role' => 'pegawai',
            'no_ktp' => '1111111111111111',
        ])->get(route('user.cari.guru'))->assertForbidden();

        $this->withSession(['cek' => true, 'role' => 'admin'])
            ->get(route('user.cari.guru'))
            ->assertOk()
            ->assertJsonPath('status', true);
    }
}
