@extends('layouts.landing.app')
@section('content')
   @push('styles')
      <link rel="stylesheet" href="{{ asset('library/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
      <link rel="stylesheet" href="{{ asset('library/datatables.net-select-bs4/css/select.bootstrap4.min.css') }}">
   @endpush

   <div id="banner-area" class="banner-area"
      style="background-image:url({{ asset('landing/images/banner/bannerKegiatan.png') }})">
      <div class="banner-text">
         <div class="container">
            <div class="row">
               <div class="col-lg-12">
                  <div class="banner-heading">
                     <h1 class="banner-title">Kegiatan</h1>
                     <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center">
                           <li class="breadcrumb-item"><a href="/">Home</a></li>
                           <li class="breadcrumb-item active" aria-current="page">Kegiatan</li>
                        </ol>
                     </nav>
                  </div>
               </div><!-- Col end -->
            </div><!-- Row end -->
         </div><!-- Container end -->
      </div><!-- Banner text end -->
   </div><!-- Banner area end -->


   <div class="container my-4">
      @if ($kegiatan->isNotEmpty())
         <h5 id="title-kegiatan">Daftar Kegiatan</h5>

         <div class="row">
            <div class="col-md-6 mb-4">
               <select required name="daftarKegiatan" class="form-control " id="daftarKegiatan">
                  <option value="">-- pilih kegiatan --</option>
                  @foreach ($kegiatan as $v)
                     <?php
                     setlocale(LC_TIME, 'id_ID.UTF-8');
                     
                     $tgl_kegiatan = strftime('%d %B', strtotime($v->tgl_kegiatan));
                     $tgl_selesai = strftime('%d %B %Y', strtotime($v->tgl_selesai));
                     ?>
                     <option value="{{ $v->id }}" data-nama-kegiatan="{{ $v->nama_kegiatan }}"
                        data-tgl-kegiatan="{{ $tgl_kegiatan }}" data-tgl-selesai="{{ $tgl_selesai }}"
                        data-tempat="{{ $v->tempat_kegiatan }}" data-deskripsi="{{ $v->deskripsi_kegiatan }}"
                        data-mulai="{{ $v->jam_mulai }}" data-selesai="{{ $v->jam_selesai }}">

                        {{ $v->nama_kegiatan }}
                        {{-- (
                                {{ $tgl_kegiatan }} -
                                {{ $tgl_selesai }}
                                ) di {{ $v->tempat_kegiatan }} --}}
                     </option>
                  @endforeach
               </select>
            </div>
            <div id="allRincian">
               <div class="col-md mb-4 ">

                  <h4>Kegiatan <span id="rincianKegiatan"></span></h4>
                  <p>Tanggal Kegiatan : <span id="rincianTgl"></span></p>
                  <p>Jam Kegiatan : <span id="rincianJam"></span></p>
                  <p>Lokasi Kegiatan : <span id="rincianLokasi"></span></p>
                  <p>Keterangan Kegiatan : <span id="rincianKeterangan"></span></p>
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
         </div>

         <div id="searchSection" style="display: none;">
            <p>Silahkan cari data anda</p>
            <form id="searchForm">
               <div class="row">
                  <div class="col-md-5 mb-4">
                     <input class="form-control" type="number" name="cari" id="nikSearch"
                        placeholder="Masukkan NIK anda ..." value="{{ old('cari') }}">
                  </div>
                  <div class="col-md-2">
                     <input style="background-color: #218838; color: white; " class="form-control btn btn-success"
                        type="submit" value="CARI">
                  </div>
                  {{-- <div class="col-md-2">
                     <a href="https://forms.gle/DbnABf2sTy6XNQMv5" target="_blank"
                        style="background-color: #1376bd; color: white; " class="form-control btn btn-success">Post
                        test</a>
                  </div>
                  <div class="col-md-3">
                     <a href="https://forms.gle/kWSieMx55GiKcEcq5" target="_blank"
                        style="background-color: #1376bd; color: white; " class="form-control btn btn-success">Evaluasi
                        Penyelenggaraan</a>
                  </div>
                  <div class="col-md-3">
                     <a href="https://forms.gle/mGNjDQiSLB469nRE7" target="_blank"
                        style="background-color: #1376bd; color: white; " class="form-control btn btn-success">Evaluasi
                        Fasilitator</a>
                  </div> --}}
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
                        <th style="width: 200px">Nama</th>
                        <th>Status Keikutpesertaan</th>
                        <th>Jenis Kelamin</th>
                        <th style="width: 200px">Nomor WhatsApp</th>
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


   <!-- Modal for Peserta Detail -->
   <div style="z-index: 999999;" class="modal fade" id="pesertaDetailModal" tabindex="-1" role="dialog"
      aria-labelledby="pesertaDetailModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
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

   @push('scripts')
      <script src="{{ asset('library/datatables/media/js/jquery.dataTables.min.js') }}"></script>
      <script src="{{ asset('library/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
      <script src="{{ asset('library/datatables.net-select-bs4/js/select.bootstrap4.min.js') }}"></script>
      <script src="{{ asset('js/page/modules-datatables.js') }}"></script>

      <script>
         function formatTanggalIndo(dateStr) {
            const options = {
               day: '2-digit',
               month: 'long',
               year: 'numeric'
            };
            const date = new Date(dateStr);
            return date.toLocaleDateString('id-ID', options);
         }

         function formatJamMenit(timeStr) {
            const time = new Date('1970-01-01T' + timeStr + 'Z');
            return time.getUTCHours().toString().padStart(2, '0') + ':' + time.getUTCMinutes()
               .toString().padStart(2, '0');
         }

         $(document).ready(function() {
            $('#btnGroup').hide(); // Menyembunyikan semua tombol saat tidak ada kegiatan dipilih
            $('#allRincian').hide();

            $('#daftarKegiatan').select2({
               width: 'resolve' // need to override the changed default
            });

            $('#daftarKegiatan').on('change', function() {
               let kegiatanId = $(this).val();
               let textKegiatan = $(this).find('option:selected');
               console.log(kegiatanId);
               if (kegiatanId === '') {
                  $('#allRincian').hide();

                  $('#searchSection').hide();
                  $('#showKegiatan').hide();
                  $('#title-kegiatan').text('Pilih Kegiatan terlebih dahulu');
                  $('#btnGroup').hide(); // Menyembunyikan semua tombol saat tidak ada kegiatan dipilih
                  return;
               }

               // Menampilkan judul kegiatan
               $('#title-kegiatan').text(`Kegiatan ${textKegiatan.text()}`);
               $('#searchSection').show();
               $('#allRincian').show();

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




               var selectedOption = $(this).find('option:selected');
               var namaKegiatan = selectedOption.data('nama-kegiatan');
               var tglKegiatan = selectedOption.data('tgl-kegiatan');
               var tglSelesai = selectedOption.data('tgl-selesai');
               var tempat = selectedOption.data('tempat');
               var deskripsi = selectedOption.data('deskripsi');
               var mulai = selectedOption.data('mulai');
               var selesai = selectedOption.data('selesai');
               console.log(mulai);

               if (namaKegiatan && tglKegiatan && tglSelesai && tempat) {
                  console.log(namaKegiatan);
                  $('#rincianKegiatan').html(`${namaKegiatan}`);
                  $('#rincianTgl').html(`${tglKegiatan} - ${tglSelesai}`);
                  $('#rincianJam').html(`${formatJamMenit(mulai)} - ${formatJamMenit(selesai)} WITA`);
                  $('#rincianLokasi').html(`${tempat}`);
                  $('#rincianKeterangan').html(`${namaKegiatan}`);
               } else {
                  $('#rincianKegiatan').html('');
                  $('#rincianTgl').html('');
                  $('#rincianJam').html('');
                  $('#rincianLokasi').html('');
                  $('#rincianKeterangan').html('');
               }
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
                        let data = response.data;
                        let tipe = response.tipe;
                        if (response.success) {
                           response.data.forEach((peserta, index) => {
                              let kelengkapanTransport = peserta
                                 .status_keikutpesertaan === 'peserta' ?
                                 `<td>${peserta.kelengkapan_peserta_transport ?? ''}</td>` :
                                 '<td>Hanya untuk peserta</td>';
                              let kelengkapanBiodata = peserta
                                 .status_keikutpesertaan === 'peserta' ?
                                 `<td>${peserta.kelengkapan_peserta_biodata ?? ''}</td>` :
                                 '<td>Hanya untuk peserta</td>';

                              $('#kegiatanPeserta').append(`
                                        <tr>
                                            <td>${index + 1}</td>
                                            <td>${peserta.no_ktp}</td>
                                            <td>${peserta.nama}</td>
                                            <td>${peserta.status_keikutpesertaan}</td>
                                            <td>${peserta.jkl ?? ''}</td>
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
                                 console.log('halo ', data.length);
                                 pesertaAda = response.success;
                                 console.log('pernah ikut : ', pesertaAda);

                                 // ada 2 alert, yg sdh pernah ikut dan belum pernah
                                 if (data.length > 0) {

                                    if (tipe === 'Peserta') {
                                       Swal.fire({
                                          title: "Warning",
                                          text: "Anda tidak terdaftar di kegiatan ini. Tapi anda sudah pernah mengikuti kegiatan BBGTK sebelumnya. Lanjut ke Registrasi ? \n Form akan terisi otomatis dari data anda sebelumnya",
                                          icon: "warning",
                                          showCancelButton: true,
                                          confirmButtonColor: "#4040f5",
                                          cancelButtonColor: "#ff3034",
                                          confirmButtonText: "Registrasi",
                                          cancelButtonText: "Tidak, terimakasih",
                                       }).then((res) => {
                                          if (res.isConfirmed) {
                                             // Redirect to registrasi page with kegiatan_id
                                             window.location
                                                .href =
                                                '{{ route('user.kegiatan_regist') }}' +
                                                '?kegiatan_id=' +
                                                kegiatanId +
                                                '&nik=' +
                                                nik;
                                          }
                                       });

                                       return;

                                    } else if (tipe === 'Pegawai') {
                                       Swal.fire({
                                          title: "Warning",
                                          text: "Anda tidak terdaftar di kegiatan ini. Tapi anda ter-data sebagai Pegawai BBGTK . Lanjut ke Registrasi ? \n Form akan terisi otomatis dari data anda sebelumnya",
                                          icon: "warning",
                                          showCancelButton: true,
                                          confirmButtonColor: "#4040f5",
                                          cancelButtonColor: "#ff3034",
                                          confirmButtonText: "Registrasi",
                                          cancelButtonText: "Tidak, terimakasih",
                                       }).then((res) => {
                                          if (res.isConfirmed) {
                                             // Redirect to registrasi page with kegiatan_id
                                             window.location
                                                .href =
                                                '{{ route('user.kegiatan_regist') }}' +
                                                '?kegiatan_id=' +
                                                kegiatanId +
                                                '&nik=' +
                                                nik;
                                          }
                                       });

                                       return;
                                    } else if (tipe === 'Eksternal') {
                                       Swal.fire({
                                          title: "Warning",
                                          text: "Anda tidak terdaftar di kegiatan ini. Tapi anda ter-data sebagai Eksternal BBGTK. Lanjut ke Registrasi ? \n Form akan terisi otomatis dari data anda sebelumnya",
                                          icon: "warning",
                                          showCancelButton: true,
                                          confirmButtonColor: "#4040f5",
                                          cancelButtonColor: "#ff3034",
                                          confirmButtonText: "Registrasi",
                                          cancelButtonText: "Tidak, terimakasih",
                                       }).then((res) => {
                                          if (res.isConfirmed) {
                                             // Redirect to registrasi page with kegiatan_id
                                             window.location
                                                .href =
                                                '{{ route('user.kegiatan_regist') }}' +
                                                '?kegiatan_id=' +
                                                kegiatanId +
                                                '&nik=' +
                                                nik;
                                          }
                                       });

                                       return;
                                    }

                                 } else {
                                    Swal.fire({
                                       title: "Warning",
                                       // text: "Peserta tidak terdaftar dalam kegiatan ini. Silahkan registrasi untuk mengikuti kegiatan",
                                       text: "Peserta belum mendaftar dalam kegiatan ini. Silahkan registrasi ke menu Data > Data Eksternal",
                                       icon: "warning",
                                       showCancelButton: true,
                                       confirmButtonColor: "#4040f5",
                                       cancelButtonColor: "#ff3034",
                                       confirmButtonText: "Daftar"
                                    }).then((res) => {
                                       if (res.isConfirmed) {
                                          // Redirect to registrasi page with kegiatan_id
                                          // window.location.href =
                                          //     '{{ route('user.kegiatan_regist') }}' +
                                          //     '?kegiatan_id=' +
                                          //     kegiatanId;
                                          window.location.href =
                                             '{{ route('user.guru') }}'
                                       }
                                    });
                                    return;
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
                  Swal.fire('warning', 'Pilih kegiatan dan masukkan NIK terlebih dahulu.', 'warning');
                  return;
               }
            });

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


         });

         function showDetail(pesertaId) {
            $.ajax({
               url: '{{ route('user.peserta.detail') }}',
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
                  const date = new Date(response.tgl_surat_tugas);
                  const day = String(date.getDate()).padStart(2, '0');
                  const month = String(date.getMonth() + 1).padStart(2, '0'); // getMonth() returns 0-11
                  const year = date.getFullYear();
                  formattedDate = `${day}-${month}-${year}`;

                  $('#pesertaDetailContent').html(`
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Nama:</strong> ${response.nama ?? ''}</p>
                                <p><strong>NIK:</strong> ${response.no_ktp ?? ''}</p>
                                <p>
                                    <strong>Status Keikutpesertaan:</strong> ${response.status_keikutpesertaan ?? ''}
                                </p>
                                <p>
                                    <strong>Nomor Surat:</strong> ${response.no_surat_tugas ?? ''} 
                                </p>
                                <p>
                                    <strong>Tanggal Surat:</strong> ${formattedDate}
                                </p>
                                <p><strong>Kabupaten:</strong> ${response.kabupaten ?? ''}</p>
        
                                
                            </div>    
                            <div class="col-md-6">
                                <p><strong>Jenis Kelamin:</strong> ${response.jkl ?? ''}</p>
                                ${kelengkapanPesertaBiodata}
                                ${kelengkapanPesertaTransport}
                                <p><strong>Nomor Handphone:</strong> ${response.no_hp ?? ''}</p>
                                <p><strong>Nomor WhatsApp:</strong> ${response.no_wa ?? ''}</p>
                                <p><strong>Instansi:</strong> ${response.instansi ?? ''}</p>
                                <p><strong>Jenis Golongan:</strong> ${response.jenis_gol ?? ''}</p>
                                <p><strong>Golongan:</strong> ${response.golongan ?? ''}</p>
                            </div>    
                        </div>
                        <div class="text-right mt-3">
                            <a href="/peserta/cetakByUser/${response.id}" class="btn btn-primary" target="_blank">
                                <i class="fas fa-print"></i> Cetak Biodata / Pakta / Surat Sehat
                            </a>
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

@endsection
