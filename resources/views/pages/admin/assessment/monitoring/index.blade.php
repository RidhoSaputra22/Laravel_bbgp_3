@extends('layouts.app', ['title' => 'Monitoring Assessment'])

@push('styles')
    <style>
        .monitor-kpi-card .card-body {
            padding: 1rem;
        }

        .monitor-kpi-card .monitor-kpi-label {
            font-size: 0.82rem;
            color: #6c757d;
            margin-bottom: 0.35rem;
        }

        .monitor-kpi-card .monitor-kpi-value {
            font-size: 1.65rem;
            font-weight: 700;
            line-height: 1.1;
        }

        .monitor-filter-card {
            border: 1px solid #d8e4ff;
            box-shadow: 0 10px 30px rgba(83, 109, 254, 0.08);
        }

        .monitor-filter-card .card-header {
            align-items: flex-start;
            border-bottom: 1px solid #edf1ff;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .monitor-filter-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .monitor-filter-grid .form-group {
            margin-bottom: 0;
        }

        .monitor-filter-actions {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            justify-content: space-between;
        }

        .monitor-active-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .monitor-summary-card {
            border: 1px solid #edf1ff;
        }

        .monitor-summary-card .card-header {
            border-bottom: 1px solid #edf1ff;
        }

        .monitor-summary-stat {
            border: 1px solid #edf1ff;
            border-radius: 0.3rem;
            height: 100%;
            padding: 1rem;
        }

        .monitor-summary-stat__label {
            color: #6c757d;
            font-size: 0.82rem;
            margin-bottom: 0.35rem;
        }

        .monitor-summary-stat__value {
            color: #34395e;
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1.1;
        }

        .monitor-competency-card {
            border: 1px solid #edf1ff;
            border-radius: 0.3rem;
            height: 100%;
            padding: 1rem;
        }

        .monitor-competency-card__label {
            color: #34395e;
            font-weight: 600;
            margin-bottom: 0.35rem;
        }

        .monitor-competency-card__score {
            color: #6777ef;
            font-size: 1.35rem;
            font-weight: 700;
            line-height: 1.1;
        }

        .monitor-summary-chart {
            min-height: 290px;
            position: relative;
        }

        @media (max-width: 991.98px) {
            .monitor-filter-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $summary = $monitoringPanel['summary'] ?? [];
        $charts = $monitoringPanel['charts'] ?? [];
        $attentionAssignments = collect($monitoringPanel['attention_assignments'] ?? []);
        $assignmentPaginator = $monitoringPanel['assignment_paginator'] ?? null;
        $monitoringExplorer = $monitoringExplorer ?? [];
        $explorerMode = $monitoringExplorer['mode'] ?? 'individual';
        $explorerFilters = $monitoringExplorer['filters'] ?? [];
        $explorerSelectedFilters = $explorerFilters['selected'] ?? [];
        $explorerFilterOptions = $explorerFilters['options'] ?? [];
        $explorerRows = $monitoringExplorer['individual_rows'] ?? [];
        $explorerPaginator = $monitoringExplorer['individual_paginator'] ?? null;
        $explorerSummary = $monitoringExplorer['summary'] ?? [];
        $explorerTrainingSummary = $explorerSummary['training'] ?? [];
        $explorerCharts = $monitoringExplorer['charts'] ?? [];
        $explorerMeta = $monitoringExplorer['meta'] ?? [];
        $explorerActiveFilterCount = collect($explorerSelectedFilters)
            ->filter(fn($value) => filled($value))
            ->count();
    @endphp

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Monitoring Assessment</h1>
                <div class="section-header-breadcrumb">
                    <a href="{{ route('assessment.assignment.index') }}" class="btn btn-light mr-2">
                        <i class="fas fa-tasks"></i> Penugasan
                    </a>
                    <span class="badge badge-light">Refresh browser untuk update terbaru</span>
                </div>
            </div>

            <div class="section-body">
                <div class="alert alert-light border">
                    Monitoring ini dibaca langsung dari database saat halaman dibuka. Tidak ada polling otomatis:
                    admin cukup refresh browser untuk melihat progres penugasan, peserta yang sudah mengisi,
                    peserta yang belum, status auto scoring, dan distribusi skor terbaru.
                </div>

                <div class="row">
                    <div class="col-xl col-lg-3 col-md-4 col-6">
                        <div class="card monitor-kpi-card">
                            <div class="card-body">
                                <div class="monitor-kpi-label">Total Penugasan</div>
                                <div class="monitor-kpi-value text-primary">
                                    {{ $summary['assignment_total'] ?? 0 }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl col-lg-3 col-md-4 col-6">
                        <div class="card monitor-kpi-card">
                            <div class="card-body">
                                <div class="monitor-kpi-label">Penugasan Berjalan</div>
                                <div class="monitor-kpi-value text-info">
                                    {{ $summary['assignment_running_total'] ?? 0 }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl col-lg-3 col-md-4 col-6">
                        <div class="card monitor-kpi-card">
                            <div class="card-body">
                                <div class="monitor-kpi-label">Jatuh Tempo</div>
                                <div class="monitor-kpi-value text-danger">
                                    {{ $summary['assignment_overdue_total'] ?? 0 }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl col-lg-3 col-md-4 col-6">
                        <div class="card monitor-kpi-card">
                            <div class="card-body">
                                <div class="monitor-kpi-label">Retry Distribusi</div>
                                <div class="monitor-kpi-value text-warning">
                                    {{ $summary['assignment_retry_total'] ?? 0 }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl col-lg-3 col-md-4 col-6">
                        <div class="card monitor-kpi-card">
                            <div class="card-body">
                                <div class="monitor-kpi-label">Total Target</div>
                                <div class="monitor-kpi-value text-dark">
                                    {{ $summary['expected_target_total'] ?? 0 }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl col-lg-3 col-md-4 col-6">
                        <div class="card monitor-kpi-card">
                            <div class="card-body">
                                <div class="monitor-kpi-label">Sudah Mengisi</div>
                                <div class="monitor-kpi-value text-success">
                                    {{ $summary['submitted_total'] ?? 0 }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl col-lg-3 col-md-4 col-6">
                        <div class="card monitor-kpi-card">
                            <div class="card-body">
                                <div class="monitor-kpi-label">Sedang Mengerjakan</div>
                                <div class="monitor-kpi-value text-warning">
                                    {{ $summary['in_progress_total'] ?? 0 }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl col-lg-3 col-md-4 col-6">
                        <div class="card monitor-kpi-card">
                            <div class="card-body">
                                <div class="monitor-kpi-label">Belum Mulai</div>
                                <div class="monitor-kpi-value text-secondary">
                                    {{ $summary['not_started_total'] ?? 0 }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl col-lg-3 col-md-4 col-6">
                        <div class="card monitor-kpi-card">
                            <div class="card-body">
                                <div class="monitor-kpi-label">Belum Tersimpan</div>
                                <div class="monitor-kpi-value text-danger">
                                    {{ $summary['distribution_missing_total'] ?? 0 }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl col-lg-3 col-md-4 col-6">
                        <div class="card monitor-kpi-card">
                            <div class="card-body">
                                <div class="monitor-kpi-label">Review Pending</div>
                                <div class="monitor-kpi-value text-primary">
                                    {{ $summary['pending_review_participant_total'] ?? 0 }}
                                </div>
                                <small class="text-muted">
                                    {{ $summary['pending_review_item_total'] ?? 0 }} item
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-lg-4">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span>Tingkat penyelesaian peserta</span>
                                <strong>{{ number_format((float) ($summary['completion_rate'] ?? 0), 2) }}%</strong>
                            </div>
                            <div class="progress" data-height="12">
                                <div class="progress-bar bg-success" role="progressbar"
                                    style="width: {{ min((float) ($summary['completion_rate'] ?? 0), 100) }}%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span>Tingkat partisipasi peserta</span>
                                <strong>{{ number_format((float) ($summary['participation_rate'] ?? 0), 2) }}%</strong>
                            </div>
                            <div class="progress" data-height="12">
                                <div class="progress-bar bg-warning" role="progressbar"
                                    style="width: {{ min((float) ($summary['participation_rate'] ?? 0), 100) }}%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span>Rata-rata skor peserta</span>
                                <strong>
                                    {{ isset($summary['average_score']) && $summary['average_score'] !== null ? number_format((float) $summary['average_score'], 2) : '-' }}
                                </strong>
                            </div>
                            <div class="progress" data-height="12">
                                <div class="progress-bar bg-info" role="progressbar"
                                    style="width: {{ isset($summary['average_score']) && $summary['average_score'] !== null ? min(((float) $summary['average_score'] / 5) * 100, 100) : 0 }}%">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card monitor-filter-card mb-4" id="monitoring-explorer">
                    <div class="card-header">
                        <div>
                            <h4 class="mb-1">Filter Monitoring Global</h4>
                            <div class="text-muted small">
                                Filter ini membaca peserta yang sudah tersimpan pada penugasan, lalu menampilkan mode
                                individu atau ringkasan agregat lintas penugasan.
                            </div>
                        </div>
                        <div class="card-header-action">
                            <span class="badge badge-light">
                                {{ $explorerActiveFilterCount }} filter aktif
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('assessment.monitoring.index') }}#monitoring-explorer" method="GET">
                            <input type="hidden" name="assignment_per_page"
                                value="{{ request('assignment_per_page', 10) }}">
                            <input type="hidden" name="monitor_per_page"
                                value="{{ $explorerPaginator?->perPage() ?? request('monitor_per_page', 25) }}">

                            <div class="monitor-filter-grid mb-3">
                                <div class="form-group">
                                    <label for="monitor-kabupaten">Kabupaten</label>
                                    <select class="form-control" id="monitor-kabupaten" name="monitor_kabupaten">
                                        <option value="">Semua kabupaten</option>
                                        @foreach ($explorerFilterOptions['kabupaten'] ?? [] as $option)
                                            <option value="{{ $option['value'] }}"
                                                {{ ($explorerSelectedFilters['kabupaten'] ?? null) === $option['value'] ? 'selected' : '' }}>
                                                {{ $option['label'] }} ({{ $option['participant_total'] }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="monitor-jabatan">Jabatan</label>
                                    <select class="form-control" id="monitor-jabatan" name="monitor_jabatan">
                                        <option value="">Semua jabatan</option>
                                        @foreach ($explorerFilterOptions['jabatan'] ?? [] as $option)
                                            <option value="{{ $option['value'] }}"
                                                {{ ($explorerSelectedFilters['jabatan'] ?? null) === $option['value'] ? 'selected' : '' }}>
                                                {{ $option['label'] }} ({{ $option['participant_total'] }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="monitor-satuan-pendidikan">Satuan Pendidikan</label>
                                    <select class="form-control" id="monitor-satuan-pendidikan"
                                        name="monitor_satuan_pendidikan">
                                        <option value="">Semua satuan pendidikan</option>
                                        @foreach ($explorerFilterOptions['satuan_pendidikan'] ?? [] as $option)
                                            <option value="{{ $option['value'] }}"
                                                {{ ($explorerSelectedFilters['satuan_pendidikan'] ?? null) === $option['value'] ? 'selected' : '' }}>
                                                {{ $option['label'] }} ({{ $option['participant_total'] }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="monitor-filter-actions">
                                <div class="monitor-active-filters">
                                    @foreach ([
                                        'Kabupaten: ' . ($explorerSelectedFilters['kabupaten'] ?? ''),
                                        'Jabatan: ' . ($explorerSelectedFilters['jabatan'] ?? ''),
                                        'Satuan Pendidikan: ' . ($explorerSelectedFilters['satuan_pendidikan'] ?? ''),
                                    ] as $filterLabel)
                                        @if (! str_ends_with($filterLabel, ': '))
                                            <span class="badge badge-primary">{{ $filterLabel }}</span>
                                        @endif
                                    @endforeach
                                    @if ($explorerActiveFilterCount === 0)
                                        <span class="text-muted small">Tidak ada filter aktif. Semua peserta tersimpan akan dibaca.</span>
                                    @endif
                                </div>
                                <div class="d-flex flex-wrap" style="gap: 0.5rem;">
                                    <button type="submit" name="monitor_view" value="individual"
                                        class="btn {{ $explorerMode === 'individual' ? 'btn-primary' : 'btn-outline-primary' }}">
                                        <i class="fas fa-users mr-1"></i> Lihat Individu
                                    </button>
                                    <button type="submit" name="monitor_view" value="summary"
                                        class="btn {{ $explorerMode === 'summary' ? 'btn-success' : 'btn-outline-success' }}">
                                        <i class="fas fa-chart-pie mr-1"></i> Lihat Semua
                                    </button>
                                    <a href="{{ route('assessment.monitoring.index') }}#monitoring-explorer"
                                        class="btn btn-light">
                                        <i class="fas fa-sync-alt mr-1"></i> Reset Filter
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                @if ($explorerMode === 'individual')
                    <div class="card monitor-summary-card mb-4">
                        <div class="card-header">
                            <h4>Hasil Monitoring Per Individu</h4>
                            <div class="card-header-action">
                                <span class="badge badge-light">
                                    {{ $explorerPaginator?->total() ?? count($explorerRows) }} peserta
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            @if ($explorerPaginator && $explorerPaginator->total() > 0)
                                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                                    <div class="text-muted small">
                                        Menampilkan {{ $explorerPaginator->firstItem() ?? 0 }} -
                                        {{ $explorerPaginator->lastItem() ?? 0 }} dari
                                        {{ $explorerPaginator->total() }} peserta.
                                    </div>
                                    <div class="text-muted small">
                                        Mode ini dipaginasi agar query peserta global tetap ringan.
                                    </div>
                                </div>
                            @endif

                            @if (empty($explorerRows))
                                <div class="alert alert-light border mb-0">
                                    Tidak ada peserta yang cocok dengan filter monitoring global saat ini.
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-center" style="width: 70px;">No</th>
                                                <th>Nama</th>
                                                <th style="width: 150px;">Skor Umum</th>
                                                <th style="width: 160px;">Level Umum</th>
                                                <th style="width: 170px;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($explorerRows as $participant)
                                                <tr>
                                                    <td class="text-center">
                                                        {{ (($explorerPaginator?->firstItem() ?? 1) - 1) + $loop->iteration }}
                                                    </td>
                                                    <td>
                                                        <div class="font-weight-bold">{{ $participant['name'] }}</div>
                                                        <small class="text-muted d-block">
                                                            {{ $participant['assignment_title'] ?: '-' }}
                                                            @if ($participant['assignment_code'])
                                                                • {{ $participant['assignment_code'] }}
                                                            @endif
                                                        </small>
                                                        <small class="text-muted d-block">
                                                            {{ $participant['jabatan'] ?: '-' }} •
                                                            {{ $participant['kabupaten'] ?: '-' }}
                                                        </small>
                                                        <small class="text-muted d-block">
                                                            {{ $participant['school'] ?: '-' }} •
                                                            {{ $participant['session_label'] ?: '-' }}
                                                        </small>
                                                        <small class="text-muted d-block">
                                                            Status: {{ $participant['status_label'] }}
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <div class="font-weight-bold text-success">
                                                            {{ $participant['score_label'] ?: '-' }}
                                                        </div>
                                                        <small class="text-muted">
                                                            Submit: {{ $participant['submitted_at'] ?: '-' }}
                                                        </small>
                                                    </td>
                                                    <td>
                                                        @if ($participant['score_level'])
                                                            <span class="badge badge-info">
                                                                {{ $participant['score_level'] }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($participant['review_url'])
                                                            <a href="{{ $participant['review_url'] }}"
                                                                class="btn btn-sm btn-primary">
                                                                <i class="fas fa-clipboard-check mr-1"></i> Detail
                                                            </a>
                                                        @else
                                                            <span class="text-muted small">Belum ada hasil</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                @if ($explorerPaginator)
                                    <div class="mt-3">
                                        {{ $explorerPaginator->links() }}
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                @else
                    <div class="card monitor-summary-card mb-4">
                        <div class="card-header">
                            <div>
                                <h4 class="mb-1">Ringkasan Visual Semua Peserta Terfilter</h4>
                                <div class="text-muted small">
                                    Nilai di bawah ini adalah hasil agregasi keseluruhan peserta terfilter lintas
                                    penugasan.
                                </div>
                            </div>
                            <div class="card-header-action">
                                <span class="badge badge-light">
                                    Cache {{ (int) ($explorerMeta['cache_ttl_seconds'] ?? 60) }} detik
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-light border">
                                Mode ringkasan disimpan singkat di cache agar agregasi skor, level, dan kompetensi
                                tidak menghitung ulang seluruh peserta setiap refresh.
                            </div>

                            <div class="row">
                                <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                                    <div class="monitor-summary-stat">
                                        <div class="monitor-summary-stat__label">Peserta Terfilter</div>
                                        <div class="monitor-summary-stat__value">
                                            {{ $explorerSummary['filtered_target_total'] ?? 0 }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                                    <div class="monitor-summary-stat">
                                        <div class="monitor-summary-stat__label">Penugasan Terdampak</div>
                                        <div class="monitor-summary-stat__value text-primary">
                                            {{ $explorerSummary['assignment_total'] ?? 0 }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                                    <div class="monitor-summary-stat">
                                        <div class="monitor-summary-stat__label">Sudah Mengisi</div>
                                        <div class="monitor-summary-stat__value text-success">
                                            {{ $explorerSummary['submitted_total'] ?? 0 }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                                    <div class="monitor-summary-stat">
                                        <div class="monitor-summary-stat__label">Skor Umum</div>
                                        <div class="monitor-summary-stat__value">
                                            {{ $explorerSummary['average_score_label'] ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                                    <div class="monitor-summary-stat">
                                        <div class="monitor-summary-stat__label">Level Umum Dominan</div>
                                        <div class="monitor-summary-stat__value">
                                            {{ $explorerSummary['dominant_level_label'] ?? '-' }}
                                        </div>
                                        <small class="text-muted">
                                            {{ $explorerSummary['dominant_level_total'] ?? 0 }} peserta
                                        </small>
                                    </div>
                                </div>
                                <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                                    <div class="monitor-summary-stat">
                                        <div class="monitor-summary-stat__label">Partisipasi</div>
                                        <div class="monitor-summary-stat__value text-warning">
                                            {{ number_format((float) ($explorerSummary['participation_rate'] ?? 0), 2) }}%
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                @foreach ($explorerSummary['competencies'] ?? [] as $competency)
                                    <div class="col-lg-3 col-md-6 mb-3">
                                        <div class="monitor-competency-card">
                                            <div class="monitor-competency-card__label">
                                                {{ $competency['label'] }}
                                            </div>
                                            <div class="monitor-competency-card__score">
                                                {{ $competency['formatted_score'] }}
                                            </div>
                                            <small class="text-muted">
                                                {{ $competency['level_label'] ?: 'Belum ada level' }}
                                            </small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="row">
                                <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                                    <div class="monitor-summary-stat">
                                        <div class="monitor-summary-stat__label">Peserta Dengan Data Pelatihan</div>
                                        <div class="monitor-summary-stat__value text-info">
                                            {{ $explorerTrainingSummary['participant_with_training_total'] ?? 0 }}
                                        </div>
                                        <small class="text-muted">
                                            dari {{ $explorerTrainingSummary['participant_total'] ?? ($explorerSummary['submitted_total'] ?? 0) }} peserta submit
                                        </small>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                                    <div class="monitor-summary-stat">
                                        <div class="monitor-summary-stat__label">Total Entri Pelatihan</div>
                                        <div class="monitor-summary-stat__value">
                                            {{ $explorerTrainingSummary['total_entries'] ?? 0 }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                                    <div class="monitor-summary-stat">
                                        <div class="monitor-summary-stat__label">Total JP Pelatihan</div>
                                        <div class="monitor-summary-stat__value text-primary">
                                            {{ $explorerTrainingSummary['formatted_total_jp'] ?? '0' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                                    <div class="monitor-summary-stat">
                                        <div class="monitor-summary-stat__label">Rata-rata Pelatihan / Peserta</div>
                                        <div class="monitor-summary-stat__value text-warning">
                                            {{ $explorerTrainingSummary['formatted_average_entries_per_participant'] ?? number_format(0, 2) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if (! ($explorerMeta['has_scored_data'] ?? false))
                                <div class="alert alert-warning">
                                    Belum ada skor peserta yang bisa diagregasi pada filter ini. Grafik status tetap
                                    ditampilkan untuk memantau progres pengisian.
                                </div>
                            @endif

                            @if (! ($explorerMeta['has_training_data'] ?? false))
                                <div class="alert alert-light border">
                                    Belum ada data pelatihan yang bisa diagregasi pada filter ini. Grafik pelatihan
                                    akan aktif otomatis setelah peserta mengirim jawaban portfolio pengalaman
                                    pelatihan.
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="card border">
                                        <div class="card-header">
                                            <h4>Jaring Laba-Laba Kompetensi</h4>
                                        </div>
                                        <div class="card-body monitor-summary-chart">
                                            <canvas id="globalExplorerRadarChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="card border">
                                        <div class="card-header">
                                            <h4>Status Peserta Terfilter</h4>
                                        </div>
                                        <div class="card-body monitor-summary-chart">
                                            <canvas id="globalExplorerStatusChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="card border">
                                        <div class="card-header">
                                            <h4>Distribusi Level Umum</h4>
                                        </div>
                                        <div class="card-body monitor-summary-chart">
                                            <canvas id="globalExplorerLevelChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="card border">
                                        <div class="card-header">
                                            <h4>Rata-rata Kompetensi</h4>
                                        </div>
                                        <div class="card-body monitor-summary-chart">
                                            <canvas id="globalExplorerCompetencyChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="card border">
                                        <div class="card-header">
                                            <h4>Ringkasan Pelatihan dan Total JP</h4>
                                        </div>
                                        <div class="card-body monitor-summary-chart">
                                            <canvas id="globalExplorerTrainingChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="alert alert-light border">
                    Dashboard di bawah ini tetap menampilkan snapshot monitoring global tanpa filter.
                </div>

                <div class="row">
                    <div class="col-lg-5">
                        <div class="card">
                            <div class="card-header">
                                <h4>Status Peserta Assessment</h4>
                            </div>
                            <div class="card-body">
                                <canvas id="assignmentParticipantStatusChart" height="260"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="card">
                            <div class="card-header">
                                <h4>Progress Isi Per Penugasan</h4>
                            </div>
                            <div class="card-body">
                                <canvas id="assignmentProgressChart" height="260"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-7">
                        <div class="card">
                            <div class="card-header">
                                <h4>Sebaran Progres Per Kabupaten</h4>
                            </div>
                            <div class="card-body">
                                <canvas id="assignmentKabupatenCompletionChart" height="260"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="card">
                            <div class="card-header">
                                <h4>Okupansi & Penyelesaian Sesi</h4>
                            </div>
                            <div class="card-body">
                                <canvas id="assignmentSessionUtilizationChart" height="260"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>Penugasan Yang Perlu Perhatian</h4>
                    </div>
                    <div class="card-body">
                        @if ($attentionAssignments->isEmpty())
                            <div class="alert alert-light mb-0">
                                Belum ada data monitoring penugasan yang perlu ditandai.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Penugasan</th>
                                            <th>Fase</th>
                                            <th>Distribusi</th>
                                            <th>Sudah Isi</th>
                                            <th>Belum Isi</th>
                                            <th>Timeout</th>
                                            <th>Review</th>
                                            <th>Rata-rata</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($attentionAssignments as $item)
                                            <tr>
                                                <td>
                                                    <div class="font-weight-bold">{{ $item['title'] }}</div>
                                                    <small class="text-muted">{{ $item['code'] }} • {{ $item['period_label'] }}</small>
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge badge-{{ $item['phase'] === 'jatuh_tempo' ? 'danger' : ($item['phase'] === 'tuntas' ? 'success' : ($item['phase'] === 'terjadwal' ? 'secondary' : 'info')) }}">
                                                        {{ $item['phase_label'] }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div>{{ $item['stored_target_total'] }}/{{ $item['target_total'] }} tersimpan</div>
                                                    @if ($item['distribution_missing_total'] > 0)
                                                        <small class="text-danger">
                                                            {{ $item['distribution_missing_total'] }} target belum tersimpan
                                                        </small>
                                                    @endif
                                                </td>
                                                <td>{{ $item['submitted_total'] }} peserta</td>
                                                <td>{{ $item['pending_total'] }} peserta</td>
                                                <td>{{ $item['timeout_total'] }} peserta</td>
                                                <td>{{ $item['pending_review_total'] }} peserta</td>
                                                <td>
                                                    {{ $item['average_score'] !== null ? number_format((float) $item['average_score'], 2) : '-' }}
                                                </td>
                                                <td>
                                                    <a href="{{ route('assessment.assignment.show', $item['id']) }}"
                                                        class="btn btn-info btn-sm">
                                                        <i class="fas fa-eye mr-1"></i> Pantau
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                        <h4 class="mb-0">Daftar Monitoring Penugasan</h4>
                        <form method="GET" class="form-inline">
                            <input type="hidden" name="monitor_view" value="{{ $explorerMode }}">
                            <input type="hidden" name="monitor_per_page"
                                value="{{ $explorerPaginator?->perPage() ?? request('monitor_per_page', 25) }}">
                            <input type="hidden" name="monitor_kabupaten"
                                value="{{ $explorerSelectedFilters['kabupaten'] ?? '' }}">
                            <input type="hidden" name="monitor_jabatan"
                                value="{{ $explorerSelectedFilters['jabatan'] ?? '' }}">
                            <input type="hidden" name="monitor_satuan_pendidikan"
                                value="{{ $explorerSelectedFilters['satuan_pendidikan'] ?? '' }}">
                            <label for="assignment_per_page" class="mr-2 mb-0 small text-muted">Tampilkan</label>
                            <select name="assignment_per_page" id="assignment_per_page"
                                class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                                @foreach ([5, 10, 15, 25, 50] as $perPageOption)
                                    <option value="{{ $perPageOption }}"
                                        @selected((int) request('assignment_per_page', 10) === $perPageOption)>
                                        {{ $perPageOption }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="small text-muted">baris per halaman</span>
                        </form>
                    </div>
                    <div class="card-body">
                        @if (!$assignmentPaginator || $assignmentPaginator->isEmpty())
                            <div class="alert alert-light mb-0">
                                Belum ada penugasan assessment yang bisa dimonitor.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Penugasan</th>
                                            <th>Fase</th>
                                            <th>Distribusi</th>
                                            <th>Progres Isi</th>
                                            <th>Review</th>
                                            <th>Rata-rata</th>
                                            <th>Sesi</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($assignmentPaginator as $item)
                                            <tr>
                                                <td>
                                                    <div class="font-weight-bold">{{ $item['title'] }}</div>
                                                    <small class="text-muted">
                                                        {{ $item['code'] }} • {{ $item['period_label'] }}
                                                    </small>
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge badge-{{ $item['phase'] === 'jatuh_tempo' ? 'danger' : ($item['phase'] === 'tuntas' ? 'success' : ($item['phase'] === 'terjadwal' ? 'secondary' : 'info')) }}">
                                                        {{ $item['phase_label'] }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div>{{ $item['stored_target_total'] }}/{{ $item['target_total'] }} tersimpan</div>
                                                    <small
                                                        class="text-{{ $item['distribution_missing_total'] > 0 ? 'danger' : 'muted' }}">
                                                        {{ $item['distribution_missing_total'] }} belum tersimpan
                                                    </small>
                                                </td>
                                                <td>
                                                    <div>{{ $item['submitted_total'] }} selesai / {{ $item['target_total'] }} target</div>
                                                    <small class="text-muted">
                                                        {{ $item['in_progress_total'] }} mengerjakan •
                                                        {{ $item['pending_total'] }} belum selesai
                                                    </small>
                                                </td>
                                                <td>
                                                    <div>{{ $item['pending_review_total'] }} peserta</div>
                                                    <small class="text-muted">
                                                        {{ $item['pending_review_item_total'] }} item
                                                    </small>
                                                </td>
                                                <td>
                                                    {{ $item['average_score'] !== null ? number_format((float) $item['average_score'], 2) : '-' }}
                                                </td>
                                                <td>
                                                    @if ($item['session_enabled'] ?? true)
                                                        {{ $item['sessions_total'] }} sesi
                                                    @else
                                                        Tanpa sesi
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('assessment.assignment.show', $item['id']) }}"
                                                        class="btn btn-info btn-sm">
                                                        <i class="fas fa-eye mr-1"></i> Pantau
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex flex-wrap justify-content-between align-items-center mt-3">
                                <div class="small text-muted">
                                    Menampilkan {{ $assignmentPaginator->firstItem() ?? 0 }} -
                                    {{ $assignmentPaginator->lastItem() ?? 0 }} dari
                                    {{ $assignmentPaginator->total() }} penugasan
                                </div>
                                <div>
                                    {{ $assignmentPaginator->appends(request()->except('assignment_page'))->links() }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('library/chart.js/dist/Chart.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            const chartPayload = {
                participantStatus: @json($charts['participant_status'] ?? ['labels' => [], 'data' => []]),
                assignmentProgress: @json($charts['assignment_progress'] ?? ['labels' => [], 'submitted' => [], 'pending' => []]),
                kabupatenCompletion: @json($charts['kabupaten_completion'] ?? ['labels' => [], 'submitted' => [], 'pending' => []]),
                sessionUtilization: @json($charts['session_utilization'] ?? ['labels' => [], 'occupancy' => [], 'completion' => []]),
                explorerStatus: @json($explorerCharts['status'] ?? ['labels' => [], 'data' => []]),
                explorerLevels: @json($explorerCharts['levels'] ?? ['labels' => [], 'data' => []]),
                explorerRadar: @json($explorerCharts['radar'] ?? ['labels' => [], 'data' => [], 'max_score' => 5]),
                explorerCompetencies: @json($explorerCharts['competencies'] ?? ['labels' => [], 'data' => []]),
                explorerTraining: @json($explorerCharts['training'] ?? ['labels' => [], 'jp_totals' => [], 'participant_totals' => []]),
            };
            const explorerMode = @json($explorerMode);

            if (typeof Chart === 'undefined') {
                return;
            }

            const participantCtx = document.getElementById('assignmentParticipantStatusChart');
            if (participantCtx) {
                new Chart(participantCtx, {
                    type: 'doughnut',
                    data: {
                        labels: chartPayload.participantStatus.labels,
                        datasets: [{
                            data: chartPayload.participantStatus.data,
                            backgroundColor: ['#47c363', '#6777ef', '#ffa426', '#6c757d', '#fc544b'],
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        legend: {
                            position: 'bottom',
                        },
                    },
                });
            }

            const assignmentCtx = document.getElementById('assignmentProgressChart');
            if (assignmentCtx) {
                new Chart(assignmentCtx, {
                    type: 'bar',
                    data: {
                        labels: chartPayload.assignmentProgress.labels,
                        datasets: [{
                                label: 'Sudah Isi',
                                data: chartPayload.assignmentProgress.submitted,
                                backgroundColor: '#47c363',
                            },
                            {
                                label: 'Sedang Mengerjakan',
                                data: chartPayload.assignmentProgress.in_progress,
                                backgroundColor: '#ffa426',
                            },
                            {
                                label: 'Belum Isi',
                                data: chartPayload.assignmentProgress.pending,
                                backgroundColor: '#6c757d',
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            xAxes: [{
                                stacked: true,
                            }],
                            yAxes: [{
                                stacked: true,
                                ticks: {
                                    beginAtZero: true,
                                    precision: 0,
                                },
                            }],
                        },
                    },
                });
            }

            const kabupatenCtx = document.getElementById('assignmentKabupatenCompletionChart');
            if (kabupatenCtx) {
                new Chart(kabupatenCtx, {
                    type: 'bar',
                    data: {
                        labels: chartPayload.kabupatenCompletion.labels,
                        datasets: [{
                                label: 'Sudah Isi',
                                data: chartPayload.kabupatenCompletion.submitted,
                                backgroundColor: '#47c363',
                            },
                            {
                                label: 'Belum Isi',
                                data: chartPayload.kabupatenCompletion.pending,
                                backgroundColor: '#fc544b',
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            xAxes: [{
                                stacked: true,
                            }],
                            yAxes: [{
                                stacked: true,
                                ticks: {
                                    beginAtZero: true,
                                    precision: 0,
                                },
                            }],
                        },
                    },
                });
            }

            const sessionCtx = document.getElementById('assignmentSessionUtilizationChart');
            if (sessionCtx) {
                new Chart(sessionCtx, {
                    type: 'bar',
                    data: {
                        labels: chartPayload.sessionUtilization.labels,
                        datasets: [{
                                label: 'Okupansi Sesi (%)',
                                data: chartPayload.sessionUtilization.occupancy,
                                backgroundColor: '#6777ef',
                            },
                            {
                                label: 'Penyelesaian Sesi (%)',
                                data: chartPayload.sessionUtilization.completion,
                                backgroundColor: '#47c363',
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero: true,
                                    max: 100,
                                },
                            }],
                        },
                    },
                });
            }

            if (explorerMode === 'summary') {
                const explorerRadarCtx = document.getElementById('globalExplorerRadarChart');
                if (explorerRadarCtx) {
                    new Chart(explorerRadarCtx, {
                        type: 'radar',
                        data: {
                            labels: chartPayload.explorerRadar.labels,
                            datasets: [{
                                label: 'Rata-rata Kompetensi',
                                data: chartPayload.explorerRadar.data,
                                backgroundColor: 'rgba(103, 119, 239, 0.18)',
                                borderColor: '#6777ef',
                                pointBackgroundColor: '#6777ef',
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            legend: {
                                position: 'bottom',
                            },
                            scale: {
                                ticks: {
                                    beginAtZero: true,
                                    min: 0,
                                    max: chartPayload.explorerRadar.max_score || 5,
                                    stepSize: 1,
                                },
                            },
                        },
                    });
                }

                const explorerStatusCtx = document.getElementById('globalExplorerStatusChart');
                if (explorerStatusCtx) {
                    new Chart(explorerStatusCtx, {
                        type: 'doughnut',
                        data: {
                            labels: chartPayload.explorerStatus.labels,
                            datasets: [{
                                data: chartPayload.explorerStatus.data,
                                backgroundColor: ['#47c363', '#ffa426', '#6c757d', '#6777ef'],
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            legend: {
                                position: 'bottom',
                            },
                        },
                    });
                }

                const explorerLevelCtx = document.getElementById('globalExplorerLevelChart');
                if (explorerLevelCtx) {
                    new Chart(explorerLevelCtx, {
                        type: 'bar',
                        data: {
                            labels: chartPayload.explorerLevels.labels,
                            datasets: [{
                                label: 'Jumlah Peserta',
                                data: chartPayload.explorerLevels.data,
                                backgroundColor: ['#6777ef', '#3abaf4', '#ffa426', '#47c363', '#fc544b'],
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            legend: {
                                display: false,
                            },
                            scales: {
                                yAxes: [{
                                    ticks: {
                                        beginAtZero: true,
                                        precision: 0,
                                    },
                                }],
                            },
                        },
                    });
                }

                const explorerCompetencyCtx = document.getElementById('globalExplorerCompetencyChart');
                if (explorerCompetencyCtx) {
                    new Chart(explorerCompetencyCtx, {
                        type: 'bar',
                        data: {
                            labels: chartPayload.explorerCompetencies.labels,
                            datasets: [{
                                label: 'Rata-rata Skor',
                                data: chartPayload.explorerCompetencies.data,
                                backgroundColor: '#3abaf4',
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            legend: {
                                display: false,
                            },
                            scales: {
                                yAxes: [{
                                    ticks: {
                                        beginAtZero: true,
                                        min: 0,
                                        max: 5,
                                    },
                                }],
                            },
                        },
                    });
                }

                const explorerTrainingCtx = document.getElementById('globalExplorerTrainingChart');
                if (explorerTrainingCtx) {
                    new Chart(explorerTrainingCtx, {
                        type: 'bar',
                        data: {
                            labels: chartPayload.explorerTraining.labels,
                            datasets: [{
                                    label: 'Total JP',
                                    data: chartPayload.explorerTraining.jp_totals,
                                    backgroundColor: '#1376bd',
                                    order: 2,
                                },
                                {
                                    type: 'line',
                                    label: 'Jumlah Peserta',
                                    data: chartPayload.explorerTraining.participant_totals,
                                    borderColor: '#f59e0b',
                                    backgroundColor: 'rgba(245, 158, 11, 0.18)',
                                    fill: false,
                                    borderWidth: 2,
                                    pointBackgroundColor: '#f59e0b',
                                    pointRadius: 4,
                                    yAxisID: 'y-axis-1',
                                    order: 1,
                                },
                            ],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            legend: {
                                position: 'bottom',
                            },
                            scales: {
                                yAxes: [{
                                        id: 'y-axis-0',
                                        ticks: {
                                            beginAtZero: true,
                                        },
                                        scaleLabel: {
                                            display: true,
                                            labelString: 'Total JP',
                                        },
                                    },
                                    {
                                        id: 'y-axis-1',
                                        position: 'right',
                                        gridLines: {
                                            drawOnChartArea: false,
                                        },
                                        ticks: {
                                            beginAtZero: true,
                                            precision: 0,
                                        },
                                        scaleLabel: {
                                            display: true,
                                            labelString: 'Jumlah Peserta',
                                        },
                                    },
                                ],
                            },
                        },
                    });
                }
            }
        });
    </script>
@endpush
