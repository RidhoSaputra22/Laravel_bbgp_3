@extends('layouts.app', ['title' => 'Data Penugasan Assessment'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('library/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/datatables.net-select-bs4/css/select.bootstrap4.min.css') }}">
@endpush

@section('content')
    @php
        $totalTargets = $datas->sum('total_target');
        $completedAssignments = $datas->where('status_distribusi', 'selesai')->count();
        $totalSessions = $datas->sum('total_sesi');
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

                <div class="row">
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-primary">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Penugasan</h4>
                                </div>
                                <div class="card-body">
                                    {{ $datas->count() }}
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
                                    <h4>Penugasan Selesai</h4>
                                </div>
                                <div class="card-body">
                                    {{ $completedAssignments }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-warning">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total User Ditugasi</h4>
                                </div>
                                <div class="card-body">
                                    {{ $totalTargets }}
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
                                    <h4>Total Sesi Assessment</h4>
                                </div>
                                <div class="card-body">
                                    {{ $totalSessions }}
                                </div>
                            </div>
                        </div>
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
                                            <th>Kode Penugasan</th>
                                            <th>Judul Penugasan</th>
                                            <th>Assessment</th>
                                            <th>Periode</th>
                                            <th>Distribusi</th>
                                            <th>Target & Sesi</th>
                                            <th>Dibuat Oleh</th>
                                            <th>Update</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($datas as $data)
                                            @php
                                                $monitoring = $monitoringByAssignmentId[$data->id] ?? null;
                                                $statusBadge = [
                                                    'draft' => 'secondary',
                                                    'diproses' => 'warning',
                                                    'selesai' => 'success',
                                                    'gagal' => 'danger',
                                                ][$data->status_distribusi] ?? 'secondary';
                                                $deliveryType = $data->job_batch_id ? 'Batch Job' : 'Langsung';
                                                $assessments = $data->assessments;
                                            @endphp
                                            <tr class="align-middle">
                                                <td class="text-center">{{ $loop->iteration }}</td>
                                                <td>
                                                    <div class="font-weight-bold">{{ $data->kode_penugasan }}</div>
                                                    <small class="text-muted">{{ $deliveryType }}</small>
                                                </td>
                                                <td>
                                                    <div class="font-weight-bold">{{ $data->judul_penugasan }}</div>
                                                    @if ($data->target_ketenagaan_label)
                                                        <small class="d-inline-block mb-1">
                                                            <span class="badge badge-{{ $data->target_ketenagaan_badge_class }}">
                                                                {{ $data->target_ketenagaan_label }}
                                                            </span>
                                                        </small>
                                                        <br>
                                                    @endif
                                                    <small class="text-muted">
                                                        {{ \Illuminate\Support\Str::limit($data->deskripsi, 80) ?: 'Tanpa deskripsi tambahan.' }}
                                                    </small>
                                                </td>
                                                <td>
                                                    <div class="font-weight-bold">{{ $assessments->count() }} Assessment ditugaskan</div>

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
                                                        s/d {{ $data->tanggal_selesai ? \App\Helpers\Helper::dateIndo($data->tanggal_selesai) : '-' }}
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
                                                        {{ $data->total_ditugaskan }}/{{ $data->total_target }} target tersimpan
                                                    </small>
                                                    @if (($monitoring['missing_target_total'] ?? 0) > 0)
                                                        <small class="d-block text-danger">
                                                            {{ $monitoring['missing_target_total'] }} target belum tersimpan
                                                        </small>
                                                    @endif
                                                    @if (($monitoring['batch']['found'] ?? false) && $data->job_batch_id)
                                                        <small class="d-block text-muted">
                                                            Job: {{ $monitoring['batch']['processed_jobs'] ?? 0 }}/{{ $monitoring['batch']['total_jobs'] ?? 0 }}
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
                                                        {{ $data->total_sesi }} sesi / {{ $data->kapasitas_per_sesi }} peserta per sesi
                                                    </small>
                                                    <br>
                                                    <small class="text-muted">
                                                        {{ $data->durasi_sesi_jam }} jam per sesi
                                                    </small>
                                                    <br>
                                                    <small class="text-muted">
                                                        Sesi awal {{ $data->jam_mulai_label ? $data->jam_mulai_label . ' WITA' : 'belum diatur' }}
                                                    </small>
                                                </td>
                                                <td>
                                                    <div>{{ optional($data->creator)->name ?: 'Sistem' }}</div>
                                                    <small class="text-muted">
                                                        {{ $data->job_batch_id ? \Illuminate\Support\Str::limit($data->job_batch_id, 18) : 'Tanpa batch id' }}
                                                    </small>
                                                </td>
                                                <td>
                                                    <div>{{ \App\Helpers\Helper::dateIndo($data->updated_at) }}</div>
                                                    <small class="text-muted">
                                                        {{ $data->updated_at->format('H:i') }}
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
                                                    <button type="button" class="btn btn-danger btn-sm my-1 js-assignment-delete-trigger"
                                                        data-toggle="modal" data-target="#assignmentDeleteModal"
                                                        data-route="{{ route('assessment.assignment.hapus', $data->id) }}"
                                                        data-code="{{ $data->kode_penugasan }}"
                                                        data-title="{{ $data->judul_penugasan }}"
                                                        data-target-total="{{ $data->total_target }}">
                                                        <i class="fas fa-trash mr-1"></i> Hapus
                                                    </button>
                                                    @if ($monitoring['retry_available'] ?? false)
                                                        <form action="{{ route('assessment.assignment.retry', $data->id) }}" method="POST"
                                                            class="d-inline-block my-1">
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
    <script src="{{ asset('library/datatables/media/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('library/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('library/datatables.net-select-bs4/js/select.bootstrap4.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            const table = $('#table-assignment-index');

            if (!table.length) {
                return;
            }

            table.DataTable({
                order: [],
                pageLength: 10,
                autoWidth: false,
                columnDefs: [{
                        targets: [0, 9],
                        orderable: false,
                        searchable: false,
                    },
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/2.1.0/i18n/id.json',
                },
            });

            $('.js-assignment-delete-trigger').on('click', function() {
                const trigger = $(this);

                $('#assignment-delete-form').attr('action', trigger.data('route'));
                $('#assignment-delete-title').text(trigger.data('title') || '-');
                $('#assignment-delete-code').text(trigger.data('code') || '-');
                $('#assignment-delete-target-total').text(trigger.data('target-total') || 0);
            });
        });
    </script>
@endpush
