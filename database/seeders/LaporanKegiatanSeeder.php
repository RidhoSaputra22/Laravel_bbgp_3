<?php

namespace Database\Seeders;

use App\Models\Berkas;
use App\Models\Pegawai;
use Illuminate\Database\Seeder;

class LaporanKegiatanSeeder extends Seeder
{
    public function run(): void
    {
        $reports = [
            [
                'nik' => '7371061502850002',
                'nama_kegiatan' => 'Bimbingan Teknis Pengelolaan Data Satuan Pendidikan',
                'nama_berkas' => 'https://drive.google.com/file/d/demo-laporan-bimtek-data/view',
                'status' => 'selesai',
            ],
            [
                'nik' => '7371092203900003',
                'nama_kegiatan' => 'Lokakarya Penguatan Komunitas Belajar Sekolah',
                'nama_berkas' => 'https://drive.google.com/file/d/demo-laporan-lokakarya/view',
                'status' => 'proses',
            ],
        ];

        foreach ($reports as $report) {
            if (! Pegawai::where('no_ktp', $report['nik'])->exists()) {
                continue;
            }

            Berkas::updateOrCreate(
                ['nik' => $report['nik'], 'nama_kegiatan' => $report['nama_kegiatan']],
                $report + ['metode_upload' => 'link']
            );
        }
    }
}
