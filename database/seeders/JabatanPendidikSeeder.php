<?php

namespace Database\Seeders;

use App\Models\JabatanPendidik;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JabatanPendidikSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = ['Guru', 'Dosen', 'Tutor', 'Fasilitator', 'Pamong Belajar', 'Widya Iswara', 'Konselor', 'Instruktur'];
        
        foreach ($data as $key => $v) {
            JabatanPendidik::create([
                'name' => $v,
            ]);
        }
    }
}
