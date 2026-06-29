@extends('layouts.app', ['title' => 'Data Sekolah'])

@section('content')
    @push('styles')
        <link rel="stylesheet" href="{{ asset('library/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('library/datatables.net-select-bs4/css/select.bootstrap4.min.css') }}">
    @endpush

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Data Sekolah</h1>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                {{-- <a href="#" class="btn btn-primary text-white my-3">
                                    <i class="fas fa-plus mr-2"></i>Tambah Sekolah
                                </a> --}}
                                <!-- Filter Section -->


                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <select class="form-control" id="filterProvinsi">
                                            <option value="">-- Semua Provinsi --</option>
                                            @foreach ($provinsiList as $prov)
                                                <option value="{{ $prov }}">{{ $prov }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-control" id="filterStatus">
                                            <option value="">-- Semua Status --</option>
                                            <option value="Negeri">Negeri</option>
                                            <option value="Swasta">Swasta</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-control" id="filterAkreditasi">
                                            <option value="">-- Semua Akreditasi --</option>
                                            <option value="A">A</option>
                                            <option value="B">B</option>
                                            <option value="C">C</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        {{-- <button type="button" class="btn btn-success" id="btnExportFiltered">
                                        <i class="fas fa-file-excel mr-2"></i>Export Data (Filter)
                                    </button> --}}
                                        <a href="{{ route('admin.data-sekolah.export') }}" class="btn btn-info">
                                            <i class="fas fa-download mr-2"></i>Export Data
                                        </a>
                                    </div>
                                </div>


                                <div class="table-responsive">
                                    <table class="table table-striped" id="table-sekolah">
                                        <thead>
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th>Logo</th>
                                                <th>Nama Sekolah</th>
                                                <th>NPSN</th>
                                                <th>Status</th>
                                                <th>Provinsi</th>
                                                <th>Kabupaten</th>
                                                <th>Akreditasi</th>
                                                <th>Kepala Sekolah</th>
                                                <th>Jumlah Siswa</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach ($datas as $i => $sekolah)
                                                <tr>
                                                    <td class="text-center">{{ ++$i }}</td>
                                                    <td>
                                                        @if ($sekolah->logo_sekolah)
                                                            <img src="{{ asset('upload/sekolah/logo_sekolah/' . $sekolah->logo_sekolah) }}"
                                                                alt="Logo" class="rounded" width="40">
                                                        @else
                                                            <div class="bg-secondary rounded d-flex align-items-center justify-content-center"
                                                                style="width: 40px; height: 40px;">
                                                                <i class="fas fa-school text-white"></i>
                                                            </div>
                                                        @endif
                                                    </td>

                                                    <td>
                                                        <strong>{{ $sekolah->nama_sekolah }}</strong><br>
                                                        <small class="text-muted">{{ $sekolah->bp_sekolah }}</small>
                                                    </td>
                                                    <td>{{ $sekolah->npsn_sekolah }}</td>
                                                    <td>
                                                        @if ($sekolah->status_sekolah == 'Negeri')
                                                            <span class="badge badge-primary">Negeri</span>
                                                        @else
                                                            <span class="badge badge-info">Swasta</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $sekolah->provinsi }}</td>
                                                    <td>{{ $sekolah->kabupaten }}</td>
                                                    <td>
                                                        @if ($sekolah->akreditasi != '-')
                                                            <span
                                                                class="badge badge-{{ $sekolah->akreditasi == 'A' ? 'success' : ($sekolah->akreditasi == 'B' ? 'warning' : 'secondary') }}">
                                                                {{ $sekolah->akreditasi }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $sekolah->nama_kepsek }}</td>
                                                    <td>
                                                        <span class="badge badge-light">{{ $sekolah->jumlah_siswa }}
                                                            Siswa</span>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex">
                                                            {{-- <a href="#" class="btn btn-sm btn-info" title="Detail">
                                                                <i class="fas fa-eye"></i>
                                                            </a> --}}
                                                            <a href="{{ route('admin.data-sekolah.edit', $sekolah->id) }}"
                                                                class="btn btn-sm btn-warning mx-1" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            {{-- <button onclick="deleteData({{ $sekolah->id }}, 'sekolah')"
                                                                class="btn btn-sm btn-danger" title="Hapus">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button> --}}
                                                        </div>
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

        <script>
            $(document).ready(function() {
                var table = $('#table-sekolah').DataTable({
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
                    }
                });

                // Filter Provinsi
                $('#filterProvinsi').on('change', function() {
                    table.column(5).search(this.value).draw();
                });

                // Filter Status
                $('#filterStatus').on('change', function() {
                    table.column(4).search(this.value).draw();
                });

                // Filter Akreditasi
                $('#filterAkreditasi').on('change', function() {
                    table.column(7).search(this.value).draw();
                });
            });

            function deleteData(id, type) {
                if (confirm('Apakah Anda yakin ingin menghapus data sekolah ini?')) {
                    $.ajax({
                        url: `/admin/${type}/${id}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            alert('Data berhasil dihapus');
                            location.reload();
                        },
                        error: function(xhr) {
                            alert('Terjadi kesalahan saat menghapus data');
                        }
                    });
                }
            }

            // Export dengan filter
            $('#btnExportFiltered').on('click', function() {
                const provinsi = $('#filterProvinsi').val();
                const status = $('#filterStatus').val();
                const akreditasi = $('#filterAkreditasi').val();

                let url = '{{ route('admin.data-sekolah.export') }}?';
                const params = [];

                if (provinsi) params.push(`provinsi=${encodeURIComponent(provinsi)}`);
                if (status) params.push(`status_sekolah=${encodeURIComponent(status)}`);
                if (akreditasi) params.push(`akreditasi=${encodeURIComponent(akreditasi)}`);

                url += params.join('&');

                // Download file
                window.location.href = url;
            });
        </script>
    @endpush
@endsection
