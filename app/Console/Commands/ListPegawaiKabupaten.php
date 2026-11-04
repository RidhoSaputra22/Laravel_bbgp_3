<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ListPegawaiKabupaten extends Command
{
    protected $signature = 'akun:list-pegawai-kabupaten';

    protected $description = 'Menampilkan akun pegawai-kabupaten beserta kredensial awalnya';

    public function handle(): int
    {
        $users = User::query()
            ->where('role', 'pegawai-kabupaten')
            ->whereNotNull('kabupaten')
            ->orderBy('kabupaten')
            ->get(['kabupaten', 'username']);

        foreach ($users as $user) {
            $this->line($user->kabupaten);
            $this->line('username: ' . $user->username);
            $this->line('password: 12345');
            $this->newLine();
        }

        if ($users->isEmpty()) {
            $this->warn('Tidak ada akun pegawai-kabupaten.');
        }

        return self::SUCCESS;
    }
}
