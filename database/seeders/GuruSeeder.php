<?php

namespace Database\Seeders;

use App\Models\Guru;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class GuruSeeder extends Seeder
{
    /**
     * Seed 100 guru/external records across four kabupaten.
     */
    public function run(): void
    {
        $kabupatenGroups = [
            [
                'kabupaten' => 'Kabupaten Soppeng',
                'tempat_lahir' => 'Watansoppeng',
                'head_position' => 2,
                'schools' => [
                    ['nama' => 'SD Negeri 1 Lalabata', 'npsn' => '91010001', 'alamat' => 'Jl. Pendidikan No. 1, Lalabata'],
                    ['nama' => 'SMP Negeri 1 Donri-Donri', 'npsn' => '91010002', 'alamat' => 'Jl. Poros Donri-Donri, Soppeng'],
                    ['nama' => 'SMA Negeri 2 Marioriawa', 'npsn' => '91010003', 'alamat' => 'Jl. Pelajar Marioriawa, Soppeng'],
                    ['nama' => 'SMK Negeri 1 Lilirilau', 'npsn' => '91010004', 'alamat' => 'Jl. Veteran Lilirilau, Soppeng'],
                    ['nama' => 'UPTD SPF SD Negeri 7 Ganra', 'npsn' => '91010005', 'alamat' => 'Jl. Ahmad Yani Ganra, Soppeng'],
                ],
                'head_profile' => [
                    'kategori_jabatan' => 'GP (Guru Penggerak)',
                    'tugas_jabatan' => 'GP (Guru Penggerak)',
                    'latar_jabatan' => 'Sertifikat GP (Guru Penggerak)',
                ],
            ],
            [
                'kabupaten' => 'Kabupaten Takalar',
                'tempat_lahir' => 'Takalar',
                'head_position' => 1,
                'schools' => [
                    ['nama' => 'SD Negeri 1 Pattallassang', 'npsn' => '91020001', 'alamat' => 'Jl. Jenderal Sudirman No. 12, Pattallassang'],
                    ['nama' => 'SMP Negeri 2 Galesong', 'npsn' => '91020002', 'alamat' => 'Jl. Poros Galesong, Takalar'],
                    ['nama' => 'SMA Negeri 5 Polongbangkeng Utara', 'npsn' => '91020003', 'alamat' => 'Jl. Pendidikan Pa\'bundukang, Takalar'],
                    ['nama' => 'SMK Negeri 1 Mangarabombang', 'npsn' => '91020004', 'alamat' => 'Jl. Poros Mangarabombang, Takalar'],
                    ['nama' => 'UPTD SPF SD Negeri Pa\'lalakkang', 'npsn' => '91020005', 'alamat' => 'Jl. Bonto Lebang, Takalar'],
                ],
                'head_profile' => [
                    'kategori_jabatan' => 'GP (Guru Penggerak)',
                    'tugas_jabatan' => 'PP (Pengajar Praktik)',
                    'latar_jabatan' => 'Diklat Cakep',
                ],
            ],
            [
                'kabupaten' => 'Kabupaten Tana Toraja',
                'tempat_lahir' => 'Makale',
                'head_position' => 2,
                'schools' => [
                    ['nama' => 'SD Negeri 3 Makale', 'npsn' => '91030001', 'alamat' => 'Jl. Pongtiku No. 7, Makale'],
                    ['nama' => 'SMP Negeri 1 Rembon', 'npsn' => '91030002', 'alamat' => 'Jl. Poros Rembon, Tana Toraja'],
                    ['nama' => 'SMA Negeri 4 Sangalla', 'npsn' => '91030003', 'alamat' => 'Jl. Sangalla Selatan, Tana Toraja'],
                    ['nama' => 'SMK Negeri 2 Makale Utara', 'npsn' => '91030004', 'alamat' => 'Jl. Andi Mappanyukki, Tana Toraja'],
                    ['nama' => 'UPTD SPF SD Negeri 5 Mengkendek', 'npsn' => '91030005', 'alamat' => 'Jl. Poros Mengkendek, Tana Toraja'],
                ],
                'head_profile' => [
                    'kategori_jabatan' => 'GP (Guru Penggerak)',
                    'tugas_jabatan' => 'Fasil (Fasilitator)',
                    'latar_jabatan' => 'Lainnya',
                ],
            ],
            [
                'kabupaten' => 'Kabupaten Toraja Utara',
                'tempat_lahir' => 'Rantepao',
                'head_position' => 1,
                'schools' => [
                    ['nama' => 'SD Negeri 1 Rantepao', 'npsn' => '91040001', 'alamat' => 'Jl. Ahmad Yani No. 4, Rantepao'],
                    ['nama' => 'SMP Negeri 2 Kesu', 'npsn' => '91040002', 'alamat' => 'Jl. Poros Kesu, Toraja Utara'],
                    ['nama' => 'SMA Negeri 1 Sesean', 'npsn' => '91040003', 'alamat' => 'Jl. Poros Sesean, Toraja Utara'],
                    ['nama' => 'SMK Negeri 1 Tallunglipu', 'npsn' => '91040004', 'alamat' => 'Jl. Emmy Saelan Tallunglipu, Toraja Utara'],
                    ['nama' => 'UPTD SPF SD Negeri 3 Tikala', 'npsn' => '91040005', 'alamat' => 'Jl. Poros Tikala, Toraja Utara'],
                ],
                'head_profile' => [
                    'kategori_jabatan' => 'NoN GP (Guru Penggerak)',
                    'tugas_jabatan' => '',
                    'latar_jabatan' => 'Lainnya',
                ],
            ],
        ];

        $maleFirstNames = [
            'Ahmad', 'Andi', 'Rizal', 'Fadli', 'Ilham',
            'Yusuf', 'Rahmat', 'Arman', 'Dedi', 'Fikri',
        ];
        $femaleFirstNames = [
            'Siti', 'Nurhayati', 'Aisyah', 'Fitriani', 'Rahma',
            'Indah', 'Dewi', 'Sri', 'Marlina', 'Wahyuni',
        ];
        $lastNames = [
            'Saputra', 'Pratama', 'Ramadhani', 'Nursalam', 'Hidayat',
            'Purnama', 'Lestari', 'Permata', 'Syafitri', 'Maulana',
        ];
        $agamaOptions = ['Islam', 'Kristen', 'Katolik', 'Hindu'];
        $teacherTasks = [
            'GP (Guru Penggerak)',
            'PP (Pengajar Praktik)',
            'Fasil (Fasilitator)',
            'Instruktur',
        ];
        $teacherBanks = [
            'Bank BRI',
            'Bank BNI',
            'Bank Mandiri',
            'Bank Syariah Indonesia',
        ];
        $specialNikMap = [
            1 => '1111111111111111',
            26 => '2222222222222222',
            51 => '3333333333333333',
        ];

        $rows = [];
        $sequence = 1;

        foreach ($kabupatenGroups as $groupIndex => $group) {
            for ($position = 1; $position <= 25; $position++, $sequence++) {
                $gender = ($sequence + $groupIndex) % 2 === 0 ? 'Laki-laki' : 'Perempuan';
                $isHead = $position === $group['head_position'];
                $isStakeholder = $sequence === 51;
                $school = $group['schools'][($position - 1) % count($group['schools'])];

                $fullName = $this->buildFullName(
                    $sequence,
                    $gender,
                    $isHead,
                    $maleFirstNames,
                    $femaleFirstNames,
                    $lastNames
                );

                $birthYear = 1980 + (($sequence + $groupIndex) % 16);
                $birthMonth = (($sequence + 2) % 12) + 1;
                $birthDay = (($sequence + 10) % 28) + 1;
                $kategoriGuru = $position % 5 === 0 ? 'NoN GP (Guru Penggerak)' : 'GP (Guru Penggerak)';
                $tugasGuru = $kategoriGuru === 'GP (Guru Penggerak)'
                    ? $teacherTasks[($position - 1) % count($teacherTasks)]
                    : '';

                $payload = [
                    'nama_lengkap' => $fullName,
                    'email' => Str::slug($fullName, '.') . $sequence . '@bbgp-seeder.test',
                    'no_ktp' => $specialNikMap[$sequence] ?? ('73' . str_pad((string) $sequence, 14, '0', STR_PAD_LEFT)),
                    'nip' => sprintf('%04d%02d%02d%s', $birthYear, $birthMonth, $birthDay, str_pad((string) $sequence, 10, '0', STR_PAD_LEFT)),
                    'tempat_lahir' => $group['tempat_lahir'],
                    'tgl_lahir' => sprintf('%04d-%02d-%02d', $birthYear, $birthMonth, $birthDay),
                    'gender' => $gender,
                    'jabatan' => $isHead ? 'Kepala Sekolah' : 'Guru',
                    'status' => $sequence % 3 === 0 ? 'Belum Kawin' : 'Kawin',
                    'status_kepegawaian' => match (true) {
                        $isHead => 'PNS',
                        $sequence % 4 === 0 => 'Guru Honorer Sekolah',
                        $sequence % 3 === 0 => 'Guru Bantu Sekolah',
                        default => 'PNS',
                    },
                    'agama' => $agamaOptions[($sequence - 1) % count($agamaOptions)],
                    'pendidikan' => $isHead || $sequence % 6 === 0 ? 'S2' : 'S1',
                    'kabupaten' => $group['kabupaten'],
                    'satuan_pendidikan' => $school['nama'],
                    'alamat_satuan' => $school['alamat'],
                    'alamat_rumah' => 'Jl. Melati No. ' . (($sequence % 25) + 1) . ', ' . $group['kabupaten'],
                    'no_hp' => '0813' . str_pad((string) (70000000 + $sequence), 8, '0', STR_PAD_LEFT),
                    'no_wa' => '0812' . str_pad((string) (80000000 + $sequence), 8, '0', STR_PAD_LEFT),
                    'pas_foto' => '',
                    'no_rek' => '700' . str_pad((string) $sequence, 7, '0', STR_PAD_LEFT),
                    'jenis_bank' => $teacherBanks[($sequence - 1) % count($teacherBanks)],
                    'npsn_sekolah' => $school['npsn'],
                    'npwp' => '91' . str_pad((string) $sequence, 13, '0', STR_PAD_LEFT),
                    'nuptk' => '99' . str_pad((string) $sequence, 14, '0', STR_PAD_LEFT),
                    'eksternal_jabatan' => 'Tenaga Pendidik',
                    'jenis_jabatan' => 'Guru',
                    'kategori_jabatan' => $kategoriGuru,
                    'tugas_jabatan' => $tugasGuru,
                    'latar_jabatan' => '',
                    'is_verif' => 'sudah',
                ];

                if ($isHead) {
                    $payload['jabatan'] = 'Kepala Sekolah';
                    $payload['eksternal_jabatan'] = 'Tenaga Kependidikan';
                    $payload['jenis_jabatan'] = 'Kepala Sekolah';
                    $payload['kategori_jabatan'] = $group['head_profile']['kategori_jabatan'];
                    $payload['tugas_jabatan'] = $group['head_profile']['tugas_jabatan'];
                    $payload['latar_jabatan'] = $group['head_profile']['latar_jabatan'];
                    $payload['status_kepegawaian'] = 'PNS';
                    $payload['pendidikan'] = 'S2';
                }

                if ($isStakeholder) {
                    $payload['jabatan'] = 'Staff';
                    $payload['eksternal_jabatan'] = 'Stakeholder';
                    $payload['jenis_jabatan'] = 'Staff';
                    $payload['kategori_jabatan'] = '';
                    $payload['tugas_jabatan'] = '';
                    $payload['latar_jabatan'] = '';
                    $payload['status_kepegawaian'] = 'PNS';
                }

                $rows[] = $payload;
            }
        }

        foreach ($rows as $row) {
            Guru::updateOrCreate(
                ['no_ktp' => $row['no_ktp']],
                Arr::except($row, ['no_ktp'])
            );
        }
    }

    /**
     * Build a deterministic full name so seeding stays stable across reruns.
     */
    private function buildFullName(
        int $sequence,
        string $gender,
        bool $isHead,
        array $maleFirstNames,
        array $femaleFirstNames,
        array $lastNames
    ): string {
        $firstNames = $gender === 'Laki-laki' ? $maleFirstNames : $femaleFirstNames;
        $firstName = $firstNames[($sequence - 1) % count($firstNames)];
        $lastName = $lastNames[intdiv($sequence - 1, count($firstNames)) % count($lastNames)];

        $title = '';

        if ($isHead) {
            $title = $gender === 'Laki-laki' ? 'Drs. ' : 'Dra. ';
        }

        return trim($title . $firstName . ' ' . $lastName);
    }
}
