<?php

namespace App\Services\Assessment;

use App\Models\Assessment;
use App\Models\AssessmentAssignment;
use App\Models\AssessmentAssignmentTarget;
use App\Models\Guru;
use App\Models\User;
use App\Support\Assessment\AssessmentStageConfig;
use Illuminate\Support\Facades\DB;

class AssessmentAdminPreviewService
{
    private const PREVIEW_GURU_NIK = 'PREVIEW-ADMIN-SYSTEM';

    public function launch(Assessment $assessment, User $adminUser): AssessmentAssignmentTarget
    {
        return DB::transaction(function () use ($assessment, $adminUser) {
            $previewGuru = $this->resolvePreviewGuru();
            $assignment = $this->upsertPreviewAssignment($assessment, $adminUser);

            $assignment->assessments()->sync([
                $assessment->id => [
                    'urutan' => 1,
                    'stage_config' => AssessmentStageConfig::defaults(),
                ],
            ]);

            $target = AssessmentAssignmentTarget::query()->firstOrCreate(
                [
                    'assessment_assignment_id' => $assignment->id,
                    'guru_id' => $previewGuru->id,
                ],
                [
                    'status' => 'ditugaskan',
                    'assigned_at' => now(),
                ]
            );

            $target->attempt()->delete();

            $target->forceFill([
                'assessment_assignment_session_id' => null,
                'assessment_combination_id' => null,
                'status' => 'ditugaskan',
                'assigned_at' => now(),
                'started_at' => null,
                'deadline_at' => null,
                'submitted_at' => null,
                'completion_mode' => null,
                'timed_out_at' => null,
            ])->save();

            return $this->findPreviewTargetForUser((int) $target->id, (int) $adminUser->id);
        });
    }

    public function findPreviewTargetForUser(int $targetId, int $adminUserId): AssessmentAssignmentTarget
    {
        return AssessmentAssignmentTarget::with([
            'assignment.assessments.forms.fields',
            'assignment.combination',
            'combination',
            'session',
            'attempt.answers',
            'attempt.securityEvents',
            'guru',
        ])
            ->whereKey($targetId)
            ->whereHas('assignment', function ($query) use ($adminUserId) {
                $query
                    ->previewOnly()
                    ->where('assigned_by', $adminUserId);
            })
            ->firstOrFail();
    }

    public function resolvePrimaryAssessmentId(AssessmentAssignmentTarget $target): int
    {
        $target->loadMissing('assignment.assessments');

        return (int) ($target->assignment->assessments->first()?->id ?? 0);
    }

    private function upsertPreviewAssignment(Assessment $assessment, User $adminUser): AssessmentAssignment
    {
        $today = now()->toDateString();

        return AssessmentAssignment::query()->updateOrCreate(
            [
                'kode_penugasan' => $this->buildPreviewAssignmentCode($assessment, $adminUser),
            ],
            [
                'judul_penugasan' => 'Preview Admin: '.$assessment->judul,
                'is_active' => true,
                'session_enabled' => false,
                'target_ketenagaan' => $assessment->target_ketenagaan,
                'target_jabatan' => [],
                'target_kabupaten' => [],
                'target_satuan_pendidikan' => [],
                'deskripsi' => 'Portal preview admin untuk mencoba pengerjaan assessment ini. Hasil skor pada halaman ini bersifat sementara dan tidak masuk monitoring peserta.',
                'tanggal_mulai' => $today,
                'jam_mulai' => null,
                'tanggal_selesai' => $today,
                'kapasitas_per_sesi' => 1,
                'durasi_sesi_jam' => 0,
                'security_config' => [
                    'enabled' => false,
                    'require_fullscreen' => false,
                    'max_serious_violations' => 3,
                    'temporary_lock_seconds' => 2,
                    'fullscreen_grace_seconds' => 10,
                ],
                'total_sesi' => 0,
                'status_distribusi' => 'selesai',
                'total_target' => 1,
                'total_ditugaskan' => 1,
                'assigned_by' => $adminUser->id,
                'assessment_combination_id' => null,
                'processed_at' => now(),
            ]
        );
    }

    private function buildPreviewAssignmentCode(Assessment $assessment, User $adminUser): string
    {
        return AssessmentAssignment::PREVIEW_CODE_PREFIX.'A'.$assessment->id.'-U'.$adminUser->id;
    }

    private function resolvePreviewGuru(): Guru
    {
        $previewGuru = Guru::query()->firstOrNew([
            'no_ktp' => self::PREVIEW_GURU_NIK,
        ]);

        $previewGuru->fill([
            'nama_lengkap' => 'Peserta Preview Admin',
            'email' => 'preview-admin@local.invalid',
            'nip' => self::PREVIEW_GURU_NIK,
            'tempat_lahir' => 'Preview',
            'tgl_lahir' => '2000-01-01',
            'gender' => 'Laki-laki',
            'jabatan' => 'Preview Admin',
            'status' => 'Belum Kawin',
            'status_kepegawaian' => 'Preview',
            'agama' => 'Islam',
            'pendidikan' => 'Preview',
            'kabupaten' => 'Preview',
            'satuan_pendidikan' => 'Portal Preview Admin',
            'alamat_satuan' => 'Portal Preview Admin',
            'alamat_rumah' => 'Portal Preview Admin',
            'no_hp' => '-',
            'no_wa' => '-',
            'pas_foto' => 'preview-admin.png',
            'no_rek' => '-',
            'jenis_bank' => null,
            'npsn_sekolah' => '-',
            'npwp' => '-',
            'nuptk' => '-',
            'eksternal_jabatan' => 'Preview Admin',
            'jenis_jabatan' => 'Preview Admin',
            'kategori_jabatan' => 'Preview',
            'tugas_jabatan' => 'Preview assessment admin',
            'latar_jabatan' => 'Preview assessment admin',
            'is_verif' => 'sudah',
        ]);

        $previewGuru->save();

        return $previewGuru;
    }
}
