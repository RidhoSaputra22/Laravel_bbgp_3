<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $akun = [
            [
                'name' => 'Administrator',
                'username' => 'admin',
                'password' => 'admin',
                'role' => 'admin',
            ],
            [
                'name' => 'Kepala',
                'username' => 'kepala',
                'password' => 'kepala',
                'role' => 'kepala',
            ],
            [
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'password' => 'superadmin',
                'role' => 'superadmin',
            ],
        ];

        foreach ($akun as $v) {
            $payload = [
                'name' => $v['name'],
                'username' => $v['username'],
                'no_ktp' => null,
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
        }
    }
}
