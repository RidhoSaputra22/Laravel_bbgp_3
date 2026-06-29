<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Amlop</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 20px;
            text-align: left;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            height: 100vh;
            /* Mengatur tinggi agar isi berada di tengah */
            page-break-after: always;
        }

        table {
            font-size: 18px;
        }

        .table-container {
            margin-left: 20px;
        }

        .table-container table {
            border-collapse: collapse;
            margin: 0 20px;
        }

        .table-container table td {
            /* border: 2px solid white; */
            padding: 10px;
            min-width: 25px;
            /* Menyesuaikan lebar minimum */
            min-height: 25px;
            /* Menyesuaikan tinggi minimum */
            text-align: center;
            /* Pusatkan teks */
        }

        ul {
            list-style: none;
            padding-left: 0;
        }

        li {
            margin-bottom: 10px;
            display: flex;
            align-items: flex-start;
        }

        li>div {
            margin-left: 10px;
        }

        li>div:first-child {
            flex: 0 0 200px;
            /* Lebar tetap untuk teks kiri */
            font-weight: bold;
        }
    </style>
</head>

<body>


    @foreach ($datas as $i => $data)
        <?php
        setlocale(LC_ALL, 'id_ID.UTF-8');
        
        $tgl_kegiatan = strftime('%d %B', strtotime($data->peserta->kegiatan->tgl_kegiatan ?? ''));
        $tgl_selesai = strftime('%d %B %Y', strtotime($data->peserta->kegiatan->tgl_selesai ?? ''));
        ?>
        <div class="container">
            <ul>
                <li>
                    {{-- <span><b>Nama Kegiatan</b></span> --}}
                    <span style="margin-left: 55px"> {{ $data->internal->kegiatan }}</span>
                </li>
                {{-- <li>
                    <span><b>Lokasi Kegiatan</b></span>
                    <span>: {{ $data->peserta->kegiatan->tempat_kegiatan }}</span>
                </li>
                <li>
                    <span><b>Tanggal Kegiatan</b></span>
                    <span>:{{ $tgl_kegiatan ?? '' }} s.d {{ $tgl_selesai ?? '' }}</span>
                </li> --}}
                <!-- Tambahkan baris selanjutnya sesuai kebutuhan -->
            </ul>

            <div class="table-container">
                <table cellspacing="0" border="0" cellpadding="0">
                    <tr>
                        <td>1</td>
                        <td style="text-align: left;" colspan="4">NAMA PETUGAS : {{ $data->internal->nama }} </td>
                    </tr>
                    <tr>
                        <?php
                        
                        $total = $data->internal->transport_pergi + $data->internal->transport_pulang + $data->internal->bill_penginapan + $data->internal->hari_1 + $data->internal->hari_2 + $data->internal->hari_3 + $data->internal->bill_penginapan;
                        ?>
                        <td></td>
                        <td style="width: 350px; text-align: right;">Rp.
                            {{ number_format($total ?? 0, 0, ',', '.') }}</td>
                        {{-- <td style="width: 200px">Rp. {{ number_format($data->total_transport ?? 0, 0, ',', '.') }} </td>
                        <td style="width: 320px; " colspan="2"><b> Keterangan </b></td> --}}
                    </tr>

                    <tr>
                        {{-- <td></td> --}}
                        {{-- loop --}}
                        {{-- <td>{{ $data->kabupaten->name . ' - ' . $data->lokasi_tujuan }}</td>
                        <td>Rp. {{ number_format($data->total_transport ?? 0, 0, ',', '.') }}</td>
                        <td colspan="2">{{ $data->jenis_angkutan }}</td> --}}

                    </tr>
                    <!-- Tambahkan baris selanjutnya sesuai kebutuhan -->

                </table>
            </div>


        </div>
    @endforeach


</body>

</html>
