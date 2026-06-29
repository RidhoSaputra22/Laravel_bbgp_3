@extends('layouts.app', ['title' => 'Data Berkas'])

@section('content')
    @push('styles')
        <link rel="stylesheet" href="{{ asset('library/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('library/datatables.net-select-bs4/css/select.bootstrap4.min.css') }}">
    @endpush

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Data {{ ucfirst($menu) }}</h1>
            </div>


            <div class="section-body">


                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">

                                @if (session('role') == 'pegawai')
                                    <div class="row">

                                        <div class="col-md-12 col-lg-12">
                                            <div class="row mb-4">
                                                <div class="col-md-4">
                                                    <button class="btn btn-primary btn-tambah" type="button"
                                                        data-toggle="modal" data-target="#uploadModal">
                                                        <i class="fas fa-plus"></i>
                                                        Upload laporan
                                                    </button>
                                                </div>
                                            </div>

                                        </div>

                                    </div>
                                @endif
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <input type="text" class="form-control" id="searchEmployee"
                                                placeholder="Cari nama pegawai...">
                                        </div>
                                    </div>
                                </div>
                                <div id="accordion">
                                    @if (session('role') == 'admin' || session('role') == 'superadmin')
                                        @foreach ($datas as $user)
                                            <div class="accordion">
                                                <div class="accordion-header collapsed" role="button"
                                                    data-toggle="collapse" data-target="#panel-user-{{ $user->id }}"
                                                    aria-expanded="false">
                                                    <h4>{{ $user->nama_lengkap }} - Total Laporan:
                                                        {{ $user->berkas->count() }}
                                                    </h4>
                                                </div>
                                                <div class="accordion-body collapse" id="panel-user-{{ $user->id }}"
                                                    data-parent="#accordion">
                                                    <div class="table-responsive">
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>No</th>
                                                                    <th>Nama kegiatan</th>
                                                                    <th>Tanggal laporan</th>
                                                                    <th>Preview laporan</th>
                                                                    <th class="text-center">Status Verifikasi</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($user->berkas as $i => $data)
                                                                    <tr>
                                                                        <td>{{ ++$i }}</td>
                                                                        <td>{{ $data->nama_kegiatan }}</td>
                                                                        <td class="text-nowrap">
                                                                            {{ Helper::dateIndo(explode(' ', $data->created_at, -1)[0]) }}
                                                                        </td>
                                                                        @php
                                                                            $path = asset(
                                                                                'upload/berkas/' . $data->nama_berkas,
                                                                            );
                                                                            $path = explode('/', $path);
                                                                        @endphp
                                                                        <td class="text-nowrap">
                                                                            <a href="{{ $path[5] == $data->nama_berkas ? asset('upload/berkas/' . $data->nama_berkas) : $data->nama_berkas }}"
                                                                                target="_blank"
                                                                                class="btn btn-icon btn-primary btn-sm">
                                                                                Preview laporan
                                                                            </a>
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <button type="button"
                                                                                class="btn btn-{{ $data->status == 'proses' ? 'warning' : 'success' }}">
                                                                                {{ $data->status == 'proses' ? 'Proses' : 'Selesai' }}
                                                                            </button>
                                                                        </td>
                                                                        <td>
                                                                            @if ($data->status == 'proses')
                                                                                <button type="button"
                                                                                    onclick="updateStatus({{ $data->id }})"
                                                                                    class="btn btn-info">
                                                                                    Verifikasi
                                                                                </button>
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        @foreach ($datas as $i => $data)
                                            <div class="accordion">
                                                <div class="accordion-header collapsed" role="button"
                                                    data-toggle="collapse" data-target="#panel-{{ $data->id }}"
                                                    aria-expanded="false">
                                                    <h3>{{ 'Laporan Kegiatan ' . ++$i }}</h3>
                                                </div>
                                                <div class="accordion-body collapse" id="panel-{{ $data->id }}"
                                                    data-parent="#accordion" style="">
                                                    <div class="table-responsive">
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>Nama kegiatan </th>
                                                                    <th>Tanggal laporan </th>
                                                                    <th>Preview laporan</th>
                                                                    <th class="text-center">Status Verifikasi</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td>{{ $data->nama_kegiatan }}</td>
                                                                    <td class="text-nowrap">
                                                                        {{ Helper::dateIndo(explode(' ', $data->created_at, -1)[0]) }}
                                                                    </td>
                                                                    @php
                                                                        $path = asset(
                                                                            'upload/berkas/' . $data->nama_berkas,
                                                                        );
                                                                        $path = explode('/', $path);
                                                                    @endphp
                                                                    <td class="text-nowrap">
                                                                        <a href="{{ $path[5] == $data->nama_berkas ? asset('upload/berkas/' . $data->nama_berkas) : $data->nama_berkas }}"
                                                                            target="_blank"
                                                                            class="btn btn-icon btn-primary btn-sm">
                                                                            Preview laporan
                                                                        </a>
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <button type="button"
                                                                            class="btn btn-{{ $data->status == 'proses' ? 'warning' : 'success' }}">
                                                                            {{ $data->status == 'proses' ? 'Proses' : 'Selesai' }}
                                                                        </button>
                                                                    </td>
                                                                    <td>
                                                                        @if (session('role') == 'pegawai')
                                                                            <a href="" data-id="{{ $data->id }}"
                                                                                data-toggle="modal"
                                                                                data-target="#uploadModal"
                                                                                class="btn btn-warning my-2"><i
                                                                                    class="fas fa-edit"></i></a>

                                                                            <button
                                                                                onclick="deleteData({{ $data->id }}, 'berkas')"
                                                                                class="btn btn-danger">
                                                                                <i class="fas fa-trash-alt"></i>
                                                                            </button>
                                                                        @endif
                                                                    </td>
                                                                </tr>

                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </section>
    </div>

    {{-- modal --}}
    <div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"><span id="titleMethod">Tambah</span> Berkas</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="#" id="submitForm" enctype="multipart/form-data">
                    @csrf
                    <input name="methodId" type="hidden" id="methodId" value="">
                    <input type="hidden" name="formId" id="formId" value="">
                    <div class="modal-body">
                        <div class="dropdown d-inline mr-2">
                            <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Pilih metode upload
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" id="methodUpload" data-method="upload">Upload file</a>
                                <a class="dropdown-item" id="methodLink" data-method="link">Sisipkan link</a>
                            </div>
                        </div>
                        <div class="fallback">
                            <input name="nama_kegiatan" id="nama_kegiatan" required type="text"
                                placeholder="Nama kegiatan" class="form-control mt-3" />
                            <input name="metode_upload" id="metode_upload" type="hidden" class="form-control mt-3" />
                            <div id="uploadForm">
                                <input name="nama_berkas" id="nama_berkas" required accept=".doc, .docx, .pdf" type="file"
                                    class="form-control mt-3" />
                                <input name="nama_berkas_old" id="nama_berkas_old" type="hidden"
                                    class="form-control mt-3" />
                            </div>
                            <div id="linkForm">
                                <input name="nama_link" placeholder="Link yang terhubung ke laporan" id="nama_link"
                                    type="url" class="form-control mt-3" />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-cancel" data-dismiss="modal">Close</button>
                        <button type="button" id="submitBtn" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('library/datatables/media/js/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('library/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('library/datatables.net-select-bs4/js/select.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('js/page/modules-datatables.js') }}"></script>
        <script src="{{ asset('js/page/bootstrap-modal.js') }}"></script>
        <!-- Page Specific JS File -->
        <script>
            $(document).ready(function() {
                const uploadForm = $('#uploadForm').hide();
                const linkForm = $('#linkForm').hide();
                let methodUpload = ''

                $('#methodUpload').on('click', function(e) {
                    e.preventDefault()
                    methodUpload = $(this).data('method')
                    uploadForm.show()
                    linkForm.hide()
                    $('#nama_link').val('')
                })

                $('#methodLink').on('click', function(e) {
                    e.preventDefault()
                    methodUpload = $(this).data('method')
                    uploadForm.hide()
                    linkForm.show()
                    $('#nama_berkas').val('')
                })

                $('#uploadModal').on('hidden.bs.modal', function() {
                    $('#submitForm')[0].reset();
                    $('#formId').val('');
                    $('#methodId').val('');
                });

                $('.btn-tambah').on('click', function(e) {
                    e.preventDefault()
                    $('#titleMethod').html('Tambah')
                })

                // Handle edit button click
                $('.btn-warning').on('click', function(e) {
                    e.preventDefault();
                    const id = $(this).data('id');
                    let url = "{{ route('berkas.edit', ':id') }}"
                    url = url.replace(':id', id)

                    $('#formId').val(id);

                    $.ajax({
                        url: url,
                        method: 'GET',
                        success: function(response) {
                            console.log(response);
                            let metodeUpload = response.data.metode_upload
                            if (metodeUpload == 'link') {
                                uploadForm.hide()
                                linkForm.show()
                                $('#nama_link').val(response.data.nama_berkas)
                            } else {
                                uploadForm.show()
                                linkForm.hide()
                                $('#nama_berkas_old').val(response.data.nama_berkas);
                            }
                            methodUpload = metodeUpload
                            $('#nama_kegiatan').val(response.data.nama_kegiatan);
                            $('#methodId').val('PUT');
                            $('#titleMethod').html('Edit')
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to fetch data'
                            });
                        }
                    });
                });

                // Handle form submission
                $('#submitBtn').on('click', function(e) {
                    e.preventDefault();

                    const formData = new FormData($('#submitForm')[0]);
                    const id = $('#formId').val();
                    const method = $('#methodId').val() || 'POST';
                    formData.append('metode_upload', methodUpload)

                    for (const [key, value] of formData) {
                        console.log('»', key, value)
                    }


                    const url = method === 'PUT' ? '{{ route('berkas.update') }}' :
                        '{{ route('berkas.store') }}';
                    method === 'PUT' ? formData.append('_method', 'PUT') : ''

                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                'content')
                        },
                        url: url,
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            $('#uploadModal').modal('hide');
                            swal({
                                icon: response.status || 'success',
                                title: response.status || 'Success',
                                text: response.message
                            }).then((result) => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            let errors = xhr.responseJSON.errors;
                            let errorMessage = '';
                            if (errors) {
                                errorMessage = Object.values(errors).flat().join('\n') ==
                                    'validation.mimes' ?
                                    'Laporan yang anda upload harus format .pdf .docx' :
                                    'Laporan tidak boleh kosong';
                                console.log(errorMessage);
                            } else {
                                errorMessage = xhr.responseJSON.message;
                            }
                            swal({
                                icon: 'error',
                                title: 'Error',
                                text: errorMessage
                            });
                        }
                    });
                });

                $('#searchEmployee').on('keyup', function() {
                    const searchValue = $(this).val().toLowerCase();
                    console.log(searchValue);
                    // Loop through each accordion
                    $('.accordion').each(function() {
                        const employeeName = $(this).find('.accordion-header h4').text().toLowerCase();

                        // Show/hide based on search value
                        if (employeeName.includes(searchValue)) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    });

                    // If search is empty, show all
                    if (searchValue === '') {
                        $('.accordion').show();
                    }
                });

            })

            function updateStatus(id) {

                let url = "{{ route('berkas.verify', ':id') }}"
                url = url.replace(':id', id)
                console.log(url);
                swal({
                    title: 'Verifikasi Laporan?',
                    text: "Status laporan akan diubah menjadi selesai",
                    icon: 'warning',
                    buttons: true,
                    dangerMode: true,
                    buttons: ['Cancel', 'Ya, verifikasi!']
                }).then((result) => {
                    if (result) {
                        try {
                            $.ajax({
                                url: url,
                                method: 'GET',
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(response) {
                                    swal(
                                        'Berhasil!',
                                        'Status laporan telah diverifikasi.',
                                        'success'
                                    ).then(() => {
                                        location.reload();
                                    });
                                },
                                error: function(xhr) {
                                    console.log(xhr);
                                    swal(
                                        'Error!',
                                        'Terjadi kesalahan saat memverifikasi laporan.',
                                        'error'
                                    );
                                }
                            });
                        } catch (error) {
                            swal({
                                icon: 'error',
                                title: 'Error',
                                text: error
                            })
                        }
                    }
                });
            }
        </script>
    @endpush
@endsection
