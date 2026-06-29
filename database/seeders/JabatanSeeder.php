<?php

namespace Database\Seeders;

use App\Models\Jabatan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JabatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $akun = [
            [
                'name' => 'Komite sekolah',
            ],
            [
                'name' => 'Kepala sekolah',
            ],
            [
                'name' => 'Kepala tata usaha',
            ],
            [
                'name' => 'Waka kurikulum',
            ],
            [
                'name' => 'Waka kesiswaan',
            ],
            [
                'name' => 'Waka Humas',
            ],
            [
                'name' => 'Waka sarpras',
            ],
            [
                'name' => 'SPMI',
            ],
            [
                'name' => 'Guru dan karyawan',
            ],
        ];
        

        foreach ($akun as $key => $v) {
            Jabatan::create([
                'name' => $v['name'],
            ]);
        }
    }
}
