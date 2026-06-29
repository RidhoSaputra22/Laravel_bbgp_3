<?php

namespace App\Services\Assessment;

use App\Models\AssessmentAssignmentTarget;
use App\Models\AssessmentAttempt;
use App\Models\Guru;
use App\Support\Assessment\AssessmentTargetTiming;
use Illuminate\Support\Collection;

class AssessmentPortalService
{
    public function __construct(
        private readonly AssessmentQuestionRandomizerService $randomizer
    ) {}

    public function getDashboardTargets(Guru $guru): Collection
    {
        $targets = AssessmentAssignmentTarget::with([
            'assignment.assessments.forms.fields',
            'session',
            'attempt',
        ])
            ->where('guru_id', $guru->id)
            ->orderByDesc('assigned_at')
            ->orderByDesc('id')
            ->get();

        return $targets->map(function (AssessmentAssignmentTarget $target) {
            return [
                'target' => $target,
                'meta' => $this->buildTargetMeta($target),
            ];
        });
    }

    public function findTargetForGuru(Guru $guru, int $targetId): AssessmentAssignmentTarget
    {
        return AssessmentAssignmentTarget::with([
            'assignment.assessments.forms.fields',
            'session',
            'attempt.answers',
            'guru',
        ])
            ->where('guru_id', $guru->id)
            ->whereKey($targetId)
            ->firstOrFail();
    }

    public function openAttempt(AssessmentAssignmentTarget $target): AssessmentAttempt
    {
        $attempt = $target->attempt;
        $now = now();

        if (! $attempt) {
            $snapshot = $this->randomizer->buildSnapshot($target);

            $attempt = $target->attempt()->create([
                'status' => 'in_progress',
                'structure_snapshot' => $snapshot,
                'total_questions' => (int) data_get($snapshot, 'meta.total_questions', 0),
                'required_questions' => (int) data_get($snapshot, 'meta.required_questions', 0),
                'started_at' => $now,
                'last_answered_at' => $now,
            ]);
        } else {
            if (empty($attempt->structure_snapshot)) {
                $snapshot = $this->randomizer->buildSnapshot($target);

                $attempt->forceFill([
                    'structure_snapshot' => $snapshot,
                    'total_questions' => (int) data_get($snapshot, 'meta.total_questions', 0),
                    'required_questions' => (int) data_get($snapshot, 'meta.required_questions', 0),
                ])->save();
            }

            if ($attempt->status !== 'submitted') {
                $attempt->forceFill([
                    'status' => 'in_progress',
                    'started_at' => $attempt->started_at ?: $now,
                ])->save();
            }
        }

        $target->forceFill([
            'status' => $target->status === 'selesai' ? 'selesai' : 'dikerjakan',
            'started_at' => $target->started_at ?: $now,
        ])->save();

        return $attempt->fresh([
            'answers',
            'target.assignment.assessments.forms.fields',
            'target.session',
            'target.guru',
        ]);
    }

