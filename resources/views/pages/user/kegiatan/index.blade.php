@extends('layouts.user.app', ['title' => 'Kegiatan '])

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1 class="text-primary"><u>Data Kegiatan</u></h1>
            </div>

            <div class="section-body">
                <div class="card">
                    <div class="card-body">
                        @if ($kegiatan->isNotEmpty())
                            <h5 id="title-kegiatan">Daftar Kegiatan</h5>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <select required name="daftarKegiatan" class="form-control" id="daftarKegiatan">
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
                                </div>
                                <div class="col-md-6 mb-4 text-right">
                                    {{-- <div id="btnGroup">
                                        <button id="btnPrintPeserta" class="btn btn-primary"><i
                                                class="fas fa-print mr-2"></i>Print Absensi Peserta</button>
                                        <button id="btnPrintRegisPeserta" class="btn btn-primary"><i
                                                class="fas fa-print mr-2"></i>Print Absensi Registrasi Peserta</button>
                                        <button id="btnPrintPanitia" class="btn btn-info"><i
                                                class="fas fa-print mr-2"></i>Print Absensi Panitia</button>
                                        <button id="btnPrintNarsum" class="btn btn-warning"><i
                                                class="fas fa-print mr-2"></i>Print Absensi Narasumber</button>
                                    </div> --}}
                                </div>
                            </div>

                            <div id="searchSection" style="display: none;">
                                <p>Silahkan cari data anda</p>
                                <form id="searchForm">
                                    <div class="row">
                                        <div class="col-md-5">
                                            <input class="form-control" type="number" name="cari" id="nikSearch"
                                                placeholder="Masukkan NIK anda ..." value="{{ old('cari') }}">
                                        </div>
                                        <div class="col-md-2">
                                            <input class="form-control btn btn-info" type="submit" value="CARI">
                                        </div>
                                    </div>
                                </form>
                                <br />
                            </div>

                            <!-- Table will be loaded here dynamically -->
                            <div id="showKegiatan" style="display: none;">
                                <div class="table-responsive">
                                    <table class="table table-striped table-internal" id="table-internal-1">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>NIK</th>
                                                <th>Status Keikutpesertaan</th>
                                                <th>Instansi</th>
                                                <th>Golongan</th>
                                                <th>Jenis Golongan</th>
                                                <th>Jenis Kelamin</th>
                                                <th>Kelengkapan Trasnport</th>
                                                <th>Kelengkapan Biodata</th>
                                                <th>Nomor Handphone</th>
                                                <th>Nomor WhatsApp</th>
                                                <th>Kabupaten</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="kegiatanPeserta">
                                            <!-- Data will be appended here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @else
                            <h5>Tidak ada kegiatan untuk saat ini.</h5>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Modal for Peserta Detail -->
    <div class="modal fade" id="pesertaDetailModal" tabindex="-1" role="dialog" aria-labelledby="pesertaDetailModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pesertaDetailModalLabel">Detail Peserta</h5>
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
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#btnGroup').hide(); // Menyembunyikan semua tombol saat tidak ada kegiatan dipilih
            $('#daftarKegiatan').on('change', function() {
                let kegiatanId = $(this).val();
                let textKegiatan = $(this).find('option:selected');
                console.log(kegiatanId);
                if (kegiatanId === '') {
                    $('#searchSection').hide();
                    $('#showKegiatan').hide();
                    $('#btnGroup').hide(); // Menyembunyikan semua tombol saat tidak ada kegiatan dipilih
                    return;
                }

                // Menampilkan judul kegiatan
                $('#title-kegiatan').text(`Kegiatan ${textKegiatan.text()}`);
                $('#searchSection').show();
                $('#showKegiatan').hide(); // Sembunyikan tabel saat memilih kegiatan baru

                // Ambil status keikutsertaan dari kegiatan yang dipilih
                $.ajax({
                    url: '{{ route('user.kegiatan.getStatus') }}', // Ganti dengan route yang sesuai untuk mengambil status
                    type: 'GET',
                    data: {
                        kegiatan_id: kegiatanId
                    },
                    success: function(response) {
                        let status = response.status_keikutpesertaan;

                        // Mengatur visibility button berdasarkan status
                        $('#btnPrintPeserta').toggle(status === 'peserta' || status ===
                            'registrasi');
                        $('#btnPrintRegisPeserta').toggle(status === 'registrasi');
                        $('#btnPrintPanitia').toggle(status === 'panitia');
                        $('#btnPrintNarsum').toggle(status === 'narasumber');

                        $('#btnGroup').show(); // Menampilkan button setelah status terambil
                    },
                    error: function(error) {
                        console.error(error);
                        alert('Error fetching kegiatan status.');
                    }
                });
            });

            $('#searchForm').on('submit', function(e) {
                e.preventDefault();
                let kegiatanId = $('#daftarKegiatan').val();
                let nik = $('#nikSearch').val();
                if (kegiatanId && nik) {
                    $.ajax({
                        url: '{{ route('user.kegiatan.cariPeserta') }}',
                        type: 'GET',
                        data: {
                            kegiatan_id: kegiatanId,
                            nik: nik
                        },
                        success: function(response) {
                            $('#kegiatanPeserta').empty();
                            console.log(response);
                            if (response.data.length > 0) {
                                response.data.forEach((peserta, index) => {
                                    $('#kegiatanPeserta').append(`
                                        <tr>
                                            <td>${index + 1}</td>
                                            <td>${peserta.no_ktp}</td>
                                            <td>${peserta.status_keikutpesertaan}</td>
                                            <td>${peserta.instansi}</td>
                                            <td>${peserta.golongan ?? ''}</td>
                                            <td>${peserta.jenis_gol ?? ''}</td>
                                            <td>${peserta.jkl ?? ''}</td>
                                            <td>${peserta.kelengkapan_peserta_transport ?? ''}</td>
                                            <td>${peserta.kelengkapan_peserta_biodata ?? ''}</td>
                                            <td>${peserta.no_hp ?? ''}</td>
                                            <td>${peserta.no_wa ?? ''}</td>
                                            <td>${peserta.kabupaten ?? ''}</td>
                                            <td>
                                                <button class="btn btn-info" onclick="showDetail(${peserta.id})">
                                                    Detail
                                                </button>
                                            </td>
                                        </tr>
                                    `);
                                });
                                $('#showKegiatan').show();

                                // Deteksi status keikutsertaan
                                let statusKeikutpesertaan = response.data[0]
                                    .status_keikutpesertaan;

                                // Menyesuaikan tombol berdasarkan status
                                $('#btnPrintPeserta').toggle(statusKeikutpesertaan ===
                                    'peserta' || statusKeikutpesertaan === 'registrasi');
                                $('#btnPrintRegisPeserta').toggle(statusKeikutpesertaan ===
                                    'registrasi');
                                $('#btnPrintPanitia').toggle(statusKeikutpesertaan ===
                                    'panitia');
                                $('#btnPrintNarsum').toggle(statusKeikutpesertaan ===
                                    'narasumber');
                            } else {

                                $('#showKegiatan').hide();
                                var pesertaAda;
                                // console.log(nik);
                                $.ajax({
                                    url: '{{ route('user.peserta.cekData') }}',
                                    type: 'GET',
                                    data: {
                                        kegiatan_id: kegiatanId,
                                        nik: nik
                                    },
                                    success: function(response) {
                                        console.log(response);
                                        pesertaAda = response.success;
                                        console.log('pernah ikut : ', pesertaAda);

                                        // ada 2 alert, yg sdh pernah ikut dan belum pernah
                                        if (response.success) {
                                            swal({
                                                title: "Warning",
                                                text: "Anda belum terdaftar di kegiatan ini. Tapi anda sudah pernah mengikuti kegiatan BBGTK sebelumnya. Lanjut ke Registrasi ? \n Form akan terisi otomatis dari data anda sebelumnya",
                                                icon: "success",
                                                buttons: true,
                                                dangerMode: true,
                                            }).then((res) => {
                                                if (res) {
                                                    // Redirect to registrasi page with kegiatan_id
                                                    window.location.href =
                                                        '{{ route('user.kegiatan_regist') }}' +
                                                        '?kegiatan_id=' +
                                                        kegiatanId +
                                                        '&nik=' +
                                                        nik;
                                                }
                                            });
                                        } else {
                                            swal({
                                                title: "Warning",
                                                text: "Peserta tidak ditemukan. Silahkan registrasi untuk mengikuti kegiatan",
                                                icon: "warning",
                                                buttons: true,
                                                dangerMode: true,
                                            }).then((res) => {
                                                if (res) {
                                                    // Redirect to registrasi page with kegiatan_id
                                                    window.location.href =
                                                        '{{ route('user.kegiatan_regist') }}' +
                                                        '?kegiatan_id=' +
                                                        kegiatanId;
                                                }
                                            });
                                        }
                                    },
                                    error: function(error) {
                                        console.error(error);
                                        alert('Error fetching detail.');
                                    }
                                });








                            }
                        },
                        error: function(error) {
                            console.error(error);
                            alert('Error fetching peserta data.');
                        }
                    });
                } else {
                    swal('warning', 'Pilih kegiatan dan masukkan NIK terlebih dahulu.', 'warning');
                }
            });
        });

        function showDetail(pesertaId) {
            $.ajax({
                url: '{{ route('user.peserta.detail') }}',
                type: 'GET',
                data: {
                    id: pesertaId
                },
                success: function(response) {
                    $('#pesertaDetailContent').html(`
                        <p><strong>NIK:</strong> ${response.no_ktp ?? ''}</p>
                        <p><strong>Nama:</strong> ${response.nama ?? ''}</p>
                        <p><strong>Status11:</strong> ${response.status_keikutpesertaan ?? ''}</p>
                        <p>
                            <strong>Nomor Surat dan Tanggal Surat:</strong> ${response.no_surat_tugas ?? ''} Tanggal ${response.tgl_surat_tugas}
                        </p>
                        <p><strong>Instansi:</strong> ${response.instansi ?? ''}</p>
                        <p><strong>Golongan:</strong> ${response.golongan ?? ''}</p>
                        <p><strong>jenis_gol:</strong> ${response.jenis_gol ?? ''}</p>
                        <p><strong>Jenis Kelamin:</strong> ${response.jkl ?? ''}</p>
                        <p><strong>Kelengkapan Peserta Transport:</strong> ${response.kelengkapan_peserta_transport ?? ''}</p>
                        <p><strong>Kelengkapan Peserta Biodata:</strong> ${response.kelengkapan_peserta_biodata ?? ''}</p>
                        <p><strong>Nomor Handphone:</strong> ${response.no_hp ?? ''}</p>
                        <p><strong>Nomor WhatsApp:</strong> ${response.no_wa ?? ''}</p>
                        <p><strong>Kabupaten:</strong> ${response.kabupaten ?? ''}</p>
                        ${response.signature ? `<p><img src="{{ asset('${response.signature}') }}" alt="Signature" style="width: 300px; height: auto;"></p>` : 'N/A'}
                    `);
                    $('#pesertaDetailModal').modal('show');
                },
                error: function(error) {
                    console.error(error);
                    alert('Error fetching detail.');
                }
            });
        }

        function openPrintWindow(url) {
            window.open(url, '_blank');
        }

        $('#btnPrintPeserta').on('click', function() {
            let kegiatanId = $('#daftarKegiatan').val();
            if (kegiatanId) {
                openPrintWindow('{{ route('print.absensi.peserta') }}' + '?kegiatan_id=' + kegiatanId);
            } else {
                alert('Pilih kegiatan terlebih dahulu.');
            }
        });

        $('#btnPrintRegisPeserta').on('click', function() {
            let kegiatanId = $('#daftarKegiatan').val();
            if (kegiatanId) {
                openPrintWindow('{{ route('print.registrasi.peserta') }}' + '?kegiatan_id=' + kegiatanId);
            } else {
                alert('Pilih kegiatan terlebih dahulu.');
            }
        });

        $('#btnPrintPanitia').on('click', function() {
            let kegiatanId = $('#daftarKegiatan').val();
            if (kegiatanId) {
                openPrintWindow('{{ route('print.absensi.panitia') }}' + '?kegiatan_id=' + kegiatanId);
            } else {
                alert('Pilih kegiatan terlebih dahulu.');
            }
        });

        $('#btnPrintNarsum').on('click', function() {
            let kegiatanId = $('#daftarKegiatan').val();
            if (kegiatanId) {
                openPrintWindow('{{ route('print.absensi.narasumber') }}' + '?kegiatan_id=' + kegiatanId);
            } else {
                alert('Pilih kegiatan terlebih dahulu.');
            }
        });
    </script>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#btnGroup').hide(); // Menyembunyikan semua tombol saat tidak ada kegiatan dipilih
            $('#daftarKegiatan').on('change', function() {
                let kegiatanId = $(this).val();
                let id_k = $(this).val();
                let textKegiatan = $(this).find('option:selected');
                // console.log(kegiatanId);
                if (kegiatanId === '') {
                    $('#searchSection').hide();
                    $('#showKegiatan').hide();
                    $('#btnGroup').hide(); // Menyembunyikan semua tombol saat tidak ada kegiatan dipilih
                    return;
                }

                // Menampilkan judul kegiatan
                $('#title-kegiatan').text(`Kegiatan ${textKegiatan.text()}`);
                $('#searchSection').show();
                $('#showKegiatan').hide(); // Sembunyikan tabel saat memilih kegiatan baru

                // Ambil status keikutsertaan dari kegiatan yang dipilih
                $.ajax({
                    url: '{{ route('user.kegiatan.getStatus') }}', // Ganti dengan route yang sesuai untuk mengambil status
                    type: 'GET',
                    data: {
                        kegiatan_id: kegiatanId
                    },
                    success: function(response) {
                        let status = response.status_keikutpesertaan;

                        // Mengatur visibility button berdasarkan status
                        $('#btnPrintPeserta').toggle(status === 'peserta' || status ===
                            'registrasi');
                        $('#btnPrintRegisPeserta').toggle(status === 'registrasi');
                        $('#btnPrintPanitia').toggle(status === 'panitia');
                        $('#btnPrintNarsum').toggle(status === 'narasumber');

                        $('#btnGroup').show(); // Menampilkan button setelah status terambil
                    },
                    error: function(error) {
                        console.error(error);
                        alert('Error fetching kegiatan status.');
                    }
                });
            });

            $('#searchForm').on('submit', function(e) {
                e.preventDefault();
                let kegiatanId = $('#daftarKegiatan').val();
                let nik = $('#nikSearch').val();

                if (kegiatanId && nik) {
                    $.ajax({
                        url: '{{ route('user.kegiatan.cariPeserta') }}',
                        type: 'GET',
                        data: {
                            kegiatan_id: kegiatanId,
                            nik: nik
                        },
                        success: function(response) {
                            $('#kegiatanPeserta').empty();
                            // console.log(response);
                            if (response.data.length > 0) {
                                response.data.forEach((peserta, index) => {
                                    $('#kegiatanPeserta').append(`
                                        <tr>
                                            <td>${index + 1}</td>
                                            <td>${peserta.no_ktp}</td>
                                            <td>${peserta.status_keikutpesertaan}</td>
                                            <td>${peserta.instansi}</td>
                                            <td>${peserta.golongan ?? 'Tidak '}</td>
                                            <td>${peserta.jenis_gol ?? 'Tidak '}</td>
                                            <td>${peserta.jkl ?? ''}</td>
                                            <td>${peserta.kelengkapan_peserta_transport ?? ''}</td>
                                            <td>${peserta.kelengkapan_peserta_biodata ?? ''}</td>
                                            <td>${peserta.no_hp ?? ''}</td>
                                            <td>${peserta.no_wa ?? ''}</td>
                                            <td>${peserta.kabupaten ?? ''}</td>
                                            <td>
                                                <button class="btn btn-info" onclick="showDetail(${peserta.id})">
                                                    Detail
                                                </button>
                                            </td>
                                        </tr>
                                    `);
                                });
                                $('#showKegiatan').show();

                                // Deteksi status keikutsertaan
                                let statusKeikutpesertaan = response.data[0]
                                    .status_keikutpesertaan;

                                // Menyesuaikan tombol berdasarkan status
                                $('#btnPrintPeserta').toggle(statusKeikutpesertaan ===
                                    'peserta' || statusKeikutpesertaan === 'registrasi');
                                $('#btnPrintRegisPeserta').toggle(
                                    statusKeikutpesertaan ===
                                    'peserta' ||
                                    statusKeikutpesertaan ===
                                    'registrasi');
                                $('#btnPrintPanitia').toggle(statusKeikutpesertaan ===
                                    'panitia');
                                $('#btnPrintNarsum').toggle(statusKeikutpesertaan ===
                                    'narasumber');


                            } else {
                                $('#showKegiatan').hide();




                                swal({
                                    title: "Warning",
                                    text: "Data anda tidak ditemukan. Silahkan registrasi untuk mengikuti kegiatan",
                                    icon: "warning",
                                    buttons: {
                                        cancel: {
                                            text: "Tidak, Terima Kasih",
                                            value: null,
                                            visible: true,
                                            className: "",
                                            closeModal: true,
                                        },
                                        confirm: {
                                            text: "Registrasi ",
                                            value: true,
                                            visible: true,
                                            className: "btn-primary",
                                            closeModal: false
                                        }
                                    },
                                    dangerMode: true,
                                }).then((res) => {
                                    if (res) {
                                        // Redirect to registrasi page with kegiatan_id
                                        window.location.href =
                                            '{{ route('user.kegiatan_regist') }}' +
                                            '?kegiatan_id=' + kegiatanId;
                                    }
                                });
                            }
                        },
                        error: function(error) {
                            console.error(error);
                            alert('Error fetching peserta data.');
                        }
                    });
                } else {
                    swal('warning', 'Pilih kegiatan dan masukkan NIK terlebih dahulu.', 'warning');
                }
            });
        });

        function showDetail(pesertaId) {
            $.ajax({
                url: '{{ route('user.peserta.detail') }}',
                type: 'GET',
                data: {
                    id: pesertaId
                },
                success: function(response) {
                    $('#pesertaDetailContent').html(`
                        <p><strong>NIK:</strong> ${response.no_ktp ?? ''}</p>
                        <p><strong>Nama:</strong> ${response.nama ?? ''}</p>
                        <p><strong>Status:</strong> ${response.status_keikutpesertaan ?? ''}</p>
                        <p>
                            <strong>Nomor Surat:</strong> ${response.no_surat_tugas ?? ''} 
                        </p>
                        <p>
                            <strong>Tanggal Surat:</strong> ${response.tgl_surat_tugas}
                        </p>

                        <p><strong>Instansi:</strong> ${response.instansi ?? ''}</p>
                        <p><strong>Golongan:</strong> ${response.golongan ?? ''}</p>
                        <p><strong>Golongan:</strong> ${response.jenis_gol ?? ''}</p>
                        <p><strong>Jenis Kelamin:</strong> ${response.jkl ?? ''}</p>
                        <p><strong>Kelengkapan Peserta Transport:</strong> ${response.kelengkapan_peserta_transport ?? ''}</p>
                        <p><strong>Kelengkapan Peserta Biodata:</strong> ${response.kelengkapan_peserta_biodata ?? ''}</p>
                        <p><strong>Nomor Handphone:</strong> ${response.no_hp ?? ''}</p>
                        <p><strong>Nomor WhatsApp:</strong> ${response.no_wa ?? ''}</p>
                        <p><strong>Kabupaten:</strong> ${response.kabupaten ?? ''}</p>
                        ${response.signature ? `<p><img src="{{ asset('${response.signature}') }}" alt="Signature" style="width: 300px; height: auto;"></p>` : 'N/A'}
                    `);
                    $('#pesertaDetailModal').modal('show');
                },
                error: function(error) {
                    console.error(error);
                    alert('Error fetching detail.');
                }
            });
        }

        function openPrintWindow(url) {
            window.open(url, '_blank');
        }

        $('#btnPrintPeserta').on('click', function() {
            let kegiatanId = $('#daftarKegiatan').val();
            if (kegiatanId) {
                openPrintWindow('{{ route('print.absensi.peserta') }}' + '?kegiatan_id=' + kegiatanId);
            } else {
                alert('Pilih kegiatan terlebih dahulu.');
            }
        });

        $('#btnPrintRegisPeserta').on('click', function() {
            let kegiatanId = $('#daftarKegiatan').val();
            if (kegiatanId) {
                openPrintWindow('{{ route('print.registrasi.peserta') }}' + '?kegiatan_id=' + kegiatanId);
            } else {
                alert('Pilih kegiatan terlebih dahulu.');
            }
        });

        $('#btnPrintPanitia').on('click', function() {
            let kegiatanId = $('#daftarKegiatan').val();
            if (kegiatanId) {
                openPrintWindow('{{ route('print.absensi.panitia') }}' + '?kegiatan_id=' + kegiatanId);
            } else {
                alert('Pilih kegiatan terlebih dahulu.');
            }
        });

        $('#btnPrintNarsum').on('click', function() {
            let kegiatanId = $('#daftarKegiatan').val();
            if (kegiatanId) {
                openPrintWindow('{{ route('print.absensi.narasumber') }}' + '?kegiatan_id=' + kegiatanId);
            } else {
                alert('Pilih kegiatan terlebih dahulu.');
            }
        });
    </script>
@endpush
