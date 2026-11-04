<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Kabupaten;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PegawaiKabupatenSeeder extends Seeder
{
    private const ROLE = 'pegawai-kabupaten';
    private const PASSWORD = '12345';

    public function run(): void
    {
        foreach (Kabupaten::query()->orderBy('id')->pluck('name') as $kabupaten) {
            $user = User::firstOrNew([
                'role' => self::ROLE,
                'kabupaten' => $kabupaten,
            ]);

            if (! $user->exists) {
                $user->username = $this->username($kabupaten);
            }

            $user->fill([
                'name' => 'Pegawai ' . $kabupaten,
                'password' => Hash::make(self::PASSWORD),
                'no_ktp' => null,
            ])->save();

            Admin::updateOrCreate(
                ['username' => $user->username, 'role' => self::ROLE],
                [
                    'name' => $user->name,
                    'password' => $user->password,
                    'no_ktp' => null,
                ]
            );
        }
    }

    private function username(string $kabupaten): string
    {
        do {
            $suffix = collect(range(1, 5))
                ->map(fn (): string => chr(random_int(65, 90)))
                ->implode('');
            $username = Str::upper(Str::slug($kabupaten, '-')) . '-' . $suffix;
        } while (User::where('username', $username)->exists() || Admin::where('username', $username)->exists());

        return $username;
    }
}
