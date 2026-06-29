@extends('layouts.app', ['title' => 'Data Sekolah'])
@section('content')
    @push('styles')
        <link rel="stylesheet" href="{{ asset('library/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('library/datatables.net-select-bs4/css/select.bootstrap4.min.css') }}">
    @endpush
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Detail Data Sekolah</h1>
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

                            <form action="{{ route('update.data-sekolah', $sekolah->id) }}" method="POST"
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
                                                <input name="npsn_sekolah" type="number" min="0"
                                                    class="form-control" required
                                                    value="{{ old('npsn_sekolah', $sekolah->npsn_sekolah) }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>* Jenjang Sekolah</label>
                                                <select required name="bp_sekolah" class="form-control">
                                                    <option value="">-- pilih jenjang sekolah --</option>
                                                    <option value="TK"
                                                        {{ old('bp_sekolah', $sekolah->bp_sekolah) == 'TK' ? 'selected' : '' }}>
                                                        TK
                                                    </option>
                                                    <option value="SD"
                                                        {{ old('bp_sekolah', $sekolah->bp_sekolah) == 'SD' ? 'selected' : '' }}>
                                                        SD
                                                    </option>
                                                    <option value="SMP"
                                                        {{ old('bp_sekolah', $sekolah->bp_sekolah) == 'SMP' ? 'selected' : '' }}>
                                                        SMP</option>
                                                    <option value="SMA/SMK"
                                                        {{ old('bp_sekolah', $sekolah->bp_sekolah) == 'SMA/SMK' ? 'selected' : '' }}>
                                                        SMA/SMK Sederajat</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>* Status Sekolah</label>
                                                <select required name="status_sekolah" class="form-control">
                                                    <option value="">-- pilih status sekolah --</option>
                                                    <option value="Negeri"
                                                        {{ old('status_sekolah', $sekolah->status_sekolah) == 'Negeri' ? 'selected' : '' }}>
                                                        Negeri</option>
                                                    <option value="Swasta"
                                                        {{ old('status_sekolah', $sekolah->status_sekolah) == 'Swasta' ? 'selected' : '' }}>
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

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>* No. Telepon/WA</label>
                                                <input name="no_telepon" id="no_telepon" type="number" min="1"
                                                    class="form-control" placeholder="Kontak resmi sekolah"
                                                    value="{{ old('no_telepon', $sekolah->no_telepon) }}" required>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>* Email Sekolah</label>
                                                <input name="email" id="email" type="text" class="form-control"
                                                    placeholder="Email operasional sekolah"
                                                    value="{{ old('email', $sekolah->email) }}" required>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Website Sekolah (jika ada)</label>
                                                <input name="website_url" id="website_url" type="text"
                                                    class="form-control" placeholder=""
                                                    value="{{ old('website_url', $sekolah->website_url) }}">
                                            </div>
                                        </div>

                                    </div>



                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Tahun berdiri</label>
                                                <input name="tahun_berdiri" id="tahun_berdiri" type="number"
                                                    class="form-control" placeholder="opsional"
                                                    value="{{ old('tahun_berdiri', $sekolah->tahun_berdiri) }}">
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label>Koordinat GPS (bisa cek <a class="text-primary"
                                                        href="https://www.google.com/maps" target="_blank">disini</a>,
                                                    kemudian cari
                                                    lokasi dan klik kanan pada titik merah)</label>
                                                <input name="koordinat" id="koordinat" type="text"
                                                    class="form-control" placeholder="Untuk peta (latitude/longitude)"
                                                    value="{{ old('koordinat', $sekolah->koordinat) }}">
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
                                                    class="form-control" placeholder="sesuai SK"
                                                    value="{{ old('nama_kepsek', $sekolah->nama_kepsek) }}" required>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Apakah ASN ?</label>
                                                <select required name="asn_opsi" id="asn_opsi" class="form-control">
                                                    <option value="">-- Pilih ya/tidak --</option>
                                                    <option {{ $sekolah->asn_opsi == 'ya' ? 'selected' : '' }}
                                                        value="ya">Ya</option>
                                                    <option {{ $sekolah->asn_opsi == 'tidak' ? 'selected' : '' }}
                                                        value="tidak">Tidak</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-3" id="nip_opsi" style="display:  none;">
                                            <div class="form-group">
                                                <label>* NIP</label>
                                                <input name="nip_kepsek" id="nip_kepsek" type="text" placeholder=""
                                                    class="form-control"
                                                    value="{{ old('nip_kepsek', $sekolah->nip_kepsek) }}">
                                            </div>
                                        </div>

                                        {{-- <div class="col-md-3">
                                            <div class="form-group">
                                                <label>* NIP</label>
                                                <input required name="nik_kepsek" id="nik_kepsek" type="text"
                                                    placeholder="" class="form-control"
                                                    value="{{ old('nik_kepsek', $sekolah->nik_kepsek) }}">
                                            </div>
                                        </div> --}}
                                    </div>

                                    <div class="row">

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>No. SK Kepala Sekolah </label>
                                                <input name="no_sk" id="no_sk" type="text" class="form-control"
                                                    placeholder="opsional" value="{{ old('no_sk', $sekolah->no_sk) }}">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>* No Telepon/WA</label>
                                                <input name="no_telp_kepsek" id="no_telp_kepsek" type="number"
                                                    min="1" class="form-control" required placeholder=""
                                                    value="{{ old('no_telp_kepsek', $sekolah->no_telp_kepsek) }}">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Email</label>
                                                <input name="email_kepsek" id="email_kepsek" type="text"
                                                    class="form-control" placeholder="opsional"
                                                    value="{{ old('email_kepsek', $sekolah->email_kepsek) }}">
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
                                                    class="form-control" placeholder="total" value="0" required
                                                    value="{{ old('jumlah_guru', $sekolah->jumlah_guru) }}">
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Jumlah Guru PNS</label>
                                                <input name="jumlah_guru_pns" id="jumlah_guru_pns" type="number"
                                                    min="0" class="form-control" placeholder="total"
                                                    value="{{ old('jumlah_guru_pns', $sekolah->jumlah_guru_pns) }}"
                                                    value="0" required>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Jumlah Honorer/PPPK</label>
                                                <input name="jumlah_honorer" id="jumlah_honorer" type="number"
                                                    min="0" class="form-control" placeholder="total"
                                                    value="{{ old('jumlah_honorer', $sekolah->jumlah_honorer) }}"
                                                    value="0" required>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Jumlah Tenaga Kependidikan</label>
                                                <input name="jumlah_kependidikan" id="jumlah_kependidikan" type="number"
                                                    min="0" class="form-control" value="0"
                                                    value="{{ old('jumlah_kependidikan', $sekolah->jumlah_kependidikan) }}"
                                                    placeholder="total TU, Pustakawan, Dll" required>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="row">

                                        <div class="col-md-4">
                                            <label>Bidang Studi (opsional)</label>
                                            <textarea name="bidang_studi" id="" cols="40" rows="5"
                                                placeholder="Misal: Matematika: 3 orang">
                                            {{ old('bidang_studi', $sekolah->bidang_studi ?? '') }}
                                            </textarea>
                                        </div>

                                    </div>
                                    <br>
                                    <div class="h3">Data Siswa</div>
                                    <hr>

                                    <div class="row">
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Jumlah SIswa</label>
                                                <input name="jumlah_siswa" id="jumlah_siswa" type="number"
                                                    min="0" class="form-control" placeholder="total"
                                                    value="{{ old('jumlah_siswa', $sekolah->jumlah_siswa) }}"
                                                    value="0" required>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Jumlah Siswa laki-laki</label>
                                                <input name="jumlah_siswa_pria" id="jumlah_siswa_pria" type="number"
                                                    min="0" class="form-control" placeholder="total"
                                                    value="{{ old('jumlah_siswa_pria', $sekolah->jumlah_siswa_pria) }}"
                                                    value="0" required>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Jumlah Siswa perempuan</label>
                                                <input name="jumlah_siswa_perempuan" id="jumlah_siswa_perempuan"
                                                    type="number" min="0" class="form-control"
                                                    value="{{ old('jumlah_siswa_perempuan', $sekolah->jumlah_siswa_perempuan) }}"
                                                    placeholder="total" value="0" required>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="row">

                                        <div class="col-md-4">
                                            <label>Jumlah per Kelas (opsional)</label>
                                            <textarea name="jumlah_siswa_per_kelas" id="" cols="40" rows="5"
                                                placeholder="Misal : kelas 1 : 30 orang">
                                                {{ old('jumlah_siswa_per_kelas', $sekolah->jumlah_siswa_per_kelas) }}
                                            </textarea>
                                        </div>

                                    </div>

                                    <br>
                                    <div class="h3">Data Fasilitas Sekolah</div>
                                    <hr>

                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Ruang Kelas</label>
                                                <input name="jumlah_kelas" id="jumlah_kelas" type="number"
                                                    min="0" class="form-control" placeholder="total"
                                                    value="{{ old('jumlah_kelas', $sekolah->jumlah_kelas) }}"
                                                    value="0" required>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Laboratorium</label>
                                                <select name="laboratorium" class="form-control" required>
                                                    <option value="">-- pilih --</option>
                                                    <option {{ $sekolah->laboratorium == 'tidak_ada' ? 'selected' : '' }}
                                                        value="tidak_ada">Tidak Ada</option>
                                                    <option {{ $sekolah->laboratorium == 'ipa' ? 'selected' : '' }}
                                                        value="ipa">IPA</option>
                                                    <option {{ $sekolah->laboratorium == 'komputer' ? 'selected' : '' }}
                                                        value="komputer">Komputer</option>
                                                    <option {{ $sekolah->laboratorium == 'keduanya' ? 'selected' : '' }}
                                                        value="keduanya">IPA & Komputer</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Perpustakaan</label>
                                                <select name="perpustakaan" class="form-control" required>
                                                    <option value="">-- pilih --</option>
                                                    <option {{ $sekolah->perpustakaan == 'ada' ? 'selected' : '' }}
                                                        value="ada">Ada</option>
                                                    <option {{ $sekolah->perpustakaan == 'tidak_ada' ? 'selected' : '' }}
                                                        value="tidak_ada">Tidak ada</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Ruang Guru</label>
                                                <select name="ruang_guru" class="form-control" required>
                                                    <option value="">-- pilih --</option>
                                                    <option {{ $sekolah->ruang_guru == 'ada' ? 'selected' : '' }}
                                                        value="ada">Ada</option>
                                                    <option {{ $sekolah->ruang_guru == 'tidak_ada' ? 'selected' : '' }}
                                                        value="tidak_ada">Tidak ada</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Jumlah Toilet Siswa dan Guru</label>
                                                <input name="jumlah_toilet" id="jumlah_toilet" type="number"
                                                    value="{{ old('jumlah_toilet', $sekolah->jumlah_toilet) }}"
                                                    min="0" class="form-control" placeholder="total" required>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="row">

                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Lapangan Olahraga</label>
                                                <select name="lapangan_olahraga" class="form-control" required>
                                                    <option value="">-- pilih --</option>
                                                    <option {{ $sekolah->lapangan_olahraga == 'ada' ? 'selected' : '' }}
                                                        value="ada">Ada</option>
                                                    <option
                                                        {{ $sekolah->lapangan_olahraga == 'tidak_ada' ? 'selected' : '' }}
                                                        value="tidak_ada">Tidak ada</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-5">
                                            <label>Fasilitas IT</label>
                                            <div class="form-group">
                                                @php
                                                    // Decode JSON fasilitas_it dari database
                                                    $fasilitasItArray = [];
                                                    if (isset($sekolah->fasilitas_it)) {
                                                        $fasilitasItArray = is_string($sekolah->fasilitas_it)
                                                            ? json_decode($sekolah->fasilitas_it, true) ?? []
                                                            : (is_array($sekolah->fasilitas_it)
                                                                ? $sekolah->fasilitas_it
                                                                : []);
                                                    }

                                                    // Atau bisa pakai old() untuk handle validation error
                                                    $fasilitasItArray = old('fasilitas_it', $fasilitasItArray);
                                                @endphp

                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="fasilitas_it[]"
                                                        value="komputer" id="it_komputer"
                                                        {{ in_array('komputer', $fasilitasItArray) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="it_komputer">Komputer</label>
                                                </div>

                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="fasilitas_it[]"
                                                        value="internet" id="it_internet"
                                                        {{ in_array('internet', $fasilitasItArray) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="it_internet">Internet</label>
                                                </div>

                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="fasilitas_it[]"
                                                        value="proyektor" id="it_proyektor"
                                                        {{ in_array('proyektor', $fasilitasItArray) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="it_proyektor">Proyektor</label>
                                                </div>

                                                <input type="text" name="fasilitas_it_tambahan"
                                                    class="form-control mt-2"
                                                    value="{{ old('fasilitas_it_tambahan', $sekolah->fasilitas_it_tambahan ?? '') }}"
                                                    placeholder="Tambahan (opsional), pakai koma (,) untuk pemisah">
                                                <small class="text-muted">Contoh: Smart TV, Tablet, Laptop</small>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Akses Internet</label>
                                                <select name="akses_internet" class="form-control" required>
                                                    <option value="">-- pilih --</option>
                                                    <option {{ $sekolah->akses_internet == 'ada' ? 'selected' : '' }}
                                                        value="ada">Ada</option>
                                                    <option {{ $sekolah->akses_internet == 'tidak_ada' ? 'selected' : '' }}
                                                        value="tidak_ada">Tidak ada</option>
                                                </select>
                                            </div>
                                        </div>

                                    </div>

                                    <br>
                                    <div class="h3">Program dan Kegiatan</div>
                                    <hr>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <label>Ekstrakurikuler</label>
                                            <textarea name="ekstrakurikuler" cols="40" rows="5" class="form-control"
                                                placeholder="Pramuka, Olahraga, Seni, dll" required>
                                            {{ old('ekstrakurikuler', $sekolah->ekstrakurikuler) }}
                                            </textarea>
                                        </div>


                                        <div class="col-md-4">
                                            <label>Program Unggulan Sekolah</label>
                                            <textarea name="program_unggulan" cols="40" rows="5" class="form-control"
                                                placeholder="Adiwiyata, Digital School, Pesantren Kilat, dll" required>
                                            {{ old('program_unggulan', $sekolah->program_unggulan) }}
                                            </textarea>
                                        </div>


                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Jam Belajar</label>
                                                <select name="jam_belajar" class="form-control" required>
                                                    <option value="">-- pilih --</option>
                                                    <option {{ $sekolah->jam_belajar == 'pagi' ? 'selected' : '' }}
                                                        value="pagi">Pagi (Pulang siang)</option>
                                                    <option {{ $sekolah->jam_belajar == 'full_day' ? 'selected' : '' }}
                                                        value="full_day">Full Day School</option>
                                                </select>
                                            </div>
                                        </div>

                                    </div>

                                    <br>
                                    <div class="h3">Upload Dokumen Pendukung</div>
                                    <hr>


                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label>Foto Tampak Depan Sekolah</label>
                                            <input type="file" name="foto_depan" id="foto_depan" class="form-control"
                                                accept="image/*" onchange="previewImage(event, 'preview_foto_depan')">
                                            <small class="text-muted">Upload gambar baru untuk mengganti</small>

                                            <!-- Preview Image -->
                                            <div class="mt-2">
                                                @if (isset($sekolah->foto_depan) && $sekolah->foto_depan)
                                                    <img id="preview_foto_depan"
                                                        src="{{ asset('upload/sekolah/foto_depan/' . $sekolah->foto_depan) }}"
                                                        class="img-thumbnail"
                                                        style="max-width: 200px; max-height: 200px;">
                                                @else
                                                    <img id="preview_foto_depan" src="#" alt="Preview"
                                                        class="img-thumbnail d-none"
                                                        style="max-width: 200px; max-height: 200px;">
                                                @endif
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <label>Logo Sekolah</label>
                                            <input type="file" name="logo_sekolah" id="logo_sekolah"
                                                class="form-control" accept="image/*"
                                                onchange="previewImage(event, 'preview_logo_sekolah')">
                                            <small class="text-muted">Upload gambar baru untuk mengganti</small>

                                            <!-- Preview Image -->
                                            <div class="mt-2">
                                                @if (isset($sekolah->logo_sekolah) && $sekolah->logo_sekolah)
                                                    <img id="preview_logo_sekolah"
                                                        src="{{ asset('upload/sekolah/logo_sekolah/' . $sekolah->logo_sekolah) }}"
                                                        class="img-thumbnail"
                                                        style="max-width: 200px; max-height: 200px;">
                                                @else
                                                    <img id="preview_logo_sekolah" src="#" alt="Preview"
                                                        class="img-thumbnail d-none"
                                                        style="max-width: 200px; max-height: 200px;">
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label>Denah Lokasi / Titik Koordinat</label>
                                            <input type="file" name="denah_lokasi" id="denah_lokasi"
                                                class="form-control" accept="image/*,application/pdf"
                                                onchange="previewFile(event, 'preview_denah_lokasi')">
                                            <small class="text-muted">Boleh upload gambar atau PDF</small>

                                            <!-- Preview -->
                                            <div class="mt-2" id="preview_denah_lokasi">
                                                @if (isset($sekolah->denah_lokasi) && $sekolah->denah_lokasi)
                                                    @if (Str::endsWith($sekolah->denah_lokasi, '.pdf'))
                                                        <a href="{{ asset('upload/sekolah/denah_lokasi/' . $sekolah->denah_lokasi) }}"
                                                            target="_blank" class="btn btn-sm btn-info">
                                                            <i class="fas fa-file-pdf"></i> Lihat PDF
                                                        </a>
                                                    @else
                                                        <img src="{{ asset('upload/sekolah/denah_lokasi/' . $sekolah->denah_lokasi) }}"
                                                            class="img-thumbnail"
                                                            style="max-width: 200px; max-height: 200px;">
                                                    @endif
                                                @endif
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <label>Struktur Organisasi (Opsional)</label>
                                            <input type="file" name="struktur_organisasi" id="struktur_organisasi"
                                                class="form-control" accept="image/*,application/pdf"
                                                onchange="previewFile(event, 'preview_struktur_organisasi')">
                                            <small class="text-muted">Boleh upload gambar atau PDF</small>

                                            <!-- Preview -->
                                            <div class="mt-2" id="preview_struktur_organisasi">
                                                @if (isset($sekolah->struktur_organisasi) && $sekolah->struktur_organisasi)
                                                    @if (Str::endsWith($sekolah->struktur_organisasi, '.pdf'))
                                                        <a href="{{ asset('upload/sekolah/struktur_organisasi/' . $sekolah->struktur_organisasi) }}"
                                                            target="_blank" class="btn btn-sm btn-info">
                                                            <i class="fas fa-file-pdf"></i> Lihat PDF
                                                        </a>
                                                    @else
                                                        <img src="{{ asset('upload/sekolah/struktur_organisasi/' . $sekolah->struktur_organisasi) }}"
                                                            class="img-thumbnail"
                                                            style="max-width: 200px; max-height: 200px;">
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <div class="card-footer text-right">
                                    <button class="btn btn-lg btn-primary" type="submit">Update Data</button>
                                    <a href="{{ route('show.data-sekolah', $sekolah->id) }}"
                                        class="btn btn-lg btn-warning">Batal</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    @push('scripts')
        <script src="{{ asset('library/datatables/media/js/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('library/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('library/datatables.net-select-bs4/js/select.bootstrap4.min.js') }}"></script>
        <script>
            $(document).ready(function() {
                $('.select2').select2();

                // Data sekolah yang sudah ada
                const currentProvinsi = "{{ old('provinsi', $sekolah->provinsi) }}";
                const currentKabupaten = "{{ old('kabupaten', $sekolah->kabupaten) }}";
                const currentKecamatan = "{{ old('kecamatan', $sekolah->kecamatan) }}";

                const API_BASE_URL = 'https://www.emsifa.com/api-wilayah-indonesia/api/';

                // Load Provinsi dan set value yang sudah ada
                loadProvinsi();

                $('#provinsi').on('change', function() {
                    const provinsiId = $(this).find(':selected').data('id');
                    resetDropdown('#kabupaten');
                    resetDropdown('#kecamatan');

                    if (provinsiId) {
                        loadKabupaten(provinsiId);
                    }
                });

                $('#kabupaten').on('change', function() {
                    const kabupatenId = $(this).find(':selected').data('id');
                    resetDropdown('#kecamatan');

                    if (kabupatenId) {
                        loadKecamatan(kabupatenId);
                    }
                });

                function loadProvinsi() {
                    $.ajax({
                        url: `${API_BASE_URL}/provinces.json`,
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            let options = '<option value="">-- pilih provinsi --</option>';
                            let selectedProvinsiId = null;

                            data.forEach(function(provinsi) {
                                const selected = provinsi.name === currentProvinsi ? 'selected' :
                                    '';
                                options +=
                                    `<option value="${provinsi.name}" data-id="${provinsi.id}" ${selected}>${provinsi.name}</option>`;

                                if (provinsi.name === currentProvinsi) {
                                    selectedProvinsiId = provinsi.id;
                                }
                            });

                            $('#provinsi').html(options);
                            $('#provinsi').prop('disabled', false);

                            // Load kabupaten jika ada provinsi terpilih
                            if (selectedProvinsiId) {
                                loadKabupaten(selectedProvinsiId);
                            }
                        }
                    });
                }

                function loadKabupaten(provinsiId) {
                    $.ajax({
                        url: `${API_BASE_URL}/regencies/${provinsiId}.json`,
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            let options = '<option value="">-- pilih kabupaten --</option>';
                            let selectedKabupatenId = null;

                            data.forEach(function(kabupaten) {
                                const selected = kabupaten.name === currentKabupaten ? 'selected' :
                                    '';
                                options +=
                                    `<option value="${kabupaten.name}" data-id="${kabupaten.id}" ${selected}>${kabupaten.name}</option>`;

                                if (kabupaten.name === currentKabupaten) {
                                    selectedKabupatenId = kabupaten.id;
                                }
                            });

                            $('#kabupaten').html(options);
                            $('#kabupaten').prop('disabled', false);

                            // Load kecamatan jika ada kabupaten terpilih
                            if (selectedKabupatenId) {
                                loadKecamatan(selectedKabupatenId);
                            }
                        }
                    });
                }

                function loadKecamatan(kabupatenId) {
                    $.ajax({
                        url: `${API_BASE_URL}/districts/${kabupatenId}.json`,
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            let options = '<option value="">-- pilih kecamatan --</option>';

                            data.forEach(function(kecamatan) {
                                const selected = kecamatan.name === currentKecamatan ? 'selected' :
                                    '';
                                options +=
                                    `<option value="${kecamatan.name}" data-id="${kecamatan.id}" ${selected}>${kecamatan.name}</option>`;
                            });

                            $('#kecamatan').html(options);
                            $('#kecamatan').prop('disabled', false);
                        }
                    });
                }

                function resetDropdown(selector) {
                    const label = $(selector).find('option:first').text();
                    $(selector).html(`<option value="">${label}</option>`);
                    $(selector).prop('disabled', true);
                }

                // ASN Toggle
                $('#asn_opsi').on('change', function() {
                    let status = $(this).val();
                    $('#nip_opsi').hide();
                    if (status == 'ya') {
                        $('#nip_opsi').show();
                    }
                });

                // Trigger ASN toggle jika sudah ada value
                $('#asn_opsi').trigger('change');
            });
        </script>

        <script>
            // Preview untuk gambar saja
            function previewImage(event, previewId) {
                const input = event.target;
                const preview = document.getElementById(previewId);
                const card = document.getElementById('card_' + previewId.replace('preview_', ''));

                if (input.files && input.files[0]) {
                    const file = input.files[0];

                    // Validasi ukuran file (max 2MB)
                    if (file.size > 2 * 1024 * 1024) {
                        alert('Ukuran file terlalu besar! Maksimal 2MB');
                        input.value = '';
                        return;
                    }

                    // Validasi tipe file
                    if (!file.type.startsWith('image/')) {
                        alert('File harus berupa gambar!');
                        input.value = '';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        if (card) {
                            card.style.display = 'block';
                        }
                    }
                    reader.readAsDataURL(file);
                }
            }

            // Preview untuk gambar atau PDF
            function previewFile(event, previewId, cardId) {
                const input = event.target;
                const previewContainer = document.getElementById(previewId);
                const card = document.getElementById(cardId);
                const file = input.files[0];

                if (file) {
                    // Validasi ukuran file (max 2MB)
                    if (file.size > 2 * 1024 * 1024) {
                        alert('Ukuran file terlalu besar! Maksimal 2MB');
                        input.value = '';
                        return;
                    }

                    // Clear previous preview
                    previewContainer.innerHTML = '';

                    // Check if PDF
                    if (file.type === 'application/pdf') {
                        previewContainer.innerHTML = `
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-file-pdf"></i> ${file.name}
                        <br><small>File PDF siap diupload</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removePreview('${previewId.replace('preview_', '')}')">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                `;
                        if (card) card.style.display = 'block';
                    } else if (file.type.startsWith('image/')) {
                        // Preview image
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewContainer.innerHTML = `
                        <img src="${e.target.result}" 
                             class="img-fluid rounded" 
                             style="max-height: 150px;">
                        <button type="button" class="btn btn-sm btn-danger mt-2 d-block mx-auto" onclick="removePreview('${previewId.replace('preview_', '')}')">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    `;
                            if (card) card.style.display = 'block';
                        }
                        reader.readAsDataURL(file);
                    } else {
                        alert('Format file tidak didukung!');
                        input.value = '';
                    }
                }
            }

            // Remove preview dan clear input
            function removePreview(fieldName) {
                const input = document.getElementById(fieldName);
                const card = document.getElementById('card_' + fieldName);

                if (input) {
                    input.value = '';
                }
                if (card) {
                    card.style.display = 'none';
                }
            }
        </script>
    @endpush
@endsection
