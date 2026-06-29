@extends('layouts.app', ['title' => 'Data Penyewaan Ruangan'])

@section('content')
    @push('styles')
        <link rel="stylesheet" href="{{ asset('library/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('library/datatables.net-select-bs4/css/select.bootstrap4.min.css') }}">
        <style>
            .table-internal {
                display: none;
            }

            .badge-tipe {
                padding: 6px 12px;
                font-size: 12px;
                font-weight: 600;
            }
        </style>
    @endpush

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Data Penyewaan Ruangan BBGTK</h1>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <!-- Navigation Buttons -->
                                <a href="{{ route('penyewaan.create') }}" class="btn btn-primary text-white my-3">
                                    <i class="fas fa-plus mr-2"></i>Tambah Ruangan
                                </a>

                                <!-- Tables Section -->
                                <div class="table-responsive">
                                    <table class="table table-striped" id="table-penyewaan">
                                        <thead>
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th>Foto</th>
                                                <th>Tipe Ruangan</th>
                                                <th>Nama Ruangan</th>
                                                <th>Harga per Malam</th>
                                                <th>Rincian</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($datas as $i => $data)
                                                <tr>
                                                    <td class="text-center">{{ ++$i }}</td>
                                                    <td>
                                                        @if ($data->foto_utama)
                                                            <img class="img img-fluid rounded" width="120"
                                                                src="{{ asset('upload/penyewaan/' . $data->foto_utama) }}"
                                                                alt="Foto Ruangan">
                                                        @else
                                                            <div class="bg-secondary rounded d-flex align-items-center justify-content-center"
                                                                style="width: 120px; height: 80px;">
                                                                <i class="fas fa-image fa-2x text-white"></i>
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($data->tipe_ruangan == 'asrama')
                                                            <span class="badge badge-tipe badge-primary">
                                                                <i class="fas fa-bed mr-1"></i>Asrama
                                                            </span>
                                                        @elseif($data->tipe_ruangan == 'aula')
                                                            <span class="badge badge-tipe badge-info">
                                                                <i class="fas fa-building mr-1"></i>Aula
                                                            </span>
                                                        @elseif($data->tipe_ruangan == 'kelas')
                                                            <span class="badge badge-tipe badge-success">
                                                                <i class="fas fa-chalkboard mr-1"></i>Kelas
                                                            </span>
                                                        @else
                                                            <span class="badge badge-tipe badge-warning">
                                                                <i class="fas fa-flask mr-1"></i>Laboratorium
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td><strong>{{ $data->nama_ruangan ?? '-' }}</strong></td>
                                                    <td>
                                                        @if ($data->harga_per_malam)
                                                            <span class="text-primary font-weight-bold">
                                                                Rp {{ number_format($data->harga_per_malam ?? 0, 0, ',', '.') }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    {{-- <td>{!! Str::limit($data->rincian_harga ?? '-', 50) !!}</td> --}}
                                                    <td>
                                                        @if ($data->tipe_ruangan == 'asrama')
                                                            {!! $data->rincian_harga !!}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($data->status == 'tersedia')
                                                            <span class="badge badge-success">
                                                                <i class="fas fa-check-circle mr-1"></i>Tersedia
                                                            </span>
                                                        @elseif($data->status == 'maintenance')
                                                            <span class="badge badge-warning">
                                                                <i class="fas fa-tools mr-1"></i>Maintenance
                                                            </span>
                                                        @else
                                                            <span class="badge badge-danger">
                                                                <i class="fas fa-times-circle mr-1"></i>Tidak Tersedia
                                                            </span>
                                                        @endif

                                                        @if (!$data->is_active)
                                                            <br><span class="badge badge-secondary mt-1">Non-Aktif</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('penyewaan.show', $data->id) }}"
                                                            class="btn btn-sm btn-info my-1" title="Lihat Detail">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('penyewaan.edit', $data->id) }}"
                                                            class="btn btn-sm btn-warning my-1" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button onclick="deleteData({{ $data->id }}, 'penyewaan')"
                                                            class="btn btn-sm btn-danger my-1" title="Hapus">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    @push('scripts')
        <script src="{{ asset('library/datatables/media/js/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('library/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('library/datatables.net-select-bs4/js/select.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('js/page/modules-datatables.js') }}"></script>

        <script type="text/javascript">
            $(document).ready(function() {
                var tablePenyewaan = $('#table-penyewaan').DataTable({
                    paging: true,
                    searching: true,
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/2.1.0/i18n/id.json',
                        "sSearch": "Pencarian Data Ruangan : ",
                    },
                    order: [
                        [0, 'asc']
                    ]
                });
            });

        </script>
    @endpush
@endsection
