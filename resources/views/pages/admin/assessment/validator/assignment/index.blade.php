@extends('layouts.app', ['title' => 'Penugasan Validator'])

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Penugasan Validator</h1>
                <div class="section-header-breadcrumb">
                    <a href="{{ route('assessment.validator.index') }}" class="btn btn-light mr-2">
                        <i class="fas fa-arrow-left"></i> Panel
                    </a>
                    <a href="{{ route('assessment.validator.assignment.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Buat Penugasan
                    </a>
                </div>
            </div>
            <div class="section-body">
                @if (session('validator_success'))
                    <div class="alert alert-success">{{ session('validator_success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                <div class="alert alert-light border">
                    Saat penugasan Quality Assurance dibuat, setiap validator otomatis didaftarkan sebagai
                    peserta pada penugasan assessment aktif yang terkait. Pengelolaan peserta assessment tetap
                    tersedia melalui menu <strong>Assessment &gt; Penugasan</strong>.
                </div>

                <div class="card">
                    <div class="card-header"><h4>Daftar Penugasan QA</h4></div>
                    <div class="card-body">
                        @if ($assignments->isEmpty())
                            <div class="empty-state" data-height="300">
                                <div class="empty-state-icon bg-info"><i class="fas fa-user-check"></i></div>
                                <h2>Belum ada penugasan validator</h2>
                                <p class="lead">Buat QA untuk seluruh penugasan aktif dan validator yang memenuhi persyaratan.</p>
                                <a href="{{ route('assessment.validator.assignment.create') }}" class="btn btn-primary mt-3">
                                    Buat Penugasan
                                </a>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Penugasan</th>
                                            <th>Penugasan Assessment & Form QA</th>
                                            <th>Validator</th>
                                            <th>Periode</th>
                                            <th>Status</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($assignments as $assignment)
                                            <tr>
                                                <td>{{ $assignments->firstItem() + $loop->index }}</td>
                                                <td>
                                                    <strong>{{ $assignment->title }}</strong>
                                                    <small class="d-block text-muted">{{ $assignment->code }}</small>
                                                </td>
                                                <td>
                                                    <span>{{ $assignment->assessment_assignments_label }}</span>
                                                    <small class="d-block text-muted">
                                                        {{ $assignment->assessment_assignments_total }} penugasan aktif
                                                    </small>
                                                    <small class="d-block text-muted">{{ $assignment->validatorForm?->title }}</small>
                                                </td>
                                                <td>
                                                    <strong>{{ $assignment->validator?->guru?->nama_lengkap ?? $assignment->validator?->name ?? data_get($assignment->validator_snapshot, 'name', '-') }}</strong>
                                                    <small class="d-block text-muted">
                                                        {{ $assignment->validator?->role ?? data_get($assignment->validator_snapshot, 'role') }} ·
                                                        {{ $assignment->validator?->guru?->jenis_jabatan ?? data_get($assignment->validator_snapshot, 'jenis_jabatan') }}
                                                    </small>
                                                </td>
                                                <td>
                                                    {{ $assignment->start_date?->format('d-m-Y') ?? '-' }}
                                                    s/d {{ $assignment->due_date?->format('d-m-Y') ?? '-' }}
                                                </td>
                                                <td>
                                                    <span class="badge badge-{{ $assignment->status_badge_class }}">
                                                        {{ $assignment->status_label }}
                                                    </span>
                                                    @if ($assignment->score_percentage !== null)
                                                        <small class="d-block mt-1">Nilai {{ number_format($assignment->score_percentage, 2) }}%</small>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('assessment.validator.assignment.show', $assignment) }}"
                                                        class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>
                                                    @if ($assignment->status !== 'submitted')
                                                        <form method="POST"
                                                            action="{{ route('assessment.validator.assignment.destroy', $assignment) }}"
                                                            class="d-inline"
                                                            onsubmit="return confirm('Hapus penugasan validator ini?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3 d-flex justify-content-end">
                                {{ $assignments->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
