<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DataAkunSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['name' => 'Rizky Kurniawan', 'username' => 'kepegawaian', 'role' => 'kepegawaian', 'no_ktp' => '7371010101800005'],
            ['name' => 'Maya Sari', 'username' => 'keuangan', 'role' => 'keuangan', 'no_ktp' => '7371010201850006'],
            ['name' => 'Fahrul Ilham', 'username' => 'kegiatan', 'role' => 'kegiatan', 'no_ktp' => '7371010301820007'],
            ['name' => 'Dwi Lestari', 'username' => 'database', 'role' => 'database', 'no_ktp' => '7371010401880008'],
            ['name' => 'Andi Nurul Fadilah', 'username' => 'andhinurulfadilah', 'role' => 'tenaga kependidikan', 'no_ktp' => '7372761008840009'],
        ];

        foreach ($accounts as $account) {
            $payload = $account + ['password' => Hash::make('12345')];

            Admin::updateOrCreate(
                ['username' => $account['username'], 'role' => $account['role']],
                $payload
            );

            User::updateOrCreate(
                ['username' => $account['username'], 'role' => $account['role']],
                $payload
            );
        }
    }
}
