<?php

namespace Database\Seeders;

use App\Models\Pendidikan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PendidikanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $akun = [
            [
                'name' => 'SMA / SMK',
            ],
            [
                'name' => 'D3',
            ],
            [
                'name' => 'S1',
            ],
            [
                'name' => 'S2',
            ],
            [
                'name' => 'S3',
            ],
        ];

        foreach ($akun as $key => $v) {
            Pendidikan::create([
                'name' => $v['name'],
            ]);
        }
    }
}
