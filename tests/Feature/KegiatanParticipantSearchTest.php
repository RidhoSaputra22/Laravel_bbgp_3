<?php

namespace Tests\Feature;

use App\Models\Kegiatan;
use App\Models\PesertaKegiatan;
use Tests\TestCase;

class KegiatanParticipantSearchTest extends TestCase
{
    private function kegiatan(): Kegiatan
    {
        return Kegiatan::query()->create([
            'nama_kegiatan' => 'Asesmen Lab School',
            'tempat_kegiatan' => 'Hartaco Indah',
            'tgl_kegiatan' => '2026-10-13',
            'tgl_selesai' => '2026-10-31',
            'jam_mulai' => '00:00',
            'jam_selesai' => '00:00',
            'status' => 'true',
        ]);
    }

    public function test_public_search_returns_participant_and_detail_without_login(): void
    {
        $kegiatan = $this->kegiatan();
        $nik = '7887878787878787';
        $participant = PesertaKegiatan::query()->create([
            'id_kegiatan' => $kegiatan->id,
            'nama' => 'Peserta Lab School',
            'no_ktp' => $nik,
            'instansi' => 'Sekolah Test',
            'status_keikutpesertaan' => 'peserta',
            'jkl' => 'Laki-laki',
        ]);

        $this->get(route('public.kegiatan.cariPeserta', [
            'kegiatan_id' => $kegiatan->id,
            'nik' => $nik,
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('tipe', 'Peserta')
            ->assertJsonPath('data.0.no_ktp', $nik)
            ->assertJsonPath('data.0.nama', 'Peserta Lab School');

        $this->get(route('public.peserta.detail', [
            'id' => $participant->id,
            'kegiatan_id' => $kegiatan->id,
        ]))
            ->assertOk()
            ->assertJsonPath('no_ktp', $nik)
            ->assertJsonPath('nama', 'Peserta Lab School');
    }

    public function test_public_search_returns_empty_result_for_unknown_nik(): void
    {
        $kegiatan = $this->kegiatan();

        $this->get(route('public.kegiatan.cariPeserta', [
            'kegiatan_id' => $kegiatan->id,
            'nik' => '7887878787878788',
        ]))
            ->assertOk()
            ->assertJsonPath('success', false)
            ->assertJsonPath('tipe', 'Eksternal')
            ->assertJsonCount(0, 'data');
    }

    public function test_protected_search_route_still_requires_login(): void
    {
        $this->get(route('user.kegiatan.cariPeserta', [
            'kegiatan_id' => $this->kegiatan()->id,
            'nik' => '7887878787878789',
        ]))->assertRedirect(route('login'));
    }
}
