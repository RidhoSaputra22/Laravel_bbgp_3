<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Kegiatan;
use App\Models\Pegawai;
use Tests\TestCase;

class PublicKegiatanRegistrationSecurityTest extends TestCase
{
    public function test_public_registration_does_not_modify_pegawai_or_guru_master_data(): void
    {
        $kegiatan = Kegiatan::query()->create([
            'nama_kegiatan' => 'Kegiatan Keamanan',
            'tempat_kegiatan' => 'Makassar',
            'tgl_kegiatan' => '2026-10-01',
            'jam_mulai' => '08:00',
            'status' => 'true',
        ]);

        foreach ([Pegawai::class, Guru::class] as $modelClass) {
            $nik = '327'.str_pad((string) random_int(1, 9999999999999), 13, '0', STR_PAD_LEFT);

            $master = $modelClass::query()->create([
                'nama_lengkap' => 'Nama Master',
                'no_ktp' => $nik,
                'nip' => 'NIP-MASTER',
                'tempat_lahir' => 'Kota Master',
                'tgl_lahir' => '1980-01-01',
                'agama' => 'Islam',
                'pendidikan' => 'Pendidikan Master',
                'alamat_rumah' => 'Alamat Master',
                'status' => 'Kawin',
            ]);

            $this->post(route('user.kegiatan_store'), [
                'kegiatan_id' => $kegiatan->id,
                'nama' => 'Nama Penyerang',
                'no_ktp' => $nik,
                'nip' => 'NIP-PENYERANG',
                'kabupaten' => 'Makassar',
                'instansi' => 'Instansi Penyerang',
                'jenis_gol' => 'Tidak ada golongan',
                'diluar_gol' => 'Non ASN',
                'no_surat_tugas' => 'ST-PENYERANG',
                'tgl_surat_tugas' => '2026-10-01',
                'status_keikutpesertaan' => 'peserta',
                'jkl' => 'Laki-laki',
                'tempat_lahir' => 'Kota Penyerang',
                'tgl_lahir' => '2000-02-02',
                'agama' => 'Kristen',
                'pendidikan' => 'Pendidikan Penyerang',
                'alamat_rumah' => 'Alamat Penyerang',
                'status' => 'Belum Kawin',
            ])->assertRedirect(route('user.kegiatan'));

            $master->refresh();

            $this->assertSame('Nama Master', $master->nama_lengkap);
            $this->assertSame('NIP-MASTER', $master->nip);
            $this->assertSame('Kota Master', $master->tempat_lahir);
            $this->assertSame('1980-01-01', $master->tgl_lahir);
            $this->assertSame('Islam', $master->agama);
            $this->assertSame('Pendidikan Master', $master->pendidikan);
            $this->assertSame('Alamat Master', $master->alamat_rumah);
            $this->assertSame('Kawin', $master->status);
        }
    }
}
