<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = env('SEED_ADMIN_PASSWORD');
        if (! is_string($password) || strlen($password) < 12) {
            throw new RuntimeException('Set SEED_ADMIN_PASSWORD to a strong value before running AdminSeeder.');
        }

        $akun = [
            [
                'name' => 'Administrator',
                'username' => 'admin',
                'password' => $password,
                'role' => 'admin',
            ],
            [
                'name' => 'Kepala',
                'username' => 'kepala',
                'password' => $password,
                'role' => 'kepala',
            ],
            [
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'password' => $password,
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
