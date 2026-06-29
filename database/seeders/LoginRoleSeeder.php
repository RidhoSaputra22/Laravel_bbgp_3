<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Guru;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LoginRoleSeeder extends Seeder
{
    /**
     * Seed akun untuk role yang muncul pada login user (/auth).
     */
    public function run(): void
    {
        $akun = [
            [
                'name' => 'Tenaga Pendidik',
                'username' => 'tenaga pendidik',
                'no_ktp' => '1111111111111111',
                'password' => 'tenaga_pendidik',
                'role' => 'tenaga pendidik',
            ],
            [
                'name' => 'Tenaga Kependidikan',
                'username' => 'tenaga kependidikan',
                'no_ktp' => '2222222222222222',
                'password' => 'tenaga_kependidikan',
                'role' => 'tenaga kependidikan',
            ],
            [
                'name' => 'Stakeholder',
                'username' => 'stakeholder',
                'no_ktp' => '3333333333333333',
                'password' => 'stakeholder',
                'role' => 'stakeholder',
            ],
            [
                'name' => 'Pegawai BBGTK',
                'username' => 'pegawai',
                'no_ktp' => '4444444444444444',
                'password' => 'pegawai',
                'role' => 'pegawai',
            ],
        ];

        foreach ($akun as $v) {
            $payload = [
                'name' => $v['name'],
                'username' => $v['username'],
                'no_ktp' => $v['no_ktp'],
                'password' => Hash::make($v['password']),
                'role' => $v['role'],
            ];

            Admin::updateOrCreate(
                ['username' => $v['username'], 'role' => $v['role']],
                $payload
            );

            User::updateOrCreate(
                ['username' => $v['username'], 'role' => $v['role']],
                $payload
            );

            if ($v['role'] === 'pegawai') {
                Pegawai::updateOrCreate(
                    ['no_ktp' => $v['no_ktp']],
                    [
                        'username' => $v['username'],
                        'nama_lengkap' => $v['name'],
                        'email' => 'pegawai@bbgtk.test',
                        'no_ktp' => $v['no_ktp'],
                        'nip' => '444444444444444444',
                        'tempat_lahir' => 'Makassar',
                        'tgl_lahir' => '1990-04-04',
                        'gender' => 'Laki-laki',
                        'jabatan' => 'Staf BBGTK',
                        'jenis_pegawai' => 'BBGP',
                        'status' => 'Belum Kawin',
                        'status_kepegawaian' => 'PNS',
                        'agama' => 'Islam',
                        'pendidikan' => 'S1',
                        'kabupaten' => 'Kota Makassar',
                        'satuan_pendidikan' => 'BBGTK Sulawesi Selatan',
                        'alamat_satuan' => 'Kantor BBGTK Sulawesi Selatan',
                        'alamat_rumah' => 'Makassar',
                        'no_hp' => '081244444444',
                        'no_wa' => '081244444444',
                        'pas_foto' => '',
                        'instansi' => 'Kantor BBGTK SulSel',
                        'golongan' => 'III/a',
                        'jenis_bank' => 'BRI',
                        'no_rek' => '4444444444',
                        'is_verif' => 'sudah',
                    ]
                );

                continue;
            }

            Guru::updateOrCreate(
                ['no_ktp' => $v['no_ktp']],
                [
                    'nama_lengkap' => $v['name'],
                    'email' => str_replace(' ', '.', $v['username']) . '@bbgtk.test',
                    'no_ktp' => $v['no_ktp'],
                    'nip' => match ($v['role']) {
                        'tenaga pendidik' => '111111111111111111',
                        'tenaga kependidikan' => '222222222222222222',
                        default => '333333333333333333',
                    },
                    'tempat_lahir' => 'Makassar',
                    'tgl_lahir' => match ($v['role']) {
                        'tenaga pendidik' => '1990-01-01',
                        'tenaga kependidikan' => '1990-02-02',
                        default => '1990-03-03',
                    },
                    'gender' => 'Laki-laki',
                    'jabatan' => match ($v['role']) {
                        'tenaga pendidik' => 'Guru',
                        'tenaga kependidikan' => 'Pengawas',
                        default => 'Kepala Dinas',
                    },
                    'status' => 'Belum Kawin',
                    'status_kepegawaian' => 'PNS',
                    'agama' => 'Islam',
                    'pendidikan' => 'S1',
                    'kabupaten' => 'Kota Makassar',
                    'satuan_pendidikan' => 'BBGTK Sulawesi Selatan',
                    'alamat_satuan' => 'BBGTK Sulawesi Selatan',
                    'alamat_rumah' => 'Makassar',
                    'no_hp' => match ($v['role']) {
                        'tenaga pendidik' => '081211111111',
                        'tenaga kependidikan' => '081222222222',
                        default => '081233333333',
                    },
                    'no_wa' => match ($v['role']) {
                        'tenaga pendidik' => '081211111111',
                        'tenaga kependidikan' => '081222222222',
                        default => '081233333333',
                    },
                    'pas_foto' => '',
                    'no_rek' => match ($v['role']) {
                        'tenaga pendidik' => '1111111111',
                        'tenaga kependidikan' => '2222222222',
                        default => '3333333333',
                    },
                    'jenis_bank' => 'BRI',
                    'npsn_sekolah' => '-',
                    'npwp' => match ($v['role']) {
                        'tenaga pendidik' => '111111111111111',
                        'tenaga kependidikan' => '222222222222222',
                        default => '333333333333333',
                    },
                    'nuptk' => match ($v['role']) {
                        'tenaga pendidik' => '1111111111111111',
                        'tenaga kependidikan' => '2222222222222222',
                        default => '3333333333333333',
                    },
                    'eksternal_jabatan' => ucwords($v['role']),
                    'jenis_jabatan' => match ($v['role']) {
                        'tenaga pendidik' => 'Guru',
                        'tenaga kependidikan' => 'Pengawas',
                        default => 'Kepala Dinas',
                    },
                    'kategori_jabatan' => match ($v['role']) {
                        'stakeholder' => '',
                        default => 'GP (Guru Penggerak)',
                    },
                    'tugas_jabatan' => match ($v['role']) {
                        'stakeholder' => '',
                        default => 'GP (Guru Penggerak)',
                    },
                    'latar_jabatan' => match ($v['role']) {
                        'tenaga kependidikan' => 'Sertifikat GP (Guru Penggerak)',
                        default => '',
                    },
                    'is_verif' => 'sudah',
                ]
            );
        }
    }
}
