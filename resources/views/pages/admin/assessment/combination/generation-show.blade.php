@extends('layouts.app', ['title' => 'Monitoring Generate Kombinasi'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('library/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/datatables.net-select-bs4/css/select.bootstrap4.min.css') }}">
@endpush

@section('content')
    @php
        $statusMeta = $generation->status_meta;
        $generatedCombinations = $generation->combinations;
        $totalGeneratedQuestions = $generatedCombinations->sum('total_questions');
        $totalAssignments = $generatedCombinations->sum('assignments_count');
        $shouldAutoRefresh = $generation->status === 'diproses' || (($monitoring['batch']['pending_jobs'] ?? 0) > 0);
    @endphp

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Monitoring Generate Kombinasi</h1>
                <div class="section-header-breadcrumb">
                    <a href="{{ route('assessment.combination.index') }}" class="btn btn-light mr-2">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <a href="{{ route('assessment.combination.create') }}" class="btn btn-primary mr-2">
                        <i class="fas fa-plus"></i> Buat Permintaan Baru
                    </a>
                    @if ($monitoring['retry_available'] ?? false)
                        <form action="{{ route('assessment.combination.generation.retry', $generation->id) }}" method="POST"
                            class="d-inline-block">
                            @csrf
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-redo"></i> {{ $monitoring['action_label'] ?? 'Retry' }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="section-body">
                @if (session('combination_notice'))
                    <div class="alert alert-info">
                        {{ session('combination_notice') }}
                    </div>
                @endif

                @if ($errors->has('combination'))
                    <div class="alert alert-danger">
                        {{ $errors->first('combination') }}
                    </div>
                @endif

                @if ($shouldAutoRefresh)
                    <div class="alert alert-info">
                        Antrean generate masih berjalan. Halaman ini memuat ulang otomatis setiap 5 detik.
                    </div>
                @endif

                <div class="row">
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-primary">
                                <i class="fas fa-layer-group"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Target Kombinasi</h4>
                                </div>
                                <div class="card-body">
                                    {{ $generation->total_kombinasi }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Berhasil Tersimpan</h4>
                                </div>
                                <div class="card-body">
                                    {{ $monitoring['generated_total'] ?? 0 }}
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
                                    <h4>Belum Lengkap</h4>
                                </div>
                                <div class="card-body">
                                    {{ $monitoring['missing_total'] ?? 0 }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-info">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Child Soal Tersimpan</h4>
                                </div>
                                <div class="card-body">
                                    {{ $totalGeneratedQuestions }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>Informasi Proses Generate</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="text-muted small">Kode Proses</div>
                                <div class="font-weight-bold">{{ $generation->kode_generate }}</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="text-muted small">Ketenagaan</div>
                                <div>
                                    <span class="badge badge-{{ $generation->target_ketenagaan_badge_class }}">
                                        {{ $generation->target_ketenagaan_label ?: '-' }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="text-muted small">Status</div>
                                <div>
                                    <span class="badge badge-{{ $statusMeta['badge_class'] }}">
                                        {{ $statusMeta['label'] }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="text-muted small">Dibuat Oleh</div>
                                <div>{{ optional($generation->generator)->name ?: 'Sistem' }}</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="text-muted small">Waktu Permintaan</div>
                                <div>{{ optional($generation->created_at)->format('d M Y H:i') }}</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="text-muted small">Selesai Diproses</div>
                                <div>{{ optional($generation->processed_at)->format('d M Y H:i') ?: '-' }}</div>
                            </div>
                            <div class="col-md-4 mb-0">
                                <div class="text-muted small">Batch ID</div>
                                <div>{{ $generation->job_batch_id ?: 'Belum tersimpan' }}</div>
                            </div>
                            <div class="col-md-4 mb-0">
                                <div class="text-muted small">Kombinasi Berhasil</div>
                                <div>{{ $monitoring['generated_total'] ?? 0 }} / {{ $generation->total_kombinasi }}</div>
                            </div>
                            <div class="col-md-4 mb-0">
                                <div class="text-muted small">Dipakai Penugasan</div>
                                <div>{{ $totalAssignments }} penugasan</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>Monitoring Antrean Batch</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 col-6 mb-3">
                                <div class="text-muted small">Progress Queue</div>
                                <div class="font-weight-bold">{{ $monitoring['queue_progress'] ?? 0 }}%</div>
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
                                <div
                                    class="font-weight-bold text-{{ ($monitoring['batch']['failed_jobs'] ?? 0) > 0 ? 'danger' : 'success' }}">
                                    {{ $monitoring['batch']['failed_jobs'] ?? 0 }}
                                </div>
                            </div>
                        </div>

                        <div class="progress mb-3" data-height="18">
                            <div class="progress-bar bg-info" role="progressbar"
                                style="width: {{ $monitoring['queue_progress'] ?? 0 }}%;"
                                aria-valuenow="{{ $monitoring['queue_progress'] ?? 0 }}" aria-valuemin="0"
                                aria-valuemax="100">
                                {{ $monitoring['queue_progress'] ?? 0 }}%
                            </div>
                        </div>

                        @if ($monitoring['batch'] && ! ($monitoring['batch']['found'] ?? false))
                            <div class="alert alert-warning">
                                Data batch tidak ditemukan lagi pada tabel queue, tetapi proses generate ini masih tercatat
                                pada sistem.
                            </div>
                        @endif

                        @if ($monitoring['failed_jobs'] ?? [])
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Waktu Gagal</th>
                                            <th>Queue</th>
                                            <th>Urutan Kombinasi</th>
                                            <th>Pesan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($monitoring['failed_jobs'] as $failedJob)
                                            <tr>
                                                <td>{{ $failedJob['failed_at'] ? $failedJob['failed_at']->format('d M Y H:i') : '-' }}
                                                </td>
                                                <td>{{ $failedJob['queue'] ?: '-' }}</td>
                                                <td>Kombinasi #{{ $failedJob['sequence'] }}</td>
                                                <td>{{ $failedJob['message'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @elseif ($monitoring['retry_available'] ?? false)
                            <div class="alert alert-warning mb-0">
                                Tidak ada detail failed job yang bisa dibaca, tetapi sistem masih mendeteksi generate
                                belum lengkap. Gunakan tombol {{ strtolower($monitoring['action_label'] ?? 'retry') }}
                                untuk melanjutkan proses.
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>Hasil Kombinasi Yang Sudah Tersimpan</h4>
                    </div>
                    <div class="card-body">
                        @if ($generatedCombinations->isEmpty())
                            <div class="empty-state" data-height="220">
                                <div class="empty-state-icon bg-warning">
                                    <i class="fas fa-hourglass-half"></i>
                                </div>
                                <h2>Belum ada kombinasi yang tersimpan</h2>
                                <p class="lead">
                                    Tunggu proses antrean selesai atau gunakan tombol retry/resume jika ada job yang gagal.
                                </p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="table-generation-combinations">
                                    <thead>
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th>Kode Kombinasi</th>
                                            <th>Struktur</th>
                                            <th>Dibuat</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($generatedCombinations as $combination)
                                            <tr>
                                                <td class="text-center">
                                                    {{ $combination->generation_sequence ?: $loop->iteration }}
                                                </td>
                                                <td>
                                                    <div class="font-weight-bold">{{ $combination->kode_kombinasi }}</div>
                                                    <small class="text-muted">
                                                        {{ $combination->total_questions }} child soal / {{ $combination->items_count }} item
                                                    </small>
                                                </td>
                                                <td>
                                                    <div>{{ $combination->total_assessments }} assessment sumber</div>
                                                    <div>{{ $combination->total_forms }} form</div>
                                                    <small class="text-muted">{{ $combination->assignments_count }} penugasan</small>
                                                </td>
                                                <td>
                                                    <div>{{ optional($combination->generated_at ?: $combination->created_at)->format('d M Y H:i') }}
                                                    </div>
                                                    <small class="text-muted">
                                                        {{ optional($combination->generator)->name ?: 'Sistem' }}
                                                    </small>
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('assessment.combination.show', $combination->id) }}"
                                                        class="btn btn-info btn-sm">
                                                        <i class="fas fa-eye mr-1"></i> Detail
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
    <script src="{{ asset('library/datatables/media/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('library/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('library/datatables.net-select-bs4/js/select.bootstrap4.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            const table = $('#table-generation-combinations');

            if (table.length) {
                table.DataTable({
                    order: [],
                    pageLength: 10,
                    autoWidth: false,
                    columnDefs: [{
                        targets: [0, 4],
                        orderable: false,
                        searchable: false,
                    }],
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/2.1.0/i18n/id.json',
                    },
                });
            }

            @if ($shouldAutoRefresh)
                window.setTimeout(() => window.location.reload(), 5000);
            @endif
        });
    </script>
@endpush
