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
    </style>
@endpush

@section('content')
    @php
        $summary = $monitoringPanel['summary'] ?? [];
        $charts = $monitoringPanel['charts'] ?? [];
        $attentionAssignments = collect($monitoringPanel['attention_assignments'] ?? []);
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
                    peserta yang belum, review assessor pending, dan distribusi score terbaru.
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
            };

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
        });
    </script>
@endpush
