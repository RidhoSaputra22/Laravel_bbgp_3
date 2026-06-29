@extends('layouts.app', ['title' => 'Data Internal'])

@section('content')
    @push('styles')
        <link rel="stylesheet" href="{{ asset('library/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('library/datatables.net-select-bs4/css/select.bootstrap4.min.css') }}">
        <style>
            .table-internal {
                display: none;
            }
        </style>
    @endpush

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Data Internal BBGTK</h1>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <!-- Navigation Buttons -->
                                <div class="row mb-3">
                                    <div class="col-10">
                                        <h4 id="title-text">Data Penugasan PPNPN : <b> {{ $datas['getNama']->nama ?? '' }}
                                            </b></h4>

                                        {{-- <div class="d-flex mt-3 mb-5">
                                            <div class="row mx-2">
                                                <div class="">
                                                    <a href="#" id="pegawaiBBGP" class="btn btn-warning btn-lg p-2">
                                                        <i class="fas fa-layer-group mr-1"></i>Penugasan Pegawai BBGP
                                                    </a>
                                                </div>
                                                <div class="">
                                                    <a href="#" id="pegawaiPpnp"
                                                        class="btn btn-success btn-lg mx-3 p-2">
                                                        <i class="fas fa-layer-group mr-1"></i>Penugasan Pegawai PPNPN
                                                    </a>
                                                </div>
                                            </div>
                                        </div> --}}
                                    </div>
                                    <div class="col text-right">
                                        <a href="{{ route('internal.index') }}" class="btn btn-warning">Kembali </a>

                                    </div>
                                </div>

                                <!-- Filter Section -->
                                {{-- <h5>Pencarian Data Internal BBGP</h5>
                                <div class="row mb-2">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <input name="nama" id="namaFilter" type="text"
                                                placeholder="Masukkan nama anda" class="form-control">
                                        </div>
                                    </div>
                                </div> --}}

                                <!-- Filter Data Internal -->
                                {{-- <h5>Filter Data Internal</h5>
                                <div class="row">
                                    <div class="col-md-4 mb-4">
                                        <label>Rekapan Data</label>
                                        <select required name="rekapan" class="form-control select2" id="rekapan">
                                            <option value="">-- Filter By Rekapan Data --</option>
                                            <option value="Penugasan Pegawai">Penugasan Pegawai</option>
                                            <option value="Penugasan PPNPN">Penugasan PPNPN</option>
                                            <option value="Pendamping Lokakarya">Pendamping Lokakarya</option>
                                        </select>
                                    </div>
                                </div> --}}

                                <!-- Tables Section -->
                                {{-- @if ($datas['getJenisPpnpn']->jenis == 'Penugasan PPNPN')
                                    <div class="table-responsive " id="table-internal-ppnpn">
                                        <!-- Table PPNPN -->
                                        <table class="table table-striped" id="table-ppnpn">
                                            <thead>
                                                <tr>
                                                    <th class="text-center">#</th>
                                                    <th>Nama</th>
                                                    <th>Jabatan</th>
                                                    <th>Penugasan</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($datas['penugasanPpnpn'] as $i => $data)
                                                    <tr data-type="ppnpn">
                                                        <td>{{ ++$i }}</td>
                                                        <td>{{ $data->nama ?? '' }}</td>
                                                        <td>{{ $data->jabatan ?? '' }}</td>

                                                        <td>
                                                            <a href="{{ route('internal.create.ppnp', $data->id) }}"
                                                                class="btn btn-primary my-2">Penugasan PPNPN</a>
                                                            <a href="{{ route('internal.create.lokakarya', $data->id) }}"
                                                                class="btn btn-info my-2">Penugasan Lokakarya</a>
                                                        </td>

                                                        <td>
                                                            <a href="{{ route('internal.edit', $data->id) }}"
                                                                class="btn btn-warning my-2"><i class="fas fa-edit"></i></a>

                                                            <button onclick="deleteData({{ $data->id }}, 'ppnpn')"
                                                                class="btn btn-danger">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                        </td>

                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else 
                                @endif
                                --}}

                                <div class="table-responsive " id="table-internal-bbgp">
                                    <!-- Table BBGP -->
                                    <table class="table table-striped" id="table-bbgp">
                                        <thead>
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th>Nama</th>
                                                <th>Jabatan</th>
                                                <th>Kegiatan</th>
                                                <th>Tempat Kegiatan</th>
                                                <th>Tanggal Kegiatan</th>
                                                <th>Jam Kegiatan</th>
                                                <th>Keterangan</th>
                                                {{-- <th>Penugasan</th> --}}
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {{-- {{ dd($datas['penugasanPpnpn']) }} --}}
                                            @foreach ($datas['penugasanPpnpn'] as $i => $data)
                                                <tr data-type="bbgp">
                                                    {{-- {{dd($data)}} --}}
                                                    <td>{{ ++$i }}</td>
                                                    <td>{{ $data->nama }} </td>
                                                    <td>{{ $data->jabatan }}</td>
                                                    <td>{{ $data->kegiatan }}</td>
                                                    <td>{{ $data->tempat }}</td>
                                                    <td>{{ $data->tgl_kegiatan }} - {{ $data->tgl_selesai_kegiatan }}</td>
                                                    <td>{{ $data->jam_mulai }} - {{ $data->jam_selesai }} WITA</td>
                                                    <td>{!! $data->deskripsi !!}</td>
                                                    {{-- <td>
                                                        <a href="{{ route('internal.index.pegawai', $data->nik) }}"
                                                            class="btn btn-primary mb-2">Lihat Penugasan</a>

                                                        <a href="{{ route('internal.create.pegawai', $data->id) }}"
                                                            class="btn btn-primary mb-2">Penugasan Pegawai</a>

                                                        <a href="{{ route('internal.create.lokakarya', $data->id) }}"
                                                            class="btn btn-info mb-2">Pendamping Lokakarya</a>
                                                    </td> --}}
                                                    <td>
                                                        {{-- <a href="#"
                                                            class="btn btn-info my-2"><i class="fas fa-info"></i></a> --}}

                                                        {{-- <a href="{{ route('internal.edit.ppnpn', $data->id) }}"
                                                            class="btn btn-warning my-2"><i class="fas fa-edit"></i></a> --}}

                                                        <a href="{{ route('internal.edit.ppnpn', $data->id) }}"
                                                            class="btn btn-warning my-2"><i class="fas fa-edit"></i></a>


                                                        <button onclick="deleteDataPpnpn({{ $data->id }}, 'internal')"
                                                            class="btn btn-danger">
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

                var language = {
                    "sSearch": "Pencarian Data Internal BBGP : ",
                };
                // Initialize DataTables for both tables
                var tablePpnpn = $('#table-ppnpn').DataTable({
                    paging: true,
                    searching: true,
                    language: language,
                    // Add more DataTable options as needed
                });

                var tableBbgp = $('#table-bbgp').DataTable({
                    paging: true,
                    searching: true,
                    language: language,

                    // Add more DataTable options as needed
                });

                // Initially hide both tables
                $('.table-internal').hide();

                // Show appropriate table based on button click
                $('#pegawaiBBGP').on('click', function(event) {
                    event.preventDefault();
                    $('.table-internal').hide();
                    $('#table-internal-bbgp').show();
                    tableBbgp.columns.adjust().draw(); // Adjust column widths on table show
                    $('#title-text').text('Data Penugasan Pegawai')

                });

                $('#pegawaiPpnp').on('click', function(event) {
                    event.preventDefault();
                    $('.table-internal').hide();
                    $('#table-internal-ppnpn').show();
                    tablePpnpn.columns.adjust().draw(); // Adjust column widths on table show
                    $('#title-text').text('Data Penugasan PPNPN')

                });

                // Filter tables based on dropdown selection
                $('#rekapan').on('change', function() {
                    let jenis = $(this).val().toLowerCase().replace(/ /g, '-');
                    console.log(jenis);
                    // Hide all tables initially
                    $('.table-internal').hide();

                    // Show the appropriate table based on the selection
                    if (jenis === 'penugasan-pegawai') {
                        $('#table-internal-bbgp').show();
                        tableBbgp.columns.adjust().draw(); // Adjust column widths on table show
                    } else if (jenis === 'penugasan-ppnpn') {
                        $('#table-internal-ppnpn').show();
                        tablePpnpn.columns.adjust().draw(); // Adjust column widths on table show
                    }
                });

                // Filter by Nama
                $('#namaFilter').on('keyup', function() {
                    tablePpnpn.column(1).search(this.value).draw();
                    tablePpnpn.column(2).search(this.value).draw();
                    tableBbgp.column(1).search(this.value).draw();
                    tableBbgp.column(2).search(this.value).draw();
                    tableBbgp.column(3).search(this.value).draw();
                });

                // Reset button functionality
                $('#resetBtn').on('click', function() {
                    $('#rekapan').val('');
                    $('#namaFilter').val('');
                    $('.table-internal').hide();
                    tablePpnpn.search('').columns().search('').draw();
                    tableBbgp.search('').columns().search('').draw();
                });
            });
        </script>

        <script>
            // swal btn hps data
            const deleteDataPpnpn = (id, tabel) => {
                console.log(id, tabel);
                let token = $("meta[name='csrf-token']").attr("content");

                swal({
                    title: "Apakah anda yakin?",
                    text: "Data yang dihapus tidak dapat dikembalikan!",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        $.ajax({
                            headers: {
                                "X-CSRF-TOKEN": token,
                            },
                            type: "POST",
                            url: `{{ route('internal.hapus.ppnpn', ['tabel' => 'PLACEHOLDER_TABEL', 'id' => 'PLACEHOLDER_ID']) }}`
                                .replace('PLACEHOLDER_TABEL', tabel)
                                .replace('PLACEHOLDER_ID', id),
                            success: function(response) {
                                console.log(response);
                                if (response.success) {
                                    swal("Terhapus", "Data telah dihapus", "success").then(() => {
                                        location.reload();
                                    });
                                } else {
                                    swal("Error", "Gagal menghapus data.", "error");
                                }
                            },
                            error: function(error) {
                                console.error("AJAX Error:", error);
                                swal("Error", "Terjadi kesalahan saat menghapus data.", "error");
                            },
                        });
                    }
                });
            };
        </script>
    @endpush
@endsection
