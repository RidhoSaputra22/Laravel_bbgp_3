@extends('layouts.app', ['title' => 'Data Internal'])

@section('content')
    @push('styles')
        <link rel="stylesheet" href="{{ asset('library/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('library/datatables.net-select-bs4/css/select.bootstrap4.min.css') }}">
        <style>
            .table-internal {
                display: none;
            }

            /* Modal */
            .modal {
                position: fixed;
                z-index: 1;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                overflow: auto;
                background-color: rgb(0, 0, 0);
                background-color: rgba(0, 0, 0, 0.4);
            }

            /* Modal Content */
            .modal-content {
                background-color: #fefefe;
                margin: 15% auto;
                padding: 20px;
                border: 1px solid #888;
                width: 80%;
            }

            /* Close Button */
            .close {
                color: #aaa;
                float: right;
                font-size: 28px;
                font-weight: bold;
            }

            .close:hover,
            .close:focus {
                color: black;
                text-decoration: none;
                cursor: pointer;
            }
        </style>
    @endpush

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Data Internal BBGTK</h1>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <!-- Navigation Buttons -->
                                <div class="row mb-3">
                                    <div class="col-10">
                                        <h4 id="title-text">Data Pendamping Lokakarya
                                            {{ $datas['getNama']->nama ?? $datas['getNamaPpnpn']->nama }}</h4>
                                    </div>
                                    <div class="col text-right">
                                        <a href="{{ route('internal.index') }}" class="btn btn-warning">Kembali </a>
                                    </div>
                                </div>

                                <div class="table-responsive" id="table-internal-bbgp">
                                    <!-- Table BBGP -->
                                    <table class="table table-striped" id="table-bbgp">
                                        <thead>
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th>Nama</th>
                                                <th>Kegiatan</th>
                                                <th>Kabupaten/Kota</th>
                                                <th>Hotel</th>
                                                <th>Biaya Pergi</th>
                                                <th>Biaya Pulang</th>
                                                <th>Bill Penginapan</th>
                                                <th>Hari 1</th>
                                                <th>Hari 2</th>
                                                <th>Hari 3</th>
                                                <th>Hari 4</th>
                                                <th>Hari 5</th>
                                                <th>Hari 6</th>
                                                <th>Hari 7</th>
                                                <th>Total</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($datas['penugasanLokakarya'] ?? $datas['penugasanLokakaryaPpnpn'] as $i => $data)
                                                @php
                                                    $total =
                                                        $data->transport_pulang +
                                                        $data->transport_pergi +
                                                        $data->bill_penginapan +
                                                        $data->hari_1 +
                                                        $data->hari_2 +
                                                        $data->hari_3;
                                                @endphp


                                                <tr data-type="bbgp">
                                                    <td>{{ ++$i }}</td>
                                                    <td>{{ $data->nama }} </td>
                                                    <td>{{ $data->kegiatan }} </td>
                                                    <td>{{ $data->kota }}</td>
                                                    <td>{{ $data->hotel }}</td>
                                                    <td class="text-nowrap">Rp.
                                                        {{ number_format($data->transport_pulang, 0, ',', '.') }}</td>
                                                    <td class="text-nowrap">Rp.
                                                        {{ number_format($data->transport_pergi, 0, ',', '.') }}</td>
                                                    <td class="text-nowrap">Rp.
                                                        {{ number_format($data->bill_penginapan, 0, ',', '.') }}</td>
                                                    <td class="text-nowrap">Rp.
                                                        {{ number_format($data->hari_1, 0, ',', '.') }}</td>
                                                    <td class="text-nowrap">Rp.
                                                        {{ number_format($data->hari_2, 0, ',', '.') }}</td>
                                                    <td class="text-nowrap">Rp.
                                                        {{ number_format($data->hari_3, 0, ',', '.') }}</td>
                                                    <td class="text-nowrap">Rp.
                                                        {{ number_format($data->hari_4, 0, ',', '.') }}</td>
                                                    <td class="text-nowrap">Rp.
                                                        {{ number_format($data->hari_5, 0, ',', '.') }}</td>
                                                    <td class="text-nowrap">Rp.
                                                        {{ number_format($data->hari_6, 0, ',', '.') }}</td>
                                                    <td class="text-nowrap">Rp.
                                                        {{ number_format($data->hari_7, 0, ',', '.') }}</td>
                                                    <td class="text-nowrap"><b> Rp.
                                                            {{ number_format($total, 0, ',', '.') }} </b></td>
                                                    <td>
                                                        {{-- {{ dump($data->bukti_bill) }} --}}
                                                        <a target="_blank" href="{{ asset('upload/bukti_bill/'. $data->bukti_bill) }}" class="btn btn-info">
                                                            <i class="fas fa-print"></i>
                                                        </a>

                                                        <a href="{{ route('internal.edit.lokakarya', $data->id) }}"
                                                            class="btn btn-warning my-2"><i class="fas fa-edit"></i></a>

                                                        <button onclick="deleteDataLoka({{ $data->id }}, 'internal')"
                                                            class="btn btn-danger">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            {{-- {{ dd(1) }} --}}
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

    <!-- Modal HTML -->
    <div id="pdfModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="title-pdf">Pratinjau PDF</h2>
            <canvas id="pdf-preview" style="width: 100%; height: auto;"></canvas>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('library/datatables/media/js/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('library/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('library/datatables.net-select-bs4/js/select.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('js/page/modules-datatables.js') }}"></script>
        <!-- PDF.js library -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
        <script>
            function openModal(button) {
                var pdfFileName = $(button).data('pdffile');
                let titlePdf = $('#title-pdf');
                if (pdfFileName === undefined) {
                    titlePdf.val('Tidak ada file');
                } else {
                    titlePdf.val('Preview Bukti'); // Update dengan nama file
                }

                console.log('PDF File Name:', pdfFileName);
                var pdfUrl = '{{ asset('upload/bukti_bill') }}/' + pdfFileName; // Gabungkan dengan URL
                console.log('PDF URL:', pdfUrl);

                // Tampilkan modal
                document.getElementById('pdfModal').style.display = 'block';
                renderPDF(pdfUrl);
            }


            function closeModal() {
                document.getElementById('pdfModal').style.display = 'none';
            }

            async function renderPDF(url) {
                try {
                    const pdfjsLib = window['pdfjs-dist/build/pdf'];
                    const loadingTask = pdfjsLib.getDocument(url);
                    const pdf = await loadingTask.promise;

                    // Ambil halaman pertama
                    const page = await pdf.getPage(1);
                    const scale = 1.5;
                    const viewport = page.getViewport({
                        scale
                    });

                    // Siapkan canvas dan konteks untuk menggambar
                    const canvas = document.getElementById('pdf-preview');
                    const context = canvas.getContext('2d');
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;

                    const renderContext = {
                        canvasContext: context,
                        viewport: viewport
                    };
                    await page.render(renderContext).promise;
                } catch (error) {
                    console.error('Error rendering PDF:', error);
                    alert('Terjadi kesalahan saat memuat PDF. Periksa konsol untuk informasi lebih lanjut.');
                }
            }
        </script>


        <script type="text/javascript">
            $(document).ready(function() {
                var language = {
                    "sSearch": "Pencarian Data Internal BBGTK : ",
                };
                // Initialize DataTables for both tables
                var tableBbgp = $('#table-bbgp').DataTable({
                    paging: true,
                    searching: true,
                    language: language,
                    columnDefs: [{
                        targets: [4, 5, 6, 7, 8],
                        render: $.fn.dataTable.render.number('.', ',', 0, 'Rp ')
                    }]
                });

                // Initially hide both tables
                $('.table-internal').hide();

                // Show appropriate table based on button click
                $('#pegawaiBBGP').on('click', function(event) {
                    event.preventDefault();
                    $('.table-internal').hide();
                    $('#table-internal-bbgp').show();
                    tableBbgp.columns.adjust().draw(); // Adjust column widths on table show
                    $('#title-text').text('Data Penugasan Pegawai')
                });

                // Filter by Nama
                $('#namaFilter').on('keyup', function() {
                    tableBbgp.column(1).search(this.value).draw();
                    tableBbgp.column(2).search(this.value).draw();
                    tableBbgp.column(3).search(this.value).draw();
                });

                // Reset button functionality
                $('#resetBtn').on('click', function() {
                    $('#rekapan').val('');
                    $('#namaFilter').val('');
                    $('.table-internal').hide();
                    tableBbgp.search('').columns().search('').draw();
                });


            });
        </script>

        <script>
            // swal btn hps data
            const deleteDataLoka = (id, tabel) => {
                console.log(id, tabel);
                let token = $("meta[name='csrf-token']").attr("content");

                swal({
                    title: "Apakah anda yakin?",
                    text: "Data yang dihapus tidak dapat dikembalikan!",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    console.log(willDelete);

                    if (willDelete) {
                        $.ajax({
                            headers: {
                                "X-CSRF-TOKEN": token,
                            },
                            type: "POST",
                            url: `{{ route('internal.hapus.loka', ['id' => 'PLACEHOLDER_ID']) }}`
                                .replace('PLACEHOLDER_ID', id),
                            success: function(response) {
                                console.log(response);
                                if (response) {
                                    swal("Terhapus", "Data telah dihapus", "success").then(() => {
                                        location.reload();
                                    });
                                } else {
                                    swal("Error", "Gagal menghapus data.", "error");
                                }
                            },
                            error: function(error) {
                                console.error("AJAX Error:", error);
                                swal("Error", "Terjadi kesalahan saat menghapus data.", "error");
                            },
                        });
                    }
                });
            };
        </script>
    @endpush
@endsection
