@extends('layouts.app', ['title' => 'Tugas Validasi Assessment'])

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Tugas Validasi Assessment</h1>
            </div>
            <div class="section-body">
                @if (session('validator_success'))
                    <div class="alert alert-success">{{ session('validator_success') }}</div>
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-warning"><i class="fas fa-hourglass-half"></i></div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Perlu Dikerjakan</h4></div>
                                <div class="card-body">{{ $pendingCount }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-success"><i class="fas fa-check-double"></i></div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Selesai</h4></div>
                                <div class="card-body">{{ $submittedCount }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h4>Daftar Penugasan Quality Assurance</h4></div>
                    <div class="card-body">
                        @if ($assignments->isEmpty())
                            <div class="empty-state" data-height="300">
                                <div class="empty-state-icon bg-light text-primary"><i class="fas fa-clipboard-check"></i></div>
                                <h2>Belum ada tugas validasi</h2>
                                <p class="lead">Penugasan dari admin akan muncul pada halaman ini.</p>
                            </div>
                        @else
                            <div class="row">
                                @foreach ($assignments as $assignment)
                                    @php
                                        $isOverdue = $assignment->due_date
                                            && $assignment->due_date->copy()->endOfDay()->isPast()
                                            && $assignment->status !== 'submitted';
                                        $isUpcoming = $assignment->start_date && $assignment->start_date->isFuture();
                                    @endphp
                                    <div class="col-lg-6">
                                        <div class="card border">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between">
                                                    <small class="text-muted">{{ $assignment->code }}</small>
                                                    <span class="badge badge-{{ $assignment->status_badge_class }}">
                                                        {{ $assignment->status_label }}
                                                    </span>
                                                </div>
                                                <h5 class="mt-2">{{ $assignment->title }}</h5>
                                                <p class="mb-1">
                                                    <strong>Penugasan assessment:</strong>
                                                    {{ $assignment->assessment_assignments_label }}
                                                </p>
                                                <p class="text-muted">{{ $assignment->validatorForm?->title }}</p>
                                                <div class="mb-3">
                                                    @if ($isOverdue)
                                                        <span class="badge badge-danger">Melewati batas waktu</span>
                                                    @elseif ($isUpcoming)
                                                        <span class="badge badge-info">Mulai {{ $assignment->start_date->format('d-m-Y') }}</span>
                                                    @elseif ($assignment->due_date)
                                                        <span class="badge badge-light">Batas {{ $assignment->due_date->format('d-m-Y') }}</span>
                                                    @endif
                                                </div>
                                                <a href="{{ route('assessment.validator.task.show', $assignment) }}"
                                                    class="btn btn-{{ $assignment->status === 'submitted' ? 'outline-success' : 'primary' }}">
                                                    <i class="fas fa-{{ $assignment->status === 'submitted' ? 'eye' : 'edit' }}"></i>
                                                    {{ $assignment->status === 'submitted' ? 'Lihat Hasil' : 'Kerjakan Validasi' }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="d-flex justify-content-end mt-3">
                                {{ $assignments->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
