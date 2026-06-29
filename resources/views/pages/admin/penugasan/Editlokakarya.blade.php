@extends('layouts.app', ['title' => 'Internal'])
@section('content')
    @push('styles')
        <link rel="stylesheet" href="{{ asset('library/select2/dist/css/select2.min.css') }}">
        <link rel="stylesheet" href="{{ asset('library/selectric/public/selectric.css') }}">
    @endpush

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Edit Lokakarya</h1>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-md-12 col-lg-12">
                        <form id="lokakaryaForm" action="{{ route('internal.update.lokakarya') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="id" value="{{ $pendamping->id }}">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md">
                                            <div class="form-group">
                                                <label>Nama Pendamping</label>
                                                <input readonly value="{{ $pendamping->nama }}" required name="nama"
                                                    type="text" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md">
                                            <div class="form-group">
                                                <label>NIK</label>
                                                <input readonly value="{{ $pendamping->nik }}" required name="nik"
                                                    type="text" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md">
                                            <div class="form-group">
                                                <label>NIP</label>
                                                <input readonly value="{{ $pendamping->nip ?? '' }}" required name="nip"
                                                    type="text" class="form-control">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Golongan</label>
                                                <input readonly
                                                    value="{{ $pegawai->golongan == '' || $pegawai->golongan == null || $pegawai->golongan == 'Tidak ada' ? 'Tidak ada' : $pegawai->golongan }}"
                                                    required name="golongan" id="golongan" type="text" class="form-control">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <label>Pilih Kegiatan</label>
                                            <select required id="selectKegiatan" name="kegiatan"
                                                class="form-control select2">
                                                <option value="">-- Pilih kegiatan --</option>
                                                @if (!$datas['dataPenugasanPpnpn']->isEmpty())
                                                    @foreach ($datas['dataPenugasanPpnpn'] as $v)
                                                        <option data-kabupaten="{{ $v->kota }}"
                                                            data-tgl-kegiatan="{{ $v->tgl_kegiatan }}"
                                                            data-tgl-selesai-kegiatan="{{ $v->tgl_selesai_kegiatan }}"
                                                            data-jam-selesai="{{ $v->jam_selesai }}"
                                                            data-jam-mulai="{{ $v->jam_mulai }}"
                                                            {{ $pendamping->kegiatan == $v->kegiatan ? 'selected' : '' }}
                                                            value="{{ $v->kegiatan }}">{{ $v->kegiatan }}
                                                        </option>
                                                    @endforeach
                                                @else
                                                    @foreach ($datas['dataPenugasanPegawai'] as $v)
                                                        <option data-kabupaten="{{ $v->kota }}"
                                                            data-tgl-kegiatan="{{ $v->tgl_kegiatan }}"
                                                            data-tgl-selesai-kegiatan="{{ $v->tgl_selesai_kegiatan }}"
                                                            data-jam-selesai="{{ $v->jam_selesai }}"
                                                            data-jam-mulai="{{ $v->jam_mulai }}"
                                                            {{ $pendamping->kegiatan == $v->kegiatan ? 'selected' : '' }}
                                                            value="{{ $v->kegiatan }}">{{ $v->kegiatan }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            <input required readonly name="jenis" type="hidden"
                                                value="Pendamping Lokakarya" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label>Kabupaten / Kota</label>
                                            <select required id="selectKabupaten" name="kota"
                                                class="form-control select2">
                                                <option value="">-- Pilih kabupaten/kota --</option>
                                                @foreach ($datas['kota'] as $v)
                                                    <option {{ $pendamping->kota == $v->name ? 'selected' : '' }}
                                                        value="{{ $v->name }}">{{ $v->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md">
                                            <div class="form-group">
                                                <label>Mulai Kegiatan</label>
                                                <input type="text" id="inputMulaiKegiatan" name="mulai_kegiatan"
                                                    class="form-control datetimepicker"
                                                    value="{{ $datas['mulai_kegiatan'] }}">
                                            </div>
                                        </div>
                                        <div class="col-md">
                                            <div class="form-group">
                                                <label>Selesai Kegiatan</label>
                                                <input type="text" id="inputSelesaiKegiatan" name="selesai_kegiatan"
                                                    class="form-control datetimepicker"
                                                    value="{{ $datas['selesai_kegiatan'] }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row my-3">

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Biaya Pergi</label>
                                                <input value="{{ $pendamping->transport_pergi }}" required
                                                    name="transport_pergi" id="transport_pergi" type="text"
                                                    class="form-control">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Biaya Pulang</label>
                                                <input value="{{ $pendamping->transport_pulang }}" required
                                                    name="transport_pulang" id="transport_pulang" type="text"
                                                    class="form-control">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="custom-switches-stacked">
                                                <label class="custom-switch">
                                                    <input type="checkbox" name="switch_penginapan"
                                                        id="switch_penginapan" class="custom-switch-input">
                                                    <span class="custom-switch-indicator"></span>
                                                    <span class="custom-switch-description">Bill atau 30%</span>
                                                </label>
                                            </div>

                                            <div class="form-group">
                                                <label></label>
                                                <input readonly required name="bill_penginapan" value="0"
                                                    id="bill_penginapan" type="text" class="form-control">
                                            </div>

                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group mt-4">
                                                <label>Bukti Bill (jika ada perubahan (opsional))</label>
                                                <input name="bukti_bill" id="bukti_bill" type="file"
                                                    class="form-control">
                                                <input name="old_bukti_bill" id="old_bukti_bill" type="hidden"
                                                    class="form-control" value="{{ $pendamping->bukti_bill }}">
                                            </div>
                                        </div>

                                        

                                    </div>


                                    <div class="row my-3">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Hotel</label>
                                                <input value="{{ $pendamping->hotel }}" name="hotel" type="text"
                                                    class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Hari 1</label>
                                                <input value="{{ $pendamping->hari_1 }}" name="hari_1" id="hari_1"
                                                    type="text" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Hari 2</label>
                                                <input value="{{ $pendamping->hari_2 }}" name="hari_2" id="hari_2"
                                                    type="text" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Hari 3</label>
                                                <input value="{{ $pendamping->hari_3 }}" name="hari_3" id="hari_3"
                                                    type="text" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Hari 4</label>
                                                <input value="{{ $pendamping->hari_4 }}" name="hari_4" id="hari_4"
                                                    type="text" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Hari 5</label>
                                                <input value="{{ $pendamping->hari_5 }}" name="hari_5" id="hari_5"
                                                    type="text" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Hari 6</label>
                                                <input value="{{ $pendamping->hari_6 }}" name="hari_6" id="hari_6"
                                                    type="text" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Hari 7</label>
                                                <input value="{{ $pendamping->hari_7 }}" name="hari_7" id="hari_7"
                                                    type="text" class="form-control">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Total</label>
                                                <input readonly value="0" name="total" id="total"
                                                    type="text" class="form-control">
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <div class="card-footer text-right">
                                    <button class="btn btn-primary " type="submit">Submit</button>
                                    <button class="btn btn-secondary mx-1" type="reset">Reset</button>
                                    <a href="{{ session('role') == 'pegawai' ? route('pegawai.show', session('no_ktp')) : route('internal.index') }}"
                                        class="btn btn-warning">Kembali</a>
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
        <script src="{{ asset('library/cleave.js/dist/cleave.min.js') }}"></script>

        <script>
            $(document).ready(function() {
                // Initialize Select2
                $('#selectNama').select2();

                // Format currency inputs
                const cleaveCurrency = [
                    new Cleave('#transport_pulang', {
                        numeral: true,
                        numeralThousandsGroupStyle: 'thousand',
                        prefix: 'Rp ',
                        noImmediatePrefix: true
                    }),
                    new Cleave('#transport_pergi', {
                        numeral: true,
                        numeralThousandsGroupStyle: 'thousand',
                        prefix: 'Rp ',
                        noImmediatePrefix: true
                    }),
                    new Cleave('#bill_penginapan', {
                        numeral: true,
                        numeralThousandsGroupStyle: 'thousand',
                        prefix: 'Rp ',
                        noImmediatePrefix: true
                    }),
                    new Cleave('#hari_1', {
                        numeral: true,
                        numeralThousandsGroupStyle: 'thousand',
                        prefix: 'Rp ',
                        noImmediatePrefix: true
                    }),
                    new Cleave('#hari_2', {
                        numeral: true,
                        numeralThousandsGroupStyle: 'thousand',
                        prefix: 'Rp ',
                        noImmediatePrefix: true
                    }),
                    new Cleave('#hari_3', {
                        numeral: true,
                        numeralThousandsGroupStyle: 'thousand',
                        prefix: 'Rp ',
                        noImmediatePrefix: true
                    }),
                    new Cleave('#hari_4', {
                        numeral: true,
                        numeralThousandsGroupStyle: 'thousand',
                        prefix: 'Rp ',
                        noImmediatePrefix: true
                    }),
                    new Cleave('#hari_5', {
                        numeral: true,
                        numeralThousandsGroupStyle: 'thousand',
                        prefix: 'Rp ',
                        noImmediatePrefix: true
                    }),
                    new Cleave('#hari_6', {
                        numeral: true,
                        numeralThousandsGroupStyle: 'thousand',
                        prefix: 'Rp ',
                        noImmediatePrefix: true
                    }),
                    new Cleave('#hari_7', {
                        numeral: true,
                        numeralThousandsGroupStyle: 'thousand',
                        prefix: 'Rp ',
                        noImmediatePrefix: true
                    }),
                ];

                const bill_penginapan = $('#bill_penginapan');

                $('#switch_penginapan').change(function() {

                    let getGolongan = $('#golongan');
                    let cutString = getGolongan.val().split('/');
                    console.log(cutString)

                    if ($(this).is(':checked')) {

                        if (cutString[0] == 'Tidak ada' || cutString[0] == 'I' || cutString[0] == 'II' ||
                            cutString[0] == 'III') {
                            bill_penginapan.val(formatRupiah(223500)); // Set nilai 210000
                        } else {

                            bill_penginapan.val(formatRupiah(426900)); // Set nilai 210000
                        }


                    } else {
                        bill_penginapan.val(0); // Set nilai 0 jika tidak aktif
                    }

                    // Recalculate total whenever bill_penginapan changes
                    calculateTotal();
                });

                // Calculate total whenever an input changes
                $('#transport_pulang, #transport_pergi, #hari_1, #hari_2, #hari_3, #hari_4, #hari_5, #hari_6, #hari_7, #bill_penginapan')
                    .on('input', function() {
                        calculateTotal();
                    });


                function calculateTotal() {
                    let total = 0;

                    // Loop through each input by ID, parse the value, and add to total
                    total += parseCurrency($('#transport_pulang').val());
                    total += parseCurrency($('#transport_pergi').val());
                    total += parseCurrency($('#hari_1').val());
                    total += parseCurrency($('#hari_2').val());
                    total += parseCurrency($('#hari_3').val());
                    total += parseCurrency($('#hari_4').val());
                    total += parseCurrency($('#hari_5').val());
                    total += parseCurrency($('#hari_6').val());
                    total += parseCurrency($('#hari_7').val());
                    total += parseCurrency($('#bill_penginapan').val());

                    $('#total').val(formatRupiah(total));
                }

                function parseCurrency(value) {
                    return parseInt(value.replace(/[^0-9]/g, '')) || 0;
                }

                $('#selectKegiatan').change(function() {
                    var selectedOption = $(this).find('option:selected');
                    var kabupaten = selectedOption.data('kabupaten');
                    var tglKegiatan = selectedOption.data('tgl-kegiatan');
                    var tglSelesaiKegiatan = selectedOption.data('tgl-selesai-kegiatan');
                    var jamMulai = selectedOption.data('jam-mulai');
                    var jamSelesai = selectedOption.data('jam-selesai');

                    // Set nilai untuk kabupaten/kota
                    $('#selectKabupaten').val(kabupaten).trigger('change');

                    // Set nilai untuk tanggal kegiatan dan selesai kegiatan
                    $('#inputMulaiKegiatan').val(tglKegiatan + ' ' + jamMulai);
                    $('#inputSelesaiKegiatan').val(tglSelesaiKegiatan + ' ' + jamSelesai);
                });

                // Handle change event on select element
                $('#selectNama').on('change', function() {
                    // Get selected option
                    var selectedOption = $(this).find(':selected');

                    // Get NIP from data-nip attribute
                    var nip = selectedOption.data('nip');

                    // Set NIP value to input field
                    $('input[name="nip"]').val(nip);
                });

                function formatRupiah(number) {
                    var reverse = number.toString().split('').reverse().join('');
                    var ribuan = reverse.match(/\d{1,3}/g);
                    return 'Rp ' + ribuan.join('.').split('').reverse().join('');
                }

                // Remove currency format before form submit
                $('#lokakaryaForm').on('submit', function() {
                    $('#transport_pulang, #transport_pergi, #hari_1, #hari_2, #hari_3, #hari_4, #hari_5, #hari_6, #hari_7, #bill_penginapan')
                        .each(function() {
                            var value = $(this).val();
                            $(this).val(value.replace(/[^0-9]/g, ''));
                        });
                });
            });
        </script>
    @endpush
@endsection
