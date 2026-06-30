@extends('layouts.app', ['title' => 'Data Penugasan Assessment'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('library/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/datatables.net-select-bs4/css/select.bootstrap4.min.css') }}">
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

        .monitor-mini-list {
            max-height: 360px;
            overflow-y: auto;
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
                <h1>Data Penugasan Assessment</h1>
                <div class="section-header-breadcrumb">
                    <a href="{{ route('assessment.assignment.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Buat Penugasan
                    </a>
                </div>
            </div>

            <div class="section-body">
                @if (session('assignment_notice'))
                    <div class="alert alert-info">
                        {{ session('assignment_notice') }}
                    </div>
                @endif

                @if ($errors->has('assignment'))
                    <div class="alert alert-danger">
                        {{ $errors->first('assignment') }}
                    </div>
                @endif

                <div class="card">
                    <div class="card-header">
                        <h4>Panel Monitor Assessment</h4>
                        <div class="card-header-action">
                            <span class="badge badge-light">Refresh browser untuk update terbaru</span>
                        </div>
                    </div>
                    <div class="card-body">
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

                <div class="card">
                    <div class="card-header">
                        <h4>Tabel Assessment Assignments</h4>
                    </div>
                    <div class="card-body">
                        @if ($datas->isEmpty())
                            <div class="empty-state" data-height="320">
                                <div class="empty-state-icon bg-primary">
                                    <i class="fas fa-tasks"></i>
                                </div>
                                <h2>Belum ada penugasan assessment</h2>
                                <p class="lead">
                                    Data pada halaman ini diambil dari tabel penugasan assessment yang sudah dibuat
                                    admin untuk mendistribusikan form ke seluruh user pada ketenagaan yang dipilih.
                                </p>
                                <a href="{{ route('assessment.assignment.create') }}" class="btn btn-primary mt-3">
                                    Buat Penugasan
                                </a>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="table-assignment-index">
                                    <thead>
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th>Penugasan</th>

                                            <th>Assessment</th>
                                            <th>Periode</th>
                                            <th>Distribusi</th>
                                            <th>Target & Sesi</th>

                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($datas as $data)
                                            @php
                                                $monitoring = $monitoringByAssignmentId[$data->id] ?? null;
                                                $statusBadge =
                                                    [
                                                        'draft' => 'secondary',
                                                        'diproses' => 'warning',
                                                        'selesai' => 'success',
                                                        'gagal' => 'danger',
                                                    ][$data->status_distribusi] ?? 'secondary';
                                                $deliveryType = $data->job_batch_id ? 'Batch Job' : 'Langsung';
                                                $assessments = $data->assessments;
                                                $combination = $data->combination;
                                            @endphp
                                            <tr class="align-middle">
                                                <td class="text-center">{{ $loop->iteration }}</td>
                                                <td >
                                                    <small class="text-muted text-sm">{{ $data->kode_penugasan }}</small>
                                                    <br>

                                                    <span class="font-weight-bold">{{ $data->judul_penugasan }}</span>
                                                    <br>
                                                    @if ($data->target_ketenagaan_label)
                                                        <small class="">
                                                            {{ $data->target_ketenagaan_label }}
                                                        </small>
                                                    @endif
                                                </td>

                                                <td>
                                                    @if ($combination)
                                                        <div class="font-weight-bold">{{ $combination->kode_kombinasi }}
                                                        </div>
                                                        <small class="text-muted">
                                                            {{ $combination->total_assessments }} assessment sumber /
                                                            {{ $combination->total_forms }} form /
                                                            {{ $combination->total_questions }} soal
                                                        </small>
                                                    @else
                                                        <div class="font-weight-bold">Distribusi kombinasi otomatis</div>
                                                        <small class="text-muted">
                                                            {{ $assessments->count() }} assessment sumber /
                                                            {{ $assessments->sum(fn($assessment) => $assessment->forms->count()) }}
                                                            form /
                                                            {{ $assessments->sum(fn($assessment) => $assessment->forms->sum(fn($form) => $form->fields->count())) }}
                                                            soal
                                                        </small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div>
                                                        {{ $data->tanggal_mulai ? \App\Helpers\Helper::dateIndo($data->tanggal_mulai) : '-' }}
                                                    </div>
                                                    <small class="text-muted">
                                                        {{ $data->jam_mulai_label ? $data->jam_mulai_label . ' WITA' : 'Jam awal belum diatur' }}
                                                    </small>
                                                    <br>
                                                    <small class="text-muted">
                                                        s/d
                                                        {{ $data->tanggal_selesai ? \App\Helpers\Helper::dateIndo($data->tanggal_selesai) : '-' }}
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-{{ $statusBadge }}">
                                                        {{ ucfirst($data->status_distribusi) }}
                                                    </span>
                                                    <div class="text-muted mt-1">
                                                        {{ $deliveryType }}
                                                    </div>
                                                    <small class="d-block text-muted mt-1">
                                                        {{ $data->total_ditugaskan }}/{{ $data->total_target }} target
                                                        tersimpan
                                                    </small>
                                                    @if (($monitoring['missing_target_total'] ?? 0) > 0)
                                                        <small class="d-block text-danger">
                                                            {{ $monitoring['missing_target_total'] }} target belum
                                                            tersimpan
                                                        </small>
                                                    @endif
                                                    @if (($monitoring['batch']['found'] ?? false) && $data->job_batch_id)
                                                        <small class="d-block text-muted">
                                                            Job:
                                                            {{ $monitoring['batch']['processed_jobs'] ?? 0 }}/{{ $monitoring['batch']['total_jobs'] ?? 0 }}
                                                            selesai
                                                            @if (($monitoring['batch']['failed_jobs'] ?? 0) > 0)
                                                                / {{ $monitoring['batch']['failed_jobs'] }} gagal
                                                            @endif
                                                        </small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="font-weight-bold">{{ $data->total_target }} user</div>
                                                    <small class="text-muted">
                                                        {{ $data->total_sesi }} sesi / {{ $data->kapasitas_per_sesi }}
                                                        peserta per sesi
                                                    </small>
                                                    <br>
                                                    <small class="text-muted">
                                                        {{ $data->durasi_sesi_jam }} jam per sesi
                                                    </small>
                                                    <br>
                                                    <small class="text-muted">
                                                        Sesi awal
                                                        {{ $data->jam_mulai_label ? $data->jam_mulai_label . ' WITA' : 'belum diatur' }}
                                                    </small>
                                                </td>

                                                <td class="text-center">
                                                    <a href="{{ route('assessment.assignment.show', $data->id) }}"
                                                        class="btn btn-info btn-sm my-1">
                                                        <i class="fas fa-eye mr-1"></i> Detail
                                                    </a>
                                                    <a href="{{ route('assessment.assignment.edit', $data->id) }}"
                                                        class="btn btn-warning btn-sm my-1">
                                                        <i class="fas fa-edit mr-1"></i> Edit
                                                    </a>
                                                    <button type="button"
                                                        class="btn btn-danger btn-sm my-1 js-assignment-delete-trigger"
                                                        data-toggle="modal" data-target="#assignmentDeleteModal"
                                                        data-route="{{ route('assessment.assignment.hapus', $data->id) }}"
                                                        data-code="{{ $data->kode_penugasan }}"
                                                        data-title="{{ $data->judul_penugasan }}"
                                                        data-target-total="{{ $data->total_target }}">
                                                        <i class="fas fa-trash mr-1"></i> Hapus
                                                    </button>
                                                    @if ($monitoring['retry_available'] ?? false)
                                                        <form
                                                            action="{{ route('assessment.assignment.retry', $data->id) }}"
                                                            method="POST" class="d-inline-block my-1">
                                                            @csrf
                                                            <button type="submit" class="btn btn-danger btn-sm">
                                                                <i class="fas fa-redo mr-1"></i> Retry
                                                            </button>
                                                        </form>
                                                    @endif
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

    <div class="modal fade" id="assignmentDeleteModal" tabindex="-1" role="dialog"
        aria-labelledby="assignmentDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white" id="assignmentDeleteModalLabel">Hapus Penugasan Assessment</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">
                        Penugasan <strong id="assignment-delete-title">-</strong> akan dihapus permanen.
                    </p>
                    <p class="text-muted mb-3">
                        Kode penugasan: <span id="assignment-delete-code">-</span>
                    </p>
                    <ul class="pl-3 mb-3">
                        <li>Seluruh pembagian peserta dan sesi assessment akan dihapus.</li>
                        <li>Riwayat mulai/submit, jawaban, penilaian, dan file unggahan peserta ikut dibersihkan.</li>
                        <li>Antrean distribusi yang masih tersisa untuk penugasan ini juga tidak bisa dipakai lagi.</li>
                    </ul>
                    <div class="alert alert-warning mb-0">
                        Total peserta yang terdampak: <strong id="assignment-delete-target-total">0</strong> user.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                    <form method="POST" id="assignment-delete-form" class="d-inline-block">
                        @csrf
                        <button type="submit" class="btn btn-danger">
                            Ya, Hapus Permanen
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('library/chart.js/dist/Chart.min.js') }}"></script>
    <script src="{{ asset('library/datatables/media/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('library/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('library/datatables.net-select-bs4/js/select.bootstrap4.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            const table = $('#table-assignment-index');
            const chartPayload = {
                participantStatus: @json($charts['participant_status'] ?? ['labels' => [], 'data' => []]),
                assignmentProgress: @json($charts['assignment_progress'] ?? ['labels' => [], 'submitted' => [], 'pending' => []]),
                kabupatenCompletion: @json($charts['kabupaten_completion'] ?? ['labels' => [], 'submitted' => [], 'pending' => []]),
                sessionUtilization: @json($charts['session_utilization'] ?? ['labels' => [], 'occupancy' => [], 'completion' => []]),
            };

            function renderCharts() {
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
            }

            if (table.length) {
                table.DataTable({
                    order: [],
                    pageLength: 10,
                    autoWidth: false,
                    columnDefs: [{
                        targets: [0, 6],
                        orderable: false,
                        searchable: false,
                    }, ],
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/2.1.0/i18n/id.json',
                    },
                });
            }

            $('.js-assignment-delete-trigger').on('click', function() {
                const trigger = $(this);

                $('#assignment-delete-form').attr('action', trigger.data('route'));
                $('#assignment-delete-title').text(trigger.data('title') || '-');
                $('#assignment-delete-code').text(trigger.data('code') || '-');
                $('#assignment-delete-target-total').text(trigger.data('target-total') || 0);
            });

            renderCharts();
        });
    </script>
@endpush
