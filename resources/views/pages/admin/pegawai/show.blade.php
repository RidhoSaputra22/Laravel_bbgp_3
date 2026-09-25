@extends('layouts.app', ['title' => 'Data Internal'])

@section('content')
    @push('styles')
        <link rel="stylesheet" href="{{ asset('library/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('library/datatables.net-select-bs4/css/select.bootstrap4.min.css') }}">
    @endpush

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Data Internal BBGTK</h1>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <!-- Navigation Buttons -->
                                <div class="row">
                                    <div class="col">
                                    </div>
                                </div>

                                <!-- Filter Section -->

                                <!-- Filter Data Internal -->
                                <h5>Data Internal {{ $datas['dataPegawai']->nama_lengkap }}</h5>
                                <div class="row mt-3">
                                    <div class="col-m ml-4 mb-4">
                                        <label>Rekapan Data</label>
                                        <div class="d-flex">

                                            <button id="rekapan" class="btn btn-info mr-4">Data Pendamping
                                                Lokakarya</button>
                                            <button id="rekapanPenugasan" class="btn btn-info">Data Penugasan</button>
                                        </div>
                                    </div>
                                    <div class="col-md-2">

                                    </div>
                                </div>

                                <!-- Tables Section -->
                                <div class="table-responsive">


                                    <!-- Table BBGTK -->
                                    <table class="table table-striped mb-5" id="">
                                        <thead>
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th style="width: 20%">NIP</th>
                                                <th style="width: 20%">Nama Lengkap</th>
                                                @if ($datas['dataPegawai']->jenis_pegawai == 'BBGP')
                                                    <th>Golongan</th>
                                                @endif
                                                <th>Jabatan</th>
                                                <th>Penugasan</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr data-type="bbgp">
                                                <td>{{ 1 }}</td>
                                                <td>{{ $datas['dataPegawai']->nip }}</td>
                                                <td>{{ $datas['dataPegawai']->nama_lengkap }}</td>
                                                @if ($datas['dataPegawai']->jenis_pegawai == 'BBGP')
                                                    <td>{{ $datas['dataPegawai']->golongan }}</td>
                                                @endif
                                                <td>{{ $datas['dataPegawai']->jabatan }}</td>
                                                <td>
                                                    <a href="{{ route('internal.create.lokakarya', $datas['dataPegawai']->id) }}"
                                                        class="btn btn-primary mb-2" onclick=""> Pendamping Loka
                                                        Karya</a>
                                                </td>
                                                <td>

                                                    <a href="{{ route('pegawai.edit', $datas['dataPegawai']->id) }} "
                                                        class="btn btn-warning my-2"><i class="fas fa-edit"></i></a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>

                                </div>

                                <div id="loka-table">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-internal-loka" id="table-lokakarya">
                                            <h5 class="mt-5">Data Pendamping Lokakarya</h5>
                                            <thead>
                                                <tr style="width: 2000px; font-size: 14px;">
                                                    <th class="text-center">#</th>
                                                    <th style="width:20%">Nama Petugas </th>
                                                    <th>Kota</th>
                                                    <th>Hotel</th>
                                                    <th>Kegiatan</th>
                                                    <th>Tanggal Kegiatan</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($datas['jadwalLokakarya'] as $i => $data)
                                                    <tr style="font-size: 14px;" data-type="penugasan-pegawai">
                                                        <td>{{ ++$i }}</td>
                                                        <td>
                                                            {{ $data->nama ?? '' }}
                                                        </td>
                                                        <td>{{ $data->kota ?? '' }}</td>
                                                        <td>{{ $data->hotel ?? '' }}</td>
                                                        <td>{{ $data->kegiatan ?? '' }}</td>
                                                        <?php
                                                        setlocale(LC_TIME, 'IND');
                                                        
                                                        $tgl = strftime('%d %B %Y', strtotime($data->tgl_kegiatan));
                                                        ?>
                                                        <td>{{ $tgl ?? '' }}</td>

                                                        <td>
                                                            <a target="_blank"
                                                                href="{{ asset('upload/bukti_bill/' . $data->bukti_bill) }}"
                                                                class="btn btn-primary">
                                                                <i class="fas fa-print"></i>
                                                            </a>

                                                            <button onclick="showDetail({{ $data->id }})"
                                                                class="btn btn-info mt-1"><i class="fas fa-info"></i>
                                                            </button>

                                                            <a href="{{ route('internal.edit.lokakarya', $data->id) }} "
                                                                class="btn btn-warning my-1"><i class="fas fa-edit"></i>
                                                            </a>


                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                </div>

                                <div id="penugasan-table">
                                    @if ($pegawai->jenis_pegawai == 'PPNPN')
                                        <div class="table-responsive">
                                            <table class="table table-striped table-internal " id="table-penugasan">
                                                <h5 class="mt-5">Data Penugasan PPNPN</h5>
                                                <thead>
                                                    <tr style="width: 2000px; font-size: 14px;">
                                                        <th class="text-center">#</th>
                                                        <th style="width:20%">Nama</th>
                                                        <th>Jabatan</th>
                                                        <th>Kegiatan</th>
                                                        <th>Tempat Kegiatan</th>
                                                        <th>Tanggal Kegiatan</th>
                                                        <th>Jam Kegiatan</th>
                                                        <th>Deskripsi Kegiatan</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($datas['dataPenugasanPpnpn'] as $i => $data)
                                                        <tr style="font-size: 14px;">
                                                            <td>{{ ++$i }}</td>
                                                            <td>
                                                                {{ $data->nama ?? '' }}
                                                            </td>
                                                            <td>{{ $data->jabatan ?? '' }}</td>
                                                            <td>{{ $data->kegiatan ?? '' }}</td>
                                                            <td>{{ $data->tempat ?? '' }}</td>
                                                            <?php
                                                            setlocale(LC_TIME, 'IND');
                                                            
                                                            $tgl = strftime('%d %B', strtotime($data->tgl_kegiatan));
                                                            $tgl_selesai = strftime('%d %B %Y', strtotime($data->tgl_selesai_kegiatan));
                                                            ?>
                                                            <td>{{ $tgl ?? '' }} - {{ $tgl_selesai }}</td>
                                                            <td>{{ $data->jam_mulai }} - {{ $data->jam_selesai }} WITA
                                                            </td>
                                                            <td>{!! safe_rich_text($data->deskripsi) !!}
                                                            </td>



                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="table-responsive">
                                            <table class="table table-striped table-internal " id="table-penugasan">
                                                <h5 class="mt-5">Data Penugasan Pegawai</h5>
                                                <thead>
                                                    <tr style="width: 2000px; font-size: 14px;">
                                                        <th class="text-center">#</th>
                                                        <th style="width:20%">Nama Lengkap </th>
                                                        <th>Kegiatan</th>
                                                        <th>Tempat Kegiatan</th>
                                                        <th>Tanggal Kegiatan</th>
                                                        <th>Jam Kegiatan</th>
                                                        <th>Keterangan Kegiatan</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($datas['dataPenugasanPegawai'] as $i => $data)
                                                        <tr style="font-size: 14px;" data-type="penugasan-pegawai">
                                                            <td>{{ ++$i }}</td>
                                                            <td>
                                                                {{ $data->nama ?? '' }}
                                                            </td>
                                                            <td>{{ $data->kegiatan ?? '' }}</td>
                                                            <td>{{ $data->tempat ?? '' }}</td>
                                                            <?php
                                                            setlocale(LC_TIME, 'IND');
                                                            
                                                            $tgl = strftime('%d %B', strtotime($data->tgl_kegiatan));
                                                            $tgl_selesai = strftime('%d %B %Y', strtotime($data->tgl_selesai_kegiatan));
                                                            ?>
                                                            <td>{{ $data->tgl_kegiatan }} -
                                                                {{ $data->tgl_selesai_kegiatan }}</td>
                                                            <td>{{ $data->jam_mulai }} - {{ $data->jam_selesai }} WITA
                                                            </td>
                                                            <td>{!! safe_rich_text($data->deskripsi) !!}</td>


                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif

                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Modal for Peserta Detail -->
    <div style="z-index: 999999;" class="modal fade" id="pesertaDetailModal" tabindex="-1" role="dialog"
        aria-labelledby="pesertaDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pesertaDetailModalLabel">Detail Loka Karya</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="pesertaDetailContent">
                    <!-- Detail content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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

                var tableLokakarya = $('#table-lokakarya').DataTable();
                var tablePenugasan = $('#table-penugasan').DataTable();




                // Initially hide both tables
                $('#loka-table').hide();
                $('#penugasan-table').hide();

                // Filter tables based on dropdown selection
                $('#rekapan').on('click', function(event) {
                    event.preventDefault();
                    $('#loka-table').show();
                    $('#penugasan-table').hide();
                });

                $('#rekapanPenugasan').on('click', function(event) {
                    event.preventDefault();
                    $('#loka-table').hide();
                    $('#penugasan-table').show();
                });

                // Filter by Nama
                $('#namaFilter').on('keyup', function() {
                    table1.column(2).search(this.value).draw();
                    table2.column(2).search(this.value).draw();
                    table3.column(1).search(this.value).draw();
                });

                // Filter by Penugasan
                $('#penugasanFilter').on('keyup', function() {
                    table1.column(5).search(this.value).draw();
                    table2.column(5).search(this.value).draw();
                });



                // Reset button functionality
                $('#resetBtn').on('click', function() {
                    $('#rekapan').val('');
                    $('#namaFilter').val('');
                    $('#penugasanFilter').val('');
                    $('#pendampingFilter').val('');
                    $('.table-internal').hide();
                    table1.search('').columns().search('').draw();
                    table2.search('').columns().search('').draw();
                    table3.search('').columns().search('').draw();
                });




            });

            function showDetail(id) {
                $.ajax({
                    url: '{{ route('pegawai.show.lokakarya') }}',
                    type: 'GET',
                    data: {
                        id: id
                    },
                    success: function(response) {

                        let kelengkapanPesertaTransport = '';
                        let kelengkapanPesertaBiodata = ''

                        kelengkapanPesertaTransport = response.statusKeikutpesertaan == 'peserta' ? `
                        <p>
                            <strong>Kelengkapan Peserta Transport:</strong> ${response.kelengkapan_peserta_transport ?? ''}
                        </p>` : '';

                        kelengkapanPesertaBiodata = response.statusKeikutpesertaan == 'peserta' ? `
                        <p>
                            <strong>Kelengkapan Peserta Biodata:</strong> ${response.kelengkapan_peserta_biodata ?? ''}
                        </p>` : '';

                        let formattedDate = '';
                        const date = new Date(response.tgl_kegiatan);
                        const day = String(date.getDate()).padStart(2, '0');
                        const month = String(date.getMonth() + 1).padStart(2, '0'); // getMonth() returns 0-11
                        const year = date.getFullYear();
                        formattedDate = `${day}-${month}-${year}`;

                        $('#pesertaDetailContent').html(`
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Nama:</strong> ${response.nama ?? ''}</p>
                                <p><strong>NIK:</strong> ${response.nik ?? ''}</p>
                                <p><strong>NIP:</strong> ${response.nip ?? ''}</p>
                                <p>
                                    <strong>Hotel:</strong> ${response.hotel ?? ''} 
                                </p>
                                <p>
                                    <strong>Kabupaten/Kota:</strong> ${response.kota ?? ''} 
                                </p>
                                <p>
                                    <strong>Tanggal Kegiatan:</strong> ${formattedDate ?? ''}
                                </p>
                            </div>    
                            <div class="col-md-6">
                                <p><strong>Transport Pergi:</strong>Rp. ${ formatRupiah( response.transport_pergi) ?? ''}</p>
                                <p><strong>Transport Pulang:</strong>Rp. ${formatRupiah( response.transport_pulang) ?? ''}</p>
                                <p><strong>Hari 1:</strong>Rp. ${formatRupiah( response.hari_1) ?? ''}</p>
                                <p><strong>Hari 2:</strong>Rp. ${formatRupiah( response.hari_2) ?? ''}</p>
                                <p><strong>Hari 3:</strong>Rp. ${formatRupiah( response.hari_3) ?? ''}</p>
                            </div>    
                        </div>
                    `);
                        $('#pesertaDetailModal').modal('show');
                    },
                    error: function(error) {
                        console.error(error);
                        alert('Error fetching detail.');
                    }
                });

                function formatRupiah(number) {
                    var reverse = number.toString().split('').reverse().join('');
                    var ribuan = reverse.match(/\d{1,3}/g);
                    return ribuan.join(',').split('').reverse().join('');
                }
            }
        </script>
    @endpush
@endsection
