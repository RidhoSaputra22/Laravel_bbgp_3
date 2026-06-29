@extends('layouts.landing.app')
@section('content')
    @push('styles')
    @endpush

    <div id="banner-area" class="banner-area"
        style="background-image:url({{ asset('landing/images/banner/bannerInternal.png') }})">
        <div class="banner-text">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="banner-heading">
                            <h1 class="banner-title">Internal BBGTK </h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb justify-content-center">
                                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Internal</li>
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
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Navigation Buttons -->
                        <div class="row">
                            <div class="col">

                            </div>
                        </div>

                        <!-- Filter Section -->
                        <h5>Pencarian Data Internal BBGTK</h5>
                        <div class="row mb-2">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <input name="nama" id="namaFilter" type="text" placeholder="Masukkan nama anda"
                                        class="form-control">
                                </div>
                            </div>
                        </div>

                        <!-- Filter Data Internal -->
                        <h5>Filter Data Internal</h5>
                        <div class="row">
                            <div class="col-md-4 mb-4">
                                <label>Rekapan Data</label>
                                <select required name="rekapan" class="form-control" id="rekapan">
                                    <option value="">-- Filter By Rekapan Data --</option>
                                    <option value="Penugasan Pegawai">Penugasan Pegawai</option>
                                    <option value="Penugasan PPNPN">Penugasan PPNPN</option>
                                    <option value="Pendamping Lokakarya">Pendamping Lokakarya</option>
                                </select>
                            </div>
                            <div class="col-md-4" id="penugasanForm">
                                <div class="form-group">
                                    <label>Pencarian Penugasan Kegiatan</label>
                                    <input placeholder="ketikkan pencarian kegiatan penugasan" id="penugasanFilter"
                                        type="text" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4 mb-4" id="pendampingForm">
                                <label>Pencarian Nama Pendamping</label>
                                <input placeholder="ketikkan pencarian nama pendamping" id="pendampingFilter" type="text"
                                    class="form-control">
                            </div>
                        </div>

                        <!-- Tables Section -->
                        <div class="table-responsive">

                        </div>

                        <div class="table-responsive  table-internal" id="table-internal-penugasan">
                            <table class="table table-striped text-center" id="table-penugasan">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th class="text-center">NIP</th>
                                        <th class="text-center">Nama</th>
                                        <th>Jabatan</th>
                                        {{-- <th>Jenis Penugasan</th> --}}
                                        <th>Kegiatan</th>
                                        <th>Tempat</th>
                                        <th>Tanggal Kegiatan</th>
                                        <th>Action</th>
                                        {{-- <th>Verifkasi</th>
                                    <th>Action</th> --}}
                                    </tr>
                                </thead>
                                <tbody id="penugasanPegawai">
                                    @foreach ($datas['dataPenugasanPegawai'] as $i => $data)
                                        <tr data-type="penugasan-pegawai">
                                            <td>{{ ++$i }}</td>
                                            <td>{{ $data->nip ?? ' - ' }}</td>
                                            <td class="text-left">{{ $data->nama ?? '' }}</td>
                                            <td class="text-left">
                                                {{ $data->jabatan ?? '' }} <br>
                                                {{ ' (Golongan : ' . $data->golongan . ')' }}
                                            </td>
                                            {{-- <td class="text-left">{{ $data->jenis ?? '' }}</td> --}}
                                            <td class="text-left">{{ $data->kegiatan ?? '' }}</td>
                                            <td class="text-left">{{ $data->tempat ?? '' }}</td>
                                            <?php
                                            setlocale(LC_TIME, 'IND');
                                            
                                            $tgl_kegiatan = strftime('%d %B %Y', strtotime($data->tgl_kegiatan));
                                            ?>
                                            <td>{{ $tgl_kegiatan ?? '' }}</td>
                                            {{-- <td>
                                            @if ($data->is_verif == 'sudah')
                                                <span class="badge badge-success">Sudah Verifikasi</span>
                                            @else
                                                <span class="badge badge-danger">Belum Verifikasi</span>
                                            @endif
                                        </td> --}}
                                            <td>

                                                <button class="btn btn-sm btn-primary"
                                                    onclick="showDetail({{ $data->id }})">
                                                    <i class="fas fa-info"></i>
                                                </button>

                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="table-responsive  table-internal" id="table-internal-ppnpn">
                            <table class="table table-striped" id="table-ppnpn">
                                <thead>
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th class="text-center">NIP</th>
                                        <th class="text-center">Nama</th>
                                        {{-- <th>Jenis Penugasan</th> --}}
                                        <th>Jabatan</th>
                                        <th>Kegiatan</th>
                                        <th>Tempat</th>
                                        <th>Tanggal Kegiatan</th>
                                        {{-- <th>Verifkasi</th>
                                    <th>Action</th> --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($datas['dataPenugasanPpnpn'] as $i => $data)
                                        <tr data-type="penugasan-ppnpn">
                                            <td>{{ ++$i }}</td>
                                            <td class="text-center">{{ $data->nip ?? ' - ' }}</td>
                                            <td class="text-center">{{ $data->nama ?? '' }}</td>
                                            {{-- <td>{{ $data->jenis ?? '' }}</td> --}}
                                            <td>{{ $data->jabatan ?? '' }} <br>
                                                {{ '(Golongan : ' . $data->golongan . ')' }}</td>
                                            <td>{{ $data->kegiatan ?? '' }}</td>
                                            <td>{{ $data->tempat ?? '' }}</td>
                                            <?php
                                            setlocale(LC_TIME, 'IND');
                                            
                                            $tgl_kegiatan = strftime('%d %B %Y', strtotime($data->tgl_kegiatan));
                                            ?>
                                            <td class="text-left">{{ $tgl_kegiatan ?? '' }}</td>
                                            {{--
                                        </td>
                                        <td>
                                            <a href="#"
                                                    onclick="verifikasi({{ $data->id }}, 'internal', '{{ $data->is_verif }}')"
                                                    class="btn btn-primary mb-2">Verifikasi</a>
                                            <a href="{{ route('internal.edit', $data->id) }} " class="btn btn-warning my-2"><i class="fas fa-edit"></i></a>
                                            <button onclick="deleteData({{ $data->id }}, 'internal')" class="btn btn-danger">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td> --}}
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="table-responsive  table-internal" id="table-internal-lokakarya">
                            <table class="table table-striped" id="table-lokakarya">
                                <thead>
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th class="text-center">Nama</th>
                                        <th>Kabupaten/Kota</th>
                                        <th>Hotel</th>
                                        {{-- <th>Transport Pulang</th>
                                        <th>Transport Pergi</th>
                                        <th>Hari 1</th>
                                        <th>Hari 2</th>
                                        <th>Hari 3</th> --}}
                                        <th>Action</th>
                                        {{-- <th>Verifkasi</th>

                                    <th>Action</th> --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($dataPendamping as $i => $data)
                                        <tr data-type="pendamping">
                                            <td>{{ ++$i }}</td>
                                            <td class="text-center">{{ $data->nama ?? '' }}</td>
                                            <td>{{ $data->kota ?? '' }}</td>
                                            <td>{{ $data->hotel ?? '' }}</td>
                                            {{-- <td>Rp. {{ $data->transport_pulang ?? '' }}</td>
                                            <td>Rp. {{ $data->transport_pergi ?? '' }}</td>
                                            <td>Rp. {{ $data->hari_1 ?? '' }}</td>
                                            <td>Rp. {{ $data->hari_2 ?? '' }}</td>
                                            <td>Rp. {{ $data->hari_3 ?? '' }}</td> --}}


                                            <td>
                                                <button class="btn btn-sm btn-primary"
                                                    onclick="showDetailLoka({{ $data->id }})">
                                                    <i class="fas fa-info"></i>
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

    <!-- Modal for Peserta Detail -->
    <div style="z-index: 999999;" class="modal fade" id="pegawaiDetail" tabindex="-1" role="dialog"
        aria-labelledby="pegawaiDetailLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pegawaiDetailLabel">Detail Pegawai</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="pegawaiDetailContent">
                    <!-- Detail content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Peserta Detail -->
    <div style="z-index: 999999;" class="modal fade" id="lokaDetail" tabindex="-1" role="dialog"
        aria-labelledby="lokaDetailLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="lokaDetailLabel">Detail Loka Karya</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="lokaDetailContent">
                    <!-- Detail content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('library/gmaps/gmaps.min.js') }}"></script>
        <script src="{{ asset('js/page/gmaps-simple.js') }}"></script>

        <script>
            $(document).ready(function() {
                // Initialize DataTables
                var tablePenugasan = $('#table-penugasan').DataTable({
                    paging: true,
                    searching: true,
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/2.1.0/i18n/id.json',
                    },
                    bAutoWidth: false,
                    aoColumns: [{
                            sWidth: '2%'
                        },
                        {
                            sWidth: '15%'
                        },
                        {
                            sWidth: '30%'
                        },
                        {
                            sWidth: '30%'
                        },
                        {
                            sWidth: '15%'
                        },
                        {
                            sWidth: '20%'
                        },
                        {
                            sWidth: '30%'
                        },
                        {
                            sWidth: '10%'
                        }
                    ]
                    // autoWidth: true
                    // columnDefs: [{ width: '60%', targets: 2 }]
                    // Add more DataTable options as needed
                });

                var tablePpnpn = $('#table-ppnpn').DataTable({
                    paging: true,
                    searching: true,
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/2.1.0/i18n/id.json',
                    },
                    bAutoWidth: false,
                    aoColumns: [{
                            sWidth: '2%'
                        },
                        {
                            sWidth: '15%'
                        },
                        {
                            sWidth: '30%'
                        },
                        {
                            sWidth: '15%'
                        },
                        {
                            sWidth: '15%'
                        },
                        {
                            sWidth: '10%'
                        },
                        {
                            sWidth: '30%'
                        },
                    ]
                    // Add more DataTable options as needed
                });

                var tableLokakarya = $('#table-lokakarya').DataTable({
                    paging: true,
                    searching: true,
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/2.1.0/i18n/id.json',
                    },
                    bAutoWidth: false,
                    aoColumns: [{
                            sWidth: '1%'
                        },
                        {
                            sWidth: '20%'
                        },
                        {
                            sWidth: '10%'
                        },
                        {
                            sWidth: '10%'
                        },
                        {
                            sWidth: '5%'
                        },

                    ]
                    // Add more DataTable options as needed
                });

                // Initially hide all tables
                $('.table-internal').hide();

                // Handle change event on #rekapan dropdown
                $('#rekapan').on('change', function() {
                    var selectedValue = $(this).val();

                    // Hide all tables initially
                    $('.table-internal').hide();

                    // Show the selected table based on dropdown value
                    if (selectedValue === 'Penugasan Pegawai') {
                        $('#table-internal-penugasan').show();
                        $('#pendampingForm').hide();
                        $('#penugasanForm').show();
                    } else if (selectedValue === 'Penugasan PPNPN') {
                        $('#table-internal-ppnpn').show();
                        $('#pendampingForm').hide();
                        $('#penugasanForm').show();
                    } else if (selectedValue === 'Pendamping Lokakarya') {
                        $('#table-internal-lokakarya').show();
                        $('#pendampingForm').show();
                        $('#penugasanForm').hide();
                    }
                });

                // Filter by Nama
                $('#namaFilter').on('keyup', function() {
                    tablePenugasan.column(2).search(this.value).draw();
                    tablePpnpn.column(2).search(this.value).draw();
                    tableLokakarya.column(1).search(this.value).draw();
                });

                // Filter by Penugasan
                $('#penugasanFilter').on('keyup', function() {
                    tablePenugasan.column(5).search(this.value).draw();
                    tablePpnpn.column(2).search(this.value).draw();
                });

                // Filter by Pendamping
                $('#pendampingFilter').on('keyup', function() {
                    tableLokakarya.column(1).search(this.value).draw();
                });

                // Reset button functionality
                $('#resetBtn').on('click', function() {
                    $('#rekapan').val('');
                    $('#namaFilter').val('');
                    $('#penugasanFilter').val('');
                    $('#pendampingFilter').val('');
                    $('.table-internal').hide();
                    tablePenugasan.search('').columns().search('').draw();
                    tablePpnpn.search('').columns().search('').draw();
                    tableLokakarya.search('').columns().search('').draw();
                });

                // ajax table penugasan
                // $.ajax({
                //     url: '{{ route('user.pegawai.all') }}',
                //     type: 'GET',
                //     success: function(response) {


                //         $('#penugasanPegawai').html(`

        //     `);
                //     },
                //     error: function(error) {
                //         console.error(error);
                //         alert('Error fetching detail.');
                //     }
                // });

            });

            function showDetail(pegawaiId) {
                console.log('get detail')
                $.ajax({
                    url: '{{ route('user.pegawai.detail') }}',
                    type: 'GET',
                    data: {
                        id: pegawaiId
                    },
                    success: function(response) {
                        console.log(response)
                        let tgl_kegiatan = '';
                        let tgl_selesai = '';
                        const dateKegiatan = new Date(response.tgl_kegiatan);
                        const dayKegiatan = String(dateKegiatan.getDate()).padStart(2, '0');
                        const monthKegiatan = String(dateKegiatan.getMonth() + 1).padStart(2,
                            '0'); // getMonth() returns 0-11
                        const yearKegiatan = dateKegiatan.getFullYear();
                        tgl_kegiatan = `${dayKegiatan}-${monthKegiatan}-${yearKegiatan}`;

                        const dateSelesai = new Date(response.tgl_selesai_kegiatan);
                        const daySelesai = String(dateSelesai.getDate()).padStart(2, '0');
                        const monthSelesai = String(dateSelesai.getMonth() + 1).padStart(2,
                            '0'); // getMonth() returns 0-11
                        const yearSelesai = dateSelesai.getFullYear();

                        tgl_selesai = `${daySelesai}-${monthSelesai}-${yearSelesai}`;

                        $('#pegawaiDetailContent').html(`
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Nama:</strong> ${response.nama ?? ''}</p>
                                    <p><strong>NIK:</strong> ${response.nik ?? ''}</p>
                                    <p><strong>NIP:</strong> ${response.nip ?? ''}</p>
                                    <p>
                                        <strong>Kabupaten/Kota:</strong> ${response.kota ?? ''}
                                    </p>
                                
                                    
                                </div>    
                                <div class="col-md-6">
                                    <p><strong>Kegiatan:</strong> ${response.kegiatan ?? ''}</p>
                                    <p><strong>Lokasi Kegiatan:</strong> ${response.tempat ?? ''}</p>
                                    <p><strong>Tanggal Kegiatan:</strong> ${tgl_kegiatan ?? ''} - ${tgl_selesai}</p>
                                    <p><strong>Pukul:</strong> ${response.jam_mulai ?? ''} - ${response.jam_selesai} WITA</p>
                                </div>    
                            </div>
                        `);
                        $('#pegawaiDetail').modal('show');
                    },
                    error: function(error) {
                        console.error(error);
                        alert('Error fetching detail.');
                    }
                });
            }

            function showDetailLoka(pegawaiId) {
                console.log('get detail')
                $.ajax({
                    url: '{{ route('user.pegawai.detail.loka') }}',
                    type: 'GET',
                    data: {
                        id: pegawaiId
                    },
                    success: function(response) {
                        console.log(response)
                        let tgl_kegiatan = '';
                        let tgl_selesai = '';
                        const dateKegiatan = new Date(response.tgl_kegiatan);
                        const dayKegiatan = String(dateKegiatan.getDate()).padStart(2, '0');
                        const monthKegiatan = String(dateKegiatan.getMonth() + 1).padStart(2,
                            '0'); // getMonth() returns 0-11
                        const yearKegiatan = dateKegiatan.getFullYear();
                        tgl_kegiatan = `${dayKegiatan}-${monthKegiatan}-${yearKegiatan}`;

                        const dateSelesai = new Date(response.tgl_selesai_kegiatan);
                        const daySelesai = String(dateSelesai.getDate()).padStart(2, '0');
                        const monthSelesai = String(dateSelesai.getMonth() + 1).padStart(2,
                            '0'); // getMonth() returns 0-11
                        const yearSelesai = dateSelesai.getFullYear();

                        tgl_selesai = `${daySelesai}-${monthSelesai}-${yearSelesai}`;

                        const transportPergi = formatRupiah(response.transport_pergi, 'Rp');
                        const transportPulang = formatRupiah(response.transport_pulang, 'Rp');
                        const hari1 = formatRupiah(response.hari_1, 'Rp');
                        const hari2 = formatRupiah(response.hari_2, 'Rp');
                        const hari3 = formatRupiah(response.hari_3, 'Rp');

                        $('#lokaDetailContent').html(`
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nama:</strong> ${response.nama ?? ''}</p>
                            <p><strong>Kabupaten/Kota:</strong> ${response.kota ?? ''}</p>
                            <p>
                                <strong>Hotel:</strong> ${response.hotel ?? ''}
                            </p>
                            
                        </div>    
                        <div class="col-md-6">
                            <p><strong>Transport Pergi:</strong> ${transportPulang ?? ''}</p>
                            <p><strong>Trasnport Pulang:</strong> ${transportPergi ?? ''}</p>
                            <p><strong>Hari ke 1:</strong> ${hari1}</p>
                            <p><strong>Hari ke 2:</strong> ${hari2}</p>
                            <p><strong>Hari ke 3:</strong> ${hari3}</p>
                        </div>    
                    </div>
                `);
                        $('#lokaDetail').modal('show');
                    },
                    error: function(error) {
                        console.error(error);
                        alert('Error fetching detail.');
                    }
                });
            }

            function formatRupiah(angka, prefix) {
                var numberString = angka.toString().replace(/[^,\d]/g, ''),
                    split = numberString.split(','),
                    sisa = split[0].length % 3,
                    rupiah = split[0].substr(0, sisa),
                    ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                // Tambahkan titik jika yang diinput sudah menjadi angka ribuan
                if (ribuan) {
                    separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }

                rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
                return prefix == undefined ? rupiah : (rupiah ? 'Rp ' + rupiah : '');
            }
        </script>
    @endpush
@endsection
