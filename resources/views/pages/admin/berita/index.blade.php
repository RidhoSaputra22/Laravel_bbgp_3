@extends('layouts.app', ['title' => 'Data Berita'])

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
                <h1>Data Berita BBGTK</h1>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <!-- Navigation Buttons -->

                                <a href="{{ route('berita.create') }}" class="btn btn-primary text-white my-3">+ Tambah
                                    Berita</a>

                                <!-- Tables Section -->
                                <!-- PPNPN -->
                                <div class="table-responsive ">
                                    <!-- Table PPNPN -->
                                    <table class="table table-striped " id="table-berita">
                                        <thead>
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th>Thumbnail</th>
                                                <th>Judul Berita</th>
                                                {{-- <th>Isi Berita</th> --}}
                                                <th>Author</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($datas as $i => $data)
                                                <tr>
                                                    <td>{{ ++$i }}</td>
                                                    <td>
                                                        <img class="img img-fluid" width="250" src="{{ asset('upload/berita/'. $data->thumbnail) }}" alt="Thumbnail Berita">  
                                                         </td>
                                                    <td>{{ $data->judul ?? '' }}</td>
                                                    {{-- <td>{!! $data->isi ?? '' !!}</td> --}}
                                                    <td>{{ $data->status }} </td>
                                                    <td>
                                                        @if ($data->status == 'publish')
                                                            <span class="badge badge-success">Publish</span>
                                                        @else
                                                            <span class="badge badge-warning">Belum Publish</span>
                                                        @endif

                                                    </td>
                                                    <td>
                                                        <a href="{{ route('berita.edit', $data->id) }}"
                                                            class="btn btn-warning my-2"><i class="fas fa-edit"></i></a>
                                                        <button onclick="deleteData({{ $data->id }}, 'berita')"
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
                var tableKegiatan = $('#table-berita').DataTable({
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
