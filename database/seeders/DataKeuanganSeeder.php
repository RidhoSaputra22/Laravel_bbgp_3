<?php

namespace Database\Seeders;

use App\Models\Honor;
use App\Models\Internal;
use App\Models\Kegiatan;
use App\Models\Kuitansi;
use App\Models\KuitansiLoka;
use App\Models\PenomoranKegiatan;
use App\Models\PesertaKegiatan;
use Illuminate\Database\Seeder;

class DataKeuanganSeeder extends Seeder
{
    public function run(): void
    {
        $narasumber = PesertaKegiatan::query()
            ->where('no_ktp', '7373012510820011')
            ->whereHas('kegiatan')
            ->first();

        if ($narasumber) {
            Honor::updateOrCreate(
                ['id_peserta' => (string) $narasumber->id],
                [
                    'kode_anggaran' => '521213',
                    'golongan' => 'III/c',
                    'jenis_gol' => 'PNS',
                    'jp_realisasi' => 16,
                    'jumlah' => 2,
                    'jumlah_honor' => 1500000,
                    'potongan' => 0,
                ]
            );

            $receipt = Kuitansi::updateOrCreate(
                ['no_bukti' => 'KWT-001/BBGTK-SULSEL/X/2026'],
                [
                    'pegawai_id' => (string) $narasumber->id,
                    'no_MAK' => '521213',
                    'no_surat_tugas' => '094/BBGTK-SULSEL/KEGIATAN/X/2026',
                    'tgl_surat_tugas' => '2026-10-01',
                    'tahun_anggaran' => '2026',
                    'lokasi_asal' => 'Kota Parepare',
                    'lokasi_tujuan' => 'Kota Makassar',
                    'jenis_angkutan' => 'Darat',
                    'biaya_pergi' => 180000,
                    'biaya_pulang' => 180000,
                    'total_pp' => 360000,
                    'pajak_bandara' => 0,
                    'biaya_asal' => 0,
                    'bea_jarak' => 0,
                    'biaya_tujuan' => 0,
                    'total_transport' => 360000,
                    'potongan' => 0,
                    'biaya_penginapan' => 900000,
                    'uang_harian' => 300000,
                    'total_penginapan' => 900000,
                    'total_harian' => 300000,
                    'jumlah_hari' => 2,
                    'jumlah_malam' => 1,
                    'bill_malam' => 900000,
                    'total_terima' => 1560000,
                ]
            );

            $receipt->transportasis()->updateOrCreate(
                ['asal_transport' => 'Kota Parepare', 'tujuan_transport' => 'Kota Makassar'],
                [
                    'transportasi' => 'Bus antarkota',
                    'keterangan' => 'Perjalanan pergi-pulang narasumber',
                    'biaya_transport' => 360000,
                ]
            );
        }

        $internal = Internal::query()
            ->where('nik', '7371092203900003')
            ->where('jenis', 'Penugasan Pegawai')
            ->first();

        if ($internal) {
            KuitansiLoka::updateOrCreate(
                ['no_bukti' => 'KWT-LOKA-001/BBGTK/X/2026'],
                [
                    'pegawai_id' => (string) $internal->id,
                    'internal_id' => (string) $internal->id,
                    'no_surat_tugas' => '095/BBGTK-SULSEL/LOKA/X/2026',
                    'tgl_surat_tugas' => '2026-10-08',
                    'kode_anggaran' => '521213',
                    'tahun_anggaran' => '2026',
                ]
            );
        }

        $activity = Kegiatan::where('nama_kegiatan', 'Lokakarya Penguatan Komunitas Belajar Sekolah')->first();

        if ($activity) {
            PenomoranKegiatan::updateOrCreate(
                ['kegiatan_id' => (string) $activity->id],
                [
                    'no_surat' => '095/BBGTK-SULSEL/LOKA/X/2026',
                    'tgl_surat' => '2026-10-08',
                    'kode_anggaran' => '521213',
                ]
            );
        }
    }
}
