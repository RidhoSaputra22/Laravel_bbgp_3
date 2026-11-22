<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Kegiatan;
use App\Models\PesertaKegiatan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class KegiatanReportTest extends TestCase
{
    use DatabaseTransactions;

    public function test_activity_report_generates_pdf_with_filtered_activity(): void
    {
        $admin = User::query()->create([
            'name' => 'Report Admin',
            'username' => 'report-admin-'.uniqid(),
            'password' => 'password',
            'role' => 'admin',
        ]);
        $activity = Kegiatan::query()->create([
            'nama_kegiatan' => 'Kegiatan Laporan '.uniqid(),
            'tempat_kegiatan' => 'Makassar',
            'tgl_kegiatan' => '2026-11-10',
            'tgl_selesai' => '2026-11-10',
            'jam_mulai' => '08:00',
            'jam_selesai' => '16:00',
            'status' => 'true',
        ]);

        foreach (['Tenaga Pendidik', 'Tenaga Kependidikan', 'Stakeholder'] as $index => $role) {
            $nik = 'REPORT-'.uniqid().$index;
            Guru::query()->create([
                'nama_lengkap' => $role,
                'no_ktp' => $nik,
                'eksternal_jabatan' => $role,
            ]);
            PesertaKegiatan::query()->create([
                'id_kegiatan' => $activity->id,
                'nama' => $role,
                'no_ktp' => $nik,
                'instansi' => 'Instansi Test',
                'status_keikutpesertaan' => 'peserta',
                'jkl' => 'Laki-laki',
            ]);
        }

        PesertaKegiatan::query()->create([
            'id_kegiatan' => $activity->id,
            'nama' => 'Panitia',
            'no_ktp' => 'REPORT-PANITIA-'.uniqid(),
            'instansi' => 'Instansi Test',
            'status_keikutpesertaan' => 'panitia',
            'jkl' => 'Perempuan',
        ]);
        PesertaKegiatan::query()->create([
            'id_kegiatan' => $activity->id,
            'nama' => 'Narasumber',
            'no_ktp' => 'REPORT-NARASUMBER-'.uniqid(),
            'instansi' => 'Instansi Test',
            'status_keikutpesertaan' => 'narasumber',
            'jkl' => 'Laki-laki',
        ]);

        $response = $this->actingAs($admin)
            ->withSession(['cek' => true, 'role' => 'admin', 'user_id' => $admin->id])
            ->get(route('kegiatan.print-laporan', [
                'kegiatan_id' => $activity->id,
                'tanggal_dari' => '2026-11-01',
                'tanggal_sampai' => '2026-11-30',
            ]));

        $response->assertOk()->assertHeader('content-type', 'application/pdf');
    }
}
