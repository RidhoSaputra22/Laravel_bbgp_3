@extends('layouts.user.app', ['title' => 'Data Internal'])

@section('content')
    @push('styles')
    @endpush

    <div class="main-content ">
        <section class="section">
            <div class="section-header d-flex justify-content-between">
                <h1 class="text-primary"><u> Data Internal BBGTK Sulawesi Selatan</u> </h1>
                {{-- <div class=" mt-3">
                    <a href="{{ route('user.form_pegawai') }}" target="_blank" class="btn btn-primary"><i
                            class="fas fa-users mr-2"></i> Daftar
                        Internal BBGTK</a>
                </div> --}}

            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <!-- Navigation Buttons -->
                                <div class="row">
                                    <div class="col">
                                        {{-- <h4>Registrasi Data Internal</h4>
                                        <div class="d-flex mt-3 mb-5">
                                            <div class="">
                                                <a href="{{ route('internal.create', 'penugasan pegawai') }}" class="btn btn-primary btn-lg p-2">
                                                    <i class="fas fa-chalkboard-teacher mr-1"></i>Penugasan Pegawai
                                                </a>
                                            </div>
                                            <div class="mx-3">
                                                <a href="{{ route('internal.create', 'penugasan ppnpn') }}" class="btn btn-info btn-lg p-2">
                                                    <i class="fas fa-school mr-1"></i>Penugasan PPNPN
                                                </a>
                                            </div>
                                            <div class="">
                                                <a href="{{ route('internal.create', 'pendamping') }}" class="btn btn-warning btn-lg p-2">
                                                    <i class="fas fa-layer-group mr-1"></i>Pendamping Lokakarya
                                                </a>
                                            </div>
                                            <div class="">
                                                <button id="resetBtn" class="btn btn-success btn-lg mx-4">
                                                    <i class="fas fa-redo-alt"></i>
                                                </button>
                                            </div>
                                        </div> --}}
                                    </div>
                                </div>

                                <!-- Filter Section -->
                                <h5>Pencarian Data Internal BBGTK</h5>
                                <div class="row mb-2">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <input name="nama" id="namaFilter" type="text" placeholder="Masukkan nama anda" class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <!-- Filter Data Internal -->
                                <h5>Filter Data Internal</h5>
                                <div class="row">
                                    <div class="col-md-4 mb-4">
                                        <label>Rekapan Data</label>
                                        <select required name="rekapan" class="form-control" id="rekapan">
                                            <option value="">-- Filter By Rekapan Data --</option>
                                            <option value="Penugasan Pegawai">Penugasan Pegawai</option>
                                            <option value="Penugasan PPNPN">Penugasan PPNPN</option>
                                            <option value="Pendamping Lokakarya">Pendamping Lokakarya</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Pencarian Penugasan</label>
                                            <input placeholder="ketikkan pencarian kegiatan penugasan" id="penugasanFilter" type="text" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <label>Pencarian Pendamping</label>
                                        <input placeholder="ketikkan pencarian nama pendamping" id="pendampingFilter" type="text" class="form-control">
                                    </div>
                                </div>

                                <!-- Tables Section -->
                                <div class="table-responsive">

                                </div>

                                <div class="table-responsive  table-internal" id="table-internal-penugasan">
                                    <table class="table table-striped " id="table-penugasan">
                                        <thead>
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th>NIP</th>
                                                <th>Nama</th>
                                                <th>Jenis Penugasan</th>
                                                <th>Jabatan</th>
                                                <th>Kegiatan</th>
                                                <th>Tempat</th>
                                                <th>Tanggal Kegiatan</th>
                                                {{-- <th>Verifkasi</th>
                                                <th>Action</th> --}}
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($datas['dataPenugasanPegawai'] as $i => $data)
                                                <tr data-type="penugasan-pegawai">
                                                    <td>{{ ++$i }}</td>
                                                    <td>{{ $data->nip ?? ' - ' }}</td>
                                                    <td>{{ $data->nama ?? '' }}</td>
                                                    <td>{{ $data->jenis ?? '' }}</td>
                                                    <td>{{ $data->jabatan . ' - (Golongan : ' . $data->golongan . ')' ?? '' }}</td>
                                                    <td>{{ $data->kegiatan ?? '' }}</td>
                                                    <td>{{ $data->tempat ?? '' }}</td>
                                                    <td>{{ $data->tgl_kegiatan ?? '' }}</td>
                                                    {{-- <td>
                                                        @if ($data->is_verif == 'sudah')
                                                            <span class="badge badge-success">Sudah Verifikasi</span>
                                                        @else
                                                            <span class="badge badge-danger">Belum Verifikasi</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="#"
                                                                onclick="verifikasi({{ $data->id }}, 'internal', '{{ $data->is_verif }}')"
                                                                class="btn btn-primary mb-2">Verifikasi</a>
                                                        <a href="{{ route('internal.edit', $data->id) }} " class="btn btn-warning my-2"><i class="fas fa-edit"></i></a>
                                                        <button onclick="deleteData({{ $data->id }}, 'internal')" class="btn btn-danger">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </td> --}}
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="table-responsive  table-internal" id="table-internal-ppnpn">
                                    <table class="table table-striped" id="table-ppnpn">
                                        <thead>
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th>NIP</th>
                                                <th>Nama</th>
                                                <th>Jenis Penugasan</th>
                                                <th>Jabatan</th>
                                                {{-- <th>Kegiatan</th>
                                                <th>Tempat</th>
                                                <th>Tanggal Kegiatan</th> --}}
                                                {{-- <th>Verifkasi</th>
                                                <th>Action</th> --}}
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($datas['dataPenugasanPpnpn'] as $i => $data)
                                                <tr data-type="penugasan-ppnpn">
                                                    <td>{{ ++$i }}</td>
                                                    <td>{{ $data->nip ?? ' - ' }}</td>
                                                    <td>{{ $data->nama ?? '' }}</td>
                                                    <td>{{ $data->jenis ?? '' }}</td>
                                                    <td>{{ $data->jabatan . ' - (Golongan : ' . $data->golongan . ')' ?? '' }}</td>
                                                    {{-- <td>{{ $data->kegiatan ?? '' }}</td>
                                                    <td>{{ $data->tempat ?? '' }}</td>
                                                    <td>{{ $data->tgl_kegiatan ?? '' }}</td> --}}
                                                    {{-- <td>
                                                        @if ($data->is_verif == 'sudah')
                                                            <span class="badge badge-success">Sudah Verifikasi</span>
                                                        @else
                                                            <span class="badge badge-danger">Belum Verifikasi</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="#"
                                                                onclick="verifikasi({{ $data->id }}, 'internal', '{{ $data->is_verif }}')"
                                                                class="btn btn-primary mb-2">Verifikasi</a>
                                                        <a href="{{ route('internal.edit', $data->id) }} " class="btn btn-warning my-2"><i class="fas fa-edit"></i></a>
                                                        <button onclick="deleteData({{ $data->id }}, 'internal')" class="btn btn-danger">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </td> --}}
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="table-responsive  table-internal" id="table-internal-lokakarya">
                                    <table class="table table-striped" id="table-lokakarya">
                                        <thead>
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th>Nama</th>
                                                <th>Kabupaten/Kota</th>
                                                <th>Hotel</th>
                                                <th>Transport Pulang</th>
                                                <th>Transport Pergi</th>
                                                <th>Hari 1</th>
                                                <th>Hari 2</th>
                                                <th>Hari 3</th>
                                                {{-- <th>Verifkasi</th>

                                                <th>Action</th> --}}
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($dataPendamping as $i => $data)
                                                <tr data-type="pendamping">
                                                    <td>{{ ++$i }}</td>
                                                    <td>{{ $data->nama ?? '' }}</td>
                                                    <td>{{ $data->kota ?? '' }}</td>
                                                    <td>{{ $data->hotel ?? '' }}</td>
                                                    <td>Rp. {{ $data->transport_pulang ?? '' }}</td>
                                                    <td>Rp. {{ $data->transport_pergi ?? '' }}</td>
                                                    <td>Rp. {{ $data->hari_1 ?? '' }}</td>
                                                    <td>Rp. {{ $data->hari_2 ?? '' }}</td>
                                                    <td>Rp. {{ $data->hari_3 ?? '' }}</td>
                                                    {{-- <td>
                                                        @if ($data->is_verif == 'sudah')
                                                            <span class="badge badge-success">Sudah Verifikasi</span>
                                                        @else
                                                            <span class="badge badge-danger">Belum Verifikasi</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="#"
                                                                onclick="verifikasi({{ $data->id }}, 'pendamping', '{{ $data->is_verif }}')"
                                                                class="btn btn-primary mb-2">Verifikasi</a>
                                                        <a href="{{ route('internal.edit', $data->id) }} " class="btn btn-warning my-2"><i class="fas fa-edit"></i></a>
                                                        <button onclick="deleteData({{ $data->id }}, 'pendamping')" class="btn btn-danger">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </td> --}}
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
        <script src="{{ asset('library/gmaps/gmaps.min.js') }}"></script>
        <script src="{{ asset('js/page/gmaps-simple.js') }}"></script>

        <script>
            $(document).ready(function() {
                // Initialize DataTables
                var tablePenugasan = $('#table-penugasan').DataTable({
                    paging: true,
                    searching: true,
                    // Add more DataTable options as needed
                });
    
                var tablePpnpn = $('#table-ppnpn').DataTable({
                    paging: true,
                    searching: true,
                    // Add more DataTable options as needed
                });
    
                var tableLokakarya = $('#table-lokakarya').DataTable({
                    paging: true,
                    searching: true,
                    // Add more DataTable options as needed
                });
    
                // Initially hide all tables
                $('.table-internal').hide();
    
                // Handle change event on #rekapan dropdown
                $('#rekapan').on('change', function() {
                    var selectedValue = $(this).val();
    
                    // Hide all tables initially
                    $('.table-internal').hide();
    
                    // Show the selected table based on dropdown value
                    if (selectedValue === 'Penugasan Pegawai') {
                        $('#table-internal-penugasan').show();
                    } else if (selectedValue === 'Penugasan PPNPN') {
                        $('#table-internal-ppnpn').show();
                    } else if (selectedValue === 'Pendamping Lokakarya') {
                        $('#table-internal-lokakarya').show();
                    }
                });
    
                // Filter by Nama
                $('#namaFilter').on('keyup', function() {
                    tablePenugasan.column(2).search(this.value).draw();
                    tablePpnpn.column(2).search(this.value).draw();
                });
    
                // Filter by Penugasan
                $('#penugasanFilter').on('keyup', function() {
                    tablePenugasan.column(5).search(this.value).draw();
                    tablePpnpn.column(5).search(this.value).draw();
                });
    
                // Filter by Pendamping
                $('#pendampingFilter').on('keyup', function() {
                    tableLokakarya.column(1).search(this.value).draw();
                });
    
                // Reset button functionality
                $('#resetBtn').on('click', function() {
                    $('#rekapan').val('');
                    $('#namaFilter').val('');
                    $('#penugasanFilter').val('');
                    $('#pendampingFilter').val('');
                    $('.table-internal').hide();
                    tablePenugasan.search('').columns().search('').draw();
                    tablePpnpn.search('').columns().search('').draw();
                    tableLokakarya.search('').columns().search('').draw();
                });
            });
        </script>
    @endpush
@endsection
