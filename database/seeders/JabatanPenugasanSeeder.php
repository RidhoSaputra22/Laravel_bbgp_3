<?php

namespace Database\Seeders;

use App\Models\JabatanPenugasanGolongan;
use App\Models\JabatanPenugasanPegawai;
use App\Models\JabatanPenugasanPpnpn;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JabatanPenugasanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pegawai = [
            "Kepala",
            "Kabag Umum",
            "Bendahara",
            "Analis Diklat",
            "Perencana Ahli Muda",
            "Pengelola Keuangan",
            "Pengelola Peningkatan Kompetensi Pendidik dan Tenaga Kependidikan",
            "Widyaswara Ahli Muda",
            "Pengolah Bahan Informasi dan Publikasi",
            "Analis Tata Laksana",
            "Analis Informasi Pengembangan Sumber Daya Manusia Aparatur",
            "Pengembang Teknologi Pembelajaran Ahli Muda",
            "Analis Data dan Informasi Pendidik dan Tenaga Kependidikan",
            "Penyusun Program Anggaran dan Pelaporan",
            "Pengelola Kepegawaian",
            "Analis Sistem Informasi dan Jaringan",
            "Widyaprada Ahli Muda",
            "Pengadministrasi Kepegawaian",
            "Penyusun Bahan Informasi dan Publikasi",
            "Analis Kemitraan",
            "Pengelola Wisma",
            "Pengadministrasi Perpustakaan",
            "Pengelola Data Tata Organisasi dan Tata Laksana",
            "Pengelola Situs atau Web",
            "Pengelola Data Barang Milik Negara",
            "Analis Barang Milik Negara",
            "Pengadministrasi Pelatihan",
            "Teknisi Sarana dan Prasarana",
            "Widyaprada Ahli Pertama",
            "Pengadministrasi Keuangan",
            "Verifikator Keuangan",
            "Pengadministrasi Sarana dan Prasarana",
            "Pengadministrasi Umum",
            "Petugas Keamanan"
        ];

        $ppnpn = [
            "Satpam",
            "Sopir",
            "Petugas Kebersihan",
            "Pramubakti"
        ];

        $golongan = [
            "I/a",
            "I/b",
            "I/c",
            "I/d",
            "II/a",
            "II/b",
            "II/c",
            "II/d",
            "III/a",
            "III/b",
            "III/c",
            "III/d",
            "IV/a",
            "IV/b",
            "IV/c",
            "IV/d",
        ];


        foreach ($pegawai as $key => $v) {
            JabatanPenugasanPegawai::create([
                'name' => $v,
            ]);
        }

        foreach ($ppnpn as $key => $v) {
            JabatanPenugasanPpnpn::create([
                'name' => $v,
            ]);
        }

        foreach ($golongan as $key => $v) {
            JabatanPenugasanGolongan::create([
                'name' => $v,
            ]);
        }


    }
}
