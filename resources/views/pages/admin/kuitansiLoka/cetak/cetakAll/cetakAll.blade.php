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

        .header,
        .footer {
            text-align: left;
            margin-bottom: 20px;
        }

        .header h2 {
            text-align: center;
            font-size: 20px;
            text-decoration: underline;
            margin-bottom: 10px;
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

        /* CSS for page breaks */
        .page-break {
            page-break-before: always;
        }
    </style>
</head>

<body>

    {{-- {{ dd($datas) }} --}}

    @foreach ($datas as $i => $data)
        <?php
        setlocale(LC_ALL, 'id_ID.UTF-8');
        
        $tgl_surat = strftime('%d %B', strtotime(date('d-m-Y')));
        $tgl_sekarang = strftime('%d %B', strtotime(date('d-m-Y')));
        $tgl_kegiatan = strftime('%d', strtotime($data->internal->tgl_kegiatan));
        $tgl_selesai_kegiatan = strftime('%d %B %Y', strtotime($data->internal->tgl_selesai_kegiatan));
        ?>
        <?php
        $total = $data->internal->transport_pergi + $data->internal->transport_pulang + $data->internal->bill_penginapan + $data->internal->hari_1 + $data->internal->hari_2 + $data->internal->hari_3;
        
        ?>
        {{-- {{ dd($tgl_sekarang) }} --}}
        <div class="container">
            @if ($i > 0)
                <div class="page-break"></div>
            @endif
            <div class=""
                style="
                    position:absolute;
                    right: 100px;
                    top:-20;
                ">
                <div class="header">
                    <p>
                    <table>
                        <tr>
                            <td>
                                <strong>Tahun Anggaran</strong>
                            </td>
                            <td>
                                : {{ $data->tahun_anggaran }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>Nomor Bukti </strong>
                            </td>
                            <td>
                                : {{ $data->no_bukti ?? '' }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>Kode Anggaran</strong>
                            </td>
                            <td>
                                : {{ $data->kode_anggaran }}
                            </td>
                        </tr>

                    </table>
                    </p>
                </div>

            </div>
            <div class="content" style="margin-top:50px">
                <h2 style="text-align: center; letter-spacing: 5"><i> KUITANSI </i></h2>
                <table style="margin-top: -10px" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="text-title"><strong>Sudah Terima Dari</strong></td>
                        <td>
                            <p>: Kuasa Pengguna Anggaran BBGTK Prop Sulawesi Selatan</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-title"><strong>Jumlah Uang</strong></td>
                        <td>

                            <p><span class="highlight bold">: Rp. {{ number_format($total ?? 0, 0, ',', '.') }}
                                </span></p>
                        </td>
                    </tr>
                    <tr style="padding: 10px">
                        <td class="text-title"><strong>Terbilang</strong></td>
                        <td>
                            <p><span class="highlight">: ##</span></p>
                        </td>
                    </tr>

                    <tr>
                        <td valign="top" width="80" class="text-title"><strong>Untuk Pembayaran</strong></td>
                        <td>
                            <p style="text-align: justify">:
                                <span class="highlight">
                                    Biaya Perjalanan Petugas {{ $data->internal->kegiatan }} yang dilaksanakan di
                                    {{ $data->internal->kota }} pada tanggal {{ $tgl_kegiatan }} s/d
                                    {{ $tgl_selesai_kegiatan }} dengan rincian sebagai berikut :
                                </span>
                            </p>
                        </td>
                    </tr>

                </table>

            </div>
            <div class="content">
                <p style="text-align: center; margin-top: -20px"><strong>RINCIAN BIAYA PERJALANAN DINAS</strong></p>
                <table style="margin-top: -20px">
                    <tr>
                        <td><strong>Lampiran SPD No.:</strong></td>
                        <td>
                            <p> <span class="highlight">:</span></p>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Tanggal:</strong></td>
                        <td>
                            <p> <span class="highlight">:</span></p>
                        </td>
                    </tr>
                </table>


                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 10px"> No.</th>
                            <th style="width: 250px">PERINCIAN BIAYA</th>
                            <th style="width: 130px">JUMLAH</th>
                            <th>KETERANGAN</th>
                        </tr>
                    </thead>
                    <tbody>

                        <tr>
                            <td valign="top">
                                1
                            </td>

                            <td valign="top">Transport: <br>
                                <ul style="list-style-type: none; margin: -1px 0 0 -25px">

                                    <li>
                                        {{ $data->internal->kota }} , PP
                                    </li>

                                    {{-- <table style="padding-top: 55px" border="0" cellspacing:="0" cellpadding="0">
                                        <tr>
                                            <td style="border:solid 0px white;">
                                                <li>Uang Harian {{ $data->jumlah_hari }} hari Rp.
                                                    {{ number_format($data->uang_harian ?? 0, 0, ',', '.') }} </li>
                                            </td>
                                        </tr>
                                    </table>
    
                                    <table border="0" cellspacing:="0" cellpadding="0">
                                        <tr>
                                            <td style="border:solid 0px white;">
                                                <li>Penginapan 4 hari
                                                    {{ number_format($data->biaya_penginapan ?? 0, 0, ',', '.') }}</li>
                                            </td>
                                        </tr>
                                    </table> --}}

                                </ul>

                            </td>

                            <td>Rp.
                                {{ number_format($data->internal->transport_pergi + $data->internal->transport_pulang ?? 0, 0, ',', '.') }}
                            </td>
                            {{-- <td> {{ $data->jenis_angkutan }} </td> --}}
                            <td> </td>
                        </tr>

                        <tr>
                            <td>2</td>
                            <?php
                            $hari = 0;
                            if ($data->internal->hari_1 != 0 && $data->internal->hari_2 && $data->internal->hari_3) {
                                $hari = 3;
                            } elseif ($data->internal->hari_1 !== 0 && $data->internal->hari_2 !== 0) {
                                $hari = 2;
                            } elseif ($data->internal->hari_1 !== 0) {
                                $hari = 1;
                            }
                            
                            ?>
                            <td>Uang Harian {{ $hari }} hari Rp.
                                {{ number_format($data->internal->hari_1 + $data->internal->hari_2 + $data->hari_3 ?? 0, 0, ',', '.') }}
                            </td>
                            <td>Rp.
                                {{ number_format($data->internal->hari_1 + $data->internal->hari_2 + $data->hari_3 ?? 0, 0, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>

                        <tr>

                            <td valign="top">
                                3
                            </td>
                            {{-- {{ dd($hari - 1 < 0 ? 0 : $hari) }} --}}
                            <td>Penginapan {{ $hari - 1 < 0 ? 0 : $hari }} malam
                                Rp. {{ number_format($data->biaya_penginapan ?? 0, 0, ',', '.') }}
                            </td>
                            <td>Rp.
                                {{ number_format($data->internal->hari_1 + $data->internal->hari_2 + $data->hari_3 + $data->internal->bill_penginapan ?? 0, 0, ',', '.') }}
                            </td>
                            {{-- <td> Rp. {{ number_format($data->biaya_penginapan ?? 0, 0, ',', '.') }} * 30% </td> --}}
                            <td> </td>
                        </tr>

                    </tbody>
                    <tfoot style="">
                        <tr style="padding: 0 !important; text-align: center">
                            <td colspan="2" style="text-align: center" class=" bold">Total</td>
                            <?php
                            $total_kuitansi = $data->internal->hari_1 + $data->internal->hari_2 + $data->hari_3 + $data->internal->bill_penginapan + $data->internal->transport_pergi + $data->internal->transport_pulang;
                            ?>
                            <td>Rp. {{ number_format($total_kuitansi ?? 0, 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>

            </div>

            <div style="margin-top: -30px; margin-left: 500px;  " class="content">
                <p style="padding:0"><strong>Makassar, {{ $tgl_sekarang }} 2024</strong></p>
                <p style="padding:0">Telah menerima jumlah uang sebesar <br> <span class="highlight bold">Rp. <span
                            style="margin-left: 50px">
                            {{ number_format($total_kuitansi ?? 0, 0, ',', '.') }}</span></span></p>
                <p style="padding:0"><strong>Yang menerima</strong></p>
                <p style="padding-top:50px" class="highlight bold">{{ $data->internal->nama }}</p>
                <p style="padding:0" class="highlight ">NIP. {{ $data->internal->nip }}</p>
            </div>
            <hr style="margin-top: -25px">

            <div class="content">
                <p style="text-align: center"><strong>PERHITUNGAN SPD RAMPUNG</strong></p>
                <table>
                    <tr style="padding: 0; margin:0;">
                        <td style="padding: 0;  width: 500px;">
                            <p>Ditetapkan sejumlah </p>
                        </td>
                        <td><span class="highlight bold">: Rp.
                                {{ number_format($total_kuitansi ?? 0, 0, ',', '.') }}</span></td>
                    </tr>
                    <tr>
                        <td style="padding: 0; width: 500px;">
                            <p>Yang telah dibayar semula </p>
                        </td>
                        <td><span class="highlight bold">: Rp. -</span></td>
                    </tr>
                    <tr>
                        <td style="padding: 0; width: 500px;">
                            <p>Sisa kurang/lebih </p>
                        </td>
                        <td><span class="highlight bold">: Rp. -</span></td>
                    </tr>
                    <tr>
                        <td style="padding: 0; width: 500px;">
                            <p>Setuju dibayar</p>
                        </td>
                        <td><span class="highlight ">
                                <p>Lunas dibayar</p>
                            </span></td>
                    </tr>
                </table>
            </div>

            <div class="footer-surat">
                <table style="margin-top: -55px;">
                    <tr>
                        <td style=" width: 500px;">
                            <p>Pejabat Pembuat Komitmen,
                        </td>
                        <td><span class="highlight ">Bendahara Pengeluaran, </span></td>
                    </tr>
                </table>
                <br>
                <br>
                <br>
                <br>
                <div style="margin-top: -50px" class="signature">
                    <table>
                        <tr>
                            <td style=" width: 500px;">
                                <p class="bold"><u>Idhil Nur Mansyur, SE</u>
                                <p style="margin-top: -10px">NIP.198306212009122002</p>
                                </p>
                            </td>
                            <td>
                                <p class="bold"><u> A. Rapiatni A. Hasan, S.Kom., MM</u>
                                <p style="margin-top: -10px">NIP.197905222003121001</p>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
</body>

</html>
