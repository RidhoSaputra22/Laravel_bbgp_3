<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PanitiaSeeder extends Seeder
{
    public function run(): void
    {
        foreach (range(1, 5) as $number) {
            $username = 'PANITIA ' . $number;
            $account = [
                'name' => $username,
                'username' => $username,
                'role' => 'panitia',
                'no_ktp' => null,
                'password' => Hash::make('BBGTKHEBAT'),
            ];

            Admin::updateOrCreate(
                ['username' => $username, 'role' => 'panitia'],
                $account
            );

            User::updateOrCreate(
                ['username' => $username, 'role' => 'panitia'],
                $account
            );
        }
    }
}
