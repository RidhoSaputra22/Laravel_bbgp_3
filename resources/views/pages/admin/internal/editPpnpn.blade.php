@extends('layouts.app', ['title' => 'Edit Penugasan PPNPN'])

@section('content')
    @push('styles')
        <link rel="stylesheet" href="{{ asset('library/select2/dist/css/select2.min.css') }}">
        <link rel="stylesheet" href="{{ asset('library/selectric/public/selectric.css') }}">
        <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.css') }}">

    @endpush

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Edit Penugasan PPNPN</h1>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-md-12 col-lg-12">
                        <form action="{{ route('internal.update.ppnpn') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="id" value="{{ $penugasan->id }}">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Nama</label>
                                                <input readonly required value="{{ $penugasan->nama }}" name="nama"
                                                    type="text" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>NIP</label>
                                                <input readonly required value="{{ $penugasan->nip }}" id="nip"
                                                    name="nip" type="number" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>NIK</label>
                                                <input readonly required value="{{ $penugasan->nik }}" id="nik"
                                                    name="nik" type="number" class="form-control">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <label>Jabatan</label>
                                            <input readonly required value="{{ $penugasan->jabatan }}" name="jabatan"
                                                type="text" class="form-control">
                                        </div>

                                        <div class="col-md-6">
                                            <label>Jenis Penugasan</label>
                                            <input required readonly name="jenis" type="text" value="Penugasan PPNPN"
                                                class="form-control">
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <label>Kabupaten / Kota</label>
                                            <select required name="kabupaten" class="form-control select2">
                                                <option value="">-- Pilih kabupaten/kota --</option>
                                                @foreach ($datas['kota'] as $v)
                                                    <option value="{{ $v->name }}"
                                                        {{ $penugasan->kota == $v->name ? 'selected' : '' }}>
                                                        {{ $v->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Nama Kegiatan</label>
                                                <input required placeholder="Nama Kegiatan yang ditugaskan" name="kegiatan"
                                                    type="text" class="form-control" value="{{ $penugasan->kegiatan }}">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Mulai Kegiatan</label>
                                                <input type="text" name="mulai_kegiatan"
                                                    class="form-control datetimepicker"
                                                    value="{{ $datas['mulai_kegiatan'] }}">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Selesai Kegiatan</label>
                                                <input type="text" name="selesai_kegiatan"
                                                    class="form-control datetimepicker"
                                                    value="{{ $datas['selesai_kegiatan'] }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Tempat Kegiatan</label>
                                                <input required placeholder="Lokasi/Tempat Kegiatan yang ditugaskan"
                                                    name="tempat" type="text" class="form-control"
                                                    value="{{ $penugasan->tempat }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Keterangan Kegiatan</label>
                                                <textarea class="form-control summernote-simple" required placeholder="Deskripsi Kegiatan yang ditugaskan" name="deskripsi" id=""
                                                    cols="30" rows="100">{{ $penugasan->deskripsi }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer text-right">
                                    <button class="btn btn-primary " type="submit">Update</button>
                                    <button class="btn btn-secondary mx-1" type="reset">Reset</button>
                                    <a href="{{ session('role') == 'pegawai' ? route('pegawai.show', session('no_ktp')) : route('internal.index') }}" class="btn btn-warning">Kembali</a>
                                </div>
                                
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>

    @push('scripts')
        <script src="{{ asset('library/select2/dist/js/select2.full.min.js') }}"></script>
        <script>
            $(document).ready(function() {
                // Initialize Select2
                $('#selectNama').select2();
            });
        </script>
    @endpush
@endsection
