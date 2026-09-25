@extends('layouts.landing.app')
@section('content')
    @push('styles')
        <link rel="stylesheet" href="{{ asset('library/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('library/datatables.net-select-bs4/css/select.bootstrap4.min.css') }}">
    @endpush

    <div id="banner-area" class="banner-area"
        style="background-image:url({{ asset('landing/images/banner/bannerKegiatan.png') }})">
        <div class="banner-text">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="banner-heading">
                            <h1 class="banner-title">Input Data Sekolah </h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb justify-content-center">
                                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                                    <li class="breadcrumb-item" aria-current="page">Sekolah</li>
                                    <li class="breadcrumb-item active" aria-current="page">Input Data Sekolah</li>
                                </ol>
                            </nav>
                        </div>
                    </div><!-- Col end -->
                </div><!-- Row end -->
            </div><!-- Container end -->
        </div><!-- Banner text end -->
    </div><!-- Banner area end -->

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

                @if (session('info'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        {{ session('info') }}
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                @endif

                @if ($errors->any())
                    <div id="validation-errors" class="alert alert-danger" role="alert" tabindex="-1">
                        <strong>Periksa kembali data berikut:</strong>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form action="{{ route('user.store.data-sekolah') }}" method="POST" enctype="multipart/form-data">
                    @csrf
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
                                    <input name="nama_sekolah" id="nama_sekolah" type="text"
                                        class="form-control @error('nama_sekolah') is-invalid @enderror"
                                        value="{{ old('nama_sekolah') }}" required placeholder="resmi sesuai Dapodik">
                                    @error('nama_sekolah')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>* NPSN</label>
                                    <input name="npsn_sekolah" id="npsn_sekolah" type="number" min="0"
                                        class="form-control @error('npsn_sekolah') is-invalid @enderror"
                                        value="{{ old('npsn_sekolah') }}" required placeholder="">
                                    @error('npsn_sekolah')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>* Jenjang Sekolah</label>
                                    <select required name="bp_sekolah" id="bp_sekolah"
                                        class="form-control @error('bp_sekolah') is-invalid @enderror">
                                        <option value="">-- pilih jenjang sekolah --</option>
                                        <option value="TK" @selected(old('bp_sekolah') === 'TK')>TK</option>
                                        <option value="SD" @selected(old('bp_sekolah') === 'SD')>SD</option>
                                        <option value="SMP" @selected(old('bp_sekolah') === 'SMP')>SMP</option>
                                        <option value="SMA/SMK" @selected(old('bp_sekolah') === 'SMA/SMK')>SMA/SMK Sederajat</option>
                                    </select>
                                    @error('bp_sekolah')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>* Status Sekolah</label>
                                    <select required name="status_sekolah" id="status_sekolah"
                                        class="form-control @error('status_sekolah') is-invalid @enderror">
                                        <option value="">-- pilih status sekolah --</option>
                                        <option value="Negeri" @selected(old('status_sekolah') === 'Negeri')>Negeri</option>
                                        <option value="Swasta" @selected(old('status_sekolah') === 'Swasta')>Swasta</option>
                                    </select>
                                    @error('status_sekolah')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>* Akreditasi Sekolah</label>
                                    <select required name="akreditasi" id="akreditasi"
                                        class="form-control @error('akreditasi') is-invalid @enderror">
                                        <option value="">-- pilih akreditasi sekolah --</option>
                                        <option value="A" @selected(old('akreditasi') === 'A')>A</option>
                                        <option value="B" @selected(old('akreditasi') === 'B')>B</option>
                                        <option value="C" @selected(old('akreditasi') === 'C')>C</option>
                                        <option value="belum" @selected(old('akreditasi') === 'belum')>Belum ada</option>
                                    </select>
                                    @error('akreditasi')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                        </div>

                        <div class="row">

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>* Alamat Sekolah</label>
                                    <input required name="alamat" id="alamat" type="text"
                                        placeholder="alamat lengkap sekolah"
                                        class="form-control @error('alamat') is-invalid @enderror"
                                        value="{{ old('alamat') }}">
                                    @error('alamat')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Provinsi</label>
                                    <select required name="provinsi" id="provinsi"
                                        class="form-control select2 @error('provinsi') is-invalid @enderror">
                                        <option value="">-- pilih provinsi --</option>
                                    </select>
                                    @error('provinsi')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Kabupaten</label>
                                    <select required name="kabupaten" id="kabupaten"
                                        class="form-control select2 @error('kabupaten') is-invalid @enderror" disabled>
                                        <option value="">-- pilih kabupaten --</option>
                                    </select>
                                    @error('kabupaten')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Kecamatan</label>
                                    <select required name="kecamatan" id="kecamatan"
                                        class="form-control select2 @error('kecamatan') is-invalid @enderror" disabled>
                                        <option value="">-- pilih kecamatan --</option>
                                    </select>
                                    @error('kecamatan')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>


                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>* No. Telepon/WA</label>
                                    <input name="no_telepon" id="no_telepon" type="number" min="1"
                                        class="form-control @error('no_telepon') is-invalid @enderror"
                                        value="{{ old('no_telepon') }}" placeholder="Kontak resmi sekolah" required>
                                    @error('no_telepon')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>* Email Sekolah</label>
                                    <input name="email" id="email" type="text"
                                        class="form-control @error('email') is-invalid @enderror"
                                        value="{{ old('email') }}" placeholder="Email operasional sekolah" required>
                                    @error('email')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Website Sekolah (jika ada)</label>
                                    <input name="website_url" id="website_url" type="text"
                                        class="form-control @error('website_url') is-invalid @enderror"
                                        value="{{ old('website_url') }}" placeholder="">
                                    @error('website_url')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                        </div>



                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tahun berdiri</label>
                                    <input name="tahun_berdiri" id="tahun_berdiri" type="number"
                                        class="form-control @error('tahun_berdiri') is-invalid @enderror"
                                        value="{{ old('tahun_berdiri') }}" placeholder="opsional">
                                    @error('tahun_berdiri')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Koordinat GPS (bisa cek <a class="text-primary"
                                            href="https://www.google.com/maps" target="_blank">disini</a>, kemudian cari
                                        lokasi dan klik kanan pada titik merah)</label>
                                    <input name="koordinat" id="koordinat" type="text"
                                        class="form-control @error('koordinat') is-invalid @enderror"
                                        value="{{ old('koordinat') }}" placeholder="Untuk peta (latitude/longitude)">
                                    @error('koordinat')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="h3">Data Kepala Sekolah</div>
                        <hr>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>* Nama Lengkap</label>
                                    <input name="nama_kepsek" id="nama_kepsek" type="text"
                                        class="form-control @error('nama_kepsek') is-invalid @enderror"
                                        value="{{ old('nama_kepsek') }}" placeholder="sesuai SK" required>
                                    @error('nama_kepsek')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Apakah ASN ?</label>
                                    <select required name="asn_opsi" id="asn_opsi"
                                        class="form-control @error('asn_opsi') is-invalid @enderror">
                                        <option value="">-- Pilih ya/tidak --</option>
                                        <option value="ya" @selected(old('asn_opsi') === 'ya')>Ya</option>
                                        <option value="tidak" @selected(old('asn_opsi') === 'tidak')>Tidak</option>
                                    </select>
                                    @error('asn_opsi')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3" id="nip_opsi" style="display:  none;">
                                <div class="form-group">
                                    <label>* NIP</label>
                                    <input name="nip_kepsek" id="nip_kepsek" type="text" placeholder=""
                                        class="form-control @error('nip_kepsek') is-invalid @enderror"
                                        value="{{ old('nip_kepsek') }}">
                                    @error('nip_kepsek')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>* NIK</label>
                                    <input required name="nik_kepsek" id="nik_kepsek" type="text" placeholder=""
                                        class="form-control @error('nik_kepsek') is-invalid @enderror"
                                        value="{{ old('nik_kepsek') }}">
                                    @error('nik_kepsek')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>No. SK Kepala Sekolah </label>
                                    <input name="no_sk" id="no_sk" type="text"
                                        class="form-control @error('no_sk') is-invalid @enderror"
                                        value="{{ old('no_sk') }}" placeholder="opsional">
                                    @error('no_sk')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>* No Telepon/WA</label>
                                    <input name="no_telp_kepsek" id="no_telp_kepsek" type="number" min="1"
                                        class="form-control @error('no_telp_kepsek') is-invalid @enderror"
                                        value="{{ old('no_telp_kepsek') }}" required placeholder="">
                                    @error('no_telp_kepsek')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Email</label>
                                    <input name="email_kepsek" id="email_kepsek" type="text"
                                        class="form-control @error('email_kepsek') is-invalid @enderror"
                                        value="{{ old('email_kepsek') }}" placeholder="opsional">
                                    @error('email_kepsek')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                        </div>
                        <br>
                        <div class="h3">Data Guru</div>
                        <hr>

                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Jumlah Guru</label>
                                    <input name="jumlah_guru" id="jumlah_guru" type="number" min="0"
                                        class="form-control @error('jumlah_guru') is-invalid @enderror"
                                        placeholder="total" value="{{ old('jumlah_guru', 0) }}" required>
                                    @error('jumlah_guru')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Jumlah Guru PNS</label>
                                    <input name="jumlah_guru_pns" id="jumlah_guru_pns" type="number" min="0"
                                        class="form-control @error('jumlah_guru_pns') is-invalid @enderror"
                                        placeholder="total" value="{{ old('jumlah_guru_pns', 0) }}" required>
                                    @error('jumlah_guru_pns')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Jumlah Honorer/PPPK</label>
                                    <input name="jumlah_honorer" id="jumlah_honorer" type="number" min="0"
                                        class="form-control @error('jumlah_honorer') is-invalid @enderror"
                                        placeholder="total" value="{{ old('jumlah_honorer', 0) }}" required>
                                    @error('jumlah_honorer')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Jumlah Tenaga Kependidikan</label>
                                    <input name="jumlah_kependidikan" id="jumlah_kependidikan" type="number"
                                        min="0" class="form-control @error('jumlah_kependidikan') is-invalid @enderror"
                                        value="{{ old('jumlah_kependidikan', 0) }}"
                                        placeholder="total TU, Pustakawan, Dll" required>
                                    @error('jumlah_kependidikan')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                        </div>

                        <div class="row">

                            <div class="col-md-4">
                                <label>Bidang Studi (opsional)</label>
                                <textarea name="bidang_studi" id="" cols="40" rows="5"
                                    class="form-control @error('bidang_studi') is-invalid @enderror"
                                    placeholder="Misal: Matematika: 3 orang">{{ old('bidang_studi') }}</textarea>
                                @error('bidang_studi')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                        <br>
                        <div class="h3">Data Siswa</div>
                        <hr>

                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Jumlah SIswa</label>
                                    <input name="jumlah_siswa" id="jumlah_siswa" type="number" min="0"
                                        class="form-control @error('jumlah_siswa') is-invalid @enderror"
                                        placeholder="total" value="{{ old('jumlah_siswa', 0) }}" required>
                                    @error('jumlah_siswa')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Jumlah Siswa laki-laki</label>
                                    <input name="jumlah_siswa_pria" id="jumlah_siswa_pria" type="number" min="0"
                                        class="form-control @error('jumlah_siswa_pria') is-invalid @enderror"
                                        placeholder="total" value="{{ old('jumlah_siswa_pria', 0) }}" required>
                                    @error('jumlah_siswa_pria')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Jumlah Siswa perempuan</label>
                                    <input name="jumlah_siswa_perempuan" id="jumlah_siswa_perempuan" type="number"
                                        min="0" class="form-control @error('jumlah_siswa_perempuan') is-invalid @enderror"
                                        placeholder="total" value="{{ old('jumlah_siswa_perempuan', 0) }}" required>
                                    @error('jumlah_siswa_perempuan')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                        </div>

                        <div class="row">

                            <div class="col-md-4">
                                <label>Jumlah per Kelas (opsional)</label>
                                <textarea name="jumlah_siswa_per_kelas" id="" cols="40" rows="5"
                                    class="form-control @error('jumlah_siswa_per_kelas') is-invalid @enderror"
                                    placeholder="Misal : kelas 1 : 30 orang">{{ old('jumlah_siswa_per_kelas') }}</textarea>
                                @error('jumlah_siswa_per_kelas')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>

                        <br>
                        <div class="h3">Data Fasilitas Sekolah</div>
                        <hr>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Ruang Kelas</label>
                                    <input name="jumlah_kelas" id="jumlah_kelas" type="number" min="0"
                                        class="form-control @error('jumlah_kelas') is-invalid @enderror"
                                        placeholder="total" value="{{ old('jumlah_kelas', 0) }}" required>
                                    @error('jumlah_kelas')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Laboratorium</label>
                                    <select name="laboratorium" class="form-control @error('laboratorium') is-invalid @enderror" required>
                                        <option value="">-- pilih --</option>
                                        <option value="tidak_ada" @selected(old('laboratorium') === 'tidak_ada')>Tidak Ada</option>
                                        <option value="ipa" @selected(old('laboratorium') === 'ipa')>IPA</option>
                                        <option value="komputer" @selected(old('laboratorium') === 'komputer')>Komputer</option>
                                        <option value="keduanya" @selected(old('laboratorium') === 'keduanya')>IPA & Komputer</option>
                                    </select>
                                    @error('laboratorium')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Perpustakaan</label>
                                    <select name="perpustakaan" class="form-control @error('perpustakaan') is-invalid @enderror" required>
                                        <option value="">-- pilih --</option>
                                        <option value="ada" @selected(old('perpustakaan') === 'ada')>Ada</option>
                                        <option value="tidak_ada" @selected(old('perpustakaan') === 'tidak_ada')>Tidak ada</option>
                                    </select>
                                    @error('perpustakaan')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Ruang Guru</label>
                                    <select name="ruang_guru" class="form-control @error('ruang_guru') is-invalid @enderror" required>
                                        <option value="">-- pilih --</option>
                                        <option value="ada" @selected(old('ruang_guru') === 'ada')>Ada</option>
                                        <option value="tidak_ada" @selected(old('ruang_guru') === 'tidak_ada')>Tidak ada</option>
                                    </select>
                                    @error('ruang_guru')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Jumlah Toilet Siswa dan Guru</label>
                                    <input name="jumlah_toilet" id="jumlah_toilet" type="number" min="0"
                                        class="form-control @error('jumlah_toilet') is-invalid @enderror"
                                        placeholder="total" value="{{ old('jumlah_toilet') }}" required>
                                    @error('jumlah_toilet')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                        </div>

                        <div class="row">

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Lapangan Olahraga</label>
                                    <select name="lapangan_olahraga" class="form-control @error('lapangan_olahraga') is-invalid @enderror" required>
                                        <option value="">-- pilih --</option>
                                        <option value="ada" @selected(old('lapangan_olahraga') === 'ada')>Ada</option>
                                        <option value="tidak_ada" @selected(old('lapangan_olahraga') === 'tidak_ada')>Tidak ada</option>
                                    </select>
                                    @error('lapangan_olahraga')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-5">
                                <label>Fasilitas IT</label>
                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fasilitas_it[]"
                                            value="komputer" id="it_komputer">
                                        <label class="form-check-label" for="it_komputer">Komputer</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fasilitas_it[]"
                                            value="internet" id="it_internet">
                                        <label class="form-check-label" for="it_internet">Internet</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fasilitas_it[]"
                                            value="proyektor" id="it_proyektor">
                                        <label class="form-check-label" for="it_proyektor">Proyektor</label>
                                    </div>
                                    <input type="text" name="fasilitas_it_tambahan"
                                        class="form-control mt-2 @error('fasilitas_it_tambahan') is-invalid @enderror"
                                        value="{{ old('fasilitas_it_tambahan') }}"
                                        placeholder="Tambahan (opsional), pakai koma (,) untuk pemisah">
                                    <small class="text-muted">Contoh: Smart TV, Tablet, Laptop</small>
                                    @error('fasilitas_it')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror

                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Akses Internet</label>
                                    <select name="akses_internet" class="form-control @error('akses_internet') is-invalid @enderror" required>
                                        <option value="">-- pilih --</option>
                                        <option value="ada" @selected(old('akses_internet') === 'ada')>Ada</option>
                                        <option value="tidak_ada" @selected(old('akses_internet') === 'tidak_ada')>Tidak ada</option>
                                    </select>
                                    @error('akses_internet')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                        </div>

                        <br>
                        <div class="h3">Program dan Kegiatan</div>
                        <hr>

                        <div class="row">
                            <div class="col-md-4">
                                <label>Ekstrakurikuler</label>
                                <textarea name="ekstrakurikuler" cols="40" rows="4"
                                    class="form-control @error('ekstrakurikuler') is-invalid @enderror"
                                    placeholder="Pramuka, Olahraga, Seni, dll" required>{{ old('ekstrakurikuler') }}</textarea>
                                @error('ekstrakurikuler')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>


                            <div class="col-md-4">
                                <label>Program Unggulan Sekolah</label>
                                <textarea name="program_unggulan" cols="40" rows="4"
                                    class="form-control @error('program_unggulan') is-invalid @enderror"
                                    placeholder="Adiwiyata, Digital School, Pesantren Kilat, dll" required>{{ old('program_unggulan') }}</textarea>
                                @error('program_unggulan')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>


                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Jam Belajar</label>
                                    <select name="jam_belajar" class="form-control @error('jam_belajar') is-invalid @enderror" required>
                                        <option value="">-- pilih --</option>
                                        <option value="pagi" @selected(old('jam_belajar') === 'pagi')>Pagi (Pulang siang)</option>
                                        <option value="full_day" @selected(old('jam_belajar') === 'full_day')>Full Day School</option>
                                    </select>
                                    @error('jam_belajar')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                        </div>

                        <br>
                        <div class="h3">Upload Dokumen Pendukung</div>
                        <hr>


                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label>Foto Tampak Depan Sekolah</label>
                                <input type="file" name="foto_depan"
                                    class="form-control @error('foto_depan') is-invalid @enderror" accept="image/*">
                                @error('foto_depan')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>


                            <div class="col-md-4">
                                <label>Logo Sekolah</label>
                                <input type="file" name="logo_sekolah"
                                    class="form-control @error('logo_sekolah') is-invalid @enderror" accept="image/*">
                                @error('logo_sekolah')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>


                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label>Denah Lokasi / Titik Koordinat</label>
                                <input type="file" name="denah_lokasi"
                                    class="form-control @error('denah_lokasi') is-invalid @enderror"
                                    accept="image/*,application/pdf">
                                <small class="text-muted">Boleh upload gambar atau PDF</small>
                                @error('denah_lokasi')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>


                            <div class="col-md-4">
                                <label>Struktur Organisasi (Opsional)</label>
                                <input type="file" name="struktur_organisasi"
                                    class="form-control @error('struktur_organisasi') is-invalid @enderror"
                                    accept="image/*,application/pdf">
                                @error('struktur_organisasi')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>


                    </div>


                    <div class="card-footer text-right">
                        <a href="{{ route('user.index') }}" class="btn btn-danger mx-5">Kembali</a>
                        <button class="btn btn-primary" type="submit">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>


    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
        <script>
            $(document).ready(function() {
                @if ($errors->any())
                    const firstInvalidField = document.querySelector('form .is-invalid');
                    if (firstInvalidField) {
                        (firstInvalidField.closest('.form-group') || firstInvalidField)
                            .scrollIntoView({ block: 'center' });
                    }
                @endif

                $('.select2').select2();

                $('#asn_opsi').on('change', function() {
                    let status = $(this).val()
                    $('#nip_opsi').hide()
                    return status == 'ya' ? $('#nip_opsi').show() : $('#nip_opsi').hide()
                })

            });
            $(document).ready(function() {
                const API_BASE_URL = 'https://www.emsifa.com/api-wilayah-indonesia/api/';

                // Load Provinsi saat halaman dimuat
                loadProvinsi();

                // Event listener untuk Provinsi
                $('#provinsi').on('change', function() {
                    const provinsiId = $(this).find(':selected').data('id');
                    // Reset dropdown kabupaten, kecamatan, kelurahan
                    resetDropdown('#kabupaten');
                    resetDropdown('#kecamatan');
                    resetDropdown('#kelurahan');

                    if (provinsiId) {
                        loadKabupaten(provinsiId);
                    }
                });

                // Event listener untuk Kabupaten
                $('#kabupaten').on('change', function() {
                    const kabupatenId = $(this).find(':selected').data('id');

                    // Reset dropdown kecamatan dan kelurahan
                    resetDropdown('#kecamatan');
                    resetDropdown('#kelurahan');

                    if (kabupatenId) {
                        loadKecamatan(kabupatenId);
                    }
                });

                // Event listener untuk Kecamatan
                $('#kecamatan').on('change', function() {
                    const kecamatanId = $(this).val();
                    // Reset dropdown kelurahan
                    resetDropdown('#kelurahan');

                });

                // Fungsi untuk load Provinsi
                function loadProvinsi() {
                    $.ajax({
                        url: `${API_BASE_URL}/provinces.json`,
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            let options = '<option value="">-- pilih provinsi --</option>';
                            data.forEach(function(provinsi) {
                                options +=
                                    `<option value="${provinsi.name}" data-id="${provinsi.id}">${provinsi.name}</option>`;
                            });
                            $('#provinsi').html(options);
                            $('#provinsi').prop('disabled', false);
                        },
                        error: function(xhr, status, error) {
                            console.error('Error loading provinsi:', error);
                            alert('Gagal memuat data provinsi');
                        }
                    });
                }

                // Fungsi untuk load Kabupaten
                function loadKabupaten(provinsiId) {
                    $.ajax({
                        url: `${API_BASE_URL}/regencies/${provinsiId}.json`,
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            let options = '<option value="">-- pilih kabupaten --</option>';
                            data.forEach(function(kabupaten) {
                                options +=
                                    `<option value="${kabupaten.name}" data-id="${kabupaten.id}">${kabupaten.name}</option>`;
                            });
                            $('#kabupaten').html(options);
                            $('#kabupaten').prop('disabled', false);
                        },
                        error: function(xhr, status, error) {
                            console.error('Error loading kabupaten:', error);
                            alert('Gagal memuat data kabupaten');
                        }
                    });
                }

                // Fungsi untuk load Kecamatan
                function loadKecamatan(kabupatenId) {
                    $.ajax({
                        url: `${API_BASE_URL}/districts/${kabupatenId}.json`,
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            let options = '<option value="">-- pilih kecamatan --</option>';
                            data.forEach(function(kecamatan) {
                                options +=
                                    `<option value="${kecamatan.name}" data-id="${kecamatan.id}">${kecamatan.name}</option>`;
                            });
                            $('#kecamatan').html(options);
                            $('#kecamatan').prop('disabled', false);
                        },
                        error: function(xhr, status, error) {
                            console.error('Error loading kecamatan:', error);
                            alert('Gagal memuat data kecamatan');
                        }
                    });
                }


                // Fungsi untuk reset dropdown
                function resetDropdown(selector) {
                    const label = $(selector).find('option:first').text();
                    $(selector).html(`<option value="">${label}</option>`);
                    $(selector).prop('disabled', true);
                }

                

            });
        </script>
    @endpush
@endsection
