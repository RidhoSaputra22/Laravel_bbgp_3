@extends('layouts.app', ['title' => 'Data Assessment'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('library/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/datatables.net-select-bs4/css/select.bootstrap4.min.css') }}">
@endpush

@section('content')
    @php
        $totalForms = $datas->sum(function ($assessment) {
            return $assessment->forms->count();
        });

        $totalFields = $datas->sum(function ($assessment) {
            return $assessment->forms->sum(function ($form) {
                return $form->fields->count();
            });
        });
    @endphp

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Data Assessment</h1>
                <div class="section-header-breadcrumb">
                    <a href="{{ route('assessment.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Assessment
                    </a>
                </div>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-primary">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Assessment</h4>
                                </div>
                                <div class="card-body">
                                    {{ $datas->count() }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-success">
                                <i class="fas fa-layer-group"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Form</h4>
                                </div>
                                <div class="card-body">
                                    {{ $totalForms }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-warning">
                                <i class="fas fa-sliders-h"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Pertanyaan</h4>
                                </div>
                                <div class="card-body">
                                    {{ $totalFields }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>Daftar Form Assessment Dinamis</h4>
                    </div>
                    <div class="card-body">
                        @if ($datas->isEmpty())
                            <div class="empty-state" data-height="320">
                                <div class="empty-state-icon bg-primary">
                                    <i class="fas fa-clipboard-list"></i>
                                </div>
                                <h2>Belum ada assessment</h2>
                                <p class="lead">
                                    Mulai buat assessment baru untuk menyusun form dan pertanyaandinamis.
                                </p>
                                <a href="{{ route('assessment.create') }}" class="btn btn-primary mt-3">
                                    Tambah Assessment
                                </a>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped" id="table-assessment-index">
                                    <thead>
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th>Kode</th>
                                            <th>Judul</th>
                                            <th>Status</th>
                                            <th>Struktur</th>
                                            <th>Update</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($datas as $data)
                                            <tr>
                                                <td class="text-center">{{ $loop->iteration }}</td>
                                                <td>
                                                    <div class="font-weight-bold">{{ $data->kode_assessment }}</div>
                                                    <small class="text-muted">{{ $data->slug }}</small>
                                                </td>
                                                <td>
                                                    <div class="font-weight-bold">{{ $data->judul }}</div>
                                                    @if ($data->target_ketenagaan_label)
                                                        <small class="d-inline-block mb-1">
                                                            <span
                                                                class="badge badge-{{ $data->target_ketenagaan_badge_class }}">
                                                                {{ $data->target_ketenagaan_label }}
                                                            </span>
                                                        </small>
                                                        <br>
                                                    @endif
                                                    <small
                                                        class="text-muted">{{ \Illuminate\Support\Str::limit($data->deskripsi, 80) }}</small>
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge badge-{{ $data->status == 'publish' ? 'success' : ($data->status == 'draft' ? 'warning' : 'secondary') }}">
                                                        {{ ucfirst($data->status) }}
                                                    </span>
                                                    @if ($data->is_active)
                                                        <span class="badge badge-primary">Aktif</span>
                                                    @else
                                                        <span class="badge badge-light">Nonaktif</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div>{{ $data->forms->count() }} form</div>
                                                    <div class="text-muted">
                                                        {{ $data->forms->sum(function ($form) { return $form->fields->count(); }) }}
                                                        pertanyaan
                                                    </div>
                                                </td>
                                                <td>{{ \App\Helpers\Helper::dateIndo($data->updated_at) }}</td>
                                                <td class="text-center">
                                                    <a href="{{ route('assessment.show', $data->id) }}"
                                                        class="btn btn-info btn-sm my-1">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('assessment.edit', $data->id) }}"
                                                        class="btn btn-warning btn-sm my-1">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button onclick="deleteData({{ $data->id }}, 'assessment')"
                                                        class="btn btn-danger btn-sm my-1">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
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
            const table = $('#table-assessment-index');

            if (!table.length) {
                return;
            }

            table.DataTable({
                order: [],
                pageLength: 10,
                autoWidth: false,
                columnDefs: [{
                        targets: [0, 6],
                        orderable: false,
                        searchable: false,
                    },
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/2.1.0/i18n/id.json',
                },
            });
        });
    </script>
@endpush
