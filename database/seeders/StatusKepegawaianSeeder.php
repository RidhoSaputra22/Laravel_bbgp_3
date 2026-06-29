<?php

namespace Database\Seeders;

use App\Models\Kepegawaian;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusKepegawaianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $akun = [
            [
                'name' => 'PNS',
            ],
            [
                'name' => 'Guru Honorer Sekolah',
            ],
            [
                'name' => 'Guru Bantu Sekolah',
            ],
            [
                'name' => 'GTT / PTTkb / Kota',
            ],
            [
                'name' => 'GTT / PTT Provinsi',
            ],
            [
                'name' => 'GTT / PTY',
            ],
        ];

        foreach ($akun as $key => $v) {
            Kepegawaian::create([
                'name' => $v['name'],
            ]);
        }
    }
}
