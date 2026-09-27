<?php

namespace Database\Seeders;

use App\Models\Jabatan;
use App\Models\JabatanPenugasanGolongan;
use App\Models\JabatanPenugasanPegawai;
use App\Models\JabatanPenugasanPpnpn;
use App\Models\JabatanStakeHolder;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Kepegawaian;
use App\Models\Pegawai;
use App\Models\Pendidikan;
use App\Models\SatuanPendidikan;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['PNS', 'PPNPN', 'Guru Honorer Sekolah', 'Guru Bantu Sekolah'] as $name) {
            Kepegawaian::firstOrCreate(['name' => $name]);
        }

        foreach (['SMA / SMK', 'D3', 'S1', 'S2', 'S3'] as $name) {
            Pendidikan::firstOrCreate(['name' => $name]);
        }

        foreach (['SD Negeri', 'SMP Negeri', 'SMA Negeri', 'SMK Negeri', 'PKBM'] as $name) {
            SatuanPendidikan::firstOrCreate(['name' => $name]);
        }

        foreach (['Kepala sekolah', 'Kepala tata usaha', 'Waka kurikulum', 'Guru dan karyawan'] as $name) {
            Jabatan::firstOrCreate(['name' => $name]);
        }

        foreach (['Kepala Subbagian Tata Usaha', 'Analis Diklat', 'Pengelola Keuangan', 'Pengembang Teknologi Pembelajaran Ahli Muda'] as $name) {
            JabatanPenugasanPegawai::firstOrCreate(['name' => $name]);
        }

        foreach (['Satpam', 'Sopir', 'Petugas Kebersihan', 'Pramubakti'] as $name) {
            JabatanPenugasanPpnpn::firstOrCreate(['name' => $name]);
        }

        foreach (['II/c', 'III/a', 'III/b', 'III/c', 'IV/a'] as $name) {
            JabatanPenugasanGolongan::firstOrCreate(['name' => $name]);
        }

        foreach (['Dinas Pendidikan Provinsi Sulawesi Selatan', 'Kepala sekolah', 'Pengawas sekolah'] as $name) {
            JabatanStakeHolder::firstOrCreate(['name' => $name]);
        }

        foreach ([
            'Kabupaten Gowa', 'Kabupaten Maros', 'Kabupaten Pangkep',
            'Kabupaten Pinrang', 'Kabupaten Soppeng', 'Kota Makassar',
            'Kota Parepare', 'Kabupaten Bone', 'Kabupaten Bulukumba',
        ] as $name) {
            Kabupaten::firstOrCreate(['name' => $name]);
        }

        foreach (['Kecamatan Rappocini', 'Kecamatan Tamalanrea', 'Kecamatan Somba Opu', 'Kecamatan Turikale', 'Kecamatan Ujung'] as $name) {
            Kecamatan::firstOrCreate(['name' => $name]);
        }

        foreach ([
            [
                'no_ktp' => '7371011201800001',
                'username' => 'nuraisyah.rahman',
                'nama_lengkap' => 'Nur Aisyah Rahman',
                'email' => 'nuraisyah.rahman@bbgtk-sulsel.test',
                'nip' => '198011122010012001',
                'tempat_lahir' => 'Makassar',
                'tgl_lahir' => '1980-11-12',
                'gender' => 'Perempuan',
                'jabatan' => 'Kepala Subbagian Tata Usaha',
                'jenis_pegawai' => 'BBGP',
                'status' => 'Kawin',
                'status_kepegawaian' => 'PNS',
                'agama' => 'Islam',
                'pendidikan' => 'S2',
                'kabupaten' => 'Kota Makassar',
                'satuan_pendidikan' => 'BBGTK Sulawesi Selatan',
                'alamat_satuan' => 'Jalan Adiyaksa Nomor 2, Makassar',
                'alamat_rumah' => 'Jalan Hertasning Baru, Rappocini, Makassar',
                'no_hp' => '081341120001',
                'no_wa' => '081341120001',
                'pas_foto' => '',
                'instansi' => 'BBGTK Sulawesi Selatan',
                'golongan' => 'IV/a',
                'jenis_bank' => 'Bank BRI',
                'no_rek' => '0201011980112001',
                'is_verif' => 'sudah',
            ],
            [
                'no_ktp' => '7371061502850002',
                'username' => 'muhammad.fadli.ramadhan',
                'nama_lengkap' => 'Muhammad Fadli Ramadhan',
                'email' => 'muhammad.fadli.ramadhan@bbgtk-sulsel.test',
                'nip' => '198502152010011002',
                'tempat_lahir' => 'Sungguminasa',
                'tgl_lahir' => '1985-02-15',
                'gender' => 'Laki-laki',
                'jabatan' => 'Analis Diklat',
                'jenis_pegawai' => 'BBGP',
                'status' => 'Kawin',
                'status_kepegawaian' => 'PNS',
                'agama' => 'Islam',
                'pendidikan' => 'S1',
                'kabupaten' => 'Kabupaten Gowa',
                'satuan_pendidikan' => 'BBGTK Sulawesi Selatan',
                'alamat_satuan' => 'Jalan Adiyaksa Nomor 2, Makassar',
                'alamat_rumah' => 'Jalan Tun Abdul Razak, Somba Opu, Gowa',
                'no_hp' => '081342150002',
                'no_wa' => '081342150002',
                'pas_foto' => '',
                'instansi' => 'BBGTK Sulawesi Selatan',
                'golongan' => 'III/b',
                'jenis_bank' => 'Bank Mandiri',
                'no_rek' => '0201011985021502',
                'is_verif' => 'sudah',
            ],
            [
                'no_ktp' => '7371092203900003',
                'username' => 'rismawati.yusuf',
                'nama_lengkap' => 'Rismawati Yusuf',
                'email' => 'rismawati.yusuf@bbgtk-sulsel.test',
                'nip' => '199003222015032003',
                'tempat_lahir' => 'Maros',
                'tgl_lahir' => '1990-03-22',
                'gender' => 'Perempuan',
                'jabatan' => 'Pengelola Keuangan',
                'jenis_pegawai' => 'BBGP',
                'status' => 'Belum Kawin',
                'status_kepegawaian' => 'PNS',
                'agama' => 'Islam',
                'pendidikan' => 'S1',
                'kabupaten' => 'Kabupaten Maros',
                'satuan_pendidikan' => 'BBGTK Sulawesi Selatan',
                'alamat_satuan' => 'Jalan Adiyaksa Nomor 2, Makassar',
                'alamat_rumah' => 'Jalan Poros Maros–Makassar, Mandai, Maros',
                'no_hp' => '081343220003',
                'no_wa' => '081343220003',
                'pas_foto' => '',
                'instansi' => 'BBGTK Sulawesi Selatan',
                'golongan' => 'III/a',
                'jenis_bank' => 'Bank BNI',
                'no_rek' => '0201011990032203',
                'is_verif' => 'sudah',
            ],
            [
                'no_ktp' => '7371120402780004',
                'username' => 'andi.syahrul.maulana',
                'nama_lengkap' => 'Andi Syahrul Maulana',
                'email' => 'andi.syahrul.maulana@bbgtk-sulsel.test',
                'nip' => '197802042005011004',
                'tempat_lahir' => 'Parepare',
                'tgl_lahir' => '1978-02-04',
                'gender' => 'Laki-laki',
                'jabatan' => 'Pengembang Teknologi Pembelajaran Ahli Muda',
                'jenis_pegawai' => 'BBGP',
                'status' => 'Kawin',
                'status_kepegawaian' => 'PNS',
                'agama' => 'Islam',
                'pendidikan' => 'S2',
                'kabupaten' => 'Kota Parepare',
                'satuan_pendidikan' => 'BBGTK Sulawesi Selatan',
                'alamat_satuan' => 'Jalan Adiyaksa Nomor 2, Makassar',
                'alamat_rumah' => 'Jalan Bau Massepe, Ujung, Parepare',
                'no_hp' => '081344040004',
                'no_wa' => '081344040004',
                'pas_foto' => '',
                'instansi' => 'BBGTK Sulawesi Selatan',
                'golongan' => 'III/c',
                'jenis_bank' => 'Bank Syariah Indonesia',
                'no_rek' => '0201011978020404',
                'is_verif' => 'sudah',
            ],
        ] as $pegawai) {
            Pegawai::updateOrCreate(['no_ktp' => $pegawai['no_ktp']], $pegawai);
        }
    }
}
