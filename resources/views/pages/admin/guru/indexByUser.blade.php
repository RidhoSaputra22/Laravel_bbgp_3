@extends('layouts.app', ['title' => 'Data Eksternal BBGTK'])

@section('content')
    @push('styles')
        <link rel="stylesheet" href="{{ asset('library/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('library/datatables.net-select-bs4/css/select.bootstrap4.min.css') }}">
    @endpush

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Data Eksternal BBGTK</h1>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row mb-3">
                                </div>

                                <div class="row">


                                </div>
                                <h5>{{ $datas->nama_lengkap }} - {{ $datas->eksternal_jabatan }} </h5>
                                <div class="row mb-2">
                                    <div class="col-md-8">
                                    </div>
                                    <div class="col">
                                        <div class="d-flex justify-content-end">
                                            <div class="text-right">
                                                <a target="_blank" href="{{ route('guru.export.user', $datas->id) }}"
                                                    class="btn btn-info btn-lg">
                                                    <i class="fas fa-file-pdf"></i> Export PDF
                                                </a>
                                            </div>
                                        </div>

                                    </div>
                                </div>



                                <div class="table-responsive">
                                    <table class="table table-striped" id="table-guru" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th style="width: 200px">Nama Lengkap</th>
                                                <th>Status Kepegawaian</th>
                                                <th>Ketenagaan</th>
                                                <th>Jabatan </th>
                                                <th>Kategori Jabatan </th>
                                                <th>Tugas Jabatan </th>
                                                <th>Status Verifikasi</th>
                                                <th style="width: 20%">Action</th>


                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>{{ 1 }}</td>
                                                <td>{{ $datas->nama_lengkap ?? '' }}</td>
                                                <td>{{ $datas->status_kepegawaian ?? '' }} </td>
                                                <td>{{ $datas->eksternal_jabatan ?? '' }}</td>
                                                <td>{{ $datas->jenis_jabatan ?? '' }}</td>
                                                <td>{{ $datas->kategori_jabatan ?? '' }}</td>
                                                <td>{{ $datas->tugas_jabatan ?? '-' }}</td>

                                                <td>
                                                    @if ($datas->is_verif == 'sudah')
                                                        <span class="badge badge-sm  badge-success">Sudah
                                                            Verifikasi</span>
                                                    @else
                                                        <span class="badge badge-sm  badge-danger">Belum
                                                            Verifikasi</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if (in_array(session('role'), ['admin', 'superadmin']) && $datas->is_verif !== 'sudah')
                                                        <a href="#" class="btn btn-primary "
                                                            onclick="verifikasi({{ $datas->id }}, 'eksternal', '{{ $datas->is_verif }}')">Verifikasi</a>
                                                    @endif

                                                    <a href="{{ route('guru.edit.user', $datas->id) }}"
                                                        class="btn btn-warning my-2"><i class="fas fa-edit"></i></a>

                                                    <button onclick="showDetail( {{ $datas->id }} )"
                                                        class="btn btn-info">
                                                        <i class="fas fa-info"></i>
                                                    </button>

                                                </td>


                                            </tr>
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

    <!-- Modal for Pegawai Detail -->
    <div style="z-index: 999999;" class="modal fade" id="pegawaiDetail" tabindex="-1" role="dialog"
        aria-labelledby="pegawaiDetailLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pegawaiDetailLabel">Detail Data Eksternal</h5>
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

    @push('scripts')
        <script src="{{ asset('library/datatables/media/js/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('library/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('library/datatables.net-select-bs4/js/select.bootstrap4.min.js') }}"></script>

        <script>
            $(document).ready(function() {
                // Initialize DataTable
                var tableGuru = $('#table-guru').DataTable();




                const resetBtn = document.querySelector('#resetBtn');

                // Select input elements
                const namaInput = document.querySelector('#namaFilter');
                const jabEksternal = document.querySelector('#jabEksternal');
                const jabJenis = document.querySelector('#jabJenis');
                const jabKategori = document.querySelector('#jabKategori');
                const jabTugas = document.querySelector('#jabTugas');
                const noDataMessage = document.querySelector('.data-not-found');

                // Function to apply search filters
                function applySearch() {
                    // Get trimmed input value
                    const searchText = namaInput.value.trim();

                    // Get select values
                    const jabEksternalValue = jabEksternal.value;
                    const jabJenisValue = jabJenis.value;
                    const jabKategoriValue = jabKategori.value;
                    const jabTugasValue = jabTugas.value;


                    // Update search and redraw tableGuru
                    tableGuru.column(2).search(searchText).draw();
                    tableGuru.column(13).search(jabEksternalValue).draw();
                    tableGuru.column(14).search(jabJenisValue).draw();
                    tableGuru.column(15).search(jabKategoriValue).draw();
                    tableGuru.column(16).search(jabTugasValue).draw();

                    // Check search result count
                    const info = tableGuru.page.info();
                    if (info.recordsDisplay === 0) {
                        noDataMessage.style.display = 'block';
                    } else {
                        noDataMessage.style.display = 'none';
                    }
                }

                // Event listener for name input keyup
                namaInput.addEventListener('keyup', applySearch);

                // Event listeners for select change
                jabEksternal.addEventListener('change', applySearch);
                jabJenis.addEventListener('change', applySearch);
                jabKategori.addEventListener('change', applySearch);
                jabTugas.addEventListener('change', applySearch);

                resetBtn.addEventListener('click', function() {
                    location.reload();
                })
            });
        </script>

        <script>
            $(document).ready(function() {


                // jabatan ketenagaan
                function fillterJabatan() {
                    var jabEksternal = $('#jabEksternal').val();
                    var jabJenis = $('#jabJenis');
                    var jabTugas = $('#jabTugas');
                    var jabKategori = $('#jabKategori');
                    var option = '';
                    const dataJab = {!! json_encode($status) !!};

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


                    if (jabEksternal == 'Tenaga Pendidik') {

                        let dataJabValue = dataJab['s_jabPendidik'].map(item => {
                            option = $("<option>")
                                .text(item.name)
                                .attr('value', item.name)
                                .removeAttr('disabled');
                            jabJenis.append(option);
                        });
                    }
                    if (jabEksternal == 'Tenaga Kependidikan') {
                        let dataJabValue = dataJab['s_jabKependidikan'].map(item => {
                            option = $("<option>")
                                .text(item.name)
                                .attr('value', item.name)
                                .removeAttr('disabled');
                            jabJenis.append(option);
                        });
                    }
                    if (jabEksternal == 'Stakeholder') {
                        let dataJabValue = dataJab['s_jabStakeholder'].map(item => {
                            option = $("<option>")
                                .text(item.name)
                                .attr('value', item.name)
                                .removeAttr('disabled');
                            jabJenis.append(option);
                        });
                    }
                }

                // kategori jabatan
                function fillterKategori() {
                    var jabKategori = $('#jabKategori').val();
                    var jabTugas = $('#jabTugas');
                    var option = '';
                    const dataJab = {!! json_encode($status) !!};

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

                // fix
                $('#jabKategori').on('change', function() {
                    var jabTugas = $('#jabTugas');
                    var option = '';
                    const dataJab = {!! json_encode($status) !!};



                    var selectedOption = $(this).find('option:selected');

                    if (selectedOption.text() == 'GP (Guru Penggerak)' ||
                        selectedOption.text() == 'Diklat Cakep' ||
                        selectedOption.text() == 'Diklat Cawas' ||
                        selectedOption.text() == 'Lainnya' ||
                        selectedOption.text() == 'Sertifikat GP (Guru Penggerak)') {
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
                            text: '-- Pilih Tugas --',
                            disabled: true,
                            selected: true
                        }));
                    }

                });

                $('#jabJenis').on('change', function() {
                    var jabKategori = $('#jabKategori');
                    var jabTugas = $('#jabTugas');
                    var jabJenis = $(this);
                    var option = '';
                    const dataJab = {!! json_encode($status) !!};

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
                        let dataJabValue = dataJab['s_jabKategoriPengawas'].map((item, i) => {
                            option = $("<option>")
                                .text(item)
                                .attr('value', item)
                                .removeAttr('disabled');
                            jabKategori.append(option);
                        });
                    } else if (selectedOption.text() == 'Kepala Sekolah') {
                        let dataJabValue = dataJab['s_jabKategoriKepsek'].map((item, i) => {
                            option = $("<option>")
                                .text(item)
                                .attr('value', item)
                                .removeAttr('disabled');
                            jabKategori.append(option);
                        });
                    } else {
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
                    }

                });
            });

            function                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           (pegawaiId) {
                $.ajax({
                    url: '{{ route('admin.eksternal.detail') }}',
                    type: 'GET',
                    data: {
                        id: pegawaiId
                    },
                    success: function(response) {

                        $('#pegawaiDetailContent').html(`
                    <h6 ><strong>NPSN Sekolah:</strong> ${response.data.npsn_sekolah ?? ''} - ${response.nama_sekolah}</h6>
                    <hr>
                    <div class="row">    
                    <div class="col-md-6">
                        <p><strong>Nama Lengkap:</strong> ${response.data.nama_lengkap ?? ''}</p>
                        <p><strong>NIK:</strong> ${response.data.no_ktp ?? ''}</p>
                        <p><strong>NIP:</strong> ${response.data.nip ?? ''}</p>
                        <p><strong>NPWP:</strong> ${response.data.npwp ?? ''}</p>
                        <p><strong>NUPTK:</strong> ${response.data.nuptk ?? ''}</p>
                        <p><strong>Tempat, Tanggal lahir:</strong> ${response.data.tempat_lahir ?? ''}, ${response.data.tgl_lahir ?? ''}</p>
                        <p><strong>Agama:</strong> ${response.data.agama ?? ''}</p>
                        <p><strong>Asal Kabupaten:</strong> ${response.data.kabupaten ?? ''}</p>
                        <p><strong>Pendidikan Terakhir:</strong> ${response.data.pendidikan ?? ''}</p>
                      
                    </div>    
                    <div class="col-md-6">
                          <p><strong>Satuan Pendidikan:</strong> ${response.data.satuan_pendidikan ?? ''}</p>
                            <p><strong>Status Kepegawaian:</strong> ${response.data.status_kepegawaian ?? ''}</p>
                            <p><strong>Eksternal Jabatan:</strong> ${response.data.eksternal_jabatan ?? ''}</p>
                            <p><strong>Jenis Jabatan:</strong> ${response.data.jenis_jabatan ?? ''}</p>
                            <p><strong>Kategori Jabatan:</strong> ${response.data.kategori_jabatan ?? ''}</p>
                            <p><strong>Tugas Jabatan:</strong> ${response.data.tugas_jabatan ?? '-'}</p>
                            <p><strong>Kecamatan Sekolah:</strong> ${response.kecamatan_sekolah ?? ''}</p>
                            <p><strong>Kabupaten Sekolah:</strong> ${response.kabupaten_sekolah ?? ''}</p>
                        <p><strong>Verifikasi:</strong> ${response.data.is_verif === 'sudah' ? '<span class="badge badge-sm badge-success">Sudah Verifikasi</span>' : '<span class="badge badge-sm badge-danger">Belum Verifikasi</span>'}</p>
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
        </script>
    @endpush
@endsection
