<?php

namespace Database\Seeders;

use App\Models\Provinsi;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProvinsiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $daftar_provinsi = [
            [
                "name" => "ACEH"
            ],
            [
                "name" => "SUMATERA UTARA"
            ],
            [
                "name" => "SUMATERA BARAT"
            ],
            [
                "name" => "RIAU"
            ],
            [
                "name" => "JAMBI"
            ],
            [
                "name" => "SUMATERA SELATAN"
            ],
            [
                "name" => "BENGKULU"
            ],
            [
                "name" => "LAMPUNG"
            ],
            [
                "name" => "KEPULAUAN BANGKA BELITUNG"
            ],
            [
                "name" => "KEPULAUAN RIAU"
            ],
            [
                "name" => "DKI JAKARTA"
            ],
            [
                "name" => "JAWA BARAT"
            ],
            [
                "name" => "JAWA TENGAH"
            ],
            [
                "name" => "DI YOGYAKARTA"
            ],
            [
                "name" => "JAWA TIMUR"
            ],
            [
                "name" => "BANTEN"
            ],
            [
                "name" => "BALI"
            ],
            [
                "name" => "NUSA TENGGARA BARAT"
            ],
            [
                "name" => "NUSA TENGGARA TIMUR"
            ],
            [
                "name" => "KALIMANTAN BARAT"
            ],
            [
                "name" => "KALIMANTAN TENGAH"
            ],
            [
                "name" => "KALIMANTAN SELATAN"
            ],
            [
                "name" => "KALIMANTAN TIMUR"
            ],
            [
                "name" => "KALIMANTAN UTARA"
            ],
            [
                "name" => "SULAWESI UTARA"
            ],
            [
                "name" => "SULAWESI TENGAH"
            ],
            [
                "name" => "SULAWESI SELATAN"
            ],
            [
                "name" => "SULAWESI TENGGARA"
            ],
            [
                "name" => "GORONTALO"
            ],
            [
                "name" => "SULAWESI BARAT"
            ],
            [
                "name" => "MALUKU"
            ],
            [
                "name" => "MALUKU UTARA"
            ],
            [
                "name" => "PAPUA BARAT"
            ],
            [
                "name" => "PAPUA"
            ]
        ];

        foreach ($daftar_provinsi as $d) {
            Provinsi::create([
                'name' => $d['name']
            ]);
        }
    }
}
