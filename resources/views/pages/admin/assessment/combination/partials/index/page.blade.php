@extends('layouts.app', ['title' => 'Panel Kombinasi Soal'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('library/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/datatables.net-select-bs4/css/select.bootstrap4.min.css') }}">
@endpush

@section('content')
    @php
        $activeGenerationCount = $generations->where('status', 'diproses')->count();
        $failedGenerationCount = $generations->where('status', 'gagal')->count();
        $hasRunningGeneration = $activeGenerationCount > 0;
    @endphp

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Panel Kombinasi Soal</h1>
                <div class="section-header-breadcrumb">
                    <a href="{{ route('assessment.index') }}" class="btn btn-light mr-2">
                        <i class="fas fa-arrow-left"></i> Assessment
                    </a>
                    <a href="{{ route('assessment.combination.create') }}" class="btn btn-primary">
                        <i class="fas fa-random"></i> Buat Kombinasi
                    </a>
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

                <div class="row">
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-primary">
                                <i class="fas fa-layer-group"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Proses Generate</h4>
                                </div>
                                <div class="card-body">
                                    {{ $generations->count() }}
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
                                    <h4>Generate Aktif</h4>
                                </div>
                                <div class="card-body">
                                    {{ $activeGenerationCount }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-warning">
                                <i class="fas fa-question-circle"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Kombinasi Tersimpan</h4>
                                </div>
                                <div class="card-body">
                                    {{ $datas->count() }}
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
                                    <h4>Generate Gagal</h4>
                                </div>
                                <div class="card-body">
                                    {{ $failedGenerationCount }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>Riwayat Proses Generate</h4>
                    </div>
                    <div class="card-body">
                        @if ($hasRunningGeneration)
                            <div class="alert alert-info">
                                Ada proses generate yang masih berjalan. Halaman ini memuat ulang otomatis setiap 5 detik.
                            </div>
                        @endif

                        @if ($generations->isEmpty())
                            <div class="empty-state" data-height="220">
                                <div class="empty-state-icon bg-primary">
                                    <i class="fas fa-layer-group"></i>
                                </div>
                                <h2>Belum ada proses generate kombinasi</h2>
                                <p class="lead">
                                    Form buat kombinasi sekarang akan mengirim permintaan ke antrean batch dan hasilnya
                                    dipantau dari tabel ini.
                                </p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="table-combination-generation">
                                    <thead>
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th>Kode Proses</th>
                                            <th>Ketenagaan</th>
                                            <th>Progress</th>
                                            <th>Dibuat</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($generations as $generation)
                                            @php
                                                $monitoring = $generationMonitoring[$generation->id] ?? [];
                                                $statusMeta = $generation->status_meta;
                                            @endphp
                                            <tr>
                                                <td class="text-center">{{ $loop->iteration }}</td>
                                                <td>
                                                    <div class="font-weight-bold">{{ $generation->kode_generate }}</div>
                                                    <small class="text-muted">
                                                        Batch ID: {{ $generation->job_batch_id ?: 'Belum tersimpan' }}
                                                    </small>
                                                </td>
                                                <td>
                                                    <small class="d-inline-block mb-1">
                                                        <span class="badge badge-{{ $generation->target_ketenagaan_badge_class }}">
                                                            {{ $generation->target_ketenagaan_label ?: '-' }}
                                                        </span>
                                                    </small>
                                                    <small class="text-muted d-block">
                                                        {{ $generation->total_kombinasi }} kombinasi diminta
                                                    </small>
                                                </td>
                                                <td>
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <span class="badge badge-{{ $statusMeta['badge_class'] }}">
                                                            {{ $statusMeta['label'] }}
                                                        </span>
                                                        <span class="text-muted small">
                                                            {{ $monitoring['generated_total'] ?? 0 }} / {{ $generation->total_kombinasi }}
                                                        </span>
                                                    </div>
                                                    <div class="progress" data-height="10">
                                                        <div class="progress-bar bg-info" role="progressbar"
                                                            style="width: {{ $monitoring['queue_progress'] ?? 0 }}%;"
                                                            aria-valuenow="{{ $monitoring['queue_progress'] ?? 0 }}"
                                                            aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                    <small class="text-muted d-block mt-1">
                                                        {{ $monitoring['missing_total'] ?? 0 }} kombinasi belum lengkap
                                                    </small>
                                                </td>
                                                <td>
                                                    <div>{{ optional($generation->created_at)->format('d M Y H:i') }}</div>
                                                    <small class="text-muted">
                                                        {{ optional($generation->generator)->name ?: 'Sistem' }}
                                                    </small>
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('assessment.combination.generation.show', $generation->id) }}"
                                                        class="btn btn-info btn-sm my-1">
                                                        <i class="fas fa-eye mr-1"></i> Detail Proses
                                                    </a>
                                                    @if ($monitoring['retry_available'] ?? false)
                                                        <form action="{{ route('assessment.combination.generation.retry', $generation->id) }}"
                                                            method="POST" class="d-inline-block my-1">
                                                            @csrf
                                                            <button type="submit" class="btn btn-danger btn-sm">
                                                                <i class="fas fa-redo mr-1"></i>
                                                                {{ ($monitoring['generated_total'] ?? 0) > 0 ? 'Resume' : 'Retry' }}
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

                <div class="card">
                    <div class="card-header">
                        <h4>Daftar Kombinasi Soal</h4>
                    </div>
                    <div class="card-body">
                        @if ($datas->isEmpty())
                            <div class="empty-state" data-height="320">
                                <div class="empty-state-icon bg-primary">
                                    <i class="fas fa-random"></i>
                                </div>
                                <h2>Belum ada kombinasi soal</h2>
                                <p class="lead">
                                    Buat kombinasi soal acak berdasarkan ketenagaan dan jumlah soal per form
                                    untuk dipakai pada penugasan assessment.
                                </p>
                                <a href="{{ route('assessment.combination.create') }}" class="btn btn-primary mt-3">
                                    Buat Kombinasi
                                </a>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="table-combination-index">
                                    <thead>
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th>Kode</th>
                                            <th>Ketenagaan</th>
                                            <th>Struktur</th>
                                            <th>Dibuat</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($datas as $data)
                                            @php($usageCount = (int) ($data->assignments_count ?? 0) + (int) ($data->assignment_targets_count ?? 0))
                                            <tr>
                                                <td class="text-center">{{ $loop->iteration }}</td>
                                                <td>
                                                    <div class="font-weight-bold">{{ $data->kode_kombinasi }}</div>
                                                    <small class="text-muted">
                                                        {{ $data->is_active ? 'Aktif' : 'Nonaktif' }}
                                                        @if ($data->generation_sequence && $data->generation)
                                                            | {{ $data->generation->kode_generate }} #{{ $data->generation_sequence }}
                                                        @endif
                                                    </small>
                                                </td>
                                                <td>
                                                    @if ($data->target_ketenagaan_label)
                                                        <small class="d-inline-block mb-1">
                                                            <span class="badge badge-{{ $data->target_ketenagaan_badge_class }}">
                                                                {{ $data->target_ketenagaan_label }}
                                                            </span>
                                                        </small>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                    <small class="text-muted d-block">
                                                        Identitas kombinasi memakai kode otomatis.
                                                    </small>
                                                </td>
                                                <td>
                                                    <div>{{ $data->total_assessments }} assessment sumber</div>
                                                    <div>{{ $data->total_forms }} form</div>
                                                    <div class="text-muted">{{ $data->total_questions }} child soal</div>
                                                    <small class="text-muted">
                                                        {{ $data->items_count }} item tersimpan / {{ $usageCount }} pemakaian
                                                    </small>
                                                </td>
                                                <td>
                                                    <div>{{ optional($data->generated_at ?: $data->created_at)->format('d M Y H:i') }}</div>
                                                    <small class="text-muted">
                                                        {{ optional($data->generator)->name ?: 'Sistem' }}
                                                    </small>
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('assessment.combination.show', $data->id) }}"
                                                        class="btn btn-info btn-sm my-1">
                                                        <i class="fas fa-eye mr-1"></i> Detail
                                                    </a>
                                                    @if ($usageCount < 1)
                                                        <form action="{{ route('assessment.combination.hapus', $data->id) }}"
                                                            method="POST" class="d-inline-block my-1"
                                                            onsubmit="return confirm('Hapus kombinasi soal {{ $data->kode_kombinasi }}?')">
                                                            @csrf
                                                            <button type="submit" class="btn btn-danger btn-sm">
                                                                <i class="fas fa-trash mr-1"></i> Hapus
                                                            </button>
                                                        </form>
                                                    @else
                                                        <button type="button" class="btn btn-light btn-sm my-1" disabled>
                                                            <i class="fas fa-lock mr-1"></i> Dipakai
                                                        </button>
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
@endsection

@push('scripts')
    <script src="{{ asset('library/datatables/media/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('library/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('library/datatables.net-select-bs4/js/select.bootstrap4.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            const table = $('#table-combination-index');
            const generationTable = $('#table-combination-generation');

            if (generationTable.length) {
                generationTable.DataTable({
                    order: [],
                    pageLength: 10,
                    autoWidth: false,
                    columnDefs: [{
                        targets: [0, 5],
                        orderable: false,
                        searchable: false,
                    }],
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/2.1.0/i18n/id.json',
                    },
                });
            }

            if (!table.length) {
                @if ($hasRunningGeneration)
                    window.setTimeout(() => window.location.reload(), 5000);
                @endif
                return;
            }

            table.DataTable({
                order: [],
                pageLength: 10,
                autoWidth: false,
                columnDefs: [{
                    targets: [0, 5],
                    orderable: false,
                    searchable: false,
                }],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/2.1.0/i18n/id.json',
                },
            });

            @if ($hasRunningGeneration)
                window.setTimeout(() => window.location.reload(), 5000);
            @endif
        });
    </script>
@endpush
