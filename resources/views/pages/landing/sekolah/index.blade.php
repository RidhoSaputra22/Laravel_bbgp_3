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
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                {{-- <form action="{{ route('user.kegiatan_store') }}" method="POST" enctype="multipart/form-data"> --}}
                <form action="{{ route('user.store.data-sekolah') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    {{-- {{ dd($_GET['kegiatan_id']) }} --}}
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
                                    <input name="nama_sekolah" id="nama_sekolah" type="text" class="form-control"
                                        required placeholder="resmi sesuai Dapodik">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>* NPSN</label>
                                    <input name="npsn_sekolah" id="npsn_sekolah" type="number" min="0"
                                        class="form-control" required placeholder="">
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>* Jenjang Sekolah</label>
                                    <select required name="bp_sekolah" id="bp_sekolah" class="form-control">
                                        <option value="">-- pilih jenjang sekolah --</option>
                                        <option value="TK">TK</option>
                                        <option value="SD">SD</option>
                                        <option value="SMP">SMP</option>
                                        <option value="SMA/SMK">SMA/SMK Sederajat</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>* Status Sekolah</label>
                                    <select required name="status_sekolah" id="status_sekolah" class="form-control">
                                        <option value="">-- pilih status sekolah --</option>
                                        <option value="Negeri">Negeri</option>
                                        <option value="Swasta">Swasta</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>* Akreditasi Sekolah</label>
                                    <select required name="akreditasi" id="akreditasi" class="form-control">
                                        <option value="">-- pilih akreditasi sekolah --</option>
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                        <option value="C">C</option>
                                        <option value="belum">Belum ada</option>
                                    </select>
                                </div>
                            </div>

                        </div>

                        <div class="row">

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>* Alamat Sekolah</label>
                                    <input required name="alamat" id="alamat" type="text"
                                        placeholder="alamat lengkap sekolah" class="form-control">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Provinsi</label>
                                    <select required name="provinsi" id="provinsi" class="form-control select2">
                                        <option value="">-- pilih provinsi --</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Kabupaten</label>
                                    <select required name="kabupaten" id="kabupaten" class="form-control select2"
                                        disabled>
                                        <option value="">-- pilih kabupaten --</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Kecamatan</label>
                                    <select required name="kecamatan" id="kecamatan" class="form-control select2"
                                        disabled>
                                        <option value="">-- pilih kecamatan --</option>
                                    </select>
                                </div>
                            </div>

                            {{-- <div class="col-md-2">
                                <div class="form-group">
                                    <label>Kelurahan</label>
                                    <select required name="kelurahan" id="kelurahan" class="form-control select2"
                                        disabled>
                                        <option value="">-- pilih kelurahan --</option>
                                    </select>
                                </div>
                            </div> --}}

                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>* No. Telepon/WA</label>
                                    <input name="no_telepon" id="no_telepon" type="number" min="1"
                                        class="form-control" placeholder="Kontak resmi sekolah" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>* Email Sekolah</label>
                                    <input name="email" id="email" type="text" class="form-control"
                                        placeholder="Email operasional sekolah" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Website Sekolah (jika ada)</label>
                                    <input name="website_url" id="website_url" type="text" class="form-control"
                                        placeholder="">
                                </div>
                            </div>

                        </div>



                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tahun berdiri</label>
                                    <input name="tahun_berdiri" id="tahun_berdiri" type="number" class="form-control"
                                        placeholder="opsional">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Koordinat GPS (bisa cek <a class="text-primary"
                                            href="https://www.google.com/maps" target="_blank">disini</a>, kemudian cari
                                        lokasi dan klik kanan pada titik merah)</label>
                                    <input name="koordinat" id="koordinat" type="text" class="form-control"
                                        placeholder="Untuk peta (latitude/longitude)">
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
                                    <input name="nama_kepsek" id="nama_kepsek" type="text" class="form-control"
                                        placeholder="sesuai SK" required>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Apakah ASN ?</label>
                                    <select required name="asn_opsi" id="asn_opsi" class="form-control">
                                        <option value="">-- Pilih ya/tidak --</option>
                                        <option value="ya">Ya</option>
                                        <option value="tidak">Tidak</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3" id="nip_opsi" style="display:  none;">
                                <div class="form-group">
                                    <label>* NIP</label>
                                    <input name="nip_kepsek" id="nip_kepsek" type="text" placeholder=""
                                        class="form-control">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>* NIK</label>
                                    <input required name="nik_kepsek" id="nik_kepsek" type="text" placeholder=""
                                        class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>No. SK Kepala Sekolah </label>
                                    <input name="no_sk" id="no_sk" type="text" class="form-control"
                                        placeholder="opsional">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>* No Telepon/WA</label>
                                    <input name="no_telp_kepsek" id="no_telp_kepsek" type="number" min="1"
                                        class="form-control" required placeholder="">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Email</label>
                                    <input name="email_kepsek" id="email_kepsek" type="text" class="form-control"
                                        placeholder="opsional">
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
                                        class="form-control" placeholder="total" value="0" required>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Jumlah Guru PNS</label>
                                    <input name="jumlah_guru_pns" id="jumlah_guru_pns" type="number" min="0"
                                        class="form-control" placeholder="total" value="0" required>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Jumlah Honorer/PPPK</label>
                                    <input name="jumlah_honorer" id="jumlah_honorer" type="number" min="0"
                                        class="form-control" placeholder="total" value="0" required>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Jumlah Tenaga Kependidikan</label>
                                    <input name="jumlah_kependidikan" id="jumlah_kependidikan" type="number"
                                        min="0" class="form-control" value="0"
                                        placeholder="total TU, Pustakawan, Dll" required>
                                </div>
                            </div>

                        </div>

                        <div class="row">

                            <div class="col-md-4">
                                <label>Bidang Studi (opsional)</label>
                                <textarea name="bidang_studi" id="" cols="40" rows="5"
                                    placeholder="Misal: Matematika: 3 orang"></textarea>
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
                                        class="form-control" placeholder="total" value="0" required>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Jumlah Siswa laki-laki</label>
                                    <input name="jumlah_siswa_pria" id="jumlah_siswa_pria" type="number" min="0"
                                        class="form-control" placeholder="total" value="0" required>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Jumlah Siswa perempuan</label>
                                    <input name="jumlah_siswa_perempuan" id="jumlah_siswa_perempuan" type="number"
                                        min="0" class="form-control" placeholder="total" value="0" required>
                                </div>
                            </div>

                        </div>

                        <div class="row">

                            <div class="col-md-4">
                                <label>Jumlah per Kelas (opsional)</label>
                                <textarea name="jumlah_siswa_per_kelas" id="" cols="40" rows="5"
                                    placeholder="Misal : kelas 1 : 30 orang"></textarea>
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
                                        class="form-control" placeholder="total" value="0" required>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Laboratorium</label>
                                    <select name="laboratorium" class="form-control" required>
                                        <option value="">-- pilih --</option>
                                        <option value="tidak_ada">Tidak Ada</option>
                                        <option value="ipa">IPA</option>
                                        <option value="komputer">Komputer</option>
                                        <option value="keduanya">IPA & Komputer</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Perpustakaan</label>
                                    <select name="perpustakaan" class="form-control" required>
                                        <option value="">-- pilih --</option>
                                        <option value="ada">Ada</option>
                                        <option value="tidak_ada">Tidak ada</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Ruang Guru</label>
                                    <select name="ruang_guru" class="form-control" required>
                                        <option value="">-- pilih --</option>
                                        <option value="ada">Ada</option>
                                        <option value="tidak_ada">Tidak ada</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Jumlah Toilet Siswa dan Guru</label>
                                    <input name="jumlah_toilet" id="jumlah_toilet" type="number" min="0"
                                        class="form-control" placeholder="total" required>
                                </div>
                            </div>

                        </div>

                        <div class="row">

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Lapangan Olahraga</label>
                                    <select name="lapangan_olahraga" class="form-control" required>
                                        <option value="">-- pilih --</option>
                                        <option value="ada">Ada</option>
                                        <option value="tidak_ada">Tidak ada</option>
                                    </select>
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
                                    {{-- <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fasilitas_it[]"
                                            value="lainnya" id="it_lainnya">
                                        <label class="form-check-label" for="it_lainnya">Lainnya</label>
                                    </div> --}}
                                    <input type="text" name="fasilitas_it_tambahan" class="form-control mt-2"
                                        placeholder="Tambahan (opsional), pakai koma (,) untuk pemisah">
                                    <small class="text-muted">Contoh: Smart TV, Tablet, Laptop</small>

                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Akses Internet</label>
                                    <select name="akses_internet" class="form-control" required>
                                        <option value="">-- pilih --</option>
                                        <option value="ada">Ada</option>
                                        <option value="tidak_ada">Tidak ada</option>
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
                                <textarea name="ekstrakurikuler" cols="40" rows="4" class="form-control"
                                    placeholder="Pramuka, Olahraga, Seni, dll" required></textarea>
                            </div>


                            <div class="col-md-4">
                                <label>Program Unggulan Sekolah</label>
                                <textarea name="program_unggulan" cols="40" rows="4" class="form-control"
                                    placeholder="Adiwiyata, Digital School, Pesantren Kilat, dll" required></textarea>
                            </div>


                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Jam Belajar</label>
                                    <select name="jam_belajar" class="form-control" required>
                                        <option value="">-- pilih --</option>
                                        <option value="pagi">Pagi (Pulang siang)</option>
                                        <option value="full_day">Full Day School</option>
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
                                <input type="file" name="foto_depan" class="form-control" accept="image/*">
                            </div>


                            <div class="col-md-4">
                                <label>Logo Sekolah</label>
                                <input type="file" name="logo_sekolah" class="form-control" accept="image/*">
                            </div>
                        </div>


                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label>Denah Lokasi / Titik Koordinat</label>
                                <input type="file" name="denah_lokasi" class="form-control"
                                    accept="image/*,application/pdf">
                                <small class="text-muted">Boleh upload gambar atau PDF</small>
                            </div>


                            <div class="col-md-4">
                                <label>Struktur Organisasi (Opsional)</label>
                                <input type="file" name="struktur_organisasi" class="form-control"
                                    accept="image/*,application/pdf">
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
                $('.select2').select2();

                $('#asn_opsi').on('change', function() {
                    // console.log($(this).val())
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

                    // if (kecamatanId) {
                    //     loadKelurahan(kecamatanId);
                    // }
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

                // Fungsi untuk load Kelurahan
                // function loadKelurahan(kecamatanId) {
                //     $.ajax({
                //         url: `${API_BASE_URL}/villages/${kecamatanId}.json`,
                //         type: 'GET',
                //         dataType: 'json',
                //         success: function(data) {
                //             let options = '<option value="">-- pilih kelurahan --</option>';
                //             data.forEach(function(kelurahan) {
                //                 options +=
                //                     `<option value="${kelurahan.id}">${kelurahan.name}</option>`;
                //             });
                //             $('#kelurahan').html(options);
                //             $('#kelurahan').prop('disabled', false);
                //         },
                //         error: function(xhr, status, error) {
                //             console.error('Error loading kelurahan:', error);
                //             alert('Gagal memuat data kelurahan');
                //         }
                //     });
                // }

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
