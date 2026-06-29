<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\Pegawai;
use App\Models\Sekolah;
use Illuminate\Database\Seeder;

class DataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schools = Sekolah::query()
            ->select('npsn_sekolah', 'kabupaten', 'bp_sekolah')
            ->orderBy('id')
            ->get();

        $bankOptions = [
            'Bank BRI',
            'Bank BNI',
            'Bank Mandiri',
            'Bank Syariah Indonesia',
        ];

        for ($i = 1; $i <= 10; $i++) {
            Pegawai::create([
                'username' => 'pegawai_' . $i,
                'nama_lengkap' => 'Pegawai ' . $i,
                'email' => 'pegawai' . $i . '@example.com',
                'no_ktp' => '73010000000000' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'nip' => '19800101' . str_pad((string) $i, 10, '0', STR_PAD_LEFT),
                'tempat_lahir' => 'Tempat Lahir ' . $i,
                'tgl_lahir' => '1990-01-01',
                'gender' => $i % 2 == 0 ? 'Laki-laki' : 'Perempuan',
                'jabatan' => $i % 2 == 0 ? 'Analis Diklat' : 'Pramubakti',
                'jenis_pegawai' => $i % 2 == 0 ? 'BBGP' : 'PPNPN',
                'status' => $i % 2 == 0 ? 'Kawin' : 'Belum Kawin',
                'status_kepegawaian' => $i % 2 == 0 ? 'PNS' : 'GTT / PTY',
                'agama' => $i % 3 == 0 ? 'Islam' : ($i % 3 == 1 ? 'Kristen' : 'Katolik'),
                'pendidikan' => 'S1',
                'kabupaten' => $i % 2 == 0 ? 'Kota Makassar' : 'Kabupaten Gowa',
                'satuan_pendidikan' => 'BBGTK Sulawesi Selatan',
                'alamat_satuan' => 'Jl. Perintis Kemerdekaan Km. 10, Makassar',
                'alamat_rumah' => 'Jl. Rumah ' . $i . ' No. ' . $i,
                'no_hp' => '08123456789' . $i,
                'no_wa' => '08123456789' . $i,
                'pas_foto' => 'default.jpg',
                'instansi' => 'Kantor BBGTK SulSel',
                'golongan' => $i % 2 == 0 ? 'III/a' : '',
                'jenis_bank' => $bankOptions[($i - 1) % count($bankOptions)],
                'no_rek' => '7100000000' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'is_verif' => $i <= 3 ? 'sudah' : 'belum',
            ]);
        }

        $guruProfiles = [
            ['eksternal' => 'Tenaga Pendidik', 'jenis' => 'Guru', 'kategori' => 'GP (Guru Penggerak)', 'tugas' => 'GP (Guru Penggerak)', 'latar' => ''],
            ['eksternal' => 'Tenaga Pendidik', 'jenis' => 'Guru', 'kategori' => 'GP (Guru Penggerak)', 'tugas' => 'PP (Pengajar Praktik)', 'latar' => ''],
            ['eksternal' => 'Tenaga Pendidik', 'jenis' => 'Guru', 'kategori' => 'NoN GP (Guru Penggerak)', 'tugas' => '', 'latar' => ''],
            ['eksternal' => 'Tenaga Pendidik', 'jenis' => 'Konselor', 'kategori' => 'GP (Guru Penggerak)', 'tugas' => 'Fasil (Fasilitator)', 'latar' => ''],
            ['eksternal' => 'Tenaga Kependidikan', 'jenis' => 'Pengawas', 'kategori' => 'GP (Guru Penggerak)', 'tugas' => 'GP (Guru Penggerak)', 'latar' => 'Sertifikat GP (Guru Penggerak)'],
            ['eksternal' => 'Tenaga Kependidikan', 'jenis' => 'Pengawas', 'kategori' => 'GP (Guru Penggerak)', 'tugas' => 'Instruktur', 'latar' => 'Diklat Cawas'],
            ['eksternal' => 'Tenaga Kependidikan', 'jenis' => 'Kepala Sekolah', 'kategori' => 'GP (Guru Penggerak)', 'tugas' => 'PP (Pengajar Praktik)', 'latar' => 'Diklat Cakep'],
            ['eksternal' => 'Tenaga Kependidikan', 'jenis' => 'Kepala Sekolah', 'kategori' => 'NoN GP (Guru Penggerak)', 'tugas' => '', 'latar' => 'Lainnya'],
            ['eksternal' => 'Stakeholder', 'jenis' => 'Kepala Dinas', 'kategori' => '', 'tugas' => '', 'latar' => ''],
            ['eksternal' => 'Stakeholder', 'jenis' => 'Pemerhati Pendidikan', 'kategori' => '', 'tugas' => '', 'latar' => ''],
        ];

        foreach ($guruProfiles as $index => $profile) {
            $i = $index + 1;
            $school = $schools->isNotEmpty()
                ? $schools[($i - 1) % $schools->count()]
                : null;

            Guru::create([
                'nama_lengkap' => 'Guru Demo ' . $i,
                'email' => 'guru' . $i . '@example.com',
                'no_ktp' => '74020000000000' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'nip' => '19750101' . str_pad((string) $i, 10, '0', STR_PAD_LEFT),
                'tempat_lahir' => 'Tempat Lahir ' . $i,
                'tgl_lahir' => '1990-01-01',
                'gender' => $i % 2 == 0 ? 'Laki-laki' : 'Perempuan',
                'jabatan' => 'Jabatan ' . $i,
                'status' => $i % 2 == 0 ? 'Kawin' : 'Belum Kawin',
                'status_kepegawaian' => $i % 2 == 0 ? 'PNS' : 'Guru Bantu Sekolah',
                'agama' => $i % 3 == 0 ? 'Islam' : ($i % 3 == 1 ? 'Kristen' : 'Katolik'),
                'pendidikan' => 'S1',
                'kabupaten' => $school->kabupaten ?? ($i % 2 == 0 ? 'Kabupaten Soppeng' : 'Kabupaten Toraja Utara'),
                'satuan_pendidikan' => $school->bp_sekolah ?? ($i % 2 == 0 ? 'SPS' : 'SMA Negeri'),
                'alamat_satuan' => 'Jl. Satuan Pendidikan ' . $i . ' No. ' . $i,
                'alamat_rumah' => 'Jl. Rumah ' . $i . ' No. ' . $i,
                'no_hp' => '08123456789' . $i,
                'no_wa' => '08123456789' . $i,
                'pas_foto' => '',
                'no_rek' => '7200000000' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'jenis_bank' => $bankOptions[($i - 1) % count($bankOptions)],
                'npsn_sekolah' => $school->npsn_sekolah ?? '4030000000' . $i,
                'npwp' => '5100000000' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'nuptk' => '6200000000' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'eksternal_jabatan' => $profile['eksternal'],
                'jenis_jabatan' => $profile['jenis'],
                'kategori_jabatan' => $profile['kategori'],
                'tugas_jabatan' => $profile['tugas'],
                'latar_jabatan' => $profile['latar'],
                'is_verif' => $i <= 5 ? 'sudah' : 'belum',
            ]);
        }
    }
}
