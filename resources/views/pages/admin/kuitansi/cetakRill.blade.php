<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Kuitansi Rencana Biaya Perjalanan Dinas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .text-title {
            font-size: 11px;
        }

        .container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
            position: relative;
        }

        .header {
            text-align: center;

        }

        .footer {
            text-align: left;
            margin-bottom: 20px;
        }

        .header h2 {
            text-align: center;
            font-size: 20px;
            margin-bottom: 10px;
            padding: 0;
        }

        .content {
            margin-bottom: 30px;
        }

        .content p {
            margin: 0;
            padding: 5px 0;
        }

        .highlight {
            /* background-color: #ffff00; */
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .table th,
        .table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        .table th {
            background-color: #fff;
            height: 1px;
            padding: 0;
            text-align: center;
        }

        .right-align {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .signature {
            margin-top: 50px;
        }

        .signature .name {
            margin-top: 60px;
        }

        .footer-surat {
            position: absolute;
            bottom: 0;
            width: 100%;
            background-color: white;
        }
    </style>
</head>

<body>
    <?php
    setlocale(LC_ALL, 'id_ID.UTF-8');

    
    $tgl_surat = strftime('%d %B %Y', strtotime($data->tgl_surat_tugas));
    $tgl_sekarang = strftime('%d %B %Y', strtotime(date('d-m-Y')));
    ?>
    <div class="container">
        <div style="margin-top: -40px">
            <img src="img_template/kop_baru.png" style="width: 100%;" alt="Kop Surat">
        </div>
        <hr>
        <div class="content" style="margin-top:-10px">
            <h2 style="text-align: center;"> DAFTAR PENGELUARAN RILL </h2>
            <table style="margin-top: -10px" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="width: 180px" class="text-title">Yang bertanda tangan dibawah ini :</td>

                </tr>
                <tr>
                    <td class="text-title">Nama</td>
                    <td>
                        <p><span class="highlight ">: {{ $data->peserta->nama }} </span></p>
                    </td>
                </tr>
                <tr style="padding: 10px">
                    <td class="text-title">NIP</td>
                    <td>
                        <p><span class="highlight">: {{ $data->peserta->nip }}</span></p>
                    </td>
                </tr>

                <tr>
                    <td valign="top" width="80" class="text-title">Jabatan</td>
                    <td>
                        <p style="text-align: justify">: {{ $data->peserta->status_keikutpesertaan ?? '' }}

                        </p>
                    </td>
                </tr>

            </table>

        </div>
        <div class="content">
            <table style="margin-top: -20px">
                <tr>
                    <td>Berdasarkan Surat Tugas Nomor : {{ $data->no_surat_tugas }} tanggal {{ $tgl_surat }}
                    </td>
                    <td>
                        <p> <span class="highlight"></span></p>
                    </td>
                </tr>
                <tr>
                    <td>dengan ini kami menyatakan dengan sesungguhnya bahwa :</td>
                    <td>
                        <p> <span class="highlight">

                            </span></p>
                    </td>
                </tr>

                <ol style="margin-left: -25px">
                    <li>Biaya transport atau biaya penginapan dibawah ini yang tidak dapat diperoleh bukti-bukti </li>

                    <p>pengeluarannya meliputi :</p>
                </ol>

            </table>


            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 10px"> No.</th>
                        <th style="width: 180px">Uraian</th>
                        <th style="width: 150px">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td valign="top">1</td>
                        <td>
                            Transport:
                            <br>
                            <br>


                            {{ $data->kabupaten->name }} - {{ $data->lokasi_tujuan }}, PP
                            <br><br>
                            <br>

                        </td>
                        <td>Rp. {{ number_format($data->total_transport ?? 0, 0, ',', '.') }}</td>
                    </tr>

                </tbody>


            </table>

            <ol start="2" style="margin-left: -30px">
                <li>
                    <p>
                        Jumlah uang tersebut pada angka 1 diatas benar-benar dikeluarkan untuk pelaksanaan perjalanan
                        Dinas
                        <br>
                        dimaksud dan apabila dikemudian hari terdapat kelebihan atas pembayaran, kami bersedia untuk
                        <br>
                        menyetorkan kelebihan tersebut ke Kas Negara.
                    </p>
                </li>
            </ol>

            <p>Demikian pernyataan ini kami buat dengan sebenarnya untuk dipergunakan sebagaimana mestinya.</p>

        </div>


        <div class="content">
            <p style=""></p>

        </div>

        <div class="footer-surat">
            <p>Mengetahui / Menyetujui <br> Pejabat Pembuat Komitmen,
            <table style="margin-top: -55px;">
                <tr style="margin-top: -40">
                    <td style=" width: 500px;">
                    </td>
                    <td valign="top"><span class="highlight ">
                            Makassar, {{ $tgl_surat }} <br>
                            Yang melaksanakan <br>
                            Perjalanan Dinas,
                        </span></td>
                </tr>
            </table>
            <br>
            <br>
            <br>
            <br>
            <div style="margin-top: -50px; padding-top: 40px;" class="signature">
                <table>
                    <tr>
                        <td style=" width: 500px;">
                            <p class="bold"><u>Idhil Nur Mansyur, SE</u>
                            <p style="margin-top: -10px">NIP.198306212009122002</p>
                            </p>
                        </td>
                        <td>
                            <p class="bold"><u> {{ $data->peserta->nama }} </u>
                            <p style="margin-top: -10px">NIP. {{ $data->peserta->nip }}</p>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>

</html>