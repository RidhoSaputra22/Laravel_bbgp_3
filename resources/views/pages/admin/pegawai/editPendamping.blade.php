@extends('layouts.app', ['title' => 'Data Internal'])
@section('content')
    @push('styles')
        <link rel="stylesheet" href="{{ asset('library/select2/dist/css/select2.min.css') }}">
        <link rel="stylesheet" href="{{ asset('library/selectric/public/selectric.css') }}">
    @endpush

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Edit {{ $title }}</h1>
            </div>

            <div class="section-body">

                <div class="row">

                    <div class="col-md-12 col-lg-12">
                        <form action="{{ route('pendamping.update.user') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="id" value="{{ $datas['pendamping']->id }}">
                            <input type="hidden" name="id_pegawai" value="{{  $pegawai->id }}">
                            <div class="card">

                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Nama</label>
                                                <input required name="nama" type="text" class="form-control"
                                                    value="{{ $datas['pendamping']->nama }}">
                                            </div>

                                        </div>
                                        <div class="col-md-6">
                                            {{-- <label>Jabatan</label>
                                            <select required name="jabatan" class="form-control select2">
                                                <option value="">-- Pilih Jabatan --</option>
                                                @foreach ($datas['jabatanPpnpn'] as $v)
                                                    <option
                                                        {{ $datas['pendamping']->jabatan == $v->name ? 'selected' : '' }}
                                                        value="{{ $v->name }}">{{ $v->name }}
                                                    </option>
                                                @endforeach
                                            </select> --}}
                                        </div>

                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label>Jenis Penugasan</label>
                                            <input required readonly name="jenis" type="text"
                                                value="{{ $title }}" class="form-control">
                                        </div>
                                        {{-- <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Tanggal Kegiatan</label>
                                                <input required name="tgl_kegiatan" type="date" class="form-control"
                                                    value="{{ $datas['pendamping']->tgl_kegiatan }}">
                                            </div>
                                        </div> --}}

                                        <div class="col-md-6">
                                            <label>Kota</label>
                                            <select required name="kota" class="form-control select2">
                                                <option value="">-- Pilih Kabupaten/ Kota --</option>
                                                @foreach ($datas['kota'] as $v)
                                                    <option
                                                        {{ $datas['pendamping']->kota == $v->name ? 'selected' : '' }}
                                                        value="{{ $v->name }}">{{ $v->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    {{-- <div class="row my-3">
                                        
                                        
                                    </div> --}}
                                   
    
    
                                    <div class="row my-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Transport Pulang</label>
                                                <input  value="{{ $datas['pendamping']->transport_pulang }}" required name="transport_pulang" type="number" class="form-control">
                                            </div>
    
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Transport Pergi</label>
                                                <input  value="{{ $datas['pendamping']->transport_pergi }}" required name="transport_pergi" type="number" class="form-control">
                                            </div>
                                        </div>
                                    </div>
    
                                    <div class="row my-3">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Hotel</label>
                                                <input  value="{{ $datas['pendamping']->hotel }}" required name="hotel" type="text" class="form-control">
                                            </div>
    
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Hari 1</label>
                                                <input  value="{{ $datas['pendamping']->hari_1 }}" required name="hari_1" type="number" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Hari 2</label>
                                                <input  value="{{ $datas['pendamping']->hari_2 }}" required name="hari_2" type="number" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Hari 3</label>
                                                <input  value="{{ $datas['pendamping']->hari_3 }}" required name="hari_3" type="number" class="form-control">
                                            </div>
                                        </div>
                                    </div>


                                </div>




                            </div>


                            <div class="card-footer text-right">
                                <button class="btn btn-primary " type="submit">Submit</button>
                                <button class="btn btn-secondary mx-1" type="reset">Reset</button>
                                <a href="{{ route('pegawai.show', session('no_ktp')) }}" class="btn btn-warning">Kembali</a>
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
