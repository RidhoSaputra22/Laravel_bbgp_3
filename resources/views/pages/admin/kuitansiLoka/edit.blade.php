@extends('layouts.app', ['title' => 'Data Kuitansi Lokakarya'])
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
                <h1>Edit Kuitansi</h1>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-md-12 col-lg-12">
                        <form action="{{ route('kuitansi.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <input required name="id" type="hidden" class="form-control" id="id" readonly
                                value="{{ $kuitansi->id }}">
                            <input required name="id_pegawai_old" type="hidden" class="form-control" id="id_pegawai"
                                readonly value="{{ $kuitansi->pegawai_id }}">
                            <input required name="tahun_anggaran" type="hidden" class="form-control" id="tahun_anggaran"
                                readonly value="{{ date('Y') }}">


                            <div class="card">
                                <div class="card-body">
                                    <small>*Jika ingin mengubah data peserta, silahkan ke <u> Data kegiatan > Peserta
                                            Kegiatan </u></small>
                                    <div class="row mt-2">

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Kegiatan yang diikuti</label>
                                                <select name="kegiatan" id="kegiatan" class="form-control select2">
                                                    <option value="">-- pilih Kegiatan --</option>
                                                    @foreach ($kegiatan as $v)
                                                        <option value="{{ $v->id }}">{{ $v->nama_kegiatan }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Nama dan NIK</label>
                                                <select name="id_pegawai" id="idPeserta" class="form-control select2">
                                                    <option value="">-- Pilih peserta --</option>
                                                    {{-- @foreach ($datas['peserta'] as $i => $v)
                                                        <option data-no_ktp="{{ $v->no_ktp }}"
                                                            data-nama="{{ $v->nama }}"
                                                            data-golongan="{{ $v->golongan }}"
                                                            data-kabupaten="{{ $v->kabupaten }}"
                                                            data-jabatan="{{ $v->jabatan ?? $v->status_kepegawaian }}"
                                                            data-instansi="{{ $v->instansi }}"
                                                            data-no_surat_tugas="{{ $v->no_surat_tugas }}"
                                                            data-tgl_surat_tugas="{{ $v->tgl_surat_tugas }}"
                                                            data-status_keikutpesertaan="{{ $v->status_keikutpesertaan }}"
                                                            value="{{ $v->id }}">
                                                            {{ $v->pegawai->nip ?? '' }} - {{ $v->nama }} (
                                                            {{ $v->status_keikutpesertaan }} )
                                                        </option>
                                                    @endforeach --}}
                                                </select>
                                            </div>
                                        </div>



                                    </div>


                                    <div class="row">

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Nama </label>
                                                {{-- <input readonly required name="nip" id="nip" type="hidden"
                                                    class="form-control"> --}}
                                                <input value="{{ $kuitansi->peserta->nama ?? '' }}" readonly required
                                                    name="nama" id="nama" type="text" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>NIP</label>
                                                <input value="{{ $kuitansi->peserta->nip ?? '' }}" readonly required
                                                    name="nip" id="nip" type="text" class="form-control">
                                            </div>
                                        </div>



                                    </div>

                                    <div class="row">


                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Jabatan dalam kegiatan</label>
                                                <input value="{{ $kuitansi->peserta->status_keikutpesertaan ?? '' }}"
                                                    readonly required name="status_keikutpesertaan"
                                                    id="status_keikutpesertaan" type="text" class="form-control">
                                            </div>
                                        </div>


                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Jenis Golongan</label>
                                                <input value="{{ $kuitansi->peserta->jenis_gol ?? '' }}" readonly required
                                                    name="jenis_gol" id="jenis_gol" type="text" class="form-control">
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Golongan</label>
                                                <input value="{{ $kuitansi->peserta->golongan ?? '' }}" readonly required
                                                    name="golongan" id="golongan" type="text" class="form-control">
                                            </div>
                                        </div>

                                    </div>

                                    <div class="row">

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Instansi </label>
                                                <input value="{{ $kuitansi->peserta->instansi ?? '' }}" readonly required
                                                    name="instansi" id="instansi" type="text" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Nomor Surat Tugas</label>
                                                <input value="{{ $kuitansi->peserta->no_surat_tugas ?? '' }}" readonly
                                                    required name="no_surat_tugas" id="no_surat_tugas" type="text"
                                                    class="form-control">
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Tanggal Surat Tugas</label>
                                                <input value="{{ $kuitansi->peserta->tgl_surat_tugas ?? '' }}" readonly
                                                    required name="tgl_surat_tugas" id="tgl_surat_tugas" type="date"
                                                    class="form-control">
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Kabupaten / Kota</label>
                                                <input value="{{ $kuitansi->kabupaten->name ?? '' }}" readonly required
                                                    name="kabupaten" id="kabupaten" type="text" class="form-control">
                                            </div>
                                        </div>

                                    </div>



                                    <div class="row">

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Lokasi Asal</label>
                                                {{-- <input required name="biaya_penginapan" type="number"
                                                    class="form-control"> --}}
                                                <select required name="lokasi_asal" id="lokasi_asal"
                                                    class="form-control select2">
                                                    <option value="">-- Pilih kabupaten / kota --</option>
                                                    @foreach ($datas['kabupaten'] as $i => $v)
                                                        {{-- {{ dd($v->pegawai->nip) }} --}}
                                                        <option {{ $kuitansi->lokasi_asal == $v->id ? 'selected' : '' }}
                                                            value="{{ $v->id }}">
                                                            {{ $v->name ?? '' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Lokasi Tujuan</label>
                                                <input value="{{ $kuitansi->lokasi_tujuan }}" required
                                                    name="lokasi_tujuan" type="text" class="form-control">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Jenis Angkutan</label>
                                                <input value="{{ $kuitansi->jenis_angkutan }}" required
                                                    name="jenis_angkutan" type="text" class="form-control">
                                            </div>
                                        </div>

                                    </div>

                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Nomor Bukti</label>
                                                <input value="{{ $kuitansi->no_bukti }}" required name="no_bukti"
                                                    type="text" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Nomor MAK</label>
                                                <input value="{{ $kuitansi->no_MAK }}" required name="no_MAK"
                                                    type="text" class="form-control">
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <hr>
                                        <div class="card-title">
                                            <h5>Tiket</h5>
                                        </div>

                                        <div class="row">

                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Biaya Pergi (Rp. )</label>
                                                    <input value="{{ $kuitansi->biaya_pergi }}" name="biaya_pergi"
                                                        id="biaya_pergi" type="text" class="form-control currency">
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Biaya Pulang (Rp. )</label>
                                                    <input value="{{ $kuitansi->biaya_pulang }}" name="biaya_pulang"
                                                        id="biaya_pulang" type="text" class="form-control currency">
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Jumlah Biaya (Rp. )</label>
                                                    <input value="{{ $kuitansi->total_pp }}" readonly required
                                                        name="jumlah_biaya" id="jumlah_biaya_tiket" type="text"
                                                        class="form-control currency">
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Tax / Pajak Bandara (Rp. ) (Opsional)</label>
                                                    <input value="{{ $kuitansi->pajak_bandara }}" name="pajak_bandara"
                                                        id="pajak_bandara" placeholder="diisi jika ada" type="text"
                                                        class="form-control currency">
                                                </div>
                                            </div>

                                        </div>

                                    </div>

                                    <div>
                                        <hr>
                                        <div class="card-title">
                                            <h5>Transport Darat / Laut / Taxi</h5>
                                        </div>

                                        <div class="row">

                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Biaya (Rp. )</label>
                                                    <input value="{{ $kuitansi->biaya_asal }}" name="biaya_asal"
                                                        id="biaya_asal" type="text" class="form-control currency">
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Biaya Bea Jarak (Rp. )</label>
                                                    <input value="{{ $kuitansi->bea_jarak }}" name="bea_jarak"
                                                        id="bea_jarak" type="text" class="form-control currency">
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Tujuan (Rp. )</label>
                                                    <input value="{{ $kuitansi->biaya_tujuan }}" name="tujuan"
                                                        id="tujuan" placeholder="" type="text"
                                                        class="form-control currency">
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Jumlah Biaya Transport</label>
                                                    <input value="{{ $kuitansi->total_transport }}" readonly required
                                                        name="total_transport" id="total_transport" placeholder=""
                                                        type="text" class="form-control currency">
                                                </div>
                                            </div>

                                        </div>

                                    </div>

                                    <div>
                                        <hr>
                                        <div class="row">

                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Penginapan (Rp. )</label>
                                                    <input value="{{ $kuitansi->biaya_penginapan }}"
                                                        name="biaya_penginapan" id="biaya_penginapan" type="text"
                                                        class="form-control currency">
                                                    <div class="form-group mt-2">
                                                        <div class="custom-switches-stacked mt-2">
                                                            <label class="custom-switch">
                                                                <input type="checkbox" name="switch_penginapan"
                                                                    id="switch_penginapan" class="custom-switch-input">
                                                                <span class="custom-switch-indicator"></span>
                                                                <span class="custom-switch-description">Bill atau
                                                                    30%</span>
                                                            </label>
                                                        </div>

                                                        <label>Jumlah Bill</label>
                                                        <input value="{{ $kuitansi->bill_malam }}" required
                                                            name="bill_penginapan" id="bill_penginapan" type="text"
                                                            value="0" class="form-control currency">


                                                        <div class="form-group mt-3">
                                                            <label>Jumlah Malam Menginap (otomatis)</label>
                                                            <input value="{{ $kuitansi->jumlah_malam }}" readonly
                                                                name="jumlah_nginap" id="jumlah_nginap" type="text"
                                                                class="form-control ">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Uang Harian (Rp. )</label>
                                                    <input value="{{ $kuitansi->uang_harian }}" name="uang_harian"
                                                        id="uang_harian" type="text" class="form-control currency">
                                                </div>
                                            </div>



                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Potongan</label>
                                                    <input value="{{ $kuitansi->potongan }}" name="potongan"
                                                        id="potongan" placeholder="diisi jika ada" type="text"
                                                        class="form-control currency">
                                                </div>
                                            </div>

                                        </div>

                                    </div>

                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Biaya Penginapan (Rp. )</label>
                                                <input value="{{ $kuitansi->total_penginapan }}" readonly required
                                                    name="total_penginapan" id="total_penginapan" type="text"
                                                    class="form-control currency">


                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Biaya Harian (Rp. )</label>
                                                <input value="{{ $kuitansi->total_harian }}" readonly required
                                                    name="biaya_harian" id="biaya_harian" type="text"
                                                    class="form-control currency">
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Jumlah Hari</label>
                                                <input value="{{ $kuitansi->jumlah_hari }}" name="jumlah_hari"
                                                    id="jumlah_hari" type="text" class="form-control ">
                                            </div>
                                        </div>


                                    </div>

                                    <div class="row">

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Jumlah Uang Diterima</label>
                                                <input value="{{ $kuitansi->total_terima }}" readonly required
                                                    name="jumlah_biaya_diterima" id="jumlah_biaya_diterima"
                                                    placeholder="" type="text" class="form-control currency">
                                            </div>
                                        </div>
                                    </div>


                                    {{-- <div class="row">

                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Jumlah Hari</label>
                                                <input required name="jumlah_hari" placeholder="diisi jika ada" type="text"
                                                    class="form-control">
                                            </div>
                                        </div>
    

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Durasi Penginapan (hari)</label>
                                                <input required name="durasi_penginapan" type="text"
                                                    class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Durasi Uang Harian (hari)</label>
                                                <input required name="durasi_uang_harian" type="text"
                                                    class="form-control">
                                            </div>
                                        </div>
                                    </div> --}}


                                    {{-- Inputan Transportasi --}}
                                    {{-- <div class="row">
                                        <div class="col-md-12">
                                            <h5>Transportasi</h5>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Jenis Transportasi</label>
                                                <input required name="transportasis[0][transportasi]" type="text"
                                                    class="form-control"
                                                    placeholder="selain kendaraan, wajb isi keterangan">
                                            </div>
                                        </div>
                                        <div class="col-md">
                                            <div class="form-group">
                                                <label>Asal Transportasi</label>
                                                <input required name="transportasis[0][asal_transport]" type="text"
                                                    class="form-control">
                                            </div>
                                        </div>
                                        <span>
                                        </span>
                                        <div class="col-md">
                                            <div class="form-group">
                                                <label>Tujuan Transportasi</label>
                                                <input required name="transportasis[0][tujuan_transport]" type="text"
                                                    class="form-control">
                                            </div>
                                        </div>

                                        <div class="col-md">
                                            <div class="form-group">
                                                <label>Biaya Transportasi</label>
                                                <input required name="transportasis[0][biaya_transport]" type="text"
                                                    class="form-control">
                                            </div>

                                        </div>


                                    </div>
                                    <div class="row">
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label>Keterangan</label>
                                                <input name="transportasis[0][keterangan]" type="text"
                                                    class="form-control" placeholder="Keterangan transportasi">
                                            </div>
                                        </div>
                                    </div> --}}

                                    <div id="transportasi_fields"></div>

                                    <!-- Tombol untuk menambahkan transportasi -->
                                    {{-- <div class="row">
                                        <div class="col-md-12">
                                            <button class="btn btn-primary add_transportasi_field" type="button">Tambah
                                                Transportasi</button>
                                        </div>
                                    </div> --}}



                                    {{-- Tombol Aksi --}}
                                </div>
                                <div class="card-footer text-right">
                                    <button class="btn btn-primary" type="submit">Submit</button>
                                    <button class="btn btn-secondary mx-1" type="reset">Reset</button>
                                    <a href="{{ route('kuitansi.index') }}" class="btn btn-warning">Kembali</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>

    @push('scripts')
        <script src="{{ asset('library/cleave.js/dist/cleave.min.js') }}"></script>

        <script src="{{ asset('library/select2/dist/js/select2.full.min.js') }}"></script>
        <script src="{{ asset('library/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
        <script src="{{ asset('library/summernote/dist/summernote-bs4.js') }}"></script>
        <script src="{{ asset('library/bootstrap-timepicker/js/bootstrap-timepicker.min.js') }}"></script>
        <script src="{{ asset('library/cleave.js/dist/cleave.min.js') }}"></script>
        <script src="{{ asset('library/cleave.js/dist/addons/cleave-phone.id.js') }}"></script>
        <script src="{{ asset('library/selectric/public/jquery.selectric.min.js') }}"></script>
        <script src="{{ asset('js/page/forms-advanced-forms.js') }}"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>


        <script>
            // Menambahkan field transportasi tambahan
            $(document).ready(function() {

                $('.currency').each(function() {
                    new Cleave(this, {
                        numeral: true,
                        numeralThousandsGroupStyle: 'thousand'
                    });
                });
                // var max_fields = 10; // maximum input fields allowed
                // var wrapper = $("#transportasi_fields"); // fields wrapper
                // var add_button = $(".add_transportasi_field"); // Add button ID

                // var x = 1; // initial text box count
                // $(add_button).click(function(e) { // on add input button click
                //     e.preventDefault();
                //     if (x < max_fields) { // max input box allowed
                //         x++; // text box increment
                //         $(wrapper).append(
                //             '<div class="row"><div class="col-md"><div class="form-group"><label>Asal Transportasi</label><input required name="transportasis[' +
                //             x +
                //             '][asal_transport]" type="text" class="form-control"></div></div><div class="col-md"><div class="form-group"><label>Tujuan Transportasi</label><input required name="transportasis[' +
                //             x +
                //             '][tujuan_transport]" type="text" class="form-control"></div></div><div class="col-md"><div class="form-group"><label>Jenis Transportasi</label><input required name="transportasis[' +
                //             x +
                //             '][transportasi]" type="text" class="form-control"></div></div><div class="col-md"><div class="form-group"><label>Keterangan</label><input name="transportasis[' +
                //             x +
                //             '][keterangan]" type="text" class="form-control"></div></div><div class="col-md"><div class="form-group"><label>Biaya Transportasi</label><input required name="transportasis[' +
                //             x +
                //             '][biaya_transport]" type="number" class="form-control"></div></div><div class="col-md-1"><a href="#" class="btn btn-danger remove_field"><i class="fas fa-minus"></i></a></div></div>'
                //         ); // add input box
                //     }
                // });

                // $(wrapper).on("click", ".remove_field", function(e) { // user click on remove text
                //     e.preventDefault();
                //     $(this).parent('div').parent('div').remove();
                //     x--;
                // })

                $('#kegiatan').change(function() {
                    console.log($(this).val())

                    var kegiatan = $(this).val();

                    if (kegiatan === '') {
                        $('#idPeserta').html('<option value="">-- pilih peserta --</option>');
                        $('#instansi').val('');
                        $('#golongan').val('');
                        $('#jabatan').val('');
                        return;
                    }

                    $.ajax({
                        url: "{{ route('kuitansi.getPeserta') }}",
                        type: "GET",
                        data: {
                            kegiatan: kegiatan,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            var options = '<option value="">-- pilih peserta --</option>';

                            $.each(response, function(index, peserta) {
                                console.log(peserta)
                                options += `<option data-jabatan="${peserta.status_keikutpesertaan}" 
                                data-golongan="${peserta.golongan}" 
                                data-nama="${peserta.nama}" 
                                data-nip="${peserta.nip}" 
                                data-no_surat_tugas="${peserta.no_surat_tugas}" 
                                data-tgl_surat_tugas="${peserta.tgl_surat_tugas}" 
                                data-kabupaten="${peserta.kabupaten}" 
                                data-mulai="${peserta.tgl_kegiatan}" 
                                data-selesai="${peserta.tgl_selesai}" 
                                data-instansi="${peserta.instansi}" 
                                data-jenis_gol="${peserta.jenis_gol}" 
                                data-golongan="${peserta.golongan}" 
                                data-diluar_gol="${peserta.diluar_gol}" 
                                value="${peserta.id}">
                                    ${peserta.no_ktp} - ${peserta.nama} (${peserta.status_keikutpesertaan})
                                </option>`;
                            });

                            $('#idPeserta').html(options);
                        }
                    });

                });


                $('#idPeserta').change(function() {
                    var selectedOption = $(this).find('option:selected');
                    // console.log(selectedOption);
                    var status_keikutpesertaan = selectedOption.data('jabatan');
                    var jabatan = selectedOption.data('jabatan');
                    var nama = selectedOption.data('nama');
                    var no_ktp = selectedOption.data('no_ktp');
                    var nip = selectedOption.data('nip');
                    var jenis_gol = selectedOption.data('jenis_gol');
                    var kabupaten = selectedOption.data('kabupaten');
                    var golongan = selectedOption.data('golongan');
                    var gender = selectedOption.data('gender');
                    var instansi = selectedOption.data('instansi');
                    var no_surat_tugas = selectedOption.data('no_surat_tugas');
                    var tgl_surat_tugas = selectedOption.data('tgl_surat_tugas');
                    var no_wa = selectedOption.data('wa');
                    console.log(status_keikutpesertaan);

                    // Isi input form dengan data yang sesuai
                    $('#nip').val(nip);
                    $('#nama').val(`${nama}`);
                    $('#status_keikutpesertaan').val(status_keikutpesertaan);
                    $('#jabatan').val(jabatan);
                    $('#jenis_gol').val(jenis_gol);
                    $('#gender').val(gender);
                    $('#golongan').val(golongan);
                    $('#kabupaten').val(kabupaten);
                    $('#instansi').val(instansi);
                    $('#no_surat_tugas').val(no_surat_tugas);
                    $('#tgl_surat_tugas').val(tgl_surat_tugas);


                });

                const bill_penginapan = $('#bill_penginapan');

                $('#switch_penginapan').change(function() {

                    console.log($(this).val())
                    if ($(this).is(':checked')) {

                        bill_penginapan.val(formatRupiah(210000)); // Set nilai 210000
                    } else {
                        bill_penginapan.val(0); // Set nilai 0 jika tidak aktif
                    }
                    // Initialize calculations
                    calculateTiket();
                    calculateTransport();
                    calculatePenginapan();
                    calculateHarian();
                    calculateTotalBiaya();
                });

                // Calculation functions
                function calculateTiket() {
                    var biayaPergi = parseFloat($('#biaya_pergi').val().replace(/[^0-9]/g, '')) || 0;
                    var biayaPulang = parseFloat($('#biaya_pulang').val().replace(/[^0-9]/g, '')) || 0;
                    var pajakBandara = parseFloat($('#pajak_bandara').val().replace(/[^0-9]/g, '')) || 0;
                    var jumlahBiayaTiket = (biayaPergi + biayaPulang) - pajakBandara;
                    $('#jumlah_biaya_tiket').val(formatRupiah(jumlahBiayaTiket));
                    calculateTotalBiaya();
                }

                function calculateTransport() {
                    var biayaAsal = parseFloat($('#biaya_asal').val().replace(/[^0-9]/g, '')) || 0;
                    var beaJarak = parseFloat($('#bea_jarak').val().replace(/[^0-9]/g, '')) || 0;
                    var biayaTujuan = parseFloat($('#tujuan').val().replace(/[^0-9]/g, '')) || 0;
                    var totalTransport = biayaAsal + beaJarak + biayaTujuan;
                    $('#total_transport').val(formatRupiah(totalTransport));
                    calculateTotalBiaya();
                }

                function calculatePenginapan() {
                    var biayaPerMalam = parseFloat($('#biaya_penginapan').val().replace(/[^0-9]/g, '')) || 0;
                    var jumlahHari = parseFloat($('#jumlah_hari').val().replace(/[^0-9]/g, '')) || 0;

                    var jumlahNginap = parseFloat($('#jumlah_nginap').val().replace(/[^0-9]/g, '')) || 0;
                    var jumlahBill = parseFloat($('#bill_penginapan').val().replace(/[^0-9]/g, '')) || 0;

                    if (jumlahHari <= 1) {
                        jumlahNginap = 0;
                        var totalPenginapan = (biayaPerMalam + jumlahBill);
                    } else {
                        jumlahNginap = jumlahHari - 1
                        var totalPenginapan = (biayaPerMalam + jumlahBill) * jumlahNginap;
                    }

                    $('#jumlah_nginap').val(jumlahNginap)

                    $('#total_penginapan').val(formatRupiah(totalPenginapan));
                    calculateTotalBiaya();
                }

                function calculateHarian() {
                    var biayaPerHari = parseFloat($('#uang_harian').val().replace(/[^0-9]/g, '')) || 0;
                    var jumlahHari = parseFloat($('#jumlah_hari').val().replace(/[^0-9]/g, '')) || 0;


                    var biayaHarian = biayaPerHari * jumlahHari;
                    $('#biaya_harian').val(formatRupiah(biayaHarian));
                    calculateTotalBiaya();
                }

                // function calculateRepresentasi() {
                //     var biayaRepresentasi = parseFloat($('#biaya_representasi').val()) || 0;
                //     var jumlahHariRepresentasi = parseFloat($('#jumlah_hari_representasi').val()) || 0;
                //     $('#jumlah_biaya_representasi').val(biayaRepresentasi * jumlahHariRepresentasi);
                // }

                function calculateTotalBiaya() {
                    var jumlahBiayaTiket = parseFloat($('#jumlah_biaya_tiket').val().replace(/[^0-9]/g, '')) || 0;
                    var jumlahBiayaTransport = parseFloat($('#total_transport').val().replace(/[^0-9]/g, '')) || 0;

                    var jumlahBiayaPenginapan = parseFloat($('#total_penginapan').val().replace(/[^0-9]/g, '')) || 0;

                    var jumlahBiayaHarian = parseFloat($('#biaya_harian').val().replace(/[^0-9]/g, '')) || 0;
                    // var potongan = parseFloat($('#potongan').val().replace(/[^0-9]/g, '')) || 0;

                    var jumlahBiayaDiterima = jumlahBiayaTiket + jumlahBiayaTransport + jumlahBiayaPenginapan +
                        jumlahBiayaHarian;
                    $('#jumlah_biaya_diterima').val(formatRupiah(jumlahBiayaDiterima));
                }

                // Event listeners for calculation
                $('#biaya_pergi, #biaya_pulang, #pajak_bandara').on('input', calculateTiket);
                $('#biaya_asal, #bea_jarak, #tujuan').on('input', calculateTransport);
                $('#biaya_penginapan, #jumlah_hari, #bill_penginapan').on('input', calculatePenginapan);
                $('#uang_harian, #jumlah_hari').on('input', calculateHarian);

                // $('#jumlah_biaya_tiket, #pajak_bandara , #jumlah_hari, #tujuan, #uang_harian, #potongan')
                //     .on('input', calculateTotalBiaya);
                $('#jumlah_biaya_tiket, #total_transport, #total_penginapan, #biaya_harian', '#jumlah_hari').on('input',
                    calculateTotalBiaya);

                $('.rupiah').mask('000.000.000.000.000', {
                    reverse: true
                });

                // Initialize calculations
                calculateTiket();
                calculateTransport();
                calculatePenginapan();
                calculateHarian();
                calculateTotalBiaya();




            });

            function formatRupiah(number) {
                var reverse = number.toString().split('').reverse().join('');
                var ribuan = reverse.match(/\d{1,3}/g);
                return ribuan.join(',').split('').reverse().join('');
            }
        </script>
    @endpush
@endsection
