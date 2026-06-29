<?php

namespace Database\Seeders;

use App\Models\Kabupaten;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KabupatenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $akun = [
            [
                'name' => 'Kabupaten Soppeng',
            ],
            [
                'name' => 'Kabupaten Takalar',
            ],
            [
                'name' => 'Kabupaten Tana Toraja',
            ],
            [
                'name' => 'Kabupaten Toraja Utara',
            ],
            [
                'name' => 'Kabupaten Wajo',
            ],
            [
                'name' => 'Kota Makassar',
            ],
            [
                'name' => 'Kota Palopo',
            ],
            [
                'name' => 'Kota Parepare',
            ],
            [
                'name' => 'Kabupaten Luwu Timur',
            ],
            [
                'name' => 'Kabupaten Luwu Utara',
            ],
            [
                'name' => 'Kabupaten Maros',
            ],
            [
                'name' => 'Kabupaten Pangkep',
            ],
            [
                'name' => 'Kabupaten Pinrang',
            ],
            [
                'name' => 'Kabupaten Kepulauan Selayar',
            ],
            [
                'name' => 'Kabupaten Sidrap',
            ],
            [
                'name' => 'Kabupaten Sinjai',
            ],
            [
                'name' => 'Kabupaten Barru',
            ],
            [
                'name' => 'Kabupaten Bone',
            ],
            [
                'name' => 'Kabupaten Bulukumba',
            ],
            [
                'name' => 'Kabupaten Enrekang',
            ],
            [
                'name' => 'Kabupaten Gowa',
            ],
            [
                'name' => 'Kabupaten Jeneponto',
            ],
            [
                'name' => 'Kabupaten Luwu',
            ],
            [
                'name' => 'Kabupaten Bantaeng',
            ],
        ];
        foreach ($akun as $key => $v) {
            Kabupaten::create([
                'name' => $v['name'],
            ]);
        }
    }
}
