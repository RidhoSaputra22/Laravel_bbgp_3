@extends('layouts.app', ['title' => 'Data Agenda'])

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
                <h1>Data Agenda BBGTK</h1>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <!-- Navigation Buttons -->

                                <a href="{{ route('agenda.create') }}" class="btn btn-primary text-white my-3">+ Tambah
                                    Agenda</a>

                                <!-- Tables Section -->
                                <!-- PPNPN -->
                                <div class="table-responsive ">
                                    <!-- Table PPNPN -->
                                    <table class="table table-striped " id="table-agenda">
                                        <thead>
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th>Thumbnail</th>
                                                <th>Judul Agenda</th>
                                                {{-- <th>Isi Agenda</th> --}}
                                                <th>Lokasi Agenda</th>
                                                <th>Tanggal Agenda</th>
                                                <th>Jam Agenda</th>
                                                <th>Author</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($datas as $i => $data)
                                                <?php
                                                setlocale(LC_ALL, 'IND');
                                                
                                                $tgl_kegiatan = strftime('%d %B', strtotime($data->tgl_kegiatan));
                                                $tgl_selesai = strftime('%d %B %Y', strtotime($data->tgl_selesai));
                                                ?>
                                                <tr>
                                                    <td>{{ ++$i }}</td>
                                                    <td>
                                                        <img class="img img-fluid" width="200"
                                                            src="{{ asset('upload/agenda/' . $data->thumbnail) }}"
                                                            alt="Thumbnail Agenda">
                                                    </td>
                                                    <td>{{ $data->nama_kegiatan ?? '' }}</td>
                                                    {{-- <td>{!! $data->isi ?? '' !!}</td> --}}
                                                    <td>{{ $data->tempat_kegiatan }} </td>
                                                    <td>{{ $data->tgl_kegiatan }} - {{ $data->tgl_selesai }} </td>
                                                    <td>{{ $data->jam_mulai }} - {{ $data->jam_selesai }} WITA </td>
                                                    <td>{{ $data->status }} </td>
                                                    <td>
                                                        @if ($data->status == 'publish')
                                                            <span class="badge badge-success">Publish</span>
                                                        @else
                                                            <span class="badge badge-warning">Belum Publish</span>
                                                        @endif

                                                    </td>
                                                    <td>
                                                        <a href="{{ route('agenda.edit', $data->id) }}"
                                                            class="btn btn-warning my-2"><i class="fas fa-edit"></i></a>
                                                        <button onclick="deleteData({{ $data->id }}, 'agenda')"
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
                // Existing DataTable initialization
                var language = {
                    "sSearch": "Pencarian Data Kegiatan BBGTK : ",
                };
                var tableKegiatan = $('#table-agenda').DataTable({
                    paging: true,
                    searching: true,
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/2.1.0/i18n/id.json',
                    },
                });


            });
        </script>
    @endpush
@endsection
