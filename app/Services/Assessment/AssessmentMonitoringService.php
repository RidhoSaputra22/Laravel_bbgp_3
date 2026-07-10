<?php

namespace App\Services\Assessment;

use App\Enum\LevelKompetensi;
use App\Enum\KompetensiGuru;
use App\Models\AssessmentAssignment;
use App\Models\AssessmentAssignmentSession;
use App\Models\AssessmentAssignmentTarget;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AssessmentMonitoringService
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private array $summaryCache = [];

    public function __construct(
        private readonly AssessmentAttemptService $attemptService
    ) {}

    public function buildGlobalDashboard(int $perPage = 10, int $page = 1): array
    {
        $summary = $this->buildGlobalSummarySnapshot();
        $assignmentPaginator = $this->paginateGlobalAssignmentRows($perPage, $page);
        $assignmentChartRows = $this->buildGlobalAssignmentChartRows();
        $kabupatenRows = $this->buildGlobalKabupatenRows();
        $sessionRows = $this->buildGlobalSessionRows();

        return [
            'summary' => $summary,
            'assignment_rows' => $assignmentPaginator->items(),
            'assignment_paginator' => $assignmentPaginator,
            'attention_assignments' => $this->buildAttentionAssignmentRows(),
            'kabupaten_rows' => $kabupatenRows->all(),
            'session_rows' => $sessionRows->all(),
            'charts' => $this->buildGlobalChartsSnapshot(
                $summary,
                $assignmentChartRows,
                $kabupatenRows,
                $sessionRows
            ),
        ];
    }

    public function buildGlobalExplorer(
        array $filters = [],
        string $mode = 'individual',
        int $perPage = 20,
        int $page = 1
    ): array {
        $normalizedFilters = $this->normalizeAssignmentExplorerFilters($filters);
        $resolvedMode = in_array($mode, ['individual', 'summary'], true) ? $mode : 'individual';
        $resolvedPerPage = max(10, min($perPage, 50));
        $resolvedPage = max($page, 1);
        $filterOptions = $this->buildGlobalExplorerFilterOptions();

        $individualPaginator = null;
        $individualRows = [];
        $summaryPayload = [
            'summary' => null,
            'charts' => [],
            'meta' => [
                'cache_ttl_seconds' => 60,
                'has_scored_data' => false,
            ],
        ];

        if ($resolvedMode === 'individual') {
            $individualPaginator = $this->paginateGlobalExplorerIndividuals(
                $normalizedFilters,
                $resolvedPerPage,
                $resolvedPage
            );
            $individualRows = $individualPaginator->items();
        } else {
            $summaryPayload = $this->buildGlobalExplorerSummaryPayload($normalizedFilters);
        }

        return [
            'mode' => $resolvedMode,
            'filters' => [
                'selected' => $normalizedFilters,
                'options' => $filterOptions,
                'has_active_filters' => collect($normalizedFilters)->contains(
                    fn (?string $value) => filled($value)
                ),
            ],
            'individual_rows' => $individualRows,
            'individual_paginator' => $individualPaginator,
            'summary' => $summaryPayload['summary'] ?? null,
            'charts' => $summaryPayload['charts'] ?? [],
            'meta' => $summaryPayload['meta'] ?? [],
        ];
    }

    private function buildGlobalSummarySnapshot(): array
    {
        $assignmentStats = DB::query()
            ->fromSub($this->assignmentMonitoringBaseQuery(), 'assignment_stats')
            ->selectRaw('count(*) as assignment_total')
            ->selectRaw("sum(case when phase = 'berjalan' then 1 else 0 end) as assignment_running_total")
            ->selectRaw("sum(case when phase = 'terjadwal' then 1 else 0 end) as assignment_upcoming_total")
            ->selectRaw("sum(case when phase = 'tuntas' then 1 else 0 end) as assignment_completed_total")
            ->selectRaw("sum(case when phase = 'jatuh_tempo' then 1 else 0 end) as assignment_overdue_total")
            ->selectRaw("sum(case when distribution_status = 'gagal' and distribution_missing_total > 0 then 1 else 0 end) as assignment_retry_total")
            ->selectRaw('sum(target_total) as expected_target_total')
            ->selectRaw('sum(distribution_missing_total) as distribution_missing_total')
            ->first() ?? (object) [];

        $targetStats = $this->fetchGlobalTargetStats();
        $expectedTargetTotal = (int) ($assignmentStats->expected_target_total ?? 0);
        $storedTargetTotal = (int) ($targetStats->stored_target_total ?? 0);
        $submittedTotal = (int) ($targetStats->submitted_total ?? 0);
        $submittedTimeoutTotal = (int) ($targetStats->timeout_total ?? 0);
        $submittedManualTotal = max($submittedTotal - $submittedTimeoutTotal, 0);
        $inProgressTotal = (int) ($targetStats->in_progress_total ?? 0);
        $averageScore = isset($targetStats->average_score) && $targetStats->average_score !== null
            ? round((float) $targetStats->average_score, 2)
            : null;

        return [
            'assignment_total' => (int) ($assignmentStats->assignment_total ?? 0),
            'assignment_running_total' => (int) ($assignmentStats->assignment_running_total ?? 0),
            'assignment_upcoming_total' => (int) ($assignmentStats->assignment_upcoming_total ?? 0),
            'assignment_completed_total' => (int) ($assignmentStats->assignment_completed_total ?? 0),
            'assignment_overdue_total' => (int) ($assignmentStats->assignment_overdue_total ?? 0),
            'assignment_retry_total' => (int) ($assignmentStats->assignment_retry_total ?? 0),
            'expected_target_total' => $expectedTargetTotal,
            'stored_target_total' => $storedTargetTotal,
            'distribution_missing_total' => (int) ($assignmentStats->distribution_missing_total ?? 0),
            'submitted_total' => $submittedTotal,
            'submitted_manual_total' => $submittedManualTotal,
            'submitted_timeout_total' => $submittedTimeoutTotal,
            'in_progress_total' => $inProgressTotal,
            'not_started_total' => (int) ($targetStats->not_started_total ?? 0),
            'pending_total' => max($expectedTargetTotal - $submittedTotal, 0),
            'pending_review_participant_total' => (int) ($targetStats->pending_review_total ?? 0),
            'pending_review_item_total' => (int) ($targetStats->pending_review_item_total ?? 0),
            'average_score' => $averageScore,
            'completion_rate' => $expectedTargetTotal > 0
                ? round(($submittedTotal / $expectedTargetTotal) * 100, 2)
                : 0.0,
            'participation_rate' => $storedTargetTotal > 0
                ? round((($submittedTotal + $inProgressTotal) / $storedTargetTotal) * 100, 2)
                : 0.0,
        ];
    }

    private function paginateGlobalAssignmentRows(int $perPage, int $page): LengthAwarePaginator
    {
        $paginator = $this->assignmentMonitoringBaseQuery()
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'assignment_page', $page);

        $paginator->setCollection(
            $paginator->getCollection()
                ->map(fn (object $row) => $this->serializeGlobalAssignmentRow($row))
                ->values()
        );

        return $paginator;
    }

    private function buildAttentionAssignmentRows(): array
    {
        return $this->assignmentMonitoringBaseQuery()
            ->orderByDesc('distribution_missing_total')
            ->orderByDesc('pending_total')
            ->orderByDesc('timeout_total')
            ->orderByDesc('id')
            ->limit(8)
            ->get()
            ->map(fn (object $row) => $this->serializeGlobalAssignmentRow($row))
            ->values()
            ->all();
    }

    private function buildGlobalAssignmentChartRows(): Collection
    {
        return $this->assignmentMonitoringBaseQuery()
            ->orderByDesc('id')
            ->limit(8)
            ->get()
            ->map(fn (object $row) => $this->serializeGlobalAssignmentRow($row))
            ->values()
            ->reverse()
            ->values();
    }

    private function buildGlobalKabupatenRows(): Collection
    {
        $submittedExpr = $this->assignmentTargetSubmittedSql();
        $inProgressExpr = $this->assignmentTargetInProgressSql();
        $notStartedExpr = $this->assignmentTargetNotStartedSql();
        $timeoutExpr = $this->assignmentTargetTimeoutSql();
        $manualPendingExpr = $this->assignmentTargetManualPendingItemsSql();
        $scoreExpr = $this->assignmentTargetOverallScoreSql();
        $kabupatenExpr = "coalesce(nullif(trim(guru.kabupaten), ''), 'Kabupaten belum diisi')";

        return $this->globalAssignmentTargetBaseQuery()
            ->leftJoin('gurus as guru', 'guru.id', '=', 'target.guru_id')
            ->selectRaw($kabupatenExpr.' as kabupaten')
            ->selectRaw('count(*) as target_total')
            ->selectRaw('sum(case when '.$submittedExpr.' then 1 else 0 end) as submitted_total')
            ->selectRaw('sum(case when '.$inProgressExpr.' then 1 else 0 end) as in_progress_total')
            ->selectRaw('sum(case when '.$notStartedExpr.' then 1 else 0 end) as not_started_total')
            ->selectRaw('sum(case when '.$timeoutExpr.' then 1 else 0 end) as timeout_total')
            ->selectRaw('sum(case when '.$manualPendingExpr.' > 0 then 1 else 0 end) as pending_review_total')
            ->selectRaw('avg(case when '.$scoreExpr.' is not null then '.$scoreExpr.' end) as average_score')
            ->groupByRaw($kabupatenExpr)
            ->get()
            ->map(function (object $row) {
                $targetTotal = (int) ($row->target_total ?? 0);
                $submittedTotal = (int) ($row->submitted_total ?? 0);
                $averageScore = isset($row->average_score) && $row->average_score !== null
                    ? round((float) $row->average_score, 2)
                    : null;

                return [
                    'kabupaten' => (string) ($row->kabupaten ?: 'Kabupaten belum diisi'),
                    'target_total' => $targetTotal,
                    'submitted_total' => $submittedTotal,
                    'in_progress_total' => (int) ($row->in_progress_total ?? 0),
                    'not_started_total' => (int) ($row->not_started_total ?? 0),
                    'timeout_total' => (int) ($row->timeout_total ?? 0),
                    'pending_review_total' => (int) ($row->pending_review_total ?? 0),
                    'average_score' => $averageScore,
                    'knowledge_percent' => $averageScore !== null
                        ? round(($averageScore / 5) * 100, 2)
                        : null,
                    'completion_rate' => $targetTotal > 0
                        ? round(($submittedTotal / $targetTotal) * 100, 2)
                        : 0.0,
                ];
            })
            ->sortByDesc(fn (array $row) => ($row['submitted_total'] * 1000000) + ($row['target_total'] * 1000) + (int) round(($row['average_score'] ?? 0) * 100))
            ->take(10)
            ->values();
    }

    private function buildGlobalSessionRows(): Collection
    {
        $submittedExpr = $this->assignmentTargetSubmittedSql();
        $statsBySessionQuery = $this->globalAssignmentTargetBaseQuery()
            ->select('target.assessment_assignment_session_id')
            ->selectRaw('count(*) as assigned_total')
            ->selectRaw('sum(case when '.$submittedExpr.' then 1 else 0 end) as submitted_total')
            ->groupBy('target.assessment_assignment_session_id');

        return DB::table('assessment_assignment_sessions as assignment_session')
            ->join(
                'assessment_assignments as assignment',
                'assignment.id',
                '=',
                'assignment_session.assessment_assignment_id'
            )
            ->leftJoinSub($statsBySessionQuery, 'session_stats', function ($join) {
                $join->on(
                    'session_stats.assessment_assignment_session_id',
                    '=',
                    'assignment_session.id'
                );
            })
            ->selectRaw('assignment.id as assignment_id')
            ->selectRaw("coalesce(assignment.judul_penugasan, 'Penugasan') as assignment_title")
            ->selectRaw('assignment_session.id as session_id')
            ->selectRaw("coalesce(assignment_session.label_sesi, 'Sesi') as session_label")
            ->selectRaw('assignment_session.waktu_mulai as session_start_at')
            ->selectRaw('assignment_session.waktu_selesai as session_end_at')
            ->selectRaw('coalesce(assignment_session.kapasitas_peserta, 0) as capacity_total')
            ->selectRaw('coalesce(session_stats.assigned_total, 0) as assigned_total')
            ->selectRaw('coalesce(session_stats.submitted_total, 0) as submitted_total')
            ->get()
            ->map(function (object $row) {
                $capacityTotal = (int) ($row->capacity_total ?? 0);
                $assignedTotal = (int) ($row->assigned_total ?? 0);
                $submittedTotal = (int) ($row->submitted_total ?? 0);

                return [
                    'assignment_id' => (int) ($row->assignment_id ?? 0),
                    'assignment_title' => (string) ($row->assignment_title ?: 'Penugasan'),
                    'session_id' => (int) ($row->session_id ?? 0),
                    'session_label' => (string) ($row->session_label ?: 'Sesi'),
                    'label' => (string) ($row->assignment_title ?: 'Penugasan').' - '.($row->session_label ?: 'Sesi'),
                    'assigned_total' => $assignedTotal,
                    'submitted_total' => $submittedTotal,
                    'capacity_total' => $capacityTotal,
                    'occupancy_rate' => $capacityTotal > 0
                        ? round(($assignedTotal / $capacityTotal) * 100, 2)
                        : 0.0,
                    'completion_rate' => $assignedTotal > 0
                        ? round(($submittedTotal / $assignedTotal) * 100, 2)
                        : 0.0,
                    'schedule_label' => $this->formatSessionScheduleLabel(
                        $row->session_start_at ?? null,
                        $row->session_end_at ?? null
                    ),
                ];
            })
            ->sortByDesc(fn (array $row) => ((int) round($row['occupancy_rate'] * 100)) * 1000000 + ($row['assigned_total'] * 1000) + (int) round($row['completion_rate'] * 100))
            ->take(8)
            ->reverse()
            ->values();
    }

    private function buildGlobalChartsSnapshot(
        array $summary,
        Collection $assignmentRows,
        Collection $kabupatenRows,
        Collection $sessionRows
    ): array {
        $knowledgeRows = $kabupatenRows
            ->filter(fn (array $row) => $row['average_score'] !== null)
            ->sortByDesc('average_score')
            ->values();

        return [
            'participant_status' => [
                'labels' => ['Selesai Manual', 'Selesai Timeout', 'Sedang Mengerjakan', 'Belum Mulai', 'Belum Tersimpan'],
                'data' => [
                    (int) ($summary['submitted_manual_total'] ?? 0),
                    (int) ($summary['submitted_timeout_total'] ?? 0),
                    (int) ($summary['in_progress_total'] ?? 0),
                    (int) ($summary['not_started_total'] ?? 0),
                    (int) ($summary['distribution_missing_total'] ?? 0),
                ],
            ],
            'assignment_progress' => [
                'labels' => $assignmentRows
                    ->pluck('title')
                    ->map(fn (string $title) => $this->shortLabel($title, 28))
                    ->all(),
                'submitted' => $assignmentRows->pluck('submitted_total')->all(),
                'pending' => $assignmentRows->pluck('pending_total')->all(),
                'in_progress' => $assignmentRows->pluck('in_progress_total')->all(),
            ],
            'kabupaten_completion' => [
                'labels' => $kabupatenRows
                    ->pluck('kabupaten')
                    ->map(fn (string $label) => $this->shortLabel($label, 24))
                    ->all(),
                'submitted' => $kabupatenRows->pluck('submitted_total')->all(),
                'pending' => $kabupatenRows
                    ->map(fn (array $row) => max((int) $row['target_total'] - (int) $row['submitted_total'], 0))
                    ->all(),
            ],
            'session_utilization' => [
                'labels' => $sessionRows->pluck('session_label')->all(),
                'occupancy' => $sessionRows->pluck('occupancy_rate')->all(),
                'completion' => $sessionRows->pluck('completion_rate')->all(),
            ],
            'kabupaten_knowledge' => [
                'labels' => $knowledgeRows
                    ->pluck('kabupaten')
                    ->map(fn (string $label) => $this->shortLabel($label, 24))
                    ->all(),
                'scores' => $knowledgeRows->pluck('average_score')->all(),
                'participants' => $knowledgeRows->pluck('submitted_total')->all(),
            ],
        ];
    }

    private function assignmentMonitoringBaseQuery()
    {
        $phaseSql = $this->globalAssignmentPhaseSql();

        return DB::table('assessment_assignments as assignment')
            ->leftJoinSub(
                $this->globalAssignmentTargetAggregateQuery(),
                'target_stats',
                fn ($join) => $join->on('target_stats.assessment_assignment_id', '=', 'assignment.id')
            )
            ->leftJoinSub(
                DB::table('assessment_assignment_sessions')
                    ->select('assessment_assignment_id')
                    ->selectRaw('count(*) as sessions_total')
                    ->groupBy('assessment_assignment_id'),
                'session_stats',
                fn ($join) => $join->on('session_stats.assessment_assignment_id', '=', 'assignment.id')
            )
            ->selectRaw('assignment.id as id')
            ->selectRaw("coalesce(assignment.kode_penugasan, '-') as code")
            ->selectRaw("coalesce(assignment.judul_penugasan, 'Penugasan') as title")
            ->selectRaw("coalesce(assignment.status_distribusi, 'draft') as distribution_status")
            ->selectRaw('assignment.total_target as target_total')
            ->selectRaw('assignment.tanggal_mulai as start_date')
            ->selectRaw('assignment.tanggal_selesai as end_date')
            ->selectRaw('coalesce(target_stats.stored_target_total, 0) as stored_target_total')
            ->selectRaw('greatest(assignment.total_target - coalesce(target_stats.stored_target_total, 0), 0) as distribution_missing_total')
            ->selectRaw('coalesce(target_stats.submitted_total, 0) as submitted_total')
            ->selectRaw('greatest(coalesce(target_stats.submitted_total, 0) - coalesce(target_stats.timeout_total, 0), 0) as submitted_manual_total')
            ->selectRaw('coalesce(target_stats.timeout_total, 0) as submitted_timeout_total')
            ->selectRaw('coalesce(target_stats.in_progress_total, 0) as in_progress_total')
            ->selectRaw('coalesce(target_stats.not_started_total, 0) as not_started_total')
            ->selectRaw('coalesce(target_stats.started_total, 0) as started_total')
            ->selectRaw('greatest(assignment.total_target - coalesce(target_stats.submitted_total, 0), 0) as pending_total')
            ->selectRaw('coalesce(target_stats.timeout_total, 0) as timeout_total')
            ->selectRaw('coalesce(target_stats.pending_review_total, 0) as pending_review_total')
            ->selectRaw('coalesce(target_stats.pending_review_item_total, 0) as pending_review_item_total')
            ->selectRaw('target_stats.average_score as average_score')
            ->selectRaw('case when assignment.total_target > 0 then round((coalesce(target_stats.submitted_total, 0) / assignment.total_target) * 100, 2) else 0 end as completion_rate')
            ->selectRaw('case when coalesce(target_stats.stored_target_total, 0) > 0 then round(((coalesce(target_stats.submitted_total, 0) + coalesce(target_stats.in_progress_total, 0)) / coalesce(target_stats.stored_target_total, 0)) * 100, 2) else 0 end as participation_rate')
            ->selectRaw($phaseSql.' as phase')
            ->selectRaw('coalesce(session_stats.sessions_total, 0) as sessions_total')
            ->selectRaw('coalesce(assignment.session_enabled, 1) as session_enabled');
    }

    private function globalAssignmentTargetAggregateQuery()
    {
        $submittedExpr = $this->assignmentTargetSubmittedSql();
        $activityExpr = $this->assignmentTargetActivitySql();
        $inProgressExpr = $this->assignmentTargetInProgressSql();
        $notStartedExpr = $this->assignmentTargetNotStartedSql();
        $timeoutExpr = $this->assignmentTargetTimeoutSql();
        $manualPendingExpr = $this->assignmentTargetManualPendingItemsSql();
        $scoreExpr = $this->assignmentTargetOverallScoreSql();

        return $this->globalAssignmentTargetBaseQuery()
            ->select('target.assessment_assignment_id')
            ->selectRaw('count(*) as stored_target_total')
            ->selectRaw('sum(case when '.$submittedExpr.' then 1 else 0 end) as submitted_total')
            ->selectRaw('sum(case when '.$activityExpr.' then 1 else 0 end) as started_total')
            ->selectRaw('sum(case when '.$inProgressExpr.' then 1 else 0 end) as in_progress_total')
            ->selectRaw('sum(case when '.$notStartedExpr.' then 1 else 0 end) as not_started_total')
            ->selectRaw('sum(case when '.$timeoutExpr.' then 1 else 0 end) as timeout_total')
            ->selectRaw('sum(case when '.$manualPendingExpr.' > 0 then 1 else 0 end) as pending_review_total')
            ->selectRaw('sum('.$manualPendingExpr.') as pending_review_item_total')
            ->selectRaw('avg(case when '.$scoreExpr.' is not null then '.$scoreExpr.' end) as average_score')
            ->groupBy('target.assessment_assignment_id');
    }

    private function fetchGlobalTargetStats(): object
    {
        $submittedExpr = $this->assignmentTargetSubmittedSql();
        $inProgressExpr = $this->assignmentTargetInProgressSql();
        $notStartedExpr = $this->assignmentTargetNotStartedSql();
        $timeoutExpr = $this->assignmentTargetTimeoutSql();
        $manualPendingExpr = $this->assignmentTargetManualPendingItemsSql();
        $scoreExpr = $this->assignmentTargetOverallScoreSql();

        return $this->globalAssignmentTargetBaseQuery()
            ->selectRaw('count(*) as stored_target_total')
            ->selectRaw('sum(case when '.$submittedExpr.' then 1 else 0 end) as submitted_total')
            ->selectRaw('sum(case when '.$inProgressExpr.' then 1 else 0 end) as in_progress_total')
            ->selectRaw('sum(case when '.$notStartedExpr.' then 1 else 0 end) as not_started_total')
            ->selectRaw('sum(case when '.$timeoutExpr.' then 1 else 0 end) as timeout_total')
            ->selectRaw('sum(case when '.$manualPendingExpr.' > 0 then 1 else 0 end) as pending_review_total')
            ->selectRaw('sum('.$manualPendingExpr.') as pending_review_item_total')
            ->selectRaw('avg(case when '.$scoreExpr.' is not null then '.$scoreExpr.' end) as average_score')
            ->first() ?? (object) [];
    }

    private function buildGlobalExplorerFilterOptions(): array
    {
        return Cache::remember(
            'assessment-global-monitor-options',
            now()->addMinutes(10),
            function () {
                return [
                    'kabupaten' => $this->buildGlobalExplorerOptionRows(
                        $this->assignmentTargetKabupatenSql(),
                        'kabupaten'
                    ),
                    'jabatan' => $this->buildGlobalExplorerOptionRows(
                        $this->assignmentTargetJabatanSql(),
                        'jabatan'
                    ),
                    'satuan_pendidikan' => $this->buildGlobalExplorerOptionRows(
                        $this->assignmentTargetSchoolSql(),
                        'satuan_pendidikan'
                    ),
                ];
            }
        );
    }

    private function buildGlobalExplorerOptionRows(string $expression, string $alias): array
    {
        return $this->globalAssignmentTargetGuruBaseQuery()
            ->selectRaw($expression.' as '.$alias)
            ->selectRaw('count(*) as participant_total')
            ->groupByRaw($expression)
            ->orderBy($alias)
            ->get()
            ->map(function (object $row) use ($alias) {
                return [
                    'value' => (string) data_get($row, $alias, ''),
                    'label' => (string) data_get($row, $alias, ''),
                    'participant_total' => (int) ($row->participant_total ?? 0),
                ];
            })
            ->filter(fn (array $item) => $item['value'] !== '')
            ->values()
            ->all();
    }

    private function paginateGlobalExplorerIndividuals(
        array $filters,
        int $perPage,
        int $page
    ): LengthAwarePaginator {
        $paginator = $this->globalAssignmentDetailListBaseQuery($filters)
            ->orderByDesc('target.assessment_assignment_id')
            ->orderByDesc('target.id')
            ->paginate($perPage, ['*'], 'monitor_page', $page)
            ->withQueryString();

        $paginator->setCollection(
            $paginator->getCollection()
                ->map(fn (object $row) => $this->serializeAssignmentDetailRow($row))
                ->values()
        );
        $paginator->fragment('monitoring-explorer');

        return $paginator;
    }

    private function buildGlobalExplorerSummaryPayload(array $filters): array
    {
        $cacheTtlSeconds = 60;
        $cacheKey = 'assessment-global-monitor-summary:'.md5(json_encode($filters));

        return Cache::remember(
            $cacheKey,
            now()->addSeconds($cacheTtlSeconds),
            function () use ($filters, $cacheTtlSeconds) {
                $stats = $this->fetchGlobalExplorerStats($filters);
                $targetTotal = (int) ($stats->filtered_target_total ?? 0);
                $assignmentTotal = (int) ($stats->assignment_total ?? 0);
                $submittedTotal = (int) ($stats->submitted_total ?? 0);
                $inProgressTotal = (int) ($stats->in_progress_total ?? 0);
                $notStartedTotal = (int) ($stats->not_started_total ?? 0);
                $timeoutTotal = (int) ($stats->timeout_total ?? 0);
                $pendingReviewTotal = (int) ($stats->pending_review_total ?? 0);
                $scoredTotal = (int) ($stats->scored_total ?? 0);
                $manualSubmittedTotal = max($submittedTotal - $timeoutTotal, 0);
                $averageScore = isset($stats->average_score) && $stats->average_score !== null
                    ? round((float) $stats->average_score, 2)
                    : null;

                $competencyRows = collect(KompetensiGuru::cases())
                    ->map(function (KompetensiGuru $kompetensi) use ($stats) {
                        $scoreColumn = $kompetensi->value.'_average_score';
                        $averageScore = isset($stats->{$scoreColumn}) && $stats->{$scoreColumn} !== null
                            ? round((float) $stats->{$scoreColumn}, 2)
                            : null;

                        return [
                            'key' => $kompetensi->value,
                            'label' => $kompetensi->label(),
                            'average_score' => $averageScore,
                            'formatted_score' => $averageScore !== null ? number_format($averageScore, 2) : '-',
                            'level_label' => $averageScore !== null
                                ? LevelKompetensi::fromScore($averageScore)?->shortLabel()
                                : null,
                        ];
                    })
                    ->values();

                $levelRows = collect(LevelKompetensi::cases())
                    ->map(function (LevelKompetensi $level) use ($stats) {
                        return [
                            'value' => $level->value,
                            'label' => $level->shortLabel(),
                            'count' => (int) data_get($stats, 'level_'.$level->value, 0),
                        ];
                    })
                    ->values();

                $dominantLevel = $levelRows
                    ->sortByDesc(fn (array $row) => ($row['count'] * 10) + $row['value'])
                    ->first(fn (array $row) => $row['count'] > 0);

                return [
                    'summary' => [
                        'filtered_target_total' => $targetTotal,
                        'assignment_total' => $assignmentTotal,
                        'submitted_total' => $submittedTotal,
                        'scored_total' => $scoredTotal,
                        'in_progress_total' => $inProgressTotal,
                        'not_started_total' => $notStartedTotal,
                        'timeout_total' => $timeoutTotal,
                        'pending_review_total' => $pendingReviewTotal,
                        'average_score' => $averageScore,
                        'average_score_label' => $averageScore !== null ? number_format($averageScore, 2) : '-',
                        'dominant_level_label' => $dominantLevel['label'] ?? 'Belum ada level',
                        'dominant_level_total' => (int) ($dominantLevel['count'] ?? 0),
                        'completion_rate' => $targetTotal > 0
                            ? round(($submittedTotal / $targetTotal) * 100, 2)
                            : 0.0,
                        'participation_rate' => $targetTotal > 0
                            ? round((($submittedTotal + $inProgressTotal) / $targetTotal) * 100, 2)
                            : 0.0,
                        'competencies' => $competencyRows->all(),
                        'levels' => $levelRows->all(),
                    ],
                    'charts' => [
                        'status' => [
                            'labels' => ['Selesai Manual', 'Sedang Mengerjakan', 'Belum Mulai', 'Selesai Timeout'],
                            'data' => [$manualSubmittedTotal, $inProgressTotal, $notStartedTotal, $timeoutTotal],
                        ],
                        'levels' => [
                            'labels' => $levelRows->pluck('label')->all(),
                            'data' => $levelRows->pluck('count')->all(),
                        ],
                        'radar' => [
                            'labels' => $competencyRows->pluck('label')->all(),
                            'data' => $competencyRows
                                ->map(fn (array $row) => $row['average_score'] ?? 0)
                                ->all(),
                            'max_score' => 5,
                        ],
                        'competencies' => [
                            'labels' => $competencyRows->pluck('label')->all(),
                            'data' => $competencyRows
                                ->map(fn (array $row) => $row['average_score'] ?? 0)
                                ->all(),
                        ],
                    ],
                    'meta' => [
                        'cache_ttl_seconds' => $cacheTtlSeconds,
                        'has_scored_data' => $scoredTotal > 0,
                    ],
                ];
            }
        );
    }

    private function fetchGlobalExplorerStats(array $filters): object
    {
        $submittedExpr = $this->assignmentTargetSubmittedSql();
        $inProgressExpr = $this->assignmentTargetInProgressSql();
        $notStartedExpr = $this->assignmentTargetNotStartedSql();
        $timeoutExpr = $this->assignmentTargetTimeoutSql();
        $manualPendingExpr = $this->assignmentTargetManualPendingItemsSql();
        $scoreExpr = $this->assignmentTargetOverallScoreSql();

        $query = $this->globalAssignmentExplorerBaseQuery($filters)
            ->selectRaw('count(*) as filtered_target_total')
            ->selectRaw('count(distinct target.assessment_assignment_id) as assignment_total')
            ->selectRaw('sum(case when '.$submittedExpr.' then 1 else 0 end) as submitted_total')
            ->selectRaw('sum(case when '.$inProgressExpr.' then 1 else 0 end) as in_progress_total')
            ->selectRaw('sum(case when '.$notStartedExpr.' then 1 else 0 end) as not_started_total')
            ->selectRaw('sum(case when '.$timeoutExpr.' then 1 else 0 end) as timeout_total')
            ->selectRaw('sum(case when '.$manualPendingExpr.' > 0 then 1 else 0 end) as pending_review_total')
            ->selectRaw('sum(case when '.$scoreExpr.' is not null then 1 else 0 end) as scored_total')
            ->selectRaw('avg(case when '.$scoreExpr.' is not null then '.$scoreExpr.' end) as average_score')
            ->selectRaw('sum(case when '.$scoreExpr.' >= 1.00 and '.$scoreExpr.' < 1.80 then 1 else 0 end) as level_1')
            ->selectRaw('sum(case when '.$scoreExpr.' >= 1.80 and '.$scoreExpr.' < 2.60 then 1 else 0 end) as level_2')
            ->selectRaw('sum(case when '.$scoreExpr.' >= 2.60 and '.$scoreExpr.' < 3.40 then 1 else 0 end) as level_3')
            ->selectRaw('sum(case when '.$scoreExpr.' >= 3.40 and '.$scoreExpr.' < 4.20 then 1 else 0 end) as level_4')
            ->selectRaw('sum(case when '.$scoreExpr.' >= 4.20 and '.$scoreExpr.' <= 5.00 then 1 else 0 end) as level_5');

        foreach (KompetensiGuru::cases() as $kompetensi) {
            $competencyScoreExpr = $this->assignmentTargetCompetencyScoreSql($kompetensi);
            $query->selectRaw(
                'avg(case when '.$competencyScoreExpr.' is not null then '.$competencyScoreExpr.' end) as '
                .$kompetensi->value.'_average_score'
            );
        }

        return $query->first() ?? (object) [];
    }

    private function globalAssignmentTargetGuruBaseQuery()
    {
        return DB::table('assessment_assignment_targets as target')
            ->join('gurus as guru', 'guru.id', '=', 'target.guru_id');
    }

    private function globalAssignmentExplorerBaseQuery(array $filters = [])
    {
        $query = $this->globalAssignmentTargetBaseQuery()
            ->leftJoin('gurus as guru', 'guru.id', '=', 'target.guru_id');

        $this->applyAssignmentExplorerFilters($query, $filters);

        return $query;
    }

    private function globalAssignmentDetailListBaseQuery(array $filters = [])
    {
        $submittedExpr = $this->assignmentTargetSubmittedSql();
        $timeoutExpr = $this->assignmentTargetTimeoutSql();
        $inProgressExpr = $this->assignmentTargetInProgressSql();
        $manualPendingExpr = $this->assignmentTargetManualPendingItemsSql();
        $scoreExpr = $this->assignmentTargetOverallScoreSql();

        return $this->globalAssignmentExplorerBaseQuery($filters)
            ->leftJoin(
                'assessment_assignment_sessions as assignment_session',
                'assignment_session.id',
                '=',
                'target.assessment_assignment_session_id'
            )
            ->leftJoin(
                'assessment_assignments as assignment',
                'assignment.id',
                '=',
                'target.assessment_assignment_id'
            )
            ->selectRaw('target.id as target_id')
            ->selectRaw('target.assessment_assignment_id as assignment_id')
            ->selectRaw("coalesce(assignment.judul_penugasan, 'Penugasan') as assignment_title")
            ->selectRaw("coalesce(assignment.kode_penugasan, '-') as assignment_code")
            ->selectRaw("coalesce(guru.nama_lengkap, 'Peserta tidak ditemukan') as participant_name")
            ->selectRaw("coalesce(nullif(trim(guru.kabupaten), ''), '-') as kabupaten")
            ->selectRaw("coalesce(nullif(trim(guru.eksternal_jabatan), ''), nullif(trim(guru.jenis_jabatan), ''), '-') as jabatan")
            ->selectRaw("coalesce(nullif(trim(guru.satuan_pendidikan), ''), '-') as school")
            ->selectRaw('coalesce(assignment.session_enabled, 1) as session_enabled')
            ->selectRaw('assignment_session.label_sesi as session_label')
            ->selectRaw('coalesce(target.submitted_at, attempt.submitted_at) as resolved_submitted_at')
            ->selectRaw('coalesce(target.started_at, attempt.started_at) as resolved_started_at')
            ->selectRaw('case when '.$submittedExpr.' then 1 else 0 end as is_submitted')
            ->selectRaw('case when '.$timeoutExpr.' then 1 else 0 end as is_timeout')
            ->selectRaw('case when '.$inProgressExpr.' then 1 else 0 end as is_in_progress')
            ->selectRaw($manualPendingExpr.' as manual_pending_items')
            ->selectRaw($scoreExpr.' as score');
    }

    private function globalAssignmentTargetBaseQuery()
    {
        return DB::table('assessment_assignment_targets as target')
            ->leftJoin(
                'assessment_attempts as attempt',
                'attempt.assessment_assignment_target_id',
                '=',
                'target.id'
            );
    }

    private function serializeGlobalAssignmentRow(object $row): array
    {
        $targetTotal = (int) ($row->target_total ?? 0);
        $storedTargetTotal = (int) ($row->stored_target_total ?? 0);
        $averageScore = isset($row->average_score) && $row->average_score !== null
            ? round((float) $row->average_score, 2)
            : null;
        $phase = (string) ($row->phase ?: 'disiapkan');

        return [
            'id' => (int) ($row->id ?? 0),
            'code' => (string) ($row->code ?: '-'),
            'title' => (string) ($row->title ?: 'Penugasan'),
            'distribution_status' => (string) ($row->distribution_status ?: 'draft'),
            'session_enabled' => $this->normalizeSessionEnabled($row->session_enabled ?? true),
            'session_mode_label' => $this->resolveSessionModeLabel(
                $this->normalizeSessionEnabled($row->session_enabled ?? true)
            ),
            'target_total' => $targetTotal,
            'stored_target_total' => $storedTargetTotal,
            'distribution_missing_total' => (int) ($row->distribution_missing_total ?? max($targetTotal - $storedTargetTotal, 0)),
            'submitted_total' => (int) ($row->submitted_total ?? 0),
            'submitted_manual_total' => (int) ($row->submitted_manual_total ?? 0),
            'submitted_timeout_total' => (int) ($row->submitted_timeout_total ?? 0),
            'in_progress_total' => (int) ($row->in_progress_total ?? 0),
            'not_started_total' => (int) ($row->not_started_total ?? 0),
            'started_total' => (int) ($row->started_total ?? 0),
            'pending_total' => (int) ($row->pending_total ?? 0),
            'timeout_total' => (int) ($row->timeout_total ?? 0),
            'pending_review_total' => (int) ($row->pending_review_total ?? 0),
            'pending_review_item_total' => (int) ($row->pending_review_item_total ?? 0),
            'average_score' => $averageScore,
            'completion_rate' => round((float) ($row->completion_rate ?? 0), 2),
            'participation_rate' => round((float) ($row->participation_rate ?? 0), 2),
            'phase' => $phase,
            'phase_label' => $this->resolveAssignmentPhaseLabel($phase),
            'retry_needed' => (string) ($row->distribution_status ?? '') === 'gagal'
                && max($targetTotal - $storedTargetTotal, 0) > 0,
            'sessions_total' => (int) ($row->sessions_total ?? 0),
            'period_label' => $this->formatPeriodLabelFromRange(
                $row->start_date ?? null,
                $row->end_date ?? null
            ),
            'start_label' => $this->formatDateLabel($row->start_date ?? null),
            'end_label' => $this->formatDateLabel($row->end_date ?? null),
        ];
    }

    private function globalAssignmentPhaseSql(): string
    {
        $today = now()->toDateString();

        return "case
            when assignment.total_target > 0 and coalesce(target_stats.submitted_total, 0) >= assignment.total_target then 'tuntas'
            when assignment.tanggal_selesai is not null and assignment.tanggal_selesai < '{$today}' and coalesce(target_stats.submitted_total, 0) < assignment.total_target then 'jatuh_tempo'
            when assignment.tanggal_mulai is not null and assignment.tanggal_mulai > '{$today}' and coalesce(target_stats.started_total, 0) = 0 then 'terjadwal'
            when coalesce(target_stats.started_total, 0) > 0 or coalesce(target_stats.stored_target_total, 0) > 0 then 'berjalan'
            else 'disiapkan'
        end";
    }

    public function buildAssignmentDetail(AssessmentAssignment $assignment): array
    {
        $assignment->loadMissing('sessions');
        $summary = $this->buildAssignmentDetailSummary($assignment);

        return [
            'summary' => $summary,
            'lists' => [
                'submitted_participants' => $this->buildSubmittedParticipantRows($assignment),
                'pending_participants' => $this->buildPendingParticipantRows($assignment),
                'pending_review_participants' => $this->buildPendingReviewParticipantRows($assignment),
                'low_score_participants' => $this->buildLowScoreParticipantRows($assignment),
            ],
            'charts' => $this->buildAssignmentDetailCharts($assignment, $summary),
            'kabupaten_rows' => [],
            'session_rows' => [],
        ];
    }

    public function buildAssignmentExplorer(
        AssessmentAssignment $assignment,
        array $filters = [],
        string $mode = 'individual',
        int $perPage = 20,
        int $page = 1
    ): array {
        $normalizedFilters = $this->normalizeAssignmentExplorerFilters($filters);
        $resolvedMode = in_array($mode, ['individual', 'summary'], true) ? $mode : 'individual';
        $resolvedPerPage = max(10, min($perPage, 50));
        $resolvedPage = max($page, 1);
        $filterOptions = $this->buildAssignmentExplorerFilterOptions($assignment);

        $individualPaginator = null;
        $individualRows = [];
        $summaryPayload = [
            'summary' => null,
            'charts' => [],
            'meta' => [
                'cache_ttl_seconds' => 60,
                'has_scored_data' => false,
            ],
        ];

        if ($resolvedMode === 'individual') {
            $individualPaginator = $this->paginateAssignmentExplorerIndividuals(
                $assignment,
                $normalizedFilters,
                $resolvedPerPage,
                $resolvedPage
            );
            $individualRows = $individualPaginator->items();
        } else {
            $summaryPayload = $this->buildAssignmentExplorerSummaryPayload($assignment, $normalizedFilters);
        }

        return [
            'mode' => $resolvedMode,
            'filters' => [
                'selected' => $normalizedFilters,
                'options' => $filterOptions,
                'has_active_filters' => collect($normalizedFilters)->contains(
                    fn (?string $value) => filled($value)
                ),
            ],
            'individual_rows' => $individualRows,
            'individual_paginator' => $individualPaginator,
            'summary' => $summaryPayload['summary'] ?? null,
            'charts' => $summaryPayload['charts'] ?? [],
            'meta' => $summaryPayload['meta'] ?? [],
        ];
    }

    private function loadAssignments(): Collection
    {
        return AssessmentAssignment::query()
            ->with($this->monitoringRelations())
            ->withCount(['targets', 'sessions'])
            ->orderByDesc('id')
            ->get();
    }

    private function prepareAssignments(Collection $assignments): Collection
    {
        if ($assignments instanceof EloquentCollection) {
            $assignments->loadMissing($this->monitoringRelations());

            return $assignments->values();
        }

        return $assignments
            ->map(fn (AssessmentAssignment $assignment) => $this->prepareAssignment($assignment))
            ->values();
    }

    private function prepareAssignment(AssessmentAssignment $assignment): AssessmentAssignment
    {
        $assignment->loadMissing($this->monitoringRelations());

        return $assignment;
    }

    private function monitoringRelations(): array
    {
        return [
            'targets' => function ($query) {
                $query->select([
                    'id',
                    'assessment_assignment_id',
                    'assessment_assignment_session_id',
                    'guru_id',
                    'status',
                    'assigned_at',
                    'started_at',
                    'submitted_at',
                    'completion_mode',
                    'timed_out_at',
                ]);
                $query->with([
                    'guru:id,nama_lengkap,email,eksternal_jabatan,jenis_jabatan,satuan_pendidikan,kabupaten',
                    'session:id,label_sesi,waktu_mulai,waktu_selesai',
                    'attempt:id,assessment_assignment_target_id,status,scoring_summary,started_at,submitted_at,completion_mode,timed_out_at,disqualified_at,disqualification_reason',
                ]);
            },
            'sessions',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildAssignmentDetailSummary(AssessmentAssignment $assignment): array
    {
        $stats = $this->fetchAssignmentDetailStats($assignment->id);
        $targetTotal = (int) $assignment->total_target;
        $storedTargetTotal = (int) ($stats->stored_target_total ?? 0);
        $submittedTotal = (int) ($stats->submitted_total ?? 0);
        $timeoutTotal = (int) ($stats->timeout_total ?? 0);
        $inProgressTotal = (int) ($stats->in_progress_total ?? 0);
        $notStartedTotal = (int) ($stats->not_started_total ?? 0);
        $pendingReviewTotal = (int) ($stats->pending_review_total ?? 0);
        $pendingReviewItemTotal = (int) ($stats->pending_review_item_total ?? 0);
        $averageScore = isset($stats->average_score) && $stats->average_score !== null
            ? round((float) $stats->average_score, 2)
            : null;

        return [
            'target_total' => $targetTotal,
            'stored_target_total' => $storedTargetTotal,
            'distribution_missing_total' => max($targetTotal - $storedTargetTotal, 0),
            'submitted_total' => $submittedTotal,
            'pending_total' => max($targetTotal - $submittedTotal, 0),
            'in_progress_total' => $inProgressTotal,
            'not_started_total' => $notStartedTotal,
            'timeout_total' => $timeoutTotal,
            'pending_review_total' => $pendingReviewTotal,
            'pending_review_item_total' => $pendingReviewItemTotal,
            'average_score' => $averageScore,
            'completion_rate' => $targetTotal > 0
                ? round(($submittedTotal / $targetTotal) * 100, 2)
                : 0.0,
            'participation_rate' => $storedTargetTotal > 0
                ? round((($submittedTotal + $inProgressTotal) / $storedTargetTotal) * 100, 2)
                : 0.0,
        ];
    }

    private function buildAssignmentDetailCharts(AssessmentAssignment $assignment, array $summary): array
    {
        $kabupatenRows = $this->buildAssignmentKabupatenRows($assignment)->take(10)->values();
        $sessionRows = $this->buildAssignmentSessionRows($assignment);
        $scoreLevelRows = $this->buildAssignmentScoreLevels($assignment);
        $timeoutTotal = (int) ($summary['timeout_total'] ?? 0);
        $submittedTotal = (int) ($summary['submitted_total'] ?? 0);
        $manualSubmittedTotal = max($submittedTotal - $timeoutTotal, 0);

        return [
            'participant_status' => [
                'labels' => ['Selesai Manual', 'Selesai Timeout', 'Sedang Mengerjakan', 'Belum Mulai', 'Belum Tersimpan'],
                'data' => [
                    $manualSubmittedTotal,
                    $timeoutTotal,
                    (int) ($summary['in_progress_total'] ?? 0),
                    (int) ($summary['not_started_total'] ?? 0),
                    (int) ($summary['distribution_missing_total'] ?? 0),
                ],
            ],
            'session_completion' => [
                'labels' => $sessionRows->pluck('session_label')->all(),
                'submitted' => $sessionRows->pluck('submitted_total')->all(),
                'pending' => $sessionRows
                    ->map(fn (array $row) => max((int) $row['assigned_total'] - (int) $row['submitted_total'], 0))
                    ->all(),
            ],
            'kabupaten_completion' => [
                'labels' => $kabupatenRows
                    ->pluck('kabupaten')
                    ->map(fn (string $label) => $this->shortLabel($label, 24))
                    ->all(),
                'submitted' => $kabupatenRows->pluck('submitted_total')->all(),
                'pending' => $kabupatenRows
                    ->map(fn (array $row) => max((int) $row['target_total'] - (int) $row['submitted_total'], 0))
                    ->all(),
            ],
            'score_levels' => [
                'labels' => $scoreLevelRows->pluck('label')->all(),
                'data' => $scoreLevelRows->pluck('count')->all(),
            ],
        ];
    }

    private function buildSubmittedParticipantRows(AssessmentAssignment $assignment, int $limit = 10): array
    {
        $submittedExpr = $this->assignmentTargetSubmittedSql();

        return $this->assignmentDetailListBaseQuery($assignment)
            ->whereRaw($submittedExpr)
            ->orderByRaw('coalesce(target.submitted_at, attempt.submitted_at) desc')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => $this->serializeAssignmentDetailRow($row))
            ->all();
    }

    private function buildPendingParticipantRows(AssessmentAssignment $assignment, int $limit = 10): array
    {
        $submittedExpr = $this->assignmentTargetSubmittedSql();

        return $this->assignmentDetailListBaseQuery($assignment)
            ->whereRaw('not '.$submittedExpr)
            ->orderByRaw('case when target.assigned_at is null then 1 else 0 end')
            ->orderBy('target.assigned_at')
            ->orderBy('target.id')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => $this->serializeAssignmentDetailRow($row))
            ->all();
    }

    private function buildPendingReviewParticipantRows(AssessmentAssignment $assignment, int $limit = 10): array
    {
        $submittedExpr = $this->assignmentTargetSubmittedSql();
        $manualPendingExpr = $this->assignmentTargetManualPendingItemsSql();

        return $this->assignmentDetailListBaseQuery($assignment)
            ->whereRaw($submittedExpr)
            ->whereRaw($manualPendingExpr.' > 0')
            ->orderByRaw($manualPendingExpr.' desc')
            ->orderByRaw('coalesce(target.submitted_at, attempt.submitted_at) desc')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => $this->serializeAssignmentDetailRow($row))
            ->all();
    }

    private function buildLowScoreParticipantRows(AssessmentAssignment $assignment, int $limit = 10): array
    {
        $submittedExpr = $this->assignmentTargetSubmittedSql();
        $scoreExpr = $this->assignmentTargetOverallScoreSql();

        return $this->assignmentDetailListBaseQuery($assignment)
            ->whereRaw($submittedExpr)
            ->whereRaw($scoreExpr.' is not null')
            ->orderByRaw($scoreExpr.' asc')
            ->orderByRaw('coalesce(target.submitted_at, attempt.submitted_at) desc')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => $this->serializeAssignmentDetailRow($row))
            ->all();
    }

    private function buildAssignmentKabupatenRows(AssessmentAssignment $assignment): Collection
    {
        $submittedExpr = $this->assignmentTargetSubmittedSql();
        $inProgressExpr = $this->assignmentTargetInProgressSql();
        $notStartedExpr = $this->assignmentTargetNotStartedSql();
        $timeoutExpr = $this->assignmentTargetTimeoutSql();
        $manualPendingExpr = $this->assignmentTargetManualPendingItemsSql();
        $scoreExpr = $this->assignmentTargetOverallScoreSql();
        $kabupatenExpr = "coalesce(nullif(trim(guru.kabupaten), ''), 'Kabupaten belum diisi')";

        return $this->assignmentTargetBaseQuery($assignment->id)
            ->leftJoin('gurus as guru', 'guru.id', '=', 'target.guru_id')
            ->selectRaw($kabupatenExpr.' as kabupaten')
            ->selectRaw('count(*) as target_total')
            ->selectRaw('sum(case when '.$submittedExpr.' then 1 else 0 end) as submitted_total')
            ->selectRaw('sum(case when '.$inProgressExpr.' then 1 else 0 end) as in_progress_total')
            ->selectRaw('sum(case when '.$notStartedExpr.' then 1 else 0 end) as not_started_total')
            ->selectRaw('sum(case when '.$timeoutExpr.' then 1 else 0 end) as timeout_total')
            ->selectRaw('sum(case when '.$manualPendingExpr.' > 0 then 1 else 0 end) as pending_review_total')
            ->selectRaw('avg(case when '.$scoreExpr.' is not null then '.$scoreExpr.' end) as average_score')
            ->groupByRaw($kabupatenExpr)
            ->get()
            ->map(function ($row) {
                $targetTotal = (int) ($row->target_total ?? 0);
                $submittedTotal = (int) ($row->submitted_total ?? 0);
                $averageScore = isset($row->average_score) && $row->average_score !== null
                    ? round((float) $row->average_score, 2)
                    : null;

                return [
                    'kabupaten' => (string) ($row->kabupaten ?: 'Kabupaten belum diisi'),
                    'target_total' => $targetTotal,
                    'submitted_total' => $submittedTotal,
                    'in_progress_total' => (int) ($row->in_progress_total ?? 0),
                    'not_started_total' => (int) ($row->not_started_total ?? 0),
                    'timeout_total' => (int) ($row->timeout_total ?? 0),
                    'pending_review_total' => (int) ($row->pending_review_total ?? 0),
                    'average_score' => $averageScore,
                    'knowledge_percent' => $averageScore !== null
                        ? round(($averageScore / 5) * 100, 2)
                        : null,
                    'completion_rate' => $targetTotal > 0
                        ? round(($submittedTotal / $targetTotal) * 100, 2)
                        : 0.0,
                ];
            })
            ->sortByDesc(fn (array $row) => ($row['submitted_total'] * 1000000) + ($row['target_total'] * 1000) + (int) round(($row['average_score'] ?? 0) * 100))
            ->values();
    }

    private function buildAssignmentSessionRows(AssessmentAssignment $assignment): Collection
    {
        $assignment->loadMissing('sessions');
        $submittedExpr = $this->assignmentTargetSubmittedSql();
        $statsBySessionId = $this->assignmentTargetBaseQuery($assignment->id)
            ->select('target.assessment_assignment_session_id')
            ->selectRaw('count(*) as assigned_total')
            ->selectRaw('sum(case when '.$submittedExpr.' then 1 else 0 end) as submitted_total')
            ->groupBy('target.assessment_assignment_session_id')
            ->get()
            ->keyBy('assessment_assignment_session_id');

        return $assignment->sessions
            ->map(function (AssessmentAssignmentSession $session) use ($assignment, $statsBySessionId) {
                $stats = $statsBySessionId->get($session->id);
                $assignedTotal = (int) data_get($stats, 'assigned_total', 0);
                $submittedTotal = (int) data_get($stats, 'submitted_total', 0);
                $capacityTotal = (int) ($session->kapasitas_peserta ?: 0);

                return [
                    'assignment_id' => (int) $assignment->id,
                    'assignment_title' => (string) $assignment->judul_penugasan,
                    'session_id' => (int) $session->id,
                    'session_label' => (string) ($session->label_sesi ?: 'Sesi'),
                    'label' => $assignment->judul_penugasan.' - '.($session->label_sesi ?: 'Sesi'),
                    'assigned_total' => $assignedTotal,
                    'submitted_total' => $submittedTotal,
                    'capacity_total' => $capacityTotal,
                    'occupancy_rate' => $capacityTotal > 0
                        ? round(($assignedTotal / $capacityTotal) * 100, 2)
                        : 0.0,
                    'completion_rate' => $assignedTotal > 0
                        ? round(($submittedTotal / $assignedTotal) * 100, 2)
                        : 0.0,
                    'schedule_label' => $session->jadwal_sesi_label ?: 'Jadwal belum diatur',
                ];
            })
            ->sortByDesc(fn (array $row) => ((int) round($row['occupancy_rate'] * 100)) * 1000000 + ($row['assigned_total'] * 1000) + (int) round($row['completion_rate'] * 100))
            ->take(8)
            ->reverse()
            ->values();
    }

    private function buildAssignmentScoreLevels(AssessmentAssignment $assignment): Collection
    {
        $scoreExpr = $this->assignmentTargetOverallScoreSql();
        $counts = $this->assignmentTargetBaseQuery($assignment->id)
            ->selectRaw('sum(case when '.$scoreExpr.' >= 1.00 and '.$scoreExpr.' < 1.80 then 1 else 0 end) as level_1')
            ->selectRaw('sum(case when '.$scoreExpr.' >= 1.80 and '.$scoreExpr.' < 2.60 then 1 else 0 end) as level_2')
            ->selectRaw('sum(case when '.$scoreExpr.' >= 2.60 and '.$scoreExpr.' < 3.40 then 1 else 0 end) as level_3')
            ->selectRaw('sum(case when '.$scoreExpr.' >= 3.40 and '.$scoreExpr.' < 4.20 then 1 else 0 end) as level_4')
            ->selectRaw('sum(case when '.$scoreExpr.' >= 4.20 and '.$scoreExpr.' <= 5.00 then 1 else 0 end) as level_5')
            ->first();

        return collect(LevelKompetensi::cases())
            ->map(function (LevelKompetensi $level) use ($counts) {
                return [
                    'label' => $level->shortLabel(),
                    'count' => (int) data_get($counts, 'level_'.$level->value, 0),
                ];
            })
            ->values();
    }

    private function fetchAssignmentDetailStats(int $assignmentId): object
    {
        $submittedExpr = $this->assignmentTargetSubmittedSql();
        $inProgressExpr = $this->assignmentTargetInProgressSql();
        $notStartedExpr = $this->assignmentTargetNotStartedSql();
        $timeoutExpr = $this->assignmentTargetTimeoutSql();
        $manualPendingExpr = $this->assignmentTargetManualPendingItemsSql();
        $scoreExpr = $this->assignmentTargetOverallScoreSql();

        return $this->assignmentTargetBaseQuery($assignmentId)
            ->selectRaw('count(*) as stored_target_total')
            ->selectRaw('sum(case when '.$submittedExpr.' then 1 else 0 end) as submitted_total')
            ->selectRaw('sum(case when '.$inProgressExpr.' then 1 else 0 end) as in_progress_total')
            ->selectRaw('sum(case when '.$notStartedExpr.' then 1 else 0 end) as not_started_total')
            ->selectRaw('sum(case when '.$timeoutExpr.' then 1 else 0 end) as timeout_total')
            ->selectRaw('sum(case when '.$manualPendingExpr.' > 0 then 1 else 0 end) as pending_review_total')
            ->selectRaw('sum('.$manualPendingExpr.') as pending_review_item_total')
            ->selectRaw('avg(case when '.$scoreExpr.' is not null then '.$scoreExpr.' end) as average_score')
            ->first();
    }

    private function assignmentDetailListBaseQuery(AssessmentAssignment $assignment, array $filters = [])
    {
        $submittedExpr = $this->assignmentTargetSubmittedSql();
        $timeoutExpr = $this->assignmentTargetTimeoutSql();
        $inProgressExpr = $this->assignmentTargetInProgressSql();
        $manualPendingExpr = $this->assignmentTargetManualPendingItemsSql();
        $scoreExpr = $this->assignmentTargetOverallScoreSql();

        return $this->assignmentExplorerBaseQuery($assignment->id, $filters)
            ->leftJoin(
                'assessment_assignment_sessions as assignment_session',
                'assignment_session.id',
                '=',
                'target.assessment_assignment_session_id'
            )
            ->leftJoin(
                'assessment_assignments as assignment',
                'assignment.id',
                '=',
                'target.assessment_assignment_id'
            )
            ->selectRaw('target.id as target_id')
            ->selectRaw("coalesce(guru.nama_lengkap, 'Peserta tidak ditemukan') as participant_name")
            ->selectRaw("coalesce(nullif(trim(guru.kabupaten), ''), '-') as kabupaten")
            ->selectRaw("coalesce(nullif(trim(guru.eksternal_jabatan), ''), nullif(trim(guru.jenis_jabatan), ''), '-') as jabatan")
            ->selectRaw("coalesce(nullif(trim(guru.satuan_pendidikan), ''), '-') as school")
            ->selectRaw('coalesce(assignment.session_enabled, 1) as session_enabled')
            ->selectRaw('assignment_session.label_sesi as session_label')
            ->selectRaw('coalesce(target.submitted_at, attempt.submitted_at) as resolved_submitted_at')
            ->selectRaw('coalesce(target.started_at, attempt.started_at) as resolved_started_at')
            ->selectRaw('case when '.$submittedExpr.' then 1 else 0 end as is_submitted')
            ->selectRaw('case when '.$timeoutExpr.' then 1 else 0 end as is_timeout')
            ->selectRaw('case when '.$inProgressExpr.' then 1 else 0 end as is_in_progress')
            ->selectRaw($manualPendingExpr.' as manual_pending_items')
            ->selectRaw($scoreExpr.' as score');
    }

    private function normalizeAssignmentExplorerFilters(array $filters): array
    {
        return [
            'kabupaten' => $this->normalizeAssignmentExplorerFilterValue($filters['kabupaten'] ?? null),
            'jabatan' => $this->normalizeAssignmentExplorerFilterValue($filters['jabatan'] ?? null),
            'satuan_pendidikan' => $this->normalizeAssignmentExplorerFilterValue($filters['satuan_pendidikan'] ?? null),
        ];
    }

    private function normalizeAssignmentExplorerFilterValue(mixed $value): ?string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : null;
    }

    private function buildAssignmentExplorerFilterOptions(AssessmentAssignment $assignment): array
    {
        return Cache::remember(
            'assessment-assignment-monitor-options:'.$assignment->id,
            now()->addMinutes(10),
            function () use ($assignment) {
                return [
                    'kabupaten' => $this->buildAssignmentExplorerOptionRows(
                        $assignment->id,
                        $this->assignmentTargetKabupatenSql(),
                        'kabupaten'
                    ),
                    'jabatan' => $this->buildAssignmentExplorerOptionRows(
                        $assignment->id,
                        $this->assignmentTargetJabatanSql(),
                        'jabatan'
                    ),
                    'satuan_pendidikan' => $this->buildAssignmentExplorerOptionRows(
                        $assignment->id,
                        $this->assignmentTargetSchoolSql(),
                        'satuan_pendidikan'
                    ),
                ];
            }
        );
    }

    private function buildAssignmentExplorerOptionRows(int $assignmentId, string $expression, string $alias): array
    {
        return $this->assignmentTargetGuruBaseQuery($assignmentId)
            ->selectRaw($expression.' as '.$alias)
            ->selectRaw('count(*) as participant_total')
            ->groupByRaw($expression)
            ->orderBy($alias)
            ->get()
            ->map(function (object $row) use ($alias) {
                return [
                    'value' => (string) data_get($row, $alias, ''),
                    'label' => (string) data_get($row, $alias, ''),
                    'participant_total' => (int) ($row->participant_total ?? 0),
                ];
            })
            ->filter(fn (array $item) => $item['value'] !== '')
            ->values()
            ->all();
    }

    private function paginateAssignmentExplorerIndividuals(
        AssessmentAssignment $assignment,
        array $filters,
        int $perPage,
        int $page
    ): LengthAwarePaginator {
        $paginator = $this->assignmentDetailListBaseQuery($assignment, $filters)
            ->orderByRaw('case when target.submitted_at is null and attempt.submitted_at is null then 1 else 0 end')
            ->orderBy('target.id')
            ->paginate($perPage, ['*'], 'monitor_page', $page)
            ->withQueryString();

        $paginator->setCollection(
            $paginator->getCollection()
                ->map(fn (object $row) => $this->serializeAssignmentDetailRow($row))
                ->values()
        );
        $paginator->fragment('monitoring-explorer');

        return $paginator;
    }

    private function buildAssignmentExplorerSummaryPayload(
        AssessmentAssignment $assignment,
        array $filters
    ): array {
        $cacheTtlSeconds = 60;
        $cacheKey = 'assessment-assignment-monitor-summary:'
            .$assignment->id.':'
            .md5(json_encode($filters));

        return Cache::remember(
            $cacheKey,
            now()->addSeconds($cacheTtlSeconds),
            function () use ($assignment, $filters, $cacheTtlSeconds) {
                $stats = $this->fetchAssignmentExplorerStats($assignment->id, $filters);
                $targetTotal = (int) ($stats->filtered_target_total ?? 0);
                $submittedTotal = (int) ($stats->submitted_total ?? 0);
                $inProgressTotal = (int) ($stats->in_progress_total ?? 0);
                $notStartedTotal = (int) ($stats->not_started_total ?? 0);
                $timeoutTotal = (int) ($stats->timeout_total ?? 0);
                $pendingReviewTotal = (int) ($stats->pending_review_total ?? 0);
                $scoredTotal = (int) ($stats->scored_total ?? 0);
                $manualSubmittedTotal = max($submittedTotal - $timeoutTotal, 0);
                $averageScore = isset($stats->average_score) && $stats->average_score !== null
                    ? round((float) $stats->average_score, 2)
                    : null;

                $competencyRows = collect(KompetensiGuru::cases())
                    ->map(function (KompetensiGuru $kompetensi) use ($stats) {
                        $scoreColumn = $kompetensi->value.'_average_score';
                        $averageScore = isset($stats->{$scoreColumn}) && $stats->{$scoreColumn} !== null
                            ? round((float) $stats->{$scoreColumn}, 2)
                            : null;

                        return [
                            'key' => $kompetensi->value,
                            'label' => $kompetensi->label(),
                            'average_score' => $averageScore,
                            'formatted_score' => $averageScore !== null ? number_format($averageScore, 2) : '-',
                            'level_label' => $averageScore !== null
                                ? LevelKompetensi::fromScore($averageScore)?->shortLabel()
                                : null,
                        ];
                    })
                    ->values();

                $levelRows = collect(LevelKompetensi::cases())
                    ->map(function (LevelKompetensi $level) use ($stats) {
                        return [
                            'value' => $level->value,
                            'label' => $level->shortLabel(),
                            'count' => (int) data_get($stats, 'level_'.$level->value, 0),
                        ];
                    })
                    ->values();

                $dominantLevel = $levelRows
                    ->sortByDesc(fn (array $row) => ($row['count'] * 10) + $row['value'])
                    ->first(fn (array $row) => $row['count'] > 0);

                return [
                    'summary' => [
                        'filtered_target_total' => $targetTotal,
                        'submitted_total' => $submittedTotal,
                        'scored_total' => $scoredTotal,
                        'in_progress_total' => $inProgressTotal,
                        'not_started_total' => $notStartedTotal,
                        'timeout_total' => $timeoutTotal,
                        'pending_review_total' => $pendingReviewTotal,
                        'average_score' => $averageScore,
                        'average_score_label' => $averageScore !== null ? number_format($averageScore, 2) : '-',
                        'dominant_level_label' => $dominantLevel['label'] ?? 'Belum ada level',
                        'dominant_level_total' => (int) ($dominantLevel['count'] ?? 0),
                        'completion_rate' => $targetTotal > 0
                            ? round(($submittedTotal / $targetTotal) * 100, 2)
                            : 0.0,
                        'participation_rate' => $targetTotal > 0
                            ? round((($submittedTotal + $inProgressTotal) / $targetTotal) * 100, 2)
                            : 0.0,
                        'competencies' => $competencyRows->all(),
                        'levels' => $levelRows->all(),
                    ],
                    'charts' => [
                        'status' => [
                            'labels' => ['Selesai Manual', 'Sedang Mengerjakan', 'Belum Mulai', 'Selesai Timeout'],
                            'data' => [$manualSubmittedTotal, $inProgressTotal, $notStartedTotal, $timeoutTotal],
                        ],
                        'levels' => [
                            'labels' => $levelRows->pluck('label')->all(),
                            'data' => $levelRows->pluck('count')->all(),
                        ],
                        'radar' => [
                            'labels' => $competencyRows->pluck('label')->all(),
                            'data' => $competencyRows
                                ->map(fn (array $row) => $row['average_score'] ?? 0)
                                ->all(),
                            'max_score' => 5,
                        ],
                        'competencies' => [
                            'labels' => $competencyRows->pluck('label')->all(),
                            'data' => $competencyRows
                                ->map(fn (array $row) => $row['average_score'] ?? 0)
                                ->all(),
                        ],
                    ],
                    'meta' => [
                        'cache_ttl_seconds' => $cacheTtlSeconds,
                        'has_scored_data' => $scoredTotal > 0,
                    ],
                ];
            }
        );
    }

    private function fetchAssignmentExplorerStats(int $assignmentId, array $filters): object
    {
        $submittedExpr = $this->assignmentTargetSubmittedSql();
        $inProgressExpr = $this->assignmentTargetInProgressSql();
        $notStartedExpr = $this->assignmentTargetNotStartedSql();
        $timeoutExpr = $this->assignmentTargetTimeoutSql();
        $manualPendingExpr = $this->assignmentTargetManualPendingItemsSql();
        $scoreExpr = $this->assignmentTargetOverallScoreSql();

        $query = $this->assignmentExplorerBaseQuery($assignmentId, $filters)
            ->selectRaw('count(*) as filtered_target_total')
            ->selectRaw('sum(case when '.$submittedExpr.' then 1 else 0 end) as submitted_total')
            ->selectRaw('sum(case when '.$inProgressExpr.' then 1 else 0 end) as in_progress_total')
            ->selectRaw('sum(case when '.$notStartedExpr.' then 1 else 0 end) as not_started_total')
            ->selectRaw('sum(case when '.$timeoutExpr.' then 1 else 0 end) as timeout_total')
            ->selectRaw('sum(case when '.$manualPendingExpr.' > 0 then 1 else 0 end) as pending_review_total')
            ->selectRaw('sum(case when '.$scoreExpr.' is not null then 1 else 0 end) as scored_total')
            ->selectRaw('avg(case when '.$scoreExpr.' is not null then '.$scoreExpr.' end) as average_score')
            ->selectRaw('sum(case when '.$scoreExpr.' >= 1.00 and '.$scoreExpr.' < 1.80 then 1 else 0 end) as level_1')
            ->selectRaw('sum(case when '.$scoreExpr.' >= 1.80 and '.$scoreExpr.' < 2.60 then 1 else 0 end) as level_2')
            ->selectRaw('sum(case when '.$scoreExpr.' >= 2.60 and '.$scoreExpr.' < 3.40 then 1 else 0 end) as level_3')
            ->selectRaw('sum(case when '.$scoreExpr.' >= 3.40 and '.$scoreExpr.' < 4.20 then 1 else 0 end) as level_4')
            ->selectRaw('sum(case when '.$scoreExpr.' >= 4.20 and '.$scoreExpr.' <= 5.00 then 1 else 0 end) as level_5');

        foreach (KompetensiGuru::cases() as $kompetensi) {
            $competencyScoreExpr = $this->assignmentTargetCompetencyScoreSql($kompetensi);
            $query->selectRaw(
                'avg(case when '.$competencyScoreExpr.' is not null then '.$competencyScoreExpr.' end) as '
                .$kompetensi->value.'_average_score'
            );
        }

        return $query->first() ?? (object) [];
    }

    private function assignmentExplorerBaseQuery(int $assignmentId, array $filters = [])
    {
        $query = $this->assignmentTargetBaseQuery($assignmentId)
            ->leftJoin('gurus as guru', 'guru.id', '=', 'target.guru_id');

        $this->applyAssignmentExplorerFilters($query, $filters);

        return $query;
    }

    private function assignmentTargetGuruBaseQuery(int $assignmentId)
    {
        return DB::table('assessment_assignment_targets as target')
            ->join('gurus as guru', 'guru.id', '=', 'target.guru_id')
            ->where('target.assessment_assignment_id', $assignmentId);
    }

    private function applyAssignmentExplorerFilters($query, array $filters): void
    {
        $normalizedFilters = $this->normalizeAssignmentExplorerFilters($filters);

        if ($normalizedFilters['kabupaten']) {
            $query->whereRaw(
                $this->assignmentTargetKabupatenSql().' = ?',
                [$normalizedFilters['kabupaten']]
            );
        }

        if ($normalizedFilters['jabatan']) {
            $query->whereRaw(
                $this->assignmentTargetJabatanSql().' = ?',
                [$normalizedFilters['jabatan']]
            );
        }

        if ($normalizedFilters['satuan_pendidikan']) {
            $query->whereRaw(
                $this->assignmentTargetSchoolSql().' = ?',
                [$normalizedFilters['satuan_pendidikan']]
            );
        }
    }

    private function assignmentTargetKabupatenSql(): string
    {
        return "coalesce(nullif(trim(guru.kabupaten), ''), 'Kabupaten belum diisi')";
    }

    private function assignmentTargetJabatanSql(): string
    {
        return "coalesce(nullif(trim(guru.eksternal_jabatan), ''), nullif(trim(guru.jenis_jabatan), ''), 'Jabatan belum diisi')";
    }

    private function assignmentTargetSchoolSql(): string
    {
        return "coalesce(nullif(trim(guru.satuan_pendidikan), ''), 'Satuan pendidikan belum diisi')";
    }

    private function assignmentTargetCompetencyScoreSql(KompetensiGuru $kompetensi): string
    {
        $competencyIndex = collect(KompetensiGuru::cases())
            ->search(fn (KompetensiGuru $case) => $case->value === $kompetensi->value);

        return "cast(nullif(json_unquote(json_extract(attempt.scoring_summary, '$.radar_chart.datasets["
            .$competencyIndex
            ."].score')), '') as decimal(8,2))";
    }

    private function assignmentTargetBaseQuery(int $assignmentId)
    {
        return DB::table('assessment_assignment_targets as target')
            ->leftJoin('assessment_attempts as attempt', 'attempt.assessment_assignment_target_id', '=', 'target.id')
            ->where('target.assessment_assignment_id', $assignmentId);
    }

    private function assignmentTargetSubmittedSql(): string
    {
        return "(attempt.status = 'submitted' or target.status = 'selesai' or target.submitted_at is not null or attempt.submitted_at is not null)";
    }

    private function assignmentTargetActivitySql(): string
    {
        return "(target.status = 'dikerjakan' or attempt.status = 'in_progress' or target.started_at is not null or attempt.started_at is not null)";
    }

    private function assignmentTargetInProgressSql(): string
    {
        return '(not '.$this->assignmentTargetSubmittedSql().' and '.$this->assignmentTargetActivitySql().')';
    }

    private function assignmentTargetNotStartedSql(): string
    {
        return '(not '.$this->assignmentTargetSubmittedSql().' and not '.$this->assignmentTargetActivitySql().')';
    }

    private function assignmentTargetTimeoutSql(): string
    {
        return "(coalesce(attempt.completion_mode, target.completion_mode) = 'timeout' or target.timed_out_at is not null or attempt.timed_out_at is not null)";
    }

    private function assignmentTargetManualPendingItemsSql(): string
    {
        return "coalesce(cast(nullif(json_unquote(json_extract(attempt.scoring_summary, '$.manual_review.pending_items')), '') as unsigned), 0)";
    }

    private function assignmentTargetOverallScoreSql(): string
    {
        return "cast(nullif(json_unquote(json_extract(attempt.scoring_summary, '$.overall.score')), '') as decimal(8,2))";
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeAssignmentDetailRow(object $row): array
    {
        $score = isset($row->score) && $row->score !== null
            ? round((float) $row->score, 2)
            : null;

        return [
            'target_id' => (int) $row->target_id,
            'assignment_id' => isset($row->assignment_id) ? (int) $row->assignment_id : null,
            'assignment_title' => isset($row->assignment_title) ? (string) ($row->assignment_title ?: 'Penugasan') : null,
            'assignment_code' => isset($row->assignment_code) ? (string) ($row->assignment_code ?: '-') : null,
            'name' => (string) ($row->participant_name ?: 'Peserta tidak ditemukan'),
            'kabupaten' => (string) ($row->kabupaten ?: '-'),
            'jabatan' => (string) ($row->jabatan ?: '-'),
            'school' => (string) ($row->school ?: '-'),
            'status_label' => $this->resolveAssignmentDetailStatusLabel($row),
            'session_label' => $this->resolveSessionLabel(
                $this->normalizeSessionEnabled($row->session_enabled ?? true),
                isset($row->session_label) ? (string) $row->session_label : null
            ),
            'submitted_at' => $this->formatDateTimeLabel($row->resolved_submitted_at ?? null),
            'started_at' => $this->formatDateTimeLabel($row->resolved_started_at ?? null),
            'manual_pending_items' => (int) ($row->manual_pending_items ?? 0),
            'score' => $score,
            'score_label' => $score !== null ? number_format($score, 2) : null,
            'score_level' => $score !== null ? LevelKompetensi::fromScore($score)?->shortLabel() : null,
            'review_url' => ((int) ($row->is_submitted ?? 0)) === 1
                ? route('assessment.portal.result', (int) $row->target_id)
                : null,
        ];
    }

    private function resolveAssignmentDetailStatusLabel(object $row): string
    {
        if ((int) ($row->is_timeout ?? 0) === 1) {
            return 'Selesai Timeout';
        }

        if ((int) ($row->is_submitted ?? 0) === 1) {
            return 'Sudah Mengisi';
        }

        if ((int) ($row->is_in_progress ?? 0) === 1) {
            return 'Sedang Mengerjakan';
        }

        return 'Belum Mengisi';
    }

    private function formatDateTimeLabel(Carbon|string|null $dateTime): ?string
    {
        if (! $dateTime) {
            return null;
        }

        if (! $dateTime instanceof Carbon) {
            $dateTime = Carbon::parse($dateTime);
        }

        return $dateTime->format('d M Y H:i');
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
            'session_enabled' => $assignment->usesSessionScheduling(),
            'session_mode_label' => $assignment->session_mode_label,
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
            'session_label' => $this->resolveSessionLabel(
                optional($target->assignment)->usesSessionScheduling() ?? true,
                optional($target->session)->label_sesi
            ),
            'submitted_at' => $submittedAt?->format('d M Y H:i'),
            'started_at' => ($target->started_at ?: $target->attempt?->started_at)?->format('d M Y H:i'),
            'manual_pending_items' => $this->manualPendingItems($target),
            'score' => $score,
            'score_label' => $score !== null ? number_format($score, 2) : null,
            'score_level' => $score !== null ? LevelKompetensi::fromScore($score)?->shortLabel() : null,
            'review_url' => $this->isSubmitted($target) ? route('assessment.portal.result', $target->id) : null,
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
        return (int) data_get($this->scoringSummary($target), 'manual_review.pending_items', 0);
    }

    private function overallScore(AssessmentAssignmentTarget $target): ?float
    {
        $score = data_get($this->scoringSummary($target), 'overall.score');

        return is_numeric($score)
            ? round((float) $score, 2)
            : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function scoringSummary(AssessmentAssignmentTarget $target): array
    {
        $attempt = $target->attempt;

        if (! $attempt) {
            return [];
        }

        $cacheKey = $attempt->id ?: spl_object_id($attempt);

        if (array_key_exists($cacheKey, $this->summaryCache)) {
            return $this->summaryCache[$cacheKey];
        }

        return $this->summaryCache[$cacheKey] = $this->attemptService->buildScoringSummary($attempt);
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

    private function formatPeriodLabelFromRange(
        Carbon|string|null $startDate,
        Carbon|string|null $endDate
    ): string {
        $startLabel = $this->formatDateLabel($startDate);
        $endLabel = $this->formatDateLabel($endDate);

        return $startLabel === '-' && $endLabel === '-'
            ? 'Periode belum diatur'
            : $startLabel.' s/d '.$endLabel;
    }

    private function formatSessionScheduleLabel(
        Carbon|string|null $startDateTime,
        Carbon|string|null $endDateTime
    ): string {
        if (! $startDateTime) {
            return 'Jadwal belum diatur';
        }

        $startDateTime = $startDateTime instanceof Carbon
            ? $startDateTime
            : Carbon::parse($startDateTime);

        if (! $endDateTime) {
            return $startDateTime->format('d M Y H:i').' WITA';
        }

        $endDateTime = $endDateTime instanceof Carbon
            ? $endDateTime
            : Carbon::parse($endDateTime);

        if ($startDateTime->isSameDay($endDateTime)) {
            return $startDateTime->format('d M Y').', '
                .$startDateTime->format('H:i')
                .' - '
                .$endDateTime->format('H:i')
                .' WITA';
        }

        return $startDateTime->format('d M Y H:i')
            .' - '
            .$endDateTime->format('d M Y H:i')
            .' WITA';
    }

    private function formatPeriodLabel(AssessmentAssignment $assignment): string
    {
        return $this->formatPeriodLabelFromRange(
            $assignment->tanggal_mulai,
            $assignment->tanggal_selesai
        );
    }

    private function normalizeSessionEnabled(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        if (is_string($value)) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        return true;
    }

    private function resolveSessionModeLabel(bool $sessionEnabled): string
    {
        return $sessionEnabled ? 'Terjadwal per sesi' : 'Tanpa sesi';
    }

    private function resolveSessionLabel(bool $sessionEnabled, ?string $sessionLabel): string
    {
        $sessionLabel = trim((string) ($sessionLabel ?? ''));

        if ($sessionLabel !== '') {
            return $sessionLabel;
        }

        return $sessionEnabled ? 'Belum dipetakan' : 'Tanpa sesi';
    }
}
