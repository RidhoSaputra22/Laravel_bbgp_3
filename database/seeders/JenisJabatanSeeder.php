<?php

namespace Database\Seeders;

use App\Models\JenisJabatan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JenisJabatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = ['GP (Guru Penggerak)', 'NoN GP (Guru Penggerak)'];

        foreach ($data as $key => $v) {
            JenisJabatan::create([
                'name' => $v,
            ]);
        }
    }
}
