@extends('layouts.app', ['title' => 'Panel Validator Assessment'])

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Panel Validator Assessment</h1>
            </div>

            <div class="section-body">
                @if (session('validator_success'))
                    <div class="alert alert-success">{{ session('validator_success') }}</div>
                @endif

                <div class="alert alert-light border">
                    <strong>Modul Quality Assurance terpisah.</strong>
                    Form dan penugasan di halaman ini tidak memakai tabel pembuatan maupun penugasan peserta
                    assessment. Validator hanya menerima snapshot instrumen yang akan diperiksa.
                </div>

                <div class="row">
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-primary"><i class="fas fa-clipboard-check"></i></div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Form Validator Aktif</h4></div>
                                <div class="card-body">{{ $activeFormCount }} <small>/ {{ $formCount }}</small></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-warning"><i class="fas fa-user-clock"></i></div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Menunggu Validasi</h4></div>
                                <div class="card-body">{{ $pendingCount }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-success"><i class="fas fa-check-double"></i></div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Validasi Selesai</h4></div>
                                <div class="card-body">{{ $submittedCount }} <small>/ {{ $assignmentCount }}</small></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="card h-100">
                            <div class="card-body d-flex align-items-start">
                                <div class="rounded-circle bg-primary text-white p-4 mr-4">
                                    <i class="fas fa-file-signature fa-2x"></i>
                                </div>
                                <div>
                                    <h4>Buat Form Validator</h4>
                                    <p class="text-muted">
                                        Susun bagian, indikator QA, skala penilaian, dan pertanyaan catatan khusus
                                        validator.
                                    </p>
                                    <a href="{{ route('assessment.validator.form.create') }}" class="btn btn-primary mr-2">
                                        <i class="fas fa-plus"></i> Buat Form Validator
                                    </a>
                                    <a href="{{ route('assessment.validator.form.index') }}" class="btn btn-outline-primary">
                                        Kelola Form
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card h-100">
                            <div class="card-body d-flex align-items-start">
                                <div class="rounded-circle bg-info text-white p-4 mr-4">
                                    <i class="fas fa-user-check fa-2x"></i>
                                </div>
                                <div>
                                    <h4>Penugasan Validator</h4>
                                    <p class="text-muted">
                                        Tugaskan assessment kepada user dengan role Stakeholder dan jabatan Validator.
                                    </p>
                                    <a href="{{ route('assessment.validator.assignment.create') }}" class="btn btn-info mr-2">
                                        <i class="fas fa-paper-plane"></i> Buat Penugasan
                                    </a>
                                    <a href="{{ route('assessment.validator.assignment.index') }}" class="btn btn-outline-info">
                                        Kelola Penugasan
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h4>Penugasan Terbaru</h4>
                        <div class="card-header-action">
                            <a href="{{ route('assessment.validator.assignment.index') }}" class="btn btn-light">Lihat Semua</a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @if ($recentAssignments->isEmpty())
                            <div class="empty-state py-5">
                                <div class="empty-state-icon bg-light text-primary"><i class="fas fa-shield-alt"></i></div>
                                <h2>Belum ada penugasan validator</h2>
                                <p class="lead">Publikasikan form validator, lalu buat penugasan QA pertama.</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Kode</th>
                                            <th>Assessment</th>
                                            <th>Validator</th>
                                            <th>Status</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($recentAssignments as $assignment)
                                            <tr>
                                                <td>{{ $assignment->code }}</td>
                                                <td>{{ $assignment->assessment?->judul ?? data_get($assignment->assessment_snapshot, 'title', '-') }}</td>
                                                <td>{{ $assignment->validator?->guru?->nama_lengkap ?? $assignment->validator?->name ?? data_get($assignment->validator_snapshot, 'name', '-') }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $assignment->status_badge_class }}">
                                                        {{ $assignment->status_label }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('assessment.validator.assignment.show', $assignment) }}"
                                                        class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
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
