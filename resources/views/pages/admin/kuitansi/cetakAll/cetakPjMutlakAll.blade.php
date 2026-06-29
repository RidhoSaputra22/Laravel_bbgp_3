<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Kuitansi Rencana Biaya Perjalanan Dinas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .text-title {
            /* font-size: 11px; */
        }

        .container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
            position: relative;
            page-break-after: always;
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
            /* background-color: white; */
        }
    </style>
</head>

<body>
    
    @foreach ($datas as $i => $data)
    <?php
    set_time_limit(300); // 300 seconds or 5 minutes
    ini_set('memory_limit', '256M');
    
    ?>
        <?php
        setlocale(LC_ALL, 'id_ID.UTF-8');
        
        $tgl_surat = strftime('%d %B %Y', strtotime($data->tgl_surat_tugas));
        $tgl_sekarang = strftime('%d %B %Y', strtotime(date('d-m-Y')));
        $tgl_mulai = strftime('%d', strtotime($data->peserta->kegiatan->tgl_kegiatan));
        $tgl_selesai = strftime('%d %B %Y', strtotime($data->peserta->kegiatan->tgl_selesai));
        ?>
        
        <div class="container">
            <img style="position: absolute; left: 20px; top: 20px; width: 110px;"
                src="img_template/iconbbgp.png" alt="Logo Kiri">
            <div class="header">
                <h2 style="margin-bottom: -20px">KEMENTRIAN PENDIDKAN DAN KEBUDAYAAN</h2>
                <h2 style="margin-bottom: -20px">RISET, DAN TEKNOLOGI</h2>
                <h2 style="margin-bottom: -20px">BALAI BESAR GURU PENGGERAK</h2>
                <h2 style="margin-bottom: -15px">SULAWESI SELATAN</h2>
                <p style="margin-bottom: -15px">Jalan Adhyaksa No. 2 Panakkukang Makassar</p>
                <p style="margin-bottom: -15px">Telepon : (0411) 440065, No. Fax. (0411) 421460 Kode Pos 90231</p>
                <p>Laman: bbgtk-sulsel.id email: bppauddikmassulsel@kemdikbud.go.id</p>
            </div>
            <hr>
            <div class="content" style="margin-top: -30px">
                <h2 style="text-align: center;">SURAT PERNYATAAN TANGGUNG JAWAB MUTLAK</h2>
                <p style="margin-top: 20px" class="text-title">Yang bertanda tangan dibawah ini :</p>
                <table cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="width: 110px" class="text-title">Nama</td>
                        <td>
                            <p><span class="highlight">: {{ $data->peserta->nama }}</span></p>
                        </td>
                    </tr>
                    <tr style="padding: 10px">
                        <td class="text-title">NIP</td>
                        <td>
                            <p><span class="highlight">:
                                    {{ $data->peserta->pegawai->nip ?? $data->peserta->nip }}</span></p>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top" width="80" class="text-title">Jabatan</td>
                        <td>
                            <p style="text-align: justify">:
                                {{ $data->peserta->pegawai->jabatan ?? $data->peserta->nip }}</p>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="content">
                <table style="margin-top: -30px">
                    <tr>
                        <td>Menyatakan dengan sesungguhnya bahwa: :</td>
                        <td>
                            <p><span class="highlight"></span></p>
                        </td>
                    </tr>
                    <ol style="margin-left: -25px; padding-top: 10px">
                        <li>
                            <p style="text-align: justify;">
                                Semua dokumen yang saya gunakan dalam melakukan kegiatan Transport Petugas dalam rangka
                                pelaksanaan

                                {{ $data->peserta->kegiatan->nama_kegiatan }}
                                pada tanggal {{ $tgl_mulai }} s.d {{ $tgl_selesai }}
                                di {{ $data->peserta->kegiatan->tempat_kegiatan }}.
                                berdasarkan Surat Tugas Nomor {{ $data->no_surat_tugas }} Tanggal {{ $tgl_surat }}

                            </p>
                        </li>
                        <li style="padding-top: 25px">
                            Bertanggung jawab sepenuhnya atas kebenaran seluruh penggunaan biaya perjalanan dinas
                            termasuk bukti-bukti pertanggungjawaban perjalanan dinas. Sehubungan dengan hal itu, maka
                            saya menyatakan tidak melakukan:
                        </li>
                        <ol type="a">
                            <li>Pemalsuan dokumen;</li>
                            <li>Tindakan berupa menaikkan dari harga sebenarnya (mark up);</li>
                            <li>Perjalanan dinas rangkap.</li>
                        </ol>
                    </ol>
                </table>

                <p>Demikian pernyataan ini saya buat dengan sesungguhnya dan apabila dikemudian hari terbukti <br>
                    tidak benar serta terdapat selisih biaya perjalanan dinas dan pengeluaran lainnya, maka saya <br>
                    bersedia mengembalikan ke kas negara.
                </p>

            </div>
            <div class="content">
                <p></p>
            </div>
            <div class="footer-surat">
                <table style="margin-top: -55px; margin-left: -30px">
                    <tr style="margin-top: -40">
                        <td style=" width: 500px;"></td>
                        <td valign="top"><span class="highlight">Makassar, {{ $tgl_sekarang }}<br>Yang melaksanakan
                                <br>Perjalanan Dinas,</span></td>
                    </tr>
                </table>
                <br><br><br><br>
                <div style="margin-top: -50px; margin-left: -30px; padding-top: 0px;" class="signature">
                    <table>
                        <tr>
                            <td style="width: 500px;"></td>
                            <td>
                                <p><b><u> {{ $data->peserta->nama }}</u></b></p>
                                <p style="margin-top: -10px">NIP. {{ $data->peserta->nip }}</p>

                            </td>
                        </tr>
                    </table>
                </div>
            </div>

        </div>
    @endforeach
</body>

</html>
