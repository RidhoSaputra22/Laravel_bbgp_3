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

        .header {
            text-align: center;
            position: fixed;
            top: 0;
            width: 100%;
            background: white;
            padding: 10px 0;
            margin-bottom: 100px;
        }

        .footer {
            text-align: left;
            position: fixed;
            bottom: -20px;
            right: 0;
            /* width: 100%; */
            /* background: white; */
            padding: 10px 0;
        }

        .content {
            margin-top: 150px; /* Adjust this value if needed */
            margin-bottom: 60px;
            page-break-inside: avoid;
        }

        .table-data {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            page-break-inside: auto;
            margin-top: -50px;
        }

        th,
        td {
            border: 1px solid black;
            padding: 8px;
            word-wrap: break-word;
        }

        th {
            /* background-color: #ff; */
        }

        thead {
            display: table-header-group;
        }

        tfoot {
            display: table-footer-group;
        }
    </style>
</head>

<body>
    <?php
    setlocale(LC_TIME, 'id_ID.UTF-8');
    // $tgl_surat = strftime('%d %B %Y', strtotime($data->tgl_surat_tugas));
    // $tgl_sekarang = strftime('%d %B %Y', strtotime(date('d-m-Y')));
    
    // Inisialisasi variabel total
    $total_transport = 0;
    $total_uang = 0;
    $total_terima = 0;
    ?>
    <div class="header">
        <p style="margin-bottom: -20px">DAFTAR PEMBAYARAN PERJALANAN DINAS</p>
        <p style="margin-bottom: -20px">Pelatihan Penggunaan dan Pemanfaatan Chromebook</p>
        <p style="margin-bottom: -20px">SESUAI SURAT TUGAS 07/106.18/SMPN3/SS/III/2024 tanggal 25-03-2024</p>
        <p style="margin-bottom: -15px"><b>Kode Anggaran : DI.5634.QDC.011.052.DA.524119</b></p>
    </div>

    <div class="container content">
        <table class="table-data">
            <thead>
                <tr>
                    <th class="text-center">No</th>
                    <th>Nama Lengkap</th>
                    <th>Instansi</th>
                    <th>Asal - Tujuan</th>
                    <th>Transport</th>
                    <th>Uang Harian</th>
                    <th>Jumlah Diterima</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $i => $v)
                    <?php
                    $total_transport += $v->total_transport;
                    $total_uang += $v->uang_harian;
                    $total_terima += $v->total_terima;
                    ?>
                    <tr>
                        <td>{{ ++$i }}</td>
                        <td>{{ $v->peserta->nama }}</td>
                        <td>{{ $v->peserta->instansi }}</td>
                        <td>{{ $v->kabupaten->name . ' - ' . $v->lokasi_tujuan }}</td>
                        <td>Rp. {{ number_format($v->total_transport ?? 0, 0, ',', '.') }}</td>
                        <td>Rp. {{ number_format($v->uang_harian ?? 0, 0, ',', '.') }}</td>
                        <td>Rp. {{ number_format($v->total_terima ?? 0, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4">Total</th>
                    <th>Rp. {{ number_format($total_transport, 0, ',', '.') }}</th>
                    <th>Rp. {{ number_format($total_uang, 0, ',', '.') }}</th>
                    <th>Rp. {{ number_format($total_terima, 0, ',', '.') }}</th>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="footer">
        <div style="text-align: left;">
            <p>Makassar, {{ $tgl_sekarang ?? '' }}</p>
            <p>Pejabat Pembuat Komitmen</p>
            <br><br><br>
            <p>Idhil Nur Mansyur, SE</p>
            <p style="margin-top: -10px"><b><u>NIP.19790522 200312 1 001</u></b></p>
        </div>
    </div>
</body>

</html>
