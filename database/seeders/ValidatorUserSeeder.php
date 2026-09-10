<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Guru;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ValidatorUserSeeder extends Seeder
{
    /**
     * Membuat lima akun Stakeholder yang dapat mengakses panel validator.
     *
     * Kredensial default:
     * username: validator1 sampai validator5
     * password: validator1 sampai validator5
     */
    public function run(): void
    {
        foreach (range(1, 5) as $number) {
            $name = 'Validator '.$number;
            $username = 'validator'.$number;
            $noKtp = '990000000000000'.$number;
            $password = $username;

            $payload = [
                'name' => $name,
                'username' => $username,
                'no_ktp' => $noKtp,
                'password' => Hash::make($password),
                'role' => 'stakeholder',
            ];

            User::updateOrCreate(
                ['username' => $username, 'role' => 'stakeholder'],
                $payload
            );

            Admin::updateOrCreate(
                ['username' => $username, 'role' => 'stakeholder'],
                $payload
            );

            Guru::updateOrCreate(
                ['no_ktp' => $noKtp],
                [
                    'nama_lengkap' => $name,
                    'email' => $username.'@bbgtk.test',
                    'no_ktp' => $noKtp,
                    'nip' => '199000000000000'.$number,
                    'tempat_lahir' => 'Makassar',
                    'tgl_lahir' => '1990-01-0'.$number,
                    'gender' => $number % 2 === 0 ? 'Perempuan' : 'Laki-laki',
                    'jabatan' => 'Validator',
                    'status' => 'Belum Kawin',
                    'status_kepegawaian' => 'PNS',
                    'agama' => 'Islam',
                    'pendidikan' => 'S1',
                    'kabupaten' => 'Kota Makassar',
                    'satuan_pendidikan' => 'BBGTK Sulawesi Selatan',
                    'alamat_satuan' => 'BBGTK Sulawesi Selatan',
                    'alamat_rumah' => 'Makassar',
                    'no_hp' => '08129900000'.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                    'no_wa' => '08129900000'.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                    'pas_foto' => '',
                    'no_rek' => '990000000'.$number,
                    'jenis_bank' => 'Bank BRI',
                    'npsn_sekolah' => '-',
                    'npwp' => '99000000000000'.$number,
                    'nuptk' => '990000000000000'.$number,
                    'eksternal_jabatan' => 'Stakeholder',
                    'jenis_jabatan' => 'Validator',
                    'kategori_jabatan' => '',
                    'tugas_jabatan' => '',
                    'latar_jabatan' => '',
                    'is_verif' => 'sudah',
                ]
            );
        }
    }
}
