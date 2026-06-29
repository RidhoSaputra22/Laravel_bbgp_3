<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Data Eksternal BBGTK</title>
    <style>
        /* Gaya CSS */
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            max-width: 100%;
            word-wrap: break-word;
        }
        th {
            background-color: #f2f2f2;
        }
        .text-center {
            text-align: center;
        }
        .img-fluid {
            max-width: 100px;
            height: auto;
        }
        .badge {
            padding: 5px 10px;
            font-size: 12px;
            border-radius: 5px;
        }
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        .badge-danger {
            background-color: #dc3545;
            color: white;
        }
        .btn {
            display: inline-block;
            padding: 6px 12px;
            margin-bottom: 0;
            font-size: 14px;
            font-weight: 400;
            line-height: 1.42857143;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            cursor: pointer;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .btn-primary {
            color: #fff;
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            color: #fff;
            background-color: #0069d9;
            border-color: #0062cc;
        }
        .btn-info {
            color: #fff;
            background-color: #17a2b8;
            border-color: #17a2b8;
        }
        .btn-info:hover {
            color: #fff;
            background-color: #138496;
            border-color: #117a8b;
        }
        .btn-warning {
            color: #212529;
            background-color: #ffc107;
            border-color: #ffc107;
        }
        .btn-warning:hover {
            color: #212529;
            background-color: #e0a800;
            border-color: #d39e00;
        }
        .btn-danger {
            color: #fff;
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .btn-danger:hover {
            color: #fff;
            background-color: #c82333;
            border-color: #bd2130;
        }
    </style>
</head>
<body>
    <h2>Data Eksternal BBGTK Sul-Sel</h2>
    <table>
        <thead>
            <tr>
                <th class="text-center">#</th>
                {{-- <th>Pas Foto</th> --}}
                <th>NPSN Sekolah</th>
                <th>Nama Lengkap</th>
                <th>NPWP</th>
                <th>NUPTK</th>
                <th>Email</th>
                <th>Nomor KTP</th>
                <th>Tempat, Tanggal Lahir</th>
                <th>Alamat Rumah</th>
                <th>Jenis Kelamin</th>
                <th>Status Kepegawaian</th>
                <th>Agama</th>
                <th>Pendidikan Terakhir</th>
                <th>Ketenagaan</th>
                <th>Jabatan</th>
                <th>Kategori Jabatan</th>
                <th>Tugas Jabatan</th>
                <th>Asal Kabupaten/Kota</th>
                <th>Satuan Pendidikan</th>
                {{-- <th>Jabatan Sekolah</th> --}}
                <th>Kecamatan Sekolah</th>
                <th>Kabupaten Sekolah</th>
                <th>Nomor Aktif</th>
                <th>No Rekening</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($data))
                <tr>
                    <td>1</td>
                    {{-- <td><img src="{{ asset('/upload/guru/' . $data->pas_foto) }}"
                                alt="" class="img-fluid"></td> --}}
                    <td>{{ $data->npsn_sekolah }} - {{ $data->sekolah->nama_sekolah ?? '' }}</td>
                    <td>{{ $data->nama_lengkap }}</td>
                    <td>{{ $data->npwp }}</td>
                    <td>{{ $data->nuptk }}</td>
                    <td>{{ $data->email }}</td>
                    <td>{{ $data->no_ktp }}</td>
                    <td>{{ $data->tempat_lahir . ', ' . $data->tgl_lahir }}</td>
                    <td>{{ $data->alamat_rumah }}</td>
                    <td>{{ $data->gender }}</td>
                    <td>{{ $data->status_kepegawaian }}</td>
                    <td>{{ $data->agama }}</td>
                    <td>{{ $data->pendidikan }}</td>
                    <td>{{ $data->eksternal_jabatan }}</td>
                    <td>{{ $data->jenis_jabatan }}</td>
                    <td>{{ $data->kategori_jabatan ? $data->kategori_jabatan : 'Tidak ada' }}</td>
                    <td>{{ $data->tugas_jabatan ? $data->tugas_jabatan : 'Tidak ada' }}</td>
                    <td>{{ $data->kabupaten }}</td>
                    <td>{{ $data->satuan_pendidikan }}</td>
                    {{-- <td>{{ $data->jabatan }}</td> --}}
                    <td>{{ $data->sekolah->kecamatan ?? '' }}</td>
                    <td>{{ $data->sekolah->kabupaten ?? '' }}</td>
                    <td>No. Hp: {{ $data->no_hp }}<br>
                        No. Whatsapp: {{ $data->no_wa }}</td>
                    <td>{{ $data->no_rek }} ({{ $data->jenis_bank }})</td>
                </tr>
            @else
                <tr>
                    <td colspan="24" class="text-center">Tidak ada data yang tersedia</td>
                </tr>
            @endif
        </tbody>
    </table>
</body>
</html>
