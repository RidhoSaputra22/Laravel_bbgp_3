@extends('layouts.app', ['title' => 'Panel Kombinasi Soal'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('library/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/datatables.net-select-bs4/css/select.bootstrap4.min.css') }}">
@endpush

@section('content')
    @php
        $activeCount = $datas->where('is_active', true)->count();
        $totalQuestions = $datas->sum('total_questions');
        $usedAssignments = $datas->sum('assignments_count');
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
                                <i class="fas fa-random"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Kombinasi</h4>
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
                                    <h4>Kombinasi Aktif</h4>
                                </div>
                                <div class="card-body">
                                    {{ $activeCount }}
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
                                    <h4>Total Soal Tersimpan</h4>
                                </div>
                                <div class="card-body">
                                    {{ $totalQuestions }}
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
                                    <h4>Dipakai Penugasan</h4>
                                </div>
                                <div class="card-body">
                                    {{ $usedAssignments }}
                                </div>
                            </div>
                        </div>
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
                                            <th>Judul</th>
                                            <th>Struktur</th>
                                            <th>Dibuat</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($datas as $data)
                                            <tr>
                                                <td class="text-center">{{ $loop->iteration }}</td>
                                                <td>
                                                    <div class="font-weight-bold">{{ $data->kode_kombinasi }}</div>
                                                    <small class="text-muted">
                                                        {{ $data->is_active ? 'Aktif' : 'Nonaktif' }}
                                                    </small>
                                                </td>
                                                <td>
                                                    <div class="font-weight-bold">{{ $data->judul }}</div>
                                                    @if ($data->target_ketenagaan_label)
                                                        <small class="d-inline-block mb-1">
                                                            <span class="badge badge-{{ $data->target_ketenagaan_badge_class }}">
                                                                {{ $data->target_ketenagaan_label }}
                                                            </span>
                                                        </small>
                                                        <br>
                                                    @endif
                                                    <small class="text-muted">
                                                        {{ \Illuminate\Support\Str::limit($data->deskripsi ?: 'Tanpa deskripsi tambahan.', 90) }}
                                                    </small>
                                                </td>
                                                <td>
                                                    <div>{{ $data->total_assessments }} assessment sumber</div>
                                                    <div>{{ $data->total_forms }} form</div>
                                                    <div class="text-muted">{{ $data->total_questions }} child soal</div>
                                                    <small class="text-muted">
                                                        {{ $data->items_count }} item tersimpan / {{ $data->assignments_count }} penugasan
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
                                                    @if ($data->assignments_count < 1)
                                                        <form action="{{ route('assessment.combination.hapus', $data->id) }}"
                                                            method="POST" class="d-inline-block my-1"
                                                            onsubmit="return confirm('Hapus kombinasi soal {{ $data->judul }}?')">
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

            if (!table.length) {
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
        });
    </script>
@endpush
