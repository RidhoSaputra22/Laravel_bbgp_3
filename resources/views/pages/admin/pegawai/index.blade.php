@extends('layouts.app', ['title' => 'Data Pegawai BBGTK'])

@section('content')
    @push('styles')
        <link rel="stylesheet" href="{{ asset('library/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('library/datatables.net-select-bs4/css/select.bootstrap4.min.css') }}">
    @endpush

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Data Pegawai BBGTK</h1>
            </div>


            <div class="section-body">


                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <a href="{{ route('pegawai.create') }}" class="btn btn-primary my-4">
                                    <i class="fas fa-plus"></i>
                                    Tambah Data Pegawai BBGTK
                                </a>
                                <div class="table-responsive">
                                    <table class="table table-striped" id="table-temp1">
                                        <thead>
                                            <tr>
                                                <th class="text-center">
                                                    #
                                                </th>
                                                <th style="width: 400px">Nama Lengkap</th>
                                                {{-- <th>Email</th> --}}
                                                <th>Golongan</th>
                                                <th>Nomor KTP</th>
                                                <th>NIP</th>
                                                {{-- <th>Tempat, Tanggal Lahir</th> --}}
                                                {{-- <th>Alamat Rumah</th> --}}
                                                {{-- <th>Jenis Kelamin</th> --}}
                                                <th>Jabatan</th>
                                                {{-- <th>Agama</th> --}}
                                                <th>Pegawai</th>
                                                {{-- <th>Satuan Pendidikan</th> --}}
                                                {{-- <th>Nomor Aktif</th> --}}
                                                {{-- <th>No Rekening</th> --}}
                                                <th>Status Verifikasi</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($datas as $i => $data)
                                                <tr>
                                                    <td>
                                                        {{ ++$i }}
                                                    </td>

                                                    <td>{{ $data->nama_lengkap }}</td>
                                                    {{-- <td>{{ $data->email }} </td> --}}
                                                    <td>{{ $data->golongan == null || $data->golongan == 'Tidak ada' ? 'Tidak ada' : $data->golongan }}</td>
                                                    <td>{{ $data->no_ktp }}</td>
                                                    <td>{{ $data->nip }}</td>
                                                    {{-- <td>{{ $data->tempat_lahir . ', ' . $data->tgl_lahir }}</td> --}}
                                                    {{-- <td>{{ $data->alamat_rumah }}</td> --}}
                                                    {{-- <td>{{ $data->gender }}</td> --}}
                                                    <td>{{ $data->jabatan }}</td>
                                                    <td>{{ $data->jenis_pegawai }}</td>
                                                    {{-- <td>{{ $data->agama }}</td> --}}
                                                    {{-- <td>
                                                        {{ $data->satuan_pendidikan }}
                                                    </td> --}}
                                                    {{-- <td>No. Hp : {{ $data->no_hp }} <br>
                                                        No. Whatsapp : {{ $data->no_wa }}
                                                    </td> --}}
                                                    {{-- <td>
                                                        {{ $data->no_rek }}
                                                    </td> --}}
                                                    <td>
                                                        @if ($data->is_verif == 'sudah')
                                                            <span class="badge badge-success">Sudah Verifikasi</span>
                                                        @else
                                                            <span class="badge badge-danger">Belum Verifikasi</span>
                                                        @endif

                                                    </td>
                                                    <td>
                                                        @if (in_array(session('role'), ['admin', 'superadmin']) && $data->is_verif !== 'sudah')
                                                            <a href="#" class="btn btn-primary mb-2"
                                                                onclick="verifikasi({{ $data->id }}, 'pegawai', '{{ $data->is_verif }}')">Verifikasi</a>
                                                        @endif

                                                        {{-- <button class="btn btn-info"
                                                            onclick="showDetail( {{ $data->id }} )">
                                                            <i class="fas fa-info"></i>
                                                        </button> --}}

                                                        <a href="{{ route('pegawai.edit', $data->id) }}"
                                                            class="btn btn-warning my-2"><i class="fas fa-edit"></i></a>

                                                        <button onclick="deleteData({{ $data->id }}, 'pegawai')"
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

    <!-- Modal for Peserta Detail -->
    <div style="z-index: 999999;" class="modal fade" id="pesertaDetailModal" tabindex="-1" role="dialog"
        aria-labelledby="pesertaDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pesertaDetailModalLabel">Detail Pegawai</h5>
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
        <!-- Page Specific JS File -->

        <script>
            $(document).ready(function() {
                const table = $('#table-temp1').DataTable({

                });
            })
        </script>

        <script>
            function showDetail(pesertaId) {
                $.ajax({
                    url: '{{ route('admin.pegawai.detail') }}',
                    type: 'GET',
                    data: {
                        id: pesertaId
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
                        const date = new Date(response.tgl_lahir);
                        const day = String(date.getDate()).padStart(2, '0');
                        const month = String(date.getMonth() + 1).padStart(2, '0'); // getMonth() returns 0-11
                        const year = date.getFullYear();
                        formattedDate = `${day}-${month}-${year}`;

                        $('#pesertaDetailContent').html(`
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Nama:</strong> ${response.nama_lengkap ?? ''}</p>
                                <p><strong>NIK:</strong> ${response.no_ktp ?? ''}</p>
                                <p>
                                    <strong>Tempat, Tanggal Lahir:</strong> ${response.tmepat_lahir ?? ''} - ${formattedDate}
                                </p>
                                <p>
                                    <strong>Alamat:</strong> ${response.alamat_rumah ?? ''} 
                                </p>
                                <p>
                                    <strong>Asal Kabupaten/Kota:</strong> ${response.kabupaten ?? ''} 
                                </p>
                                <p><strong>Agama:</strong> ${response.agama ?? ''}</p>
                                <p><strong>Email:</strong> ${response.email ?? ''}</p>
                                <p><strong>Jabatan:</strong> ${response.jabatan ?? ''}</p>
                                <p><strong>Golongan:</strong> ${response.golongan ?? ''}</p>
        
                                
                            </div>    
                            <div class="col-md-6">
                                <p><strong>Jenis Kelamin:</strong> ${response.gender ?? ''}</p>
                                <p><strong>Nomor Handphone:</strong> ${response.no_hp ?? ''}</p>
                                <p><strong>Nomor WhatsApp:</strong> ${response.no_wa ?? ''}</p>
                                <p><strong>Status Kepegawaian:</strong> ${response.status_kepegawaian ?? ''}</p>
                                <p><strong>No Rekening:</strong> ${response.no_rek ?? ''} ( ${response.jenis_bank} ) </p>
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
            }
        </script>
    @endpush
@endsection
