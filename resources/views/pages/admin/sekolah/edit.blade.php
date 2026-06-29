@extends('layouts.app', ['title' => 'Edit Data Sekolah'])

@section('content')
    @push('styles')
        <link rel="stylesheet" href="{{ asset('library/select2/dist/css/select2.min.css') }}">
        <style>
            .form-section {
                background: #f8f9fa;
                padding: 20px;
                border-radius: 8px;
                margin-bottom: 20px;
                border-left: 4px solid #6777ef;
            }

            .section-title {
                font-size: 1.1rem;
                font-weight: 600;
                color: #6777ef;
                margin-bottom: 15px;
            }

            .preview-image {
                max-width: 200px;
                margin-top: 10px;
                border-radius: 4px;
            }
        </style>
    @endpush
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <div class="section-header-back">
                    <a href="{{ route('admin.data-sekolah.index') }}" class="btn btn-icon"><i
                            class="fas fa-arrow-left"></i></a>
                </div>
                <h1>Edit Data Sekolah</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('admin.data-sekolah.index') }}">Data Sekolah</a></div>
                    <div class="breadcrumb-item">Edit</div>
                </div>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <form action="{{ route('update.data-sekolah', $sekolah->id) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="card">
                                <div class="card-header">
                                    <h4>Form Edit Sekolah</h4>
                                </div>
                                <div class="card-body">

                                    <!-- Informasi Umum Sekolah -->
                                    <div class="form-section">
                                        <h5 class="section-title"><i class="fas fa-school mr-2"></i>Informasi Umum Sekolah
                                        </h5>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Nama Sekolah <span class="text-danger">*</span></label>
                                                    <input type="text" name="nama_sekolah"
                                                        class="form-control @error('nama_sekolah') is-invalid @enderror"
                                                        value="{{ old('nama_sekolah', $sekolah->nama_sekolah) }}" required>
                                                    @error('nama_sekolah')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>NPSN <span class="text-danger">*</span></label>
                                                    <input type="text" name="npsn_sekolah"
                                                        class="form-control @error('npsn_sekolah') is-invalid @enderror"
                                                        value="{{ old('npsn_sekolah', $sekolah->npsn_sekolah) }}" required>
                                                    @error('npsn_sekolah')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Bentuk Pendidikan <span class="text-danger">*</span></label>
                                                    <select name="bp_sekolah"
                                                        class="form-control @error('bp_sekolah') is-invalid @enderror"
                                                        required>
                                                        <option value="">-- Pilih --</option>
                                                        <option value="TK"
                                                            {{ old('bp_sekolah', $sekolah->bp_sekolah) == 'TK' ? 'selected' : '' }}>
                                                            TK</option>
                                                        <option value="SD"
                                                            {{ old('bp_sekolah', $sekolah->bp_sekolah) == 'SD' ? 'selected' : '' }}>
                                                            SD</option>
                                                        <option value="SMP"
                                                            {{ old('bp_sekolah', $sekolah->bp_sekolah) == 'SMP' ? 'selected' : '' }}>
                                                            SMP</option>
                                                        <option value="SMA"
                                                            {{ old('bp_sekolah', $sekolah->bp_sekolah) == 'SMA' ? 'selected' : '' }}>
                                                            SMA</option>
                                                        <option value="SMK"
                                                            {{ old('bp_sekolah', $sekolah->bp_sekolah) == 'SMK' ? 'selected' : '' }}>
                                                            SMK</option>
                                                    </select>
                                                    @error('bp_sekolah')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Status Sekolah <span class="text-danger">*</span></label>
                                                    <select name="status_sekolah"
                                                        class="form-control @error('status_sekolah') is-invalid @enderror"
                                                        required>
                                                        <option value="">-- Pilih --</option>
                                                        <option value="Negeri"
                                                            {{ old('status_sekolah', $sekolah->status_sekolah) == 'Negeri' ? 'selected' : '' }}>
                                                            Negeri</option>
                                                        <option value="Swasta"
                                                            {{ old('status_sekolah', $sekolah->status_sekolah) == 'Swasta' ? 'selected' : '' }}>
                                                            Swasta</option>
                                                    </select>
                                                    @error('status_sekolah')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Provinsi <span class="text-danger">*</span></label>
                                                    <input type="text" name="provinsi"
                                                        class="form-control @error('provinsi') is-invalid @enderror"
                                                        value="{{ old('provinsi', $sekolah->provinsi) }}" required>
                                                    @error('provinsi')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Kabupaten <span class="text-danger">*</span></label>
                                                    <input type="text" name="kabupaten"
                                                        class="form-control @error('kabupaten') is-invalid @enderror"
                                                        value="{{ old('kabupaten', $sekolah->kabupaten) }}" required>
                                                    @error('kabupaten')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Kecamatan <span class="text-danger">*</span></label>
                                                    <input type="text" name="kecamatan"
                                                        class="form-control @error('kecamatan') is-invalid @enderror"
                                                        value="{{ old('kecamatan', $sekolah->kecamatan) }}" required>
                                                    @error('kecamatan')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>Alamat Lengkap</label>
                                                    <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="3">{{ old('alamat', $sekolah->alamat) }}</textarea>
                                                    @error('alamat')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Akreditasi</label>
                                                    <select name="akreditasi"
                                                        class="form-control @error('akreditasi') is-invalid @enderror">
                                                        <option value="-">-- Pilih --</option>
                                                        <option value="A"
                                                            {{ old('akreditasi', $sekolah->akreditasi) == 'A' ? 'selected' : '' }}>
                                                            A</option>
                                                        <option value="B"
                                                            {{ old('akreditasi', $sekolah->akreditasi) == 'B' ? 'selected' : '' }}>
                                                            B</option>
                                                        <option value="C"
                                                            {{ old('akreditasi', $sekolah->akreditasi) == 'C' ? 'selected' : '' }}>
                                                            C</option>
                                                    </select>
                                                    @error('akreditasi')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>No. Telepon</label>
                                                    <input type="text" name="no_telepon"
                                                        class="form-control @error('no_telepon') is-invalid @enderror"
                                                        value="{{ old('no_telepon', $sekolah->no_telepon) }}">
                                                    @error('no_telepon')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Email</label>
                                                    <input type="email" name="email"
                                                        class="form-control @error('email') is-invalid @enderror"
                                                        value="{{ old('email', $sekolah->email) }}">
                                                    @error('email')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Website</label>
                                                    <input type="text" name="website_url"
                                                        class="form-control @error('website_url') is-invalid @enderror"
                                                        value="{{ old('website_url', $sekolah->website_url) }}">
                                                    @error('website_url')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Tahun Berdiri</label>
                                                    <input type="text" name="tahun_berdiri"
                                                        class="form-control @error('tahun_berdiri') is-invalid @enderror"
                                                        value="{{ old('tahun_berdiri', $sekolah->tahun_berdiri) }}">
                                                    @error('tahun_berdiri')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Koordinat</label>
                                                    <input type="text" name="koordinat"
                                                        class="form-control @error('koordinat') is-invalid @enderror"
                                                        value="{{ old('koordinat', $sekolah->koordinat) }}"
                                                        placeholder="-0.123, 119.456">
                                                    @error('koordinat')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Data Kepala Sekolah -->
                                    <div class="form-section">
                                        <h5 class="section-title"><i class="fas fa-user-tie mr-2"></i>Data Kepala Sekolah
                                        </h5>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Nama Kepala Sekolah <span class="text-danger">*</span></label>
                                                    <input type="text" name="nama_kepsek"
                                                        class="form-control @error('nama_kepsek') is-invalid @enderror"
                                                        value="{{ old('nama_kepsek', $sekolah->nama_kepsek) }}" required>
                                                    @error('nama_kepsek')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Status ASN <span class="text-danger">*</span></label>
                                                    <select name="asn_opsi"
                                                        class="form-control @error('asn_opsi') is-invalid @enderror"
                                                        id="asnOpsi" required>
                                                        <option value="">-- Pilih --</option>
                                                        <option value="ya"
                                                            {{ old('asn_opsi', $sekolah->asn_opsi) == 'ya' ? 'selected' : '' }}>
                                                            Ya</option>
                                                        <option value="tidak"
                                                            {{ old('asn_opsi', $sekolah->asn_opsi) == 'tidak' ? 'selected' : '' }}>
                                                            Tidak</option>
                                                    </select>
                                                    @error('asn_opsi')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6" id="nipField">
                                                <div class="form-group">
                                                    <label>NIP</label>
                                                    <input type="text" name="nip_kepsek"
                                                        class="form-control @error('nip_kepsek') is-invalid @enderror"
                                                        value="{{ old('nip_kepsek', $sekolah->nip_kepsek) }}">
                                                    @error('nip_kepsek')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>No. SK</label>
                                                    <input type="text" name="no_sk"
                                                        class="form-control @error('no_sk') is-invalid @enderror"
                                                        value="{{ old('no_sk', $sekolah->no_sk) }}">
                                                    @error('no_sk')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>No. Telepon <span class="text-danger">*</span></label>
                                                    <input type="text" name="no_telp_kepsek"
                                                        class="form-control @error('no_telp_kepsek') is-invalid @enderror"
                                                        value="{{ old('no_telp_kepsek', $sekolah->no_telp_kepsek) }}"
                                                        required>
                                                    @error('no_telp_kepsek')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Email</label>
                                                    <input type="text" name="email_kepsek"
                                                        class="form-control @error('email_kepsek') is-invalid @enderror"
                                                        value="{{ old('email_kepsek', $sekolah->email_kepsek) }}">
                                                    @error('email_kepsek')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Data Guru -->
                                    <div class="form-section">
                                        <h5 class="section-title"><i class="fas fa-chalkboard-teacher mr-2"></i>Data Guru
                                            dan Tenaga Kependidikan</h5>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Jumlah Guru</label>
                                                    <input type="number" name="jumlah_guru"
                                                        class="form-control @error('jumlah_guru') is-invalid @enderror"
                                                        value="{{ old('jumlah_guru', $sekolah->jumlah_guru_pns + $sekolah->jumlah_honorer) }}"
                                                        min="0" readonly>
                                                    @error('jumlah_guru')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Jumlah Guru PNS</label>
                                                    <input type="number" name="jumlah_guru_pns"
                                                        class="form-control @error('jumlah_guru_pns') is-invalid @enderror"
                                                        value="{{ old('jumlah_guru_pns', $sekolah->jumlah_guru_pns) }}"
                                                        min="0">
                                                    @error('jumlah_guru_pns')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Jumlah Guru Honorer</label>
                                                    <input type="number" name="jumlah_honorer"
                                                        class="form-control @error('jumlah_honorer') is-invalid @enderror"
                                                        value="{{ old('jumlah_honorer', $sekolah->jumlah_honorer) }}"
                                                        min="0">
                                                    @error('jumlah_honorer')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Tenaga Kependidikan</label>
                                                    <input type="number" name="jumlah_kependidikan"
                                                        class="form-control @error('jumlah_kependidikan') is-invalid @enderror"
                                                        value="{{ old('jumlah_kependidikan', $sekolah->jumlah_kependidikan) }}"
                                                        min="0">
                                                    @error('jumlah_kependidikan')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>Bidang Studi</label>
                                                    <textarea name="bidang_studi" class="form-control @error('bidang_studi') is-invalid @enderror" rows="3">{{ old('bidang_studi', $sekolah->bidang_studi) }}</textarea>
                                                    <small class="form-text text-muted">Contoh: Matematika, Bahasa
                                                        Indonesia, IPA, dll</small>
                                                    @error('bidang_studi')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Data Siswa -->
                                    <div class="form-section">
                                        <h5 class="section-title"><i class="fas fa-users mr-2"></i>Data Siswa</h5>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Jumlah Siswa</label>
                                                    <input type="number" name="jumlah_siswa"
                                                        class="form-control @error('jumlah_siswa') is-invalid @enderror"
                                                        value="{{ old('jumlah_siswa', $sekolah->jumlah_siswa) }}"
                                                        min="0">
                                                    @error('jumlah_siswa')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Siswa Laki-laki</label>
                                                    <input type="number" name="jumlah_siswa_pria"
                                                        class="form-control @error('jumlah_siswa_pria') is-invalid @enderror"
                                                        value="{{ old('jumlah_siswa_pria', $sekolah->jumlah_siswa_pria) }}"
                                                        min="0">
                                                    @error('jumlah_siswa_pria')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Siswa Perempuan</label>
                                                    <input type="number" name="jumlah_siswa_perempuan"
                                                        class="form-control @error('jumlah_siswa_perempuan') is-invalid @enderror"
                                                        value="{{ old('jumlah_siswa_perempuan', $sekolah->jumlah_siswa_perempuan) }}"
                                                        min="0">
                                                    @error('jumlah_siswa_perempuan')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>Jumlah Siswa Per Kelas</label>
                                                    <textarea name="jumlah_siswa_per_kelas" class="form-control @error('jumlah_siswa_per_kelas') is-invalid @enderror"
                                                        rows="3">{{ old('jumlah_siswa_per_kelas', $sekolah->jumlah_siswa_per_kelas) }}</textarea>
                                                    <small class="form-text text-muted">Contoh: Kelas 1: 30 siswa, Kelas 2:
                                                        28 siswa</small>
                                                    @error('jumlah_siswa_per_kelas')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Fasilitas -->
                                    <div class="form-section">
                                        <h5 class="section-title"><i class="fas fa-building mr-2"></i>Fasilitas Sekolah
                                        </h5>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Jumlah Kelas <span class="text-danger">*</span></label>
                                                    <input type="number" name="jumlah_kelas"
                                                        class="form-control @error('jumlah_kelas') is-invalid @enderror"
                                                        value="{{ old('jumlah_kelas', $sekolah->jumlah_kelas) }}"
                                                        min="0" required>
                                                    @error('jumlah_kelas')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Laboratorium <span class="text-danger">*</span></label>
                                                    <input type="text" name="laboratorium"
                                                        class="form-control @error('laboratorium') is-invalid @enderror"
                                                        value="{{ old('laboratorium', $sekolah->laboratorium) }}"
                                                        required>
                                                    @error('laboratorium')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Perpustakaan <span class="text-danger">*</span></label>
                                                    <input type="text" name="perpustakaan"
                                                        class="form-control @error('perpustakaan') is-invalid @enderror"
                                                        value="{{ old('perpustakaan', $sekolah->perpustakaan) }}"
                                                        required>
                                                    @error('perpustakaan')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Ruang Guru <span class="text-danger">*</span></label>
                                                    <input type="text" name="ruang_guru"
                                                        class="form-control @error('ruang_guru') is-invalid @enderror"
                                                        value="{{ old('ruang_guru', $sekolah->ruang_guru) }}" required>
                                                    @error('ruang_guru')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Jumlah Toilet <span class="text-danger">*</span></label>
                                                    <input type="number" name="jumlah_toilet"
                                                        class="form-control @error('jumlah_toilet') is-invalid @enderror"
                                                        value="{{ old('jumlah_toilet', $sekolah->jumlah_toilet) }}"
                                                        min="0" required>
                                                    @error('jumlah_toilet')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Lapangan Olahraga <span class="text-danger">*</span></label>
                                                    <input type="text" name="lapangan_olahraga"
                                                        class="form-control @error('lapangan_olahraga') is-invalid @enderror"
                                                        value="{{ old('lapangan_olahraga', $sekolah->lapangan_olahraga) }}"
                                                        required>
                                                    @error('lapangan_olahraga')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Akses Internet <span class="text-danger">*</span></label>
                                                    <select name="akses_internet"
                                                        class="form-control @error('akses_internet') is-invalid @enderror"
                                                        required>
                                                        <option value="">-- Pilih --</option>
                                                        <option value="ada"
                                                            {{ old('akses_internet', $sekolah->akses_internet) == 'ada' ? 'selected' : '' }}>
                                                            Ada</option>
                                                        <option value="tidak_ada"
                                                            {{ old('akses_internet', $sekolah->akses_internet) == 'tidak_ada' ? 'selected' : '' }}>
                                                            Tidak Ada</option>
                                                    </select>
                                                    @error('akses_internet')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>Fasilitas IT</label>
                                                    <select name="fasilitas_it[]"
                                                        class="form-control select2 @error('fasilitas_it') is-invalid @enderror"
                                                        multiple>
                                                        @php
                                                            $fasilitasIT = [
                                                                'Komputer',
                                                                'Laptop',
                                                                'Proyektor',
                                                                'Printer',
                                                                'Scanner',
                                                                'Wifi',
                                                                'Smart TV',
                                                            ];
                                                            $selectedIT = $sekolah->fasilitas_it
                                                                ? json_decode(json_encode($sekolah->fasilitas_it))
                                                                : [];
                                                        @endphp
                                                        @foreach ($fasilitasIT as $it)
                                                            <option value="{{ $it }}"
                                                                {{ in_array($it, $selectedIT) ? 'selected' : '' }}>
                                                                {{ $it }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('fasilitas_it')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Program -->
                                    <div class="form-section">
                                        <h5 class="section-title"><i class="fas fa-trophy mr-2"></i>Program Sekolah</h5>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>Ekstrakurikuler <span class="text-danger">*</span></label>
                                                    <textarea name="ekstrakurikuler" class="form-control @error('ekstrakurikuler') is-invalid @enderror" rows="3"
                                                        required>{{ old('ekstrakurikuler', $sekolah->ekstrakurikuler) }}</textarea>
                                                    <small class="form-text text-muted">Contoh: Pramuka, Paskibra, Basket,
                                                        Futsal, dll</small>
                                                    @error('ekstrakurikuler')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>Program Unggulan <span class="text-danger">*</span></label>
                                                    <textarea name="program_unggulan" class="form-control @error('program_unggulan') is-invalid @enderror"
                                                        rows="3" required>{{ old('program_unggulan', $sekolah->program_unggulan) }}</textarea>
                                                    @error('program_unggulan')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>Jam Belajar <span class="text-danger">*</span></label>
                                                    <input type="text" name="jam_belajar"
                                                        class="form-control @error('jam_belajar') is-invalid @enderror"
                                                        value="{{ old('jam_belajar', $sekolah->jam_belajar) }}"
                                                        placeholder="Contoh: 07:00 - 14:00 WITA" required>
                                                    @error('jam_belajar')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Dokumen -->
                                    <div class="form-section">
                                        <h5 class="section-title"><i class="fas fa-file-image mr-2"></i>Dokumen dan Foto
                                        </h5>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Logo Sekolah</label>
                                                    <input type="file" name="logo_sekolah"
                                                        class="form-control @error('logo_sekolah') is-invalid @enderror"
                                                        accept="image/*" onchange="previewImage(event, 'logoPreview')">
                                                    @if ($sekolah->logo_sekolah)
                                                        <img src="{{ asset('upload/sekolah/logo_sekolah/' . $sekolah->logo_sekolah) }}"
                                                            id="logoPreview" class="preview-image" alt="Logo">
                                                    @else
                                                        <img id="logoPreview" class="preview-image"
                                                            style="display: none;">
                                                    @endif
                                                    @error('logo_sekolah')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Foto Depan Sekolah</label>
                                                    <input type="file" name="foto_depan"
                                                        class="form-control @error('foto_depan') is-invalid @enderror"
                                                        accept="image/*" onchange="previewImage(event, 'fotoPreview')">
                                                    @if ($sekolah->foto_depan)
                                                        <img src="{{ asset('upload/sekolah/foto_depan/' . $sekolah->foto_depan) }}"
                                                            id="fotoPreview" class="preview-image" alt="Foto">
                                                    @else
                                                        <img id="fotoPreview" class="preview-image"
                                                            style="display: none;">
                                                    @endif
                                                    @error('foto_depan')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Denah Lokasi</label>
                                                    <input type="file" name="denah_lokasi"
                                                        class="form-control @error('denah_lokasi') is-invalid @enderror"
                                                        accept="image/*" onchange="previewImage(event, 'denahPreview')">
                                                    @if ($sekolah->denah_lokasi)
                                                        <img src="{{ asset('upload/sekolah/denah_lokasi/' . $sekolah->denah_lokasi) }}"
                                                            id="denahPreview" class="preview-image" alt="Denah">
                                                    @else
                                                        <img id="denahPreview" class="preview-image"
                                                            style="display: none;">
                                                    @endif
                                                    @error('denah_lokasi')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Struktur Organisasi</label>
                                                    <input type="file" name="struktur_organisasi"
                                                        class="form-control @error('struktur_organisasi') is-invalid @enderror"
                                                        accept="image/*"
                                                        onchange="previewImage(event, 'strukturPreview')">
                                                    @if ($sekolah->struktur_organisasi)
                                                        <img src="{{ asset('upload/sekolah/struktur_organisasi/' . $sekolah->struktur_organisasi) }}"
                                                            id="strukturPreview" class="preview-image" alt="Struktur">
                                                    @else
                                                        <img id="strukturPreview" class="preview-image"
                                                            style="display: none;">
                                                    @endif
                                                    @error('struktur_organisasi')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="card-footer text-right">
                                    <a href="{{ route('admin.data-sekolah.index') }}"
                                        class="btn btn-secondary mr-2">Batal</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save mr-2"></i>Simpan Perubahan
                                    </button>
                                </div>
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
                $('.select2').select2({
                    placeholder: '-- Pilih Fasilitas IT --',
                    allowClear: true
                });

                // Toggle NIP field based on ASN option
                $('#asnOpsi').on('change', function() {
                    if ($(this).val() == 'ya') {
                        $('#nipField').show();
                    } else {
                        $('#nipField').hide();
                        $('input[name="nip_kepsek"]').val('');
                    }
                });

                // Trigger on page load
                $('#asnOpsi').trigger('change');
            });

            // Preview image function
            function previewImage(event, previewId) {
                const preview = document.getElementById(previewId);
                const file = event.target.files[0];

                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                    reader.readAsDataURL(file);
                }
            }
        </script>
    @endpush
@endsection
