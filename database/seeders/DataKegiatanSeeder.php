<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\Kegiatan;
use App\Models\Pegawai;
use App\Models\PesertaKegiatan;
use Illuminate\Database\Seeder;

class DataKegiatanSeeder extends Seeder
{
    public function run(): void
    {
        $activities = [];

        foreach ([
            [
                'nama_kegiatan' => 'Pelatihan Pembelajaran Mendalam, Koding, dan Kecerdasan Artifisial',
                'tempat_kegiatan' => 'Aula BBGTK Sulawesi Selatan, Jalan Adiyaksa Nomor 2, Makassar',
                'tgl_kegiatan' => '2026-10-20',
                'tgl_selesai' => '2026-10-22',
                'jam_mulai' => '08:00:00',
                'jam_selesai' => '16:00:00',
                'deskripsi_kegiatan' => 'Pelatihan bagi guru dan kepala sekolah untuk menerapkan pembelajaran mendalam, koding, dan kecerdasan artifisial secara bertanggung jawab.',
                'status' => 'true',
            ],
            [
                'nama_kegiatan' => 'Lokakarya Penguatan Komunitas Belajar Sekolah',
                'tempat_kegiatan' => 'UPTD SMP Negeri 1 Parepare, Kota Parepare',
                'tgl_kegiatan' => '2026-10-13',
                'tgl_selesai' => '2026-10-15',
                'jam_mulai' => '08:00:00',
                'jam_selesai' => '16:00:00',
                'deskripsi_kegiatan' => 'Lokakarya untuk menyusun praktik baik komunitas belajar dan rencana tindak lanjut satuan pendidikan.',
                'status' => 'true',
            ],
            [
                'nama_kegiatan' => 'Bimbingan Teknis Pengelolaan Data Satuan Pendidikan',
                'tempat_kegiatan' => 'Aula Dinas Pendidikan Kabupaten Gowa',
                'tgl_kegiatan' => '2026-10-06',
                'tgl_selesai' => '2026-10-07',
                'jam_mulai' => '08:00:00',
                'jam_selesai' => '16:00:00',
                'deskripsi_kegiatan' => 'Bimbingan teknis pemutakhiran data sekolah, validasi dokumen, dan penguatan tata kelola informasi pendidikan.',
                'status' => 'true',
            ],
        ] as $activity) {
            $activities[$activity['nama_kegiatan']] = Kegiatan::updateOrCreate(
                ['nama_kegiatan' => $activity['nama_kegiatan']],
                $activity
            );
        }

        $sources = [
            [
                'activity' => 'Pelatihan Pembelajaran Mendalam, Koding, dan Kecerdasan Artifisial',
                'no_ktp' => '7373012510820011',
                'role' => 'narasumber',
                'golongan' => 'III/c',
                'jenis_gol' => 'PNS',
                'mata_pelajaran' => 'Matematika dan Kecerdasan Artifisial',
            ],
            [
                'activity' => 'Pelatihan Pembelajaran Mendalam, Koding, dan Kecerdasan Artifisial',
                'no_ktp' => '7373021809870010',
                'role' => 'peserta',
                'golongan' => 'III/b',
                'jenis_gol' => 'PNS',
                'mata_pelajaran' => 'Bahasa Indonesia',
            ],
            [
                'activity' => 'Lokakarya Penguatan Komunitas Belajar Sekolah',
                'no_ktp' => '7372761008840009',
                'role' => 'peserta',
                'golongan' => 'IV/a',
                'jenis_gol' => 'PNS',
                'mata_pelajaran' => 'Manajemen sekolah',
            ],
            [
                'activity' => 'Bimbingan Teknis Pengelolaan Data Satuan Pendidikan',
                'no_ktp' => '7371061502850002',
                'role' => 'peserta',
                'golongan' => 'III/b',
                'jenis_gol' => 'PNS',
                'mata_pelajaran' => 'Pengelolaan data pendidikan',
            ],
        ];

        foreach ($sources as $source) {
            $guru = Guru::where('no_ktp', $source['no_ktp'])->first();
            $pegawai = Pegawai::where('no_ktp', $source['no_ktp'])->first();
            $person = $guru ?: $pegawai;

            if (! $person || ! isset($activities[$source['activity']])) {
                continue;
            }

            PesertaKegiatan::updateOrCreate(
                [
                    'id_kegiatan' => (string) $activities[$source['activity']]->id,
                    'no_ktp' => $source['no_ktp'],
                ],
                [
                    'id_pegawai' => $pegawai ? (string) $pegawai->id : null,
                    'nama' => $person->nama_lengkap,
                    'nip' => $person->nip,
                    'alamat' => $person->alamat_satuan,
                    'email' => $person->email,
                    'mata_pelajaran' => $source['mata_pelajaran'],
                    'status_keikutpesertaan' => $source['role'],
                    'instansi' => $person instanceof Guru ? $person->satuan_pendidikan : 'BBGTK Sulawesi Selatan',
                    'golongan' => $source['golongan'],
                    'jenis_gol' => $source['jenis_gol'],
                    'jkl' => $person->gender,
                    'kelengkapan_peserta_transport' => 'lengkap',
                    'kelengkapan_peserta_biodata' => 'lengkap',
                    'no_hp' => $person->no_hp,
                    'no_wa' => $person->no_wa,
                    'status' => 'terdaftar',
                    'tempat_lahir' => $person->tempat_lahir,
                    'tgl_lahir' => $person->tgl_lahir,
                    'agama' => $person->agama,
                    'pendidikan' => $person->pendidikan,
                    'alamat_rumah' => $person->alamat_rumah,
                    'kabupaten_rumah' => $person->kabupaten,
                    'npwp' => $person instanceof Guru ? $person->npwp : null,
                    'kabupaten' => $person->kabupaten,
                    'no_surat_tugas' => '094/BBGTK-SULSEL/KEGIATAN/X/2026',
                    'tgl_surat_tugas' => '2026-10-01',
                ]
            );
        }
    }
}
