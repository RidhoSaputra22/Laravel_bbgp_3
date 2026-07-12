<?php

namespace App\Services\Assessment;

use App\Enum\AssessmentInstrumentType;
use App\Models\AssessmentAssignmentTarget;
use App\Models\AssessmentAttempt;
use App\Models\Guru;
use App\Support\Assessment\AssessmentStageConfig;
use App\Support\Assessment\AssessmentStageProgress;
use App\Support\Assessment\AssessmentTargetTiming;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
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
            'assignment.combination',
            'combination',
            'session',
            'attempt',
        ])
            ->whereHas('assignment', fn ($query) => $query->active())
            ->where('guru_id', $guru->id)
            ->latestAssignmentFirst()
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
            'assignment.combination',
            'combination',
            'session',
            'attempt.answers',
            'attempt.securityEvents',
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
                'deadline_at' => null,
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

        $target->setRelation('attempt', $attempt);
        $startedAt = $attempt->started_at ?: $target->started_at ?: $now;
        $deadlineAt = $attempt->deadline_at
            ?: $target->deadline_at
            ?: AssessmentTargetTiming::resolveDeadlineAt($target, $startedAt->copy());

        $attempt->forceFill([
            'status' => 'in_progress',
            'started_at' => $startedAt,
            'deadline_at' => $deadlineAt,
        ])->save();

        $target->forceFill([
            'status' => $target->status === 'selesai' ? 'selesai' : 'dikerjakan',
            'started_at' => $target->started_at ?: $startedAt,
            'deadline_at' => $target->deadline_at ?: $deadlineAt,
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
        $combination = $target->combination ?: $assignment->combination;
        $questionTotal = $attempt
            ? (int) ($attempt->total_questions ?: data_get($attempt->structure_snapshot, 'meta.total_questions', 0))
            : ($combination?->total_questions ?: $this->countQuestions($target));

        $assessmentCount = $combination?->total_assessments ?: $assignment->assessments->count();
        $formCount = $combination?->total_forms ?: $assignment->assessments->sum(
            fn ($assessment) => $assessment->forms->where('is_active', true)->count()
        );
        $sessionEnabled = $assignment->usesSessionScheduling();
        $now = now();
        $startedAt = AssessmentTargetTiming::resolveStartedAt($target);
        $deadlineAt = AssessmentTargetTiming::resolveDeadlineAt($target);
        $durationMinutes = AssessmentTargetTiming::resolveDurationMinutes($target);
        $completionMode = optional($target->attempt)->completion_mode ?: $target->completion_mode;
        $stageProgress = $attempt && is_array($attempt->progress_snapshot ?? null)
            ? AssessmentStageProgress::normalize($attempt->progress_snapshot, $attempt->structure_snapshot ?? [])
            : null;
        $assignmentUsesStageFlow = $this->assignmentUsesStageFlow($assignment);
        $currentStagePreview = $this->resolveCurrentStagePreview($target, $stageProgress);

        $meta = [
            'status' => 'ready',
            'label' => 'Siap Dikerjakan',
            'badge' => 'success',
            'description' => $assignmentUsesStageFlow
                ? ($currentStagePreview['description'] ?? 'Buka penugasan untuk mengerjakan tahap assessment yang tersedia.')
                : ($sessionEnabled
                    ? 'Assessment siap dimulai. Waktu mulai pertama dan timer akan dicatat saat Anda menekan tombol Mulai Ujian.'
                    : 'Assessment siap dimulai. Anda dapat memulai kapan saja selama periode penugasan, lalu timer dicatat saat tombol Mulai Ujian ditekan.'),
            'can_open' => true,
            'can_view_result' => false,
            'question_total' => $questionTotal,
            'assessment_total' => $assessmentCount,
            'form_total' => $formCount,
            'combination_code' => $combination?->kode_kombinasi,
            'combination_title' => $combination?->judul,
            'session_label' => $sessionEnabled
                ? (optional($target->session)->label_sesi ?: 'Belum dipetakan')
                : 'Tanpa sesi',
            'session_schedule_text' => $sessionEnabled
                ? (optional($target->session)->jadwal_sesi_label ?: 'Jadwal sesi belum ditentukan')
                : $this->resolveOpenAccessScheduleText($assignment),
            'date_text' => $this->formatDateRange(
                $assignment->tanggal_mulai,
                $assignment->tanggal_selesai,
                $assignment->jam_mulai_label
            ),
            'started_at' => $startedAt?->toIso8601String(),
            'deadline_at' => $deadlineAt?->toIso8601String(),
            'duration_minutes' => $durationMinutes,
            'completion_mode' => $completionMode,
            'action_label' => $assignmentUsesStageFlow ? 'Buka Penugasan' : 'Mulai Ujian',
            'stage_progress' => $currentStagePreview,
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
            if ($attempt->disqualified_at || ($attempt->result_summary['submission_mode'] ?? null) === 'security_disqualified') {
                return array_merge($meta, [
                    'status' => 'submitted',
                    'label' => 'Didiskualifikasi',
                    'badge' => 'danger',
                    'description' => $attempt->disqualification_reason
                        ?: 'Assessment dihentikan oleh sistem guard karena pelanggaran aturan ujian.',
                    'can_open' => false,
                    'can_view_result' => true,
                ]);
            }

            $submittedAutomatically = $completionMode === 'timeout'
                || data_get($attempt->result_summary ?? [], 'submission_mode') === 'deadline_auto';

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

        if (! $assignment->isActive()) {
            return array_merge($meta, [
                'status' => 'inactive',
                'label' => 'Dinonaktifkan',
                'badge' => 'secondary',
                'description' => 'Penugasan ini sedang dinonaktifkan admin dan sementara tidak tampil di dashboard peserta.',
                'can_open' => false,
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
                'label' => $assignmentUsesStageFlow
                    ? ($currentStagePreview['label'] ?? 'Tahap Sedang Berjalan')
                    : 'Sedang Dikerjakan',
                'badge' => 'warning',
                'description' => $assignmentUsesStageFlow
                    ? ($currentStagePreview['description'] ?? 'Lanjutkan tahap yang sedang dikerjakan.')
                    : ($deadlineAt
                        ? 'Assessment sudah dimulai. Lanjutkan sebelum batas waktu berakhir pada '.$this->formatDateTime($deadlineAt).'.'
                        : 'Assessment sudah dimulai. Lanjutkan dari halaman ujian.'),
                'can_open' => true,
                'action_label' => 'Lanjutkan Penugasan',
            ]);
        }

        return $meta;
    }

    public function buildStageOverview(AssessmentAssignmentTarget $target, AssessmentAttempt $attempt): array
    {
        $snapshot = is_array($attempt->structure_snapshot ?? null) ? $attempt->structure_snapshot : [];
        $progress = AssessmentStageProgress::normalize($attempt->progress_snapshot, $snapshot);
        $stages = collect($snapshot['assessments'] ?? [])
            ->filter(fn ($assessment) => is_array($assessment))
            ->values()
            ->map(function (array $assessment, int $index) use ($progress) {
                $stage = AssessmentStageProgress::stage($progress, $index) ?? [];
                $config = AssessmentStageProgress::stageConfig($progress, $index);
                $forms = collect($assessment['forms'] ?? [])
                    ->filter(fn ($form) => is_array($form))
                    ->values();
                $questionTotal = (int) $forms->sum(
                    fn ($form) => collect($form['fields'] ?? [])->filter(fn ($field) => is_array($field))->count()
                );
                $requiredQuestionTotal = (int) $forms->sum(function ($form) {
                    return collect($form['fields'] ?? [])
                        ->filter(function ($field) {
                            if (! is_array($field)) {
                                return false;
                            }

                            return (bool) ($field['is_required'] ?? data_get($field, 'validasi.required', false));
                        })
                        ->count();
                });
                $status = (string) ($stage['status'] ?? AssessmentStageProgress::STATUS_READY);
                $allowDraft = (bool) ($config['allow_draft'] ?? false);
                $requiresStartButton =
                    ($config['entry_mode'] ?? null) === AssessmentStageConfig::ENTRY_START_BUTTON;
                $statusLabel = $this->resolveStageStatusLabel($status, $allowDraft);
                $statusTone = $this->resolveStageStatusTone($status, $allowDraft);
                $title = trim((string) ($assessment['judul'] ?? '')) ?: 'Assessment '.($index + 1);
                $startedAt = $this->parseDateTimeValue($stage['started_at'] ?? null);
                $submittedAt = $this->parseDateTimeValue($stage['submitted_at'] ?? null);
                $deadlineAt = $this->parseDateTimeValue($stage['deadline_at'] ?? null);
                $instrumentLabel = AssessmentInstrumentType::tryFromMixed($assessment['instrument_type'] ?? null)?->label()
                    ?: 'Assessment';

                return [
                    'index' => $index,
                    'number' => $index + 1,
                    'code' => trim((string) ($assessment['kode_assessment'] ?? '')) ?: 'ASM-'.($index + 1),
                    'title' => $title,
                    'description' => trim((string) ($assessment['deskripsi'] ?? '')),
                    'instruction' => trim((string) ($assessment['petunjuk'] ?? '')),
                    'instrument_label' => $instrumentLabel,
                    'form_total' => (int) $forms->count(),
                    'question_total' => $questionTotal,
                    'required_question_total' => $requiredQuestionTotal,
                    'status' => $status,
                    'status_label' => $statusLabel,
                    'status_tone' => $statusTone,
                    'status_description' => $this->resolveStageStatusDescription(
                        $status,
                        $title,
                        $allowDraft,
                        $deadlineAt,
                        $submittedAt,
                        AssessmentStageProgress::lockReason($progress, $index),
                        (string) ($stage['completion_mode'] ?? ($config['finalize_mode'] ?? 'manual'))
                    ),
                    'started_at_label' => $this->formatDateTimeValue($startedAt),
                    'deadline_at_label' => $deadlineAt ? $this->formatDateTime($deadlineAt) : 'Tanpa batas waktu',
                    'submitted_at_label' => $this->formatDateTimeValue($submittedAt),
                    'entry_mode_label' => $requiresStartButton ? 'Tombol mulai' : 'Langsung isi',
                    'finalize_mode_label' => ($config['finalize_mode'] ?? null) === AssessmentStageConfig::FINALIZE_AUTO
                        ? 'Auto submit'
                        : 'Manual / permanen',
                    'time_limit_label' => $config['time_limit_minutes']
                        ? $config['time_limit_minutes'].' menit'
                        : 'Tanpa timer',
                    'security_label' => data_get($config, 'security.enabled', false)
                        ? (data_get($config, 'security.require_fullscreen', false)
                            ? 'Guard aktif, fullscreen wajib'
                            : 'Guard aktif')
                        : 'Guard nonaktif',
                    'allow_draft' => $allowDraft,
                    'requires_start_button' => $requiresStartButton,
                    'is_current' => (int) ($progress['current_stage_index'] ?? 0) === $index,
                    'is_locked' => $status === AssessmentStageProgress::STATUS_LOCKED,
                    'is_submitted' => $status === AssessmentStageProgress::STATUS_SUBMITTED,
                    'can_open' => $status !== AssessmentStageProgress::STATUS_LOCKED,
                    'action_mode' => $this->resolveStageActionMode($status, $requiresStartButton),
                    'action_label' => $this->resolveStageActionLabel($status, $requiresStartButton),
                ];
            })
            ->all();
        $stageCollection = collect($stages);
        $stageTotal = (int) $stageCollection->count();
        $submittedTotal = (int) $stageCollection->where('status', AssessmentStageProgress::STATUS_SUBMITTED)->count();
        $inProgressTotal = (int) $stageCollection->where('status', AssessmentStageProgress::STATUS_IN_PROGRESS)->count();
        $draftTotal = (int) $stageCollection->where('status', AssessmentStageProgress::STATUS_DRAFT)->count();
        $readyTotal = (int) $stageCollection->where('status', AssessmentStageProgress::STATUS_READY)->count();
        $lockedTotal = (int) $stageCollection->where('status', AssessmentStageProgress::STATUS_LOCKED)->count();

        return [
            'current_stage_index' => (int) ($progress['current_stage_index'] ?? 0),
            'stage_total' => $stageTotal,
            'submitted_total' => $submittedTotal,
            'in_progress_total' => $inProgressTotal,
            'draft_total' => $draftTotal,
            'ready_total' => $readyTotal,
            'available_total' => $readyTotal + $draftTotal,
            'locked_total' => $lockedTotal,
            'completion_percent' => $stageTotal > 0
                ? (int) round(($submittedTotal / $stageTotal) * 100)
                : 0,
            'stages' => $stages,
        ];
    }

    private function countQuestions(AssessmentAssignmentTarget $target): int
    {
        if ($target->combination) {
            return (int) $target->combination->total_questions;
        }

        if ($target->assignment->combination) {
            return (int) $target->assignment->combination->total_questions;
        }

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

    private function resolveOpenAccessScheduleText($assignment): string
    {
        if ($assignment->tanggal_mulai || $assignment->tanggal_selesai) {
            return 'Akses fleksibel selama periode penugasan';
        }

        return 'Akses fleksibel kapan saja';
    }

    private function assignmentUsesStageFlow($assignment): bool
    {
        return $assignment->assessments
            ->contains(function ($assessment, int $index) {
                return AssessmentStageConfig::isEnabled(
                    AssessmentStageConfig::normalize(
                        is_array($assessment->pivot?->stage_config ?? null) ? $assessment->pivot->stage_config : [],
                        AssessmentStageConfig::defaultForAssessment($assessment->instrument_type, $index)
                    )
                );
            });
    }

    private function resolveCurrentStagePreview(
        AssessmentAssignmentTarget $target,
        ?array $stageProgress = null
    ): array {
        $assignment = $target->assignment;

        if ($stageProgress && AssessmentStageProgress::usesStageFlow($target->attempt?->structure_snapshot ?? [], $stageProgress)) {
            $stageIndex = AssessmentStageProgress::resolveCurrentStageIndex($stageProgress);
            $stage = AssessmentStageProgress::stage($stageProgress, $stageIndex);

            if ($stage) {
                $status = $stage['status'] ?? AssessmentStageProgress::STATUS_READY;
                $title = trim((string) ($stage['title'] ?? 'Tahap '.($stageIndex + 1)));
                $prefix = 'Tahap '.($stageIndex + 1).': '.$title;

                return [
                    'stage_index' => $stageIndex,
                    'title' => $title,
                    'status' => $status,
                    'label' => match ($status) {
                        AssessmentStageProgress::STATUS_DRAFT => 'Tahap '.($stageIndex + 1).' Draft',
                        AssessmentStageProgress::STATUS_IN_PROGRESS => 'Tahap '.($stageIndex + 1).' Sedang Dikerjakan',
                        AssessmentStageProgress::STATUS_SUBMITTED => 'Tahap '.($stageIndex + 1).' Selesai',
                        AssessmentStageProgress::STATUS_LOCKED => 'Tahap '.($stageIndex + 1).' Terkunci',
                        default => 'Tahap '.($stageIndex + 1).' Siap Dikerjakan',
                    },
                    'description' => match ($status) {
                        AssessmentStageProgress::STATUS_DRAFT => $prefix.' tersimpan sebagai draft. Buka kembali tahap ini untuk melanjutkan.',
                        AssessmentStageProgress::STATUS_IN_PROGRESS => $prefix.' sedang berjalan. Buka kembali penugasan untuk melanjutkan.',
                        AssessmentStageProgress::STATUS_SUBMITTED => $prefix.' sudah selesai. Lanjutkan ke tahap berikutnya jika sudah tersedia.',
                        AssessmentStageProgress::STATUS_LOCKED => $prefix.' masih terkunci sampai tahap sebelumnya selesai atau disimpan permanen.',
                        default => $prefix.' siap dibuka dari halaman penugasan.',
                    },
                ];
            }
        }

        $firstStage = $assignment->assessments->values()->first();

        if (! $firstStage) {
            return [];
        }

        return [
            'stage_index' => 0,
            'title' => trim((string) $firstStage->judul) ?: 'Tahap 1',
            'status' => AssessmentStageProgress::STATUS_READY,
            'label' => 'Tahap 1 Siap Dikerjakan',
            'description' => 'Tahap 1: '.(trim((string) $firstStage->judul) ?: 'Assessment pertama')
                .' siap dibuka dari halaman penugasan.',
        ];
    }

    private function resolveStageStatusLabel(string $status, bool $allowDraft): string
    {
        return match ($status) {
            AssessmentStageProgress::STATUS_LOCKED => 'Terkunci',
            AssessmentStageProgress::STATUS_DRAFT => 'Draft',
            AssessmentStageProgress::STATUS_IN_PROGRESS => 'Sedang Dikerjakan',
            AssessmentStageProgress::STATUS_SUBMITTED => 'Selesai',
            default => 'Siap Dikerjakan',
        };
    }

    private function resolveStageStatusTone(string $status, bool $allowDraft): string
    {
        return match ($status) {
            AssessmentStageProgress::STATUS_LOCKED => 'secondary',
            AssessmentStageProgress::STATUS_DRAFT => 'secondary',
            AssessmentStageProgress::STATUS_IN_PROGRESS => 'warning',
            AssessmentStageProgress::STATUS_SUBMITTED => 'success',
            default => 'info',
        };
    }

    private function resolveStageStatusDescription(
        string $status,
        string $title,
        bool $allowDraft,
        ?CarbonInterface $deadlineAt,
        ?CarbonInterface $submittedAt,
        ?string $lockReason,
        ?string $completionMode
    ): string {
        return match ($status) {
            AssessmentStageProgress::STATUS_LOCKED => $lockReason
                ?: 'Tahap ini masih menunggu tahap sebelumnya selesai.',
            AssessmentStageProgress::STATUS_DRAFT => $deadlineAt
                ? 'Draft '.$title.' berhasil disimpan. Lanjutkan kembali sebelum '.$this->formatDateTime($deadlineAt).'.'
                : 'Draft '.$title.' berhasil disimpan. Buka kembali tahap ini untuk melanjutkan pengerjaan.',
            AssessmentStageProgress::STATUS_IN_PROGRESS => $deadlineAt
                ? $title.' sedang dikerjakan. Lanjutkan sebelum '.$this->formatDateTime($deadlineAt).'.'
                : $title.' sedang dikerjakan dan dapat dilanjutkan kembali kapan saja selama penugasan masih aktif.',
            AssessmentStageProgress::STATUS_SUBMITTED => $submittedAt
                ? $title.' selesai pada '.$this->formatDateTime($submittedAt)
                    .(($completionMode === 'timeout' || $completionMode === 'deadline_auto')
                        ? ' melalui submit otomatis.'
                        : '.')
                : $title.' sudah selesai disimpan permanen.',
            default => $allowDraft
                ? $title.' belum dimulai. Tahap ini mendukung penyimpanan draft sebelum dikirim permanen.'
                : $title.' siap dibuka untuk mulai dikerjakan.',
        };
    }

    private function resolveStageActionMode(string $status, bool $requiresStartButton): string
    {
        return match ($status) {
            AssessmentStageProgress::STATUS_LOCKED => 'disabled',
            AssessmentStageProgress::STATUS_SUBMITTED => 'open',
            AssessmentStageProgress::STATUS_DRAFT => 'open',
            AssessmentStageProgress::STATUS_IN_PROGRESS => 'open',
            default => $requiresStartButton ? 'start' : 'open',
        };
    }

    private function resolveStageActionLabel(string $status, bool $requiresStartButton): string
    {
        return match ($status) {
            AssessmentStageProgress::STATUS_LOCKED => 'Tahap Terkunci',
            AssessmentStageProgress::STATUS_SUBMITTED => 'Lihat Tahap',
            AssessmentStageProgress::STATUS_DRAFT => 'Lanjutkan Tahap',
            AssessmentStageProgress::STATUS_IN_PROGRESS => 'Lanjutkan Tahap',
            default => $requiresStartButton ? 'Mulai Tahap' : 'Buka Tahap',
        };
    }

    private function formatDateTimeValue(?CarbonInterface $value): string
    {
        return $value ? $this->formatDateTime($value) : '-';
    }

    private function parseDateTimeValue(mixed $value): ?CarbonInterface
    {
        if ($value instanceof CarbonInterface) {
            return $value;
        }

        if (! filled($value)) {
            return null;
        }

        try {
            return Carbon::parse((string) $value);
        } catch (\Throwable $exception) {
            return null;
        }
    }
}
