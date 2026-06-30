@extends('layouts.app', ['title' => 'Detail Penugasan Assessment'])

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
            font-size: 1.6rem;
            font-weight: 700;
            line-height: 1.1;
        }

        .monitor-mini-list {
            max-height: 350px;
            overflow-y: auto;
        }
    </style>
@endpush

@section('content')
    @php
        $statusBadge =
            [
                'draft' => 'secondary',
                'diproses' => 'warning',
                'selesai' => 'success',
                'gagal' => 'danger',
            ][$assignment->status_distribusi] ?? 'secondary';

        $assessments = $assignment->assessments;
        $combination = $assignment->combination;
        $totalAssessments = $combination?->total_assessments ?: $assessments->count();
        $totalForms = $combination?->total_forms ?: $assessments->sum(fn($assessment) => $assessment->forms->count());
        $totalFields = $combination?->total_questions ?: $assessments->sum(
            fn($assessment) => $assessment->forms->sum(fn($form) => $form->fields->count()),
        );
        $resolvedSecurityConfig = \App\Support\Assessment\AssessmentSecurityConfig::normalize($assignment->security_config ?? []);
        $monitoringSummary = $monitoringPanel['summary'] ?? [];
        $detailLists = $monitoringPanel['lists'] ?? [];
        $detailCharts = $monitoringPanel['charts'] ?? [];
    @endphp

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Detail Penugasan Assessment</h1>
                <div class="section-header-breadcrumb">
                    <a href="{{ route('assessment.assignment.index') }}" class="btn btn-light mr-2">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <a href="{{ route('assessment.assignment.edit', $assignment->id) }}" class="btn btn-warning mr-2">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <button type="button" class="btn btn-danger mr-2" data-toggle="modal"
                        data-target="#assignmentDeleteModal">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                    @if ($monitoring['retry_available'] ?? false)
                        <form action="{{ route('assessment.assignment.retry', $assignment->id) }}" method="POST"
                            class="d-inline-block mr-2">
                            @csrf
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-redo"></i> Retry
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('assessment.assignment.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Buat Penugasan Baru
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

                <div class="row">
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-primary">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Target User</h4>
                                </div>
                                <div class="card-body">
                                    {{ $assignment->total_target }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-success">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Sudah Ditugaskan</h4>
                                </div>
                                <div class="card-body">
                                    {{ $assignment->total_ditugaskan }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-info">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Sesi</h4>
                                </div>
                                <div class="card-body">
                                    {{ $assignment->total_sesi }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-warning">
                                <i class="fas fa-hourglass-half"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Durasi Per Sesi</h4>
                                </div>
                                <div class="card-body">
                                    {{ $assignment->durasi_sesi_jam }} jam
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>Informasi Penugasan</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="text-muted small">Kode Penugasan</div>
                            <div class="font-weight-bold">{{ $assignment->kode_penugasan }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="text-muted small">Judul</div>
                            <div class="font-weight-bold">{{ $assignment->judul_penugasan }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="text-muted small">Status Distribusi</div>
                            <div>
                                <span class="badge badge-{{ $statusBadge }}">
                                    {{ ucfirst($assignment->status_distribusi) }}
                                </span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="text-muted small">Ketenagaan Target</div>
                            <div>
                                @if ($assignment->target_ketenagaan_label)
                                    <span class="badge badge-{{ $assignment->target_ketenagaan_badge_class }}">
                                        {{ $assignment->target_ketenagaan_label }}
                                    </span>
                                @else
                                    <span class="text-muted">Belum tersimpan</span>
                                @endif
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="text-muted small">Jabatan Target</div>
                            <div>
                                @forelse ($assignment->target_jabatan_labels as $jabatanLabel)
                                    <span class="badge badge-light border mr-1 mb-1">{{ $jabatanLabel }}</span>
                                @empty
                                    <span class="text-muted">Semua jabatan pada ketenagaan target</span>
                                @endforelse
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="text-muted small">Kabupaten Target</div>
                            <div>
                                @forelse ($assignment->target_kabupaten_labels as $kabupatenLabel)
                                    <span class="badge badge-light border mr-1 mb-1">{{ $kabupatenLabel }}</span>
                                @empty
                                    <span class="text-muted">Semua kabupaten pada jabatan target</span>
                                @endforelse
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="text-muted small">Periode</div>
                            <div>
                                {{ $assignment->tanggal_mulai ? \App\Helpers\Helper::dateIndo($assignment->tanggal_mulai) : '-' }}
                                @if ($assignment->jam_mulai_label)
                                    / {{ $assignment->jam_mulai_label }} WITA
                                @endif
                                s/d
                                {{ $assignment->tanggal_selesai ? \App\Helpers\Helper::dateIndo($assignment->tanggal_selesai) : '-' }}
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="text-muted small">Jam Sesi Awal</div>
                            <div>{{ $assignment->jam_mulai_label ? $assignment->jam_mulai_label . ' WITA' : 'Belum diatur' }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="text-muted small">Dibuat Oleh</div>
                            <div>{{ optional($assignment->creator)->name ?: 'Sistem' }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="text-muted small">Pengaturan Sesi</div>
                            <div>{{ $assignment->total_sesi }} sesi</div>
                            <small class="text-muted">
                                {{ $assignment->kapasitas_per_sesi }} peserta per sesi / {{ $assignment->durasi_sesi_jam }}
                                jam per sesi
                            </small>
                            @if ($assignment->jam_mulai_label)
                                <br>
                                <small class="text-muted">
                                    Patokan sesi 1 mulai {{ $assignment->jam_mulai_label }} WITA
                                </small>
                            @endif
                        </div>
                        <div class="mb-3">
                            <div class="text-muted small">Guard Ujian</div>
                            <div class="font-weight-bold">
                                {{ $resolvedSecurityConfig['enabled'] ? 'Aktif' : 'Tidak Aktif' }}
                            </div>
                            <small class="text-muted d-block">
                                Fullscreen: {{ $resolvedSecurityConfig['require_fullscreen'] ? 'Wajib' : 'Opsional' }}
                            </small>
                            <small class="text-muted d-block">
                                Limit serius: {{ $resolvedSecurityConfig['max_serious_violations'] }} |
                                Lock: {{ $resolvedSecurityConfig['temporary_lock_seconds'] }} detik |
                                Grace fullscreen: {{ $resolvedSecurityConfig['fullscreen_grace_seconds'] }} detik
                            </small>
                        </div>
                        <div class="mb-3">
                            <div class="text-muted small">Form Assessment Dipilih</div>
                            <div>{{ $totalAssessments }} assessment sumber</div>
                            <small class="text-muted">
                                {{ $totalForms }} form / {{ $totalFields }} pertanyaan
                            </small>
                        </div>
                        <div class="mb-3">
                            <div class="text-muted small">Kombinasi Soal Dipakai</div>
                            <div>
                                @if ($combination)
                                    <a href="{{ route('assessment.combination.show', $combination->id) }}">
                                        {{ $combination->kode_kombinasi }}
                                    </a>
                                @else
                                    <span class="text-muted">Belum terhubung ke kombinasi soal.</span>
                                @endif
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="text-muted small">Batch ID</div>
                            <div>{{ $assignment->job_batch_id ?: 'Distribusi langsung' }}</div>
                        </div>
                        <div class="mb-0">
                            <div class="text-muted small">Deskripsi</div>
                            <div>{{ $assignment->deskripsi ?: 'Tidak ada deskripsi tambahan.' }}</div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>Monitoring Distribusi Queue</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 col-6 mb-3">
                                <div class="text-muted small">Metode Distribusi</div>
                                <div class="font-weight-bold">{{ ucfirst($monitoring['distribution_type'] ?? 'langsung') }}</div>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="text-muted small">Target Tersimpan</div>
                                <div class="font-weight-bold">
                                    {{ $monitoring['assigned_total'] ?? 0 }} / {{ $monitoring['target_total'] ?? 0 }}
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="text-muted small">Belum Tersimpan</div>
                                <div class="font-weight-bold text-{{ ($monitoring['missing_target_total'] ?? 0) > 0 ? 'danger' : 'success' }}">
                                    {{ $monitoring['missing_target_total'] ?? 0 }} target
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="text-muted small">Batch ID</div>
                                <div class="font-weight-bold">{{ $assignment->job_batch_id ?: 'Distribusi langsung' }}</div>
                            </div>
                        </div>

                        @if ($monitoring['batch'])
                            <div class="row">
                                <div class="col-md-3 col-6 mb-3">
                                    <div class="text-muted small">Total Job Chunk</div>
                                    <div class="font-weight-bold">{{ $monitoring['batch']['total_jobs'] ?? 0 }}</div>
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <div class="text-muted small">Job Selesai</div>
                                    <div class="font-weight-bold">{{ $monitoring['batch']['processed_jobs'] ?? 0 }}</div>
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <div class="text-muted small">Job Pending</div>
                                    <div class="font-weight-bold">{{ $monitoring['batch']['pending_jobs'] ?? 0 }}</div>
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <div class="text-muted small">Job Gagal</div>
                                    <div class="font-weight-bold text-{{ ($monitoring['batch']['failed_jobs'] ?? 0) > 0 ? 'danger' : 'success' }}">
                                        {{ $monitoring['batch']['failed_jobs'] ?? 0 }}
                                    </div>
                                </div>
                            </div>

                            @if ($monitoring['batch']['found'] ?? false)
                                <div class="progress mb-3" data-height="18">
                                    <div class="progress-bar bg-info" role="progressbar"
                                        style="width: {{ $monitoring['batch']['progress'] ?? 0 }}%;"
                                        aria-valuenow="{{ $monitoring['batch']['progress'] ?? 0 }}" aria-valuemin="0"
                                        aria-valuemax="100">
                                        {{ $monitoring['batch']['progress'] ?? 0 }}%
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    Batch job tersimpan pada penugasan, tetapi data batch tidak ditemukan lagi pada tabel queue.
                                </div>
                            @endif
                        @endif

                        @if ($monitoring['failed_jobs'] ?? [])
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Waktu Gagal</th>
                                            <th>Queue</th>
                                            <th>Target Chunk</th>
                                            <th>Pesan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($monitoring['failed_jobs'] as $failedJob)
                                            <tr>
                                                <td>
                                                    {{ $failedJob['failed_at'] ? $failedJob['failed_at']->format('d M Y H:i') : '-' }}
                                                </td>
                                                <td>{{ $failedJob['queue'] ?: '-' }}</td>
                                                <td>{{ $failedJob['target_count'] }} target</td>
                                                <td>{{ $failedJob['message'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @elseif ($assignment->status_distribusi === 'gagal')
                            <div class="alert alert-warning mb-0">
                                Tidak ada detail failed job yang bisa dibaca, tetapi sistem masih mendeteksi distribusi
                                belum lengkap. Tombol retry akan me-resume target yang belum tersimpan.
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>Panel Monitor Peserta</h4>
                        <div class="card-header-action">
                            <span class="badge badge-light">Refresh browser untuk memuat progres terbaru</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-light border">
                            Panel ini membaca progres peserta langsung dari database saat halaman dibuka. Cocok untuk
                            memantau siapa yang sudah mengisi, belum mengisi, timeout, dan hasil yang masih butuh
                            review assessor tanpa polling otomatis.
                        </div>

                        <div class="row">
                            <div class="col-xl-3 col-lg-4 col-md-6 col-6">
                                <div class="card monitor-kpi-card">
                                    <div class="card-body">
                                        <div class="monitor-kpi-label">Total Target</div>
                                        <div class="monitor-kpi-value text-dark">
                                            {{ $monitoringSummary['target_total'] ?? 0 }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-4 col-md-6 col-6">
                                <div class="card monitor-kpi-card">
                                    <div class="card-body">
                                        <div class="monitor-kpi-label">Sudah Mengisi</div>
                                        <div class="monitor-kpi-value text-success">
                                            {{ $monitoringSummary['submitted_total'] ?? 0 }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-4 col-md-6 col-6">
                                <div class="card monitor-kpi-card">
                                    <div class="card-body">
                                        <div class="monitor-kpi-label">Belum Mengisi</div>
                                        <div class="monitor-kpi-value text-danger">
                                            {{ $monitoringSummary['pending_total'] ?? 0 }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-4 col-md-6 col-6">
                                <div class="card monitor-kpi-card">
                                    <div class="card-body">
                                        <div class="monitor-kpi-label">Sedang Mengerjakan</div>
                                        <div class="monitor-kpi-value text-warning">
                                            {{ $monitoringSummary['in_progress_total'] ?? 0 }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-4 col-md-6 col-6">
                                <div class="card monitor-kpi-card">
                                    <div class="card-body">
                                        <div class="monitor-kpi-label">Belum Mulai</div>
                                        <div class="monitor-kpi-value text-secondary">
                                            {{ $monitoringSummary['not_started_total'] ?? 0 }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-4 col-md-6 col-6">
                                <div class="card monitor-kpi-card">
                                    <div class="card-body">
                                        <div class="monitor-kpi-label">Timeout</div>
                                        <div class="monitor-kpi-value text-info">
                                            {{ $monitoringSummary['timeout_total'] ?? 0 }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-4 col-md-6 col-6">
                                <div class="card monitor-kpi-card">
                                    <div class="card-body">
                                        <div class="monitor-kpi-label">Review Pending</div>
                                        <div class="monitor-kpi-value text-primary">
                                            {{ $monitoringSummary['pending_review_total'] ?? 0 }}
                                        </div>
                                        <small class="text-muted">
                                            {{ $monitoringSummary['pending_review_item_total'] ?? 0 }} item
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-4 col-md-6 col-6">
                                <div class="card monitor-kpi-card">
                                    <div class="card-body">
                                        <div class="monitor-kpi-label">Rata-rata Skor</div>
                                        <div class="monitor-kpi-value text-success">
                                            {{ isset($monitoringSummary['average_score']) && $monitoringSummary['average_score'] !== null ? number_format((float) $monitoringSummary['average_score'], 2) : '-' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span>Tingkat penyelesaian</span>
                                        <strong>{{ number_format((float) ($monitoringSummary['completion_rate'] ?? 0), 2) }}%</strong>
                                    </div>
                                    <div class="progress" data-height="12">
                                        <div class="progress-bar bg-success" role="progressbar"
                                            style="width: {{ min((float) ($monitoringSummary['completion_rate'] ?? 0), 100) }}%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span>Tingkat partisipasi</span>
                                        <strong>{{ number_format((float) ($monitoringSummary['participation_rate'] ?? 0), 2) }}%</strong>
                                    </div>
                                    <div class="progress" data-height="12">
                                        <div class="progress-bar bg-warning" role="progressbar"
                                            style="width: {{ min((float) ($monitoringSummary['participation_rate'] ?? 0), 100) }}%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="card border">
                                    <div class="card-header">
                                        <h4>Peserta Sudah Mengisi</h4>
                                    </div>
                                    <div class="card-body monitor-mini-list">
                                        @forelse ($detailLists['submitted_participants'] ?? [] as $participant)
                                            <div class="mb-3 pb-3 border-bottom">
                                                <div class="d-flex justify-content-between">
                                                    <div class="font-weight-bold">{{ $participant['name'] }}</div>
                                                    <span class="badge badge-success">{{ $participant['status_label'] }}</span>
                                                </div>
                                                <div class="text-muted small">
                                                    {{ $participant['kabupaten'] }} • {{ $participant['session_label'] }}
                                                </div>
                                                <div class="small mt-1">
                                                    Submit: {{ $participant['submitted_at'] ?: '-' }}
                                                    @if ($participant['score_label'])
                                                        • Skor {{ $participant['score_label'] }}
                                                        @if ($participant['score_level'])
                                                            ({{ $participant['score_level'] }})
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-muted">Belum ada peserta yang menyelesaikan assessment.</div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card border">
                                    <div class="card-header">
                                        <h4>Peserta Belum Mengisi</h4>
                                    </div>
                                    <div class="card-body monitor-mini-list">
                                        @forelse ($detailLists['pending_participants'] ?? [] as $participant)
                                            <div class="mb-3 pb-3 border-bottom">
                                                <div class="d-flex justify-content-between">
                                                    <div class="font-weight-bold">{{ $participant['name'] }}</div>
                                                    <span class="badge badge-secondary">{{ $participant['status_label'] }}</span>
                                                </div>
                                                <div class="text-muted small">
                                                    {{ $participant['kabupaten'] }} • {{ $participant['session_label'] }}
                                                </div>
                                                <div class="small mt-1">
                                                    Mulai: {{ $participant['started_at'] ?: 'Belum ada aktivitas' }}
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-muted">Semua peserta pada penugasan ini sudah mengisi assessment.</div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card border">
                                    <div class="card-header">
                                        <h4>Perlu Review Assessor</h4>
                                    </div>
                                    <div class="card-body monitor-mini-list">
                                        @forelse ($detailLists['pending_review_participants'] ?? [] as $participant)
                                            <div class="mb-3 pb-3 border-bottom">
                                                <div class="d-flex justify-content-between">
                                                    <div class="font-weight-bold">{{ $participant['name'] }}</div>
                                                    <span class="badge badge-primary">
                                                        {{ $participant['manual_pending_items'] }} item
                                                    </span>
                                                </div>
                                                <div class="text-muted small">
                                                    {{ $participant['kabupaten'] }} • {{ $participant['session_label'] }}
                                                </div>
                                                <div class="small mt-1">
                                                    <a href="{{ $participant['review_url'] }}" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-clipboard-check mr-1"></i> Review Nilai
                                                    </a>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-muted">Tidak ada jawaban yang menunggu review assessor.</div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card border">
                                    <div class="card-header">
                                        <h4>Skor Terendah</h4>
                                    </div>
                                    <div class="card-body monitor-mini-list">
                                        @forelse ($detailLists['low_score_participants'] ?? [] as $participant)
                                            <div class="mb-3 pb-3 border-bottom">
                                                <div class="d-flex justify-content-between">
                                                    <div class="font-weight-bold">{{ $participant['name'] }}</div>
                                                    <span class="badge badge-warning">
                                                        {{ $participant['score_label'] ?: '-' }}
                                                    </span>
                                                </div>
                                                <div class="text-muted small">
                                                    {{ $participant['kabupaten'] }} • {{ $participant['session_label'] }}
                                                </div>
                                                <div class="small mt-1">
                                                    Level: {{ $participant['score_level'] ?: 'Belum terpetakan' }}
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-muted">Belum ada skor akhir peserta yang dapat dibandingkan.</div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-4">
                                <div class="card border">
                                    <div class="card-header">
                                        <h4>Status Peserta</h4>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="assignmentDetailStatusChart" height="250"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-8">
                                <div class="card border">
                                    <div class="card-header">
                                        <h4>Progress Per Sesi</h4>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="assignmentDetailSessionChart" height="250"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-8">
                                <div class="card border">
                                    <div class="card-header">
                                        <h4>Progress Per Kabupaten</h4>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="assignmentDetailKabupatenChart" height="250"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="card border">
                                    <div class="card-header">
                                        <h4>Distribusi Level Skor</h4>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="assignmentDetailScoreLevelChart" height="250"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>Assessment Yang Ditugaskan</h4>
                    </div>
                    <div class="card-body">
                        @if ($assessments->isEmpty())
                            <div class="alert alert-warning mb-0">
                                Penugasan ini belum memiliki form assessment terhubung.
                            </div>
                        @else
                            <div class="mb-3">
                                <div class="text-muted small">Ringkasan Assessment</div>
                                <div class="font-weight-bold">{{ $totalAssessments }} assessment</div>
                                <small class="text-muted">
                                    {{ $totalForms }} form / {{ $totalFields }} pertanyaan
                                </small>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped" id="table-assignment-assessment">
                                    <thead>
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th>Kode</th>
                                            <th>Judul</th>
                                            <th>Status</th>
                                            <th>Struktur</th>
                                            <th>Deskripsi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($assessments as $assessment)
                                            @php
                                                $assessmentStatusBadge =
                                                    $assessment->status === 'publish'
                                                        ? 'success'
                                                        : ($assessment->status === 'draft'
                                                            ? 'warning'
                                                            : 'secondary');
                                            @endphp
                                            <tr>
                                                <td class="text-center">{{ $loop->iteration }}</td>
                                                <td class="font-weight-bold">{{ $assessment->kode_assessment }}</td>
                                                <td>
                                                    {{ $assessment->judul }}
                                                    @if ($assessment->target_ketenagaan_label)
                                                        <br>
                                                        <small class="text-muted">{{ $assessment->target_ketenagaan_label }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge badge-{{ $assessmentStatusBadge }}">
                                                        {{ ucfirst($assessment->status) }}
                                                    </span>
                                                    <span
                                                        class="badge badge-{{ $assessment->is_active ? 'primary' : 'light' }}">
                                                        {{ $assessment->is_active ? 'Aktif' : 'Nonaktif' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    {{ $assessment->forms->count() }} form /
                                                    {{ $assessment->forms->sum(fn($form) => $form->fields->count()) }}
                                                    pertanyaan
                                                </td>
                                                <td>
                                                    {{ \Illuminate\Support\Str::limit($assessment->deskripsi ?: 'Tidak ada deskripsi assessment.', 120) }}
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
                        <h4>Pembagian Sesi Assessment</h4>
                    </div>
                    <div class="card-body">
                        @if ($assignment->sessions->isEmpty())
                            <div class="alert alert-warning mb-0">
                                Sesi assessment belum terbentuk pada penugasan ini.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped" id="table-assignment-session">
                                    <thead>
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th>Label Sesi</th>
                                            <th>Jadwal</th>
                                            <th>Kapasitas</th>
                                            <th>Alokasi Peserta</th>
                                            <th>Durasi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($assignment->sessions as $session)
                                            <tr>
                                                <td class="text-center">{{ $session->nomor_sesi }}</td>
                                                <td class="font-weight-bold">{{ $session->label_sesi }}</td>
                                                <td>{{ $session->jadwal_sesi_label ?: 'Jadwal belum diatur' }}</td>
                                                <td>{{ $session->kapasitas_peserta }} peserta</td>
                                                <td>{{ $session->total_peserta }} peserta</td>
                                                <td>{{ $session->durasi_sesi_jam }} jam</td>
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
                        <h4>Daftar Peserta Yang Ditugasi</h4>
                    </div>
                    <div class="card-body">
                        @if ($assignment->targets->isEmpty())
                            <div class="alert alert-warning mb-0">
                                Penugasan ini masih diproses atau belum memiliki target peserta tersimpan.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped" id="table-assignment-target">
                                    <thead>
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th>Nama Peserta</th>
                                            <th>Instansi</th>
                                            <th>Kabupaten</th>
                                            <th>Sesi Assessment</th>
                                            <th>Status Target</th>
                                            <th>Waktu Ditugaskan</th>
                                            <th>Mulai Dikerjakan</th>
                                            <th>Batas Selesai</th>
                                            <th>Selesai / Timeout</th>
                                            <th>Mode Selesai</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($assignment->targets as $target)
                                            @php
                                                $targetBadge =
                                                    [
                                                        'ditugaskan' => 'primary',
                                                        'dikerjakan' => 'warning',
                                                        'selesai' => 'success',
                                                        'dibatalkan' => 'secondary',
                                                    ][$target->status] ?? 'secondary';
                                                $attemptStatus = optional($target->attempt)->status;
                                                $completionMode = optional($target->attempt)->completion_mode ?: $target->completion_mode;
                                                $isDisqualified = optional($target->attempt)->disqualified_at !== null;
                                                $seriousViolationCount = (int) (optional($target->attempt)->serious_violation_count ?? 0);
                                                $warningViolationCount = (int) (optional($target->attempt)->warning_violation_count ?? 0);
                                                $completionBadge = $isDisqualified
                                                    ? 'danger'
                                                    : ($completionMode === 'timeout' ? 'secondary' : 'success');
                                            @endphp
                                            <tr>
                                                <td class="text-center">{{ $loop->iteration }}</td>
                                                <td>
                                                    <div class="font-weight-bold">
                                                        {{ optional($target->guru)->nama_lengkap ?: 'Peserta tidak ditemukan' }}
                                                    </div>
                                                    <small class="text-muted">
                                                        {{ collect([
                                                            optional($target->guru)->email ?: null,
                                                            optional($target->guru)->eksternal_jabatan ?: null,
                                                            optional($target->guru)->jenis_jabatan ?: null,
                                                        ])->filter()->implode(' | ') ?: '-' }}
                                                    </small>
                                                </td>
                                                <td>{{ optional($target->guru)->satuan_pendidikan ?: '-' }}</td>
                                                <td>{{ optional($target->guru)->kabupaten ?: '-' }}</td>
                                                <td>
                                                    <div class="font-weight-bold">
                                                        {{ optional($target->session)->label_sesi ?: 'Belum dipetakan' }}
                                                    </div>
                                                    <small class="text-muted">
                                                        {{ optional($target->session)->jadwal_sesi_label ?: 'Jadwal belum diatur' }}
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-{{ $targetBadge }}">
                                                        {{ ucfirst($target->status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    {{ $target->assigned_at ? $target->assigned_at->format('d M Y H:i') : '-' }}
                                                </td>
                                                <td>
                                                    {{ $target->started_at ? $target->started_at->format('d M Y H:i') : '-' }}
                                                </td>
                                                <td>
                                                    {{ $target->deadline_at ? $target->deadline_at->format('d M Y H:i') : '-' }}
                                                </td>
                                                <td>
                                                    @if ($target->submitted_at)
                                                        {{ $target->submitted_at->format('d M Y H:i') }}
                                                    @elseif ($target->timed_out_at)
                                                        {{ $target->timed_out_at->format('d M Y H:i') }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($isDisqualified)
                                                        <span class="badge badge-{{ $completionBadge }}">
                                                            Didiskualifikasi
                                                        </span>
                                                        <small class="text-muted d-block mt-2">
                                                            {{ optional($target->attempt)->disqualification_reason ?: 'Guard ujian menghentikan sesi peserta.' }}
                                                        </small>
                                                    @elseif ($completionMode)
                                                        <span class="badge badge-{{ $completionBadge }}">
                                                            {{ $completionMode === 'timeout' ? 'Timeout' : 'Manual' }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                    @if ($seriousViolationCount > 0 || $warningViolationCount > 0)
                                                        <small class="text-muted d-block mt-2">
                                                            Pelanggaran: {{ $seriousViolationCount }} serius /
                                                            {{ $warningViolationCount }} warning
                                                        </small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($attemptStatus === 'submitted')
                                                        <a href="{{ route('assessment.assignment.review.show', $target->id) }}"
                                                            class="btn btn-sm btn-primary">
                                                            <i class="fas fa-clipboard-check"></i> Review Nilai
                                                        </a>
                                                    @elseif ($attemptStatus === 'in_progress')
                                                        <span class="badge badge-warning">Sedang dikerjakan</span>
                                                    @else
                                                        <span class="text-muted">Belum ada hasil</span>
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
                        Penugasan <strong>{{ $assignment->judul_penugasan }}</strong> akan dihapus permanen.
                    </p>
                    <p class="text-muted mb-3">
                        Kode penugasan: {{ $assignment->kode_penugasan }}
                    </p>
                    <ul class="pl-3 mb-3">
                        <li>Seluruh pembagian peserta dan sesi assessment akan dihapus.</li>
                        <li>Riwayat mulai/submit, jawaban, penilaian, dan file unggahan peserta ikut dibersihkan.</li>
                        <li>Antrean distribusi yang masih tersisa untuk penugasan ini juga tidak dapat dipakai lagi.</li>
                    </ul>
                    <div class="alert alert-warning mb-0">
                        Total peserta yang terdampak: <strong>{{ $assignment->total_target }}</strong> user.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                    <form action="{{ route('assessment.assignment.hapus', $assignment->id) }}" method="POST"
                        class="d-inline-block">
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
            const chartPayload = {
                status: @json($detailCharts['participant_status'] ?? ['labels' => [], 'data' => []]),
                session: @json($detailCharts['session_completion'] ?? ['labels' => [], 'submitted' => [], 'pending' => []]),
                kabupaten: @json($detailCharts['kabupaten_completion'] ?? ['labels' => [], 'submitted' => [], 'pending' => []]),
                scoreLevels: @json($detailCharts['score_levels'] ?? ['labels' => [], 'data' => []]),
            };

            function initDataTable(selector, nonSortableColumns) {
                const table = $(selector);

                if (!table.length) {
                    return;
                }

                table.DataTable({
                    order: [],
                    pageLength: 10,
                    autoWidth: false,
                    columnDefs: [{
                        targets: nonSortableColumns,
                        orderable: false,
                        searchable: false,
                    }, ],
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/2.1.0/i18n/id.json',
                    },
                });
            }

            function initCharts() {
                if (typeof Chart === 'undefined') {
                    return;
                }

                const statusCtx = document.getElementById('assignmentDetailStatusChart');
                if (statusCtx) {
                    new Chart(statusCtx, {
                        type: 'doughnut',
                        data: {
                            labels: chartPayload.status.labels,
                            datasets: [{
                                data: chartPayload.status.data,
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

                const sessionCtx = document.getElementById('assignmentDetailSessionChart');
                if (sessionCtx) {
                    new Chart(sessionCtx, {
                        type: 'bar',
                        data: {
                            labels: chartPayload.session.labels,
                            datasets: [{
                                    label: 'Sudah Isi',
                                    data: chartPayload.session.submitted,
                                    backgroundColor: '#47c363',
                                },
                                {
                                    label: 'Belum Isi',
                                    data: chartPayload.session.pending,
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

                const kabupatenCtx = document.getElementById('assignmentDetailKabupatenChart');
                if (kabupatenCtx) {
                    new Chart(kabupatenCtx, {
                        type: 'bar',
                        data: {
                            labels: chartPayload.kabupaten.labels,
                            datasets: [{
                                    label: 'Sudah Isi',
                                    data: chartPayload.kabupaten.submitted,
                                    backgroundColor: '#47c363',
                                },
                                {
                                    label: 'Belum Isi',
                                    data: chartPayload.kabupaten.pending,
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

                const scoreLevelCtx = document.getElementById('assignmentDetailScoreLevelChart');
                if (scoreLevelCtx) {
                    new Chart(scoreLevelCtx, {
                        type: 'bar',
                        data: {
                            labels: chartPayload.scoreLevels.labels,
                            datasets: [{
                                label: 'Jumlah Peserta',
                                data: chartPayload.scoreLevels.data,
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
            }

            initDataTable('#table-assignment-assessment', [0]);
            initDataTable('#table-assignment-session', [0]);
            initDataTable('#table-assignment-target', [0, 11]);
            initCharts();
        });
    </script>
@endpush
