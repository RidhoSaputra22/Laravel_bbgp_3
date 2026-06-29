<?php

namespace Database\Seeders;

use App\Models\JabatanStakeHolder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JabatanStakeHolderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = ['Kepala Dinas', 'Kepala Bidang', 'Kepala Seksi', 'Staff', 'Pemerhati Pendidikan', 'Pers'];
        
        foreach ($data as $key => $v) {
            JabatanStakeHolder::create([
                'name' => $v,
            ]);
        }
    }
}
