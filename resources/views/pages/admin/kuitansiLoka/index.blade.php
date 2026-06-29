@extends('layouts.app', ['title' => 'Data Kuitansi Lokakarya'])

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
                <h1>Data Kuitansi Lokakarya BBGTK</h1>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <!-- Navigation Buttons -->
                        <div class="row">
                            <div class="col">
                                <div class="d-flex mb-3">
                                    <div class="row mx-2">
                                        <div class="mr-3">
                                            <button id="btnShowKegiatan" class="btn btn-primary">Data Kegiatan
                                                Lokakarya</button>
                                        </div>
                                        <div class="">
                                            <button id="btnShowKuitansi" class="btn btn-success">Data Kuitansi
                                                Lokakarya</button>
                                        </div>

                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5>Data Kegiatan Lokakarya</h5>
                            </div>
                            <div class="card-body">


                                <div class="row">
                                    <div class="col-md-4">


                                    </div>
                                    {{-- {{ dd($title) }} --}}
                                    {{-- <div class="col-md-3">
                                        <div class="form-group">
                                            <select name="" class="form-control" id="kegiatanSelect">
                                                <option value="">-- pilih kegiatan --</option>
                                                @foreach ($datas as $v)
                                                    <?php
                                                    setlocale(LC_TIME, 'id_ID.UTF-8');
                                                    $tgl_kegiatan = strftime('%d %B', strtotime($v->tgl_kegiatan));
                                                    $tgl_selesai = strftime('%d %B %Y', strtotime($v->tgl_selesai));
                                                    ?>
                                                    <option value="{{ $v->id }}">{{ $v->nama_kegiatan }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div> --}}

                                </div>



                                <div class="table-responsive">
                                    <table class="table table-hover table-striped" id="table_kuitansi">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Nama Kegiatan</th>
                                                <th>Lokasi Kegiatan</th>
                                                <th class="text-nowrap">Tanggal Kegiatan</th>
                                                <th>Action</th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($datas as $i => $v)
                                                <?php
                                                setlocale(LC_TIME, 'id_ID.UTF-8');
                                                
                                                $tgl_kegiatan = strftime('%d %B %Y', strtotime($v['tgl_kegiatan']));
                                                ?>
                                                <tr>
                                                    <td>{{ ++$i }}</td>
                                                    <td class="text-nowrap">{{ $v['kegiatan'] }}</td>
                                                    <td class="text-nowrap">{{ $v['kota'] }}</td>
                                                    <td class="text-nowrap">{{ $tgl_kegiatan }}</td>
                                                    <td class="text-nowrap">
                                                        <a href="#" class="btn btn-primary my-2" data-toggle="modal"
                                                            data-target="#pegawaiModal" data-kegiatan="{{ $v['kegiatan'] }}"
                                                            data-pegawai="{{ json_encode($v['penugasan_pegawai']) }}">
                                                            Daftar Pegawai
                                                        </a>
                                                    </td>

                                                </tr>
                                            @endforeach
                                            <!-- Daftar pegawai akan dimasukkan di sini oleh JavaScript -->
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5>Data Kuitansi Lokakarya</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-5">
                                        <h6>Filter By Kegiatan </h6>

                                        <div class="form-group">
                                            <select name="" class="form-control select2" id="kegiatanSelect">
                                                <option value="">-- pilih kegiatan --</option>
                                                @foreach ($datas as $v)
                                                    <?php
                                                    // setlocale(LC_TIME, 'id_ID.UTF-8');
                                                    
                                                    // $tgl_kegiatan = strftime('%d %B', strtotime($v->tgl_kegiatan));
                                                    // $tgl_selesai = strftime('%d %B %Y', strtotime($v->tgl_selesai));
                                                    ?>
                                                    <option data-id="{{ $v['id'] }}" value="{{ $v['kegiatan'] }}">{{ $v['kegiatan'] }}
                                                        {{-- ( {{ $tgl_kegiatan }} -
                                                        {{ $tgl_selesai }}
                                                    ) --}}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>


                                    {{-- <div class="col-md-3">

                                        <div class="form-group">
                                            <select name="" class="form-control" id="jabatanKegiatan">
                                                <option value="">-- pilih status keikutsertaan --</option>
                                                <option value="peserta">Peserta</option>
                                                <option value="panitia">Panitia</option>
                                                <option value="narasumber">Narasumber</option>

                                            </select>
                                        </div>
                                    </div> --}}


                                </div>



                                <div id="btnGroup">
                                    <div class="row">
                                        {{-- <div class="col-md-9">
                                            <h6>Print semua data dari :</h6>
                                            <a href="#" id="printAllKuitansi" class="btn btn-info"><i
                                                    class="fas fa-print mr-2"></i>Kuitansi </a>

                                            <a href="#" id="printAllRill" class="btn btn-info"><i
                                                    class="fas fa-print mr-2"></i>Pengeluaran Rill Peserta </a>

                                            <a href="#" id="printAllPJ" class="btn btn-info"><i
                                                    class="fas fa-print mr-2"></i>PJ Mutlak Peserta</a>

                                            <a href="#" id="printAllAmplop" class="btn btn-info"><i
                                                    class="fas fa-print mr-2"></i>Amplop Peserta</a>
                                        </div> --}}
                                    </div>



                                    {{-- <div class="row">

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Nomor Surat</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control"
                                                        placeholder="masukkan nomor surat" id="no_surat" name="no_surat">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Tanggal Surat</label>
                                                <div class="input-group">
                                                    <input type="date" class="form-control" id="tgl_surat"
                                                        name="tgl_surat">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Kode Anggaran</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control"
                                                        placeholder="masukkan kode anggaran" id="kode_anggaran"
                                                        name="kode_anggaran">
                                                </div>
                                            </div>
                                        </div>

                                    </div> --}}


                                </div>


                                <div class="table-responsive">
                                    <table class="table table-hover table-striped" id="table_kuitansi_lokakarya">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Nama Pegawai</th>
                                                <th>Kegiatan</th>
                                                <th>Lokasi</th>
                                                <th>Hotel</th>
                                                <th>Nomor Bukti</th>
                                                <th class="text-nowrap">Nomor dan Tanggal Surat Tugas</th>
                                                <th class="text-nowrap">Kode dan Tahun Anggaran</th>
                                                <th>Cetak</th>
                                                <th>Action</th>
                                                {{-- <th>Transport Pergi</th>
                                                    <th>Transport Pulang</th>
                                                    <th>Bill Penginapan</th>
                                                    <th>Hari 1</th>
                                                    <th>Hari 2</th>
                                                    <th>Hari 3</th>
                                                    <th>Total</th> --}}
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($kuitansiLoka as $i => $v)
                                                @php
                                                    setlocale(LC_ALL, 'id_ID.UTF-8');

                                                    $tgl_surat = strftime('%d %B %Y', strtotime($v->tgl_surat_tugas));
                                                @endphp
                                                <tr data-id="{{ $v->id }}">
                                                    <td class="text-nowrap">{{ ++$i }}</td>
                                                    <td class="text-nowrap">{{ $v->internal->nama }}</td>
                                                    <td class="text-nowrap">{{ $v->internal->kegiatan }}</td>
                                                    <td class="">{{ $v->internal->kota }} </td>
                                                    <td class="">{{ $v->internal->hotel }} </td>
                                                    <td class="">{{ $v->no_bukti }} </td>
                                                    <td class="text-nowrap">{{ $v->no_surat_tugas }} tanggal
                                                        {{ $tgl_surat }}</td>
                                                    <td class="text-nowrap">{{ $v->kode_anggaran }} -
                                                        {{ $v->tahun_anggaran }}</td>
                                                    <td class="">
                                                        <div class="">

                                                            <a target="_blank"
                                                                href="{{ route('kuitansiLoka.cetak', $v->id) }}"
                                                                class="btn btn-info "> <i class="fas fa-print"></i>
                                                                Kuitansi
                                                            </a>


                                                            <a target="_blank"
                                                                href="{{ route('kuitansiLoka.cetakRill', $v->id) }}"
                                                                class="btn btn-info my-2"> <i class="fas fa-print"></i>
                                                                Pengeluaran Rill </a>
                                                        </div>

                                                        <div class="">
                                                            <a target="_blank"
                                                                href="{{ route('kuitansiLoka.cetakPJmutlak', $v->id) }}"
                                                                class="btn btn-info "> <i class="fas fa-print"></i> PJ
                                                                Mutlak
                                                            </a>

                                                            <a target="_blank"
                                                                href="{{ route('kuitansiLoka.cetakAmplop', $v->id) }}"
                                                                class="btn btn-info my-2"> <i class="fas fa-print"></i>
                                                                Amplop </a>

                                                        </div>
                                                    </td>
                                                    <td class="">
                                                        {{-- <button class="btn btn-primary btn-detail"
                                                                data-id="{{ $v->id }}">
                                                                <i class="fas fa-eye"></i>
                                                            </button> --}}

                                                        <a href="#" class="btn btn-warning btn-edit-kuitansi my-2"
                                                            data-id="{{ $v->id }}"
                                                            data-pegawai="{{ $v->internal->nama }}"
                                                            data-hotel="{{ $v->internal->hotel }}"
                                                            data-transportpergi="{{ $v->internal->transport_pergi }}"
                                                            data-transportpulang="{{ $v->internal->transport_pulang }}"
                                                            data-billpenginapan="{{ $v->internal->bill_penginapan }}"
                                                            data-hari1="{{ $v->internal->hari_1 }}"
                                                            data-hari2="{{ $v->internal->hari_2 }}"
                                                            data-hari3="{{ $v->internal->hari_3 }}"
                                                            data-hari4="{{ $v->internal->hari_4 }}"
                                                            data-hari5="{{ $v->internal->hari_5 }}"
                                                            data-hari6="{{ $v->internal->hari_6 }}"
                                                            data-hari7="{{ $v->internal->hari_7 }}"
                                                            data-total="{{ $v->internal->total }}"
                                                            data-nosurattugas="{{ $v->no_surat_tugas }}"
                                                            data-tglsurattugas="{{ $v->tgl_surat_tugas }}"
                                                            data-kodeanggaran="{{ $v->kode_anggaran }}"
                                                            data-tahunanggaran="{{ $v->tahun_anggaran }}"
                                                            data-nobukti="{{ $v->no_bukti }}">
                                                            <i class="fas fa-edit"></i>
                                                        </a>

                                                        <button onclick="deleteData({{ $v->id }}, 'kuitansiLoka')"
                                                            class="btn btn-danger "><i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </td>
                                                    {{-- <td class="text-nowrap">Rp.
                                                            {{ number_format($v->internal->transport_pergi ?? 0, 0, ',', '.') }}
                                                        </td>
                                                        <td class="text-nowrap">Rp.
                                                            {{ number_format($v->internal->transport_pulang ?? 0, 0, ',', '.') }}
                                                        </td>
                                                        <td class="text-nowrap">Rp.
                                                            {{ number_format($v->internal->bill_penginapan ?? 0, 0, ',', '.') }}
                                                        </td>
                                                        <td class="text-nowrap">Rp.
                                                            {{ number_format($v->internal->hari_1 ?? 0, 0, ',', '.') }}</td>
                                                        <td class="text-nowrap">Rp.
                                                            {{ number_format($v->internal->hari_2 ?? 0, 0, ',', '.') }}</td>
                                                        <td class="text-nowrap">Rp.
                                                            {{ number_format($v->internal->hari_3 ?? 0, 0, ',', '.') }}</td>
                                                        <td class="text-nowrap">Rp.
                                                            {{ number_format($v->internal->transport_pergi ?? 0, 0, ',', '.') }}
                                                        </td> --}}
                                                </tr>
                                            @endforeach
                                            <!-- Populate this table with data from the server or use JavaScript to add rows -->
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    </div>


                </div>
            </div>
    </div>
    </section>
    </div>

    <div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel">Detail Kuitansi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Tempat untuk menampilkan detail kuitansi -->
                    <div id="detailContent">
                        <!-- Konten detail kuitansi akan dimuat di sini -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal untuk Daftar Pegawai -->
    <div class="modal fade" id="pegawaiModal" tabindex="-1" role="dialog" aria-labelledby="pegawaiModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl " role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="mr-4">Daftar Pegawai </h5>
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

    <!-- Modal for Creating Kuitansi -->
    <div class="modal fade" id="createKuitansiModal" tabindex="-1" role="dialog"
        aria-labelledby="createKuitansiModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createKuitansiModalLabel">Buat Kuitansi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="createKuitansiForm" action="{{ route('kuitansiLoka.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id" id="idPegawai">
                    <div class="modal-body">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pegawaiName">Nomor Surat Tugas</label>
                                    <input required type="text" class="form-control" name="no_surat_tugas">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="hotel">Tanggal Surat Tugas</label>
                                    <input required type="date" class="form-control" name="tgl_surat_tugas">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pegawaiName">Kode Anggaran</label>
                                    <input required type="text" class="form-control" name="kode_anggaran">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="hotel">Tanggal Surat Tugas</label>
                                    <input required type="text" class="form-control" id="tahun_anggaran"
                                        name="tahun_anggaran">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="hotel">Nomor Bukti</label>
                                    <input required type="text" class="form-control" id="no_bukti"
                                        name="no_bukti">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pegawaiName">Nama Pegawai</label>
                                    <input type="text" class="form-control" id="pegawaiName" name="pegawaiName"
                                        readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="hotel">Hotel</label>
                                    <input type="text" class="form-control" id="hotel" name="hotel" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="transportPergi">Biaya Pergi</label>
                                    <input type="text" class="form-control" id="transportPergi"
                                        name="transport_pergi" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="transportPulang">Biaya Pulang</label>
                                    <input type="text" class="form-control" id="transportPulang"
                                        name="transport_pulang" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="billPenginapan">Bill Penginapan</label>
                                    <input type="text" class="form-control" id="billPenginapan"
                                        name="bill_penginapan" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="hari1">Hari 1</label>
                                    <input type="text" class="form-control" id="hari1" name="hari_1" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="hari2">Hari 2</label>
                                    <input type="text" class="form-control" id="hari2" name="hari_2" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="hari3">Hari 3</label>
                                    <input type="text" class="form-control" id="hari3" name="hari_3" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="hari4">Hari 4</label>
                                    <input type="text" class="form-control" id="hari4" name="hari_4" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="hari5">Hari 5</label>
                                    <input type="text" class="form-control" id="hari5" name="hari_5" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="hari6">Hari 6</label>
                                    <input type="text" class="form-control" id="hari6" name="hari_6" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="hari7">Hari 7</label>
                                    <input type="text" class="form-control" id="hari7" name="hari_7" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="totalAmount">Total</label>
                            <input type="text" class="form-control" id="totalAmount" name="total" readonly>
                        </div>
                        <!-- Add more fields as needed -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Buat Kuitansi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal for Editing Kuitansi -->
    <div class="modal fade" id="editKuitansiModal" tabindex="-1" role="dialog"
        aria-labelledby="editKuitansiModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editKuitansiModalLabel">Edit Kuitansi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editKuitansiForm" action="{{ route('kuitansiLoka.update', ['id' => '__ID__']) }}"
                    method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" id="editIdPegawai">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="editNoSuratTugas">Nomor Surat Tugas</label>
                                    <input required type="text" class="form-control" id="editNoSuratTugas"
                                        name="no_surat_tugas">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="editTglSuratTugas">Tanggal Surat Tugas</label>
                                    <input required type="date" class="form-control" id="editTglSuratTugas"
                                        name="tgl_surat_tugas">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="editKodeAnggaran">Kode Anggaran</label>
                                    <input required type="text" class="form-control" id="editKodeAnggaran"
                                        name="kode_anggaran">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="editTahunAnggaran">Tahun Anggaran</label>
                                    <input required type="text" class="form-control" id="editTahunAnggaran"
                                        name="tahun_anggaran">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="hotel">Nomor Bukti</label>
                                    <input required type="text" class="form-control" id="editNoBukti"
                                        name="no_bukti">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="editPegawaiName">Nama Pegawai</label>
                                    <input type="text" class="form-control" id="editPegawaiName" name="pegawaiName"
                                        readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="editHotel">Hotel</label>
                                    <input type="text" class="form-control" id="editHotel" name="hotel" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="editTransportPergi">Biaya Pergi</label>
                                    <input type="text" class="form-control" id="editTransportPergi"
                                        name="transport_pergi" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="editTransportPulang">Biaya Pulang</label>
                                    <input type="text" class="form-control" id="editTransportPulang"
                                        name="transport_pulang" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="editBillPenginapan">Bill Penginapan</label>
                                    <input type="text" class="form-control" id="editBillPenginapan"
                                        name="bill_penginapan" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="editHari1">Hari 1</label>
                                    <input type="text" class="form-control" id="editHari1" name="hari_1" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="editHari2">Hari 2</label>
                                    <input type="text" class="form-control" id="editHari2" name="hari_2" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="editHari3">Hari 3</label>
                                    <input type="text" class="form-control" id="editHari3" name="hari_3" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="editHari4">Hari 4</label>
                                    <input type="text" class="form-control" id="editHari4" name="hari_4" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="editHari5">Hari 5</label>
                                    <input type="text" class="form-control" id="editHari5" name="hari_5" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="editHari6">Hari 6</label>
                                    <input type="text" class="form-control" id="editHari6" name="hari_6" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="editHari7">Hari 7</label>
                                    <input type="text" class="form-control" id="editHari7" name="hari_7" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="editTotalAmount">Total</label>
                            <input type="text" class="form-control" id="editTotalAmount" name="total" readonly>
                        </div>
                        <!-- Add more fields as needed -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Update Kuitansi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>




    @push('scripts')
        <script src="{{ asset('library/datatables/media/js/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('library/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('library/datatables.net-select-bs4/js/select.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('js/page/modules-datatables.js') }}"></script>

        <script type="text/javascript">
            $(document).ready(function() {
                var tableKuitansi = $('#table_kuitansi').DataTable();
                var tableKuitansiLokakarya = $('#table_kuitansi_lokakarya').DataTable();

                var btnGroup = $('#btnGroup').hide();

                var kegiatan = '';
                var jabatan = '';

                var $tableKegiatan = $('#table_kuitansi').closest('.card');
                var $tableKuitansi = $('#table_kuitansi_lokakarya').closest('.card').hide();

                $('#btnShowKegiatan').click(function() {
                    $tableKegiatan.show();
                    $tableKuitansi.hide();
                });

                $('#btnShowKuitansi').click(function() {
                    $tableKegiatan.hide();
                    $tableKuitansi.show();
                });

                $('#kegiatanSelect').on('change', function(e) {
                    e.preventDefault();
                    kegiatanValue = $(this).val();
                    if (kegiatanValue == '') {
                        btnGroup.hide();
                        return;
                    }
                    console.log(kegiatanValue);

                    tableKuitansiLokakarya.column(2).search(kegiatanValue).draw();
                    kegiatan = kegiatanValue;
                    btnGroup.show();

                    // var totalFilteredRows = tableKuitansi.rows({
                    //     search: 'applied'
                    // }).count();
                    // console.log("Total number of filtered rows: " + totalFilteredRows);
                });

                $('#jabatanKegiatan').on('change', function(e) {
                    e.preventDefault();
                    jabatan = $(this).val();
                    console.log(jabatan);

                    tableKuitansi.column(5).search(jabatan).draw();
                });

                $('#printKuitansi').on('click', function(e) {
                    e.preventDefault();
                    let no_surat = $('#no_surat').val();
                    let tgl_surat = $('#tgl_surat').val();
                    let kode_anggaran = $('#kode_anggaran').val();

                    swal({
                            title: 'Apakah anda sudah yakin?',
                            text: 'pastikan anda sudah mengisi Nomor, Tanggal Surat dan Kode Anggaran ',
                            icon: 'warning',
                            buttons: true,
                            dangerMode: true,
                        })
                        .then((willDelete) => {
                            if (willDelete) {
                                if (no_surat == '' || tgl_surat == '' || kode_anggaran == '') {
                                    swal('Nomor, Tanggal Surat atau Kode Anggaran Tidak Valid / Tidak boleh kosong', {
                                        icon: 'error',
                                    });
                                    return;
                                }

                                $.ajax({
                                    url: '{{ route('kuitansi.storeNomor') }}',
                                    type: 'GET',
                                    data: {
                                        no_surat: no_surat,
                                        tgl_surat: tgl_surat,
                                        kode_anggaran: kode_anggaran,
                                        kegiatan_id: kegiatan,
                                    },
                                    success: function(response) {
                                        console.log(kegiatan);

                                        // Construct the URL dynamically
                                        var printUrl =
                                            '{{ route('kuitansi.cetakexcel', ['id_kegiatan' => '__KEGIATAN__']) }}'
                                            .replace('__KEGIATAN__', encodeURIComponent(
                                                kegiatan));

                                        window.location.href = printUrl;
                                    },
                                    error: function(error) {
                                        console.error(error);
                                        alert('Error fetching detail.');
                                    }
                                });
                            }
                        });
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
                    <td class="text-nowrap">${pegawaiItem.nama}</td>
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
                        <a href="#" class="btn btn-success btn-create-kuitansi" 
                        data-toggle="modal" data-target="#createKuitansiModal" 
                        data-pegawai="${pegawaiItem.nama}" 
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
                        data-hari7="${hari7}" 
                        data-id="${pegawaiItem.id}" 
                        data-total="${totalPerRow}">Buat Kuitansi</a>
                    </td>
                </tr>
            `);
                    });

                });

            });

            // tambah data
            $(document).on('click', '.btn-create-kuitansi', function() {
                var pegawaiName = $(this).data('pegawai');
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
                var id = $(this).data('id');
                var totalAmount = $(this).data('total');

                var currenyYear = new Date();
                // console.log(id ,transportPergi, transportPulang, billPenginapan, hari1, hari2, hari3)
                // Fill the form in the modal
                $('#tahun_anggaran').val(currenyYear.getFullYear());
                $('#pegawaiName').val(pegawaiName);
                $('#idPegawai').val(id);
                $('#hotel').val(hotel);
                $('#transportPergi').val(formatRupiah(transportPergi, 'Rp.'));
                $('#transportPulang').val(formatRupiah(transportPulang, 'Rp.'));
                $('#billPenginapan').val(formatRupiah(billPenginapan, 'Rp.'));
                $('#hari1').val(formatRupiah(hari1, 'Rp.'));
                $('#hari2').val(formatRupiah(hari2, 'Rp.'));
                $('#hari3').val(formatRupiah(hari3, 'Rp.'));
                $('#hari4').val(formatRupiah(hari4, 'Rp.'));
                $('#hari5').val(formatRupiah(hari5, 'Rp.'));
                $('#hari6').val(formatRupiah(hari6, 'Rp.'));
                $('#hari7').val(formatRupiah(hari7, 'Rp.'));
                $('#totalAmount').val(formatRupiah(totalAmount, 'Rp.'));
            });

            // update data
            // Handle "Edit Kuitansi" button click
            $(document).on('click', '.btn-edit-kuitansi', function() {
                var id = $(this).data('id');
                var pegawaiName = $(this).data('pegawai');
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
                var totalAmount = $(this).data('total');
                var noSuratTugas = $(this).data('nosurattugas');
                var tglSuratTugas = $(this).data('tglsurattugas');
                var kodeAnggaran = $(this).data('kodeanggaran');
                var tahunAnggaran = $(this).data('tahunanggaran');
                var noBukti = $(this).data('nobukti');
                console.log(tahunAnggaran)
                // Fill the form in the modal
                $('#editIdPegawai').val(id);
                $('#editPegawaiName').val(pegawaiName);
                $('#editHotel').val(hotel);
                $('#editTransportPergi').val(formatRupiah(transportPergi, 'Rp.'));
                $('#editTransportPulang').val(formatRupiah(transportPulang, 'Rp.'));
                $('#editBillPenginapan').val(formatRupiah(billPenginapan, 'Rp.'));
                $('#editHari1').val(formatRupiah(hari1, 'Rp.'));
                $('#editHari2').val(formatRupiah(hari2, 'Rp.'));
                $('#editHari3').val(formatRupiah(hari3, 'Rp.'));
                $('#editHari4').val(formatRupiah(hari4, 'Rp.'));
                $('#editHari5').val(formatRupiah(hari5, 'Rp.'));
                $('#editHari6').val(formatRupiah(hari6, 'Rp.'));
                $('#editHari7').val(formatRupiah(hari7, 'Rp.'));
                $('#editTotalAmount').val(formatRupiah(transportPergi + transportPulang + billPenginapan + hari1 +
                    hari2 + hari3 + hari4 + hari5 + hari6 + hari7, 'Rp.'));
                $('#editNoSuratTugas').val(noSuratTugas);
                $('#editTglSuratTugas').val(tglSuratTugas);
                $('#editKodeAnggaran').val(kodeAnggaran);
                $('#editTahunAnggaran').val(tahunAnggaran);
                $('#editNoBukti').val(noBukti);

                // Update the form action URL
                var updateUrl = '{{ route('kuitansiLoka.update', ['id' => '__ID__']) }}'.replace('__ID__', id);
                $('#editKuitansiForm').attr('action', updateUrl);

                // Show the modal
                $('#editKuitansiModal').modal('show');
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

        <script>
            $(document).ready(function() {
                $('.btn-detail').on('click', function() {
                    var kuitansiId = $(this).data('id');
                    $.ajax({
                        url: '{{ route('kuitansi.show', ['id' => ':kuitansiId']) }}'.replace(
                            ':kuitansiId', kuitansiId
                        ), // Ganti dengan rute yang sesuai untuk mengambil detail kuitansi
                        type: 'GET',
                        success: function(response) {
                            // Memuat konten detail kuitansi ke dalam modal
                            $('#detailContent').html(response);
                            $('#detailModal').modal('show');
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                        }
                    });
                });
            });
        </script>

        <script>
            $(document).ready(function() {
                $('#printAllKuitansi').click(function(e) {
                    e.preventDefault();
                    var kegiatanId = $('#kegiatanSelect').val();
                    var selectedOption = $('#kegiatanSelect').find('option:selected');
                    
                    var id = selectedOption.data('id');

                    console.log('halo',selectedOption.data('id'));

                    var rowIds = [];

                    // Collect the IDs of the rows you want to print
                    $('#table_kuitansi_lokakarya tbody tr').each(function() {
                        var rowId = $(this).data(
                            'id'); // Assuming the rows have data-id attribute with their ID
                        // console.log(rowId)
                        if (rowId) {
                            rowIds.push(rowId);
                        }
                    });

                    if (rowIds.length === 0) {
                        alert("No rows selected.");
                        return;
                    }

                    // Construct the URL with the collected row IDs and kegiatanId
                    var url = id ?
                        "{{ route('kuitansiLoka.cetakAll') }}?rows=" + rowIds.join(',') + "&kegiatan_id=" +
                        id :
                        "{{ route('kuitansiLoka.cetakAll') }}?rows=" + rowIds.join(',');

                    window.open(url, '_blank');
                });
            });
        </script>

        <script>
            $(document).ready(function() {
                $('#printAllRill').click(function(e) {
                    e.preventDefault();
                    var kegiatanId = $('#kegiatanSelect').val();
                    var selectedOption = $('#kegiatanSelect').find('option:selected');
                    
                    var id = selectedOption.data('id');
                    var rowIds = [];

                    // Collect the IDs of the rows you want to print
                    $('#table_kuitansi_lokakarya tbody tr').each(function() {
                        var rowId = $(this).data(
                            'id'); // Assuming the rows have data-id attribute with their ID
                        if (rowId) {
                            rowIds.push(rowId);
                        }
                    });

                    if (rowIds.length === 0) {
                        alert("No rows selected.");
                        return;
                    }

                    // Construct the URL with the collected row IDs and kegiatanId
                    var url = id ?
                        "{{ route('kuitansiLoka.cetakRillAll') }}?rows=" + rowIds.join(',') + "&kegiatan_id=" +
                        id :
                        "{{ route('kuitansiLoka.cetakRillAll') }}?rows=" + rowIds.join(',');

                    window.open(url, '_blank');
                });
            });
        </script>

        <script>
            $(document).ready(function() {
                $('#printAllPJ').click(function(e) {
                    e.preventDefault();
                    var kegiatanId = $('#kegiatanSelect').val();
                    var selectedOption = $('#kegiatanSelect').find('option:selected');
                    
                    var id = selectedOption.data('id');
                    var rowIds = [];

                    // Collect the IDs of the rows you want to print
                    $('#table_kuitansi_lokakarya tbody tr').each(function() {
                        var rowId = $(this).data(
                            'id'); // Assuming the rows have data-id attribute with their ID
                        if (rowId) {
                            rowIds.push(rowId);
                        }
                    });

                    if (rowIds.length === 0) {
                        alert("No rows selected.");
                        return;
                    }

                    // Construct the URL with the collected row IDs and kegiatanId
                    var url = id ?
                        "{{ route('kuitansiLoka.cetakPJmutlakAll') }}?rows=" + rowIds.join(',') +
                        "&kegiatan_id=" +
                        id :
                        "{{ route('kuitansiLoka.cetakPJmutlakAll') }}?rows=" + rowIds.join(',');

                    window.open(url, '_blank');
                });
            });
        </script>

        <script>
            $(document).ready(function() {
                $('#printAllAmplop').click(function(e) {
                    e.preventDefault();
                    var kegiatanId = $('#kegiatanSelect').val();
                    var rowIds = [];
                    var selectedOption = $('#kegiatanSelect').find('option:selected');
                    
                    var id = selectedOption.data('id');

                    // Collect the IDs of the rows you want to print
                    $('#table_kuitansi_lokakarya tbody tr').each(function() {
                        var rowId = $(this).data(
                            'id'); // Assuming the rows have data-id attribute with their ID
                        if (rowId) {
                            rowIds.push(rowId);
                        }
                    });

                    if (rowIds.length === 0) {
                        alert("No rows selected.");
                        return;
                    }

                    // Construct the URL with the collected row IDs and kegiatanId
                    var url = id ?
                        "{{ route('kuitansiLoka.cetakAmplopAll') }}?rows=" + rowIds.join(',') +
                        "&kegiatan_id=" +
                        id :
                        "{{ route('kuitansiLoka.cetakAmplopAll') }}?rows=" + rowIds.join(',');

                    window.open(url, '_blank');
                });
            });
        </script>
    @endpush
@endsection
