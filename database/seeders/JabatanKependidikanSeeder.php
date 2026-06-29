<?php

namespace Database\Seeders;

use App\Models\JabatanKependidikan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JabatanKependidikanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = ['Pengawas', 'Kepala Sekolah', 'Tata Usaha', 'Pendidik', 'Laboran', 'Pustakawan'];
        
        foreach ($data as $key => $v) {
            JabatanKependidikan::create([
                'name' => $v,
            ]);
        }
    }
}
