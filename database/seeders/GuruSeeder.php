<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Guru;
use App\Models\Kabupaten;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GuruSeeder extends Seeder
{
    // private const TOTAL_GURU = 20000;
    private const TOTAL_GURU = 100;

    private const SYNC_CHUNK_SIZE = 500;

    /**
     * Seed 20,000 guru/external records with a normal kabupaten distribution.
     */
    public function run(): void
    {
        $kabupatenGroups = $this->buildKabupatenGroups();
        $kabupatenAllocations = $this->buildKabupatenAllocations($kabupatenGroups, self::TOTAL_GURU);
        $hashedPassword = Hash::make('password');

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
        $agamaOptions = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Budha', 'Konghucu'];
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
        $specialNikByRole = [
            'teacher' => '1111111111111111',
            'head' => '2222222222222222',
            'stakeholder' => '3333333333333333',
        ];
        $specialNikAssigned = array_fill_keys(array_keys($specialNikByRole), false);

        $rows = [];
        $sequence = 1;

        foreach ($kabupatenGroups as $groupIndex => $group) {
            $allocation = $kabupatenAllocations[$group['kabupaten']] ?? 0;

            for ($position = 1; $position <= $allocation; $position++, $sequence++) {
                $roleType = $this->resolveRoleType($sequence, $position, (int) $group['head_position']);
                $gender = ($sequence + $groupIndex) % 2 === 0 ? 'Laki-laki' : 'Perempuan';
                $school = $group['schools'][($position - 1) % count($group['schools'])];
                $fullName = $this->buildFullName(
                    $sequence,
                    $gender,
                    $roleType === 'head',
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
                    'no_ktp' => $this->buildNoKtp($sequence, $roleType, $specialNikByRole, $specialNikAssigned),
                    'nip' => sprintf(
                        '%04d%02d%02d%s',
                        $birthYear,
                        $birthMonth,
                        $birthDay,
                        str_pad((string) $sequence, 10, '0', STR_PAD_LEFT)
                    ),
                    'tempat_lahir' => $group['tempat_lahir'],
                    'tgl_lahir' => sprintf('%04d-%02d-%02d', $birthYear, $birthMonth, $birthDay),
                    'gender' => $gender,
                    'jabatan' => 'Guru',
                    'status' => $sequence % 3 === 0 ? 'Belum Kawin' : 'Kawin',
                    'status_kepegawaian' => match (true) {
                        $sequence % 4 === 0 => 'Guru Honorer Sekolah',
                        $sequence % 3 === 0 => 'Guru Bantu Sekolah',
                        default => 'PNS',
                    },
                    'agama' => $agamaOptions[($sequence - 1) % count($agamaOptions)],
                    'pendidikan' => $sequence % 6 === 0 ? 'S2' : 'S1',
                    'kabupaten' => $group['kabupaten'],
                    'satuan_pendidikan' => $school['nama'],
                    'alamat_satuan' => $school['alamat'],
                    'alamat_rumah' => 'Jl. Melati No. ' . (($sequence % 250) + 1) . ', ' . $group['kabupaten'],
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

                if ($roleType === 'head') {
                    $payload['jabatan'] = 'Kepala Sekolah';
                    $payload['eksternal_jabatan'] = 'Tenaga Kependidikan';
                    $payload['jenis_jabatan'] = 'Kepala Sekolah';
                    $payload['kategori_jabatan'] = $group['head_profile']['kategori_jabatan'];
                    $payload['tugas_jabatan'] = $group['head_profile']['tugas_jabatan'];
                    $payload['latar_jabatan'] = $group['head_profile']['latar_jabatan'];
                    $payload['status_kepegawaian'] = 'PNS';
                    $payload['pendidikan'] = 'S2';
                }

                if ($roleType === 'stakeholder') {
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

        $this->deleteStaleSeedRows(array_column($rows, 'no_ktp'));

        foreach (array_chunk($rows, self::SYNC_CHUNK_SIZE) as $chunk) {
            DB::transaction(function () use ($chunk, $hashedPassword): void {
                foreach ($chunk as $row) {
                    Guru::updateOrCreate(
                        ['no_ktp' => $row['no_ktp']],
                        Arr::except($row, ['no_ktp'])
                    );
                }

                $this->syncLoginAccounts($chunk, $hashedPassword);
            });
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function syncLoginAccounts(array $rows, string $hashedPassword): void
    {
        foreach ($rows as $row) {
            $role = $this->resolveLoginRole((string) $row['eksternal_jabatan']);
            $payload = [
                'name' => (string) $row['nama_lengkap'],
                'username' => $this->buildSeedUsername((string) $row['no_ktp']),
                'no_ktp' => (string) $row['no_ktp'],
                'password' => $hashedPassword,
                'role' => $role,
            ];

            User::updateOrCreate(
                ['no_ktp' => $payload['no_ktp'], 'role' => $payload['role']],
                $payload
            );

            Admin::updateOrCreate(
                ['no_ktp' => $payload['no_ktp'], 'role' => $payload['role']],
                $payload
            );
        }
    }

    private function resolveLoginRole(string $eksternalJabatan): string
    {
        return match (Str::lower(trim($eksternalJabatan))) {
            'tenaga kependidikan' => 'tenaga kependidikan',
            'stakeholder' => 'stakeholder',
            default => 'tenaga pendidik',
        };
    }

    private function buildSeedUsername(string $noKtp): string
    {
        return 'guru_seed_' . $noKtp;
    }

    /**
     * Build kabupaten groups using existing reference tables, with fallback profiles.
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildKabupatenGroups(): array
    {
        $legacyGroups = $this->legacyKabupatenGroups();
        $schoolLookup = $this->existingSchoolLookup();
        $headProfiles = $this->headProfiles();

        $kabupatenNames = Kabupaten::query()
            ->orderBy('id')
            ->pluck('name')
            ->map(fn ($name) => trim((string) $name))
            ->filter(fn (string $name) => $name !== '')
            ->values()
            ->all();

        if ($kabupatenNames === []) {
            $kabupatenNames = $this->defaultKabupatenNames();
        }

        return array_map(function (string $kabupaten, int $index) use ($legacyGroups, $schoolLookup, $headProfiles): array {
            if (isset($legacyGroups[$kabupaten])) {
                $group = $legacyGroups[$kabupaten];
                $group['schools'] = $schoolLookup[$kabupaten] ?? $group['schools'];

                return $group;
            }

            return [
                'kabupaten' => $kabupaten,
                'tempat_lahir' => $this->defaultTempatLahir($kabupaten),
                'head_position' => $index % 2 === 0 ? 2 : 1,
                'schools' => $schoolLookup[$kabupaten] ?? $this->fallbackSchools($kabupaten, $index),
                'head_profile' => $headProfiles[$index % count($headProfiles)],
            ];
        }, $kabupatenNames, array_keys($kabupatenNames));
    }

    /**
     * @param  array<int, array<string, mixed>>  $kabupatenGroups
     * @return array<string, int>
     */
    private function buildKabupatenAllocations(array $kabupatenGroups, int $total): array
    {
        $count = count($kabupatenGroups);

        if ($count === 0) {
            return [];
        }

        if ($count === 1) {
            return [$kabupatenGroups[0]['kabupaten'] => $total];
        }

        $mean = ($count - 1) / 2;
        $sigma = max(($count - 1) / 4, 1.0);
        $weights = [];
        $totalWeight = 0.0;

        foreach ($kabupatenGroups as $index => $group) {
            $distance = ($index - $mean) / $sigma;
            $weight = exp(-0.5 * ($distance ** 2));
            $weights[$group['kabupaten']] = $weight;
            $totalWeight += $weight;
        }

        $allocations = [];
        $remainders = [];
        $assigned = 0;

        foreach ($weights as $kabupaten => $weight) {
            $rawAllocation = ($weight / $totalWeight) * $total;
            $allocation = (int) floor($rawAllocation);
            $allocations[$kabupaten] = $allocation;
            $remainders[$kabupaten] = $rawAllocation - $allocation;
            $assigned += $allocation;
        }

        $remaining = $total - $assigned;
        $kabupatenOrder = array_keys($remainders);

        usort($kabupatenOrder, function (string $left, string $right) use ($remainders): int {
            $fractionComparison = $remainders[$right] <=> $remainders[$left];

            if ($fractionComparison !== 0) {
                return $fractionComparison;
            }

            return strcmp($left, $right);
        });

        for ($index = 0; $index < $remaining; $index++) {
            $kabupaten = $kabupatenOrder[$index % count($kabupatenOrder)];
            $allocations[$kabupaten]++;
        }

        return $allocations;
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
        $middleName = $lastNames[intdiv($sequence - 1, count($firstNames)) % count($lastNames)];
        $familyName = $lastNames[
            intdiv($sequence - 1, count($firstNames) * count($lastNames)) % count($lastNames)
        ];

        $title = '';

        if ($isHead) {
            $title = $gender === 'Laki-laki' ? 'Drs. ' : 'Dra. ';
        }

        return trim($title . $firstName . ' ' . $middleName . ' ' . $familyName);
    }

    private function resolveRoleType(int $sequence, int $position, int $headPosition): string
    {
        if ($sequence >= 51 && ($sequence - 51) % 100 === 0) {
            return 'stakeholder';
        }

        $cyclePosition = (($position - 1) % 25) + 1;

        if ($cyclePosition === $headPosition) {
            return 'head';
        }

        return 'teacher';
    }

    private function buildNoKtp(
        int $sequence,
        string $roleType,
        array $specialNikByRole,
        array &$specialNikAssigned
    ): string {
        if (isset($specialNikByRole[$roleType]) && ! $specialNikAssigned[$roleType]) {
            $specialNikAssigned[$roleType] = true;

            return $specialNikByRole[$roleType];
        }

        return '73' . str_pad((string) $sequence, 14, '0', STR_PAD_LEFT);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function legacyKabupatenGroups(): array
    {
        return [
            'Kabupaten Soppeng' => [
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
            'Kabupaten Takalar' => [
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
            'Kabupaten Tana Toraja' => [
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
            'Kabupaten Toraja Utara' => [
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
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function headProfiles(): array
    {
        return [
            [
                'kategori_jabatan' => 'GP (Guru Penggerak)',
                'tugas_jabatan' => 'GP (Guru Penggerak)',
                'latar_jabatan' => 'Sertifikat GP (Guru Penggerak)',
            ],
            [
                'kategori_jabatan' => 'GP (Guru Penggerak)',
                'tugas_jabatan' => 'PP (Pengajar Praktik)',
                'latar_jabatan' => 'Diklat Cakep',
            ],
            [
                'kategori_jabatan' => 'GP (Guru Penggerak)',
                'tugas_jabatan' => 'Fasil (Fasilitator)',
                'latar_jabatan' => 'Lainnya',
            ],
            [
                'kategori_jabatan' => 'NoN GP (Guru Penggerak)',
                'tugas_jabatan' => '',
                'latar_jabatan' => 'Lainnya',
            ],
        ];
    }

    /**
     * @return array<string, array<int, array<string, string>>>
     */
    private function existingSchoolLookup(): array
    {
        return Sekolah::query()
            ->select(['nama_sekolah', 'npsn_sekolah', 'alamat', 'kabupaten'])
            ->orderBy('kabupaten')
            ->orderBy('nama_sekolah')
            ->get()
            ->groupBy('kabupaten')
            ->map(function ($schools, string $kabupaten): array {
                return $schools
                    ->map(function (Sekolah $school) use ($kabupaten): array {
                        $alamat = trim((string) ($school->alamat ?? ''));

                        return [
                            'nama' => (string) $school->nama_sekolah,
                            'npsn' => (string) $school->npsn_sekolah,
                            'alamat' => $alamat !== '' ? $alamat : 'Jl. Pendidikan, ' . $kabupaten,
                        ];
                    })
                    ->values()
                    ->all();
            })
            ->all();
    }

    /**
     * Remove obsolete rows from previous runs while keeping non-seed data intact.
     *
     * @param  array<int, string>  $targetNoKtps
     */
    private function deleteStaleSeedRows(array $targetNoKtps): void
    {
        $targetNoKtpLookup = array_fill_keys($targetNoKtps, true);

        Guru::query()
            ->where('email', 'like', '%@bbgp-seeder.test')
            ->select(['id', 'no_ktp'])
            ->orderBy('id')
            ->chunkById(self::SYNC_CHUNK_SIZE, function ($gurus) use ($targetNoKtpLookup): void {
                $staleIds = $gurus
                    ->reject(fn (Guru $guru) => isset($targetNoKtpLookup[$guru->no_ktp]))
                    ->pluck('id')
                    ->all();

                if ($staleIds !== []) {
                    Guru::query()->whereIn('id', $staleIds)->delete();
                }
            });
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function fallbackSchools(string $kabupaten, int $groupIndex): array
    {
        $shortName = $this->kabupatenShortName($kabupaten);
        $schoolSeed = str_pad((string) ($groupIndex + 1), 2, '0', STR_PAD_LEFT);

        return [
            [
                'nama' => 'SD Negeri 1 ' . $shortName,
                'npsn' => '92' . $schoolSeed . '0001',
                'alamat' => 'Jl. Pendidikan No. 1, ' . $shortName,
            ],
            [
                'nama' => 'SMP Negeri 1 ' . $shortName,
                'npsn' => '92' . $schoolSeed . '0002',
                'alamat' => 'Jl. Pelajar No. 2, ' . $shortName,
            ],
            [
                'nama' => 'SMA Negeri 1 ' . $shortName,
                'npsn' => '92' . $schoolSeed . '0003',
                'alamat' => 'Jl. Poros Utama No. 3, ' . $shortName,
            ],
            [
                'nama' => 'SMK Negeri 1 ' . $shortName,
                'npsn' => '92' . $schoolSeed . '0004',
                'alamat' => 'Jl. Veteran No. 4, ' . $shortName,
            ],
            [
                'nama' => 'UPTD SPF SD Negeri 2 ' . $shortName,
                'npsn' => '92' . $schoolSeed . '0005',
                'alamat' => 'Jl. Ahmad Yani No. 5, ' . $shortName,
            ],
        ];
    }

    private function defaultTempatLahir(string $kabupaten): string
    {
        return $this->kabupatenShortName($kabupaten);
    }

    private function kabupatenShortName(string $kabupaten): string
    {
        return trim((string) preg_replace('/^(Kabupaten|Kota)\s+/i', '', $kabupaten));
    }

    /**
     * @return array<int, string>
     */
    private function defaultKabupatenNames(): array
    {
        return [
            'Kabupaten Soppeng',
            'Kabupaten Takalar',
            'Kabupaten Tana Toraja',
            'Kabupaten Toraja Utara',
            'Kabupaten Wajo',
            'Kota Makassar',
            'Kota Palopo',
            'Kota Parepare',
            'Kabupaten Luwu Timur',
            'Kabupaten Luwu Utara',
            'Kabupaten Maros',
            'Kabupaten Pangkep',
            'Kabupaten Pinrang',
            'Kabupaten Kepulauan Selayar',
            'Kabupaten Sidrap',
            'Kabupaten Sinjai',
            'Kabupaten Barru',
            'Kabupaten Bone',
            'Kabupaten Bulukumba',
            'Kabupaten Enrekang',
            'Kabupaten Gowa',
            'Kabupaten Jeneponto',
            'Kabupaten Luwu',
            'Kabupaten Bantaeng',
        ];
    }
}
