<?php

namespace Database\Seeders;

use App\Models\SatuanPendidikan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusPendidikanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $akun = [
            [
                'name' => 'TPA',
            ],
            [
                'name' => 'SPS',
            ],
            [
                'name' => 'KB',
            ],
            [
                'name' => 'TK Negeri',
            ],
            [
                'name' => 'TK Swasta',
            ],
            [
                'name' => 'SKB',
            ],
            [
                'name' => 'PKBM',
            ],
            [
                'name' => 'SD Negeri',
            ],
            [
                'name' => 'SD Swasta',
            ],
            [
                'name' => 'SMP Negeri',
            ],
            [
                'name' => 'SMP Swasta',
            ],
            [
                'name' => 'SMA Negeri',
            ],
            [
                'name' => 'SMA Swasta',
            ],
            [
                'name' => 'SMK Negeri',
            ],
            [
                'name' => 'SMK Swasta',
            ],
            [
                'name' => 'SLB',
            ],
        ];

        foreach ($akun as $key => $v) {
            SatuanPendidikan::create([
                'name' => $v['name'],
            ]);
        }
    }
}
