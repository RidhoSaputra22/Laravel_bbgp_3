
@extends('layouts.app', ['title' => 'Data Kegiatan'])
@section('content')
    @push('styles')
        <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.css') }}">
        <link rel="stylesheet" href="{{ asset('library/select2/dist/css/select2.min.css') }}">
        <link rel="stylesheet" href="{{ asset('library/selectric/public/selectric.css') }}">
        <link rel="stylesheet" href="{{ asset('library/bootstrap-daterangepicker/daterangepicker.css') }}">
        <link rel="stylesheet" href="{{ asset('library/bootstrap-timepicker/css/bootstrap-timepicker.min.css') }}">
    @endpush

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Edit Kegiatan</h1>
            </div>

            <div class="section-body">

                <div class="row">

                    <div class="col-md-12 col-lg-12">
                        <form action="{{ route('kegiatan.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="id" value="{{ $datas->id }}">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md">
                                            <div class="form-group">
                                                <label>Nama Kegiatan</label>
                                                <input value="{{ $datas->nama_kegiatan }}" required name="nama_kegiatan" type="text" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md">
                                            <div class="form-group">
                                                <label>Tempat Kegiatan</label>
                                                <input value="{{ $datas->tempat_kegiatan }}" required name="tempat_kegiatan" type="text" class="form-control">
                                            </div>

                                        </div>

                                    </div>
                                    <div class="row">

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Tanggal Kegiatan</label>
                                                <input value="{{ $datetime['mulai_kegiatan'] }}" type="text" name="mulai_kegiatan"
                                                    class="form-control datetimepicker">

                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Tanggal Selesai Kegiatan</label>
                                                <input value="{{ $datetime['selesai_kegiatan'] }}" type="text" name="selesai_kegiatan"
                                                class="form-control datetimepicker">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Keterangan Kegiatan</label>
                                                <textarea name="deskripsi_kegiatan" style="height: 100px;" placeholder="Deskripsi tentang kegiatan " class="form-control summernote-simple" id="" cols="30" rows="10">
                                                    {!! $datas->deskripsi_kegiatan !!}
                                                </textarea>

                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Aktifkan Kegiatan</label>
                                                <select name="status" id="" class="form-control">
                                                    <option value="">-- pilih status --</option>
                                                    <option  {{ $datas->status == 'true' ? 'selected' : '' }} value="true">Aktifkan</option>
                                                    <option {{ $datas->status == 'false' ? 'selected' : '' }} value="false">Non-Aktifkan</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>


                                </div>


                                <div class="card-footer text-right">
                                    <button class="btn btn-primary " type="submit">Submit</button>
                                    <button class="btn btn-secondary mx-1" type="reset">Reset</button>
                                    <a href="{{ route('kegiatan.index') }}" class="btn btn-warning">Kembali</a>
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
        <script src="{{ asset('library/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
        <script src="{{ asset('library/bootstrap-daterangepicker/daterangepicker.js') }}"></script>

        <script src="{{ asset('library/summernote/dist/summernote-bs4.js') }}"></script>
        <script src="{{ asset('js/page/forms-advanced-forms.js') }}"></script>

    @endpush
@endsection
