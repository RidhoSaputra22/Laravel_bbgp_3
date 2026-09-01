<?php

namespace Database\Seeders;

use App\Enum\AssessmentInstrumentType;
use App\Enum\AssessmentKetenagaanType;
use App\Models\Assessment;
use App\Models\AssessmentAssignment;
use App\Models\AssessmentAssignmentTarget;
use App\Models\Guru;
use App\Support\Assessment\AssessmentSchoolTargetKey;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AssessmentAssignmentParticipantFilterDemoSeeder extends Seeder
{
    private const ASSIGNMENT_CODE = 'SIM-FILTER-PENUGASAN-001';

    private const ASSESSMENT_CODE = 'SIM-FILTER-PESERTA-001';

    /**
     * Seed a repeatable scenario for the additional-participant modal.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $assessment = $this->upsertAssessment();
            $participants = $this->upsertParticipants();
            $assignment = $this->upsertAssignment();

            $assignment->assessments()->sync([
                $assessment->id => [
                    'urutan' => 1,
                    'stage_config' => null,
                ],
            ]);

            // Reset only targets owned by this demo assignment so the scenario stays repeatable.
            $assignment->targets()->delete();

            AssessmentAssignmentTarget::query()->create([
                'assessment_assignment_id' => $assignment->id,
                'guru_id' => $participants['assigned']->id,
                'status' => 'ditugaskan',
                'assigned_at' => now(),
            ]);

            $assignment->forceFill([
                'total_target' => 1,
                'total_ditugaskan' => 1,
                'total_sesi' => 0,
                'status_distribusi' => 'selesai',
                'processed_at' => now(),
            ])->save();
        });
    }

    private function upsertAssessment(): Assessment
    {
        return Assessment::query()->updateOrCreate(
            ['kode_assessment' => self::ASSESSMENT_CODE],
            [
                'judul' => 'Simulasi Filter Peserta Penugasan',
                'slug' => Str::slug('Simulasi Filter Peserta Penugasan'),
                'deskripsi' => 'Instrumen ringan untuk simulasi filter modal tambah peserta.',
                'petunjuk' => 'Data ini digunakan untuk memeriksa filter peserta pada penugasan simulasi.',
                'instrument_type' => AssessmentInstrumentType::PILIHAN_GANDA_KOMPLEKS->value,
                'target_ketenagaan' => AssessmentKetenagaanType::TENAGA_PENDIDIK->value,
                'scoring_config' => [],
                'status' => 'publish',
                'is_active' => true,
            ]
        );
    }

    /**
     * @return array{assigned: Guru, available_makassar: Guru, available_gowa: Guru}
     */
    private function upsertParticipants(): array
    {
        $participants = [
            'assigned' => $this->upsertGuru(
                '9000000000000001',
                'SIM Peserta Lama Sudah Ditugaskan',
                'Guru',
                'Kota Makassar',
                'SMP Demo Filter Makassar 1'
            ),
            'available_makassar' => $this->upsertGuru(
                '9000000000000002',
                'SIM Peserta Baru Belum Ditugaskan Makassar',
                'Guru',
                'Kota Makassar',
                'SMP Demo Filter Makassar 1'
            ),
            'available_gowa' => $this->upsertGuru(
                '9000000000000003',
                'SIM Peserta Baru Belum Ditugaskan Gowa',
                'Guru',
                'Kabupaten Gowa',
                'SMP Demo Filter Gowa 1'
            ),
            // These records must not appear under the default assignment filters.
            'outside_jabatan' => $this->upsertGuru(
                '9000000000000004',
                'SIM Pembanding Jabatan Di Luar Target',
                'Kepala Sekolah',
                'Kota Makassar',
                'SMP Demo Filter Makassar 1'
            ),
            'outside_kabupaten' => $this->upsertGuru(
                '9000000000000005',
                'SIM Pembanding Kabupaten Di Luar Target',
                'Guru',
                'Kabupaten Bone',
                'SMP Demo Filter Bone 1'
            ),
            'outside_school' => $this->upsertGuru(
                '9000000000000006',
                'SIM Pembanding Sekolah Di Luar Target',
                'Guru',
                'Kota Makassar',
                'SMP Demo Filter Makassar Lain'
            ),
        ];

        return $participants;
    }

    private function upsertAssignment(): AssessmentAssignment
    {
        $makassarSchool = AssessmentSchoolTargetKey::encode(
            'Kota Makassar',
            'SMP Demo Filter Makassar 1'
        );
        $gowaSchool = AssessmentSchoolTargetKey::encode(
            'Kabupaten Gowa',
            'SMP Demo Filter Gowa 1'
        );

        return AssessmentAssignment::query()->updateOrCreate(
            ['kode_penugasan' => self::ASSIGNMENT_CODE],
            [
                'judul_penugasan' => 'SIMULASI - Filter Tambah Peserta Berdasarkan Target',
                'is_active' => true,
                'session_enabled' => false,
                'target_ketenagaan' => AssessmentKetenagaanType::TENAGA_PENDIDIK->value,
                'assessment_combination_id' => null,
                'target_jabatan' => ['Guru'],
                'target_kabupaten' => ['Kota Makassar', 'Kabupaten Gowa'],
                'target_satuan_pendidikan' => [$makassarSchool, $gowaSchool],
                'deskripsi' => 'Buka modal Tambah Peserta untuk melihat dua peserta baru yang sesuai target namun belum ditugaskan.',
                'tanggal_mulai' => now()->toDateString(),
                'jam_mulai' => '08:00:00',
                'tanggal_selesai' => now()->addDays(7)->toDateString(),
                'kapasitas_per_sesi' => 0,
                'durasi_sesi_jam' => 3,
                'security_config' => [],
                'total_sesi' => 0,
                'status_distribusi' => 'draft',
                'total_target' => 0,
                'total_ditugaskan' => 0,
                'assigned_by' => null,
                'job_batch_id' => null,
                'processed_at' => null,
            ]
        );
    }

    private function upsertGuru(
        string $noKtp,
        string $nama,
        string $jenisJabatan,
        string $kabupaten,
        string $satuanPendidikan
    ): Guru {
        $sequence = (int) substr($noKtp, -2);

        return Guru::query()->updateOrCreate(
            ['no_ktp' => $noKtp],
            [
                'nama_lengkap' => $nama,
                'email' => 'sim.filter.' . $sequence . '@bbgp-seeder.test',
                'nip' => 'SIM-FILTER-' . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT),
                'tempat_lahir' => 'Makassar',
                'tgl_lahir' => '1988-08-17',
                'gender' => $sequence % 2 === 0 ? 'Perempuan' : 'Laki-laki',
                'jabatan' => $jenisJabatan,
                'status' => 'Kawin',
                'status_kepegawaian' => 'PNS',
                'agama' => 'Islam',
                'pendidikan' => 'S1',
                'kabupaten' => $kabupaten,
                'satuan_pendidikan' => $satuanPendidikan,
                'alamat_satuan' => 'Jl. Simulasi Filter No. ' . $sequence,
                'alamat_rumah' => 'Jl. Peserta Simulasi No. ' . $sequence,
                'no_hp' => '0813000000' . str_pad((string) $sequence, 2, '0', STR_PAD_LEFT),
                'no_wa' => '0812000000' . str_pad((string) $sequence, 2, '0', STR_PAD_LEFT),
                'pas_foto' => '',
                'no_rek' => 'SIM' . str_pad((string) $sequence, 9, '0', STR_PAD_LEFT),
                'jenis_bank' => 'Bank BRI',
                'npsn_sekolah' => 'SIM' . str_pad((string) $sequence, 5, '0', STR_PAD_LEFT),
                'npwp' => 'SIMNPWP' . str_pad((string) $sequence, 8, '0', STR_PAD_LEFT),
                'nuptk' => 'SIMNUPTK' . str_pad((string) $sequence, 7, '0', STR_PAD_LEFT),
                'eksternal_jabatan' => AssessmentKetenagaanType::TENAGA_PENDIDIK->guruValue(),
                'jenis_jabatan' => $jenisJabatan,
                'kategori_jabatan' => 'Simulasi',
                'tugas_jabatan' => 'Simulasi Filter',
                'latar_jabatan' => '',
                'is_verif' => 'sudah',
            ]
        );
    }
}
