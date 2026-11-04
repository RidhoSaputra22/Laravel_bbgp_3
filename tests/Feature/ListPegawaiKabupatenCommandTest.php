<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class ListPegawaiKabupatenCommandTest extends TestCase
{
    public function test_command_lists_pegawai_kabupaten_credentials(): void
    {
        User::query()->create([
            'name' => 'Pegawai Gowa',
            'username' => 'KABUPATEN-GOWA-ABCDE',
            'password' => 'password123',
            'role' => 'pegawai-kabupaten',
            'kabupaten' => 'Kabupaten Gowa',
        ]);
        User::query()->create([
            'name' => 'Pegawai Soppeng',
            'username' => 'KABUPATEN-SOPPENG-FGHIJ',
            'password' => 'password123',
            'role' => 'pegawai-kabupaten',
            'kabupaten' => 'Kabupaten Soppeng',
        ]);
        User::query()->create([
            'name' => 'Pegawai BBGTK',
            'username' => 'pegawai-bbgtk',
            'password' => 'password123',
            'role' => 'pegawai',
            'kabupaten' => null,
        ]);

        $this->artisan('akun:list-pegawai-kabupaten')
            ->expectsOutputToContain('Kabupaten Gowa')
            ->expectsOutputToContain('username: KABUPATEN-GOWA-ABCDE')
            ->expectsOutputToContain('password: 12345')
            ->expectsOutputToContain('Kabupaten Soppeng')
            ->expectsOutputToContain('username: KABUPATEN-SOPPENG-FGHIJ')
            ->assertExitCode(0);
    }
}
