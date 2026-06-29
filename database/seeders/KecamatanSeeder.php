<?php

namespace Database\Seeders;

use App\Models\Kecamatan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KecamatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $daftar_kecamatan = [
            "Bacukiki", "Ujung", "Baculkiki Barat", "Soreang", "Wara", "Bara", "Wara Timur", "Wara Utara", "Telluwanua",
            "Wara Selatan", "Wara Barat", "Mungkajang", "Sendana", "Bontomanai", "Bontosikuyu", "Benteng", "Bontomatene",
            "Bontoharu", "Taka Bonerate", "Buki", "Pasimasunggu", "Pasimasunggu Timur", "Pasimarannu", "Pasilambena",
            "Bantaeng", "Pa`jukukang", "Tompobulu", "Bissappu", "Ere Merasa", "Gantarang Keke", "Ulu Ere", "Sinoa", "Malili",
            "Towuti", "Burau", "Tomoni", "Wotu", "Mangkutana", "Nuha", "Angkona", "Wasuponda", "Tomoni Timur", "Kalaena",
            "Lalabata", "Marioriwawo", "Lili Rilau", "Liliriaja", "Donri-Donri", "Mario Riawa", "Ganra", "Citta", "Rantepao",
            "Sa`dan", "Kesu", "Tallunglipu", "Buntu Pepasan", "Sopai", "Nanggala", "Tikala", "Sesean", "Rindingallo", "Buntao",
            "Dende` Piongan Napo", "Rantebua", "Kapala Pitu", "Baruppu", "Awan Rante karua", "Balusu", "Sanggalangi",
            "Sesean Suloara", "Tondon", "Bangkelekila", "Barru", "Tanete Riaja", "Tanete Rilau", "Mallusetasi", "Pujananting",
            "Soppeng Riaja", "Balusu", "Enrekang", "Maiwa", "Baraka", "Anggeraja", "Buntu Batu", "Curio", "Alla", "Cendana",
            "Malua", "Bungin", "Masalle", "Baroko", "Maritengae", "Panca Rijang", "Baranti", "Watang Pulu", "Pitu Riase",
            "Pitu Riawa", "Dua Pitue", "Tellu Limpoe", "Panca Lautang", "Kulo", "Watang Sidenreng", "Masamba", "Baebunta",
            "Malangke Barat", "Malangke", "Sukamaju", "Tana Lili", "Seko", "Mappedeceng", "Sabbang Selatan", "Bone-Bone",
            "Baebunta Selatan", "Sabbang", "Sukamaju Selatan", "Rongkong", "Rampi", "Polombangkeng Utara", "Pattallassang",
            "Mangarabombang", "Galesong Utara", "Polombangkeng Selatan", "Galesong", "Galesong Selatan", "Sanrobone",
            "Kepulauan Tanakeke", "Mappakasunggu", "Lembang", "Watang Sawitto", "Duampanua", "Patampanua", "Mattiro Sompe",
            "Paleteang", "Mattiro Bulu", "Suppa", "Tiroang", "Cempa", "Lanrisang", "Batulappa", "Mengkendek", "Makale",
            "Rembon", "Gandangbatu Sillanan", "Bittuang", "Rantetayo", "Makale Selatan", "Makale Utara", "Masanda",
            "Saluputti", "Malimbong Balepe", "Bonggakaradeng", "Mappak", "Simbuang", "Sangalla Selatan", "Sangalla",
            "Rano", "Kurra", "Sangalla Utara", "Turikale", "Mandai", "Tanralili", "Maros Utara / Bontoa", "Bantimurung",
            "Simbang", "Cenrana", "Moncongloe", "Camba", "Lau", "Maros Baru", "Mallawa", "Tompobulu", "Marusu", "Sinjai Utara",
            "Sinjai Selatan", "Sinjai Tengah", "Tellu Limpoe", "Sinjai Timur", "Sinjai Barat", "Bulupoddo", "Sinjai Borong",
            "Pulau Sembilan", "Bua", "Ponrang", "Larompong", "Ponrang Selatan", "Belopa", "Walenrang Utara", "Suli",
            "Larompong Selatan", "Lamasi", "Walenrang", "Bajo", "Lamasi Timur", "Bua Ponrang (Bupon)", "Latimojong",
            "Walenrang Timur", "Belopa Utara", "Bajo Barat", "Bassesangtempe Utara", "Suli Barat", "Kamanre", "Walenrang Barat",
            "Bassesangtempe", "Binamu", "Bangkala", "Bontoramba", "Rumbia", "Tamalatea", "Bangkala Barat", "Turatea",
            "Arungkeke", "Kelara", "Tarowang", "Batang", "Bungoro", "Pangkajene", "Labakkang", "Ma`rang", "Minasatene",
            "Liukang Tangaya", "Liukang Kalmas", "Segeri", "Liukang Tupabbiring Utara", "Tondong Tallasa", "Balocci",
            "Liukang Tapabbiring", "Mandalle", "Tempe", "Majauleng", "Pammana", "Pitumpanua", "Tanasitolo", "Belawa",
            "Bola", "Sabbangparu", "Takkalala", "Keera", "Sajo Anging", "Maniang Pajo", "Gilireng", "Penrang", "Gantarang",
            "Bulukumpa", "Ujung Bulu", "Ujung Loe", "Herlang", "Kajang", "Bontotiro", "Kindang", "Rilau Ale", "Bontobahari",
            "Somba Opu", "Pallangga", "Bajeng", "Bontonompo", "Bontomarannu", "Tinggi Moncong", "Barombong", "Tombolo Pao",
            "Pattallassang", "Tompobulu", "Biring Bulu", "Bajeng Barat", "Manuju", "Bontonompo Selatan", "Bontolempangan",
            "Parang Loe", "Bungaya", "Parigi", "Tanete Riattang", "Kahu", "Dua Boccoe", "Tellu Siattinge", "Mare", "Kajuara",
            "Sibulue", "Awangpone", "Barebbo", "Libureng", "Tanete Riattang Barat", "Tanete Riattang Timur", "Cina",
            "Cenrana", "Ajangale", "Amali", "Ponre", "Ulaweng", "Bontocani", "Palakka", "Lamuru", "Salomekko", "Patimpeng",
            "Bengo", "Tonra", "Lappariaja", "Tellu Limpoe", "Biringkanaya", "Manggala", "Tamalate", "Panakukkang",
            "Rappocini", "Tamalanrea", "Tallo", "Makasar", "Mamajang", "Ujung Pandang", "Bontoala", "Mariso", "Ujung Tanah",
            "Wajo", "Kepulauan Sangkarrang"
        ];
        // dd($daftar_kecamatan[0]);
        
        foreach ($daftar_kecamatan as $key => $v) {
            Kecamatan::create([
                'name' => 'Kecamatan ' . $v,
            ]);
        }
    }
}
