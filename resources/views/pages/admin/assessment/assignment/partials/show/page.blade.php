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
        $sessionEnabled = $assignment->usesSessionScheduling();
        $resolvedSecurityConfig = \App\Support\Assessment\AssessmentSecurityConfig::normalize($assignment->security_config ?? []);
        $monitoringSummary = $monitoringPanel['summary'] ?? [];
        $detailLists = $monitoringPanel['lists'] ?? [];
        $detailCharts = $monitoringPanel['charts'] ?? [];
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
        $participantAdditionPanel = $participantAdditionPanel ?? [];
        $addParticipantsErrors = $errors->getBag('addParticipants');
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
                    <form action="{{ route('assessment.assignment.activation', $assignment->id) }}" method="POST"
                        class="d-inline-block mr-2"
                        onsubmit="return confirm('{{ $assignment->isActive() ? 'Nonaktifkan penugasan ini? Penugasan akan disembunyikan dari portal peserta tetapi histori tetap tersimpan.' : 'Aktifkan kembali penugasan ini agar muncul lagi di portal peserta?' }}');">
                        @csrf
                        <input type="hidden" name="is_active" value="{{ $assignment->isActive() ? 0 : 1 }}">
                        <button type="submit"
                            class="btn btn-{{ $assignment->isActive() ? 'secondary' : 'success' }}">
                            <i class="fas fa-{{ $assignment->isActive() ? 'toggle-off' : 'toggle-on' }}"></i>
                            {{ $assignment->isActive() ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </form>
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

                @if (! $assignment->isActive())
                    <div class="alert alert-warning">
                        Penugasan ini sedang <strong>nonaktif</strong>. Penugasan tidak tampil di portal peserta sampai
                        diaktifkan kembali, tetapi target, attempt, jawaban, dan histori tetap tersimpan.
                    </div>
                @endif

                @if (!empty($participantAdditionPanel['disabled_reason']))
                    <div class="alert alert-light border">
                        <div class="font-weight-bold mb-1">Tambah Peserta Manual</div>
                        <div>{{ $participantAdditionPanel['disabled_reason'] }}</div>
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
                                    {{ $sessionEnabled ? $assignment->total_sesi : 'Tanpa sesi' }}
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
                                    <h4>{{ $sessionEnabled ? 'Durasi Per Sesi' : 'Durasi Pengerjaan' }}</h4>
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
                            <div class="text-muted small">Status Akses Peserta</div>
                            <div>
                                <span class="badge badge-{{ $assignment->activation_status_badge_class }}">
                                    {{ $assignment->activation_status_label }}
                                </span>
                            </div>
                            <small class="text-muted d-block">
                                Saat nonaktif, penugasan disembunyikan dari portal peserta tanpa menghapus histori.
                            </small>
                        </div>
                        <div class="mb-3">
                            <div class="text-muted small">Mode Sesi</div>
                            <div class="font-weight-bold">{{ $assignment->session_mode_label }}</div>
                            <small class="text-muted d-block">
                                {{ $sessionEnabled
                                    ? 'Peserta dibagi otomatis ke sesi terjadwal.'
                                    : 'Peserta tidak dibagi ke sesi dan dapat mengakses assessment secara fleksibel selama periode penugasan.' }}
                            </small>
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
                            <div class="text-muted small">Satuan Pendidikan Target</div>
                            <div>
                                @php
                                    $targetSchoolLabels = collect($assignment->target_satuan_pendidikan_labels);
                                @endphp
                                @forelse ($targetSchoolLabels->take(10) as $schoolLabel)
                                    <span class="badge badge-light border mr-1 mb-1">{{ $schoolLabel }}</span>
                                @empty
                                    <span class="text-muted">Semua satuan pendidikan pada kabupaten target</span>
                                @endforelse
                                @if ($targetSchoolLabels->count() > 10)
                                    <span class="badge badge-secondary mr-1 mb-1">
                                        +{{ $targetSchoolLabels->count() - 10 }} lainnya
                                    </span>
                                @endif
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
                            <div class="text-muted small">{{ $sessionEnabled ? 'Jam Sesi Awal' : 'Jam Buka Penugasan' }}</div>
                            <div>{{ $assignment->jam_mulai_label ? $assignment->jam_mulai_label . ' WITA' : 'Belum diatur' }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="text-muted small">Dibuat Oleh</div>
                            <div>{{ optional($assignment->creator)->name ?: 'Sistem' }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="text-muted small">Pengaturan Sesi</div>
                            @if ($sessionEnabled)
                                <div>{{ $assignment->total_sesi }} sesi</div>
                                <small class="text-muted">
                                    {{ $assignment->kapasitas_per_sesi }} peserta per sesi /
                                    {{ $assignment->durasi_sesi_jam }} jam per sesi
                                </small>
                                @if ($assignment->jam_mulai_label)
                                    <br>
                                    <small class="text-muted">
                                        Patokan sesi 1 mulai {{ $assignment->jam_mulai_label }} WITA
                                    </small>
                                @endif
                            @else
                                <div>Tanpa sesi</div>
                                <small class="text-muted">
                                    Sistem tidak membuat data sesi dan target peserta tidak disimpan pada slot sesi mana
                                    pun.
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
                            diproses auto scoring tanpa polling otomatis.
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

                        <div class="card monitor-filter-card mb-4" id="monitoring-explorer">
                        <div class="card-header">
                            <div>
                                <h4 class="mb-1">Filter Monitoring Penugasan</h4>
                                <div class="text-muted small">
                                    Filter ini dipakai untuk mode individu dan ringkasan visual agregat.
                                </div>
                            </div>
                            <div class="card-header-action">
                                <div class="d-flex flex-wrap justify-content-end align-items-center" style="gap: 0.5rem;">
                                    <span class="badge badge-light">
                                        {{ $explorerActiveFilterCount }} filter aktif
                                    </span>
                                    <button type="button"
                                        class="btn btn-sm {{ !empty($participantAdditionPanel['can_open_modal']) ? 'btn-success' : 'btn-light disabled' }}"
                                        data-toggle="modal" data-target="#assignmentAddParticipantsModal"
                                        {{ !empty($participantAdditionPanel['can_open_modal']) ? '' : 'disabled' }}>
                                        <i class="fas fa-user-plus mr-1"></i> Tambah Peserta
                                    </button>
                                </div>
                            </div>
                        </div>
                            <div class="card-body">
                                <form action="{{ route('assessment.assignment.show', $assignment->id) }}#monitoring-explorer"
                                    method="GET">
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
                                                <span class="text-muted small">Tidak ada filter aktif. Semua peserta akan ditampilkan.</span>
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
                                            <a href="{{ route('assessment.assignment.show', $assignment->id) }}#monitoring-explorer"
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
                                        <div class="d-flex flex-wrap justify-content-end align-items-center" style="gap: 0.5rem;">
                                            <span class="badge badge-light">
                                                {{ $explorerPaginator?->total() ?? count($explorerRows) }} peserta
                                            </span>
                                            <span class="badge badge-success">
                                                {{ (int) ($participantAdditionPanel['available_total'] ?? 0) }} kandidat tambahan
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @if ($explorerPaginator && $explorerPaginator->total() > 0)
                                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                                            <div class="text-muted small">
                                                {{ $explorerPaginator->total() }} peserta.
                                            </div>
                                            <div class="text-muted small">
                                                Pencarian dan pagination memakai DataTables server-side agar query
                                                tetap ringan.
                                            </div>
                                        </div>
                                    @endif

                                    <div class="table-responsive">
                                        <table class="table table-striped mb-0" id="table-monitoring-individual"
                                            data-source-url="{{ route('assessment.assignment.monitoring-individuals', $assignment->id) }}"
                                            data-page-length="{{ $explorerPaginator?->perPage() ?? request('monitor_per_page', 25) }}"
                                            data-return-url="{{ request()->fullUrl() }}#monitoring-explorer"
                                            data-monitor-kabupaten="{{ $explorerSelectedFilters['kabupaten'] ?? '' }}"
                                            data-monitor-jabatan="{{ $explorerSelectedFilters['jabatan'] ?? '' }}"
                                            data-monitor-satuan-pendidikan="{{ $explorerSelectedFilters['satuan_pendidikan'] ?? '' }}">
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
                                                @forelse ($explorerRows as $participant)
                                                    <tr>
                                                        <td class="text-center">
                                                            {{ (($explorerPaginator?->firstItem() ?? 1) - 1) + $loop->iteration }}
                                                        </td>
                                                        <td>
                                                            <div class="font-weight-bold">{{ $participant['name'] }}</div>
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
                                                            @if ($participant['review_url'] || $participant['retry_url'])
                                                                <div class="d-flex flex-wrap"
                                                                    style="gap: 0.35rem;">
                                                                    @if ($participant['review_url'])
                                                                        <a href="{{ $participant['review_url'] }}"
                                                                            class="btn btn-sm btn-primary">
                                                                            <i class="fas fa-clipboard-check mr-1"></i> Detail
                                                                        </a>
                                                                    @endif
                                                                    @if ($participant['retry_url'])
                                                                        <form action="{{ $participant['retry_url'] }}"
                                                                            method="POST" class="d-inline-block mb-0"
                                                                            onsubmit="return confirm('Izinkan peserta ini mengulangi assessment dari record terakhir? Jawaban yang sudah tersimpan tidak akan dihapus.');">
                                                                            @csrf
                                                                            <input type="hidden" name="return_url"
                                                                                value="{{ request()->fullUrl() }}#monitoring-explorer">
                                                                            <button type="submit"
                                                                                class="btn btn-sm btn-warning">
                                                                                <i class="fas fa-redo mr-1"></i> Ulangi
                                                                            </button>
                                                                        </form>
                                                                    @endif
                                                                </div>
                                                            @else
                                                                <span class="text-muted small">Belum ada hasil</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted py-4">
                                                            Tidak ada peserta yang cocok dengan filter monitoring saat
                                                            ini.
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>

                                    @if ($explorerPaginator)
                                        <div class="mt-3 monitoring-individual-pagination">
                                            {{ $explorerPaginator->links() }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="card monitor-summary-card mb-4">
                                <div class="card-header">
                                    <div>
                                        <h4 class="mb-1">Ringkasan Visual Semua Peserta Terfilter</h4>
                                        <div class="text-muted small">
                                            Nilai di bawah ini adalah hasil agregasi keseluruhan peserta yang lolos filter.
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
                                        Mode ringkasan disimpan singkat di cache agar agregasi skor, level, dan
                                        kompetensi tidak menghitung ulang semua peserta setiap refresh.
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
                                                <div class="monitor-summary-stat__label">Review Pending</div>
                                                <div class="monitor-summary-stat__value text-primary">
                                                    {{ $explorerSummary['pending_review_total'] ?? 0 }}
                                                </div>
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
                                            Belum ada skor peserta yang bisa diagregasi pada filter ini. Grafik status
                                            tetap ditampilkan untuk memantau progres pengisian.
                                        </div>
                                    @endif

                                    @if (! ($explorerMeta['has_training_data'] ?? false))
                                        <div class="alert alert-light border">
                                            Belum ada data pelatihan yang bisa diagregasi pada filter ini. Grafik
                                            pelatihan akan aktif otomatis setelah peserta mengirim jawaban portfolio
                                            pengalaman pelatihan.
                                        </div>
                                    @endif

                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="card border">
                                                <div class="card-header">
                                                    <h4>Jaring Laba-Laba Kompetensi</h4>
                                                </div>
                                                <div class="card-body monitor-summary-chart">
                                                    <canvas id="assignmentExplorerRadarChart"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="card border">
                                                <div class="card-header">
                                                    <h4>Status Peserta Terfilter</h4>
                                                </div>
                                                <div class="card-body monitor-summary-chart">
                                                    <canvas id="assignmentExplorerStatusChart"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="card border">
                                                <div class="card-header">
                                                    <h4>Distribusi Level Umum</h4>
                                                </div>
                                                <div class="card-body monitor-summary-chart">
                                                    <canvas id="assignmentExplorerLevelChart"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="card border">
                                                <div class="card-header">
                                                    <h4>Rata-rata Kompetensi</h4>
                                                </div>
                                                <div class="card-body monitor-summary-chart">
                                                    <canvas id="assignmentExplorerCompetencyChart"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <div class="card border">
                                                <div class="card-header">
                                                    <h4>Ringkasan Pelatihan dan Total JP</h4>
                                                </div>
                                                <div class="card-body monitor-summary-chart">
                                                    <canvas id="assignmentExplorerTrainingChart"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
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
                                            <th>Konfig Tahap</th>
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
                                                $stageConfig = \App\Support\Assessment\AssessmentStageConfig::normalize(
                                                    is_array($assessment->pivot?->stage_config ?? null)
                                                        ? $assessment->pivot->stage_config
                                                        : [],
                                                    \App\Support\Assessment\AssessmentStageConfig::defaultForAssessment(
                                                        $assessment->instrument_type,
                                                        max($loop->iteration - 1, 0)
                                                    )
                                                );
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
                                                    <div class="small text-muted">
                                                        Akses:
                                                        {{ ($stageConfig['entry_mode'] ?? null) === \App\Support\Assessment\AssessmentStageConfig::ENTRY_START_BUTTON ? 'Tombol mulai' : 'Langsung isi' }}
                                                    </div>
                                                    <div class="small text-muted">
                                                        Draft: {{ $stageConfig['allow_draft'] ? 'Ya' : 'Tidak' }}
                                                    </div>
                                                    <div class="small text-muted">
                                                        Submit:
                                                        {{ ($stageConfig['finalize_mode'] ?? null) === \App\Support\Assessment\AssessmentStageConfig::FINALIZE_AUTO ? 'Auto saat selesai' : 'Manual / permanen' }}
                                                    </div>
                                                    <div class="small text-muted">
                                                        Timer:
                                                        {{ $stageConfig['time_limit_minutes'] ? $stageConfig['time_limit_minutes'].' menit' : 'Tanpa timer' }}
                                                    </div>
                                                    <div class="small text-muted">
                                                        Guard:
                                                        {{ data_get($stageConfig, 'security.enabled', false) ? 'Aktif' : 'Nonaktif' }}
                                                        /
                                                        Fullscreen:
                                                        {{ data_get($stageConfig, 'security.require_fullscreen', false) ? 'Wajib' : 'Opsional' }}
                                                    </div>
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
                        <h4>{{ $sessionEnabled ? 'Pembagian Sesi Assessment' : 'Mode Akses Assessment' }}</h4>
                    </div>
                    <div class="card-body">
                        @if (! $sessionEnabled)
                            <div class="alert alert-info mb-0">
                                Mode tanpa sesi aktif. Peserta tidak dipetakan ke sesi mana pun dan dapat memulai
                                assessment secara fleksibel selama periode penugasan.
                            </div>
                        @elseif ($assignment->sessions->isEmpty())
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

            </div>
        </section>
    </div>

    <div class="modal fade" id="assignmentAddParticipantsModal" tabindex="-1" role="dialog"
        aria-labelledby="assignmentAddParticipantsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document" id="assignment-add-participants">
            <form action="{{ route('assessment.assignment.add-participants', $assignment->id) }}" method="POST"
                class="modal-content">
                @csrf
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white" id="assignmentAddParticipantsModalLabel">Tambah Peserta Penugasan</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-light border">
                        Peserta baru akan ditambahkan ke penugasan ini tanpa reset distribusi, tanpa menghapus jawaban,
                        dan tanpa mengubah progres peserta lama yang sedang mengerjakan maupun yang sudah selesai.
                    </div>

                    <div class="alert alert-success border">
                        Daftar di bawah ini memuat semua peserta pada ketenagaan
                        <strong>{{ $assignment->target_ketenagaan_label ?: '-' }}</strong>, kecuali yang sudah ada di
                        penugasan ini.
                    </div>

                    <div class="row">
                        <div class="col-lg-4 mb-3">
                            <div class="text-muted small">Ketenagaan</div>
                            <div class="font-weight-bold">{{ $assignment->target_ketenagaan_label ?: '-' }}</div>
                        </div>
                        <div class="col-lg-4 mb-3">
                            <div class="text-muted small">Peserta Sudah Ditugaskan</div>
                            <div class="font-weight-bold">{{ $assignment->total_ditugaskan }} peserta</div>
                        </div>
                        <div class="col-lg-4 mb-3">
                            <div class="text-muted small">Kandidat Tambahan Tersedia</div>
                            <div class="font-weight-bold text-success">
                                {{ (int) ($participantAdditionPanel['available_total'] ?? 0) }} peserta
                            </div>
                        </div>
                    </div>

                    @if ($addParticipantsErrors->has('guru_ids'))
                        <div class="alert alert-danger">
                            {{ $addParticipantsErrors->first('guru_ids') }}
                        </div>
                    @endif

                    <x-multiple-choice-table id="assignment-add-participants-selector" name="guru_ids"
                        :headers="['Nama', 'Email', 'Satuan Pendidikan', 'Kabupaten', 'Verifikasi']"
                        :items="[]" :selected="$participantAdditionPanel['selected_ids'] ?? []"
                        :initialSelectedItems="$participantAdditionPanel['selected_items'] ?? []"
                        ajax-url="{{ route('assessment.assignment.add-participants-options', $assignment->id) }}"
                        page-size="10" search-placeholder="Cari nama peserta tambahan..."
                        empty-message="{{ $participantAdditionPanel['disabled_reason'] ?? 'Tidak ada peserta tambahan yang tersedia pada ketenagaan ini.' }}"
                        selected-title="Peserta Tambahan" />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"
                        {{ !empty($participantAdditionPanel['can_open_modal']) ? '' : 'disabled' }}>
                        <i class="fas fa-user-plus mr-1"></i> Tambahkan ke Penugasan
                    </button>
                </div>
            </form>
        </div>
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
                explorerStatus: @json($explorerCharts['status'] ?? ['labels' => [], 'data' => []]),
                explorerLevels: @json($explorerCharts['levels'] ?? ['labels' => [], 'data' => []]),
                explorerRadar: @json($explorerCharts['radar'] ?? ['labels' => [], 'data' => [], 'max_score' => 5]),
                explorerCompetencies: @json($explorerCharts['competencies'] ?? ['labels' => [], 'data' => []]),
                explorerTraining: @json($explorerCharts['training'] ?? ['labels' => [], 'jp_totals' => [], 'participant_totals' => []]),
            };
            const explorerMode = @json($explorerMode);
            const csrfToken = @json(csrf_token());
            const shouldOpenAddParticipantsModal = @json($addParticipantsErrors->any());

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

            function escapeHtml(value) {
                return $('<div>').text(value ?? '').html();
            }

            function initMonitoringIndividualDataTable() {
                const table = $('#table-monitoring-individual');

                if (!table.length) {
                    return;
                }

                const sourceUrl = table.data('sourceUrl');

                if (!sourceUrl) {
                    return;
                }

                const fallbackPagination = $('.monitoring-individual-pagination');
                const returnUrl = table.data('returnUrl') || '';

                table.DataTable({
                    processing: true,
                    serverSide: true,
                    ordering: false,
                    pageLength: Number(table.data('pageLength')) || 25,
                    lengthMenu: [
                        [10, 25, 50],
                        [10, 25, 50],
                    ],
                    searchDelay: 400,
                    autoWidth: false,
                    ajax: {
                        url: sourceUrl,
                        data: function(d) {
                            d.monitor_kabupaten = table.data('monitorKabupaten') || '';
                            d.monitor_jabatan = table.data('monitorJabatan') || '';
                            d.monitor_satuan_pendidikan = table.data('monitorSatuanPendidikan') || '';
                        },
                    },
                    columns: [{
                            data: 'row_number',
                            className: 'text-center',
                            searchable: false,
                        },
                        {
                            data: null,
                            render: function(data, type, row) {
                                if (type !== 'display') {
                                    return [
                                        row.name || '',
                                        row.jabatan || '',
                                        row.kabupaten || '',
                                        row.school || '',
                                        row.session_label || '',
                                        row.status_label || '',
                                    ].join(' ');
                                }

                                return `
                                    <div class="font-weight-bold">${escapeHtml(row.name || '-')}</div>
                                    <small class="text-muted d-block">
                                        ${escapeHtml(row.jabatan || '-')} • ${escapeHtml(row.kabupaten || '-')}
                                    </small>
                                    <small class="text-muted d-block">
                                        ${escapeHtml(row.school || '-')} • ${escapeHtml(row.session_label || '-')}
                                    </small>
                                    <small class="text-muted d-block">
                                        Status: ${escapeHtml(row.status_label || '-')}
                                    </small>
                                `;
                            },
                        },
                        {
                            data: null,
                            render: function(data, type, row) {
                                if (type !== 'display') {
                                    return [row.score_label || '', row.submitted_at || ''].join(' ');
                                }

                                return `
                                    <div class="font-weight-bold text-success">${escapeHtml(row.score_label || '-')}</div>
                                    <small class="text-muted">
                                        Submit: ${escapeHtml(row.submitted_at || '-')}
                                    </small>
                                `;
                            },
                        },
                        {
                            data: 'score_level',
                            render: function(data, type, row) {
                                if (type !== 'display') {
                                    return row.score_level || '';
                                }

                                if (row.score_level) {
                                    return `<span class="badge badge-info">${escapeHtml(row.score_level)}</span>`;
                                }

                                return '<span class="text-muted">-</span>';
                            },
                        },
                        {
                            data: null,
                            searchable: false,
                            render: function(data, type, row) {
                                if (type !== 'display') {
                                    return [row.review_url || '', row.retry_url || ''].join(' ');
                                }

                                if (row.review_url || row.retry_url) {
                                    const detailButton = row.review_url
                                        ? `
                                            <a href="${escapeHtml(row.review_url)}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-clipboard-check mr-1"></i> Detail
                                            </a>
                                        `
                                        : '';
                                    const retryButton = row.retry_url
                                        ? `
                                            <form action="${escapeHtml(row.retry_url)}" method="POST" class="d-inline-block mb-0"
                                                onsubmit="return confirm('Izinkan peserta ini mengulangi assessment dari record terakhir? Jawaban yang sudah tersimpan tidak akan dihapus.');">
                                                <input type="hidden" name="_token" value="${escapeHtml(csrfToken)}">
                                                <input type="hidden" name="return_url" value="${escapeHtml(returnUrl)}">
                                                <button type="submit" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-redo mr-1"></i> Ulangi
                                                </button>
                                            </form>
                                        `
                                        : '';

                                    return `
                                        <div class="d-flex flex-wrap" style="gap: 0.35rem;">
                                            ${detailButton}
                                            ${retryButton}
                                        </div>
                                    `;
                                }

                                return '<span class="text-muted small">Belum ada hasil</span>';
                            },
                        },
                    ],
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/2.1.0/i18n/id.json',
                        search: 'Cari data:',
                        searchPlaceholder: 'Nama, kabupaten, jabatan, sekolah...',
                    },
                    initComplete: function() {
                        fallbackPagination.hide();
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

            function initExplorerCharts() {
                if (typeof Chart === 'undefined' || explorerMode !== 'summary') {
                    return;
                }

                const radarCtx = document.getElementById('assignmentExplorerRadarChart');
                if (radarCtx) {
                    new Chart(radarCtx, {
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

                const statusCtx = document.getElementById('assignmentExplorerStatusChart');
                if (statusCtx) {
                    new Chart(statusCtx, {
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

                const levelCtx = document.getElementById('assignmentExplorerLevelChart');
                if (levelCtx) {
                    new Chart(levelCtx, {
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

                const competencyCtx = document.getElementById('assignmentExplorerCompetencyChart');
                if (competencyCtx) {
                    new Chart(competencyCtx, {
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

                const trainingCtx = document.getElementById('assignmentExplorerTrainingChart');
                if (trainingCtx) {
                    new Chart(trainingCtx, {
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

            initDataTable('#table-assignment-assessment', [0]);
            initDataTable('#table-assignment-session', [0]);
            initMonitoringIndividualDataTable();
            initCharts();
            initExplorerCharts();

            if (shouldOpenAddParticipantsModal) {
                $('#assignmentAddParticipantsModal').modal('show');
            }
        });
    </script>
@endpush
