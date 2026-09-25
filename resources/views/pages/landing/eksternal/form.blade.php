@extends('layouts.landing.app')
@section('content')
    <div id="banner-area" class="banner-area"
        style="background-image:url({{ asset('landing/images/banner/bannerEksternal.png') }})">
        <div class="banner-text">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="banner-heading">
                            <h1 class="banner-title">Eksternal BBGTK</h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb justify-content-center">
                                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                                    <li class="breadcrumb-item " aria-current="page">Eksternal</li>
                                    <li class="breadcrumb-item active" aria-current="page">Form Eksternal
                                        {{ $jenis }}</li>
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
                <form action="{{ route('user.daftar_guru') }}" method="POST" enctype="multipart/form-data" novalidate>
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Lengkap</label>
                                <input required name="nama_lengkap" type="text" class="form-control"
                                    value="{{ old('nama_lengkap') }}">
                                @error('nama_lengkap')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nomor KTP</label>
                                <input required name="no_ktp" type="text" class="form-control"
                                    value="{{ old('no_ktp') }}" minlength="16" maxlength="16"
                                    inputmode="numeric">
                                @error('no_ktp')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email</label>
                                <input required name="email" type="email" class="form-control"
                                    value="{{ old('email') }}">
                                @error('email')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>NIP</label>
                                <input required name="nip" type="text" class="form-control"
                                    value="{{ old('nip') }}" minlength="18" maxlength="18"
                                    inputmode="numeric">
                                @error('nip')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>NPWP</label>
                                <input required name="npwp" type="text" class="form-control"
                                    value="{{ old('npwp') }}" minlength="15" maxlength="16"
                                    inputmode="numeric">
                                @error('npwp')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>NUPTK</label>
                                <input {{ $jenis == 'Stakeholder' ? '' : 'required' }} name="nuptk" type="text"
                                    class="form-control" value="{{ old('nuptk') }}" minlength="16" maxlength="16"
                                    inputmode="numeric">
                                @error('nuptk')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">

                        <div class="col-md">
                            <div class="form-group">
                                <label>Status Kepegawaian</label>
                                <select required name="status_kepegawaian" class="form-control select2">
                                    <option value="">-- Pilih status kepegawaian --</option>
                                    @foreach ($status['s_kepegawaian'] as $v)
                                        <option value="{{ $v->name }}"
                                            {{ old('status_kepegawaian') === $v->name ? 'selected' : '' }}>
                                            {{ $v->name }}</option>
                                    @endforeach

                                </select>
                                @error('status_kepegawaian')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md">
                            <div class="form-group">
                                <label>Tempat Lahir</label>
                                <input required name="tempat_lahir" type="text" class="form-control"
                                    value="{{ old('tempat_lahir') }}">
                                @error('tempat_lahir')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md">
                            <div class="form-group">
                                <label>Tanggal Lahir</label>
                                <input required name="tgl_lahir" type="date" class="form-control"
                                    value="{{ old('tgl_lahir') }}">
                                @error('tgl_lahir')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Jenis Kelamin</label>
                                <select required name="gender" class="form-control ">
                                    <option value="">-- Pilih Jenis Kelamin --</option>
                                    <option value="Laki-laki" {{ old('gender') === 'Laki-laki' ? 'selected' : '' }}>
                                        Laki-laki</option>
                                    <option value="Perempuan" {{ old('gender') === 'Perempuan' ? 'selected' : '' }}>
                                        Perempuan</option>
                                </select>
                                @error('gender')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md">
                            <div class="form-group">
                                <label>Alamat Rumah</label>
                                <input required type="text" name="alamat_rumah" class="form-control"
                                    placeholder="isi dengan lengkap " value="{{ old('alamat_rumah') }}">
                                @error('alamat_rumah')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Agama</label>
                                <select required name="agama" class="form-control ">
                                    <option value="">-- Pilih Agama --</option>
                                    <option value="Islam" {{ old('agama') === 'Islam' ? 'selected' : '' }}>Islam
                                    </option>
                                    <option value="Kristen" {{ old('agama') === 'Kristen' ? 'selected' : '' }}>Kristen
                                    </option>
                                    <option value="Katolik" {{ old('agama') === 'Katolik' ? 'selected' : '' }}>Katolik
                                    </option>
                                    <option value="Hindu" {{ old('agama') === 'Hindu' ? 'selected' : '' }}>Hindu
                                    </option>
                                    <option value="Buddha" {{ old('agama') === 'Buddha' ? 'selected' : '' }}>Buddha
                                    </option>
                                </select>
                                @error('agama')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Pendidikan Terakhir</label>
                                <select required name="pendidikan" class="form-control ">
                                    <option value="">-- Pilih pendidikan terakhir --</option>
                                    @foreach ($status['s_gelar'] as $v)
                                        <option value="{{ $v->name }}"
                                            {{ old('pendidikan') === $v->name ? 'selected' : '' }}>
                                            {{ $v->name }}</option>
                                    @endforeach

                                </select>
                                @error('pendidikan')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Satuan Pendidikan</label>
                                <select required name="satuan_pendidikan" class="form-control select2">
                                    <option value="">-- Pilih status Satuan Pendidikan --</option>
                                    @foreach ($status['s_kependidikan'] as $v)
                                        <option value="{{ $v->name }}"
                                            {{ old('satuan_pendidikan') === $v->name ? 'selected' : '' }}>
                                            {{ $v->name }}</option>
                                    @endforeach

                                </select>
                                @error('satuan_pendidikan')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>


                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Kabupaten / Kota</label>
                                <select required name="kabupaten" id="kabupaten" class="form-control select2">
                                    <option value="">-- Pilih Kabupaten / Kota --</option>
                                    <option value="Tidak ada" {{ old('kabupaten') === 'Tidak ada' ? 'selected' : '' }}>
                                        Diluar SulSel</option>
                                    @foreach ($status['s_kabupaten'] as $v)
                                        <option value="{{ $v->name }}"
                                            {{ old('kabupaten') === $v->name ? 'selected' : '' }}>
                                            {{ $v->name }}</option>
                                    @endforeach

                                </select>
                                @error('kabupaten')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4" id="diluarKab">
                            <div class="form-group">
                                <label>Asal Kabupaten/Kota</label>
                                <input name="diluarKab" placeholder="jika diluar SulSel" type="text"
                                    class="form-control" value="{{ old('diluarKab') }}">
                                @error('diluarKab')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>





                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Nomor Handphone</label>
                                <input required name="no_hp" type="number" class="form-control"
                                    value="{{ old('no_hp') }}" min="1000000000" max="999999999999999"
                                    inputmode="numeric">
                                @error('no_hp')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Nomor Whatsapp</label>
                                <input required name="no_wa" type="number" class="form-control"
                                    value="{{ old('no_wa') }}" min="1000000000" max="999999999999999"
                                    inputmode="numeric">
                                @error('no_wa')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Bank</label>
                                <select required name="jenis_bank" class="form-control" id="jenisBank">
                                    <option value="">-- Pilih Bank --</option>
                                    <option value="Bank BCA" {{ old('jenis_bank') === 'Bank BCA' ? 'selected' : '' }}>
                                        Bank BCA</option>
                                    <option value="Bank BRI" {{ old('jenis_bank') === 'Bank BRI' ? 'selected' : '' }}>
                                        Bank BRI</option>
                                    <option value="Bank BNI" {{ old('jenis_bank') === 'Bank BNI' ? 'selected' : '' }}>
                                        Bank BNI</option>
                                    <option value="Bank BTN" {{ old('jenis_bank') === 'Bank BTN' ? 'selected' : '' }}>
                                        Bank BTN</option>
                                    <option value="Bank Mandiri"
                                        {{ old('jenis_bank') === 'Bank Mandiri' ? 'selected' : '' }}>Bank Mandiri</option>
                                    <option value="Bank Syariah Indonesia"
                                        {{ old('jenis_bank') === 'Bank Syariah Indonesia' ? 'selected' : '' }}>Bank Syariah
                                        Indonesia</option>
                                    <option value="Bank SulSelBar"
                                        {{ old('jenis_bank') === 'Bank SulSelBar' ? 'selected' : '' }}>Bank SulSelBar</option>
                                    <option value="Tidak ada" {{ old('jenis_bank') === 'Tidak ada' ? 'selected' : '' }}>
                                        Tidak ada</option>
                                </select>
                                @error('jenis_bank')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Nomor Rekening</label>
                                <input required type="number" name="no_rek" id="no_rek" class="form-control"
                                    value="{{ old('no_rek') }}" min="0" max="999999999999999999999999999999"
                                    inputmode="numeric">
                                @error('no_rek')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror

                            </div>
                        </div>


                    </div>


                    <div class="row">

                        <div class="col-md-4 mb-4">
                            <label>Jenis Jabatan Eksternal</label>
                            <select required name="jenisJabatan" class="form-control " readonly id="jabEksternal">
                                <option value="">-- Pilih Jabatan Eksternal --</option>
                                <option {{ $jenis == 'Tenaga Pendidik' ? 'selected' : 'disabled' }}
                                    value="Tenaga Pendidik">Tenaga Pendidik</option>
                                <option {{ $jenis == 'Tenaga Kependidikan' ? 'selected' : 'disabled' }}
                                    value="Tenaga Kependidikan">Tenaga Kependidikan</option>
                                <option {{ $jenis == 'Stakeholder' ? 'selected' : 'disabled' }} value="Stakeholder">
                                    Stakeholder</option>
                            </select>
                            @error('jenisJabatan')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Jabatan (Pilih Eksternal dulu)</label>
                                <select required name="jabJenis" class="form-control " id="jabJenis">
                                    <option value="">-- Pilih Jenis Jabatan --</option>
                                </select>
                                @error('jabJenis')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4" id="jabLainnya">
                            <div class="form-group">
                                <label>Jabatan </label>
                                <input type="text" name="jabLainnya" id=""
                                    placeholder="ketikkan jabatan anda" class="form-control"
                                    value="{{ old('jabLainnya') }}">
                                @error('jabLainnya')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>



                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <label>NPSN Sekolah dan Nama Sekolah</label>
                            <select {{ $jenis == 'Stakeholder' ? '' : '' }} name="npsn_sekolah" class="form-control"
                                id="data_sekolah" onchange="updateLocation()">
                                <option value="">-- Pilih Data Sekolah --</option>

                            </select>
                            @error('npsn_sekolah')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Kabupaten Sekolah</label>
                                <input id="kabupaten_sekolah" type="text" name="kabupaten_sekolah"
                                    class="form-control" placeholder="Otomatis terisi berdasarkan NPSN Sekolah" readonly
                                    value="{{ old('kabupaten_sekolah') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Kecamatan Sekolah</label>
                                <input id="kecamatan_sekolah" type="text" name="alamat_satuan" class="form-control"
                                    placeholder="Otomatis terisi berdasarkan NPSN Sekolah" readonly
                                    value="{{ old('alamat_satuan') }}">
                                @error('alamat_satuan')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>


                    </div>




                    <div class="card-footer text-right">
                        <button class="btn btn-primary " type="submit">Submit</button>
                        <a href="{{ route('user.guru') }}" class="btn btn-warning">Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>


    @push('scripts')
        <script>
            function updateLocation() {
                var selectedOption = $('#data_sekolah').select2('data')[0];
                const kecamatanInput = document.getElementById('kecamatan_sekolah');
                const kabupatenInput = document.getElementById('kabupaten_sekolah');

                if (selectedOption) {
                    var kecamatan = selectedOption.kecamatan;
                    var kabupaten = selectedOption.kabupaten;

                    kecamatanInput.value = kecamatan
                    kabupatenInput.value = kabupaten

                    // Update the location based on selected kecamatan and kabupaten
                }
            }
        </script>

        <script>
            $(window).on("load", function() {});
            $(document).ready(function() {

                $('.select2').select2({
                    width: 'resolve' // need to override the changed default
                });
                const fieldLainnya = $('#jabLainnya');
                fieldLainnya.hide()

                const getKabupaten = $('#kabupaten');
                const fieldKab = $('#diluarKab');
                fieldKab.hide()

                if (getKabupaten.val() === 'Tidak ada' || @json($errors->has('diluarKab'))) {
                    fieldKab.show()
                }

                getKabupaten.on('change', function() {
                    const val = $(this).find(':selected').val()
                    if (val === 'Tidak ada') {
                        fieldKab.show()
                    } else {
                        fieldKab.hide()
                    }
                })



                $('#data_sekolah').select2({
                    ajax: {
                        url: '/get-sekolahs', // Your route to fetch data
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                q: params.term || '', // Search term (if any)
                                page: params.page || 1, // Pagination
                                per_page: 500 // Items per page
                            };
                        },
                        processResults: function(data, params) {
                            params.page = params.page || 1;

                            return {
                                results: $.map(data.data, function(item) {
                                    return {
                                        id: item.npsn_sekolah,
                                        text: item.npsn_sekolah + ' - ' + item.nama_sekolah,
                                        kecamatan: item.kecamatan,
                                        kabupaten: item.kabupaten
                                    }
                                }),
                                pagination: {
                                    more: (params.page * data.per_page) < data.total
                                }
                            };
                        },
                        cache: true
                    },
                    placeholder: '-- Pilih Data Sekolah --',
                    minimumInputLength: 0, // Show results without requiring input
                    templateResult: formatSekolah,
                    templateSelection: formatSekolahSelection
                });

                function formatSekolah(sekolah) {
                    if (sekolah.loading) {
                        return sekolah.text;
                    }
                    return $('<span>' + sekolah.text + '</span>');
                }

                function formatSekolahSelection(sekolah) {
                    return sekolah.text;
                }

                $('#jenisBank').on('change', function() {
                    const value = $(this).find(':selected').val()
                    const noRekField = value == 'Tidak ada' ? $('#no_rek').val('0') : '';
                })


                const jenisEksternal = {!! json_encode($jenis, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};

                $('#colJabatan').hide();

                function fillterJabatan() {
                    var jabEksternal = $('#jabEksternal').val();
                    var jabJenis = $('#jabJenis');
                    var jabTugas = $('#jabTugas');
                    var colJabatan = $('#colJabatan');
                    var jabKategori = $('#jabKategori');
                    var option = '';
                    const dataJab = {!! json_encode($status, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};

                    jabJenis.empty();

                    jabJenis.append($('<option>', {
                        value: '',
                        text: '-- Pilih Jabatan --',
                        disabled: true,
                        selected: true
                    }));

                    jabTugas.empty();
                    jabTugas.append($('<option>', {
                        value: '',
                        text: '-- Pilih Tugas --',
                        disabled: true,
                        selected: true
                    }));

                    jabKategori.empty();
                    jabKategori.append($('<option>', {
                        value: '',
                        text: '-- Pilih Kategori --',
                        disabled: true,
                        selected: true
                    }));
                    colJabatan.hide();


                    if (jabEksternal == 'Tenaga Pendidik') {

                        colJabatan.hide();

                        let dataJabValue = dataJab['s_jabPendidik'].map(item => {
                            option = $("<option>")
                                .text(item.name)
                                .attr('value', item.name)
                                .removeAttr('disabled');
                            jabJenis.append(option);
                        });
                    }
                    if (jabEksternal == 'Tenaga Kependidikan') {
                        colJabatan.show();

                        let dataJabValue = dataJab['s_jabKependidikan'].map(item => {
                            option = $("<option>")
                                .text(item.name)
                                .attr('value', item.name)
                                .removeAttr('disabled');
                            jabJenis.append(option);
                        });
                    }
                    if (jabEksternal == 'Stakeholder') {
                        colJabatan.hide();

                        let dataJabValue = dataJab['s_jabStakeholder'].map(item => {
                            option = $("<option>")
                                .text(item.name)
                                .attr('value', item.name)
                                .removeAttr('disabled');
                            jabJenis.append(option);
                        });
                    }
                }

                function fillterKategori() {
                    var jabKategori = $('#jabKategori').val();
                    var jabTugas = $('#jabTugas');
                    var option = '';
                    const dataJab = {!! json_encode($status, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};

                    jabTugas.empty();

                    jabTugas.append($('<option>', {
                        value: '',
                        text: '-- Pilih Tugas --',
                        disabled: true,
                        selected: true
                    }));

                    if (jabKategori == 'GP (Guru Penggerak)') {
                        let dataJabValue = dataJab['s_jabTugas'].map(item => {
                            option = $("<option>")
                                .text(item)
                                .attr('value', item)
                                .removeAttr('disabled');
                            jabTugas.append(option);
                        });
                    }
                    if (jabKategori == 'NoN GP (Guru Penggerak)') {
                        let dataJabValue = dataJab['s_jabTugas'].map((item, i) => {
                            option = $("<option>")
                                .text(item)
                                .attr('value', item)
                                .removeAttr('disabled');
                            jabTugas.append(option);
                        });
                    }
                }

                $('#jabEksternal').on('change', function() {
                    fillterJabatan();
                    fillterKategori();
                });

                $('#jabKategori').on('change', function() {
                    var jabLatar = $('#jabLatar');
                    var jabJenis = $('#jabJenis');
                    var jabTugas = $('#jabTugas');

                    var option = '';
                    const dataJab = {!! json_encode($status, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};

                    var selectedOption = $(this).find('option:selected');
                    var seletJenis = jabJenis.find('option:selected');
                    if (selectedOption.text() == 'GP (Guru Penggerak)' && seletJenis.text() ==
                        'Kepala Sekolah') {
                        let dataJabValue = dataJab['s_jabKategoriKepsek'].map((item, i) => {
                            option = $("<option>")
                                .text(item)
                                .attr('value', item)
                                .removeAttr('disabled');
                            jabLatar.append(option);
                        });

                    } else if (selectedOption.text() == 'GP (Guru Penggerak)' && seletJenis.text() ==
                        'Pengawas') {
                        let dataJabValue = dataJab['s_jabKategoriPengawas'].map((item, i) => {
                            option = $("<option>")
                                .text(item)
                                .attr('value', item)
                                .removeAttr('disabled');
                            jabLatar.append(option);
                        });

                    } else if (selectedOption.text() == 'GP (Guru Penggerak)' && (seletJenis.text() ==
                            'Guru' || seletJenis.text() ==
                            'Konselor')) {
                        let dataJabValue = dataJab['s_jabTugas'].map((item, i) => {
                            option = $("<option>")
                                .text(item)
                                .attr('value', item)
                                .removeAttr('disabled');
                            jabTugas.append(option);
                        });

                    } else {
                        jabLatar.empty();
                        jabLatar.append($('<option>', {
                            value: '',
                            text: '-- Pilih Latar Jabatan --',
                            disabled: true,
                            selected: true
                        }));

                        jabTugas.empty();
                        jabTugas.append($('<option>', {
                            value: '',
                            text: '-- Pilih Tugas Jabatan --',
                            disabled: true,
                            selected: true
                        }));
                    }

                });

                $('#jabJenis').on('change', function() {
                    var jabKategori = $('#jabKategori');
                    var jabTugas = $('#jabTugas');
                    var jabLatar = $('#jabLatar');
                    var jabJenis = $(this);
                    var option = '';
                    const dataJab = {!! json_encode($status, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};

                    jabKategori.empty();
                    jabKategori.append($('<option>', {
                        value: '',
                        text: '-- Pilih Kategori --',
                        disabled: true,
                        selected: true
                    }));

                    jabTugas.empty();
                    jabTugas.append($('<option>', {
                        value: '',
                        text: '-- Pilih Tugas --',
                        disabled: true,
                        selected: true
                    }));

                    jabLatar.empty();
                    jabLatar.append($('<option>', {
                        value: '',
                        text: '-- Pilih Latar Jabatan --',
                        disabled: true,
                        selected: true
                    }));

                    var selectedOption = $(this).find('option:selected');

                    if (selectedOption.text() == 'Guru' || selectedOption.text() == 'Konselor') {
                        let dataJabValue = dataJab['s_jabKategori'].map((item, i) => {
                            option = $("<option>")
                                .text(item)
                                .attr('value', item)
                                .removeAttr('disabled');
                            jabKategori.append(option);
                        });
                    } else if (selectedOption.text() == 'Pengawas') {
                        let dataJabValue = dataJab['s_jabKategori'].map((item, i) => {
                            option = $("<option>")
                                .text(item)
                                .attr('value', item)
                                .removeAttr('disabled');
                            jabKategori.append(option);
                        });


                    } else if (selectedOption.text() == 'Kepala Sekolah') {
                        let dataJabValue = dataJab['s_jabKategori'].map((item, i) => {
                            option = $("<option>")
                                .text(item)
                                .attr('value', item)
                                .removeAttr('disabled');
                            jabKategori.append(option);
                        });




                    } else if (selectedOption.text() == 'Lainnya') {
                        // show some field
                        fieldLainnya.show()

                    } else {
                        fieldLainnya.hide()
                        jabKategori.empty();
                        jabKategori.append($('<option>', {
                            value: '',
                            text: '-- Pilih Kategori --',
                            disabled: true,
                            selected: true
                        }));

                        jabTugas.empty();
                        jabTugas.append($('<option>', {
                            value: '',
                            text: '-- Pilih Tugas --',
                            disabled: true,
                            selected: true
                        }));

                        jabLatar.empty();
                        jabLatar.append($('<option>', {
                            value: '',
                            text: '-- Pilih Latar Jabatan --',
                            disabled: true,
                            selected: true
                        }));
                    }

                });

                $('#jabLatar').on('change', function() {
                    var jabLatar = $(this);
                    var jabTugas = $('#jabTugas');

                    var option = '';
                    const dataJab = {!! json_encode($status, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};

                    var selectedOption = $(this).find('option:selected');
                    var seletTugas = jabTugas.find('option:selected');

                    jabTugas.empty();
                    jabTugas.append($('<option>', {
                        value: '',
                        text: '-- Pilih Tugas Jabatan --',
                        disabled: true,
                        selected: true
                    }));

                    if (selectedOption.text() != '') {
                        let dataJabValue = dataJab['s_jabTugas'].map((item, i) => {
                            option = $("<option>")
                                .text(item)
                                .attr('value', item)
                                .removeAttr('disabled');
                            jabTugas.append(option);
                        });

                    } else {
                        jabTugas.empty();
                        jabTugas.append($('<option>', {
                            value: '',
                            text: '-- Pilih Tugas Jabatan --',
                            disabled: true,
                            selected: true
                        }));
                    }
                });
                fillterJabatan();

                const oldJabJenis = @json(old('jabJenis'));
                if (oldJabJenis) {
                    $('#jabJenis').val(oldJabJenis);
                }

                if ($('#jabJenis').val() === 'Lainnya' || @json($errors->has('jabLainnya'))) {
                    fieldLainnya.show()
                }

            });
        </script>
    @endpush
@endsection
