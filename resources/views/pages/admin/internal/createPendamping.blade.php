@extends('layouts.app', ['title' => 'Tambah Data Status Kepegawaian'])
@section('content')
    @push('styles')
        <link rel="stylesheet" href="{{ asset('library/select2/dist/css/select2.min.css') }}">
        <link rel="stylesheet" href="{{ asset('library/selectric/public/selectric.css') }}">
    @endpush

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Tambah {{ $title }}</h1>
            </div>

            <div class="section-body">

                <div class="row">

                    <div class="col-md-12 col-lg-12">
                        <form action="{{ route('internal.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Nama Petugas</label>
                                                <input required name="nama" type="text" class="form-control">
                                            </div>
                                        </div>
                                        {{-- <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Tempat</label>
                                                <input required name="tempat" type="text" class="form-control">
                                            </div>

                                        </div> --}}

                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Kegiatan</label>
                                                <input required name="kegiatan" type="text" class="form-control">
                                            </div>
                                            
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Tanggal Kegiatan</label>
                                                <input required name="tgl_kegiatan" type="date" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label>Jenis Penugasan</label>
                                            <input required readonly name="jenis" type="text"
                                                value="{{ $title }}" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label>Kabupaten / Kota</label>
                                            <select required name="kota" class="form-control select2">
                                                <option value="">-- Pilih kabupaten/kota --</option>
                                                @foreach ($datas['kota'] as $v)
                                                    <option value="{{ $v->name }}">{{ $v->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row my-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Transport Pulang</label>
                                                <input required name="transport_pulang" type="number" class="form-control">
                                            </div>
                                            
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Transport Pergi</label>
                                                <input required name="transport_pergi" type="number" class="form-control">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row my-3">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Hotel</label>
                                                <input required name="hotel" type="text" class="form-control">
                                            </div>
                                            
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Hari 1</label>
                                                <input required name="hari_1" type="number" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Hari 2</label>
                                                <input required name="hari_2" type="number" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Hari 3</label>
                                                <input required name="hari_3" type="number" class="form-control">
                                            </div>
                                        </div>
                                    </div>

                                    


                                </div>

                                <div class="card-footer text-right">
                                    <button class="btn btn-primary " type="submit">Submit</button>
                                    <button class="btn btn-secondary mx-1" type="reset">Reset</button>
                                    <a href="{{ route('internal.index') }}" class="btn btn-warning">Kembali</a>
                                </div>
                        </form>
                    </div>

                </div>



            </div>
    </div>
    </section>
    </div>

    @push('scripts')
        <script src="{{ asset('library/select2/dist/js/select2.full.min.js') }}"></script>
    @endpush
@endsection
