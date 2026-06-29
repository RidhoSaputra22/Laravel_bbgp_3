@extends('layouts.app', ['title' => 'Data Peserta Kegiatan'])

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
                <h1>Data Peserta Kegiatan BBGTK</h1>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <!-- Navigation Buttons -->

                                <a href="{{ route('peserta.create') }}" class="btn btn-primary text-white my-3">+ Tambah
                                    Peserta</a>

                                <h6>Filter By </h6>
                                <div class="row">
                                    <div class="col-md-3">

                                        <div class="form-group">
                                            <select name="" class="form-control" id="kegiatanSelect">
                                                <option value="">-- pilih kegiatan --</option>
                                                @foreach ($kegiatan as $v)
                                                    <?php
                                                    setlocale(LC_TIME, 'id_ID.UTF-8');
                                                    
                                                    $tgl_kegiatan = strftime('%d %B', strtotime($v->tgl_kegiatan));
                                                    $tgl_selesai = strftime('%d %B %Y', strtotime($v->tgl_selesai));
                                                    ?>
                                                    <option data-id="{{ $v->id }}" value="{{ $v->nama_kegiatan }}">
                                                        {{ $v->nama_kegiatan }}
                                                        {{-- ( {{ $tgl_kegiatan }} -
                                                        {{ $tgl_selesai }}
                                                       ) --}}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 ">
                                        <div class="form-group">
                                            <select name="" class="form-control select2" id="kabupatenSelect">
                                                <option value="">-- pilih kabupaten/kota --</option>
                                                @foreach ($kabupaten as $v)
                                                    <option value="{{ $v->name }}">{{ $v->name }}
                                                        {{-- ( {{ $tgl_kegiatan }} -
                                                        {{ $tgl_selesai }}
                                                       ) --}}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        {{-- <div id="btnGroup">
                                            <button id="btnPrintPeserta" class="btn btn-primary"><i
                                                    class="fas fa-print mr-2"></i>Absensi Peserta</button>
                                            <button id="btnPrintRegisPeserta" class="btn btn-primary"><i
                                                    class="fas fa-print mr-2"></i>Registrasi Peserta</button>

                                            <button id="btnPrintPanitia" class="btn btn-info"><i
                                                    class="fas fa-print mr-2"></i>Absensi Panitia</button>
                                            <button id="btnPrintNarsum" class="btn btn-warning"><i
                                                    class="fas fa-print mr-2"></i>Absensi Narasumber</button>
                                        </div> --}}
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
                                <div id="export-section">
                                    <h6>Export Data</h6>
                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <a href="#" id="exportBtn" class="btn btn-success">
                                                <i class="fas fa-print mr-2"></i>
                                                Export Partisipan</a>
                                        </div>
                                    </div>
                                </div>
                                <!-- Tables Section -->
                                <!-- PPNPN -->
                                <div class="table-responsive ">
                                    <!-- Table PPNPN -->
                                    <table class="table table-striped " id="table-kegiatan">
                                        <thead>
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th>NIK</th>
                                                <th>Nama </th>
                                                <th class="text-nowrap">Asal kabupaten/kota </th>
                                                <th>Status Keikutpesertaan</th>
                                                <th>Nama Kegiatan</th>
                                                <th>Instansi</th>
                                                <th>Jenis Golongan</th>
                                                <th>Golongan</th>
                                                <th>Kontak</th>
                                                <th class="text-nowrap">Cetak Biodata</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($datas as $i => $data)
                                                <tr data-id="{{ $data->id }}">
                                                    <td>{{ ++$i }}</td>
                                                    <td>{{ $data->no_ktp ?? '' }}</td>
                                                    <td>{{ $data->nama ?? '' }}</td>
                                                    <td class="text-nowrap">{{ $data->kabupaten ?? '' }}</td>
                                                    <td>{{ $data->status_keikutpesertaan ?? '' }}</td>
                                                    <td><b> {{ $data->kegiatan->nama_kegiatan ?? '' }} </b></td>
                                                    <td>{{ $data->instansi }}</td>
                                                    <td>{{ $data->jenis_gol }}</td>
                                                    <td>{{ $data->golongan }}</td>
                                                    <td>No : Hp {{ $data->no_hp }}
                                                        <br>
                                                        No : WA {{ $data->no_wa }}
                                                    </td>
                                                    <td>
                                                        <a target="_blank" href="{{ route('peserta.cetak', $data->id) }}"
                                                            class="btn btn-primary">
                                                            <i class="fas fa-print"></i>
                                                        </a>
                                                    </td>

                                                    <td>
                                                        <a href="{{ route('peserta.edit', $data->id) }}"
                                                            class="btn btn-warning my-2"><i class="fas fa-edit"></i></a>
                                                        <button onclick="deleteData({{ $data->id }}, 'peserta')"
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

                const exportBtn = $('#export-section');
                const kegiatan = document.querySelector('#kegiatanSelect');

                exportBtn.hide();

                function applySearch() {
                    exportBtn.show();
                    const kegiatanValue = kegiatan.value;

                    if (kegiatanValue == '') {
                        exportBtn.hide();
                    }

                    console.log('Search Text:', kegiatanValue);

                    tableKegiatan.column(5).search(kegiatanValue).draw();

                    var kegiatanId = $('#kegiatanSelect').find(':selected').attr('data-id')

                    console.log(kegiatanId);
                    // Construct the URL with the collected row IDs and kegiatanId
                    var url = '{{ route('peserta.export', ['id_kegiatan' => ':id']) }}'
                    url = url.replace(':id', kegiatanId)

                     $.ajax({
                        url: url, // Ganti dengan route yang sesuai untuk mengambil status
                        type: 'GET',
                        success: function(response) {
                            var url = '{{ route('peserta.export', ['id_kegiatan' => ':id']) }}'
                            url = url.replace(':id', kegiatanId)
                            $('#exportBtn').attr({
                                'href': url
                            });
                            console.log('sukses cetak');
                            // console.log(response.status);
                            // console.log(response);
                        },
                        error: function(error) {
                            console.error(error);
                            alert('Error fetching'.error);
                        }
                    });

                }

                kegiatan.addEventListener('change', applySearch);

                kegiatan.on('change', function(e) {
                    e.preventDefault();
                    var kegiatanId = $('#kegiatanSelect').find(':selected').attr('data-id')

                    console.log(kegiatanId);
                    // Construct the URL with the collected row IDs and kegiatanId
                    var url = '{{ route('peserta.export', ['id_kegiatan' => ':id']) }}'
                    url = url.replace(':id', kegiatanId)

                    $.ajax({
                        url: url, // Ganti dengan route yang sesuai untuk mengambil status
                        type: 'GET',
                        success: function(response) {
                            var url = '{{ route('peserta.export', ['id_kegiatan' => ':id']) }}'
                            url = url.replace(':id', kegiatanId)
                            $('#exportBtn').attr({
                                'href': url
                            });
                            console.log('sukses cetak');
                            // console.log(response.status);
                            // console.log(response);
                        },
                        error: function(error) {
                            console.error(error);
                            alert('Error fetching'.error);
                        }
                    });

                });

                $('#kabupatenSelect').on('change', () => {
                    const kab = document.querySelector('#kabupatenSelect');
                    console.log(kab.value);
                    tableKegiatan.column(3).search(kab.value).draw();
                })



            });
        </script>
    @endpush
@endsection
