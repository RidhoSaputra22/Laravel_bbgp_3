@extends('layouts.app', ['title' => 'Progress Sinkronisasi MongoDB'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('library/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
@endpush

@section('content')
    @php
        $hasPendingSync = ($summary['pending_total'] ?? 0) > 0;
    @endphp

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Progress Sinkronisasi MongoDB</h1>
                <div class="section-header-breadcrumb">
                    <a href="{{ route('assessment.assignment.index') }}" class="btn btn-light mr-2">
                        <i class="fas fa-arrow-left"></i> Penugasan
                    </a>
                    <a href="{{ route('assessment.monitoring.index') }}" class="btn btn-primary">
                        <i class="fas fa-chart-line"></i> Monitoring Assessment
                    </a>
                </div>
            </div>

            <div class="section-body">
                @if (! $mongoAvailable)
                    <div class="alert alert-warning">
                        Sinkronisasi MongoDB belum tersedia. Periksa konfigurasi MongoDB dan extension PHP MongoDB.
                    </div>
                @else
                    <div class="alert alert-info">
                        Panel ini menghitung target aktif yang sudah tersedia di MongoDB. Halaman memuat ulang otomatis
                        selama masih ada sinkronisasi yang belum selesai.
                    </div>
                @endif

                <div class="row">
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-primary"><i class="fas fa-tasks"></i></div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Penugasan Aktif</h4></div>
                                <div class="card-body">{{ $summary['assignment_total'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-success"><i class="fas fa-check-circle"></i></div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Sinkron Selesai</h4></div>
                                <div class="card-body">{{ $summary['complete_total'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-warning"><i class="fas fa-sync-alt"></i></div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Menunggu Sinkron</h4></div>
                                <div class="card-body">{{ $summary['pending_total'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-info"><i class="fas fa-database"></i></div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Total Target MongoDB</h4></div>
                                <div class="card-body">
                                    {{ $summary['synced_total'] ?? 0 }} / {{ $summary['target_total'] ?? 0 }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>Progress Sinkronisasi per Penugasan</h4>
                    </div>
                    <div class="card-body">
                        @if ($progressRows->isEmpty())
                            <div class="empty-state" data-height="220">
                                <div class="empty-state-icon bg-info"><i class="fas fa-database"></i></div>
                                <h2>Belum ada penugasan aktif</h2>
                                <p class="lead">Progress sinkronisasi MongoDB akan tampil setelah penugasan aktif tersedia.</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="table-mongodb-progress">
                                    <thead>
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th>Penugasan</th>
                                            <th>Status Distribusi</th>
                                            <th>Progress MongoDB</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($progressRows as $row)
                                            @php
                                                $assignment = $row['assignment'];
                                                $progress = $row['progress'];
                                                $percent = (int) ($progress['percent'] ?? 0);
                                            @endphp
                                            <tr>
                                                <td class="text-center">{{ $loop->iteration }}</td>
                                                <td>
                                                    <small class="text-muted">{{ $assignment->kode_penugasan }}</small>
                                                    <div class="font-weight-bold">{{ $assignment->judul_penugasan }}</div>
                                                </td>
                                                <td>
                                                    <span class="badge badge-{{ $assignment->status_distribusi === 'selesai' ? 'success' : 'warning' }}">
                                                        {{ ucfirst($assignment->status_distribusi ?: 'draft') }}
                                                    </span>
                                                    <small class="d-block text-muted mt-1">
                                                        {{ $assignment->total_ditugaskan }}/{{ $assignment->total_target }} target tersimpan di SQL
                                                    </small>
                                                </td>
                                                <td style="min-width: 240px;">
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>{{ $progress['synced'] ?? 0 }}/{{ $progress['total'] ?? 0 }} target</span>
                                                        <strong>{{ $percent }}%</strong>
                                                    </div>
                                                    <div class="progress" style="height: 10px;">
                                                        <div class="progress-bar bg-{{ $progress['complete'] ? 'success' : 'info' }}"
                                                            role="progressbar" style="width: {{ $percent }}%;"
                                                            aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('assessment.assignment.show', $assignment->id) }}"
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
    <script>
        $(document).ready(function() {
            const table = $('#table-mongodb-progress');

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

            @if ($mongoAvailable && $hasPendingSync)
                window.setTimeout(() => window.location.reload(), 5000);
            @endif
        });
    </script>
@endpush
