<?php

namespace Database\Seeders;

use App\Models\JenisTugas;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JenisTugasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = ['GP (Guru Penggerak)', 'PP (Pengajar Praktik)', 'Fasil (Fasilitator)', 'Instruktur'];

        foreach ($data as $key => $v) {
            JenisTugas::create([
                'name' => $v,
            ]);
        }
    }
    
}
