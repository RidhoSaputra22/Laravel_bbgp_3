@extends('layouts.app', ['title' => 'Data Internal'])

@section('content')
    @push('styles')
        <link rel="stylesheet" href="{{ asset('library/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('library/datatables.net-select-bs4/css/select.bootstrap4.min.css') }}">
        <style>
            .table-internal {
                display: none;
            }

            .table-responsive {
                overflow-y: auto;
                max-height: 600px;
                /* Adjust based on your requirements */
            }

            .fixed-header-table thead th,
            .fixed-header-table thead .sticky-col,
            .fixed-header-table thead .sticky-header {
                position: sticky;
                top: 0;
                z-index: 10;
                background-color: white;
                /* Ensure the header has a background */
                border: 2px solid #000;
                /* Ensure consistent border styling */
            }

            .fixed-header-table .sticky-col {
                position: sticky;
                left: 0;
                z-index: 10;
                background-color: white;
                /* Ensure the sticky column has a background */
                border-right: 2px solid #000;
                /* Optional: Add border to differentiate sticky columns */
            }

            .fixed-header-table .sticky-col+.sticky-col {
                left: 60px;
                /* Adjust based on the width of the first sticky column */
            }

            .fixed-header-table .sticky-col.sticky-header {
                z-index: 20;
                /* Ensure the sticky header columns stay above the other columns */
            }

            .fixed-header-table th.sticky-header {
                z-index: 5;
                /* Ensure the sticky header columns stay above the other columns */
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
                                <div class="row">
                                    <div class="col">
                                        <h4 id="title-text">Data Internal</h4>
                                        <div class="d-flex mt-3 mb-5">
                                            <div class="row mx-2">
                                                <div class="">
                                                    <a href="#" id="pegawaiBBGTK" class="btn btn-warning btn-lg p-2">
                                                        <i class="fas fa-layer-group mr-1"></i>Penugasan Pegawai BBGTK
                                                    </a>
                                                </div>
                                                <div class="">
                                                    <a href="#" id="pegawaiPpnp"
                                                        class="btn btn-success btn-lg mx-3 p-2">
                                                        <i class="fas fa-layer-group mr-1"></i>Penugasan Pegawai PPNPN
                                                    </a>
                                                </div>
                                                <div class="">
                                                    <a href="#" id="statusPegawai"
                                                        class="btn btn-info btn-lg mr-3 p-2">
                                                        <i class="fas fa-search mr-1"></i>Lihat Status Pegawai BBGTK
                                                    </a>
                                                </div>
                                                <div class="">
                                                    <a href="#" id="daftarKegiatanLoka"
                                                        class="btn btn-primary btn-lg  p-2">
                                                        <i class="fas fa-calendar-alt mr-1"></i></i>Daftar Kegiatan
                                                        Lokakarya
                                                    </a>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                <!-- Filter Section -->
                                {{-- <h5>Pencarian Data Internal BBGTK</h5>
                                <div class="row mb-2">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <input name="nama" id="namaFilter" type="text"
                                                placeholder="Masukkan nama anda" class="form-control">
                                        </div>
                                    </div>
                                </div> --}}



                                <!-- Filter Data Internal -->
                                {{-- Area PEgawai --}}
                                <div id="filter-pegawai">
                                    <div class="row">
                                        <div class="col-md-4 mb-4">
                                            <label>Penugasan Pegawai</label>
                                            <select required name="rekapan" class="form-control selectric" id="rekapan">
                                                <option value="">-- Silahkan Pilih --</option>
                                                <option value="Status Pegawai">Table Status Pegawai</option>
                                                <option value="Penugasan Pegawai">Table Penugasan Pegawai</option>
                                            </select>
                                        </div>
                                    </div>

                                </div>


                                <div id="status-pegawai">
                                    <h4>Lihat status pegawai di Bulan <span id="getBulanTahun"></span></h4>
                                    <div class="row">
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <h6>Cari Nama</h6>
                                                <input type="text" class="form-control" placeholder="masukkan nama"
                                                    name="namaStatus" id="namaStatus">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <h6>Filter Status</h6>
                                                <select name="filterStatus" class="form-control selectric"
                                                    id="filterStatus">
                                                    <option value="">-- Pilih StatusPegawai --</option>
                                                    <option value="BBGP">Pegawai BBGTK</option>
                                                    <option value="PPNPN">Pegawai PPNPN</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <div class="card">
                                                <div class="card-body">
                                                    <!-- Status Pegawai -->
                                                    <!-- Navigation Buttons -->
                                                    <div class="d-flex justify-content-between mb-3">
                                                        <button id="prevMonth" class="btn btn-primary">Previous
                                                            Month</button>
                                                        <button id="nextMonth" class="btn btn-primary">Next Month</button>
                                                    </div>
                                                    <div class="table-responsive">
                                                        <table class="table table-md table-bordered fixed-header-table"
                                                            style="border: 2px solid #000;">
                                                            <thead>
                                                                <tr>
                                                                    <th rowspan="2"
                                                                        class="text-center align-middle sticky-header"
                                                                        style="border: 2px solid #000; background-color: white;">
                                                                        #</th>
                                                                    <th rowspan="2"
                                                                        class="text-center align-middle sticky-col sticky-header"
                                                                        style="border: 2px solid #000; background-color: white;">
                                                                        Nama Lengkap</th>
                                                                    <th rowspan="2"
                                                                        class="text-center align-middle sticky-header"
                                                                        style="border: 2px solid #000; background-color: white;">
                                                                        Status Pegawai</th>
                                                                    <th id="monthHeader" colspan="31"
                                                                        class="text-center sticky-header"
                                                                        style="border: 2px solid #000; background-color: white;">
                                                                        <span id="getBulanTahunCol"></span>
                                                                    </th>
                                                                </tr>
                                                                <tr id="dateHeader" class="sticky-header"
                                                                    style="background-color: white;">
                                                                    <!-- Dates will be injected here -->
                                                                </tr>
                                                            </thead>
                                                            <tbody id="employeeData">
                                                                <!-- Employee data will be injected here -->
                                                            </tbody>
                                                        </table>
                                                    </div>




                                                    {{-- <div id="pagination" class="mt-3">
                                                        <!-- Pagination links will be injected here -->
                                                    </div> --}}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>


                                {{-- Area Kegiatan Lokakarya --}}
                                <div id="kegiatan-lokakarya">
                                    <div class="col-md-8">

                                        <div class="form-group">
                                            <label for="filterKegiatan">Filter Kegiatan</label>
                                            <select id="filterKegiatan" class="form-control select2">
                                                <option value="">-- Pilih Kegiatan --</option>
                                                @foreach ($datas['lokaBBGP'] as $data)
                                                    <option value="{{ $data['kegiatan'] }}">{{ $data['kegiatan'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Tabel BBGP -->
                                    <div class="table-responsive table-kegiatan" id="table-kegiatan-bbgp"
                                        style="display: none;">
                                        <table class="table table-striped" id="table-bbgp-kegiatan">
                                            <thead>
                                                <tr>
                                                    <th class="text-center">#</th>
                                                    <th>Kegiatan</th>
                                                    <th>Lokasi Kegiatan</th>
                                                    <th>Tanggal Kegiatan</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($datas['lokaBBGP'] as $i => $data)
                                                    <?php
                                                    setlocale(LC_ALL, 'IND');
                                                    
                                                    $tgl_kegiatan = strftime('%d %B %Y', strtotime($data['tgl_kegiatan']));
                                                    ?>
                                                    <tr>
                                                        <td>{{ ++$i }}</td>
                                                        <td>{{ $data['kegiatan'] }}</td>
                                                        <td>{{ $data['kota'] }}</td>
                                                        <td>{{ $tgl_kegiatan }}</td>
                                                        <td>
                                                            <a href="#" class="btn btn-primary my-2"
                                                                data-toggle="modal" data-target="#pegawaiModal"
                                                                data-kegiatan="{{ $data['kegiatan'] }}"
                                                                data-pegawai="{{ json_encode($data['penugasan_pegawai']) }}">
                                                                Daftar Pegawai
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>


                                </div>




                                <!-- Tables Section -->
                                <!-- PPNPN -->
                                <div class="table-responsive table-internal" id="table-internal-ppnpn">
                                    <!-- Table PPNPN -->
                                    <table class="table table-striped" id="table-ppnpn">
                                        <thead>
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th style="width: 200px">Nama</th>
                                                <th style="width: 150px">NIK</th>
                                                <th style="width: 150px">NIP</th>
                                                <th>Jabatan</th>
                                                <th>Penugasan</th>
                                                {{-- <th>Action</th> --}}
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($datas['dataPenugasanPpnpn'] as $i => $data)
                                                <tr data-type="ppnpn">
                                                    <td>{{ ++$i }}</td>
                                                    <td>{{ $data->nama_lengkap ?? '' }}</td>
                                                    <td>{{ $data->no_ktp ?? '' }}</td>
                                                    <td>{{ $data->nip ?? '' }}</td>
                                                    <td>{{ $data->jabatan ?? '' }}</td>
                                                    <td>
                                                        <button class="btn btn-success my-2"
                                                            data-nama="{{ $data->nama_lengkap }}"
                                                            data-id="{{ $data->id }}" data-nik="{{ $data->no_ktp }}"
                                                            data-toggle="modal" data-target="#modalPpnpn">
                                                            Menu Penugasan PPNPN
                                                        </button>

                                                        <button class="btn btn-info my-2"
                                                            data-nama="{{ $data->nama_lengkap }}"
                                                            data-id="{{ $data->id }}" data-nik="{{ $data->no_ktp }}"
                                                            data-toggle="modal" data-target="#modalLokakarya">
                                                            Menu Lokakarya
                                                        </button>

                                                        {{-- <a href="{{ route('internal.create.ppnp', $data->id) }}"
                                                            class="btn btn-primary my-2">Penugasan PPNPN</a> --}}
                                                        {{-- <a href="{{ route('internal.create.lokakarya', $data->id) }}"
                                                            class="btn btn-info my-2">Penugasan Lokakarya</a> --}}
                                                    </td>
                                                    {{-- <td>

                                                        <a href="{{ route('internal.edit.ppnpn', $data->id) }}"
                                                            class="btn btn-warning my-2"><i class="fas fa-edit"></i></a>
                                                        <button onclick="deleteData({{ $data->id }}, 'ppnpn')"
                                                            class="btn btn-danger">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                        
                                                    </td> --}}

                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                {{-- BBGP --}}
                                <div class="table-responsive table-internal" id="table-internal-bbgp">
                                    <!-- Table BBGTK -->
                                    <table class="table table-striped" id="table-bbgp">
                                        <thead>
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th>Nama Lengkap</th>
                                                <th>Golongan</th>
                                                <th>Jabatan</th>
                                                <th>NIK</th>
                                                <th>NIP</th>
                                                <th>Penugasan</th>
                                                {{-- <th>Action</th> --}}
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($datas['dataPenugasanPegawai'] as $i => $data)
                                                <tr data-type="bbgp">
                                                    <td>{{ ++$i }}</td>
                                                    <td>{{ $data->nama_lengkap }}</td>
                                                    <td>{{ $data->golongan }}</td>
                                                    <td>{{ $data->jabatan }}</td>
                                                    <td>{{ $data->no_ktp }}</td>
                                                    <td>{{ $data->nip }}</td>
                                                    <td>
                                                        {{-- <a href="{{ route('internal.index.pegawai', $data->no_ktp) }}"
                                                            class="btn btn-primary mb-2">Lihat Penugasan</a> --}}
                                                        <button class="btn btn-primary my-2"
                                                            data-nama="{{ $data->nama_lengkap }}"
                                                            data-id="{{ $data->id }}" data-nik="{{ $data->no_ktp }}"
                                                            data-toggle="modal" data-target="#modalPenugasan">
                                                            Menu Penugasan
                                                        </button>

                                                        <button class="btn btn-info my-2"
                                                            data-nama="{{ $data->nama_lengkap }}"
                                                            data-id="{{ $data->id }}" data-nik="{{ $data->no_ktp }}"
                                                            data-toggle="modal" data-target="#modalLokakarya">
                                                            Menu Lokakarya
                                                        </button>

                                                        {{-- <a href="{{ route('internal.create.pegawai', $data->id) }}"
                                                            class="btn btn-primary mb-2">Penugasan Pegawai</a> --}}

                                                        {{-- <a href="{{ route('internal.create.lokakarya', $data->id) }}"
                                                            class="btn btn-info mb-2">Pendamping Lokakarya</a> --}}
                                                    </td>
                                                    {{-- <td>
                                                        <a href="#"
                                                            class="btn btn-info my-2"><i class="fas fa-info"></i></a>

                                                        <a href="{{ route('pegawai.edit', $data->id) }}"
                                                            class="btn btn-warning my-2"><i class="fas fa-edit"></i></a>

                                                        <button onclick="deleteData({{ $data->id }}, 'bbgp')"
                                                            class="btn btn-danger">
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

    {{-- Modal Penugasan Pegawai --}}
    <div class="modal fade" tabindex="-1" role="dialog" id="modalPenugasan">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title-pegawai">Menu Penugasan Pegawai BBGTK</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-footer bg-whitesmoke br">
                    {{-- Link dengan id, yang nantinya akan diubah oleh script --}}
                    <a id="lihatPenugasanLink" class="btn text-white btn-info">Lihat Penugasan</a>
                    <a id="tambahPenugasanLink" class="btn text-white btn-success">Tambah Penugasan</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Pendamping Lokakarya --}}
    <div class="modal fade" tabindex="-1" role="dialog" id="modalLokakarya">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title-lokakarya">Menu Pendamping Lokakarya BBGTK</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-footer bg-whitesmoke br">
                    {{-- Link dengan id, yang nantinya akan diubah oleh script --}}
                    <a id="lihatLokakaryaLink" class="btn text-white btn-info">Lihat Lokakarya</a>
                    <a id="tambahLokakaryaLink" class="btn text-white btn-success">Tambah Lokakarya</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Penugasan PPNPN --}}
    <div class="modal fade" tabindex="-1" role="dialog" id="modalPpnpn">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title-ppnpn">Menu Penugasan Pegawai PPNPN BBGTK</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-footer bg-whitesmoke br">
                    {{-- Link dengan id, yang nantinya akan diubah oleh script --}}
                    <a id="lihatPPNPNLink" class="btn text-white btn-info">Lihat Penugasan PPNPN</a>
                    <a id="tambahPPNPNLink" class="btn text-white btn-success">Tambah Penugasan PPNPN</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="assignmentModal" tabindex="-1" role="dialog" aria-labelledby="assignmentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignmentModalLabel">Detail Penugasan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modalBodyContent">
                    <p><strong>Penugasan:</strong> <span id="modalAssignmentTitle"></span></p>
                    <p><strong>Tipe Penugasan:</strong> <span id="modalAssignmentType"></span></p>
                    <p><strong>Atas nama:</strong> <span id="modalAssignmentName"></span></p>
                    <p><strong>Tanggal Kegiatan:</strong> <span id="modalAssignmentDate"></span></p>
                    <p><strong>Deskripsi:</strong></p>
                    <p id="modalAssignmentDescription"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="assignmentModal" tabindex="-1" role="dialog" aria-labelledby="assignmentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignmentModalLabel">Detail Penugasan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modalBodyContent">
                    <!-- Detail penugasan akan dimuat di sini -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal untuk Daftar Pegawai -->
    <div class="modal fade" id="pegawaiModal" tabindex="-1" role="dialog" aria-labelledby="pegawaiModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex align-items-center">
                        <h5 class="mr-4">Daftar Pegawai</h5>
                        <button id="printButton" class="btn btn-primary mb-3">Cetak Data</button>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="printableArea">
                        <h5 class="modal-title mb-3" id="modalHeader"></h5>
                        <table class="table table-responsive table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Pegawai</th>
                                    <th>Hotel</th>
                                    <th>Transport Pergi</th>
                                    <th>Transport Pulang</th>
                                    <th>Bill Penginapan</th>
                                    <th>Hari 1</th>
                                    <th>Hari 2</th>
                                    <th>Hari 3</th>
                                    <th>Hari 4</th>
                                    <th>Hari 5</th>
                                    <th>Hari 6</th>
                                    <th>Hari 7</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="pegawaiTableBody">
                                <!-- Daftar pegawai akan dimasukkan di sini oleh JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    {{-- <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Pegawai</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        <input type="hidden" id="editId">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="editNama">Nama Pegawai</label>
                                <input type="text" class="form-control" id="editNama" readonly>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="editHotel">Hotel</label>
                                <input type="text" class="form-control" id="editHotel">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="editTransportPergi">Transport Pergi</label>
                                <input type="text" class="form-control rupiah-input" id="editTransportPergi">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="editTransportPulang">Transport Pulang</label>
                                <input type="text" class="form-control rupiah-input" id="editTransportPulang">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="editBillPenginapan">Bill Penginapan</label>
                                <input type="text" class="form-control rupiah-input" id="editBillPenginapan">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="editHari1">Hari 1</label>
                                <input type="text" class="form-control rupiah-input" id="editHari1">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="editHari2">Hari 2</label>
                                <input type="text" class="form-control rupiah-input" id="editHari2">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="editHari3">Hari 3</label>
                                <input type="text" class="form-control rupiah-input" id="editHari3">
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary" id="saveEditButton">Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </div>
    </div> --}}


    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Pegawai</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        <input type="hidden" id="editId">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="editNama">Nama Pegawai</label>
                                <input type="text" class="form-control" id="editNama" readonly>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="editHotel">Hotel</label>
                                <input type="text" class="form-control" id="editHotel">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Golongan</label>
                                    <input readonly
                                        value="" id="golongan" type="text" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="editTransportPergi">Transport Pergi</label>
                                <input type="text" class="form-control rupiah-input" id="editTransportPergi">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="editTransportPulang">Transport Pulang</label>
                                <input type="text" class="form-control rupiah-input" id="editTransportPulang">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="editBillPenginapan">Bill Penginapan</label>
                                <div class="custom-switches-stacked">
                                    <label class="custom-switch">
                                        <input type="checkbox" name="switch_penginapan" id="switch_penginapan"
                                            class="custom-switch-input">
                                        <span class="custom-switch-indicator"></span>
                                        <span class="custom-switch-description">Bill atau 30%</span>
                                    </label>
                                </div>

                                <input readonly type="text" class="form-control rupiah-input" id="editBillPenginapan">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="editHari1">Hari 1</label>
                                <input type="text" class="form-control rupiah-input" id="editHari1">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="editHari2">Hari 2</label>
                                <input type="text" class="form-control rupiah-input" id="editHari2">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="editHari3">Hari 3</label>
                                <input type="text" class="form-control rupiah-input" id="editHari3">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="editHari4">Hari 4</label>
                                <input type="text" class="form-control rupiah-input" id="editHari4">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="editHari5">Hari 5</label>
                                <input type="text" class="form-control rupiah-input" id="editHari5">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="editHari6">Hari 6</label>
                                <input type="text" class="form-control rupiah-input" id="editHari6">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="editHari7">Hari 7</label>
                                <input type="text" class="form-control rupiah-input" id="editHari7">
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary" id="saveEditButton">Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>





    @push('scripts')
        <script src="{{ asset('library/datatables/media/js/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('library/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('library/datatables.net-select-bs4/js/select.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('js/page/modules-datatables.js') }}"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/id.min.js"></script>


        <script>
            // Event yang dijalankan saat modal muncul
            $('#modalPenugasan').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget); // Button yang membuka modal
                var nik = button.data('id'); // Ambil data-nik dari tombol
                var nama = button.data('nama'); // Ambil data-nama dari tombol
                var lihatLink = "{{ route('internal.index.pegawai', ':id') }}".replace(':id', nik);
                var tambahLink = "{{ route('internal.create.pegawai', ':id') }}".replace(':id', button.data('id'));

                // Update href dari link di dalam modal
                $('.modal-title-pegawai').text(`Pegawai ${nama}`);
                $('#lihatPenugasanLink').attr('href', lihatLink);
                $('#tambahPenugasanLink').attr('href', tambahLink);
            });

            // Event Lokakarya yang dijalankan saat modal muncul
            $('#modalLokakarya').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget); // Button yang membuka modal
                var nik = button.data('nik'); // Ambil data-nik dari tombol
                var nama = button.data('nama'); // Ambil data-nama dari tombol

                var lihatLink = "{{ route('internal.index.lokakarya', ':nik') }}".replace(':nik', nik);
                var tambahLink = "{{ route('internal.create.lokakarya', ':id') }}".replace(':id', button.data('id'));
                console.log(nik);
                // Update href dari link di dalam modal
                $('.modal-title-lokakarya').text(`Pegawai ${nama}`);
                $('#lihatLokakaryaLink').attr('href', lihatLink);
                $('#tambahLokakaryaLink').attr('href', tambahLink);
            });


            // Event yang dijalankan saat modal muncul
            $('#modalPpnpn').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget); // Button yang membuka modal
                var nik = button.data('id'); // Ambil data-nik dari tombol
                var nama = button.data('nama'); // Ambil data-nama dari tombol
                var id = button.data('id'); // Ambil data-id dari tombol

                var lihatLink = "{{ route('internal.index.ppnpn', ':id') }}".replace(':id', nik);
                var tambahLink = "{{ route('internal.create.ppnpn', ':id') }}".replace(':id', id);

                // Update href dari link di dalam modal
                $('.modal-title-ppnpn').text(`Pegawai ${nama}`);
                $('#lihatPPNPNLink').attr('href', lihatLink);
                $('#tambahPPNPNLink').attr('href', tambahLink);
            });
        </script>


        <script type="text/javascript">
            $(document).ready(function() {

                var language = {
                    "sSearch": "Pencarian Data Internal BBGTK : ",
                };
                var tableStatusPegawai = $('#table-status-pegawai').DataTable({
                    paging: true,
                    searching: true,
                    language: language,
                    // Add more DataTable options as needed
                });


                var tableKegiatanBBGP = $('#table-bbgp-kegiatan').DataTable({
                    paging: true,
                    searching: true,
                    language: language,
                    // Add more DataTable options as needed
                });


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
                $('#status-pegawai').hide();
                $('#kegiatan-lokakarya').hide();


                $('#filter-pegawai').hide();



                // Show appropriate table based on button click
                $('#statusPegawai').on('click', function(event) {
                    event.preventDefault();
                    $('.table-internal').hide();
                    $('#kegiatan-lokakarya').hide();

                    // $('#filter-pegawai').show();
                    $('#status-pegawai').show();
                    $('#title-text').text('Data Status Pegawai')
                    $('.table-kegiatan').hide();


                });

                $('#daftarKegiatan').on('click', function(event) {
                    event.preventDefault();
                    $('.table-internal').hide();
                    // $('#filter-pegawai').show();
                    $('#kegiatan-lokakarya').show();
                    $('#title-text').text('Data Kegiatan Lokakarya')


                    $('.table-kegiatan').hide();
                    $('#status-pegawai').hide();


                });

                // Show appropriate table based on button click
                $('#pegawaiBBGTK').on('click', function(event) {
                    event.preventDefault();
                    $('.table-internal').hide();
                    $('#kegiatan-lokakarya').hide();

                    // $('#filter-pegawai').show();
                    $('#table-internal-bbgp').show();
                    $('#title-text').text('Data Penugasan Pegawai')

                    $('.table-kegiatan').hide();
                    $('#status-pegawai').hide();


                });



                $('#pegawaiPpnp').on('click', function(event) {
                    event.preventDefault();
                    $('.table-internal').hide();
                    $('#kegiatan-lokakarya').hide();

                    $('#table-internal-ppnpn').show();
                    tablePpnpn.columns.adjust().draw(); // Adjust column widths on table show
                    $('#title-text').text('Data Penugasan PPNPN')

                    // $('#status-pegawai').hide();
                    $('#table-internal-bbgp').hide();
                    // $('#filter-pegawai').hide();

                    $('#status-pegawai').hide();
                    $('.table-kegiatan').hide();




                });

                // Filter tables based on dropdown selection
                $('#rekapan').on('change', function() {
                    let jenis = $(this).val().toLowerCase().replace(/ /g, '-');
                    console.log(jenis);
                    // Hide all tables initially
                    $('.table-internal').hide();

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


                let year = {{ \Carbon\Carbon::now()->year }};
                let month = {{ \Carbon\Carbon::now()->month }};

                function loadCalendarData(year, month, page = 1, name = '', status = '') {
                    $.ajax({
                        url: "{{ route('internal.getCalendarData') }}",
                        type: 'GET',
                        data: {
                            year: year,
                            month: month,
                            page: page,
                            name: name,
                            status: status
                        },
                        success: function(response) {
                            console.log(response)
                            moment.locale('id'); // Set locale to Indonesian
                            let monthName = moment().year(response.year).month(response.month - 1).format(
                                'MMMM YYYY');

                            $('#calendarTitle').text('Calendar for ' + monthName);
                            $('#getBulanTahun').text(monthName);
                            $('#getBulanTahunCol').text(monthName);
                            $('#monthHeader').attr('colspan', response.dates.length);
                            $('#dateHeader').empty();
                            $.each(response.dates, function(index, date) {
                                // console.log(index)
                                $('#dateHeader').append(
                                    '<th class="text-center" style="border: 2px solid #000;">' +
                                    (index + 1) + '</th>');
                            });

                            $('#employeeData').empty();

                            $.each(response.employees, function(index, employee) {
                                // console.log(employee)
                                let row = '<tr>';
                                row +=
                                    '<td class="text-center" style="border: 2px solid #000; background-color: white;">' +
                                    (index + 1) + '</td>';
                                row +=
                                    '<td class="text-nowrap sticky-col" style="border: 2px solid #000; background-color: white;">' +
                                    employee.name + '</td>';
                                row +=
                                    '<td class="text-nowrap" style="border: 2px solid #000; background-color: white;">' +
                                    employee.status + '</td>';

                                let assignmentDates = [];

                                // Memasukkan semua tanggal penugasan ke dalam array
                                $.each(employee.assignments, function(aIndex, assignment) {
                                    let startDate = moment(assignment.start);
                                    let endDate = moment(assignment.end);

                                    for (let m = moment(startDate); m.isSameOrBefore(
                                            endDate); m.add(1, 'days')) {
                                        assignmentDates.push(m.format('YYYY-MM-DD'));
                                    }
                                });

                                // Memeriksa setiap tanggal dalam response.dates
                                $.each(response.dates, function(index, date) {
                                    let currentDate = moment(date.date).format(
                                        'YYYY-MM-DD');
                                    let cellColor = 'white';
                                    let cellText = '';

                                    // Memeriksa apakah ada dua penugasan yang tumpang tindih pada tanggal yang sama
                                    let overlappingAssignments = employee.assignments
                                        .filter(assignment => {
                                            let startDate = moment(assignment.start)
                                                .format('YYYY-MM-DD');
                                            let endDate = moment(assignment.end).format(
                                                'YYYY-MM-DD');
                                            return currentDate >= startDate &&
                                                currentDate <= endDate;
                                        });

                                    if (overlappingAssignments.length > 1) {
                                        cellColor = 'red';
                                    } else if (overlappingAssignments.length === 1) {
                                        cellColor = 'green';
                                    }


                                    if (cellColor !== 'white') {
                                        row +=
                                            '<td class="text-center assignment-cell" style="border: 2px solid #000; background-color: ' +
                                            cellColor + ';" data-assignments="' +
                                            encodeURIComponent(JSON.stringify(
                                                overlappingAssignments)) + '" data-name="' +
                                            employee.name + '" data-cell-color="' +
                                            cellColor + '">' + cellText + '</td>';
                                    } else {
                                        row +=
                                            '<td class="text-center" style="border: 2px solid #000; background-color: ' +
                                            cellColor + ';">' + cellText + '</td>';
                                    }
                                });

                                row += '</tr>';
                                $('#employeeData').append(row);
                            });



                            // Update pagination
                            $('#pagination').html(response.pagination);
                        }
                    });
                }

                $('#prevMonth').click(function() {
                    if (month == 1) {
                        month = 12;
                        year--;
                    } else {
                        month--;
                    }
                    loadCalendarData(year, month);
                });

                $('#nextMonth').click(function() {
                    if (month == 12) {
                        month = 1;
                        year++;
                    } else {
                        month++;
                    }
                    loadCalendarData(year, month);
                });

                // Handle pagination click
                // $(document).on('click', '#pagination a', function(e) {
                //     e.preventDefault();
                //     let page = $(this).attr('href').split('page=')[1];
                //     loadCalendarData(year, month, page, $('#namaStatus').val(), $('#filterStatus').val());
                // });

                // Handle name search
                $('#namaStatus').on('keyup', function() {
                    loadCalendarData(year, month, 1, $(this).val(), $('#filterStatus').val());
                });

                // Handle status filter
                $('#filterStatus').on('change', function() {
                    var selectedOption = $(this).find('option:selected');
                    console.log(selectedOption.val())
                    loadCalendarData(year, month, 1, $('#namaStatus').val(), selectedOption.val());
                });

                // Load the initial calendar data
                loadCalendarData(year, month);


                // Initially hide both tables

                // Event listener for showing the kegiatan-lokakarya section
                $('#daftarKegiatanLoka').on('click', function(event) {
                    event.preventDefault();
                    $('#kegiatan-lokakarya').show();
                    $('#table-kegiatan-bbgp').show();

                    $('#status-pegawai').hide();
                    $('.table-internal').hide();
                });

                var kegiatanList = [];
                $('#filterKegiatan option').each(function() {
                    var kegiatan = $(this).val();
                    if (kegiatan && !kegiatanList.includes(kegiatan)) {
                        kegiatanList.push(kegiatan);
                    }
                });

                // Event listener untuk filter kegiatan
                $('#filterKegiatan').on('change', function() {
                    var selectedKegiatan = $(this).val().toLowerCase();
                    tableKegiatanBBGP.column(1).search(this.value).draw();
                });


                $('#pegawaiModal').on('show.bs.modal', function(event) {
                    var button = $(event.relatedTarget); // Button yang membuka modal
                    var kegiatan = button.data('kegiatan'); // Ambil data-kegiatan dari tombol
                    var pegawai = button.data('pegawai'); // Ambil data-pegawai dari tombol (sebagai JSON)

                    var modal = $(this);
                    modal.find('.modal-title').text('Daftar Pegawai untuk Kegiatan: ' + kegiatan);
                    modal.find('#modalHeader').text('Daftar Lokakarya: ' + kegiatan);

                    var pegawaiTableBody = modal.find('#pegawaiTableBody');
                    pegawaiTableBody.empty(); // Kosongkan isi tabel sebelumnya

                    // Variabel untuk menyimpan total keseluruhan
                    var totalTransportPergi = 0;
                    var totalTransportPulang = 0;
                    var totalBill = 0;
                    var totalHari1 = 0;
                    var totalHari2 = 0;
                    var totalHari3 = 0;
                    var totalHari4 = 0;
                    var totalHari5 = 0;
                    var totalHari6 = 0;
                    var totalHari7 = 0;
                    var totalKeseluruhan = 0;

                    // Tambahkan setiap pegawai ke dalam tabel
                    $.each(pegawai, function(index, pegawaiItem) {
                        console.log(pegawaiItem)
                        // Parse nilai ke integer dan hitung total
                        var transportPergi = parseInt(pegawaiItem.transport_pergi) || 0;
                        var transportPulang = parseInt(pegawaiItem.transport_pulang) || 0;
                        var billPenginapan = parseInt(pegawaiItem.bill_penginapan) || 0;
                        var hari1 = parseInt(pegawaiItem.hari_1) || 0;
                        var hari2 = parseInt(pegawaiItem.hari_2) || 0;
                        var hari3 = parseInt(pegawaiItem.hari_3) || 0;
                        var hari4 = parseInt(pegawaiItem.hari_4) || 0;
                        var hari5 = parseInt(pegawaiItem.hari_5) || 0;
                        var hari6 = parseInt(pegawaiItem.hari_6) || 0;
                        var hari7 = parseInt(pegawaiItem.hari_7) || 0;

                        var totalPerRow = transportPergi + transportPulang + billPenginapan + hari1 +
                            hari2 + hari3 + hari4 + hari5 + hari6 + hari7;

                        // Tambahkan ke total keseluruhan
                        totalTransportPergi += transportPergi;
                        totalTransportPulang += transportPulang;
                        totalBill += billPenginapan;
                        totalHari1 += hari1;
                        totalHari2 += hari2;
                        totalHari3 += hari3;
                        totalHari4 += hari4;
                        totalHari5 += hari5;
                        totalHari6 += hari6;
                        totalHari7 += hari7;
                        totalKeseluruhan += totalPerRow;

                        pegawaiTableBody.append(`
                            <tr>
                                <td>${index + 1}</td>
                                <td class="text-nowrap">
                                    ${pegawaiItem.nama}
                                </td>
                                <td>${pegawaiItem.hotel}</td>
                                <td class="text-nowrap">${formatRupiah(transportPergi, 'Rp.')}</td>
                                <td class="text-nowrap">${formatRupiah(transportPulang, 'Rp.')}</td>
                                <td class="text-nowrap">${formatRupiah(billPenginapan, 'Rp.')}</td>
                                <td class="text-nowrap">${formatRupiah(hari1, 'Rp.')}</td>
                                <td class="text-nowrap">${formatRupiah(hari2, 'Rp.')}</td>
                                <td class="text-nowrap">${formatRupiah(hari3, 'Rp.')}</td>
                                <td class="text-nowrap">${formatRupiah(hari4, 'Rp.')}</td>
                                <td class="text-nowrap">${formatRupiah(hari5, 'Rp.')}</td>
                                <td class="text-nowrap">${formatRupiah(hari6, 'Rp.')}</td>
                                <td class="text-nowrap">${formatRupiah(hari7, 'Rp.')}</td>
                                <td class="text-nowrap">${formatRupiah(totalPerRow, 'Rp.')}</td>
                                <td class="text-nowrap">
                                    <button class="btn btn-warning edit-button" 
                                    data-id="${pegawaiItem.id}" 
                                    data-golongan="${pegawaiItem.golongan}" 
                                    data-nama="${pegawaiItem.nama}" 
                                    data-hotel="${pegawaiItem.hotel}" 
                                    data-transportpergi="${transportPergi}" 
                                    data-transportpulang="${transportPulang}" 
                                    data-billpenginapan="${billPenginapan}" 
                                    data-hari1="${hari1}" 
                                    data-hari2="${hari2}" 
                                    data-hari3="${hari3}" 
                                    data-hari4="${hari4}" 
                                    data-hari5="${hari5}" 
                                    data-hari6="${hari6}" 
                                    data-hari7="${hari7}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger delete-button" data-id="${pegawaiItem.id}">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        `);
                    });

                    // Tambahkan baris total di akhir tabel dengan kelas total-row
                    pegawaiTableBody.append(`
                        <tr class="total-row">
                            <td colspan="3" class="text-center"><strong>Total</strong></td>
                            <td id="transportPergiTotal" class="text-nowrap"><strong>${formatRupiah(totalTransportPergi, 'Rp.')}</strong></td>
                            <td id="transportPulangTotal" class="text-nowrap"><strong>${formatRupiah(totalTransportPulang, 'Rp.')}</strong></td>
                            <td id="billPenginapanTotal" class="text-nowrap"><strong>${formatRupiah(totalBill, 'Rp.')}</strong></td>
                            <td id="hari1Total" class="text-nowrap"><strong>${formatRupiah(totalHari1, 'Rp.')}</strong></td>
                            <td id="hari2Total" class="text-nowrap"><strong>${formatRupiah(totalHari2, 'Rp.')}</strong></td>
                            <td id="hari3Total" class="text-nowrap"><strong>${formatRupiah(totalHari3, 'Rp.')}</strong></td>
                            <td id="hari4Total" class="text-nowrap"><strong>${formatRupiah(totalHari4, 'Rp.')}</strong></td>
                            <td id="hari5Total" class="text-nowrap"><strong>${formatRupiah(totalHari5, 'Rp.')}</strong></td>
                            <td id="hari6Total" class="text-nowrap"><strong>${formatRupiah(totalHari6, 'Rp.')}</strong></td>
                            <td id="hari7Total" class="text-nowrap"><strong>${formatRupiah(totalHari7, 'Rp.')}</strong></td>
                            <td id="grandTotal" class="text-nowrap"><strong>${formatRupiah(totalKeseluruhan, 'Rp.')}</strong></td>
                        </tr>
                    `);


                });

                // Handle Delete Button Click
                $(document).on('click', '.delete-button', function() {
                    var id = $(this).data('id');
                    console.log(id)
                    swal({
                        title: "Konfirmasi",
                        text: "Apakah Anda yakin ingin menghapus data ini?",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    }).then((willDelete) => {
                        if (willDelete) {
                            console.log('hapus')
                            deleteDataLoka(id, 'internal');
                            // $('#pegawaiTableBody').find(`button[data-id='${id}']`).closest(
                            //     'tr').remove();
                            // location.reload();

                            // Recalculate column totals
                            // recalculateColumnTotals();
                        }
                    });
                });

                // Delete Function
                function deleteDataLoka(id, tabel) {
                    let token = $("meta[name='csrf-token']").attr("content");

                    $.ajax({
                        headers: {
                            "X-CSRF-TOKEN": token,
                        },
                        type: "POST",
                        url: `${tabel}/hapus/${id}`,
                        success: function(response) {
                            console.log(response);
                            if (response) {
                                swal("Terhapus", "Data telah dihapus", "success").then(
                                    () => {
                                        // Here, you can make an AJAX request to delete the data from the server
                                        console.log(`Deleting data with ID: ${id} of type: ${tabel}`);

                                        // For now, remove the row from the table for demonstration purposes
                                        $('#pegawaiTableBody').find(`button[data-id='${id}']`).closest(
                                            'tr').remove();

                                        location.reload();
                                        // Recalculate column totals
                                        recalculateColumnTotals();
                                    }
                                );
                            } else {
                                swal("Error", "Failed to delete data.", "error");
                            }
                        },
                        error: function(error) {
                            console.error("AJAX Error:", error);
                            swal("Error", "Ajax Error.", "error");
                        },
                    });

                }

                // Function to recalculate the totals for each column
                function recalculateColumnTotals() {
                    var transportPergiTotal = 0;
                    var transportPulangTotal = 0;
                    var billPenginapanTotal = 0;
                    var hari1Total = 0;
                    var hari2Total = 0;
                    var hari3Total = 0;
                    var hari4Total = 0;
                    var hari5Total = 0;
                    var hari6Total = 0;
                    var hari7Total = 0;
                    var grandTotal = 0;

                    $('#pegawaiTableBody tr').each(function() {
                        var row = $(this);
                        var rowTotal = 0;

                        row.find('td').each(function(index, cell) {
                            // Only parse the cell value if an input exists
                            var inputField = $(cell).find('.last-row');
                            var value = 0;
                            if (inputField.length) {
                                // If an input field is found, get its value
                                value = parseInt(inputField.val().replace(/[^\d.-]/g, '').replace(',',
                                    '.')) || 0;
                            } else {
                                // If there's no input, try to parse the text directly if it's in edit mode
                                value = parseInt($(cell).text().replace(/[^\d.-]/g, '').replace(',',
                                    '.')) || 0;
                            }
                            console.log(cell)
                            console.log(inputField.val())
                            console.log(indnex)

                            switch (index) {
                                case 3:
                                    transportPergiTotal += value;
                                    break;
                                case 4:
                                    transportPulangTotal += value;
                                    break;
                                case 5:
                                    billPenginapanTotal += value;
                                    break;
                                case 6:
                                    hari1Total += value;
                                    break;
                                case 7:
                                    hari2Total += value;
                                    break;
                                case 8:
                                    hari3Total += value;
                                    break;
                                case 9:
                                    hari4Total += value;
                                    break;
                                case 10:
                                    hari5Total += value;
                                    break;
                                case 11:
                                    hari6Total += value;
                                    break;
                                case 12:
                                    hari7Total += value;
                                    break;
                            }

                            if (index > 2 && index < 13) {
                                rowTotal += value;
                            }
                        });

                        grandTotal += rowTotal;
                    });

                    $('#transportPergiTotal').text(formatRupiah(transportPergiTotal, 'Rp. '));
                    $('#transportPulangTotal').text(formatRupiah(transportPulangTotal, 'Rp. '));
                    $('#billPenginapanTotal').text(formatRupiah(billPenginapanTotal, 'Rp. '));
                    $('#hari1Total').text(formatRupiah(hari1Total, 'Rp. '));
                    $('#hari2Total').text(formatRupiah(hari2Total, 'Rp. '));
                    $('#hari3Total').text(formatRupiah(hari3Total, 'Rp. '));
                    $('#hari4Total').text(formatRupiah(hari4Total, 'Rp. '));
                    $('#hari5Total').text(formatRupiah(hari5Total, 'Rp. '));
                    $('#hari6Total').text(formatRupiah(hari6Total, 'Rp. '));
                    $('#hari7Total').text(formatRupiah(hari7Total, 'Rp. '));
                    $('#grandTotal').text(formatRupiah(grandTotal, 'Rp. '));
                }


                $('#switch_penginapan').change(function() {
                     let getGolongan = $('#golongan');
                    let cutString = getGolongan.val().split('/');
                    console.log(cutString)

                    if ($(this).is(':checked')) {

                        if (cutString[0] == 'Tidak ada' || cutString[0] == 'I' || cutString[0] == 'II' ||
                            cutString[0] == 'III') {
                             $('#editBillPenginapan').val(formatRupiah(223500)); // Set nilai 210000
                        } else {

                             $('#editBillPenginapan').val(formatRupiah(426900)); // Set nilai 210000
                        }


                    } else {
                         $('#editBillPenginapan').val(0); // Set nilai 0 jika tidak aktif
                    }

                    // Trigger input event to recalculate totals if necessary
                    $('#editBillPenginapan').trigger('input');
                });

                // Event listener for formatting input fields as Rupiah
                $(document).on('input', '.rupiah-input', function() {
                    var value = $(this).val().replace(/[^0-9]/g, '');
                    $(this).val(formatRupiah(value, 'Rp.'));
                });

                // Handle Save Button in Edit Modal
                $('#saveEditButton').click(function() {
                    let token = $("meta[name='csrf-token']").attr("content");

                    swal({
                        title: "Konfirmasi",
                        text: "Apakah Anda yakin ingin menyimpan perubahan?",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    }).then((willSave) => {
                        if (willSave) {
                            // Collect data from the form
                            var id = $('#editId').val();
                            var nama = $('#editNama').val();
                            var hotel = $('#editHotel').val();
                            // Convert formatted Rupiah to integer
                            var transportPergi = parseInt($('#editTransportPergi').val().replace(
                                /[^,\d]/g, '').replace(',', '')) || 0;
                            var transportPulang = parseInt($('#editTransportPulang').val().replace(
                                /[^,\d]/g, '').replace(',', '')) || 0;
                            var billPenginapan = parseInt($('#editBillPenginapan').val().replace(
                                /[^,\d]/g, '').replace(',', '')) || 0;
                            var hari1 = parseInt($('#editHari1').val().replace(/[^,\d]/g, '').replace(
                                ',', '')) || 0;
                            var hari2 = parseInt($('#editHari2').val().replace(/[^,\d]/g, '').replace(
                                ',', '')) || 0;
                            var hari3 = parseInt($('#editHari3').val().replace(/[^,\d]/g, '').replace(
                                ',', '')) || 0;
                            var hari4 = parseInt($('#editHari4').val().replace(/[^,\d]/g, '').replace(
                                ',', '')) || 0;
                            var hari5 = parseInt($('#editHari5').val().replace(/[^,\d]/g, '').replace(
                                ',', '')) || 0;
                            var hari6 = parseInt($('#editHari6').val().replace(/[^,\d]/g, '').replace(
                                ',', '')) || 0;
                            var hari7 = parseInt($('#editHari7').val().replace(/[^,\d]/g, '').replace(
                                ',', '')) || 0;

                            var total = transportPergi + transportPulang + billPenginapan + hari1 +
                                hari2 + hari3 + hari4 + hari5 + hari6 + hari7;

                            // Prepare data object
                            var updatedData = {
                                id: id,
                                nama: nama,
                                hotel: hotel,
                                transportPergi: transportPergi,
                                transportPulang: transportPulang,
                                billPenginapan: billPenginapan,
                                hari1: hari1,
                                hari2: hari2,
                                hari3: hari3,
                                hari4: hari4,
                                hari5: hari5,
                                hari6: hari6,
                                hari7: hari7,
                                total: total
                            };
                            console.log(JSON.stringify(updatedData))

                            // AJAX POST request
                            $.ajax({
                                headers: {
                                    "X-CSRF-TOKEN": token,
                                },
                                url: `internal/updateLokakaryaJS`, // Replace with your server endpoint
                                type: 'POST',
                                data: JSON.stringify(updatedData),
                                contentType: 'application/json',
                                success: function(response) {
                                    if (response.success) {
                                        // Update the table row with new values
                                        var row = $('#pegawaiTableBody').find(
                                            `button[data-id='${id}']`).closest('tr');
                                        row.find('td').eq(2).text(hotel);
                                        row.find('td').eq(3).text(formatRupiah(
                                            transportPergi, 'Rp. '));
                                        row.find('td').eq(4).text(formatRupiah(
                                            transportPulang, 'Rp. '));
                                        row.find('td').eq(5).text(formatRupiah(
                                            billPenginapan, 'Rp. '));
                                        row.find('td').eq(6).text(formatRupiah(hari1,
                                            'Rp. '));
                                        row.find('td').eq(7).text(formatRupiah(hari2,
                                            'Rp. '));
                                        row.find('td').eq(8).text(formatRupiah(hari3,
                                            'Rp. '));
                                        row.find('td').eq(9).text(formatRupiah(hari4,
                                            'Rp. '));
                                        row.find('td').eq(10).text(formatRupiah(hari5,
                                            'Rp. '));
                                        row.find('td').eq(11).text(formatRupiah(hari6,
                                            'Rp. '));
                                        row.find('td').eq(12).text(formatRupiah(hari7,
                                            'Rp. '));
                                        row.find('td').eq(13).text(formatRupiah(total,
                                            'Rp. '));

                                        // Recalculate column totals
                                        recalculateColumnTotals();

                                        $('#editModal').modal('hide');
                                        swal("Perubahan telah disimpan!", {
                                            icon: "success",
                                        });
                                    } else {
                                        swal("Error", "Gagal menyimpan perubahan.",
                                            "error");
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error("AJAX Error:", error);
                                    swal("Error",
                                        "Terjadi kesalahan saat menghubungi server.",
                                        "error");
                                }
                            });
                        }
                    });
                });


                // Handle Edit Button Click
                $(document).on('click', '.edit-button', function() {
                    var id = $(this).data('id');
                    var nama = $(this).data('nama');
                    var hotel = $(this).data('hotel');
                    var transportPergi = $(this).data('transportpergi');
                    var transportPulang = $(this).data('transportpulang');
                    var billPenginapan = $(this).data('billpenginapan');
                    var hari1 = $(this).data('hari1');
                    var hari2 = $(this).data('hari2');
                    var hari3 = $(this).data('hari3');
                    var hari4 = $(this).data('hari4');
                    var hari5 = $(this).data('hari5');
                    var hari6 = $(this).data('hari6');
                    var hari7 = $(this).data('hari7');

                    $('#editId').val(id);
                    $('#editNama').val(nama);
                    $('#editHotel').val(hotel);
                    $('#editTransportPergi').val(formatRupiah(transportPergi, 'Rp. '));
                    $('#editTransportPulang').val(formatRupiah(transportPulang, 'Rp. '));
                    $('#editBillPenginapan').val(formatRupiah(billPenginapan, 'Rp. '));
                    $('#editHari1').val(formatRupiah(hari1, 'Rp. '));
                    $('#editHari2').val(formatRupiah(hari2, 'Rp. '));
                    $('#editHari3').val(formatRupiah(hari3, 'Rp. '));
                    $('#editHari4').val(formatRupiah(hari4, 'Rp. '));
                    $('#editHari5').val(formatRupiah(hari5, 'Rp. '));
                    $('#editHari6').val(formatRupiah(hari6, 'Rp. '));
                    $('#editHari7').val(formatRupiah(hari7, 'Rp. '));

                    $('#editModal').modal('show');
                });


                $('#printButton').on('click', function() {
                    var printContent = document.getElementById('printableArea');
                    var WinPrint = window.open('', '', 'width=900,height=650');
                    WinPrint.document.write('<html><head><title>Print</title>');
                    WinPrint.document.write('<style>');
                    WinPrint.document.write('@media print {');
                    WinPrint.document.write('table {border-collapse: collapse;}');
                    WinPrint.document.write('table, th, td {border: 1px solid black; padding: 10px;}');
                    WinPrint.document.write('th, td {text-align: left;}');
                    WinPrint.document.write(
                        'th, td {vertical-align: middle;}'); // Tambahkan vertikal align tengah
                    WinPrint.document.write(
                        '.total-row td {text-align: center; font-weight: bold;}'); // Gaya untuk baris total
                    WinPrint.document.write('.modal-title {font-size: 20px; margin-bottom: 20px;}');
                    WinPrint.document.write(
                        'tr:not(.total-row) td:last-child, tr:not(.total-row) th:last-child {display: none;}'
                        );

                    WinPrint.document.write('</style></head><body>');
                    WinPrint.document.write(printContent.innerHTML);
                    WinPrint.document.write('</body></html>');
                    WinPrint.document.close();
                    WinPrint.focus();
                    WinPrint.print();
                    WinPrint.close();
                });






            });

            // Event listener untuk klik pada cell penugasan berwarna
            $(document).on('click', '.assignment-cell', function() {
                let assignments = JSON.parse(decodeURIComponent($(this).data('assignments')));
                let nama = $(this).data('name');
                console.log('tes', nama)
                let cellColor = $(this).data('cell-color');
                let modalBodyContent = '';

                if (cellColor === 'red') {
                    modalBodyContent += '<div class="row">';
                    assignments.forEach(function(assignment) {
                        modalBodyContent += `
                    <div class="col-md-6">
                        <p><strong>Penugasan:</strong> ${assignment.title}</p>
                        <p><strong>Tipe Penugasan:</strong> ${assignment.type}</p>
                        <p><strong>Atas nama:</strong> ${nama}</p>
                        <p><strong>Tanggal Kegiatan:</strong> ${moment(assignment.start).format('dddd, D MMMM YYYY')} s/d ${moment(assignment.end).format('dddd, D MMMM YYYY')}</p>
                        <p><strong>Deskripsi:</strong> ${assignment.description}</p>
                    </div>`;
                    });
                    modalBodyContent += '</div>';
                } else if (assignments.length === 1) {
                    let assignment = assignments[0];
                    // console.log(assignment)
                    modalBodyContent += `
                <p><strong>Penugasan:</strong> ${assignment.title}</p>
                <p><strong>Tipe Penugasan:</strong> ${assignment.type}</p>
                <p><strong>Atas nama:</strong> ${nama}</p>`;
                    if (assignment.start === assignment.end) {
                        modalBodyContent +=
                            `<p><strong>Tanggal Kegiatan:</strong> ${moment(assignment.start).format('dddd, D MMMM YYYY')}</p>`;
                    } else {
                        modalBodyContent +=
                            `<p><strong>Tanggal Kegiatan:</strong> ${moment(assignment.start).format('dddd, D MMMM YYYY')} s/d ${moment(assignment.end).format('dddd, D MMMM YYYY')}</p>`;
                    }
                    modalBodyContent += `<p><strong>Deskripsi:</strong> ${assignment.description}</p>`;
                } else {
                    modalBodyContent = '<p>Tidak ada penugasan pada tanggal ini.</p>';
                }

                $('#modalBodyContent').html(modalBodyContent);
                $('#assignmentModal').modal('show');
            });

            function stripHtml(html) {
                var temporalDivElement = document.createElement("div");
                temporalDivElement.innerHTML = html;
                return temporalDivElement.textContent || temporalDivElement.innerText || "";
            }

            function formatRupiah(angka, prefix) {
                var numberString = angka.toString().replace(/[^,\d]/g, ''),
                    split = numberString.split(','),
                    sisa = split[0].length % 3,
                    rupiah = split[0].substr(0, sisa),
                    ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                if (ribuan) {
                    separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }

                rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
                return prefix == undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : '');
            }
        </script>
    @endpush
@endsection
