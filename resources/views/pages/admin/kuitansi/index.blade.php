@extends('layouts.app', ['title' => 'Data Kuitansi'])

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
                <h1>Data Kuitansi BBGTK</h1>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">

                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <a href="{{ route('kuitansi.create') }}" class="btn btn-primary text-white ">
                                            + Buat Kuitansi
                                        </a>

                                    </div>
                                    {{-- {{ dd($title) }} --}}
                                    {{-- <div class="col-md-3">
                                        <div class="form-group">
                                            <select name="" class="form-control" id="kegiatanSelect">
                                                <option value="">-- pilih kegiatan --</option>
                                                @foreach ($datas as $v)
                                                    <?php
                                                    setlocale(LC_TIME, 'id_ID.UTF-8');
                                                    $tgl_kegiatan = strftime('%d %B', strtotime($v->tgl_kegiatan));
                                                    $tgl_selesai = strftime('%d %B %Y', strtotime($v->tgl_selesai));
                                                    ?>
                                                    <option value="{{ $v->id }}">{{ $v->nama_kegiatan }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div> --}}

                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <h6>Filter By Kegiatan </h6>

                                        <div class="form-group">
                                            <select name="" class="form-control" id="kegiatanSelect">
                                                <option value="">-- pilih kegiatan --</option>
                                                @foreach ($kegiatan as $v)
                                                    <?php
                                                    setlocale(LC_TIME, 'id_ID.UTF-8');
                                                    
                                                    $tgl_kegiatan = strftime('%d %B', strtotime($v->tgl_kegiatan));
                                                    $tgl_selesai = strftime('%d %B %Y', strtotime($v->tgl_selesai));
                                                    ?>
                                                    <option value="{{ $v->id }}">{{ $v->nama_kegiatan }}
                                                        {{-- ( {{ $tgl_kegiatan }} -
                                                        {{ $tgl_selesai }}
                                                    ) --}}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>


                                    {{-- <div class="col-md-3">

                                        <div class="form-group">
                                            <select name="" class="form-control" id="jabatanKegiatan">
                                                <option value="">-- pilih status keikutsertaan --</option>
                                                <option value="peserta">Peserta</option>
                                                <option value="panitia">Panitia</option>
                                                <option value="narasumber">Narasumber</option>

                                            </select>
                                        </div>
                                    </div> --}}


                                </div>



                                <div id="btnGroup">
                                    <div class="row">
                                        <div class="col-md-9">
                                            <h6>Print semua data dari :</h6>
                                            <a href="#" id="printAllKuitansi" class="btn btn-info"><i
                                                    class="fas fa-print mr-2"></i>Kuitansi </a>

                                            <a href="#" id="printAllRill" class="btn btn-info"><i
                                                    class="fas fa-print mr-2"></i>Pengeluaran Rill Peserta </a>

                                            <a href="#" id="printAllPJ" class="btn btn-info"><i
                                                    class="fas fa-print mr-2"></i>PJ Mutlak Peserta</a>

                                            <a href="#" id="printAllAmplop" class="btn btn-info"><i
                                                    class="fas fa-print mr-2"></i>Amplop Peserta</a>
                                        </div>
                                    </div>
                                    <div class="row mb-3 mt-2">
                                        <div class="col-md-5">
                                            {{-- <h6>Print Permintaan</h6> --}}

                                            {{-- <a target="_blank" href="{{ route('kuitansi.cetakPermintaan') }}"
                                                class="btn btn-info mr-2"><i class="fas fa-print mr-2"></i>Cetak Permintaan
                                                Kuitansi</a> --}}

                                            {{-- <a target="_blank" href="{{ route('kuitansi.cetakLampiran') }}"
                                                class="btn btn-info"><i class="fas fa-print mr-2"></i>Cetak Lampiran </a> --}}

                                            <a href="#" id="printKuitansi" class="btn btn-success"><i
                                                    class="fas fa-print mr-2"></i>Cetak Permintaan
                                                Kuitansi </a>




                                            {{-- <button id="btnPrintRegisPeserta" class="btn btn-primary"><i
                                                    class="fas fa-print mr-2"></i>Registrasi Peserta</button>
                                            <button id="btnPrintPanitia" class="btn btn-info"><i
                                                    class="fas fa-print mr-2"></i>Absensi Panitia</button>
                                            <button id="btnPrintNarsum" class="btn btn-warning"><i
                                                    class="fas fa-print mr-2"></i>Absensi Narasumber</button> --}}
                                        </div>

                                        <div class="col-md-8">
                                            {{-- <h6>Print Permintaan</h6> --}}


                                            {{-- <a href="#" id="printAllKuitansi" class="btn btn-info"><i
                                                    class="fas fa-print mr-2"></i>Cetak Semua
                                                Kuitansi </a>

                                            <a href="#" id="printAllRill" class="btn btn-info"><i
                                                    class="fas fa-print mr-2"></i>Cetak Pengeluaran Rill </a>

                                            <a href="#" id="printAllPJ" class="btn btn-info"><i
                                                    class="fas fa-print mr-2"></i>Cetak PJ Mutlak </a>

                                            <a href="#" id="printAllAmplop" class="btn btn-info"><i
                                                    class="fas fa-print mr-2"></i>Cetak Amplop </a> --}}

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

                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered" id="table_kuitansi">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th class="text-center">No</th>
                                                <th>Nomor Bukti</th>
                                                <th>Nomor MAK</th>
                                                <th>Nama Kegiatan</th>
                                                <th>Nama Peserta</th>
                                                <th>Jabatan Kegiatan</th>
                                                <th>NIP</th>
                                                <th>Jenis Angkutan</th>
                                                <th>Lokasi Asal</th>
                                                <th>Lokasi Tujuan</th>
                                                <th>Total Diterima</th>
                                                <th>Print Surat</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($datas as $i => $data)
                                                <tr data-id="{{ $data->id }}">
                                                    <td>
                                                        <h6>{{ ++$i }}</h6>
                                                    </td>
                                                    <td>{{ $data->no_bukti ?? '' }}</td>
                                                    <td>{{ $data->no_MAK ?? '' }}</td>
                                                    <td>{{ $data->peserta->kegiatan->nama_kegiatan ?? '' }} <p
                                                            class="text-white">{{ $data->peserta->kegiatan->id ?? '' }}</p>
                                                    </td>
                                                    <td>{{ $data->peserta->nama ?? '' }}</td>
                                                    <td>{{ $data->peserta->status_keikutpesertaan ?? '' }}</td>
                                                    <td>{{ $data->peserta->nip ?? '' }}</td>
                                                    <td>{{ $data->jenis_angkutan ?? '' }}</td>
                                                    <td>{{ $data->kabupaten->name ?? '' }}</td>
                                                    <td>{{ $data->lokasi_tujuan ?? '' }}</td>
                                                    <td>Rp {{ number_format($data->total_terima ?? 0, 0, ',', '.') }}</td>

                                                    {{-- <td>Rp {{ number_format($data->biaya_uang_harian ?? 0, 0, ',', '.') }}
                                                    </td>
                                                    <td>{{ $data->durasi_penginapan ?? '' }} Hari</td>
                                                    <td>{{ $data->durasi_uang_harian ?? '' }} Hari</td>
                                                    <td>Rp
                                                        {{ number_format($data->total_biaya_penginapan ?? 0, 0, ',', '.') }}
                                                    </td>
                                                    <td>Rp {{ number_format($data->total_biaya_harian ?? 0, 0, ',', '.') }}
                                                    </td> --}}

                                                    <td>
                                                        <div class="">

                                                            <a target="_blank"
                                                                href="{{ route('kuitansi.cetak', $data->id) }}"
                                                                class="btn btn-info "> <i class="fas fa-print"></i> Kuitansi
                                                            </a>


                                                            <a target="_blank"
                                                                href="{{ route('kuitansi.cetakRill', $data->id) }}"
                                                                class="btn btn-info my-2"> <i class="fas fa-print"></i>
                                                                Pengeluaran Rill </a>
                                                        </div>

                                                        <div class="">
                                                            <a target="_blank"
                                                                href="{{ route('kuitansi.cetakPJmutlak', $data->id) }}"
                                                                class="btn btn-info "> <i class="fas fa-print"></i> PJ
                                                                Mutlak
                                                            </a>

                                                            <a target="_blank"
                                                                href="{{ route('kuitansi.cetakAmplop', $data->id) }}"
                                                                class="btn btn-info my-2"> <i class="fas fa-print"></i>
                                                                Amplop </a>

                                                        </div>
                                                    </td>
                                                    <td>



                                                        <button class="btn btn-primary btn-detail"
                                                            data-id="{{ $data->id }}">
                                                            <i class="fas fa-eye"></i>
                                                        </button>

                                                        <a href="{{ route('kuitansi.edit', $data->id) }}"
                                                            class="btn btn-warning my-2"><i class="fas fa-edit"></i> </a>

                                                        <button onclick="deleteData({{ $data->id }}, 'kuitansi')"
                                                            class="btn btn-danger "><i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                {{-- @if ($data->transportasis->isNotEmpty())
                                                    <tr>
                                                        <td colspan="11">
                                                            <div class="table-responsive">
                                                                <table class="table table-hover table-striped table-bordered table-sm">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Transportasi</th>
                                                                            <th>Asal</th>
                                                                            <th>Tujuan</th>
                                                                            <th>Biaya</th>
                                                                            <th>Keterangan</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach ($data->transportasis as $transportasi)
                                                                            <tr>
                                                                                <td>{{ $transportasi->transportasi ?? '' }}
                                                                                </td>
                                                                                <td>{{ $transportasi->asal_transport ?? '' }}
                                                                                </td>
                                                                                <td>{{ $transportasi->tujuan_transport ?? '' }}
                                                                                </td>
                                                                                <td>Rp
                                                                                    {{ number_format($transportasi->biaya_transport ?? 0, 0, ',', '.') }}
                                                                                </td>
                                                                                <td>{{ $transportasi->keterangan ?? '' }}
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endif --}}
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

    <div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel">Detail Kuitansi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Tempat untuk menampilkan detail kuitansi -->
                    <div id="detailContent">
                        <!-- Konten detail kuitansi akan dimuat di sini -->
                    </div>
                </div>
            </div>
        </div>
    </div>


    @push('scripts')
        <script src="{{ asset('library/datatables/media/js/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('library/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('library/datatables.net-select-bs4/js/select.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('js/page/modules-datatables.js') }}"></script>

        <script type="text/javascript">
            $(document).ready(function() {
                var tableKuitansi = $('#table_kuitansi').DataTable();
                var btnGroup = $('#btnGroup').hide();

                var kegiatan = '';
                var jabatan = '';

                $('#kegiatanSelect').on('change', function(e) {
                    e.preventDefault();
                    kegiatanValue = $(this).val();
                    if (kegiatanValue == '') {
                        btnGroup.hide();
                        return;
                    }
                    console.log(kegiatanValue);

                    tableKuitansi.column(3).search(kegiatanValue).draw();
                    kegiatan = kegiatanValue;
                    btnGroup.show();

                    // var totalFilteredRows = tableKuitansi.rows({
                    //     search: 'applied'
                    // }).count();
                    // console.log("Total number of filtered rows: " + totalFilteredRows);
                });

                $('#jabatanKegiatan').on('change', function(e) {
                    e.preventDefault();
                    jabatan = $(this).val();
                    console.log(jabatan);

                    tableKuitansi.column(5).search(jabatan).draw();
                });

                $('#printKuitansi').on('click', function(e) {
                    e.preventDefault();
                    let no_surat = $('#no_surat').val();
                    let tgl_surat = $('#tgl_surat').val();
                    let kode_anggaran = $('#kode_anggaran').val();

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
                                    url: '{{ route('kuitansi.storeNomor') }}',
                                    type: 'GET',
                                    data: {
                                        no_surat: no_surat,
                                        tgl_surat: tgl_surat,
                                        kode_anggaran: kode_anggaran,
                                        kegiatan_id: kegiatan,
                                    },
                                    success: function(response) {
                                        console.log(kegiatan);

                                        // Construct the URL dynamically
                                        var printUrl =
                                            '{{ route('kuitansi.cetakexcel', ['id_kegiatan' => '__KEGIATAN__']) }}'
                                            .replace('__KEGIATAN__', encodeURIComponent(
                                                kegiatan));

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

                // $('#printAllKuitansi').on('click', function() {
                //     var selectedKegiatan = $('#kegiatanSelect').val();
                //     if (selectedKegiatan) {
                //         window.open("{{ url('kuitansi/print-all') }}?kegiatan_id=" + selectedKegiatan,
                //             '_blank');
                //     } else {
                //         swal("Silakan pilih kegiatan terlebih dahulu.");
                //     }
                // });

                // $('#printAllRill').on('click', function() {

                // });

                // $('#printAllPJ').on('click', function() {

                // });

                // $('#printAllAmplop').on('click', function() {

                // });


            });
        </script>

        <script>
            $(document).ready(function() {
                $('.btn-detail').on('click', function() {
                    var kuitansiId = $(this).data('id');
                    $.ajax({
                        url: '{{ route('kuitansi.show', ['id' => ':kuitansiId']) }}'.replace(
                            ':kuitansiId', kuitansiId
                        ), // Ganti dengan rute yang sesuai untuk mengambil detail kuitansi
                        type: 'GET',
                        success: function(response) {
                            // Memuat konten detail kuitansi ke dalam modal
                            $('#detailContent').html(response);
                            $('#detailModal').modal('show');
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                        }
                    });
                });
            });
        </script>

        <script>
            $(document).ready(function() {
                $('#printAllKuitansi').click(function(e) {
                    e.preventDefault();
                    var kegiatanId = $('#kegiatanSelect').val();
                    var rowIds = [];

                    // Collect the IDs of the rows you want to print
                    $('#table_kuitansi tbody tr').each(function() {
                        var rowId = $(this).data(
                        'id'); // Assuming the rows have data-id attribute with their ID
                        if (rowId) {
                            rowIds.push(rowId);
                        }
                    });

                    if (rowIds.length === 0) {
                        alert("No rows selected.");
                        return;
                    }

                    // Construct the URL with the collected row IDs and kegiatanId
                    var url = kegiatanId ?
                        "{{ route('kuitansi.cetakAll') }}?rows=" + rowIds.join(',') + "&kegiatan_id=" +
                        kegiatanId :
                        "{{ route('kuitansi.cetakAll') }}?rows=" + rowIds.join(',');

                    window.open(url, '_blank');
                });
            });
        </script>

        <script>
            $(document).ready(function() {
                $('#printAllRill').click(function(e) {
                    e.preventDefault();
                    var kegiatanId = $('#kegiatanSelect').val();
                    var rowIds = [];

                    // Collect the IDs of the rows you want to print
                    $('#table_kuitansi tbody tr').each(function() {
                        var rowId = $(this).data(
                        'id'); // Assuming the rows have data-id attribute with their ID
                        if (rowId) {
                            rowIds.push(rowId);
                        }
                    });

                    if (rowIds.length === 0) {
                        alert("No rows selected.");
                        return;
                    }

                    // Construct the URL with the collected row IDs and kegiatanId
                    var url = kegiatanId ?
                        "{{ route('kuitansi.cetakRillAll') }}?rows=" + rowIds.join(',') + "&kegiatan_id=" +
                        kegiatanId :
                        "{{ route('kuitansi.cetakRillAll') }}?rows=" + rowIds.join(',');

                    window.open(url, '_blank');
                });
            });
        </script>

        <script>
            $(document).ready(function() {
                $('#printAllPJ').click(function(e) {
                    e.preventDefault();
                    var kegiatanId = $('#kegiatanSelect').val();
                    var rowIds = [];

                    // Collect the IDs of the rows you want to print
                    $('#table_kuitansi tbody tr').each(function() {
                        var rowId = $(this).data(
                        'id'); // Assuming the rows have data-id attribute with their ID
                        if (rowId) {
                            rowIds.push(rowId);
                        }
                    });

                    if (rowIds.length === 0) {
                        alert("No rows selected.");
                        return;
                    }

                    // Construct the URL with the collected row IDs and kegiatanId
                    var url = kegiatanId ?
                        "{{ route('kuitansi.cetakPJmutlakAll') }}?rows=" + rowIds.join(',') + "&kegiatan_id=" +
                        kegiatanId :
                        "{{ route('kuitansi.cetakPJmutlakAll') }}?rows=" + rowIds.join(',');

                    window.open(url, '_blank');
                });
            });
        </script>

        <script>
            $(document).ready(function() {
                $('#printAllAmplop').click(function(e) {
                    e.preventDefault();
                    var kegiatanId = $('#kegiatanSelect').val();
                    var rowIds = [];

                    // Collect the IDs of the rows you want to print
                    $('#table_kuitansi tbody tr').each(function() {
                        var rowId = $(this).data(
                        'id'); // Assuming the rows have data-id attribute with their ID
                        if (rowId) {
                            rowIds.push(rowId);
                        }
                    });

                    if (rowIds.length === 0) {
                        alert("No rows selected.");
                        return;
                    }

                    // Construct the URL with the collected row IDs and kegiatanId
                    var url = kegiatanId ?
                        "{{ route('kuitansi.cetakAmplopAll') }}?rows=" + rowIds.join(',') + "&kegiatan_id=" +
                        kegiatanId :
                        "{{ route('kuitansi.cetakAmplopAll') }}?rows=" + rowIds.join(',');

                    window.open(url, '_blank');
                });
            });
        </script>
    @endpush
@endsection
