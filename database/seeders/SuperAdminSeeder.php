<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'name' => 'Kepala Balai',
                'username' => 'KEPALABALAI',
                'password' => 'SULSELCERDAS',
            ],
            [
                'name' => 'Super Admin',
                'username' => 'SUPERADMIN',
                'password' => 'TOPSECRET',
            ],
        ];

        foreach ($accounts as $account) {
            $payload = [
                'name' => $account['name'],
                'username' => $account['username'],
                'no_ktp' => null,
                'password' => Hash::make($account['password']),
                'role' => 'superadmin',
            ];

            Admin::updateOrCreate(
                ['username' => $account['username'], 'role' => 'superadmin'],
                $payload
            );

            User::updateOrCreate(
                ['username' => $account['username'], 'role' => 'superadmin'],
                $payload
            );
        }
    }
}
