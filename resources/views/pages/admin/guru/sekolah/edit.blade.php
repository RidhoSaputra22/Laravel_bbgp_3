@extends('layouts.app', ['title' => 'Data Sekolah'])
@section('content')
    @push('styles')
        <link rel="stylesheet" href="{{ asset('library/select2/dist/css/select2.min.css') }}">
        <link rel="stylesheet" href="{{ asset('library/selectric/public/selectric.css') }}">
    @endpush

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Edit Data Sekolah</h1>
                {{-- <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="#">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="#">Bootstrap Components</a></div>
                    <div class="breadcrumb-item">Form</div>
                </div> --}}
            </div>

            <div class="section-body">

                <div class="container my-4">
                    <div class="row">
                        <div class="col-md-12 col-lg-12">
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ session('error') }}
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form action="{{ route('user.update.data-sekolah', $sekolah->id) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="card-body">
                                    <div class="h3">Identitas Sekolah</div>
                                    <small>
                                        <li>yang memiliki tanda bintang (*) wajib anda isi</li>
                                    </small>
                                    <hr>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>* Nama Sekolah</label>
                                                <input name="nama_sekolah" type="text" class="form-control" required
                                                    value="{{ old('nama_sekolah', $sekolah->nama_sekolah) }}"
                                                    placeholder="resmi sesuai Dapodik">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>* NPSN</label>
                                                <input name="npsn" type="number" min="0" class="form-control"
                                                    required value="{{ old('npsn', $sekolah->npsn) }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>* Jenjang Sekolah</label>
                                                <select required name="jenjang" class="form-control">
                                                    <option value="">-- pilih jenjang sekolah --</option>
                                                    <option value="TK"
                                                        {{ old('jenjang', $sekolah->jenjang) == 'TK' ? 'selected' : '' }}>TK
                                                    </option>
                                                    <option value="SD"
                                                        {{ old('jenjang', $sekolah->jenjang) == 'SD' ? 'selected' : '' }}>SD
                                                    </option>
                                                    <option value="SMP"
                                                        {{ old('jenjang', $sekolah->jenjang) == 'SMP' ? 'selected' : '' }}>
                                                        SMP</option>
                                                    <option value="SMA/SMK"
                                                        {{ old('jenjang', $sekolah->jenjang) == 'SMA/SMK' ? 'selected' : '' }}>
                                                        SMA/SMK Sederajat</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>* Status Sekolah</label>
                                                <select required name="status" class="form-control">
                                                    <option value="">-- pilih status sekolah --</option>
                                                    <option value="Negeri"
                                                        {{ old('status', $sekolah->status) == 'Negeri' ? 'selected' : '' }}>
                                                        Negeri</option>
                                                    <option value="Swasta"
                                                        {{ old('status', $sekolah->status) == 'Swasta' ? 'selected' : '' }}>
                                                        Swasta</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>* Akreditasi Sekolah</label>
                                                <select required name="akreditasi" class="form-control">
                                                    <option value="">-- pilih akreditasi sekolah --</option>
                                                    <option value="A"
                                                        {{ old('akreditasi', $sekolah->akreditasi) == 'A' ? 'selected' : '' }}>
                                                        A</option>
                                                    <option value="B"
                                                        {{ old('akreditasi', $sekolah->akreditasi) == 'B' ? 'selected' : '' }}>
                                                        B</option>
                                                    <option value="C"
                                                        {{ old('akreditasi', $sekolah->akreditasi) == 'C' ? 'selected' : '' }}>
                                                        C</option>
                                                    <option value="belum"
                                                        {{ old('akreditasi', $sekolah->akreditasi) == 'belum' ? 'selected' : '' }}>
                                                        Belum ada</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>* Alamat Sekolah</label>
                                                <input required name="alamat" type="text" class="form-control"
                                                    value="{{ old('alamat', $sekolah->alamat) }}"
                                                    placeholder="alamat lengkap sekolah">
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Provinsi</label>
                                                <select required name="provinsi" id="provinsi"
                                                    class="form-control select2">
                                                    <option value="">-- pilih provinsi --</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Kabupaten</label>
                                                <select required name="kabupaten" id="kabupaten"
                                                    class="form-control select2" disabled>
                                                    <option value="">-- pilih kabupaten --</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Kecamatan</label>
                                                <select required name="kecamatan" id="kecamatan"
                                                    class="form-control select2" disabled>
                                                    <option value="">-- pilih kecamatan --</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Sisanya sama seperti form create, tapi dengan value dari $sekolah -->
                                    <!-- ... copy semua field lainnya dengan value="{{ old('field', $sekolah->field) }}" -->

                                </div>

                                <div class="card-footer text-right">
                                    <button class="btn btn-primary" type="submit">Update Data</button>
                                    <a href="{{ route('user.show.data-sekolah', $sekolah->id) }}"
                                        class="btn btn-secondary">Batal</a>
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
