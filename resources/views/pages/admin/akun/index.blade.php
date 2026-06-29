@extends('layouts.app', ['title' => 'Data Akun'])

@section('content')
   @push('styles')
      <link rel="stylesheet" href="{{ asset('library/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
      <link rel="stylesheet" href="{{ asset('library/datatables.net-select-bs4/css/select.bootstrap4.min.css') }}">
   @endpush

   <div class="main-content">
      <section class="section">
         <div class="section-header">
            <h1>Data Akun</h1>
         </div>

         <div class="section-body">
            <div class="row">
               <div class="col-12">
                  <div class="card">
                     <div class="card-body">

                        <div class="row">
                           <div class="col-md-12 col-lg-12">
                              <form action="{{ route('akun.store') }}" method="POST" id="form-akun">
                                 @csrf
                                 <div class="card">
                                    <div class="card-body">
                                       <div class="row">
                                          <div class="col-md-4">
                                             <div class="form-group">
                                                <label>Nomor KTP</label>
                                                <input name="no_ktp" required placeholder="Masukkan nomor ktp"
                                                   type="number" class="form-control">
                                             </div>
                                          </div>
                                          <div class="col-md-4">
                                             <div class="form-group">
                                                <label>Nama</label>
                                                <input name="name" required placeholder="Masukkan Nama Akun"
                                                   type="text" class="form-control">
                                             </div>
                                          </div>
                                          <div class="col-md-4">
                                             <div class="form-group">
                                                <label>Username</label>
                                                <input name="username" required placeholder="Masukkan Username untuk login"
                                                   type="text" class="form-control">
                                             </div>
                                          </div>
                                       </div>
                                       <div class="row">
                                          <div class="col-md-6">
                                             <div class="form-group">
                                                <label>Password</label>
                                                <input name="password" required placeholder="Masukkan Password"
                                                   type="password" class="form-control">
                                             </div>
                                          </div>
                                          <div class="col-md-6">
                                             <div class="form-group">
                                                <label>Role</label>
                                                <select name="role" required class="form-control selectric">
                                                   <option value="">-- Pilih Role Akun --</option>
                                                   <option value="tenaga pendidik">Tenaga Pendidik</option>
                                                   <option value="tenaga kependidikan">Tenaga Kependidikan
                                                   </option>
                                                   <option value="stakeholder">Stakeholder</option>
                                                   <option value="pegawai">Pegawai BBGTK</option>
                                                   <option value="superadmin">Super Admin</option>
                                                </select>
                                             </div>
                                          </div>
                                       </div>

                                       <div class="row">
                                          <div class="col-md-4">
                                             <button type="submit" class="btn btn-primary" id="btn-submit">
                                                <i class="fas fa-plus"></i>
                                                <span class="btn-text">Tambah Data Akun</span>
                                                <span class="spinner-border spinner-border-sm d-none" role="status"
                                                   aria-hidden="true"></span>
                                             </button>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </form>
                           </div>
                        </div>

                        <!-- Loading Indicator -->
                        <div class="text-center py-4 d-none" id="loading-indicator">
                           <div class="spinner-border" role="status">
                              <span class="sr-only">Loading...</span>
                           </div>
                           <p class="mt-2">Memuat data...</p>
                        </div>

                        <div class="table-responsive" id="table-container">
                           <table class="table table-striped" id="table-akun">
                              <thead>
                                 <tr>
                                    <th class="text-center">#</th>
                                    <th>Nama</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Action</th>
                                 </tr>
                              </thead>
                              <tbody>
                                 <!-- Data akan dimuat via AJAX -->
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

   @push('scripts')
      <script src="{{ asset('library/datatables/media/js/jquery.dataTables.min.js') }}"></script>
      <script src="{{ asset('library/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
      <script src="{{ asset('library/datatables.net-select-bs4/js/select.bootstrap4.min.js') }}"></script>

      <script>
         $(document).ready(function() {
            // Initialize DataTable dengan server-side processing
            var table = $('#table-akun').DataTable({
               processing: true,
               serverSide: true,
               ajax: {
                  url: "{{ route('akun.data') }}", // Route untuk AJAX
                  type: 'GET',
                  beforeSend: function() {
                     $('#loading-indicator').removeClass('d-none');
                  },
                  complete: function() {
                     $('#loading-indicator').addClass('d-none');
                  },
                  error: function(xhr, error, thrown) {
                     console.error('Error loading data:', error);
                     alert('Error loading data. Please refresh the page.');
                  }
               },
               columns: [{
                     data: null,
                     searchable: false,
                     orderable: false,
                     render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                     }
                  },
                  {
                     data: 'name',
                     name: 'name'
                  },
                  {
                     data: 'username',
                     name: 'username'
                  },
                  {
                     data: 'role',
                     name: 'role'
                  },
                  {
                     data: 'id',
                     name: 'action',
                     orderable: false,
                     searchable: false,
                     render: function(data, type, row) {
                        return `
                                    <a href="/dashboard/akun/edit/${data}" class="btn btn-warning btn-sm my-1">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button onclick="deleteData(${data}, 'akun')" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                `;
                     }
                  }
               ],
               language: {
                  processing: "Memproses...",
                  lengthMenu: "Tampilkan _MENU_ data per halaman",
                  zeroRecords: "Data tidak ditemukan",
                  info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                  infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                  infoFiltered: "(disaring dari _MAX_ total data)",
                  search: "Cari:",
                  paginate: {
                     first: "Pertama",
                     last: "Terakhir",
                     next: "Selanjutnya",
                     previous: "Sebelumnya"
                  }
               },
               pageLength: 15,
               lengthMenu: [
                  [10, 25, 50, 100],
                  [10, 25, 50, 100]
               ]
            });

            // Handle form submission
            $('#form-akun').on('submit', function(e) {
               e.preventDefault();

               var $btn = $('#btn-submit');
               var $btnText = $btn.find('.btn-text');
               var $spinner = $btn.find('.spinner-border');

               // Show loading state
               $btn.prop('disabled', true);
               $btnText.text('Menyimpan...');
               $spinner.removeClass('d-none');

               $.ajax({
                  url: $(this).attr('action'),
                  method: 'POST',
                  data: $(this).serialize(),
                  success: function(response) {
                     // Reset form
                     $('#form-akun')[0].reset();
                     $('.selectric').selectric('refresh');

                     // Reload table
                     table.ajax.reload();

                     // Show success message
                     alert('Data akun berhasil ditambahkan!');
                  },
                  error: function(xhr) {
                     var errors = xhr.responseJSON?.errors;
                     if (errors) {
                        var errorMsg = Object.values(errors).flat().join('\n');
                        alert('Error: ' + errorMsg);
                     } else {
                        alert('Terjadi kesalahan saat menyimpan data');
                     }
                  },
                  complete: function() {
                     // Reset button state
                     $btn.prop('disabled', false);
                     $btnText.text('Tambah Data Akun');
                     $spinner.addClass('d-none');
                  }
               });
            });
         });

         // Delete function
         function deleteData(id, type) {
            if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
               $.ajax({
                  url: `/dashboard/${type}/hapus/${id}`,
                  method: 'POST',
                  data: {
                     _token: '{{ csrf_token() }}'
                  },
                  success: function(response) {
                     $('#table-akun').DataTable().ajax.reload();
                     alert('Data berhasil dihapus!');
                  },
                  error: function() {
                     alert('Terjadi kesalahan saat menghapus data');
                  }
               });
            }
         }
      </script>
   @endpush
@endsection
