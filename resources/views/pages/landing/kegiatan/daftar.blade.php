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
                            <h1 class="banner-title">Daftar Kegiatan {{ $status['kegiatanById']->nama_kegiatan }} </h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb justify-content-center">
                                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                                    <li class="breadcrumb-item" aria-current="page">Kegiatan</li>
                                    <li class="breadcrumb-item active" aria-current="page">Daftar Kegiatan</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-4">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <form action="{{ route('user.kegiatan_store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="kegiatan_id" id="kegiatan_id"
                        value="{{ $_GET['kegiatan_id'] ?? $kegiatan_id }}">

                    <div class="card">
                        <div class="card-header">
                            <h4>Biodata Peserta</h4>
                        </div>
                        <div class="card-body">

                            <!-- SECTION 1: DATA PRIBADI -->
                            <h5 class="mb-3">Data Pribadi</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>1. Nama Lengkap (dengan gelar) <span class="text-danger">*</span></label>
                                        <input name="nama" id="nama" type="text" class="form-control" required>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>2. NIP <span class="text-danger">*</span></label>
                                        <input name="nip" id="nip" type="text" class="form-control" required>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>NIK <span class="text-danger">*</span></label>
                                        <input name="no_ktp" id="no_ktp" type="text" class="form-control" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>3. Pangkat & Golongan</label>
                                        <select name="jenis_gol" id="jenis_gol" class="form-control">
                                            <option value="">-- pilih jenis golongan --</option>
                                            <option value="PNS">PNS</option>
                                            <option value="P3K">PPPK/P3K</option>
                                            <option value="Tidak ada golongan">Tidak Ada Golongan</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3" id="form_golongan_pns" style="display: none;">
                                    <div class="form-group">
                                        <label>Golongan PNS</label>
                                        <select name="golongan_pns" id="golongan_pns" class="form-control select2">
                                            <option value="">-- pilih golongan --</option>
                                            @foreach ($status['golongan'] as $v)
                                                <option value="{{ $v->name }}">{{ $v->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3" id="form_golongan_p3k" style="display: none;">
                                    <div class="form-group">
                                        <label>Golongan PPPK/P3K</label>
                                        <select name="golongan_p3k" id="golongan_p3k" class="form-control select2">
                                            <option value="">-- pilih golongan --</option>
                                            @foreach ($status['golongan_p3k'] as $v)
                                                <option value="{{ $v->name }}">{{ $v->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3" id="form_diluar_gol" style="display: none;">
                                    <div class="form-group">
                                        <label>Isi Golongan</label>
                                        <input name="diluar_gol" id="diluar_gol" placeholder="jika tidak ada ketik tanda -"
                                            type="text" class="form-control">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>4. Jabatan <span class="text-danger">*</span></label>
                                        <input name="jabatan" id="jabatan" type="text" class="form-control" required>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>5. Mata Pelajaran yang diampu (opsional)</label>
                                        <input name="mata_pelajaran" type="text" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>6. Tempat Lahir <span class="text-danger">*</span></label>
                                        <input name="tempat_lahir" id="tempat_lahir" type="text" class="form-control"
                                            required>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Tanggal Lahir <span class="text-danger">*</span></label>
                                        <input name="tgl_lahir" id="tgl_lahir" type="date" class="form-control"
                                            required>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>7. Jenis Kelamin <span class="text-danger">*</span></label>
                                        <select name="jkl" id="gender" class="form-control" required>
                                            <option value="">-- pilih --</option>
                                            <option value="Laki-laki">Laki-laki</option>
                                            <option value="Perempuan">Perempuan</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>8. Status <span class="text-danger">*</span></label>
                                        <select name="status" id="status" class="form-control" required>
                                            <option value="">-- pilih --</option>
                                            <option value="Kawin">Kawin</option>
                                            <option value="Belum Kawin">Belum Kawin</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>9. Agama <span class="text-danger">*</span></label>
                                        <select name="agama" id="agama" class="form-control" required>
                                            <option value="">-- pilih agama --</option>
                                            <option value="Islam">Islam</option>
                                            <option value="Kristen">Kristen</option>
                                            <option value="Katolik">Katolik</option>
                                            <option value="Hindu">Hindu</option>
                                            <option value="Buddha">Buddha</option>
                                            <option value="Konghucu">Konghucu</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>10. Pendidikan Terakhir <span class="text-danger">*</span></label>
                                        <select name="pendidikan" id="pendidikan" class="form-control" required>
                                            <option value="">-- pilih pendidikan --</option>
                                            <option value="S3">S3</option>
                                            <option value="S2">S2</option>
                                            <option value="S1">S1</option>
                                            <option value="D4">D4</option>
                                            <option value="D3">D3</option>
                                            <option value="D2">D2</option>
                                            <option value="D1">D1</option>
                                            <option value="SMA/SMK">SMA/SMK</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- SECTION 2: DATA UNIT KERJA -->
                            <h5 class="mb-3">Data Unit Kerja</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>11. Nama Unit Kerja / Instansi <span class="text-danger">*</span></label>
                                        <input name="instansi" id="instansi" type="text" class="form-control"
                                            required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nama Satuan Pendidikan</label>
                                        <input name="satuan_pendidikan" id="satuan_pendidikan" type="text"
                                            class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>12. Alamat Unit Kerja</label>
                                        <input type="text" name="alamat" id="alamat"
                                            class="form-control" rows="2" />
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Kabupaten / Kota (Unit Kerja) <span class="text-danger">*</span></label>
                                        <select name="kabupaten" id="kabupaten" class="form-control select2" required>
                                            <option value="">-- pilih kabupaten --</option>
                                            @foreach ($status['kabupaten'] as $v)
                                                <option value="{{ $v->name }}">{{ $v->name }}</option>
                                            @endforeach
                                            <option value="lainnya">Lainnya</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4" id="formAsal" style="display: none;">
                                    <div class="form-group">
                                        <label>Asal Kabupaten / Kota</label>
                                        <input name="asal_kabupaten" id="asal_kabupaten" type="text"
                                            class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                {{-- <div class="col-md-3">
                                    <div class="form-group">
                                        <label>NPSN Sekolah</label>
                                        <input name="npsn_sekolah" id="npsn_sekolah" type="text"
                                            class="form-control">
                                    </div>
                                </div> --}}

                                {{-- <div class="col-md-3">
                                    <div class="form-group">
                                        <label>NUPTK</label>
                                        <input name="nuptk" id="nuptk" type="text" class="form-control">
                                    </div>
                                </div> --}}
                            </div>

                            <hr class="my-4">

                            <!-- SECTION 3: DATA ALAMAT & KONTAK -->
                            <h5 class="mb-3">Data Alamat & Kontak</h5>
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>13. Alamat Rumah <span class="text-danger">*</span></label>
                                        <input type="text" name="alamat_rumah" id="alamat_rumah" class="form-control"
                                            rows="2" required />
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Kabupaten / Kota (Alamat Rumah)</label>
                                        <select name="kabupaten_rumah" id="kabupaten_rumah" class="form-control select2" required>
                                            <option value="">-- pilih kabupaten --</option>
                                            @foreach ($status['kabupaten'] as $v)
                                                <option value="{{ $v->name }}">{{ $v->name }}</option>
                                            @endforeach
                                            <option value="lainnya">Lainnya</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>14. Nomor HP / WA <span class="text-danger">*</span></label>
                                        <input name="no_hp" id="no_hp" type="number" class="form-control"
                                            required>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Nomor WhatsApp</label>
                                        <input name="no_wa" id="no_wa" type="number" class="form-control">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>15. Alamat Email / Akun Belajar <span class="text-danger">*</span></label>
                                        <input name="email" id="email" type="email" class="form-control"
                                            required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>16. NPWP</label>
                                        <input name="npwp" id="npwp" type="text" class="form-control"
                                            placeholder="Format: XX.XXX.XXX.X-XXX.XXX">
                                    </div>
                                </div>

                                {{-- <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Jenis Bank</label>
                                        <select name="jenis_bank" id="jenis_bank" class="form-control select2">
                                            <option value="">-- pilih bank --</option>
                                            <option value="BRI">BRI</option>
                                            <option value="BNI">BNI</option>
                                            <option value="Mandiri">Mandiri</option>
                                            <option value="BTN">BTN</option>
                                            <option value="BCA">BCA</option>
                                            <option value="BSI">BSI (Bank Syariah Indonesia)</option>
                                            <option value="Lainnya">Lainnya</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Nomor Rekening</label>
                                        <input name="no_rek" id="no_rek" type="text" class="form-control">
                                    </div>
                                </div> --}}
                            </div>

                            <hr class="my-4">

                            <!-- SECTION 4: DATA SURAT TUGAS -->
                            <h5 class="mb-3">Data Surat Tugas</h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Nomor Surat Tugas <span class="text-danger">*</span></label>
                                        <input name="no_surat_tugas" id="no_surat_tugas" type="text"
                                            class="form-control" placeholder="Contoh: **/**/**/**" required>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Tanggal Surat Tugas <span class="text-danger">*</span></label>
                                        <input name="tgl_surat_tugas" id="tgl_surat_tugas" type="date"
                                            class="form-control" required>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Status Keikutpesertaan <span class="text-danger">*</span></label>
                                        <select name="status_keikutpesertaan" id="status_keikutpesertaan"
                                            class="form-control" required>
                                            <option value="">-- Pilih Status --</option>
                                            <option value="peserta">Peserta</option>
                                            <option value="panitia">Panitia</option>
                                            <option value="narasumber">Narasumber</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Upload Pas Foto (Optional)</label>
                                        <input name="pas_foto" id="pas_foto" type="file" class="form-control"
                                            accept="image/*">
                                        <small class="form-text text-muted">Format: JPG, PNG, JPEG. Max: 2MB</small>
                                    </div>
                                </div>
                            </div> --}}

                        </div>

                        <div class="card-footer text-right">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-save"></i> Submit
                            </button>
                            <a href="{{ route('user.kegiatan') }}" class="btn btn-warning">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                // Initialize Select2
                $('.select2').select2();

                // Hide form sections initially
                $('#formAsal').hide();
                $('#form_golongan_pns').hide();
                $('#form_golongan_p3k').hide();
                $('#form_diluar_gol').hide();

                // Load existing data if available
                $.ajax({
                    url: '{{ route('user.peserta.cekData') }}',
                    type: 'GET',
                    data: {
                        nik: '{{ session('nik') }}'
                    },
                    success: function(response) {
                        console.log('Data loaded:', response.data);

                        if (response.data) {
                            let data = response.data;

                            // Fill form with existing data
                            $('#no_ktp').val(data.no_ktp);
                            $('#nip').val(data.nip);
                            $('#nama').val(data.nama ?? data.nama_lengkap);
                            $('#email').val(data.email);
                            $('#tempat_lahir').val(data.tempat_lahir);
                            $('#tgl_lahir').val(data.tgl_lahir);
                            $('#agama').val(data.agama);
                            $('#pendidikan').val(data.pendidikan);
                            $('#jabatan').val(data.jabatan);
                            $('#tugas_jabatan').val(data.tugas_jabatan);
                            $('#status').val(data.status);
                            $('#instansi').val(data.instansi ?? data.satuan_pendidikan);
                            $('#satuan_pendidikan').val(data.satuan_pendidikan);
                            $('#alamat_satuan').val(data.alamat_satuan);
                            $('#alamat_rumah').val(data.alamat_rumah);
                            $('#no_hp').val(data.no_hp);
                            $('#no_wa').val(data.no_wa);
                            $('#npwp').val(data.npwp);
                            $('#npsn_sekolah').val(data.npsn_sekolah);
                            $('#nuptk').val(data.nuptk);
                            $('#jenis_bank').val(data.jenis_bank);
                            $('#no_rek').val(data.no_rek);

                            // Set gender
                            $(`#gender option[value="${data.jkl ?? data.gender}"]`).prop('selected', true);

                            // Set kabupaten
                            if (data.kabupaten) {
                                $('#kabupaten').append($("<option>")
                                    .text(data.kabupaten)
                                    .attr('value', data.kabupaten)
                                    .prop('selected', true)
                                );
                            }

                            // Set kabupaten
                            if (data.pendidikan) {
                                $('#pendidikan').append($("<option>")
                                    .text(data.pendidikan)
                                    .attr('value', data.pendidikan)
                                    .prop('selected', true)
                                );
                            }

                            // Handle golongan
                            let jenis_gol = $.trim(data.jenis_gol);
                            $(`#jenis_gol option[value="${jenis_gol}"]`).prop('selected', true);

                            if (jenis_gol == 'PNS') {
                                $('#form_golongan_pns').show();
                                $('#golongan_pns').append($("<option>")
                                    .text(data.golongan)
                                    .attr('value', data.golongan)
                                    .prop('selected', true)
                                );
                            } else if (jenis_gol == 'P3K') {
                                $('#form_golongan_p3k').show();
                                $('#golongan_p3k').append($("<option>")
                                    .text(data.golongan)
                                    .attr('value', data.golongan)
                                    .prop('selected', true)
                                );
                            } else if (jenis_gol == 'Tidak ada golongan') {
                                $('#form_diluar_gol').show();
                                $('#diluar_gol').val(data.golongan);
                            }
                        }
                    },
                    error: function(error) {
                        console.error('Error fetching data:', error);
                    }
                });

                // Handle Kabupaten selection
                $('#kabupaten').change(function() {
                    let selectedValue = $(this).val();
                    if (selectedValue === 'lainnya') {
                        $('#formAsal').show();
                    } else {
                        $('#formAsal').hide();
                        $('#asal_kabupaten').val('');
                    }
                });

                // Handle Jenis Golongan change
                $('#jenis_gol').change(function() {
                    let status = $(this).val();

                    $('#form_golongan_pns').hide();
                    $('#form_golongan_p3k').hide();
                    $('#form_diluar_gol').hide();

                    $('#golongan_pns').val('');
                    $('#golongan_p3k').val('');
                    $('#diluar_gol').val('');

                    if (status == 'PNS') {
                        $('#form_golongan_pns').show();
                    } else if (status == 'P3K') {
                        $('#form_golongan_p3k').show();
                    } else if (status == 'Tidak ada golongan') {
                        $('#form_diluar_gol').show();
                    }
                });

                // Auto-fill WA from HP if empty
                $('#no_hp').blur(function() {
                    if ($('#no_wa').val() == '') {
                        $('#no_wa').val($(this).val());
                    }
                });
            });
        </script>
    @endpush
@endsection
