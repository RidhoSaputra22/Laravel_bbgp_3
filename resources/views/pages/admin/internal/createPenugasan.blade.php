@extends('layouts.app', ['title' => 'Data Internal'])
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
                                @if ($title == 'Penugasan Pegawai')
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Nama</label>
                                                    <input required name="nama" type="text" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>NIP</label>
                                                    <input required name="nip" type="number" class="form-control">
                                                </div>

                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>NIK</label>
                                                    <input required name="nik" type="number" class="form-control">
                                                </div>

                                            </div>
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
                                                <label>Jabatan</label>
                                                <select required name="jabatan" class="form-control select2">
                                                    <option value="">-- Pilih Jabatan --</option>
                                                    @foreach ($datas['jabatanPegawai'] as $v)
                                                        <option  value="{{ $v->name }}">{{ $v->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Golongan</label>
                                                    <select required name="golongan" class="form-control select2">
                                                        <option value="">-- Pilih Golongan --</option>
                                                        @foreach ($datas['golongan'] as $v)
                                                            <option value="{{ $v->name }}">{{ $v->name }}</option>
                                                        @endforeach
                                                    </select>
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
                                                <div class="form-group">
                                                    <label>Tempat</label>
                                                    <input required name="tempat" type="text" class="form-control">
                                                </div>

                                            </div>
                                        </div>


                                    </div>
                                @else
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Nama</label>
                                                <input required name="nama" type="text" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>NIP</label>
                                                <input required name="nip" type="number" class="form-control">
                                            </div>

                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>NIK</label>
                                                <input required name="nik" type="number" class="form-control">
                                            </div>

                                        </div>
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
                                    <div class="row ">
                                        {{-- <div class="col-md-6">
                                            <label>Jabatan</label>
                                            <select required name="jabatan" class="form-control select2">
                                                <option value="">-- Pilih Jabatan --</option>
                                                @foreach ($datas['jabatanPegawai'] as $v)
                                                    <option  value="{{ $v->name }}">{{ $v->name }}</option>
                                                @endforeach
                                            </select>
                                        </div> --}}
                                        {{-- <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Golongan</label>
                                                <select required name="golongan" class="form-control select2">
                                                    <option value="">-- Pilih Golongan --</option>
                                                    @foreach ($datas['golongan'] as $v)
                                                        <option value="{{ $v->name }}">{{ $v->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                        </div> --}}
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label>Jenis Penugasan</label>
                                            <input required readonly name="jenis" type="text"
                                                value="{{ $title }}" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Tempat</label>
                                                <input required name="tempat" type="text" class="form-control">
                                            </div>

                                        </div>
                                    </div>


                                </div>
                                @endif


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
