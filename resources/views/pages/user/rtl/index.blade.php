@extends('layouts.app', ['title' => 'RTL & Sertifikat'])

@section('content')
   <div class="main-content">
      <section class="section">
         <div class="section-header">
            <h1>Rencana Tindak Lanjut (RTL) & Sertifikat</h1>
         </div>

         <div class="section-body">
            <h2 class="section-title">Kelola dokumen RTL Anda</h2>
            <p class="section-lead">
               Unggah dokumen RTL untuk mendapatkan sertifikat kegiatan. Admin akan meninjau dokumen yang Anda unggah.
            </p>

            <div class="row">
               <div class="col-12">
                  <div class="card">
                     <div class="card-header">
                        <h4>Daftar RTL Saya</h4>
                        <div class="card-header-action">
                           @if (count($availableKegiatans) > 0)
                              <button class="btn btn-primary" onclick="openUploadModal()">
                                 <i class="fas fa-plus"></i> Upload RTL Baru
                              </button>
                           @endif
                        </div>
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
                        @if ($errors->any())
                           <div class="alert alert-danger alert-dismissible show fade">
                              <div class="alert-body">
                                 <button class="close" data-dismiss="alert">
                                    <span>&times;</span>
                                 </button>
                                 <ul>
                                    @foreach ($errors->all() as $error)
                                       <li>{{ $error }}</li>
                                    @endforeach
                                 </ul>
                              </div>
                           </div>
                        @endif
                        @if (session('error'))
                           <div class="alert alert-danger alert-dismissible show fade">
                              <div class="alert-body">
                                 <button class="close" data-dismiss="alert">
                                    <span>&times;</span>
                                 </button>
                                 {{ session('error') }}
                              </div>
                           </div>
                        @endif

                        <div class="table-responsive">
                           <table class="table table-striped" id="table-rtl">
                              <thead>
                                 <tr>
                                    <th>#</th>
                                    <th>Nama Kegiatan</th>
                                    <th>Status</th>
                                    <th>Dokumen RTL</th>
                                    <th>Catatan Admin</th>
                                    <th>Sertifikat</th>
                                    <th>Aksi</th>
                                 </tr>
                              </thead>
                              <tbody>
                                 @foreach ($datas as $index => $rtl)
                                    <tr>
                                       <td>{{ $index + 1 }}</td>
                                       <td>{{ $rtl->kegiatan->nama_kegiatan }}</td>
                                       <td>
                                          @if ($rtl->status == 'pending')
                                             <div class="badge badge-warning">Menunggu Review</div>
                                          @elseif($rtl->status == 'approved')
                                             <div class="badge badge-success">Disetujui</div>
                                          @elseif($rtl->status == 'rejected')
                                             <div class="badge badge-danger">Ditolak</div>
                                          @endif
                                       </td>
                                       <td>
                                          <div class="avatars-container">
                                             @foreach ($rtl->documents as $doc)
                                                <a href="{{ $doc->url }}" target="_blank"
                                                   class="btn btn-sm btn-outline-primary mb-1"
                                                   title="{{ $doc->original_name }}">
                                                   <i class="fas fa-file-pdf"></i> File {{ $loop->iteration }}
                                                </a>
                                             @endforeach
                                          </div>
                                       </td>
                                       <td>{{ $rtl->admin_notes ?? '-' }}</td>
                                       <td>
                                          @if ($rtl->status == 'approved' && $rtl->certificate_file)
                                             <a href="{{ $rtl->cert_url }}" class="btn btn-success btn-icon icon-left"
                                                download>
                                                <i class="fas fa-certificate"></i> Download Sertifikat
                                             </a>
                                          @else
                                             <span class="text-muted small">Belum tersedia</span>
                                          @endif
                                       </td>
                                       <td>
                                          @if ($rtl->status != 'approved')
                                             <button class="btn btn-primary btn-sm"
                                                onclick="addFile({{ $rtl->id_kegiatan }}, '{{ $rtl->kegiatan->nama_kegiatan }}')">
                                                <i class="fas fa-upload"></i> Tambah File
                                             </button>
                                          @endif
                                          <button class="btn btn-info btn-sm" onclick="previewRtl({{ $rtl->id }})">
                                             <i class="fas fa-eye"></i> Detail
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

   <!-- Modal Upload -->
   <div class="modal fade" tabindex="-1" role="dialog" id="uploadRtlModal">
      <div class="modal-dialog" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="uploadModalTitle">Unggah Dokumen RTL</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
               </button>
            </div>
            <form action="{{ route('user.rtl.store') }}" method="POST" enctype="multipart/form-data">
               @csrf
               <div class="modal-body">
                  <div id="kegiatanSelectGroup" class="form-group">
                     <label>Pilih Kegiatan</label>
                     <select class="form-control select2" name="id_kegiatan" id="id_kegiatan_select" style="width: 100%">
                        <option value="">-- Pilih Kegiatan --</option>
                        @foreach ($availableKegiatans as $k)
                           <option value="{{ $k->id }}">{{ $k->nama_kegiatan }}</option>
                        @endforeach
                     </select>
                  </div>
                  <div id="kegiatanDisplayGroup" class="form-group" style="display:none;">
                     <label>Kegiatan</label>
                     <input type="text" id="display_kegiatan_nama" class="form-control" readonly>
                     <input type="hidden" name="id_kegiatan" id="hidden_id_kegiatan">
                  </div>
                  <div class="form-group">
                     <label>Dokumen (PDF/DOCX)</label>
                     <input type="file" name="files[]" class="form-control" required accept=".pdf,.doc,.docx">
                     <small class="form-text text-muted">Maks: 10MB.</small>
                  </div>
               </div>
               <div class="modal-footer bg-whitesmoke br">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                  <button type="submit" class="btn btn-primary">Simpan</button>
               </div>
            </form>
         </div>
      </div>
   </div>

   <!-- Modal Preview -->
   <div class="modal fade" tabindex="-1" role="dialog" id="previewRtlModal">
      <div class="modal-dialog modal-lg" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title">Detail RTL</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
               </button>
            </div>
            <div class="modal-body">
               <div class="row">
                  <div class="col-md-6">
                     <h6>Informasi</h6>
                     <dl class="row">
                        <dt class="col-sm-4">Kegiatan</dt>
                        <dd class="col-sm-8" id="prevKegiatan"></dd>
                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8" id="prevStatus"></dd>
                        <dt class="col-sm-4">Catatan</dt>
                        <dd class="col-sm-8" id="prevNotes"></dd>
                     </dl>
                  </div>
                  <div class="col-md-6">
                     <h6>Daftar File</h6>
                     <ul class="list-group" id="prevDocsList"></ul>
                  </div>
               </div>
               <hr>
               <div id="filePreviewFrame" style="display:none;">
                  <h6>Pratinjau File</h6>
                  <iframe src="" frameborder="0" style="width:100%; height:500px;" id="previewIframe"></iframe>
               </div>
            </div>
            <div class="modal-footer bg-whitesmoke br">
               <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
         </div>
      </div>
   </div>

   @push('scripts')
      <script>
         $(document).ready(function() {
            $('.select2').select2({
               dropdownParent: $('#uploadRtlModal')
            });
         });

         function openUploadModal() {
            $('#uploadModalTitle').text('Unggah RTL Baru');
            $('#kegiatanSelectGroup').show();
            $('#kegiatanDisplayGroup').hide();

            // Enable dropdown, disable hidden
            $('#id_kegiatan_select').attr('disabled', false).attr('required', true);
            $('#hidden_id_kegiatan').attr('disabled', true);

            $('#uploadRtlModal').modal('show');
         }

         function addFile(kegiatanId, kegiatanNama) {
            $('#uploadModalTitle').text('Tambah File Dokumen');
            $('#kegiatanSelectGroup').hide();
            $('#kegiatanDisplayGroup').show();

            // Disable dropdown, enable hidden
            $('#id_kegiatan_select').attr('disabled', true).attr('required', false);
            $('#hidden_id_kegiatan').attr('disabled', false).val(kegiatanId);

            $('#display_kegiatan_nama').val(kegiatanNama);
            $('#uploadRtlModal').modal('show');
         }

         function previewRtl(id) {
            $.get(`/rtl/${id}`, function(data) {
               $('#prevKegiatan').text(data.kegiatan.nama_kegiatan);
               $('#prevStatus').text(data.status.toUpperCase());
               $('#prevNotes').text(data.admin_notes || '-');

               $('#prevDocsList').empty();
               data.documents.forEach(doc => {
                  let li = `<li class="list-group-item d-flex justify-content-between align-items-center">
                    ${doc.original_name}
                    <button class="btn btn-sm btn-primary" onclick="showFile('${doc.url}')">
                        <i class="fas fa-eye"></i> Preview
                    </button>
                </li>`;
                  $('#prevDocsList').append(li);
               });

               $('#filePreviewFrame').hide();
               $('#previewRtlModal').modal('show');
            });
         }

         function showFile(url) {
            let lowUrl = url.toLowerCase();
            $('#previewIframe').hide();
            $('#imagePreview').remove();

            if (lowUrl.endsWith('.pdf')) {
               $('#previewIframe').attr('src', url).show();
               $('#filePreviewFrame').show();
            } else if (lowUrl.match(/\.(jpg|jpeg|png|gif)$/)) {
               $('#previewIframe').after(
                  `<img id="imagePreview" src="${url}" class="img-fluid border mx-auto d-block" style="max-height: 500px; background: white;">`
               );
               $('#filePreviewFrame').show();
            } else {
               window.open(url, '_blank');
               return;
            }

            // Scroll to preview
            $('#previewRtlModal').animate({
               scrollTop: $('#previewRtlModal').scrollTop() + $("#filePreviewFrame").position().top
            }, 500);
         }
      </script>
   @endpush
@endsection
