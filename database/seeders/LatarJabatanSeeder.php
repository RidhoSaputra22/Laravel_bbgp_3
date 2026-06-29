<?php

namespace Database\Seeders;

use App\Models\LatarJabatan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LatarJabatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'Sertifikat GP (Guru Penggerak)', 'Diklat Cawas', 'Diklat Cakep', 'Lainnya'
        ];
        foreach ($data as $key => $v) {
            LatarJabatan::create([
                'name' => $v,
            ]);
        }
    }
}
