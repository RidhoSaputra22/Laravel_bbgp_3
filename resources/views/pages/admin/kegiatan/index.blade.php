@extends('layouts.app', ['title' => 'Data Kegiatan'])

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
                <h1>Data Kegiatan BBGTK</h1>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <!-- Navigation Buttons -->

                                <a href="{{ route('kegiatan.create') }}" class="btn btn-primary text-white my-3">+ Tambah
                                    Kegiatan</a>

                                <h6>Print Absensi</h6>
                                <div class="row">
                                    <div class="col-md-3">

                                        <div class="form-group">
                                            <select name="" class="form-control" id="kegiatanSelect">
                                                <option value="">-- pilih kegiatan --</option>
                                                @foreach ($datas as $v)
                                                    <?php
                                                    setlocale(LC_TIME, 'id_ID.UTF-8');
                                                    
                                                    $tgl_kegiatan = strftime('%d %B', strtotime($v->tgl_kegiatan));
                                                    $tgl_selesai = strftime('%d %B %Y', strtotime($v->tgl_selesai));
                                                    ?>
                                                    <option value="{{ $v->id }}">{{ $v->nama_kegiatan }}
                                                        {{-- ( {{ $tgl_kegiatan }} -
                                                        {{ $tgl_selesai }}
                                                       ) --}}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md mb-4">
                                        <div id="btnGroup">
                                            <button id="btnPrintPeserta" class="btn btn-primary"><i
                                                    class="fas fa-print mr-2"></i>Absensi Peserta</button>
                                            <button id="btnPrintRegisPeserta" class="btn btn-primary"><i
                                                    class="fas fa-print mr-2"></i>Registrasi Peserta</button>

                                            <button id="btnPrintPanitia" class="btn btn-info"><i
                                                    class="fas fa-print mr-2"></i>Absensi Panitia</button>
                                            <button id="btnPrintNarsum" class="btn btn-warning"><i
                                                    class="fas fa-print mr-2"></i>Absensi Narasumber</button>


                                        </div>
                                    </div>


                                </div>

                                <div class="row mb-3">
                                    <div class="col-md">
                                        <div class="mt-2">
                                            <button id="btnPrintTP" class="btn btn-primary"><i
                                                    class="fas fa-chalkboard-teacher mr-1"></i>Absensi Tenaga
                                                Pendidik</button>
                                            <button id="btnPrintTKP" class="btn btn-info"> <i
                                                    class="fas fa-school mr-1"></i>Absensi Tenaga Kependidikan</button>

                                            <button id="btnPrintSTK" class="btn btn-success"><i
                                                    class="fas fa-layer-group mr-1">></i>Absensi
                                                Stakeholder</button>
                                            <button id="btnPrintPGW" class="btn btn-warning"><i
                                                    class="fas fa-print mr-2"></i>Absensi Pegawai BBGTK</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Filter Section -->
                                {{-- <h5>Pencarian Data Kegiatan BBGTK</h5>
                                <div class="row mb-2"> 
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <input name="nama" id="namaFilter" type="text"
                                                placeholder="Masukkan nama anda" class="form-control">
                                        </div>
                                    </div>
                                </div> --}}

                                <!-- Filter Data Kegiatan -->
                                {{-- <h5>Filter Data Kegiatan</h5>
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
                                <!-- PPNPN -->
                                <div class="table-responsive ">
                                    <!-- Table PPNPN -->
                                    <table class="table table-striped " id="table-kegiatan">
                                        <thead>
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th>Nama Kegiatan</th>
                                                <th>Tempat Kegiatan</th>
                                                <th>Tanggal Kegiatan</th>
                                                <th>Jam kegiatan</th>
                                                <th>Keterangan Kegiatan</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($datas as $i => $data)
                                                <tr>
                                                    <td>{{ ++$i }}</td>
                                                    <td>{{ $data->nama_kegiatan ?? '' }}</td>
                                                    <td>{{ $data->tempat_kegiatan ?? '' }}</td>
                                                    <td>{{ $data->tgl_kegiatan . ' - ' . $data->tgl_selesai ?? '' }}</td>
                                                    <td>{{ $data->jam_mulai }} - {{ $data->jam_selesai }} WITA</td>
                                                    <td>{!! Str::limit($data->deskripsi_kegiatan ?? '', 100) !!}</td>
                                                    <td>
                                                        @if ($data->status == 'true')
                                                            <span class="badge badge-success">Aktif</span>
                                                        @else
                                                            <span class="badge badge-danger">Non-Aktif</span>
                                                        @endif

                                                    </td>
                                                    <td>
                                                        <a href="{{ route('kegiatan.edit', $data->id) }}"
                                                            class="btn btn-warning my-2"><i class="fas fa-edit"></i></a>
                                                        <button onclick="deleteData({{ $data->id }}, 'kegiatan')"
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




    @push('scripts')
        <script src="{{ asset('library/datatables/media/js/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('library/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('library/datatables.net-select-bs4/js/select.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('js/page/modules-datatables.js') }}"></script>

        <script type="text/javascript">
            $(document).ready(function() {
                // Existing DataTable initialization
                var language = {
                    "sSearch": "Pencarian Data Kegiatan BBGTK : ",
                };
                var tableKegiatan = $('#table-kegiatan').DataTable({
                    paging: true,
                    searching: true,
                    language: language,
                });

                // Button click event handlers
                $('#btnPrintPeserta').on('click', function() {
                    handlePrint('peserta');
                });

                $('#btnPrintRegisPeserta').on('click', function() {
                    handlePrint('regis_peserta');
                });

                $('#btnPrintPanitia').on('click', function() {
                    handlePrint('panitia');
                });

                $('#btnPrintNarsum').on('click', function() {
                    handlePrint('narsum');
                });

                $('#btnPrintTP').on('click', function() {
                    handlePrint('tp');
                });

                $('#btnPrintTKP').on('click', function() {
                    handlePrint('tkp');
                });

                $('#btnPrintSTK').on('click', function() {
                    handlePrint('stk');
                });

                $('#btnPrintPGW').on('click', function() {
                    handlePrint('pgw');
                });

                function handlePrint(type) {
                    var kegiatanId = $('#kegiatanSelect').val();
                    if (kegiatanId === '') {
                        swal('Warning', 'Silakan pilih kegiatan terlebih dahulu!', 'warning');
                        return;
                    }

                    var printUrl = '';

                    switch (type) {
                        case 'peserta':
                            printUrl = '{{ route('print.absensi.peserta') }}' + '?kegiatan_id=' + kegiatanId;
                            break;
                        case 'regis_peserta':
                            printUrl = '{{ route('print.registrasi.peserta') }}' + '?kegiatan_id=' + kegiatanId;
                            break;
                        case 'panitia':
                            printUrl = '{{ route('print.absensi.panitia') }}' + '?kegiatan_id=' + kegiatanId;
                            break;
                        case 'narsum':
                            printUrl = '{{ route('print.absensi.narasumber') }}' + '?kegiatan_id=' + kegiatanId;
                            break;
                        case 'tp':
                            printUrl = '{{ route('print.absensi.tp') }}' + '?kegiatan_id=' + kegiatanId;
                            break;
                        case 'tkp':
                            printUrl = '{{ route('print.absensi.tkp') }}' + '?kegiatan_id=' + kegiatanId;
                            break;
                        case 'stk':
                            printUrl = '{{ route('print.absensi.stk') }}' + '?kegiatan_id=' + kegiatanId;
                            break;
                        case 'pgw':
                            printUrl = '{{ route('print.absensi.pgw') }}' + '?kegiatan_id=' + kegiatanId;
                            break;
                    }

                    window.open(printUrl, '_blank');
                }
            });
        </script>
    @endpush
@endsection
