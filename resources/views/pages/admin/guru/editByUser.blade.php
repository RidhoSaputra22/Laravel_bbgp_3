@extends('layouts.app', ['title' => 'Eksternal'])
@section('content')
    @push('styles')
        <link rel="stylesheet" href="{{ asset('library/select2/dist/css/select2.min.css') }}">
        <link rel="stylesheet" href="{{ asset('library/selectric/public/selectric.css') }}">
    @endpush

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Edit Data Eksternal BBGTK</h1>
                {{-- <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="#">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="#">Bootstrap Components</a></div>
                    <div class="breadcrumb-item">Form</div>
                </div> --}}
            </div>

            <div class="section-body">

                <div class="row">

                    <div class="col-md-12 col-lg-12">
                        <form action="{{ route('guru.update.user') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <input type="hidden" value="{{ $datas->id }}" name="id">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Nama Lengkap</label>
                                                <input required name="nama_lengkap" type="text" class="form-control"
                                                    value="{{ $datas->nama_lengkap }}">
                                            </div>
                                            <div class="form-group">
                                                <label>Email</label>
                                                <input required name="email" type="text" class="form-control"
                                                    value="{{ $datas->email }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Nomor KTP</label>
                                                <input readonly required name="no_ktp" type="number" class="form-control"
                                                    value="{{ $datas->no_ktp }}">
                                            </div>
                                            <div class="form-group">
                                                <label>NIP</label>
                                                <input required name="nip" type="number" class="form-control"
                                                    value="{{ $datas->nip }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>NPWP</label>
                                                <input required name="npwp" type="number" class="form-control"
                                                    value="{{ $datas->npwp }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>NUPTK</label>
                                                <input required name="nuptk" type="number" class="form-control"
                                                    value="{{ $datas->nuptk }}">
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
                                                        <option
                                                            {{ $datas->status_kepegawaian == $v->name ? 'selected' : '' }}
                                                            value="{{ $v->name }}">{{ $v->name }}</option>
                                                    @endforeach

                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md">
                                            <div class="form-group">
                                                <label>Tempat Lahir</label>
                                                <input required name="tempat_lahir" type="text" class="form-control"
                                                    value="{{ $datas->tempat_lahir }}">
                                            </div>
                                        </div>
                                        <div class="col-md">
                                            <div class="form-group">
                                                <label>Tanggal Lahir</label>
                                                <input required name="tgl_lahir" type="date" class="form-control"
                                                    value="{{ $datas->tgl_lahir }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Jenis Kelamin</label>
                                                <select required name="gender" class="form-control ">
                                                    <option value="">-- Pilih Jenis Kelamin --</option>
                                                    <option {{ $datas->gender == 'Laki-laki' ? 'selected' : '' }}
                                                        value="Laki-laki">Laki-laki</option>
                                                    <option {{ $datas->gender == 'Perempuan' ? 'selected' : '' }}
                                                        value="Perempuan">Perempuan</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md">
                                            <div class="form-group">
                                                <label>Alamat Rumah</label>
                                                <input required type="text" name="alamat_rumah" class="form-control"
                                                    placeholder="isi dengan lengkap " value="{{ $datas->alamat_rumah }}">
                                            </div>
                                        </div>

                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Agama</label>
                                                <select required name="agama" class="form-control ">
                                                    <option value="">-- Pilih Agama --</option>
                                                    <option {{ $datas->agama == 'Islam' ? 'selected' : '' }}
                                                        value="Islam">Islam</option>
                                                    <option {{ $datas->agama == 'Kristen' ? 'selected' : '' }}
                                                        value="Kristen">Kristen</option>
                                                    <option {{ $datas->agama == 'Katolik' ? 'selected' : '' }}
                                                        value="Katolik">Katolik</option>
                                                    <option {{ $datas->agama == 'Hindu' ? 'selected' : '' }}
                                                        value="Hindu">Hindu</option>
                                                    <option {{ $datas->agama == 'Buddha' ? 'selected' : '' }}
                                                        value="Buddha">Buddha</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Pendidikan Terakhir</label>
                                                <select required name="pendidikan" class="form-control ">
                                                    <option value="">-- Pilih pendidikan terakhir --</option>
                                                    @foreach ($status['s_gelar'] as $v)
                                                        <option {{ $datas->pendidikan == $v->name ? 'selected' : '' }}
                                                            value="{{ $v->name }}">{{ $v->name }}</option>
                                                    @endforeach

                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Satuan Pendidikan</label>
                                                <select required name="satuan_pendidikan" class="form-control select2">
                                                    <option value="">-- Pilih status Satuan Pendidikan --</option>
                                                    @foreach ($status['s_kependidikan'] as $v)
                                                        <option
                                                            {{ $datas->satuan_pendidikan == $v->name ? 'selected' : '' }}
                                                            value="{{ $v->name }}">{{ $v->name }}</option>
                                                    @endforeach

                                                </select>
                                            </div>
                                        </div>


                                    </div>


                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Kabupaten / Kota</label>
                                                <select required name="kabupaten" class="form-control select2">
                                                    <option value="">-- Pilih Kabupaten / Kota --</option>
                                                    @foreach ($status['s_kabupaten'] as $v)
                                                        <option {{ $datas->kabupaten == $v->name ? 'selected' : '' }}
                                                            value="{{ $v->name }}">{{ $v->name }}</option>
                                                    @endforeach

                                                </select>
                                            </div>
                                        </div>

                                        {{-- <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Status</label>
                                                <select required name="status" class="form-control ">
                                                    <option value="">-- Kawin/Belum Kawin --</option>
                                                    <option value="Kawin">Kawin</option>
                                                    <option value="Belum Kawin">Belum Kawin</option>
                                                </select>
                                            </div>
                                        </div> --}}



                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Nomor Handphone</label>
                                                <input required name="no_hp" type="number" class="form-control"
                                                    value="{{ $datas->no_hp }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Nomor Whatsapp</label>
                                                <input required name="no_wa" type="number" class="form-control"
                                                    value="{{ $datas->no_wa }}">
                                            </div>
                                        </div>

                                    </div>
                                    <div class="row">
                                        {{-- <div class="col-md-6 mb-4">
                                            <label>Jabatan Sekolah</label>
                                            <select required name="jabatan" class="form-control select2">
                                                <option value="">-- Pilih Jabatan Sekolah --</option>
                                                @foreach ($status['s_jabatan'] as $v)
                                                    <option {{ $datas->jabatan == $v->name ? 'selected' : '' }}
                                                        value="{{ $v->name }}">{{ $v->name }}</option>
                                                @endforeach

                                            </select>
                                        </div> --}}
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Bank</label>
                                                <select name="jenis_bank" class="form-control" id="">
                                                    <option value="Bank BCA">-- Pilih Bank --</option>
                                                    <option {{ $datas->jenis_bank == 'Bank BCA' ? 'selected' : '' }}
                                                        value="Bank BCA">Bank BCA</option>
                                                    <option {{ $datas->jenis_bank == 'Bank BRI' ? 'selected' : '' }}
                                                        value="Bank BRI">Bank BRI</option>
                                                    <option {{ $datas->jenis_bank == 'Bank BNI' ? 'selected' : '' }}
                                                        value="Bank BNI">Bank BNI</option>
                                                    <option {{ $datas->jenis_bank == 'Bank BTN' ? 'selected' : '' }}
                                                        value="Bank BTN">Bank BTN</option>
                                                    <option {{ $datas->jenis_bank == 'Bank Mandiri' ? 'selected' : '' }}
                                                        value="Bank Mandiri">Bank Mandiri</option>
                                                    <option
                                                        {{ $datas->jenis_bank == 'Bank Syariah Indonesia' ? 'selected' : '' }}
                                                        value="Bank Syariah Indonesia">Bank Syariah Indonesia</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label>Nomor Rekening</label>
                                                <input required type="number" name="no_rek" class="form-control"
                                                    value="{{ $datas->no_rek }}">
                                            </div>
                                        </div>
                                        {{-- <div class="col-md-5">
                                            <div class="form-group">
                                                <label>Pas Foto</label>
                                                <input required type="file" name="pas_foto" class="form-control">
                                            </div>
                                        </div> --}}


                                        <div class="col-md-4 mb-4">
                                            <label>Jenis Jabatan Eksternal</label>
                                            <select required name="jenisJabatan" class="form-control select2"
                                                id="jabEksternal"
                                                data-selected-value="{{ old('jabEksternal', $datas->eksternal_jabatan) }}">
                                                <option disabled value="">-- Pilih Jabatan Eksternal --</option>
                                                <option
                                                    {{ $datas->eksternal_jabatan == 'Tenaga Pendidik' ? 'selected' : '' }}
                                                    value="Tenaga Pendidik">Tenaga Pendidik</option>
                                                <option
                                                    {{ $datas->eksternal_jabatan == 'Tenaga Kependidikan' ? 'selected' : '' }}
                                                    value="Tenaga Kependidikan">Tenaga Kependidikan</option>
                                                <option {{ $datas->eksternal_jabatan == 'Stakeholder' ? 'selected' : '' }}
                                                    value="Stakeholder">Stakeholder</option>
                                            </select>
                                        </div>
                                    </div>




                                    <div class="row">

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Jabatan (Pilih Eksternal dulu)</label>
                                                <select required name="jabJenis" class="form-control select2"
                                                    id="jabJenis"
                                                    data-selected-value="{{ old('jabJenis', $datas->jenis_jabatan) }}">
                                                    <option disabled value="">-- Pilih Jenis Jabatan --</option>
                                                    {{-- <option id="valJabJenis" value="">-- Pilih Jabatan</option> --}}

                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-4 mb-4">
                                            <label>Kategori Jabatan (Pilih Eksternal dulu) </label>
                                            <select name="jabKategori" class="form-control"
                                                data-selected-value="{{ old('jabKategori', $datas->kategori_jabatan) }}"
                                                id="jabKategori">
                                                <option value="">-- Pilih Jenis Jabatan --</option>
                                                {{-- <option id="valJabJenis" value="">-- Pilih Jabatan</option> --}}
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Jenis Tugas</label>
                                                <select name="jabTugas" class="form-control select2" id="jabTugas"
                                                    data-selected-value="{{ old('jabTugas', $datas->tugas_jabatan) }}">
                                                    <option value="">-- Pilih Tugas Jabatan --</option>

                                                </select>
                                            </div>
                                        </div>


                                    </div>

                                    <div class="row">
                                        <div class="col-md-4 mb-4">
                                            <label>NPSN Sekolah dan Nama Sekolah</label>
                                            <select required name="npsn_sekolah" class="form-control select2"
                                                id="data_sekolah" onchange="updateLocation()">
                                                <option value="">-- Pilih Data Sekolah --</option>
                                                @foreach ($status['s_sekolah'] as $v)
                                                    <option
                                                        {{ $datas->npsn_sekolah == $v->npsn_sekolah ? 'selected' : '' }}
                                                        value="{{ $v->npsn_sekolah }}"
                                                        data-kecamatan="{{ $v->kecamatan }}"
                                                        data-kabupaten="{{ $v->kabupaten }}">
                                                        {{ $v->npsn_sekolah }} - {{ $v->nama_sekolah }}
                                                    </option>
                                                @endforeach

                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Kabupaten Sekolah</label>
                                                <input id="kabupaten_sekolah" type="text" name="kabupaten_sekolah"
                                                    class="form-control"
                                                    placeholder="Otomatis terisi berdasarkan NPSN Sekolah" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Kecamatan Sekolah</label>
                                                <input id="kecamatan_sekolah" type="text" name="alamat_satuan"
                                                    class="form-control"
                                                    placeholder="Otomatis terisi berdasarkan NPSN Sekolah" readonly>
                                            </div>
                                        </div>


                                    </div>

                                </div>

                                <div class="card-footer text-right">
                                    <button class="btn btn-primary " type="submit">Submit</button>
                                    <button class="btn btn-secondary mx-1" type="reset">Reset</button>
                                    <a href="{{ route('guru.show', $datas->id) }}" class="btn btn-warning">Kembali</a>
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
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                updateLocation(); // Call function on page load to set initial values
            });

            function updateLocation() {
                console.log('object');
                const selectElement = document.getElementById('data_sekolah');
                const kecamatanInput = document.getElementById('kecamatan_sekolah');
                const kabupatenInput = document.getElementById('kabupaten_sekolah');

                // Ambil opsi yang dipilih
                const selectedOption = selectElement.options[selectElement.selectedIndex];

                // Ambil data kecamatan dan kabupaten dari atribut data
                const kecamatan = selectedOption.getAttribute('data-kecamatan');
                const kabupaten = selectedOption.getAttribute('data-kabupaten');

                // Perbarui nilai input kecamatan dan kabupaten
                kecamatanInput.value = kecamatan;
                kabupatenInput.value = kabupaten;
            }
            document.getElementById('data_sekolah').addEventListener('change', updateLocation);
        </script>

        <script>
            $(document).ready(function() {
                // Filtering functions
                function fillterJabatan() {
                    var jabEksternal = $('#jabEksternal').val();
                    var jabJenis = $('#jabJenis');
                    var option = '';
                    const dataJab = {!! json_encode($status) !!};
                    // console.log( dataJab['s_jabKependidikan']);
                    jabJenis.empty();

                    jabJenis.append($('<option>', {
                        value: '',
                        text: '-- Pilih Jabatan --',
                        disabled: true,
                        selected: true
                    }));

                    if (jabEksternal == 'Tenaga Pendidik') {
                        dataJab['s_jabPendidik'].forEach(item => {
                            option = $("<option>")
                                .text(item.name)
                                .attr('value', item.name);
                            jabJenis.append(option);
                        });
                    }
                    if (jabEksternal == 'Tenaga Kependidikan') {
                        dataJab['s_jabKependidikan'].forEach(item => {
                            option = $("<option>")
                                .text(item.name)
                                .attr('value', item.name);
                            jabJenis.append(option);
                        });
                    }
                    if (jabEksternal == 'Stakeholder') {
                        dataJab['s_jabStakeholder'].forEach(item => {
                            option = $("<option>")
                                .text(item.name)
                                .attr('value', item.name);
                            jabJenis.append(option);
                        });
                    }

                    // Trigger change to select the already selected option if any
                    jabJenis.val(jabJenis.data('selected-value')).trigger('change');
                }

                function fillterKategori() {
                    var jabKategori = $('#jabKategori').val();
                    var jabTugas = $('#jabTugas');
                    var option = '';
                    const dataJab = {!! json_encode($status) !!};
                    // console.log(dataJab);
                    jabTugas.empty();

                    jabTugas.append($('<option>', {
                        value: '',
                        text: '-- Pilih Tugas --',
                        disabled: true,
                        selected: true
                    }));

                    if (jabKategori == 'GP (Guru Penggerak)' ||
                        jabKategori == 'Diklat Cakep' ||
                        jabKategori == 'Diklat Cawas' ||
                        jabKategori == 'Lainnya' ||
                        jabKategori == 'Sertifikat GP (Guru Penggerak)'
                    ) {
                        dataJab['s_jabTugas'].forEach(item => {
                            option = $("<option>")
                                .text(item)
                                .attr('value', item);
                            jabTugas.append(option);
                        });
                    }

                    // Trigger change to select the already selected option if any
                    jabTugas.val(jabTugas.data('selected-value')).trigger('change');
                }

                function fillterJabatanKategori() {
                    var jabJenis = $('#jabJenis').val();
                    var jabKategori = $('#jabKategori');
                    var jabTugas = $('#jabTugas');
                    var option = '';
                    const dataJab = {!! json_encode($status) !!};
                    console.log(dataJab['s_jabKategoriPengawas']);
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

                    if (jabJenis == 'Guru' || jabJenis == 'Konselor') {
                        dataJab['s_jabKategori'].forEach(item => {
                            option = $("<option>")
                                .text(item)
                                .attr('value', item);
                            jabKategori.append(option);
                        });
                    } else if (jabJenis == 'Pengawas') {
                        dataJab['s_jabKategoriPengawas'].forEach(item => {
                            option = $("<option>")
                                .text(item)
                                .attr('value', item);
                            jabKategori.append(option);
                        });


                    } else if (jabJenis == 'Kepala Sekolah') {
                        dataJab['s_jabKategoriKepsek'].forEach(item => {
                            option = $("<option>")
                                .text(item)
                                .attr('value', item);
                            jabKategori.append(option);
                        });


                    }

                    // Trigger change to select the already selected option if any
                    jabKategori.val(jabKategori.data('selected-value')).trigger('change');
                }

                // Event listeners
                $('#jabEksternal').on('change', function() {
                    fillterJabatan();
                    fillterKategori();
                });

                $('#jabKategori').on('change', function() {
                    fillterKategori();
                });

                $('#jabJenis').on('change', function() {
                    fillterJabatanKategori();
                });

                // Initialize select elements on page load
                function initializeSelections() {
                    fillterJabatan();
                    fillterKategori();
                    fillterJabatanKategori();
                }

                initializeSelections();
            });
        </script>
    @endpush
@endsection
