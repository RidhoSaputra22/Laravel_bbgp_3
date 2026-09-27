<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\Kegiatan;
use App\Models\Rtl;
use App\Models\RtlDocument;
use Illuminate\Database\Seeder;

class DataRtlSeeder extends Seeder
{
    public function run(): void
    {
        $submissions = [
            [
                'no_ktp' => '7373021809870010',
                'activity' => 'Pelatihan Pembelajaran Mendalam, Koding, dan Kecerdasan Artifisial',
                'status' => 'approved',
                'admin_notes' => 'Rencana tindak lanjut telah diverifikasi oleh admin program.',
                'certificate_file' => 'sertifikat/demo-sertifikat-nur-aulia.pdf',
                'document' => 'Rencana Tindak Lanjut - Nur Aulia Ramadhani.pdf',
                'path' => 'rtl/demo-rencana-tindak-lanjut-nur-aulia.pdf',
            ],
            [
                'no_ktp' => '7372761008840009',
                'activity' => 'Lokakarya Penguatan Komunitas Belajar Sekolah',
                'status' => 'pending',
                'admin_notes' => null,
                'certificate_file' => null,
                'document' => 'Rencana Tindak Lanjut - Andi Nurul Fadilah.pdf',
                'path' => 'rtl/demo-rencana-tindak-lanjut-andi-nurul.pdf',
            ],
        ];

        foreach ($submissions as $submission) {
            $activity = Kegiatan::where('nama_kegiatan', $submission['activity'])->first();
            $guruExists = Guru::where('no_ktp', $submission['no_ktp'])->exists();

            if (! $activity || ! $guruExists) {
                continue;
            }

            $rtl = Rtl::updateOrCreate(
                ['no_ktp' => $submission['no_ktp'], 'id_kegiatan' => $activity->id],
                [
                    'status' => $submission['status'],
                    'admin_notes' => $submission['admin_notes'],
                    'certificate_file' => $submission['certificate_file'],
                ]
            );

            RtlDocument::updateOrCreate(
                ['rtl_id' => $rtl->id, 'original_name' => $submission['document']],
                ['file_path' => $submission['path']]
            );
        }
    }
}
