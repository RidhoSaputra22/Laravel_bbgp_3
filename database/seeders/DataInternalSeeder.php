<?php

namespace Database\Seeders;

use App\Models\Internal;
use App\Models\Pendamping;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DataInternalSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            [
                'nik' => '7371061502850002',
                'jenis' => 'Penugasan Pegawai',
                'kegiatan' => 'Bimbingan Teknis Pengelolaan Data Satuan Pendidikan',
                'tempat' => 'Aula Dinas Pendidikan Kabupaten Gowa',
                'kota' => 'Kabupaten Gowa',
                'tgl_kegiatan' => '2026-10-06',
                'tgl_selesai_kegiatan' => '2026-10-07',
                'jam_mulai' => '08:00:00',
                'jam_selesai' => '16:00:00',
                'deskripsi' => 'Pendampingan pemutakhiran data satuan pendidikan dan verifikasi dokumen sekolah.',
                'transport_pergi' => 85000,
                'transport_pulang' => 85000,
                'hotel' => 'Hotel Santika Makassar',
                'bill_penginapan' => 450000,
                'hari_1' => 150000,
                'hari_2' => 150000,
                'is_verif' => 'sudah',
                'jenis_data' => 'ASN',
                'nama' => 'Muhammad Fadli Ramadhan',
                'nip' => '198502152010011002',
                'golongan' => 'III/b',
                'jabatan' => 'Analis Diklat',
            ],
            [
                'nik' => '7371092203900003',
                'jenis' => 'Penugasan Pegawai',
                'kegiatan' => 'Lokakarya Penguatan Komunitas Belajar Sekolah',
                'tempat' => 'UPTD SMP Negeri 1 Parepare',
                'kota' => 'Kota Parepare',
                'tgl_kegiatan' => '2026-10-13',
                'tgl_selesai_kegiatan' => '2026-10-15',
                'jam_mulai' => '08:00:00',
                'jam_selesai' => '16:00:00',
                'deskripsi' => 'Koordinasi lokakarya dan pendampingan penyusunan rencana tindak lanjut sekolah.',
                'transport_pergi' => 175000,
                'transport_pulang' => 175000,
                'hotel' => 'Hotel Bukit Kenari Parepare',
                'bill_penginapan' => 900000,
                'hari_1' => 150000,
                'hari_2' => 150000,
                'hari_3' => 150000,
                'is_verif' => 'sudah',
                'jenis_data' => 'ASN',
                'nama' => 'Rismawati Yusuf',
                'nip' => '199003222015032003',
                'golongan' => 'III/a',
                'jabatan' => 'Pengelola Keuangan',
            ],
        ] as $assignment) {
            Internal::updateOrCreate(
                [
                    'nik' => $assignment['nik'],
                    'jenis' => $assignment['jenis'],
                    'kegiatan' => $assignment['kegiatan'],
                ],
                $assignment
            );
        }

        $ppnpnId = DB::table('pegawaiPpnpns')->updateOrInsert(
            ['nik' => '7371010501900013'],
            [
                'nama' => 'Sudirman',
                'jabatan' => 'Sopir',
                'nip' => '199001052020011013',
            ]
        );

        $ppnpnId = DB::table('pegawaiPpnpns')->where('nik', '7371010501900013')->value('id');

        DB::table('internal_ppnpns')->updateOrInsert(
            [
                'id_pegawai' => (string) $ppnpnId,
                'kegiatan' => 'Distribusi perangkat pembelajaran digital ke Kota Parepare',
            ],
            [
                'nik' => '7371010501900013',
                'jabatan' => 'Sopir',
                'tempat' => 'BBGTK Sulawesi Selatan',
                'kabupaten' => 'Kota Parepare',
                'tgl_kegiatan' => '2026-10-12',
                'tgl_selesai_kegiatan' => '2026-10-13',
                'jam_mulai' => '07:00:00',
                'jam_selesai' => '17:00:00',
                'deskripsi' => 'Pengantaran perangkat pembelajaran dan dokumen kegiatan ke satuan pendidikan mitra.',
            ]
        );

        Pendamping::updateOrCreate(
            ['nik' => '7371120402780004', 'tgl_kegiatan' => '2026-10-13'],
            [
                'nama' => 'Andi Syahrul Maulana',
                'nip' => '197802042005011004',
                'kota' => 'Kota Parepare',
                'kabupaten' => 'Kota Parepare',
                'hotel' => 'Hotel Bukit Kenari Parepare',
                'transport_pergi' => 175000,
                'transport_pulang' => 175000,
                'hari_1' => 150000,
                'hari_2' => 150000,
                'hari_3' => 150000,
                'is_verif' => 'sudah',
            ]
        );
    }
}
