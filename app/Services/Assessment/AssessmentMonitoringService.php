<?php

namespace App\Services\Assessment;

use App\Enum\LevelKompetensi;
use App\Models\AssessmentAssignment;
use App\Models\AssessmentAssignmentSession;
use App\Models\AssessmentAssignmentTarget;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AssessmentMonitoringService
{
    public function buildGlobalDashboard(?Collection $assignments = null): array
    {
        $assignments = $assignments instanceof Collection
            ? $this->prepareAssignments($assignments)
            : $this->loadAssignments();

        $assignmentRows = $assignments
            ->map(fn (AssessmentAssignment $assignment) => $this->buildAssignmentRow($assignment))
            ->values();
        $targets = $assignments
            ->flatMap(fn (AssessmentAssignment $assignment) => $assignment->targets)
            ->values();
        $sessions = $assignments
            ->flatMap(fn (AssessmentAssignment $assignment) => $assignment->sessions)
            ->values();

        return [
            'summary' => $this->buildGlobalSummary($assignmentRows, $targets),
            'assignment_rows' => $assignmentRows->all(),
            'attention_assignments' => $assignmentRows
                ->sortByDesc(fn (array $row) => ($row['distribution_missing_total'] * 1000000) + ($row['pending_total'] * 1000) + $row['timeout_total'])
                ->take(8)
                ->values()
                ->all(),
            'kabupaten_rows' => $this->buildKabupatenRows($targets)->all(),
            'session_rows' => $this->buildSessionRows($assignments, $sessions)->all(),
            'charts' => $this->buildGlobalCharts($assignmentRows, $targets, $assignments, $sessions),
        ];
    }

    public function buildAssignmentDetail(AssessmentAssignment $assignment): array
    {
        $assignment = $this->prepareAssignment($assignment);
        $targets = $assignment->targets->values();
        $submittedTargets = $targets->filter(fn (AssessmentAssignmentTarget $target) => $this->isSubmitted($target))->values();
        $pendingTargets = $targets->reject(fn (AssessmentAssignmentTarget $target) => $this->isSubmitted($target))->values();
        $pendingReviewTargets = $submittedTargets
            ->filter(fn (AssessmentAssignmentTarget $target) => $this->manualPendingItems($target) > 0)
            ->sortByDesc(fn (AssessmentAssignmentTarget $target) => $this->manualPendingItems($target))
            ->values();
        $scoredTargets = $submittedTargets
            ->filter(fn (AssessmentAssignmentTarget $target) => $this->overallScore($target) !== null)
            ->sortBy('attempt.scoring_summary.overall.score')
            ->values();

        return [
            'summary' => $this->buildAssignmentRow($assignment),
            'lists' => [
                'submitted_participants' => $submittedTargets
                    ->sortByDesc(function (AssessmentAssignmentTarget $target) {
                        $submittedAt = $target->submitted_at ?: $target->attempt?->submitted_at;

                        return $submittedAt?->timestamp ?? 0;
                    })
                    ->take(10)
                    ->map(fn (AssessmentAssignmentTarget $target) => $this->serializeTargetRow($target))
                    ->values()
                    ->all(),
                'pending_participants' => $pendingTargets
                    ->sortBy(fn (AssessmentAssignmentTarget $target) => $target->assigned_at?->timestamp ?? PHP_INT_MAX)
                    ->take(10)
                    ->map(fn (AssessmentAssignmentTarget $target) => $this->serializeTargetRow($target))
                    ->values()
                    ->all(),
                'pending_review_participants' => $pendingReviewTargets
                    ->take(10)
                    ->map(fn (AssessmentAssignmentTarget $target) => $this->serializeTargetRow($target))
                    ->values()
                    ->all(),
                'low_score_participants' => $scoredTargets
                    ->take(10)
                    ->map(fn (AssessmentAssignmentTarget $target) => $this->serializeTargetRow($target))
                    ->values()
                    ->all(),
            ],
            'charts' => $this->buildAssignmentCharts($assignment),
            'kabupaten_rows' => $this->buildKabupatenRows($targets)->all(),
            'session_rows' => $this->buildSessionRows(collect([$assignment]), $assignment->sessions)->all(),
        ];
    }

    private function loadAssignments(): Collection
    {
        return AssessmentAssignment::query()
            ->with([
                'targets.guru',
                'targets.attempt',
                'sessions',
            ])
            ->withCount(['targets', 'sessions'])
            ->orderByDesc('id')
            ->get();
    }

    private function prepareAssignments(Collection $assignments): Collection
    {
        if ($assignments instanceof EloquentCollection) {
            $assignments->loadMissing([
                'targets.guru',
                'targets.attempt',
                'sessions',
            ]);

            return $assignments->values();
        }

        return $assignments
            ->map(fn (AssessmentAssignment $assignment) => $this->prepareAssignment($assignment))
            ->values();
    }

    private function prepareAssignment(AssessmentAssignment $assignment): AssessmentAssignment
    {
        $assignment->loadMissing([
            'targets.guru',
            'targets.attempt',
            'sessions',
        ]);

        return $assignment;
    }

    private function buildGlobalSummary(Collection $assignmentRows, Collection $targets): array
    {
        $expectedTargetTotal = (int) $assignmentRows->sum('target_total');
        $storedTargetTotal = (int) $targets->count();
        $distributionMissingTotal = (int) $assignmentRows->sum('distribution_missing_total');
        $submittedTotal = (int) $targets->filter(fn (AssessmentAssignmentTarget $target) => $this->isSubmitted($target))->count();
        $submittedTimeoutTotal = (int) $targets->filter(fn (AssessmentAssignmentTarget $target) => $this->isTimeout($target))->count();
        $submittedManualTotal = max($submittedTotal - $submittedTimeoutTotal, 0);
        $inProgressTotal = (int) $targets->filter(fn (AssessmentAssignmentTarget $target) => $this->isInProgress($target))->count();
        $notStartedTotal = (int) $targets->filter(fn (AssessmentAssignmentTarget $target) => $this->isNotStarted($target))->count();
        $pendingReviewParticipants = (int) $targets->filter(fn (AssessmentAssignmentTarget $target) => $this->manualPendingItems($target) > 0)->count();
        $pendingReviewItems = (int) $targets->sum(fn (AssessmentAssignmentTarget $target) => $this->manualPendingItems($target));
        $averageScore = $this->averageScore($targets->all());
        $completionRate = $expectedTargetTotal > 0
            ? round(($submittedTotal / $expectedTargetTotal) * 100, 2)
            : 0.0;
        $participationRate = $storedTargetTotal > 0
            ? round((($submittedTotal + $inProgressTotal) / $storedTargetTotal) * 100, 2)
            : 0.0;

        return [
            'assignment_total' => (int) $assignmentRows->count(),
            'assignment_running_total' => (int) $assignmentRows->where('phase', 'berjalan')->count(),
            'assignment_upcoming_total' => (int) $assignmentRows->where('phase', 'terjadwal')->count(),
            'assignment_completed_total' => (int) $assignmentRows->where('phase', 'tuntas')->count(),
            'assignment_overdue_total' => (int) $assignmentRows->where('phase', 'jatuh_tempo')->count(),
            'assignment_retry_total' => (int) $assignmentRows->where('retry_needed', true)->count(),
            'expected_target_total' => $expectedTargetTotal,
            'stored_target_total' => $storedTargetTotal,
            'distribution_missing_total' => $distributionMissingTotal,
            'submitted_total' => $submittedTotal,
            'submitted_manual_total' => $submittedManualTotal,
            'submitted_timeout_total' => $submittedTimeoutTotal,
            'in_progress_total' => $inProgressTotal,
            'not_started_total' => $notStartedTotal,
            'pending_total' => max($expectedTargetTotal - $submittedTotal, 0),
            'pending_review_participant_total' => $pendingReviewParticipants,
            'pending_review_item_total' => $pendingReviewItems,
            'average_score' => $averageScore,
            'completion_rate' => $completionRate,
            'participation_rate' => $participationRate,
        ];
    }

    private function buildAssignmentRow(AssessmentAssignment $assignment): array
    {
        $targets = $assignment->targets->values();
        $submittedTotal = (int) $targets->filter(fn (AssessmentAssignmentTarget $target) => $this->isSubmitted($target))->count();
        $submittedTimeoutTotal = (int) $targets->filter(fn (AssessmentAssignmentTarget $target) => $this->isTimeout($target))->count();
        $submittedManualTotal = max($submittedTotal - $submittedTimeoutTotal, 0);
        $inProgressTotal = (int) $targets->filter(fn (AssessmentAssignmentTarget $target) => $this->isInProgress($target))->count();
        $notStartedTotal = (int) $targets->filter(fn (AssessmentAssignmentTarget $target) => $this->isNotStarted($target))->count();
        $startedTotal = (int) $targets->filter(fn (AssessmentAssignmentTarget $target) => $this->hasStarted($target))->count();
        $pendingReviewTotal = (int) $targets->filter(fn (AssessmentAssignmentTarget $target) => $this->manualPendingItems($target) > 0)->count();
        $timeoutTotal = $submittedTimeoutTotal;
        $storedTargetTotal = (int) $targets->count();
        $distributionMissingTotal = max((int) $assignment->total_target - $storedTargetTotal, 0);
        $targetTotal = (int) $assignment->total_target;
        $pendingTotal = max($targetTotal - $submittedTotal, 0);
        $completionRate = $targetTotal > 0 ? round(($submittedTotal / $targetTotal) * 100, 2) : 0.0;
        $participationRate = $storedTargetTotal > 0 ? round((($submittedTotal + $inProgressTotal) / $storedTargetTotal) * 100, 2) : 0.0;
        $averageScore = $this->averageScore($targets->all());
        $phase = $this->resolveAssignmentPhase($assignment, $submittedTotal, $targetTotal, $startedTotal);

        return [
            'id' => (int) $assignment->id,
            'code' => (string) ($assignment->kode_penugasan ?: '-'),
            'title' => (string) $assignment->judul_penugasan,
            'distribution_status' => (string) ($assignment->status_distribusi ?: 'draft'),
            'target_total' => $targetTotal,
            'stored_target_total' => $storedTargetTotal,
            'distribution_missing_total' => $distributionMissingTotal,
            'submitted_total' => $submittedTotal,
            'submitted_manual_total' => $submittedManualTotal,
            'submitted_timeout_total' => $submittedTimeoutTotal,
            'in_progress_total' => $inProgressTotal,
            'not_started_total' => $notStartedTotal,
            'started_total' => $startedTotal,
            'pending_total' => $pendingTotal,
            'timeout_total' => $timeoutTotal,
            'pending_review_total' => $pendingReviewTotal,
            'pending_review_item_total' => (int) $targets->sum(fn (AssessmentAssignmentTarget $target) => $this->manualPendingItems($target)),
            'average_score' => $averageScore,
            'completion_rate' => $completionRate,
            'participation_rate' => $participationRate,
            'phase' => $phase,
            'phase_label' => $this->resolveAssignmentPhaseLabel($phase),
            'retry_needed' => $assignment->status_distribusi === 'gagal' && $distributionMissingTotal > 0,
            'sessions_total' => (int) $assignment->sessions->count(),
            'period_label' => $this->formatPeriodLabel($assignment),
            'start_label' => $this->formatDateLabel($assignment->tanggal_mulai),
            'end_label' => $this->formatDateLabel($assignment->tanggal_selesai),
        ];
    }

    private function buildKabupatenRows(Collection $targets): Collection
    {
        return $targets
            ->groupBy(fn (AssessmentAssignmentTarget $target) => $this->normalizeKabupatenLabel(optional($target->guru)->kabupaten))
            ->map(function (Collection $items, string $kabupaten) {
                $submittedTotal = (int) $items->filter(fn (AssessmentAssignmentTarget $target) => $this->isSubmitted($target))->count();
                $inProgressTotal = (int) $items->filter(fn (AssessmentAssignmentTarget $target) => $this->isInProgress($target))->count();
                $notStartedTotal = (int) $items->filter(fn (AssessmentAssignmentTarget $target) => $this->isNotStarted($target))->count();
                $timeoutTotal = (int) $items->filter(fn (AssessmentAssignmentTarget $target) => $this->isTimeout($target))->count();
                $pendingReviewTotal = (int) $items->filter(fn (AssessmentAssignmentTarget $target) => $this->manualPendingItems($target) > 0)->count();
                $averageScore = $this->averageScore($items->all());
                $targetTotal = (int) $items->count();

                return [
                    'kabupaten' => $kabupaten,
                    'target_total' => $targetTotal,
                    'submitted_total' => $submittedTotal,
                    'in_progress_total' => $inProgressTotal,
                    'not_started_total' => $notStartedTotal,
                    'timeout_total' => $timeoutTotal,
                    'pending_review_total' => $pendingReviewTotal,
                    'average_score' => $averageScore,
                    'knowledge_percent' => $averageScore !== null ? round(($averageScore / 5) * 100, 2) : null,
                    'completion_rate' => $targetTotal > 0
                        ? round(($submittedTotal / $targetTotal) * 100, 2)
                        : 0.0,
                ];
            })
            ->sortByDesc(fn (array $row) => ($row['submitted_total'] * 1000000) + ($row['target_total'] * 1000) + (int) round(($row['average_score'] ?? 0) * 100))
            ->values();
    }

    private function buildSessionRows(Collection $assignments, Collection $sessions): Collection
    {
        $assignmentMap = $assignments->keyBy('id');

        return $sessions
            ->map(function (AssessmentAssignmentSession $session) use ($assignmentMap) {
                /** @var AssessmentAssignment|null $assignment */
                $assignment = $assignmentMap->get($session->assessment_assignment_id);
                $targets = $assignment
                    ? $assignment->targets
                        ->where('assessment_assignment_session_id', $session->id)
                        ->values()
                    : collect();
                $submittedTotal = (int) $targets->filter(fn (AssessmentAssignmentTarget $target) => $this->isSubmitted($target))->count();
                $assignedTotal = (int) $targets->count();
                $capacityTotal = (int) ($session->kapasitas_peserta ?: 0);

                return [
                    'assignment_id' => (int) $session->assessment_assignment_id,
                    'assignment_title' => $assignment?->judul_penugasan ?: 'Penugasan',
                    'session_id' => (int) $session->id,
                    'session_label' => (string) ($session->label_sesi ?: 'Sesi'),
                    'label' => ($assignment?->judul_penugasan ?: 'Penugasan').' - '.($session->label_sesi ?: 'Sesi'),
                    'assigned_total' => $assignedTotal,
                    'submitted_total' => $submittedTotal,
                    'capacity_total' => $capacityTotal,
                    'occupancy_rate' => $capacityTotal > 0 ? round(($assignedTotal / $capacityTotal) * 100, 2) : 0.0,
                    'completion_rate' => $assignedTotal > 0 ? round(($submittedTotal / $assignedTotal) * 100, 2) : 0.0,
                    'schedule_label' => $session->jadwal_sesi_label ?: 'Jadwal belum diatur',
                ];
            })
            ->sortByDesc(fn (array $row) => ((int) round($row['occupancy_rate'] * 100)) * 1000000 + ($row['assigned_total'] * 1000) + (int) round($row['completion_rate'] * 100))
            ->values();
    }

    private function buildGlobalCharts(
        Collection $assignmentRows,
        Collection $targets,
        Collection $assignments,
        Collection $sessions
    ): array {
        $submittedTotal = (int) $targets->filter(fn (AssessmentAssignmentTarget $target) => $this->isSubmitted($target))->count();
        $timeoutTotal = (int) $targets->filter(fn (AssessmentAssignmentTarget $target) => $this->isTimeout($target))->count();
        $inProgressTotal = (int) $targets->filter(fn (AssessmentAssignmentTarget $target) => $this->isInProgress($target))->count();
        $notStartedTotal = (int) $targets->filter(fn (AssessmentAssignmentTarget $target) => $this->isNotStarted($target))->count();
        $distributionMissingTotal = max((int) $assignmentRows->sum('distribution_missing_total'), 0);
        $manualSubmittedTotal = max($submittedTotal - $timeoutTotal, 0);
        $assignmentChartRows = $assignmentRows
            ->sortByDesc('id')
            ->take(8)
            ->reverse()
            ->values();
        $kabupatenRows = $this->buildKabupatenRows($targets)->take(10)->values();
        $sessionRows = $this->buildSessionRows($assignments, $sessions)->take(8)->reverse()->values();
        $knowledgeRows = $kabupatenRows
            ->filter(fn (array $row) => $row['average_score'] !== null)
            ->sortByDesc('average_score')
            ->values();

        return [
            'participant_status' => [
                'labels' => ['Selesai Manual', 'Selesai Timeout', 'Sedang Mengerjakan', 'Belum Mulai', 'Belum Tersimpan'],
                'data' => [$manualSubmittedTotal, $timeoutTotal, $inProgressTotal, $notStartedTotal, $distributionMissingTotal],
            ],
            'assignment_progress' => [
                'labels' => $assignmentChartRows->pluck('title')->map(fn (string $title) => $this->shortLabel($title, 28))->all(),
                'submitted' => $assignmentChartRows->pluck('submitted_total')->all(),
                'pending' => $assignmentChartRows->pluck('pending_total')->all(),
                'in_progress' => $assignmentChartRows->pluck('in_progress_total')->all(),
            ],
            'kabupaten_completion' => [
                'labels' => $kabupatenRows->pluck('kabupaten')->map(fn (string $label) => $this->shortLabel($label, 24))->all(),
                'submitted' => $kabupatenRows->pluck('submitted_total')->all(),
                'pending' => $kabupatenRows->map(fn (array $row) => max((int) $row['target_total'] - (int) $row['submitted_total'], 0))->all(),
            ],
            'session_utilization' => [
                'labels' => $sessionRows->pluck('session_label')->all(),
                'occupancy' => $sessionRows->pluck('occupancy_rate')->all(),
                'completion' => $sessionRows->pluck('completion_rate')->all(),
            ],
            'kabupaten_knowledge' => [
                'labels' => $knowledgeRows->pluck('kabupaten')->map(fn (string $label) => $this->shortLabel($label, 24))->all(),
                'scores' => $knowledgeRows->pluck('average_score')->all(),
                'participants' => $knowledgeRows->pluck('submitted_total')->all(),
            ],
        ];
    }

    private function buildAssignmentCharts(AssessmentAssignment $assignment): array
    {
        $targets = $assignment->targets->values();
        $submittedTotal = (int) $targets->filter(fn (AssessmentAssignmentTarget $target) => $this->isSubmitted($target))->count();
        $timeoutTotal = (int) $targets->filter(fn (AssessmentAssignmentTarget $target) => $this->isTimeout($target))->count();
        $inProgressTotal = (int) $targets->filter(fn (AssessmentAssignmentTarget $target) => $this->isInProgress($target))->count();
        $notStartedTotal = (int) $targets->filter(fn (AssessmentAssignmentTarget $target) => $this->isNotStarted($target))->count();
        $distributionMissingTotal = max((int) $assignment->total_target - (int) $targets->count(), 0);
        $manualSubmittedTotal = max($submittedTotal - $timeoutTotal, 0);
        $kabupatenRows = $this->buildKabupatenRows($targets)->take(10)->values();
        $sessionRows = $this->buildSessionRows(collect([$assignment]), $assignment->sessions)->take(8)->reverse()->values();
        $scoreLevelRows = collect(LevelKompetensi::cases())
            ->map(function (LevelKompetensi $level) use ($targets) {
                $count = (int) $targets->filter(function (AssessmentAssignmentTarget $target) use ($level) {
                    $score = $this->overallScore($target);

                    return $score !== null && LevelKompetensi::fromScore($score)?->value === $level->value;
                })->count();

                return [
                    'label' => $level->shortLabel(),
                    'count' => $count,
                ];
            })
            ->values();

        return [
            'participant_status' => [
                'labels' => ['Selesai Manual', 'Selesai Timeout', 'Sedang Mengerjakan', 'Belum Mulai', 'Belum Tersimpan'],
                'data' => [$manualSubmittedTotal, $timeoutTotal, $inProgressTotal, $notStartedTotal, $distributionMissingTotal],
            ],
            'session_completion' => [
                'labels' => $sessionRows->pluck('session_label')->all(),
                'submitted' => $sessionRows->pluck('submitted_total')->all(),
                'pending' => $sessionRows->map(fn (array $row) => max((int) $row['assigned_total'] - (int) $row['submitted_total'], 0))->all(),
            ],
            'kabupaten_completion' => [
                'labels' => $kabupatenRows->pluck('kabupaten')->map(fn (string $label) => $this->shortLabel($label, 24))->all(),
                'submitted' => $kabupatenRows->pluck('submitted_total')->all(),
                'pending' => $kabupatenRows->map(fn (array $row) => max((int) $row['target_total'] - (int) $row['submitted_total'], 0))->all(),
            ],
            'score_levels' => [
                'labels' => $scoreLevelRows->pluck('label')->all(),
                'data' => $scoreLevelRows->pluck('count')->all(),
            ],
        ];
    }

    private function serializeTargetRow(AssessmentAssignmentTarget $target): array
    {
        $score = $this->overallScore($target);
        $submittedAt = $target->submitted_at ?: $target->attempt?->submitted_at;
        $statusLabel = $this->resolveTargetStatusLabel($target);

        return [
            'target_id' => (int) $target->id,
            'name' => (string) (optional($target->guru)->nama_lengkap ?: 'Peserta tidak ditemukan'),
            'kabupaten' => (string) (optional($target->guru)->kabupaten ?: '-'),
            'school' => (string) (optional($target->guru)->satuan_pendidikan ?: '-'),
            'status_label' => $statusLabel,
            'session_label' => (string) (optional($target->session)->label_sesi ?: 'Belum dipetakan'),
            'submitted_at' => $submittedAt?->format('d M Y H:i'),
            'started_at' => ($target->started_at ?: $target->attempt?->started_at)?->format('d M Y H:i'),
            'manual_pending_items' => $this->manualPendingItems($target),
            'score' => $score,
            'score_label' => $score !== null ? number_format($score, 2) : null,
            'score_level' => $score !== null ? LevelKompetensi::fromScore($score)?->shortLabel() : null,
            'review_url' => $this->isSubmitted($target) ? route('assessment.assignment.review.show', $target->id) : null,
        ];
    }

    private function resolveAssignmentPhase(
        AssessmentAssignment $assignment,
        int $submittedTotal,
        int $targetTotal,
        int $startedTotal
    ): string {
        if ($targetTotal > 0 && $submittedTotal >= $targetTotal) {
            return 'tuntas';
        }

        $today = now()->startOfDay();
        $startDate = $assignment->tanggal_mulai?->copy()->startOfDay();
        $endDate = $assignment->tanggal_selesai?->copy()->endOfDay();

        if ($endDate && $endDate->lt(now()) && $submittedTotal < $targetTotal) {
            return 'jatuh_tempo';
        }

        if ($startDate && $startDate->gt($today) && $startedTotal === 0) {
            return 'terjadwal';
        }

        return $startedTotal > 0 || $assignment->targets->isNotEmpty()
            ? 'berjalan'
            : 'disiapkan';
    }

    private function resolveAssignmentPhaseLabel(string $phase): string
    {
        return match ($phase) {
            'tuntas' => 'Semua peserta selesai',
            'jatuh_tempo' => 'Perlu tindak lanjut',
            'terjadwal' => 'Belum mulai periode',
            'berjalan' => 'Masih berjalan',
            default => 'Siap dipantau',
        };
    }

    private function resolveTargetStatusLabel(AssessmentAssignmentTarget $target): string
    {
        if ($this->isTimeout($target)) {
            return 'Selesai Timeout';
        }

        if ($this->isSubmitted($target)) {
            return 'Sudah Mengisi';
        }

        if ($this->isInProgress($target)) {
            return 'Sedang Mengerjakan';
        }

        return 'Belum Mengisi';
    }

    private function hasStarted(AssessmentAssignmentTarget $target): bool
    {
        return $target->started_at !== null
            || $target->attempt?->started_at !== null
            || in_array((string) ($target->status ?: $target->attempt?->status), ['dikerjakan', 'selesai', 'in_progress', 'submitted'], true);
    }

    private function isSubmitted(AssessmentAssignmentTarget $target): bool
    {
        return in_array((string) ($target->attempt?->status ?: $target->status), ['submitted', 'selesai'], true)
            || $target->submitted_at !== null
            || $target->attempt?->submitted_at !== null;
    }

    private function isInProgress(AssessmentAssignmentTarget $target): bool
    {
        return ! $this->isSubmitted($target) && (
            (string) $target->status === 'dikerjakan'
            || (string) $target->attempt?->status === 'in_progress'
            || $this->hasStarted($target)
        );
    }

    private function isNotStarted(AssessmentAssignmentTarget $target): bool
    {
        return ! $this->isSubmitted($target) && ! $this->isInProgress($target);
    }

    private function isTimeout(AssessmentAssignmentTarget $target): bool
    {
        return ($target->attempt?->completion_mode ?: $target->completion_mode) === 'timeout'
            || $target->timed_out_at !== null
            || $target->attempt?->timed_out_at !== null;
    }

    private function manualPendingItems(AssessmentAssignmentTarget $target): int
    {
        return (int) data_get($target->attempt?->scoring_summary ?? [], 'manual_review.pending_items', 0);
    }

    private function overallScore(AssessmentAssignmentTarget $target): ?float
    {
        $score = data_get($target->attempt?->scoring_summary ?? [], 'overall.score');

        return is_numeric($score)
            ? round((float) $score, 2)
            : null;
    }

    private function averageScore(array $targets): ?float
    {
        $scores = collect($targets)
            ->map(fn (AssessmentAssignmentTarget $target) => $this->overallScore($target))
            ->filter(fn ($score) => $score !== null)
            ->values();

        if ($scores->isEmpty()) {
            return null;
        }

        return round((float) $scores->avg(), 2);
    }

    private function normalizeKabupatenLabel(?string $kabupaten): string
    {
        $label = trim((string) $kabupaten);

        return $label !== ''
            ? $label
            : 'Kabupaten belum diisi';
    }

    private function shortLabel(string $label, int $limit = 24): string
    {
        $label = trim($label);

        if (mb_strlen($label) <= $limit) {
            return $label;
        }

        return rtrim(mb_substr($label, 0, max($limit - 1, 1))).'…';
    }

    private function formatDateLabel(Carbon|string|null $date): string
    {
        if (! $date) {
            return '-';
        }

        if (! $date instanceof Carbon) {
            $date = Carbon::parse($date);
        }

        return $date->format('d M Y');
    }

    private function formatPeriodLabel(AssessmentAssignment $assignment): string
    {
        $startLabel = $this->formatDateLabel($assignment->tanggal_mulai);
        $endLabel = $this->formatDateLabel($assignment->tanggal_selesai);

        return $startLabel === '-' && $endLabel === '-'
            ? 'Periode belum diatur'
            : $startLabel.' s/d '.$endLabel;
    }
}
