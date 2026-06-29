@extends('layouts.app', ['title' => 'jabatan_penugasan_pegawai'])

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
                <h1>Data Jabatan Pegawai BBGTK</h1>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <!-- Navigation Buttons -->
                                <a href="{{ route('jabatan_penugasan_pegawai.create') }}" class="btn btn-primary text-white my-3">+ Tambah Data Jabatan Pegawai BBGTK</a>
                                <!-- <h6>Print Absensi</h6> -->

                                <!-- Tables Section -->
                                <div class="table-responsive">
                                    <table class="table table-striped" id="table-kegiatan">
                                        <thead>
                                            <tr>
                                                <th >#</th>
                                                <th>Nama Data Jabatan Pegawai BBGTK</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($datas as $i => $data)
                                                <tr>
                                                    <td >{{ ++$i }}</td>
                                                    <td>{{ $data->name ?? '' }}</td>
                                                    <td>
                                                        <a href="{{ route('jabatan_penugasan_pegawai.edit', $data->id) }}" class="btn btn-warning my-2">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button onclick="deleteData({{ $data->id }}, 'jabatan_penugasan_pegawai')" class="btn btn-danger">
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
                var language = {
                    "sSearch": "Pencarian Data Jabatan Pegawai BBGTK : ",
                };
                var tableKegiatan = $('#table-kegiatan').DataTable({
                    paging: true,
                    searching: true,
                    language: language,
                });

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
                    }

                    window.open(printUrl, '_blank');
                }
            });
        </script>
    @endpush
@endsection
