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
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>




                                </div>

                                <!-- Filter Section -->

                                <!-- Filter Data Kegiatan -->
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
                                        <tbody></tbody>
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
                const cetakUrl = @json(route('peserta.cetak', '__id__'));
                const editUrl = @json(route('peserta.edit', '__id__'));

                var tableKegiatan = $('#table-kegiatan').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route('peserta.index') }}',
                        data: function(data) {
                            data.kegiatan_id = $('#kegiatanSelect').find(':selected').data('id') || '';
                            data.kabupaten = $('#kabupatenSelect').val() || '';
                        }
                    },
                    columns: [
                        {
                            data: null,
                            searchable: false,
                            orderable: false,
                            render: function(data, type, row, meta) {
                                return meta.settings._iDisplayStart + meta.row + 1;
                            }
                        },
                        { data: 'no_ktp', render: escapeHtml },
                        { data: 'nama', render: escapeHtml },
                        { data: 'kabupaten', render: escapeHtml },
                        { data: 'status_keikutpesertaan', render: escapeHtml },
                        {
                            data: 'kegiatan.nama_kegiatan',
                            render: function(data) {
                                return '<b>' + escapeHtml(data) + '</b>';
                            }
                        },
                        { data: 'instansi', render: escapeHtml },
                        { data: 'jenis_gol', render: escapeHtml },
                        { data: 'golongan', render: escapeHtml },
                        {
                            data: null,
                            render: function(data, type, row) {
                                return 'No : Hp ' + escapeHtml(row.no_hp) + '<br>No : WA ' + escapeHtml(row.no_wa);
                            }
                        },
                        {
                            data: 'id',
                            searchable: false,
                            orderable: false,
                            render: function(data) {
                                return '<a target="_blank" href="' + cetakUrl.replace('__id__', data) +
                                    '" class="btn btn-primary"><i class="fas fa-print"></i></a>';
                            }
                        },
                        {
                            data: 'id',
                            searchable: false,
                            orderable: false,
                            render: function(data) {
                                return '<a href="' + editUrl.replace('__id__', data) +
                                    '" class="btn btn-warning my-2"><i class="fas fa-edit"></i></a>' +
                                    '<button onclick="deleteData(' + data + ', \'peserta\')" class="btn btn-danger">' +
                                    '<i class="fas fa-trash-alt"></i></button>';
                            }
                        }
                    ],
                    order: [[0, 'desc']],
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                    paging: true,
                    searching: true,
                    language: {
                        processing: 'Memproses...',
                        search: 'Pencarian Data Kegiatan BBGTK :',
                        lengthMenu: 'Tampilkan _MENU_ data',
                        info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                        infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
                        zeroRecords: 'Data tidak ditemukan',
                        paginate: {
                            first: 'Pertama',
                            last: 'Terakhir',
                            next: 'Selanjutnya',
                            previous: 'Sebelumnya'
                        }
                    }
                });

                function escapeHtml(value) {
                    return $('<div>').text(value ?? '').html();
                }

                const exportBtn = $('#export-section');
                const kegiatan = document.querySelector('#kegiatanSelect');

                exportBtn.hide();

                function applySearch() {
                    exportBtn.show();
                    const kegiatanValue = kegiatan.value;

                    if (kegiatanValue == '') {
                        exportBtn.hide();
                        tableKegiatan.ajax.reload();
                        return;
                    }


                    var kegiatanId = $('#kegiatanSelect').find(':selected').attr('data-id');

                    // Construct the URL with the collected row IDs and kegiatanId
                    var url = '{{ route('peserta.export', ['id_kegiatan' => ':id']) }}';
                    url = url.replace(':id', kegiatanId);
                    $('#exportBtn').attr('href', url);
                    tableKegiatan.ajax.reload();

                }

                kegiatan.addEventListener('change', applySearch);

                $('#kabupatenSelect').on('change', () => {
                    tableKegiatan.ajax.reload();
                })



            });
        </script>
    @endpush
@endsection
