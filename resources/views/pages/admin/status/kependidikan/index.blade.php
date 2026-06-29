@extends('layouts.app', ['title' => 'Data Pendidikan Terakhir'])

@section('content')
    @push('styles')
        <link rel="stylesheet" href="{{ asset('library/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('library/datatables.net-select-bs4/css/select.bootstrap4.min.css') }}">
    @endpush

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Data Pendidikan Terakhir</h1>
            </div>


            <div class="section-body">


                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">

                                <div class="row">

                                    <div class="col-md-12 col-lg-12">
                                        <form action="{{ route('kependidikan.store') }}" method="POST">
                                            @csrf
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Nama</label>
                                                                <input name="name" required
                                                                    placeholder="Masukkan Pendidikan Terakhir"
                                                                    type="text" class="form-control">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">

                                                        <div class="col-md-4">

                                                            <button type="submit" class="btn btn-primary">
                                                                <i class="fas fa-plus"></i>
                                                                Tambah Data
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>

                                        </form>
                                    </div>

                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped" id="table-temp">
                                        <thead>
                                            <tr>
                                                <th class="text-center">
                                                    #
                                                </th>
                                                <th>Nama Pendidikan</th>
                                                
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($datas as $i => $data)
                                                <tr>
                                                    <td>
                                                        {{ ++$i }}
                                                    </td>
                                                    <td>{{ $data->name }}</td>
                                                    <td>
                                                        <a href="{{ route('kependidikan.edit', $data->id) }} "
                                                            class="btn btn-warning my-2"><i class="fas fa-edit"></i></a>

                                                        <button onclick="deleteData({{ $data->id }}, 'kependidikan')"
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
        <!-- Page Specific JS File -->
    @endpush
@endsection
