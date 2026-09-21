<?php

namespace Database\Seeders;

use App\Models\Kegiatan;
use App\Models\PesertaKegiatan;
use Illuminate\Database\Seeder;

class PdfBiodataLongTextSeeder extends Seeder
{
    private const ACTIVITY_MARKER = '[SEED-PDF-BIODATA-LONG-TEXT]';
    private const NO_KTP = '7371010101010001';

    public function run(): void
    {
        $activity = Kegiatan::updateOrCreate(
            ['deskripsi_kegiatan' => self::ACTIVITY_MARKER],
            [
                'nama_kegiatan' => 'PELATIHAN IMPLEMENTASI PEMBELAJARAN MENDALAM, KODING, DAN KECERDASAN ARTIFISIAL BAGI KEPALA SEKOLAH DAN GURU DI PROVINSI SULAWESI SELATAN TAHUN 2026',
                'tempat_kegiatan' => 'Aula Utama Balai Besar Guru dan Tenaga Kependidikan Provinsi Sulawesi Selatan, Jalan Adiyaksa Nomor 2, Kota Makassar',
                'tgl_kegiatan' => '2026-09-21',
                'tgl_selesai' => '2026-09-25',
                'jam_mulai' => '08:00:00',
                'jam_selesai' => '16:00:00',
                'deskripsi_kegiatan' => self::ACTIVITY_MARKER,
                'status' => 'true',
            ]
        );

        PesertaKegiatan::updateOrCreate(
            [
                'id_kegiatan' => (string) $activity->id,
                'no_ktp' => self::NO_KTP,
            ],
            [
                'nama' => 'Dr. Abdul Gafur Ramadhan Pratama Nurhidayatullah Al-Makassari, S.Pd., M.Pd., Gr.',
                'nip' => '198001012026010001',
                'alamat' => 'Sekolah Penggerak Terpadu Pusat Pengembangan Pembelajaran Mendalam dan Kecerdasan Artifisial Sulawesi Selatan',
                'email' => 'abdul.gafur.ramadhan.pratama.nurhidayatullah@bbgtk-sulsel.test',
                'mata_pelajaran' => 'Pemrograman, Koding, Literasi Digital, dan Kecerdasan Artifisial',
                'status_keikutpesertaan' => 'peserta',
                'instansi' => 'Balai Besar Guru dan Tenaga Kependidikan Provinsi Sulawesi Selatan',
                'golongan' => 'Pembina Utama Madya, IV/d',
                'jenis_gol' => 'PNS',
                'jkl' => 'Laki-laki',
                'status' => 'PNS',
                'no_hp' => '081234567890',
                'no_wa' => '081234567890',
                'tempat_lahir' => 'Kabupaten Kepulauan Selayar',
                'tgl_lahir' => '1980-01-01',
                'agama' => 'Islam',
                'pendidikan' => 'Magister Pendidikan Teknologi dan Kejuruan',
                'alamat_rumah' => 'Perumahan Pendidikan dan Kebudayaan Nusantara Blok C Nomor 27, Jalan Sultan Alauddin, Kecamatan Rappocini, Kota Makassar',
                'kabupaten_rumah' => 'Kota Makassar',
                'npwp' => '599050721813000',
                'kabupaten' => 'Kabupaten Luwu Timur',
                'no_surat_tugas' => '094/BBGTK-SULSEL/DIKLAT/IX/2026',
                'tgl_surat_tugas' => '2026-09-15',
            ]
        );

        $this->command?->info('Seed PDF biodata panjang berhasil dibuat/diperbarui.');
        $this->command?->line('Kegiatan ID: ' . $activity->id);
    }
}