    public function buildTargetMeta(AssessmentAssignmentTarget $target): array
    {
        $attempt = $target->attempt;
        $assignment = $target->assignment;
        $questionTotal = $attempt
            ? (int) ($attempt->total_questions ?: data_get($attempt->structure_snapshot, 'meta.total_questions', 0))
            : $this->countQuestions($target);

        $assessmentCount = $assignment->assessments->count();
        $formCount = $assignment->assessments->sum(fn ($assessment) => $assessment->forms->where('is_active', true)->count());
        $now = now();

        $meta = [
            'status' => 'ready',
            'label' => 'Siap Dikerjakan',
            'badge' => 'success',
            'description' => 'Assessment tersedia untuk mulai dikerjakan sekarang.',
            'can_open' => true,
            'can_view_result' => false,
            'question_total' => $questionTotal,
            'assessment_total' => $assessmentCount,
            'form_total' => $formCount,
            'session_label' => optional($target->session)->label_sesi ?: 'Belum dibagi sesi',
            'session_schedule_text' => optional($target->session)->jadwal_sesi_label ?: 'Jadwal sesi belum ditentukan',
            'date_text' => $this->formatDateRange(
                $assignment->tanggal_mulai,
                $assignment->tanggal_selesai,
                $assignment->jam_mulai_label
            ),
        ];

        if ($target->status === 'dibatalkan') {
            return array_merge($meta, [
                'status' => 'cancelled',
                'label' => 'Dibatalkan',
                'badge' => 'dark',
                'description' => 'Penugasan ini dibatalkan dan tidak bisa diakses.',
                'can_open' => false,
            ]);
        }

        if ($attempt && $attempt->status === 'submitted') {
            $submittedAutomatically = data_get($attempt->result_summary ?? [], 'submission_mode') === 'deadline_auto';

            return array_merge($meta, [
                'status' => 'submitted',
                'label' => $submittedAutomatically ? 'Selesai Otomatis' : 'Selesai',
                'badge' => $submittedAutomatically ? 'secondary' : 'primary',
                'description' => $submittedAutomatically
                    ? 'Batas waktu berakhir. Jawaban terakhir yang tersimpan diproses otomatis dan soal kosong diberi skor 0.'
                    : 'Assessment sudah dikirim. Anda dapat melihat hasilnya kembali kapan saja.',
                'can_open' => false,
                'can_view_result' => true,
            ]);
        }

        if ($assignment->status_distribusi === 'gagal') {
            return array_merge($meta, [
                'status' => 'unavailable',
                'label' => 'Belum Tersedia',
                'badge' => 'danger',
                'description' => 'Penugasan sedang bermasalah dan belum bisa dikerjakan.',
                'can_open' => false,
            ]);
        }

        if ($questionTotal === 0) {
            return array_merge($meta, [
                'status' => 'unavailable',
                'label' => 'Belum Ada Soal',
                'badge' => 'warning',
                'description' => 'Assessment belum memiliki soal aktif yang bisa dikerjakan.',
                'can_open' => false,
            ]);
        }

        $sessionStartAt = optional($target->session)->waktu_mulai;

        if ($sessionStartAt && $now->lt($sessionStartAt)) {
            return array_merge($meta, [
                'status' => 'upcoming',
                'label' => 'Menunggu Jadwal',
                'badge' => 'info',
                'description' => 'Assessment untuk sesi Anda dibuka mulai '.$this->formatDateTime($sessionStartAt).'.',
                'can_open' => false,
            ]);
        }

        $assignmentStartAt = AssessmentTargetTiming::resolveAssignmentStartAt($assignment);

        if ($assignmentStartAt && $now->lt($assignmentStartAt)) {
            return array_merge($meta, [
                'status' => 'upcoming',
                'label' => 'Menunggu Jadwal',
                'badge' => 'info',
                'description' => 'Assessment belum dibuka dan akan tersedia mulai '.$this->formatDateTime($assignmentStartAt).'.',
                'can_open' => false,
            ]);
        }

        $deadlineAt = AssessmentTargetTiming::resolveDeadlineAt($target);

        if ($deadlineAt && $now->greaterThanOrEqualTo($deadlineAt)) {
            return array_merge($meta, [
                'status' => 'expired',
                'label' => 'Sudah Ditutup',
                'badge' => 'secondary',
                'description' => 'Masa pengerjaan assessment sudah selesai.',
                'can_open' => false,
            ]);
        }

        if ($attempt && $attempt->status === 'in_progress') {
            return array_merge($meta, [
                'status' => 'in_progress',
                'label' => 'Sedang Dikerjakan',
                'badge' => 'warning',
                'description' => 'Anda sudah memulai assessment ini. Lanjutkan dari halaman ujian.',
                'can_open' => true,
            ]);
        }

        return $meta;
    }

    private function countQuestions(AssessmentAssignmentTarget $target): int
    {
        return $target->assignment->assessments
            ->where('is_active', true)
            ->sum(function ($assessment) {
                return $assessment->forms
                    ->where('is_active', true)
                    ->sum(fn ($form) => $form->fields->where('is_active', true)->count());
            });
    }

    private function formatDateRange($startDate, $endDate, ?string $startTimeLabel = null): string
    {
        $start = $startDate ? $startDate->format('d M Y') : null;
        $end = $endDate ? $endDate->format('d M Y') : null;

        if ($start && $startTimeLabel) {
            $start .= ' '.$startTimeLabel.' WITA';
        }

        if ($start && $end) {
            return $start.' - '.$end;
        }

        if ($start) {
            return 'Mulai '.$start;
        }

        if ($end) {
            return 'Sampai '.$end;
        }

        return 'Tanpa batas tanggal';
    }
    private function formatDateTime(\Illuminate\Support\Carbon $dateTime): string
    {
        return $dateTime->format('d M Y H:i').' WITA';
    }
}
