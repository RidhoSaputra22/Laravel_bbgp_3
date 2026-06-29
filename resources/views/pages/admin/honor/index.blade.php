@extends('layouts.app', ['title' => 'Data Honor Kegiatan'])

@section('content')
    @push('styles')
        <link rel="stylesheet" href="{{ asset('library/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('library/datatables.net-select-bs4/css/select.bootstrap4.min.css') }}">
        <style>
            .table-internal {
                display: none;
            }
        </style>
    @endpush

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Data Honor Kegiatan BBGTK</h1>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-14">
                        <div class="card">
                            <div class="card-body">
                                <!-- Navigation Buttons -->
                                <a href="{{ route('honor.create') }}" class="btn btn-primary text-white my-3">+ Tambah
                                    Honor</a>

                                <h6>Filter By Kegiatan dan Status Keikutpesertaan</h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <select name="kegiatanSelect" class="form-control" id="kegiatanSelect">
                                                <option value="">-- pilih kegiatan --</option>
                                                @foreach ($kegiatan as $v)
                                                    <?php
                                                    setlocale(LC_TIME, 'id_ID.UTF-8');
                                                    $tgl_kegiatan = strftime('%d %B', strtotime($v->tgl_kegiatan));
                                                    $tgl_selesai = strftime('%d %B %Y', strtotime($v->tgl_selesai));
                                                    ?>
                                                    <option value="{{ $v->id }}">{{ $v->nama_kegiatan }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <select name="jabatanKegiatan" class="form-control" id="jabatanKegiatan">
                                                <option value="">-- pilih status keikutsertaan --</option>
                                                {{-- <option value="peserta">Peserta</option> --}}

                                                <option value="panitia">Panitia</option>
                                                <option value="narasumber">Narasumber</option>
                                                <option value="peserta">Peserta</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div id="printShow">

                                    <div class="row mb-3">
                                        <div class="text-white">
                                            <a href="#" id="printHonorPanitia" class="btn btn-success mx-3">
                                                <i class="fas fa-print mr-2"></i>Print Honor Panitia
                                            </a>
                                            <a href="#" id="printHonorNarasumber" class="btn btn-success">
                                                <i class="fas fa-print mr-2"></i>Print Honor Narasumber
                                            </a>
                                            <a href="#" id="printHonorPeserta" class="btn btn-success">
                                                <i class="fas fa-print"></i>Print Honor Peserta
                                            </a>
                                        </div>
                                    </div>
                                    <div class="row">

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Nomor Surat</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control"
                                                        placeholder="masukkan nomor surat" id="no_surat" name="no_surat">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Tanggal Surat</label>
                                                <div class="input-group">
                                                    <input type="date" class="form-control" id="tgl_surat"
                                                        name="tgl_surat">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Kode Anggaran</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control"
                                                        placeholder="masukkan kode anggaran" id="kode_anggaran"
                                                        name="kode_anggaran">
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <!-- Tables Section -->
                                <div class="table-responsive ">
                                    <table class="table table-striped " id="table-kegiatan">
                                        <thead>
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th>Nama Penerima</th>
                                                <th>Jabatan dalam kegiatan</th>
                                                <th>Kegiatan</th>
                                                <th>Jenis Golongan</th>
                                                <th>Golongan</th>
                                                <th>Instansi</th>
                                                <th>JP Realisasi</th>
                                                <th>Jumlah</th>
                                                <th>Jumlah Honor</th>
                                                <th>Jumlah Diterima</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($datas as $i => $data)
                                                <tr>
                                                    <td>{{ ++$i }}</td>
                                                    <td>{{ $data['nama'] ?? '' }}</td>
                                                    <td>{{ $data['jabatan'] ?? '' }}</td>
                                                    <td>{{ $data['kegiatan'] ?? '' }} <p class="text-white">
                                                            {{ $data['id_kegiatan'] }}</p>
                                                    </td>
                                                    <td>{{ $data['jenis_gol'] ?? '' }}</td>
                                                    <td>{{ $data['golongan'] ?? '' }}</td>
                                                    <td>{{ $data['instansi'] ?? '' }}</td>
                                                    <td>{{ $data['jp_realisasi'] }}.0</td>
                                                    <td class="text-nowrap">{{ $data['jumlah'] ?? 0 }} </td>
                                                    <td class="text-nowrap">{{ $data['jumlah_honor'] ?? 0 }}</td>
                                                    <td class="text-nowrap"> {{ $data['total'] ?? 0 }}</td>
                                                    <td>
                                                        <a href="{{ route('honor.edit', $data['id']) }}"
                                                            class="btn btn-warning my-2"><i class="fas fa-edit"></i></a>
                                                        <button onclick="deleteData({{ $data['id'] }}, 'honor')"
                                                            class="btn btn-danger">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                            </div>
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
        <script src="{{ asset('js/page/modules-datatables.js') }}"></script>

        <script type="text/javascript">
            $(document).ready(function() {
                var tableKegiatan = $('#table-kegiatan').DataTable();
                var showPrint = $('#printShow').hide();

                var kegiatan = '';
                var jabatan = '';

                $('#kegiatanSelect').on('change', function(e) {
                    e.preventDefault();
                    var kegiatanValue = $(this).val();
                    if (kegiatanValue == '') {
                        showPrint.hide();
                        return
                    }
                    console.log(kegiatanValue);

                    tableKegiatan.column(3).search(kegiatanValue).draw();
                    showPrint.show();

                    // Update the export link
                    var jabatanValue = $('#jabatanKegiatan').val();
                    kegiatan = kegiatanValue;
                    jabatan = jabatanValue;
                    updatePrintLink(kegiatanValue, jabatanValue);
                });

                $('#jabatanKegiatan').on('change', function(e) {
                    e.preventDefault();
                    var jabatanValue = $(this).val();
                    console.log(jabatanValue);

                    tableKegiatan.column(2).search(jabatanValue).draw();

                    // Update the export link
                    var kegiatanValue = $('#kegiatanSelect').val();
                    kegiatan = kegiatanValue;
                    jabatan = jabatanValue;
                    updatePrintLink(kegiatanValue, jabatanValue);
                });

                $('#printHonorPanitia').on('click', function(e) {
                    e.preventDefault();
                    let no_surat = $('[name="no_surat"]').val();
                    let tgl_surat = $('[name="tgl_surat"]').val();
                    let kode_anggaran = $('[name="kode_anggaran"]').val();

                    swal({
                            title: 'Apakah anda sudah yakin?',
                            text: 'pastikan anda sudah mengisi Nomor, Tanggal Surat dan Kode Anggaran ',
                            icon: 'warning',
                            buttons: true,
                            dangerMode: true,
                        })
                        .then((willDelete) => {
                            if (willDelete) {
                                if (no_surat == '' || tgl_surat == '' || kode_anggaran == '') {
                                    swal('Nomor, Tanggal Surat atau Kode Anggaran Tidak Valid / Tidak boleh kosong', {
                                        icon: 'error',
                                    });
                                    return;
                                }

                                $.ajax({
                                    url: '{{ route('honor.storeNomor') }}',
                                    type: 'GET',
                                    data: {
                                        no_surat: no_surat,
                                        tgl_surat: tgl_surat,
                                        kode_anggaran: kode_anggaran,
                                        kegiatan_id: kegiatan,
                                    },
                                    success: function(response) {
                                        console.log(kegiatan, jabatan);

                                        if (jabatan != 'panitia') {
                                            jabatan = 'panitia'
                                        }

                                        // Construct the URL dynamically
                                        var printUrl =
                                            '{{ route('honor.cetakExcelPanitia', ['id_kegiatan' => '__KEGIATAN__', 'jabatan' => '__JABATAN__']) }}'
                                            .replace('__KEGIATAN__', encodeURIComponent(
                                                kegiatan))
                                            .replace('__JABATAN__', encodeURIComponent(
                                                'panitia'));

                                        window.location.href = printUrl;
                                    },
                                    error: function(error) {
                                        console.error(error);
                                        alert('Error fetching detail.');
                                    }
                                });
                            }
                        });
                });

                $('#printHonorNarasumber').on('click', function(e) {
                    let no_surat = $('[name="no_surat"]').val();
                    let tgl_surat = $('[name="tgl_surat"]').val();
                    let kode_anggaran = $('[name="kode_anggaran"]').val();

                    e.preventDefault();
                    swal({
                            title: 'Apakah anda sudah yakin?',
                            text: 'pastikan anda sudah mengisi Nomor, Tanggal Surat dan Kode Anggaran ',
                            icon: 'warning',
                            buttons: true,
                            dangerMode: true,
                        })
                        .then((willDelete) => {
                            if (willDelete) {
                                if (no_surat == '' || tgl_surat == '' || kode_anggaran == '') {
                                    swal('Nomor, Tanggal Surat atau Kode Anggaran Tidak Valid / Tidak boleh kosong', {
                                        icon: 'error',
                                    });
                                    return;
                                }

                                $.ajax({
                                    url: '{{ route('honor.storeNomor') }}',
                                    type: 'GET',
                                    data: {
                                        no_surat: no_surat,
                                        tgl_surat: tgl_surat,
                                        kode_anggaran: kode_anggaran,
                                        kegiatan_id: kegiatan,
                                    },
                                    success: function(response) {
                                        console.log(kegiatan, jabatan);

                                        if (jabatan != 'narasumber') {
                                            jabatan = 'narasumber'
                                        }

                                        // Construct the URL dynamically
                                        var printUrl =
                                            '{{ route('honor.cetakExcelNarasumber', ['id_kegiatan' => '__KEGIATAN__', 'jabatan' => '__JABATAN__']) }}'
                                            .replace('__KEGIATAN__', encodeURIComponent(
                                                kegiatan))
                                            .replace('__JABATAN__', encodeURIComponent(
                                                'narasumber'));

                                        window.location.href = printUrl;
                                    },
                                    error: function(error) {
                                        console.error(error);
                                        alert('Error fetching detail.');
                                    }
                                });
                            }
                        });
                });

                $('#printHonorPeserta').on('click', function(e) {
                    let no_surat = $('[name="no_surat"]').val();
                    let tgl_surat = $('[name="tgl_surat"]').val();
                    let kode_anggaran = $('[name="kode_anggaran"]').val();

                    e.preventDefault();
                    swal({
                            title: 'Apakah anda sudah yakin?',
                            text: 'pastikan anda sudah mengisi Nomor, Tanggal Surat dan Kode Anggaran ',
                            icon: 'warning',
                            buttons: true,
                            dangerMode: true,
                        })
                        .then((willDelete) => {
                            if (willDelete) {
                                if (no_surat == '' || tgl_surat == '' || kode_anggaran == '') {
                                    swal('Nomor, Tanggal Surat atau Kode Anggaran Tidak Valid / Tidak boleh kosong', {
                                        icon: 'error',
                                    });
                                    return;
                                }

                                $.ajax({
                                    url: '{{ route('honor.storeNomor') }}',
                                    type: 'GET',
                                    data: {
                                        no_surat: no_surat,
                                        tgl_surat: tgl_surat,
                                        kode_anggaran: kode_anggaran,
                                        kegiatan_id: kegiatan,
                                    },
                                    success: function(response) {
                                        console.log(kegiatan, jabatan);

                                        if (jabatan != 'peserta') {
                                            jabatan = 'peserta'
                                        }

                                        // Construct the URL dynamically
                                        var printUrl =
                                            '{{ route('honor.cetakExcelPeserta', ['id_kegiatan' => '__KEGIATAN__', 'jabatan' => '__JABATAN__']) }}'
                                            .replace('__KEGIATAN__', encodeURIComponent(
                                                kegiatan))
                                            .replace('__JABATAN__', encodeURIComponent(
                                                'peserta'));

                                        window.location.href = printUrl;
                                    },
                                    error: function(error) {
                                        console.error(error);
                                        alert('Error fetching detail.');
                                    }
                                });
                            }
                        });
                });



                function updatePrintLink(kegiatan, jabatan) {
                    // var printLinkNarasumber = '{{ route('honor.cetakExcelNarasumber', [':kegiatan', ':jabatan']) }}';
                    // printLinkNarasumber = printLinkNarasumber.replace(':kegiatan', encodeURIComponent(kegiatan)).replace(':jabatan', 'narasumber');
                    // $('#printHonorNarasumber').attr('href', printLinkNarasumber);
                }
            });
        </script>
    @endpush
@endsection
