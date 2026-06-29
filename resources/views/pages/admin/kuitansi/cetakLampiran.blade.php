<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>DAFTAR PEMBAYARAN PERJALANAN DINAS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
        }

        .header,
        .sub-header {
            text-align: left;
        }

        .header p,
        .sub-header p {
            margin: 0;
        }

        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }

        .footer {
            margin-top: 40px;
            float: right;
        }

        .footer p {
            margin: 0;
        }
    </style>
</head>

<body>
    <div class="">

        <div class="header">
            <p>Lampiran SPD</p>
            <p>Tanggal 28 Maret 2024</p>
        </div>
        <div class="sub-header">
            <p>DAFTAR PESERTA KEGIATAN: Pelatihan Penggunaan dan Pemanfaatan Chromebook</p>
            <p>TANGGAL PENYELENGGARAAN: 26 s.d 28 Maret 2024</p>
            <p>KOTA TEMPAT PENYELENGGARAAN: Makassar</p>
            <p>SATUAN KERJA: Balai Besar Guru Penggerak Provinsi Sulawesi Selatan</p>
            <p>KEMENTRIAN NEGARA/LEMBAGA: KEMENTERIAN PENDIDIKAN DAN KEBUDAYAAN, RISET DAN TEKNOLOGI</p>
        </div>

        <div style="font-size: 16px ;position: absolute; top: -20; right:20px; text-align:center" class="">

            <p>
                PERATURAN DIREKTUR JENDERAL PERBENDAHARAAN NOMOR PER-
            </p>
            <p>
                22/PB/2013 TENTANG KETENTUAN LEBIH LANJUT PELAKSANAAN
            </p>
            <p>
                PERJALANAN DINAS DALAM NEGERI BAGI PEJABAT NEGARA
            </p>
            <p>
                PEGAWAI NEGERI DAN PEGAWAI TIDAK TETAP
            </p>
        </div>

    </div>

    <table class="table-data">
        <thead>
            <tr>
                <th>NO</th>
                <th>NAMA PELAKSANA SPD</th>
                <th>NIP</th>
                <th>PANGKAT/ GOL</th>
                <th>JABATAN</th>
                <th>TEMPAT KEDUDUKAN ASAL</th>
                <th>TEMPAT KEDUDUKAN TUJUAN</th>
                <th>TINGKAT BIAYA PERJALANAN DINAS</th>
                <th>ALAT ANGKUT YANG DIGUNAKAN</th>
                <th>NOMOR & Tanggal SURAT TUGAS</th>
                <th>TANGGAL KEBERANGKATAN DARI TEMPAT KEDUDUKAN ASAL</th>
                <th>TANGGAL TIBA KEMBALI KEDUDUKAN ASAL</th>
                <th colspan="2">LAMANYA PERJALANAN DINAS</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $i => $v)
                <tr>
                    <td>{{ ++$i }} </td>
                    <td>{{ $v->peserta->nama }}</td>
                    <td>{{ $v->peserta->pegawai->nip  ?? $v->peserta->nip }}</td>
                    <td>{{ $v->peserta->golongan }}</td>
                    <td>{{ $v->peserta->pegawai->jabatan ?? $v->peserta->jabatan }}</td>
                    <td>{{ $v->kabupaten->name }}</td>
                    <td>{{ $v->lokasi_tujuan }}</td>
                    <td>C</td>
                    <td>Makassar</td>
                    <td>{{ $v->no_surat_tugas . ' tanggal ' . $v->tgl_surat_tugas }}</td>
                    <td>3/26/2024</td>
                    <td>3/28/2024</td>
                    <td>{{ $v->jumlah_hari }} </td>
                    <td>Hari</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">
        <p>Makassar, {{ $tgl_sekarang ?? '' }}</p>
        <p>Pejabat Pembuat Komitmen</p>
        <br><br><br>
        <p>Idhil Nur Mansyur, SE</p>
        <p style="margin-top: -10px"><b><u>NIP.19790522 200312 1 001</u></b></p>
    </div>
</body>

</html>