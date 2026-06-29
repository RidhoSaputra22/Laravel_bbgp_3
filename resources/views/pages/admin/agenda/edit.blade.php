@extends('layouts.app', ['title' => 'Data Agenda'])
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
                <h1>Ubah Agenda</h1>
            </div>

            <div class="section-body">

                <div class="row">

                    <div class="col-md-12 col-lg-12">
                        <form action="{{ route('agenda.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <input required type="hidden" name="id" value="{{ $data->id }}" class="form-control">
                            <div class="card">
                                <div class="card-header">
                                    <h4></h4>
                                </div>
                                <div class="card-body">
                                    <div class="form-group row mb-4">
                                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Judul</label>
                                        <div class="col-sm-12 col-md-7">
                                            <input value="{{ $data->nama_kegiatan }}" required type="text" name="judul"
                                                class="form-control">
                                        </div>
                                    </div>

                                    <div class="form-group row mb-4">
                                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Lokasi
                                            Agenda</label>
                                        <div class="col-sm-6 col-md-4">
                                            <input required type="text"  class="form-control"
                                                value="{{ $data->tempat_kegiatan }}" name="lokasi_kegiatan">
                                        </div>
                                    </div>
                                    {{-- <div class="form-group row mb-4">
                                        <label
                                            class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Kategori</label>
                                        <div class="col-sm-12 col-md-7">
                                            <select required name="kategori_id" class="form-control selectric">
                                                <option>Tech</option>
                                                <option>News</option>
                                                <option>Political</option>
                                            </select>
                                        </div>
                                    </div> --}}
                                    <div class="form-group row mb-4">
                                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Isi
                                            Agenda</label>
                                        <div class="col-sm-12 col-md-7">
                                            <textarea required name="deskripsi_kegiatan"  class="summernote">{!! $data->deskripsi_kegiatan !!}</textarea>
                                        </div>
                                    </div>


                                    <div class="form-group row mb-4">
                                        <label
                                            class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Thumbnail</label>
                                        <div class="col-sm-6 col-md-4">
                                            <div id="image-preview" class="image-preview">
                                                <label for="image-upload" id="image-label">Choose File</label>
                                                <input type="file" name="thumbnail" id="image-upload" />
                                            </div>
                                            <input type="hidden" name="thumbnail_old" value="{{ $data->thumbnail }}"
                                                id="" />
                                        </div>
                                        <div class="col-sm-6 col-md-4 mt-3">

                                            <img class="img img-fluid" width="250"
                                                src="{{ asset('upload/agenda/' . $data->thumbnail) }}" alt="">
                                        </div>
                                    </div>



                                    <div class="form-group row mb-4">
                                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Tanggal
                                            Agenda</label>
                                        <div class="col-sm-6 col-md-4 mb-4">
                                            <input value="{{ $data->tgl_kegiatan }}" required type="date" value=""
                                                class="form-control" name="tgl_kegiatan">
                                        </div>
                                        <div class="col-sm-6 col-md-4 ">
                                            <input value="{{ $data->tgl_selesai }}" required type="date" value=""
                                                class="form-control" name="tgl_selesai">
                                        </div>
                                    </div>

                                    <div class="form-group row mb-4">
                                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Jam
                                            Agenda</label>
                                        <div class="col-sm-6 col-md-4 mb-4">
                                            <input value="{{ $data->jam_mulai }}" required type="time" value=""
                                                class="form-control" name="jam_mulai">
                                        </div>
                                        <div class="col-sm-6 col-md-4 ">
                                            <input value="{{ $data->jam_selesai }}" required type="time" value=""
                                                class="form-control" name="jam_selesai">
                                        </div>
                                    </div>

                                    <div class="form-group row mb-4">
                                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Author</label>
                                        <div class="col-sm-6 col-md-4">
                                            <input readonly type="text" value="{{ session('name') }}"
                                                class="form-control" name="author">
                                        </div>
                                    </div>

                                    <div class="row form-group mb-4">

                                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Tanggal
                                            Publish</label>
                                        <div class="col-sm-6 col-md-4">
                                            <input readonly class="form-control"
                                                value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" type="date"
                                                name="tgl_publish" />

                                        </div>
                                    </div>

                                    <div class="form-group row mb-4">

                                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Status</label>

                                        <div class="col-sm-6 col-md-4">
                                            <select class="form-control selectric" name="status" required>
                                                <option {{ $data->status == 'publish' ? 'selected' : '' }}
                                                    value="publish">Publish</option>
                                                <option {{ $data->status == 'pending' ? 'selected' : '' }}
                                                    value="pending">Pending</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group row mb-4">
                                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3"></label>
                                        <div class="col-sm-12 col-md-7">
                                            <button class="btn btn-primary">Ubah Agenda</button>
                                            <a href="{{ route('agenda.index') }}" class="btn btn-warning">Kembali</a>
                                        </div>
                                    </div>
                                </div>
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
        <script src="{{ asset('library/summernote/dist/summernote-bs4.js') }}"></script>
        <script src="{{ asset('library/upload-preview/upload-preview.js') }}"></script>
        <script src="{{ asset('js/page/features-post-create.js') }}"></script>
        <script src="{{ asset('library/selectric/public/jquery.selectric.min.js') }}"></script>
        <script src="{{ asset('js/page/features-post-create.js') }}"></script>

        <script src="{{ asset('js/page/forms-advanced-forms.js') }}"></script>
    @endpush
@endsection
