@extends('layouts.app', ['title' => 'Manajemen RTL'])

@section('content')
   <div class="main-content">
      <section class="section">
         <div class="section-header">
            <h1>Manajemen Rencana Tindak Lanjut (RTL)</h1>
         </div>

         <div class="section-body">
            <h2 class="section-title">Review Dokumen RTL Peserta</h2>
            <p class="section-lead">
               Verifikasi dokumen RTL yang diunggah peserta untuk penerbitan sertifikat.
            </p>

            <div class="row">
               <div class="col-12">
                  <div class="card">
                     <div class="card-header">
                        <h4>Daftar Pengajuan RTL</h4>
                     </div>
                     <div class="card-body">
                        @if (session('message'))
                           <div class="alert alert-success alert-dismissible show fade">
                              <div class="alert-body">
                                 <button class="close" data-dismiss="alert">
                                    <span>&times;</span>
                                 </button>
                                 {{ session('message') }}
                              </div>
                           </div>
                        @endif

                        <div class="table-responsive">
                           <table class="table table-striped" id="table-rtl-admin">
                              <thead>
                                 <tr>
                                    <th>#</th>
                                    <th>Nama Peserta</th>
                                    <th>Nama Kegiatan</th>
                                    <th>Jumlah File</th>
                                    <th>Status</th>
                                    <th>Update Terakhir</th>
                                    <th style="width: 15%">Aksi</th>
                                 </tr>
                              </thead>
                              <tbody>
                                 @foreach ($datas as $index => $rtl)
                                    <tr>
                                       <td>{{ $index + 1 }}</td>
                                       <td>
                                          <strong>{{ $rtl->user->nama_lengkap ?? 'User (' . $rtl->no_ktp . ')' }}</strong><br>
                                          <small class="text-muted">{{ $rtl->no_ktp }}</small>
                                       </td>
                                       <td>{{ $rtl->kegiatan->nama_kegiatan }}</td>
                                       <td>
                                          <span class="badge badge-info">{{ count($rtl->documents) }} File</span>
                                       </td>
                                       <td>
                                          @if ($rtl->status == 'pending')
                                             <div class="badge badge-warning">Pending Review</div>
                                          @elseif($rtl->status == 'approved')
                                             <div class="badge badge-success">Selesai/Approved</div>
                                          @elseif($rtl->status == 'rejected')
                                             <div class="badge badge-danger">Ditolak/Rejected</div>
                                          @endif
                                       </td>
                                       <td>{{ $rtl->updated_at->format('d M Y H:i') }}</td>
                                       <td>
                                          <button class="btn btn-primary btn-sm btn-block"
                                             onclick="reviewRtl({{ $rtl->id }})">
                                             <i class="fas fa-search"></i> Detail & Review
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

   <!-- Modal Review -->
   <div class="modal fade" tabindex="-1" role="dialog" id="reviewRtlModal">
      <div class="modal-dialog modal-xl" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title">Review RTL: <span id="revUser"></span></h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
               </button>
            </div>
            <form id="reviewForm" action="" method="POST" enctype="multipart/form-data">
               @csrf
               <div class="modal-body">
                  <div class="row">
                     <div class="col-md-4">
                        <div class="card card-primary border shadow-none">
                           <div class="card-header">
                              <h4>Status Pengajuan</h4>
                           </div>
                           <div class="card-body p-3">
                              <table class="table table-sm mb-3">
                                 <tr>
                                    <td>Kegiatan</td>
                                    <td id="revKegiatan" class="font-weight-bold text-primary"></td>
                                 </tr>
                                 <tr>
                                    <td>No KTP</td>
                                    <td id="revKtp"></td>
                                 </tr>
                              </table>

                              <div class="form-group mb-3">
                                 <label class="font-weight-bold">Keputusan Review</label>
                                 <select class="form-control" name="status" id="revStatusSelect" required
                                    onchange="toggleCertUpload()">
                                    <option value="pending">Tetap Pending</option>
                                    <option value="approved">Setujui (Approve)</option>
                                    <option value="rejected">Tolak (Reject)</option>
                                 </select>
                              </div>

                              <div class="form-group mb-3" id="certUploadGroup" style="display:none;">
                                 <label class="font-weight-bold text-success">Upload Sertifikat</label>
                                 <div id="existingCert" class="mb-2" style="display:none;">
                                    <span class="badge badge-success">Sertifikat sudah ada</span>
                                 </div>
                                 <input type="file" name="certificate" class="form-control" accept=".pdf,.doc,.docx">
                                 <small class="text-muted">Upload file baru untuk mengganti yang lama.</small>
                              </div>

                              <div class="form-group mb-0">
                                 <label class="font-weight-bold">Catatan Untuk Peserta</label>
                                 <textarea name="admin_notes" id="revAdminNotes" class="form-control" rows="4"
                                    placeholder="Alasan penolakan atau informasi tambahan..."></textarea>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="col-md-8">
                        <div class="card shadow-none border">
                           <div class="card-header d-flex justify-content-between">
                              <h4>Daftar Dokumen Unggahan</h4>
                              <span class="text-muted" id="revDocsCount"></span>
                           </div>
                           <div class="card-body p-0">
                              <div class="list-group list-group-flush" id="revDocsList">
                                 <!-- Document list -->
                              </div>
                           </div>
                           <div id="revFilePreviewFrame" class="card-footer p-2"
                              style="display:none; background: #f4f6f9;">
                              <div class="d-flex justify-content-between align-items-center mb-2 px-2">
                                 <h6 class="mb-0">Pratinjau: <span id="currentFileName" class="text-primary"></span></h6>
                                 <button type="button" class="btn btn-sm btn-secondary"
                                    onclick="$('#revFilePreviewFrame').hide()">Tutup Preview</button>
                              </div>
                              <iframe src="" frameborder="0"
                                 style="width:100%; height:550px; background: white; border-radius: 4px;"
                                 id="revPreviewIframe"></iframe>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="modal-footer bg-whitesmoke br">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                  <button type="submit" class="btn btn-primary px-4">Simpan Keputusan Review</button>
               </div>
            </form>
         </div>
      </div>
   </div>

   @push('scripts')
      <script>
         function reviewRtl(id) {
            $.get(`/dashboard/rtl/${id}`, function(data) {
               $('#revUser').text(data.user ? data.user.nama_lengkap : data.no_ktp);
               $('#revKegiatan').text(data.kegiatan.nama_kegiatan);
               $('#revKtp').text(data.no_ktp);
               $('#revStatusSelect').val(data.status);
               $('#revAdminNotes').val(data.admin_notes);
               $('#reviewForm').attr('action', `/dashboard/rtl/update/${id}`);
               $('#revDocsCount').text(`${data.documents.length} File`);

               if (data.certificate_file) {
                  $('#existingCert').show();
               } else {
                  $('#existingCert').hide();
               }

               $('#revDocsList').empty();
               if (data.documents.length === 0) {
                  $('#revDocsList').append(
                     '<div class="p-3 text-center text-muted">Belum ada dokumen yang diunggah.</div>');
               } else {
                  data.documents.forEach(doc => {
                     let li = `<a href="javascript:void(0)" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="revShowFile('${doc.url}', '${doc.original_name}')">
                        <div>
                            <i class="fas fa-file-pdf text-danger mr-2"></i> 
                            <span class="font-weight-600">${doc.original_name}</span>
                        </div>
                        <span class="btn btn-sm btn-outline-primary">Lihat Preview</span>
                    </a>`;
                     $('#revDocsList').append(li);
                  });
               }

               $('#revFilePreviewFrame').hide();
               toggleCertUpload();
               $('#reviewRtlModal').modal('show');
            });
         }

         function toggleCertUpload() {
            if ($('#revStatusSelect').val() == 'approved') {
               $('#certUploadGroup').show();
               $('input[name="certificate"]').attr('required', $('#existingCert').is(':hidden'));
            } else {
               $('#certUploadGroup').hide();
               $('input[name="certificate"]').attr('required', false);
            }
         }

         function revShowFile(url, fileName) {
            $('#currentFileName').text(fileName);
            let lowUrl = url.toLowerCase();

            $('#revPreviewIframe').hide();
            $('#revImagePreview').remove();

            if (lowUrl.endsWith('.pdf')) {
               $('#revPreviewIframe').attr('src', url).show();
               $('#revFilePreviewFrame').show();
            } else if (lowUrl.match(/\.(jpg|jpeg|png|gif)$/)) {
               $('#revPreviewIframe').after(
                  `<img id="revImagePreview" src="${url}" class="img-fluid border mx-auto d-block" style="max-height: 550px; background: white;">`
               );
               $('#revFilePreviewFrame').show();
            } else {
               window.open(url, '_blank');
               return;
            }

            // Smooth scroll inside modal if possible, otherwise body
            $('#reviewRtlModal').animate({
               scrollTop: $('#reviewRtlModal').scrollTop() + $("#revFilePreviewFrame").position().top
            }, 500);
         }
      </script>
   @endpush
@endsection
