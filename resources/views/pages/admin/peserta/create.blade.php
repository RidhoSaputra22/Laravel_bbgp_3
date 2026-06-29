@extends('layouts.app', ['title' => 'Tambah Data Peserta'])

@section('content')
    @push('styles')
        <link rel="stylesheet" href="{{ asset('library/select2/dist/css/select2.min.css') }}">
        <link rel="stylesheet" href="{{ asset('library/selectric/public/selectric.css') }}">
        <style>
            .signature-pad {
                border: 1px solid #e0e0e0;
                border-radius: 4px;
            }
        </style>
    @endpush

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Tambah Peserta </h1>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-md-12 col-lg-12">
                        <form action="{{ route('peserta.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="card">
                                <div class="card-body">

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Kegiatan yang di daftarkan</label>

                                                <select name="id_kegiatan" id="" class="form-control select2">
                                                    <option value="">-- pilih kegiatan --</option>
                                                    @foreach ($kegiatan as $v)
                                                        <?php
                                                        setlocale(LC_TIME, 'id_ID.UTF-8');
                                                        
                                                        $tgl_kegiatan = strftime('%d %B', strtotime($v->tgl_kegiatan));
                                                        $tgl_selesai = strftime('%d %B %Y', strtotime($v->tgl_selesai));
                                                        ?>
                                                        <option value="{{ $v->id }}">
                                                            {{ $v->nama_kegiatan }} (
                                                            {{ $tgl_kegiatan }} -
                                                            {{ $tgl_selesai }}
                                                            ) di {{ $v->tempat_kegiatan }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                {{-- <input  name="golongan" id="golongan" type="text"
                                                    class="form-control" required> --}}
                                            </div>

                                        </div>
                                    </div>

                                    <div class="row">

                                        {{-- <div class="col-md-5">
                                            <div class="form-group">
                                                <label>Nama dan NIK</label>
                                                <input  name="no_ktp" id="no_ktp" type="text"
                                                    class="form-control" required>
                                                <select required name="id_pegawai" id="id_pegawai"
                                                    class="form-control select2">
                                                    <option value="">-- Pilih pegawai --</option>
                                                    @foreach ($merge as $v)
                                                        <option data-no_ktp="{{ $v->no_ktp }}"
                                                            data-nama="{{ $v->nama_lengkap }}"
                                                            data-golongan="{{ $v->golongan }}"
                                                            data-kabupaten="{{ $v->kabupaten }}"
                                                            data-gender="{{ $v->gender }}"
                                                            data-jabatan="{{ $v->jabatan ?? $v->status_kepegawaian }}"
                                                            data-instansi="{{ $v->instansi }}"
                                                            data-wa="{{ $v->no_wa }}"
                                                            data-hp="{{ $v->no_hp }}"
                                                            data-instansi="{{ $v->instansi }}"
                                                            value="{{ $v->id }}">
                                                            {{ $v->no_ktp }} - {{ $v->nama_lengkap }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div> --}}

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Nama Lengkap</label>
                                                <input name="nama" id="nama" type="nama" class="form-control"
                                                    required>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>NIK</label>
                                                <input name="no_ktp" id="no_ktp" type="text" class="form-control"
                                                    required>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>NIP</label>
                                                <input name="nip" id="nip" type="text" class="form-control"
                                                    required>
                                            </div>
                                        </div>







                                    </div>

                                    <div class="row">

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Kabupaten / Kota (jika tidak ada, pilih lainnya)</label>
                                                <select name="kabupaten" id="kabupaten" class="form-control select2">
                                                    <option id="selectedKab" value="">-- piilih kabupaten --</option>
                                                    @foreach ($status['kabupaten'] as $v)
                                                        <option value=" {{ $v->name }} ">{{ $v->name }}</option>
                                                    @endforeach
                                                    <option id="selectedKabLainnya" value="lainnya">Lainnya</option>
                                                </select>
                                                {{-- <input readonly name="kabupaten" id="kabupaten" type="text"
                                                class="form-control" required> --}}
                                            </div>
                                        </div>

                                        <div class="col-md-4" id="formAsal" style="display: none;">
                                            <div class="form-group">
                                                <label>Asal Kabupaten / Kota</label>
                                                <input name="asal_kabupaten" id="asal_kabupaten" type="text"
                                                    class="form-control">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Instansi</label>
                                                <input name="instansi" id="instansi" type="text" class="form-control"
                                                    required>
                                            </div>
                                        </div>




                                    </div>

                                    <div class="row">


                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Jenis Kelamin</label>
                                                <select name="gender" id="" class="form-control">
                                                    <option value="">-- pilih jenis kelamin --</option>
                                                    <option value="Laki-laki">Laki-laki</option>
                                                    <option value="Perempuan">Perempuan</option>
                                                </select>
                                                {{-- <input name="gender" id="gender" type="text" class="form-control"
                                                    required> --}}
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Jenis Golongan</label>
                                                <select required name="jenis_gol" id="jenis_gol" class="form-control ">
                                                    <option value="">-- pilih jenis kelamin --</option>
                                                    <option value="PNS">PNS</option>
                                                    <option value="P3K">PPPK/P3K</option>
                                                    <option value="Tidak ada golongan">Tidak Ada Golongan</option>
                                                </select>
                                            </div>
                                        </div>


                                        <div class="col-md-3" id="form_diluar_gol">
                                            <div class="form-group">
                                                <label>Isi Golongan </label>
                                                <input name="diluar_gol" id="diluar_gol" type="text"
                                                    class="form-control">
                                            </div>
                                        </div>

                                        <div class="col-md-2" id="form_golongan_pns">
                                            <div class="form-group">
                                                <label>Golongan PNS</label>

                                                <select name="golongan_pns" id="golongan_pns" class="form-control select2">
                                                    <option id="valPns" value="">-- pilih golongan --
                                                    </option>
                                                    @foreach ($status['golongan'] as $v)
                                                        <option value="{{ $v->name }}">{{ $v->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                {{-- <input  name="golongan" id="golongan" type="text"
                                                class="form-control" required> --}}
                                            </div>
                                        </div>

                                        <div class="col-md-3" id="form_golongan_p3k">
                                            <div class="form-group">
                                                <label>Golongan PPPK/P3K</label>

                                                <select name="golongan_p3k" id="golongan_p3k"
                                                    class="form-control select2">
                                                    <option id="valP3K" value="">-- pilih golongan --
                                                    </option>
                                                    @foreach ($status['golongan_p3k'] as $v)
                                                        <option value="{{ $v->name }}">{{ $v->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                {{-- <input  name="golongan" id="golongan" type="text"
                                                class="form-control" required> --}}
                                            </div>
                                        </div>

                                    </div>

                                    <div class="row">

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Nomor WhatsApp</label>
                                                <input name="no_wa" id="no_wa" type="number"
                                                    class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Nomor Handphone</label>
                                                <input name="no_hp" id="no_hp" type="number"
                                                    class="form-control">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Nomor Surat (CONTOH : 0562/KEU/IV/2024)</label>
                                                <input name="no_surat_tugas" type="text" class="form-control"
                                                    required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Tanggal Surat Tugas</label>
                                                <input name="tgl_surat_tugas" type="date" class="form-control"
                                                    required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Status Keikutpesertaan</label>
                                                <select required name="status_keikutpesertaan" id="status_keikutpesertaan"
                                                    class="form-control">
                                                    <option value="">-- Pilih Status --</option>
                                                    <option value="peserta">Peserta</option>
                                                    <option value="panitia">Panitia</option>
                                                    <option value="narasumber">Narasumber</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="formOpsional">
                                        <div class="row">
                                            <div class="col-md-4" id="transportContainer">
                                                <div class="form-group">
                                                    <label>Kelengkapan Peserta (Transport)</label>
                                                    <select name="kelengkapan_transport" id="kelengkapan_transport"
                                                        class="form-control">
                                                        <option value="">-- Pilih Kelengkapan Transport --</option>
                                                        <option value="Ada">Ada Transport</option>
                                                        <option value="Tidak Ada">Tidak Ada Transport</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4" id="biodataContainer">
                                                <div class="form-group">
                                                    <label>Kelengkapan Peserta (Biodata)</label>
                                                    <select name="kelengkapan_biodata" id="kelengkapan_biodata"
                                                        class="form-control">
                                                        <option value="">-- Pilih Kelengkapan Biodata --</option>
                                                        <option value="Ada">Ada Biodata</option>
                                                        <option value="Tidak Ada">Tidak Ada Biodata</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    {{-- Signature Section --}}
                                    <div class="row">
                                        <div class="col-md-3">
                                            {{-- Uncomment if using signature --}}
                                            {{-- <div class="form-group">
                                                <label for="signature">Tanda Tangan Digital</label>
                                                <div id="signature-pad" class="signature-pad">
                                                    <canvas width="600" height="200"></canvas>
                                                </div>
                                                <input type="hidden" name="signature" id="signature">
                                                <button id="clear" class="btn btn-secondary mt-2">Clear</button>
                                            </div> --}}
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer text-right">
                                    <button class="btn btn-primary" type="submit"
                                        onclick="submitSignature()">Submit</button>
                                    <button class="btn btn-secondary mx-1" type="reset">Reset</button>
                                    <a href="{{ route('peserta.index') }}" class="btn btn-warning">Kembali</a>
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
        <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
        <script>
            $(document).ready(function() {

                $.ajax({
                    url: '{{ route('user.peserta.cekData') }}',
                    type: 'GET',
                    data: {

                        nik: '{{ session('dataAda') }}'
                    },
                    success: function(response) {
                        console.log(response.data);

                        $('#no_ktp').val(response.data.no_ktp);
                        $('#nama').val(response.data.nama);
                        $('#jabatan').val(response.data.jabatan);
                        $('#gender').val(response.data.jkl);
                        $('#golongan').val(response.data.golongan);
                        $('#kabupaten').val(response.data.kabupaten);
                        $('#instansi').val(response.data.instansi);
                        $('#no_hp').val(response.data.no_hp);
                        $('#no_wa').val(response.data.no_wa);

                        // console.log($('#no_wa').val(response.data.no_wa));

                    },
                    error: function(error) {
                        console.error(error);
                        alert('Error fetching detail.');
                    }
                });


                $('.select2').select2();
                $('#formOpsional').hide();
                $('#narasumberTime').hide();

                // Handle change event for Kabupaten selection
                $('#kabupaten').change(function() {
                    let selectedValue = $(this).val();
                    if (selectedValue === 'lainnya') {
                        $('#formAsal').show(); // Show the input for manual entry
                    } else {
                        $('#formAsal').hide(); // Hide the input
                        $('#asal_kabupaten').val(''); // Clear the input value if not needed
                    }
                });


                $('#status_keikutpesertaan').change(function() {
                    let status = $(this).val();
                    if (status === 'peserta') {
                        $('#formOpsional').show();
                    } else if (status === 'panitia') {
                        $('#formOpsional').hide();
                    } else if (status === 'narasumber') {
                        $('#formOpsional').hide();

                    } else {
                        $('#formOpsional').hide();
                    }
                });

                // change jenis golongan 
                let gol_pns = $('#form_golongan_pns');
                let gol_p3k = $('#form_golongan_p3k')
                let tdk_gol = $('#form_diluar_gol')

                $('#form_diluar_gol').hide();
                $('#form_golongan_pns').hide();
                $('#form_golongan_p3k').hide();

                let valPns = $('#valPns');
                let valP3K = $('#valP3K')

                $('#jenis_gol').change(function() {
                    let status = $(this).val();
                    console.log(status);
                    if (status == 'PNS') {
                        gol_pns.show();
                        gol_p3k.hide().val('');
                        tdk_gol.hide().val('');
                    } else if (status == 'P3K') {
                        gol_p3k.show();
                        gol_pns.hide().val('');
                        tdk_gol.hide().val('');
                    } else if (status == 'Tidak ada golongan') {
                        gol_pns.hide().val('');
                        gol_p3k.hide().val('');
                        tdk_gol.show();
                    } else {
                        gol_pns.hide().val('');
                        gol_p3k.hide().val('');
                        tdk_gol.hide();
                    }



                });

                $('#id_pegawai').change(function() {
                    var selectedOption = $(this).find('option:selected');
                    // console.log(selectedOption);
                    var jabatan = selectedOption.data('jabatan');
                    var nama = selectedOption.data('nama');
                    var no_ktp = selectedOption.data('no_ktp');
                    var kabupaten = selectedOption.data('kabupaten');
                    var golongan = selectedOption.data('golongan');
                    var gender = selectedOption.data('gender');
                    var instansi = selectedOption.data('instansi');
                    var no_hp = selectedOption.data('hp');
                    var no_wa = selectedOption.data('wa');
                    // console.log(kabupaten);

                    // Isi input form dengan data yang sesuai
                    $('#no_ktp').val(no_ktp);
                    $('#nama').val(`${nama}`);
                    $('#jabatan').val(jabatan);
                    $('#gender').val(gender);
                    $('#golongan').val(golongan);
                    $('#kabupaten').val(kabupaten);
                    $('#instansi').val(instansi);
                    $('#no_hp').val(no_hp);
                    $('#no_wa').val(no_wa);
                });







            });

            const canvas = document.querySelector("canvas");
            const signaturePad = new SignaturePad(canvas);

            document.getElementById('clear').addEventListener('click', function(event) {
                event.preventDefault();
                signaturePad.clear();
            });

            function submitSignature() {
                if (signaturePad.isEmpty()) {
                    alert("Please provide a signature first.");
                } else {
                    const dataUrl = signaturePad.toDataURL();
                    document.getElementById('signature').value = dataUrl;
                }
            }
        </script>
    @endpush
@endsection
