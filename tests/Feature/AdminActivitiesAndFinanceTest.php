<?php

namespace Tests\Feature;

use App\Models\Honor;
use App\Models\Internal;
use App\Models\Kegiatan;
use App\Models\Kuitansi;
use App\Models\KuitansiLoka;
use App\Models\PesertaKegiatan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminActivitiesAndFinanceTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::query()->create([
            'name' => 'Finance Admin Test',
            'username' => 'finance-admin-'.uniqid(),
            'password' => 'password',
            'role' => 'admin',
        ]);

        $this->actingAs($this->admin);
    }

    private function adminSession(): array
    {
        return [
            'cek' => true,
            'role' => 'admin',
            'user_id' => $this->admin->id,
        ];
    }

    private function activity(string $prefix = 'Finance Activity'): Kegiatan
    {
        return Kegiatan::query()->create([
            'nama_kegiatan' => $prefix.' '.uniqid(),
            'tempat_kegiatan' => 'Makassar',
            'tgl_kegiatan' => '2026-10-05',
            'tgl_selesai' => '2026-10-05',
            'jam_mulai' => '08:00',
            'jam_selesai' => '16:00',
            'status' => 'true',
        ]);
    }

    public function test_participant_input_handles_custom_district_and_duplicate_identity(): void
    {
        $activity = $this->activity('Participant Activity');
        $noKtp = '93'.str_pad((string) random_int(1, 99999999999999), 14, '0', STR_PAD_LEFT);
        $payload = [
            'id_kegiatan' => $activity->id,
            'nama' => 'Participant Feature Test',
            'no_ktp' => $noKtp,
            'instansi' => 'Instansi Test',
            'status_keikutpesertaan' => 'peserta',
            'jkl' => 'Laki-laki',
            'kabupaten' => 'lainnya',
            'asal_kabupaten' => 'Kabupaten Test',
            'jenis_gol' => 'Tidak ada golongan',
            'diluar_gol' => 'Non ASN',
        ];

        $this->withSession($this->adminSession())
            ->post(route('peserta.store'), $payload)
            ->assertRedirect(route('peserta.index'));

        $participant = PesertaKegiatan::query()->where('no_ktp', $noKtp)->firstOrFail();
        $this->assertSame('Kabupaten Test', $participant->kabupaten);
        $this->assertSame('Non ASN', $participant->golongan);

        $this->withSession($this->adminSession())
            ->post(route('peserta.store'), $payload)
            ->assertRedirect(route('peserta.create'));

        $this->withSession($this->adminSession())
            ->post(route('peserta.hapus', $participant->id))
            ->assertOk()
            ->assertJsonPath('id', $participant->id);
    }

    public function test_honor_input_normalizes_currency_and_rejects_duplicate_participant(): void
    {
        $activity = $this->activity('Honor Activity');
        $participant = PesertaKegiatan::query()->create([
            'id_kegiatan' => $activity->id,
            'nama' => 'Narasumber Feature Test',
            'no_ktp' => '94'.str_pad((string) random_int(1, 99999999999999), 14, '0', STR_PAD_LEFT),
            'instansi' => 'Instansi Test',
            'status_keikutpesertaan' => 'narasumber',
            'jkl' => 'Perempuan',
        ]);

        $payload = [
            'id_peserta' => $participant->id,
            'kode_anggaran' => '521213',
            'golongan' => 'III/c / Pembina',
            'jenis_gol' => 'PNS',
            'jp_realisasi' => '8',
            'jumlah' => '100.000',
            'jumlah_honor' => '750.000',
            'potongan' => '0',
            'jumlah_diterima' => '750.000',
        ];

        $this->withSession($this->adminSession())
            ->post(route('honor.store'), $payload)
            ->assertRedirect(route('honor.index'));

        $honor = Honor::query()->where('id_peserta', $participant->id)->firstOrFail();
        $this->assertSame(750000, (int) $honor->jumlah_honor);
        $this->assertSame('III', $honor->golongan);

        $this->withSession($this->adminSession())
            ->post(route('honor.store'), $payload)
            ->assertRedirect(route('honor.create'));
    }

    public function test_internal_assignment_and_numbering_inputs_persist(): void
    {
        $nik = '95'.str_pad((string) random_int(1, 99999999999999), 14, '0', STR_PAD_LEFT);

        $this->withSession($this->adminSession())
            ->post(route('internal.store'), [
                'jenis' => 'Penugasan Pegawai',
                'nik' => $nik,
                'nama' => 'Internal Feature Test',
                'kegiatan' => 'Kegiatan Internal',
                'kota' => 'Makassar',
                'is_verif' => 'belum',
            ])
            ->assertRedirect(route('internal.index'));

        $internal = Internal::query()->where('nik', $nik)->firstOrFail();
        $this->assertSame('sudah', $internal->is_verif);

        $this->withSession($this->adminSession())
            ->post(route('internal.verifikasi', $internal->id))
            ->assertOk()
            ->assertJsonPath('status.is_verif', 'sudah');

        $this->withSession($this->adminSession())
            ->get(route('honor.storeNomor', [
                'no_surat' => 'ST-TEST-001',
                'tgl_surat' => '2026-10-05',
                'kode_anggaran' => '521213',
                'kegiatan_id' => 1,
            ]))
            ->assertOk()
            ->assertJsonPath('status', true);

        $this->withSession($this->adminSession())
            ->post(route('internal.hapus', $internal->id))
            ->assertOk();
    }

    public function test_travel_receipt_and_workshop_receipt_accept_numeric_inputs(): void
    {
        $participant = PesertaKegiatan::query()->create([
            'id_kegiatan' => $this->activity('Receipt Activity')->id,
            'nama' => 'Receipt Participant',
            'no_ktp' => '96'.str_pad((string) random_int(1, 99999999999999), 14, '0', STR_PAD_LEFT),
            'instansi' => 'Instansi Test',
            'status_keikutpesertaan' => 'peserta',
            'jkl' => 'Laki-laki',
        ]);

        $receiptPayload = [
            'id_pegawai' => $participant->id,
            'no_bukti' => 'BK-TEST-001',
            'no_MAK' => '521213',
            'no_surat_tugas' => 'ST-TEST-001',
            'tgl_surat_tugas' => '2026-10-05',
            'tahun_anggaran' => '2026',
            'lokasi_asal' => 'Makassar',
            'lokasi_tujuan' => 'Gowa',
            'jenis_angkutan' => 'Darat',
            'biaya_pergi' => '100000',
            'biaya_pulang' => '100000',
            'jumlah_biaya' => '200000',
            'pajak_bandara' => '0',
            'biaya_asal' => '0',
            'bea_jarak' => '0',
            'tujuan' => '0',
            'total_transport' => '200000',
            'biaya_penginapan' => '0',
            'uang_harian' => '0',
            'potongan' => '0',
            'total_penginapan' => '0',
            'biaya_harian' => '0',
            'jumlah_hari' => '1',
            'jumlah_biaya_diterima' => '200000',
            'bill_penginapan' => '0',
            'jumlah_nginap' => '0',
        ];

        $this->withSession($this->adminSession())
            ->post(route('kuitansi.store'), $receiptPayload)
            ->assertRedirect(route('kuitansi.index'));

        $receipt = Kuitansi::query()->where('pegawai_id', (string) $participant->id)->firstOrFail();
        $this->assertSame(200000, (int) $receipt->total_transport);

        $internal = Internal::query()->create([
            'nik' => '97'.str_pad((string) random_int(1, 99999999999999), 14, '0', STR_PAD_LEFT),
            'jenis' => 'Pendamping Lokakarya',
            'nama' => 'Pendamping Receipt Test',
        ]);

        $lokaPayload = [
            'id' => $internal->id,
            'transport_pergi' => 'Rp 100.000',
            'transport_pulang' => 'Rp 100.000',
            'bill_penginapan' => 'Rp 0',
            'hari_1' => 'Rp 0',
            'hari_2' => 'Rp 0',
            'hari_3' => 'Rp 0',
            'hari_4' => 'Rp 0',
            'hari_5' => 'Rp 0',
            'hari_6' => 'Rp 0',
            'hari_7' => 'Rp 0',
            'total' => 'Rp 200.000',
            'no_surat_tugas' => 'ST-LOKA-001',
            'tgl_surat_tugas' => '2026-10-05',
            'kode_anggaran' => '521213',
            'tahun_anggaran' => '2026',
            'no_bukti' => 'BK-LOKA-001',
        ];

        $this->withSession($this->adminSession())
            ->post(route('kuitansiLoka.store'), $lokaPayload)
            ->assertRedirect(route('kuitansiLoka.index'));

        $this->assertDatabaseHas('kuitansi_lokas', [
            'internal_id' => (string) $internal->id,
            'no_bukti' => 'BK-LOKA-001',
        ]);
    }
}
