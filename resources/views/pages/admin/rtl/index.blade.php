@extends('layouts.app', ['title' => 'Manajemen RTL'])

@push('styles')
   <style>
      @include('pages.user.rtl.certificate-styles')

      .cert-editor-dialog {
         width: calc(100% - 30px);
         max-width: 1500px;
      }

      #certificateEditorModal {
         z-index: 1060;
      }

      .modal-backdrop.certificate-editor-backdrop {
         z-index: 1055;
      }

      #certificateEditorModal .modal-content {
         max-height: calc(100vh - 30px);
      }

      #certificateEditorModal .modal-body {
         overflow-y: auto;
      }

      #reviewRtlModal .modal-content {
         height: 670px;
         max-height: calc(100vh - 30px);
      }

      #reviewRtlModal #reviewForm {
         display: flex;
         flex: 1 1 auto;
         flex-direction: column;
         min-height: 0;
      }

      #reviewRtlModal .modal-body {
         flex: 1 1 auto;
         min-height: 0;
         overflow-y: auto;
      }

      #reviewRtlModal .modal-body > .row,
      #reviewRtlModal .modal-body > .row > [class*="col-"] {
         height: 100%;
      }

      #reviewRtlModal .modal-body > .row > [class*="col-"] {
         display: flex;
      }

      #reviewRtlModal .modal-body .card {
         width: 100%;
      }

      #reviewRtlModal #revAdminNotes {
         height: 90px;
         min-height: 90px;
         resize: vertical;
      }

      .certificate-editor-form {
         height: 100%;
         padding: 1.25rem;
         border: 1px solid #e4e6fc;
         border-radius: .25rem;
         background: #fff;
      }

      .certificate-editor-form .form-group {
         margin-bottom: .85rem;
      }

      .certificate-rich-field {
         height: auto;
         min-height: 31px;
         line-height: 1.35;
         white-space: pre-wrap;
         overflow-wrap: anywhere;
         word-break: break-all;
      }

      .certificate-rich-field-multiline {
         min-height: 58px;
      }

      .certificate-rich-field:empty::before {
         color: #98a0b3;
         content: attr(data-placeholder);
         pointer-events: none;
      }

      .certificate-editor-form .certificate-validation-invalid {
         border-color: #e3342f !important;
         box-shadow: 0 0 0 .2rem rgba(227, 52, 47, .15);
      }

      .certificate-editor-form label {
         margin-bottom: .35rem;
         font-size: .78rem;
         font-weight: 600;
         color: #34395e;
      }

      .certificate-preview-shell {
         display: flex;
         align-items: flex-start;
         justify-content: center;
         padding: 1rem;
         border-radius: .25rem;
         background: #f1f3f8;
      }

      .certificate-preview-viewport {
         position: relative;
         width: 100%;
         overflow: hidden;
         aspect-ratio: 297 / 210;
      }

      .certificate-preview-viewport .certificate-page {
         position: absolute;
         top: 0;
         left: 0;
         transform: scale(var(--certificate-preview-scale, 1));
         transform-origin: top left;
         box-shadow: 0 8px 25px rgba(31, 45, 61, .18);
      }

      .certificate-preview-viewport .certificate-materials-page {
         position: absolute;
         top: 0;
         left: 0;
         transform: scale(var(--certificate-preview-scale, 1));
         transform-origin: top left;
         box-shadow: 0 8px 25px rgba(31, 45, 61, .18);
      }

      .certificate-materials-editor-form .table {
         margin-bottom: .75rem;
         font-size: .72rem;
      }

      .certificate-materials-editor-form .table th {
         color: #34395e;
         font-size: .68rem;
         text-align: center;
      }

      .certificate-materials-editor-form .table th:first-child,
      .certificate-materials-editor-form .table td:first-child {
         width: 2rem;
         text-align: center;
      }

      .certificate-materials-editor-form .table th:last-child,
      .certificate-materials-editor-form .table td:last-child {
         width: 2.5rem;
         text-align: center;
      }

      .certificate-materials-editor-form .table .form-control {
         min-width: 0;
         padding: .2rem .35rem;
         font-size: .72rem;
      }

      .certificate-materials-editor-form [contenteditable="true"] {
         height: auto;
         width: 100%;
         max-width: 250px;
         min-height: 30px;
         line-height: 1.35;
         white-space: pre-wrap;
         overflow-wrap: anywhere;
         word-break: break-all;
      }

      .certificate-materials-editor-form [contenteditable="true"]:empty::before {
         color: #98a0b3;
         content: attr(data-placeholder);
         pointer-events: none;
      }

      .certificate-materials-empty-row td {
         padding: .75rem .35rem;
         color: #98a0b3;
         text-align: center;
      }

      @media (max-width: 991.98px) {
         .certificate-preview-shell {
            margin-top: 1rem;
         }
      }
   </style>
@endpush

