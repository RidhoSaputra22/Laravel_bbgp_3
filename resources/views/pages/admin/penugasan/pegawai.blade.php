@extends('layouts.app', ['title' => 'Data Internal'])
@section('content')
    @push('styles')
        <link rel="stylesheet" href="{{ asset('library/select2/dist/css/select2.min.css') }}">
        <link rel="stylesheet" href="{{ asset('library/selectric/public/selectric.css') }}">
        <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.css') }}">
    @endpush

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Tambah Penugasan Pegawai</h1>
            </div>

            <div class="section-body">

                <div class="row">

                    <div class="col-md-12 col-lg-12">
                        <form action="{{ route('internal.store.pegawai') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Nama</label>
                                                <input readonly required value="{{ $pegawai->nama_lengkap }}" name="nama"
                                                    type="text" class="form-control">
                                                {{-- <select name="nama" class="form-control select2" id="selectNama">
                                                        <option value="">-- Pilih Pegawai --</option>
    
                                                        @foreach ($datas['dataPegawai'] as $v)
                                                            <option data-nip="{{ $v->nip }}"
                                                                value="{{ $v->nama_lengkap }}">{{ $v->nama_lengkap }}
                                                            </option>
                                                        @endforeach
                                                    </select> --}}
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>NIP</label>
                                                <input readonly required value="{{ $pegawai->nip }}" name="nip"
                                                    type="number" class="form-control">
                                            </div>

                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>NIK</label>
                                                <input readonly required value="{{ $pegawai->no_ktp }}" name="nik"
                                                    type="number" class="form-control">
                                            </div>

                                        </div>
                                    </div>


                                    {{-- <div class="row">
                                        <div class="col-md-6"></div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Jam Mulai Kegiatan</label>
                                                <input readonly required name="tgl_kegiatan" type="text" class="form-control"
                                                    id="time">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Jam Selesai Kegiatan</label>
                                                <input readonly required name="tgl_selesai_kegiatan" type="text"
                                                    class="form-control" id="time">
                                            </div>
                                        </div>
                                    </div> --}}


                                    <div class="row">
                                        <div class="col-md-6">
                                            <label>Jabatan</label>
                                            <input readonly required value="{{ $pegawai->jabatan }}" name="jabatan"
                                                type="text" class="form-control">
                                            {{-- <select required name="jabatan" class="form-control select2">
                                                <option value="">-- Pilih Jabatan --</option>
                                                @foreach ($datas['jabatanPegawai'] as $v)
                                                    <option {{ $pegawai->jabatan == $v->name ? 'selected' : '' }}
                                                        value="{{ $v->name }}">{{ $v->name }}</option>
                                                @endforeach
                                            </select> --}}
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Golongan</label>
                                                <input readonly required value="{{ $pegawai->golongan }}" name="golongan"
                                                    type="text" class="form-control">
                                                {{-- <select required name="golongan" class="form-control select2">
                                                    <option value="">-- Pilih Golongan --</option>
                                                    @foreach ($datas['golongan'] as $v)
                                                        <option {{ $pegawai->golongan == $v->name ? 'selected' : '' }}
                                                            value="{{ $v->name }}">{{ $v->name }}</option>
                                                    @endforeach
                                                </select> --}}
                                            </div>

                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label>Jenis Penugasan</label>
                                            <input required readonly name="jenis" type="text" value="Penugasan Pegawai"
                                                class="form-control">
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


                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Nama Kegiatan</label>
                                                <input required placeholder="Nama Kegiatan yang ditugaskan" name="kegiatan"
                                                    type="text" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Mulai Kegiatan</label>
                                                <input type="text" name="mulai_kegiatan"
                                                    class="form-control datetimepicker">

                                                {{-- <input required name="tgl_kegiatan" type="date" class="form-control"> --}}
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Selesai Kegiatan</label>
                                                <input type="text" name="selesai_kegiatan"
                                                    class="form-control datetimepicker">
                                                {{-- <input required name="tgl_selesai_kegiatan" type="date"
                                                    class="form-control"> --}}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Tempat Kegiatan</label>
                                                <input required placeholder="Lokasi/Tempat Kegiatan yang ditugaskan"
                                                    name="tempat" type="text" class="form-control">
                                            </div>

                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Keterangan Kegiatan</label>
                                                <textarea class="form-control summernote-simple" required placeholder="Deskripsi Kegiatan yang ditugaskan"
                                                    name="deskripsi" id="" cols="30" rows="100"></textarea>
                                                {{-- <input required name="deskripsi" type="text" class="form-control"> --}}
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
        <script src="{{ asset('library/summernote/dist/summernote-bs4.js') }}"></script>

        <script>
            $(document).ready(function() {
                // Initialize Select2
                $('#selectNama').select2();

                // Handle change event on select element
                $('#selectNama').on('change', function() {
                    // Get selected option
                    var selectedOption = $(this).find(':selected');

                    // Get NIP from data-nip attribute
                    var nip = selectedOption.data('nip');

                    // Set NIP value to input field
                    $('input[name="nip"]').val(nip);
                    $('input[name="nik"]').val(nip);
                });
            });
        </script>
    @endpush
@endsection
