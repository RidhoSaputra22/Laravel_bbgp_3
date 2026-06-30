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
        $combination = $assignment->combination;
        $totalAssessments = $combination?->total_assessments ?: $assessments->count();
        $totalForms = $combination?->total_forms ?: $assessments->sum(fn($assessment) => $assessment->forms->count());
        $totalFields = $combination?->total_questions ?: $assessments->sum(
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
                                                $completionBadge = $completionMode === 'timeout' ? 'secondary' : 'success';
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
                                                    @if ($completionMode)
                                                        <span class="badge badge-{{ $completionBadge }}">
                                                            {{ $completionMode === 'timeout' ? 'Timeout' : 'Manual' }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">-</span>
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
            initDataTable('#table-assignment-target', [0, 11]);
        });
    </script>
@endpush
