<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Cetak Honor {{ ucfirst($jenis) }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;

        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
        }

        .kop-surat {
            width: 100%;
            margin-bottom: 20px;
        }

        .kop-surat img {
            width: 100%;
            height: auto;
        }
    </style>
</head>

<body>
    <div class="kop-surat">
        <img src="img_template/kop_baru.png" alt="Kop Surat">
    </div>
    <table>
        <thead>
            <tr>
                <th style="width: 10px" rowspan="2">No</th>
                <th style="width: 250px; " rowspan="2">Nama Lengkap</th>
                <th style="width: 10px" rowspan="2">Gol</th>
                <th style="text-align:center" colspan="4">Perhitungan</th>
                <th rowspan="2">Jumlah Diterima</th>
            </tr>
            <tr>
                <th style="width: 10px">Realisasi JP</th>
                <th>Jumlah</th>
                <th>Jumlah Honor</th>
                <th>Pot</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($datas as $i => $honor)
                <tr>
                    <td>{{ ++$i }}</td>
                    <td>{{ $honor['nama'] }} </td>
                    <td>{{ explode('/', $honor['golongan'])[0] }}</td>
                    <td>{{ $honor['jp_realisasi'] }}.0</td>
                    <td>{{ $honor['jumlah'] }}</td>
                    <td>{{ $honor['jumlah_honor'] }}</td>
                    <td>{{ $honor['pot'] }}</td>
                    <td>{{ $honor['total'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
