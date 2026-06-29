@extends('layouts.app', ['title' => 'Detail Penugasan Assessment'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('library/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/datatables.net-select-bs4/css/select.bootstrap4.min.css') }}">
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
        $totalAssessments = $assessments->count();
        $totalForms = $assessments->sum(fn($assessment) => $assessment->forms->count());
        $totalFields = $assessments->sum(
            fn($assessment) => $assessment->forms->sum(fn($form) => $form->fields->count()),
        );
    @endphp

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Detail Penugasan Assessment</h1>
                <div class="section-header-breadcrumb">
                    <a href="{{ route('assessment.assignment.index') }}" class="btn btn-light mr-2">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <a href="{{ route('assessment.assignment.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Buat Penugasan Baru
                    </a>
                </div>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-primary">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Target</h4>
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
                                {{ $assignment->kapasitas_per_sesi }} guru per sesi / {{ $assignment->durasi_sesi_jam }}
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
                            <div class="text-muted small">Form Assessment Dipilih</div>
                            <div>{{ $totalAssessments }} assessment</div>
                            <small class="text-muted">
                                {{ $totalForms }} form / {{ $totalFields }} pertanyaan
                            </small>
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
                                                <td>{{ $assessment->judul }}</td>
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
                                            <th>Alokasi Guru</th>
                                            <th>Durasi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($assignment->sessions as $session)
                                            <tr>
                                                <td class="text-center">{{ $session->nomor_sesi }}</td>
                                                <td class="font-weight-bold">{{ $session->label_sesi }}</td>
                                                <td>{{ $session->jadwal_sesi_label ?: 'Jadwal belum diatur' }}</td>
                                                <td>{{ $session->kapasitas_peserta }} guru</td>
                                                <td>{{ $session->total_peserta }} guru</td>
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
                        <h4>Daftar Guru Yang Ditugasi</h4>
                    </div>
                    <div class="card-body">
                        @if ($assignment->targets->isEmpty())
                            <div class="alert alert-warning mb-0">
                                Penugasan ini masih diproses atau belum memiliki target guru tersimpan.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped" id="table-assignment-target">
                                    <thead>
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th>Nama Guru</th>
                                            <th>Instansi</th>
                                            <th>Kabupaten</th>
                                            <th>Sesi Assessment</th>
                                            <th>Status Target</th>
                                            <th>Waktu Ditugaskan</th>
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
                                            @endphp
                                            <tr>
                                                <td class="text-center">{{ $loop->iteration }}</td>
                                                <td>
                                                    <div class="font-weight-bold">
                                                        {{ optional($target->guru)->nama_lengkap ?: 'Guru tidak ditemukan' }}
                                                    </div>
                                                    <small class="text-muted">
                                                        {{ optional($target->guru)->email ?: '-' }}
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
@endsection

@push('scripts')
    <script src="{{ asset('library/datatables/media/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('library/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('library/datatables.net-select-bs4/js/select.bootstrap4.min.js') }}"></script>

    <script>
        $(document).ready(function() {
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

            initDataTable('#table-assignment-assessment', [0]);
            initDataTable('#table-assignment-session', [0]);
            initDataTable('#table-assignment-target', [0, 7]);
        });
    </script>
@endpush