@section('content')
   <div class="main-content">
      <section class="section">
         <div class="section-header">
            <h1>Manajemen Rencana Tindak Lanjut (RTL)</h1>
         </div>

         <div class="section-body">
            <h2 class="section-title">Review Dokumen RTL Peserta</h2>
            <p class="section-lead">
               Verifikasi dokumen RTL yang diunggah peserta. Saat disetujui, sertifikat akan dibuat otomatis.
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
            <form id="reviewForm" action="" method="POST">
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

                              <div class="form-group mb-3" id="certGenerateGroup" style="display:none;">
                                 <button type="button" class="btn btn-outline-success btn-sm btn-block mb-2"
                                    id="openCertificateEditorButton" onclick="openCertificateEditor()">
                                    <i class="fas fa-edit mr-1"></i>
                                    <span id="certificateEditorButtonText">Buat Sertifikat</span>
                                 </button>
                                 <small class="text-muted">Sistem menggunakan data peserta dan kegiatan yang telah disetujui.</small>
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
               <div id="certificateDataInputs"></div>
               <div class="modal-footer bg-whitesmoke br">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                  <button type="submit" class="btn btn-primary px-4">Simpan Keputusan Review</button>
               </div>
            </form>
         </div>
      </div>
   </div>

   <!-- Modal Editor Sertifikat -->
   <div class="modal fade" id="certificateEditorModal" tabindex="-1" role="dialog"
      aria-labelledby="certificateEditorModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered cert-editor-dialog" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <div>
                  <h5 class="modal-title" id="certificateEditorModalLabel">Buat/Edit Sertifikat</h5>
                  <small class="text-muted">Perubahan akan digunakan saat keputusan review disimpan.</small>
               </div>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
               </button>
            </div>
            <div class="modal-body">
               <div class="row">
                  <div class="col-lg-4">
                     <div class="certificate-editor-form" id="certificateStepOneForm">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                           <h6 class="mb-0">Data Sertifikat</h6>
                           <button type="button" class="btn btn-light btn-sm rounded-sm px-3"
                              id="resetCertificateEditorButton" onclick="resetCertificateDraft()"
                              title="Kembalikan data awal">
                              <i class="fas fa-undo-alt mr-1" aria-hidden="true"></i> Reset
                           </button>
                        </div>

                        <div class="form-group">
                           <label for="certFieldNumber">Nomor Sertifikat</label>
                           <div class="form-control form-control-sm certificate-rich-field" id="certFieldNumber"
                              contenteditable="true" role="textbox" spellcheck="false"
                              data-certificate-field="number" data-placeholder="Nomor Sertifikat"></div>
                        </div>
                        <div class="form-group">
                           <label for="certFieldParticipantName">Nama Peserta</label>
                           <div class="form-control form-control-sm certificate-rich-field" id="certFieldParticipantName"
                              contenteditable="true" role="textbox" spellcheck="false"
                              data-certificate-field="participantName" data-placeholder="Nama Peserta"></div>
                        </div>
                        <div class="form-group">
                           <label for="certFieldInstitution">Instansi/Sekolah</label>
                           <div class="form-control form-control-sm certificate-rich-field" id="certFieldInstitution"
                              contenteditable="true" role="textbox" spellcheck="false"
                              data-certificate-field="institution" data-placeholder="Instansi/Sekolah"></div>
                        </div>
                        <div class="form-group">
                           <label for="certFieldParticipantRole">Peran Peserta</label>
                           <div class="form-control form-control-sm certificate-rich-field" id="certFieldParticipantRole"
                              contenteditable="true" role="textbox" spellcheck="false"
                              data-certificate-field="participantRole" data-placeholder="Peran Peserta"></div>
                        </div>
                        <div class="form-group">
                           <label for="certFieldActivityName">Nama Kegiatan</label>
                           <div class="form-control form-control-sm certificate-rich-field certificate-rich-field-multiline"
                              id="certFieldActivityName" contenteditable="true" role="textbox" aria-multiline="true"
                              spellcheck="false" data-certificate-field="activityName"
                              data-placeholder="Nama Kegiatan"></div>
                        </div>
                        <div class="form-group">
                           <label for="certFieldActivityDate">Tanggal Kegiatan</label>
                           <div class="form-control form-control-sm certificate-rich-field" id="certFieldActivityDate"
                              contenteditable="true" role="textbox" spellcheck="false"
                              data-certificate-field="activityDate" data-placeholder="Contoh: 1 Januari 2026"></div>
                        </div>
                        <div class="form-group">
                           <label for="certFieldActivityPlace">Tempat Kegiatan</label>
                           <div class="form-control form-control-sm certificate-rich-field" id="certFieldActivityPlace"
                              contenteditable="true" role="textbox" spellcheck="false"
                              data-certificate-field="activityPlace" data-placeholder="Tempat Kegiatan"></div>
                        </div>
                        <div class="form-group">
                           <label for="certFieldIssueDate">Tanggal Terbit</label>
                           <div class="form-control form-control-sm certificate-rich-field" id="certFieldIssueDate"
                              contenteditable="true" role="textbox" spellcheck="false"
                              data-certificate-field="issueDate" data-placeholder="Contoh: 1 Januari 2026"></div>
                        </div>
                        <div class="form-row">
                           <div class="form-group col-md-7">
                              <label for="certFieldSignerName">Nama Penandatangan</label>
                              <div class="form-control form-control-sm certificate-rich-field" id="certFieldSignerName"
                                 contenteditable="true" role="textbox" spellcheck="false"
                                 data-certificate-field="signerName" data-placeholder="Nama Penandatangan"></div>
                           </div>
                           <div class="form-group col-md-5">
                              <label for="certFieldSignerNip">NIP</label>
                              <div class="form-control form-control-sm certificate-rich-field" id="certFieldSignerNip"
                                 contenteditable="true" role="textbox" spellcheck="false"
                                 data-certificate-field="signerNip" data-placeholder="NIP"></div>
                           </div>
                        </div>
                        <div class="form-group">
                           <label for="certFieldQrLink">Link QR</label>
                           <input type="url" class="form-control form-control-sm" id="certFieldQrLink"
                              data-certificate-field="qrLink" placeholder="https://...">
                           <small class="form-text text-muted">QR akan tampil di bawah tulisan Kepala Balai.</small>
                        </div>
                     </div>
                     <div class="certificate-editor-form certificate-materials-editor-form" id="certificateStepTwoForm"
                        style="display:none;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                           <div>
                              <h6 class="mb-0">Daftar Materi</h6>
                              <small class="text-muted">Tambahkan materi dan jumlah JP untuk halaman berikutnya.</small>
                           </div>
                           <button type="button" class="btn btn-light btn-sm rounded-sm px-3"
                              onclick="resetCertificateDraft()" title="Kembalikan data awal">
                              <i class="fas fa-undo-alt mr-1" aria-hidden="true"></i> Reset
                           </button>
                        </div>

                        <div class="form-group">
                           <label for="certFieldMaterialsTitle">Kegiatan</label>
                           <div class="form-control form-control-sm certificate-rich-field" id="certFieldMaterialsTitle"
                              contenteditable="true" role="textbox" spellcheck="false"
                              data-certificate-field="materialsTitle" data-placeholder="Kegiatan"></div>
                        </div>

                        <div class="table-responsive">
                           <table class="table table-sm table-bordered">
                              <thead>
                                 <tr>
                                    <th>No</th>
                                    <th>Materi</th>
                                    <th>JP</th>
                                    <th aria-label="Aksi"></th>
                                 </tr>
                              </thead>
                              <tbody id="certificateMaterialsRows"></tbody>
                           </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                           <button type="button" class="btn btn-outline-primary btn-sm"
                              id="certificateAddMaterialRowButton"
                              onclick="addCertificateMaterialRow()">
                              <i class="fas fa-plus mr-1"></i> Tambah Baris
                           </button>
                           <span class="text-muted small">Total JP: <strong id="certificateMaterialsTotal">0</strong></span>
                        </div>
                     </div>
                  </div>
                  <div class="col-lg-8">
                     <div class="certificate-preview-shell">
                        <div class="certificate-preview-viewport" aria-label="Preview sertifikat">
                           @include('pages.user.rtl._certificate-content', [
                              'backgroundSrc' => asset('img_template/bg-sertifikat.png'),
                              'logoSrc' => asset('img/logo-tutwuri.png'),
                              'topLogoLeftSrc' => asset('img/pendidikan-bermutu.png'),
                              'topLogoRightSrc' => asset('img/kemendikdasmen-ramah.png'),
                              'qrImageSrc' => '',
                           ])
                        </div>
                        <div class="certificate-preview-viewport" id="certificateMaterialsPreview"
                           aria-label="Preview daftar materi" style="display:none;">
                           @include('pages.user.rtl._certificate-materials-content', [
                              'materialsBackgroundSrc' => asset('img_template/bg-sertifikat-2.png'),
                              'materialsTitle' => '',
                              'materials' => [],
                           ])
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="modal-footer bg-whitesmoke br">
               <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
               <small class="text-muted ml-3">Note: Blok teks dan gunakan Ctrl+B, Ctrl+I, Ctrl+U untuk bold, italic, dan underline.</small>
               <div class="ml-auto">
                  <button type="button" class="btn btn-outline-primary mr-2" id="certificatePreviousButton"
                     onclick="goToCertificateStep(1)" style="display:none;">
                     <i class="fas fa-arrow-left mr-1"></i> Kembali
                  </button>
                  <button type="button" class="btn btn-primary" id="certificateNextButton"
                     onclick="goToCertificateStep(2)">
                     Selanjutnya <i class="fas fa-arrow-right ml-1"></i>
                  </button>
                  <button type="button" class="btn btn-success" id="certificateCommitButton"
                     onclick="commitCertificateDraft()" style="display:none;">
                  <i class="fas fa-check mr-1"></i> Simpan Sertifikat
                  </button>
               </div>
            </div>
         </div>
      </div>
   </div>

   @push('scripts')
      <script src="{{ asset('vendor/qrcode.js') }}"></script>
      <script>
         const certificateQrDebounceMs = 350;
         const certificateValidationDebounceMs = 300;
         const certificateFieldNames = [
            'number',
            'participantName',
            'institution',
            'participantRole',
            'activityName',
            'activityDate',
            'activityPlace',
            'issueDate',
            'signerName',
            'signerNip',
            'qrLink',
            'materialsTitle'
         ];

         const certificateValidationRules = {
            number: { label: 'Nomor Sertifikat', max: 255 },
            participantName: { label: 'Nama Peserta', max: 255 },
            institution: { label: 'Instansi/Sekolah', max: 255 },
            participantRole: { label: 'Peran Peserta', max: 255 },
            activityName: { label: 'Nama Kegiatan', max: 500 },
            activityDate: { label: 'Tanggal Kegiatan', max: 255 },
            activityPlace: { label: 'Tempat Kegiatan', max: 255 },
            issueDate: { label: 'Tanggal Terbit', max: 255 },
            signerName: { label: 'Nama Penandatangan', max: 255 },
            signerNip: { label: 'NIP', max: 255 },
            qrLink: { label: 'Link QR', max: 2048 },
            materialsTitle: { label: 'Kegiatan', max: 500 }
         };

         let certificateValidationTimer = null;
         let certificateValidationTarget = null;

         const rtlReviewState = {
            activeId: null,
            certificateData: {},
            certificateEditorInitialData: {},
            certificateDraft: null,
            certificateStep: 1,
            qrGenerationPending: false,
            qrGenerationTimer: null,
            qrGenerationToken: 0,
            hasCertificate: false,
            certificateSaving: false,
            requestToken: 0
         };

         let rtlReviewRequest = null;

         function copyCertificateData(data) {
            return $.extend(true, {}, data || {});
         }

         function setCertificateSavingState(saving) {
            rtlReviewState.certificateSaving = saving;

            const modals = $('#certificateEditorModal, #reviewRtlModal');
            modals.find('button, input, textarea, select')
               .prop('disabled', saving)
               .attr('aria-disabled', saving ? 'true' : 'false');
            $('#certificateEditorModal [contenteditable]')
               .attr('contenteditable', saving ? 'false' : 'true');
            modals.attr('aria-busy', saving ? 'true' : 'false');
         }

         function sanitizeRichText(value) {
            const source = document.createElement('div');
            source.innerHTML = value || '';

            function escapeText(text) {
               const escaped = document.createElement('div');
               escaped.textContent = text;
               return escaped.innerHTML;
            }

            function clean(node) {
               return Array.from(node.childNodes).map(function(child) {
                  if (child.nodeType === Node.TEXT_NODE) {
                     return escapeText(child.nodeValue || '');
                  }

                  if (child.nodeType !== Node.ELEMENT_NODE) {
                     return '';
                  }

                  const content = clean(child);
                  const tag = child.tagName.toLowerCase();
                  const allowedTags = {
                     b: 'strong',
                     strong: 'strong',
                     i: 'em',
                     em: 'em',
                     u: 'u',
                     br: 'br'
                  };

                  if (tag === 'br') {
                     return '<br>';
                  }

                  if (allowedTags[tag]) {
                     return `<${allowedTags[tag]}>${content}</${allowedTags[tag]}>`;
                  }

                  return content;
               }).join('');
            }

            return clean(source);
         }

         function getCertificateFieldValue(element) {
            const field = $(element);
            return field.is('[contenteditable]')
               ? sanitizeRichText(field.html())
               : field.val();
         }

         function certificateValueLength(value) {
            return Array.from(String(value == null ? '' : value)).length;
         }

         function certificateValidationError(element) {
            const field = $(element);

            if (field.is('[data-certificate-field]')) {
               const name = field.data('certificate-field');
               const rule = certificateValidationRules[name];
               const value = getCertificateFieldValue(element);

               if (rule && certificateValueLength(value) > rule.max) {
                  return `${rule.label} maksimal ${rule.max} karakter.`;
               }

               return '';
            }

            if (field.is('[data-material-field]')) {
               const name = field.data('material-field');
               const index = Number(field.data('material-index')) + 1;
               const value = field.is('[contenteditable]')
                  ? sanitizeRichText(field.html())
                  : field.val();

               if (name === 'materi' && certificateValueLength(value) > 500) {
                  return `Materi baris ${index} maksimal 500 karakter.`;
               }

               if (name === 'jp') {
                  const numericValue = String(value == null ? '' : value).trim();
                  if (!numericValue) {
                     return '';
                  }

                  if (!/^[+-]?(?:\d+\.?\d*|\.\d+)(?:e[+-]?\d+)?$/i.test(numericValue)) {
                     return `JP baris ${index} harus berupa angka.`;
                  }

                  const number = Number(numericValue);
                  if (number < 0) {
                     return `JP baris ${index} minimal 0.`;
                  }

                  if (number > 999) {
                     return `JP baris ${index} maksimal 999.`;
                  }
               }
            }

            return '';
         }

         function clearCertificateValidationTimer() {
            if (certificateValidationTimer !== null) {
               clearTimeout(certificateValidationTimer);
               certificateValidationTimer = null;
            }

            certificateValidationTarget = null;
         }

         function clearCertificateValidation(element) {
            const field = $(element);
            if (field.data('bs.tooltip')) {
               field.tooltip('dispose');
            }

            field.removeClass('is-invalid certificate-validation-invalid')
               .removeAttr('aria-invalid title data-original-title');
         }

         function clearCertificateValidations() {
            clearCertificateValidationTimer();
            $('#certificateEditorModal [data-certificate-field], #certificateMaterialsRows [data-material-field], #certificateAddMaterialRowButton')
               .each(function() {
                  clearCertificateValidation(this);
               });
         }

         function showCertificateValidationError(element, message, focus) {
            const field = $(element);
            clearCertificateValidation(field);
            field.addClass('is-invalid certificate-validation-invalid')
               .attr('aria-invalid', 'true')
               .attr('title', message);

            if ($.fn.tooltip) {
               field.tooltip({
                  boundary: 'window',
                  container: '#certificateEditorModal',
                  placement: 'top',
                  title: message,
                  trigger: 'manual'
               }).tooltip('show');
            }

            if (focus) {
               field.trigger('focus');
            }
         }

         function validateCertificateElement(element, focus) {
            const message = certificateValidationError(element);
            if (!message) {
               clearCertificateValidation(element);
               return true;
            }

            showCertificateValidationError(element, message, focus);
            return false;
         }

         function validateCertificateElements(selector, focus) {
            let firstInvalid = null;

            $(selector).each(function() {
               if (!validateCertificateElement(this, false) && !firstInvalid) {
                  firstInvalid = this;
               }
            });

            if (firstInvalid && focus) {
               $(firstInvalid).trigger('focus');
            }

            return !firstInvalid;
         }

         function validateCertificateStep(step, focus) {
            const selector = step === 2
               ? '#certificateStepTwoForm [data-certificate-field], #certificateMaterialsRows [data-material-field]'
               : '#certificateStepOneForm [data-certificate-field]';
            const valid = validateCertificateElements(selector, focus);

            if (step === 2 && Array.isArray(rtlReviewState.certificateDraft?.materials)
               && rtlReviewState.certificateDraft.materials.length > 50) {
               showCertificateValidationError(
                  '#certificateAddMaterialRowButton',
                  'Maksimal 50 baris materi.',
                  focus
               );
               return false;
            }

            return valid;
         }

         function validateCertificateDraft(focus) {
            const currentStep = rtlReviewState.certificateStep;
            if (!validateCertificateStep(currentStep, focus)) {
               return false;
            }

            if (currentStep === 2 && !validateCertificateStep(1, false)) {
               goToCertificateStep(1, false);
               validateCertificateStep(1, focus);
               return false;
            }

            const qrImage = rtlReviewState.certificateDraft?.qrImage || '';
            if (certificateValueLength(qrImage) > 300000) {
               if (currentStep !== 1) {
                  goToCertificateStep(1, false);
               }
               showCertificateValidationError(
                  '#certFieldQrLink',
                  'QR image maksimal 300000 karakter.',
                  focus
               );
               return false;
            }

            return true;
         }

         function scheduleCertificateValidation(element) {
            clearCertificateValidationTimer();
            certificateValidationTarget = element;
            certificateValidationTimer = setTimeout(function() {
               const target = certificateValidationTarget;
               clearCertificateValidationTimer();
               if (target && document.body.contains(target)) {
                  validateCertificateElement(target, true);
               }
            }, certificateValidationDebounceMs);
         }

         function sanitizeCertificateJpInput(element) {
            const field = $(element);
            const value = String(field.val() || '');
            const numericValue = value.replace(/\D/g, '');

            if (value !== numericValue) {
               field.val(numericValue);
            }
         }

         function setCertificateFieldValue(field, value) {
            const element = $(`[data-certificate-field="${field}"]`);
            if (element.is('[contenteditable]')) {
               element.html(sanitizeRichText(value || ''));
            } else {
               element.val(value || '');
            }
         }

         function renderCertificateFieldPreview(field, value) {
            $(`[data-certificate-preview="${field}"]`).html(sanitizeRichText(value || '-'));
            if (field === 'activityName' || field === 'materialsTitle') {
               const data = rtlReviewState.certificateDraft || {};
               $('[data-certificate-materials-title]').html(
                  sanitizeRichText(data.materialsTitle || data.activityName || '-')
               );
            }
         }

         function syncCertificateDataInputs() {
            const container = $('#certificateDataInputs').empty();

            certificateFieldNames.forEach(function(field) {
               $('<input>', {
                  type: 'hidden',
                  name: `certificate_data[${field}]`
               }).val(rtlReviewState.certificateData[field] || '').appendTo(container);
            });

            (rtlReviewState.certificateData.materials || []).forEach(function(material, index) {
               $('<input>', {
                  type: 'hidden',
                  name: `certificate_data[materials][${index}][materi]`
               }).val(material.materi || '').appendTo(container);
               $('<input>', {
                  type: 'hidden',
                  name: `certificate_data[materials][${index}][jp]`
               }).val(material.jp || '').appendTo(container);
            });

            $('<input>', {
               type: 'hidden',
               name: 'certificate_data[qrImage]'
            }).val(rtlReviewState.certificateData.qrImage || '').appendTo(container);
         }

         function renderCertificateEditor(data) {
            clearCertificateValidations();
            certificateFieldNames.forEach(function(field) {
               setCertificateFieldValue(field, data[field] || '');
               renderCertificateFieldPreview(field, data[field]);
            });

            $('[data-certificate-materials-title]').html(
               sanitizeRichText(data.materialsTitle || data.activityName || '-')
            );
            renderCertificateQr(data);
            renderMaterialsForm(data);
            renderMaterialsPreview(data);
         }

         function renderCertificateQr(data) {
            const qrImage = data.qrImage || '';
            const hasQr = /^data:image\//.test(qrImage);

            $('[data-certificate-qr]').attr('src', hasQr ? qrImage : '');
            $('[data-certificate-qr-wrap]').toggle(hasQr);
            $('[data-certificate-qr-spacer]').toggle(!hasQr);
         }

         function clearCertificateQrTimer() {
            if (rtlReviewState.qrGenerationTimer !== null) {
               clearTimeout(rtlReviewState.qrGenerationTimer);
               rtlReviewState.qrGenerationTimer = null;
            }
         }

         function scheduleCertificateQr(link, immediate) {
            if (!rtlReviewState.certificateDraft) {
               return;
            }

            clearCertificateQrTimer();
            const token = ++rtlReviewState.qrGenerationToken;
            const value = $.trim(link || '');

            rtlReviewState.certificateDraft.qrImage = '';
            rtlReviewState.qrGenerationPending = Boolean(value);
            renderCertificateQr(rtlReviewState.certificateDraft);

            if (!value) {
               rtlReviewState.qrGenerationPending = false;
               return;
            }

            if (immediate) {
               generateCertificateQr(value);
               return;
            }

            rtlReviewState.qrGenerationTimer = setTimeout(function() {
               rtlReviewState.qrGenerationTimer = null;
               if (token === rtlReviewState.qrGenerationToken && rtlReviewState.certificateDraft) {
                  generateCertificateQr(value);
               }
            }, certificateQrDebounceMs);
         }

         function generateCertificateQr(link) {
            if (!rtlReviewState.certificateDraft) {
               return;
            }

            const token = ++rtlReviewState.qrGenerationToken;
            const value = $.trim(link || '');
            if (!value) {
               rtlReviewState.qrGenerationPending = false;
               rtlReviewState.certificateDraft.qrImage = '';
               renderCertificateQr(rtlReviewState.certificateDraft);
               return;
            }

            // Remove the previous QR immediately so the preview never shows a
            // stale code while the new link is being encoded.
            rtlReviewState.certificateDraft.qrImage = '';
            renderCertificateQr(rtlReviewState.certificateDraft);

            if (typeof QRCode === 'undefined' || typeof QRCode.toDataURL !== 'function') {
               rtlReviewState.qrGenerationPending = false;
               rtlReviewState.certificateDraft.qrImage = '';
               renderCertificateQr(rtlReviewState.certificateDraft);
               return;
            }

            rtlReviewState.qrGenerationPending = true;
            QRCode.toDataURL(value, {
               errorCorrectionLevel: 'M',
               margin: 1,
               width: 180
            }, function(error, dataUrl) {
               if (token !== rtlReviewState.qrGenerationToken || !rtlReviewState.certificateDraft) {
                  return;
               }

               rtlReviewState.qrGenerationPending = false;
               rtlReviewState.certificateDraft.qrImage = error ? '' : dataUrl;
               renderCertificateQr(rtlReviewState.certificateDraft);
            });
         }

         function renderMaterialsForm(data) {
            const materials = Array.isArray(data.materials) ? data.materials : [];
            clearCertificateValidations();
            const rows = $('#certificateMaterialsRows').empty();

            if (materials.length === 0) {
               rows.append('<tr class="certificate-materials-empty-row"><td colspan="4">Belum ada materi. Klik Tambah Baris.</td></tr>');
               updateMaterialsTotal();
               return;
            }

            materials.forEach(function(material, index) {
               const row = $('<tr>');
               $('<td>').text(index + 1).appendTo(row);
               $('<td>').append($('<div>', {
                  class: 'form-control form-control-sm',
                  contenteditable: 'true',
                  role: 'textbox',
                  'aria-label': 'Materi',
                  'data-material-index': index,
                  'data-material-field': 'materi',
                  'data-placeholder': 'Nama materi',
                  spellcheck: 'false'
               }).html(sanitizeRichText(material.materi || ''))).appendTo(row);
               $('<td>').append($('<input>', {
                  type: 'number',
                  inputmode: 'numeric',
                  min: 0,
                  step: 1,
                  class: 'form-control form-control-sm',
                  'data-material-index': index,
                  'data-material-field': 'jp',
                  value: material.jp || '',
                  placeholder: '0'
               })).appendTo(row);
               $('<td>').append($('<button>', {
                  type: 'button',
                  class: 'btn btn-link text-danger btn-sm p-0',
                  title: 'Hapus baris',
                  'aria-label': 'Hapus baris'
               }).html('<i class="fas fa-trash-alt"></i>').on('click', function() {
                  removeCertificateMaterialRow(index);
               })).appendTo(row);
               rows.append(row);
            });

            updateMaterialsTotal();
         }

         function renderMaterialsPreview(data) {
            const materials = Array.isArray(data.materials) ? data.materials : [];
            const rows = $('[data-certificate-materials-preview]').empty();

            $('[data-certificate-materials-title]').text(data.materialsTitle || data.activityName || '-');
            if (materials.length === 0) {
               rows.append('<tr><td colspan="3" class="certificate-materials-empty">Belum ada materi</td></tr>');
            } else {
               materials.forEach(function(material, index) {
                  const row = $('<tr>');
                  $('<td>').text(index + 1).appendTo(row);
                  $('<td>').html(sanitizeRichText(material.materi || '')).appendTo(row);
                  $('<td>').text(material.jp || '').appendTo(row);
                  rows.append(row);
               });
            }

            $('[data-certificate-materials-total]').text(calculateMaterialsTotal(materials));
         }

         function calculateMaterialsTotal(materials) {
            const total = (materials || []).reduce(function(sum, material) {
               const jp = parseFloat(material.jp);
               return sum + (Number.isFinite(jp) ? jp : 0);
            }, 0);

            return Number.isInteger(total) ? total : total.toFixed(2).replace(/0+$/, '').replace(/\.$/, '');
         }

         function updateMaterialsTotal() {
            $('#certificateMaterialsTotal').text(calculateMaterialsTotal(
               rtlReviewState.certificateDraft ? rtlReviewState.certificateDraft.materials : []
            ));
         }

         function addCertificateMaterialRow() {
            if (!rtlReviewState.certificateDraft) {
               return;
            }

            if (!Array.isArray(rtlReviewState.certificateDraft.materials)) {
               rtlReviewState.certificateDraft.materials = [];
            }

            if (rtlReviewState.certificateDraft.materials.length >= 50) {
               showCertificateValidationError(
                  '#certificateAddMaterialRowButton',
                  'Maksimal 50 baris materi.',
                  true
               );
               return;
            }

            clearCertificateValidation('#certificateAddMaterialRowButton');

            rtlReviewState.certificateDraft.materials.push({ materi: '', jp: '' });
            renderMaterialsForm(rtlReviewState.certificateDraft);
            renderMaterialsPreview(rtlReviewState.certificateDraft);
            $('#certificateMaterialsRows tr:last [data-material-field="materi"]').trigger('focus');
         }

         function removeCertificateMaterialRow(index) {
            if (!rtlReviewState.certificateDraft || !Array.isArray(rtlReviewState.certificateDraft.materials)) {
               return;
            }

            rtlReviewState.certificateDraft.materials.splice(index, 1);
            clearCertificateValidation('#certificateAddMaterialRowButton');
            renderMaterialsForm(rtlReviewState.certificateDraft);
            renderMaterialsPreview(rtlReviewState.certificateDraft);
         }

         function goToCertificateStep(step, shouldValidate) {
            if (!rtlReviewState.certificateDraft) {
               return false;
            }

            const nextStep = step === 2 ? 2 : 1;
            if (shouldValidate !== false && nextStep === 2 && !validateCertificateStep(1, true)) {
               return false;
            }

            rtlReviewState.certificateStep = nextStep;
            const isFirstStep = rtlReviewState.certificateStep === 1;

            $('#certificateStepOneForm').toggle(isFirstStep);
            $('#certificateStepTwoForm').toggle(!isFirstStep);
            $('.certificate-preview-viewport').first().toggle(isFirstStep);
            $('#certificateMaterialsPreview').toggle(!isFirstStep);
            $('#certificatePreviousButton').toggle(!isFirstStep);
            $('#certificateNextButton').toggle(isFirstStep);
            $('#certificateCommitButton').toggle(!isFirstStep);

            resizeCertificatePreview();
            return true;
         }

         function resizeCertificatePreview() {
            $('.certificate-preview-viewport').each(function() {
               const viewport = this;
               const page = viewport.querySelector('.certificate-page, .certificate-materials-page');

               if (!page || !viewport.clientWidth || !page.offsetWidth) {
                  return;
               }

               page.style.setProperty('--certificate-preview-scale', viewport.clientWidth / page.offsetWidth);
            });
         }

         function reviewRtl(id) {
            if (rtlReviewRequest) {
               rtlReviewRequest.abort();
            }

            const requestToken = ++rtlReviewState.requestToken;
            rtlReviewRequest = $.get(`/dashboard/rtl/${id}`).done(function(data) {
               if (requestToken !== rtlReviewState.requestToken) {
                  return;
               }

               const documents = data.documents || [];
               rtlReviewState.activeId = id;
               rtlReviewState.certificateData = copyCertificateData(data.certificate_data);
               rtlReviewState.certificateEditorInitialData = {};
               rtlReviewState.certificateDraft = null;
               rtlReviewState.qrGenerationPending = false;
               clearCertificateQrTimer();
               rtlReviewState.qrGenerationToken++;
               rtlReviewState.hasCertificate = Boolean(data.certificate_file);

               $('#revUser').text(data.user ? data.user.nama_lengkap : data.no_ktp);
               $('#revKegiatan').text(data.kegiatan ? data.kegiatan.nama_kegiatan : '-');
               $('#revKtp').text(data.no_ktp);
               $('#revStatusSelect').val(data.status);
               $('#revAdminNotes').val(data.admin_notes || '');
               $('#reviewForm').attr('action', `/dashboard/rtl/update/${id}`);
               $('#revDocsCount').text(`${documents.length} File`);
               syncCertificateDataInputs();

               if (rtlReviewState.hasCertificate) {
                  $('#certificateEditorButtonText').text('Edit Sertifikat');
               } else {
                  $('#certificateEditorButtonText').text('Buat Sertifikat');
               }

               $('#revDocsList').empty();
               if (documents.length === 0) {
                  $('#revDocsList').append(
                     '<div class="p-3 text-center text-muted">Belum ada dokumen yang diunggah.</div>');
               } else {
                  documents.forEach(function(doc) {
                     const item = $('<a>', {
                        href: '#',
                        class: 'list-group-item list-group-item-action d-flex justify-content-between align-items-center'
                     });
                     const fileInfo = $('<div>');
                     $('<i>', { class: 'fas fa-file-pdf text-danger mr-2' }).appendTo(fileInfo);
                     $('<span>', { class: 'font-weight-600' }).text(doc.original_name).appendTo(fileInfo);
                     $('<span>', { class: 'btn btn-sm btn-outline-primary' }).text('Lihat Preview').appendTo(item);
                     item.prepend(fileInfo).on('click', function(event) {
                        event.preventDefault();
                        revShowFile(doc.url, doc.original_name);
                     });
                     $('#revDocsList').append(item);
                  });
               }

               $('#revFilePreviewFrame').hide();
               toggleCertUpload();
               $('#reviewRtlModal').modal('show');
            }).fail(function(xhr, status) {
               if (status !== 'abort' && typeof iziToast !== 'undefined') {
                  iziToast.error({ title: 'Gagal', message: 'Data review RTL tidak dapat dimuat.' });
               }
            });
         }

         function toggleCertUpload() {
            if ($('#revStatusSelect').val() === 'approved') {
               $('#certGenerateGroup').show();
            } else {
               $('#certGenerateGroup').hide();
               if ($('#certificateEditorModal').hasClass('show')) {
                  $('#certificateEditorModal').modal('hide');
               }
            }
         }

         function openCertificateEditor() {
            if (!rtlReviewState.activeId || $('#revStatusSelect').val() !== 'approved') {
               return;
            }

            rtlReviewState.certificateEditorInitialData = copyCertificateData(rtlReviewState.certificateData);
            rtlReviewState.certificateDraft = copyCertificateData(rtlReviewState.certificateEditorInitialData);
            clearCertificateQrTimer();
            rtlReviewState.qrGenerationPending = false;
            rtlReviewState.qrGenerationToken++;
            rtlReviewState.certificateStep = 1;
            renderCertificateEditor(rtlReviewState.certificateDraft);
            if (rtlReviewState.certificateDraft.qrLink && !rtlReviewState.certificateDraft.qrImage) {
               scheduleCertificateQr(rtlReviewState.certificateDraft.qrLink, true);
            }
            goToCertificateStep(1);
            $('#certificateEditorModal').modal('show');
         }

         function resetCertificateDraft() {
            if (!rtlReviewState.certificateDraft) {
               return;
            }

            clearCertificateQrTimer();
            rtlReviewState.qrGenerationPending = false;
            rtlReviewState.qrGenerationToken++;
            rtlReviewState.certificateDraft = copyCertificateData(rtlReviewState.certificateEditorInitialData);
            renderCertificateEditor(rtlReviewState.certificateDraft);
            if (rtlReviewState.certificateDraft.qrLink && !rtlReviewState.certificateDraft.qrImage) {
               scheduleCertificateQr(rtlReviewState.certificateDraft.qrLink, true);
            }
         }

         function commitCertificateDraft() {
            if (!rtlReviewState.certificateDraft) {
               return;
            }

            if (!validateCertificateDraft(true)) {
               return;
            }

            if (rtlReviewState.qrGenerationPending) {
               if (typeof iziToast !== 'undefined') {
                  iziToast.info({ title: 'Tunggu', message: 'QR Code sedang dibuat.' });
               }
               return;
            }

            if ($.trim(rtlReviewState.certificateDraft.qrLink || '') &&
               !/^data:image\//.test(rtlReviewState.certificateDraft.qrImage || '')) {
               if (typeof iziToast !== 'undefined') {
                  iziToast.error({ title: 'QR belum siap', message: 'QR Code belum berhasil dibuat. Periksa link QR lalu coba lagi.' });
               }
               return;
            }

            rtlReviewState.certificateData = copyCertificateData(rtlReviewState.certificateDraft);
            syncCertificateDataInputs();
            // Serialize the inputs inside the container; serializing the div itself
            // produces an empty payload because the div is not a form control.
            const certificatePayload = $('#certificateDataInputs').find(':input').serialize();
            const saveButton = $('#certificateCommitButton');
            const originalSaveButtonHtml = saveButton.html();
            setCertificateSavingState(true);
            saveButton
               .prop('disabled', true)
               .attr('aria-busy', 'true')
               .html('<i class="fas fa-spinner fa-spin mr-1" aria-hidden="true"></i> Menyimpan...');
            const csrfToken = $('meta[name="csrf-token"]').attr('content') || '';

            $.ajax({
               url: `/dashboard/rtl/certificate/${rtlReviewState.activeId}`,
               type: 'POST',
               data: `${certificatePayload}&_token=${encodeURIComponent(csrfToken)}`,
               headers: { Accept: 'application/json' }
            }).done(function(response) {
               rtlReviewState.certificateData = copyCertificateData(
                  response.certificate_data || rtlReviewState.certificateData
               );
               syncCertificateDataInputs();
               setCertificateSavingState(false);
               $('#certificateEditorModal').modal('hide');

               if (typeof iziToast !== 'undefined') {
                  iziToast.success({
                     title: 'Tersimpan',
                     message: 'Data sertifikat berhasil disimpan ke database.'
                  });
               }
            }).fail(function(xhr) {
               if (typeof iziToast !== 'undefined') {
                  iziToast.error({
                     title: 'Gagal menyimpan',
                     message: xhr.responseJSON?.message || 'Data sertifikat tidak dapat disimpan.'
                  });
               }
            }).always(function() {
               setCertificateSavingState(false);
               saveButton
                  .prop('disabled', false)
                  .attr('aria-busy', 'false')
                  .html(originalSaveButtonHtml);
            });
         }

         function revShowFile(url, fileName) {
            $('#currentFileName').text(fileName);
            const lowUrl = url.toLowerCase();

            $('#revPreviewIframe').hide();
            $('#revImagePreview').remove();

            if (/\.pdf(?:\?|$)/.test(lowUrl)) {
               $('#revPreviewIframe').attr('src', url).show();
               $('#revFilePreviewFrame').show();
            } else if (lowUrl.match(/\.(jpg|jpeg|png|gif)(?:\?|$)/)) {
               $('#revPreviewIframe').after(
                  `<img id="revImagePreview" src="${url}" class="img-fluid border mx-auto d-block" style="max-height: 550px; background: white;">`
               );
               $('#revFilePreviewFrame').show();
            } else {
               window.open(url, '_blank');
               return;
            }

            $('#reviewRtlModal').animate({
               scrollTop: $('#reviewRtlModal').scrollTop() + $('#revFilePreviewFrame').position().top
            }, 500);
         }

         $(function() {
            $('#certificateEditorModal').on('shown.bs.modal', function() {
               $('.modal-backdrop').last().addClass('certificate-editor-backdrop');
               $('body').addClass('modal-open');
               resizeCertificatePreview();
            });

            $('#certificateEditorModal').on('hide.bs.modal', function(event) {
               if (rtlReviewState.certificateSaving) {
                  event.preventDefault();
               }
            });

            $('#certificateEditorModal').on('hidden.bs.modal', function() {
               $('.modal-backdrop.certificate-editor-backdrop').removeClass('certificate-editor-backdrop');
               rtlReviewState.certificateEditorInitialData = {};
               rtlReviewState.certificateDraft = null;
               clearCertificateValidations();
               clearCertificateQrTimer();
               rtlReviewState.qrGenerationPending = false;
               rtlReviewState.qrGenerationToken++;

               if ($('#reviewRtlModal').hasClass('show')) {
                  $('body').addClass('modal-open');
                  $('#reviewRtlModal').modal('handleUpdate');
               }
            });

            $('#reviewRtlModal').on('hide.bs.modal', function(event) {
               if (rtlReviewState.certificateSaving) {
                  event.preventDefault();
                  return;
               }

               if ($('#certificateEditorModal').hasClass('show')) {
                  $('#certificateEditorModal').modal('hide');
               }
            });

            $(document).on('keydown.certificateSaving', function(event) {
               if (rtlReviewState.certificateSaving && event.key === 'Escape') {
                  event.preventDefault();
                  event.stopImmediatePropagation();
               }
            });

            $('#reviewRtlModal').on('hidden.bs.modal', function() {
               rtlReviewState.activeId = null;
               rtlReviewState.certificateData = {};
               rtlReviewState.certificateEditorInitialData = {};
               rtlReviewState.certificateDraft = null;
               clearCertificateQrTimer();
               rtlReviewState.qrGenerationPending = false;
               rtlReviewState.qrGenerationToken++;
               $('#certificateDataInputs').empty();
            });

            $('#certificateEditorModal [data-certificate-field]').on('input', function() {
               if (!rtlReviewState.certificateDraft) {
                  return;
               }

               const field = $(this).data('certificate-field');
               rtlReviewState.certificateDraft[field] = getCertificateFieldValue(this);
               renderCertificateFieldPreview(field, rtlReviewState.certificateDraft[field]);
               if (field === 'qrLink') {
                  scheduleCertificateQr(rtlReviewState.certificateDraft.qrLink, false);
               }
               scheduleCertificateValidation(this);
            });

            $('#certificateStepOneForm').on('keydown', '[contenteditable="true"]', function(event) {
               if (!(event.ctrlKey || event.metaKey) || event.altKey) {
                  return;
               }

               const commands = {
                  b: 'bold',
                  i: 'italic',
                  u: 'underline'
               };
               const command = commands[event.key.toLowerCase()];
               if (!command) {
                  return;
               }

               event.preventDefault();
               event.stopPropagation();
               document.execCommand(command, false);
               $(this).trigger('input');
            });

            $('#certificateMaterialsRows').on('input', '[data-material-field]', function() {
               if (!rtlReviewState.certificateDraft) {
                  return;
               }

               const index = Number($(this).data('material-index'));
               const field = $(this).data('material-field');
               if (!Array.isArray(rtlReviewState.certificateDraft.materials) || !rtlReviewState.certificateDraft.materials[index]) {
                  return;
               }

               if (field === 'jp') {
                  sanitizeCertificateJpInput(this);
               }

               rtlReviewState.certificateDraft.materials[index][field] = $(this).is('[contenteditable]')
                  ? sanitizeRichText($(this).html())
                  : $(this).val();
               renderMaterialsPreview(rtlReviewState.certificateDraft);
               updateMaterialsTotal();
               scheduleCertificateValidation(this);
            });

            $('#certificateMaterialsRows').on('keydown', '[data-material-field="jp"]', function(event) {
               if (event.isComposing || event.ctrlKey || event.metaKey || event.key.length > 1) {
                  return;
               }

               if (!/^\d$/.test(event.key)) {
                  event.preventDefault();
               }
            });

            $('#certificateMaterialsRows').on('keydown', '[contenteditable="true"]', function(event) {
               if (!(event.ctrlKey || event.metaKey) || event.altKey) {
                  return;
               }

               const commands = {
                  b: 'bold',
                  i: 'italic',
                  u: 'underline'
               };
               const command = commands[event.key.toLowerCase()];
               if (!command) {
                  return;
               }

               event.preventDefault();
               event.stopPropagation();
               document.execCommand(command, false);
               $(this).trigger('input');
            });

            $('#reviewForm').on('submit', function() {
               syncCertificateDataInputs();
            });

            $(window).on('resize', resizeCertificatePreview);
         });
      </script>
   @endpush
@endsection
