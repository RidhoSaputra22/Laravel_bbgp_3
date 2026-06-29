@extends('layouts.app', ['title' => 'Data Sekolah'])
@section('content')
    @push('styles')
        <link rel="stylesheet" href="{{ asset('library/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('library/datatables.net-select-bs4/css/select.bootstrap4.min.css') }}">
    @endpush
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Detail Data Sekolah</h1>
            </div>
            <div class="section-body">
                <div class="container my-4">
                    <div class="row">
                        <div class="col-md-12">
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show">
                                    {{ session('success') }}
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show">
                                    {{ session('error') }}
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                </div>
                            @endif

                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h3>{{ $sekolah->nama_sekolah }}</h3>
                                    <a href="{{ route('edit.data-sekolah', $sekolah->id) }}" class="btn btn-warning">
                                        <i class="fas fa-edit"></i> Edit Data
                                    </a>
                                </div>

                                <div class="card-body">
                                    <!-- Identitas Sekolah -->
                                    <h4>Identitas Sekolah</h4>
                                    <table class="table table-bordered">
                                        <tr>
                                            <th width="30%">Nama Sekolah</th>
                                            <td>{{ $sekolah->nama_sekolah }}</td>
                                        </tr>
                                        <tr>
                                            <th>NPSN</th>
                                            <td>{{ $sekolah->npsn_sekolah }}</td>
                                        </tr>
                                        <tr>
                                            <th>Jenjang</th>
                                            <td>{{ $sekolah->bp_sekolah }}</td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td>{{ $sekolah->status_sekolah }}</td>
                                        </tr>
                                        <tr>
                                            <th>Akreditasi</th>
                                            <td>{{ strtoupper($sekolah->akreditasi) }}</td>
                                        </tr>
                                        <tr>
                                            <th>Alamat</th>
                                            <td>{{ ucfirst($sekolah->alamat)  }}, {{ $sekolah->kecamatan }},
                                                {{ $sekolah->kabupaten }}, {{ $sekolah->provinsi }}</td>
                                        </tr>
                                        <tr>
                                            <th>No. Telepon</th>
                                            <td>{{ $sekolah->no_telepon }}</td>
                                        </tr>
                                        <tr>
                                            <th>Email</th>
                                            <td>{{ $sekolah->email }}</td>
                                        </tr>
                                        <tr>
                                            <th>Website</th>
                                            <td>{{ $sekolah->website_url ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Tahun Berdiri</th>
                                            <td>{{ $sekolah->tahun_berdiri ?? '-' }}</td>
                                        </tr>
                                    </table>

                                    <!-- Data Kepala Sekolah -->
                                    <h4 class="mt-4">Data Kepala Sekolah</h4>
                                    <table class="table table-bordered">
                                        <tr>
                                            <th width="30%">Nama Kepala Sekolah</th>
                                            <td>{{ $sekolah->nama_kepsek }}</td>
                                        </tr>
                                        <tr>
                                            <th>Status ASN</th>
                                            <td>{{ $sekolah->asn_opsi == 'ya' ? 'Ya' : 'Tidak' }}</td>
                                        </tr>
                                        @if ($sekolah->nip_kepsek)
                                            <tr>
                                                <th>NIP</th>
                                                <td>{{ $sekolah->nip_kepsek }}</td>
                                            </tr>
                                        @endif
                                        @if ($sekolah->no_sk)
                                            <tr>
                                                <th>No. SK</th>
                                                <td>{{ $sekolah->no_sk }}</td>
                                            </tr>
                                        @endif
                                        <tr>
                                            <th>No. Telepon</th>
                                            <td>{{ $sekolah->no_telp_kepsek }}</td>
                                        </tr>
                                        @if ($sekolah->email_kepsek)
                                            <tr>
                                                <th>Email</th>
                                                <td>{{ $sekolah->email_kepsek }}</td>
                                            </tr>
                                        @endif
                                    </table>

                                    <!-- Data Guru & Siswa -->
                                    <h4 class="mt-4">Data Guru & Siswa</h4>
                                    <table class="table table-bordered">
                                        <tr>
                                            <th width="30%">Jumlah Guru</th>
                                            <td>{{ $sekolah->jumlah_guru_pns + $sekolah->jumlah_guru_honorer }} orang (PNS: {{ $sekolah->jumlah_guru_pns }},
                                                Honorer: {{ $sekolah->jumlah_honorer }})</td>
                                        </tr>
                                        <tr>
                                            <th>Tenaga Kependidikan</th>
                                            <td>{{ $sekolah->jumlah_kependidikan }} orang</td>
                                        </tr>
                                        <tr>
                                            <th>Jumlah Siswa</th>
                                            <td>{{ $sekolah->jumlah_siswa_pria + $sekolah->jumlah_pria_perempuan }} siswa (L: {{ $sekolah->jumlah_siswa_pria }},
                                                P: {{ $sekolah->jumlah_siswa_perempuan }})</td>
                                        </tr>
                                    </table>

                                    <!-- Foto -->
                                    @if ($sekolah->foto_depan || $sekolah->logo_sekolah)
                                        <h4 class="mt-4">Dokumentasi</h4>
                                        <div class="row">
                                            @if ($sekolah->foto_depan)
                                                <div class="col-md-6 mb-3">
                                                    <p><strong>Foto Depan Sekolah</strong></p>
                                                    <img src="{{ asset('upload/sekolah/foto_depan/' . $sekolah->foto_depan) }}"
                                                        class="img-fluid rounded border" alt="Foto Depan Sekolah">
                                                </div>
                                            @endif
                                            @if ($sekolah->logo_sekolah)
                                                <div class="col-md-6 mb-3">
                                                    <p><strong>Logo Sekolah</strong></p>
                                                    <img src="{{ asset('upload/sekolah/logo_sekolah/' . $sekolah->logo_sekolah) }}"
                                                        class="img-fluid rounded border" alt="Logo Sekolah"
                                                        style="max-width: 300px">
                                                </div>
                                            @endif
                                            @if ($sekolah->denah_lokasi)
                                                <div class="col-md-6 mb-3">
                                                    <p><strong>Logo Sekolah</strong></p>
                                                    <img src="{{ asset('upload/sekolah/denah_lokasi/' . $sekolah->denah_lokasi) }}"
                                                        class="img-fluid rounded border" alt="Logo Sekolah"
                                                        style="max-width: 300px">
                                                </div>
                                            @endif
                                            @if ($sekolah->stuktur_organisasi)
                                                <div class="col-md-6 mb-3">
                                                    <p><strong>Logo Sekolah</strong></p>
                                                    <img src="{{ asset('upload/sekolah/stuktur_organisasi/' . $sekolah->stuktur_organisasi) }}"
                                                        class="img-fluid rounded border" alt="Logo Sekolah"
                                                        style="max-width: 300px">
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <div class="card-footer">
                                    <a href="#" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Kembali
                                    </a>
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
    @endpush
@endsection
