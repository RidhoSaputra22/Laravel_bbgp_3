<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 24px 28px; }
        body { font-family: Arial, sans-serif; font-size: 10px; }
        .kop-surat { margin-bottom: 16px; text-align: center; }
        .kop-surat img { width: 100%; height: auto; }
        h3 { margin: 8px 0 4px; text-align: center; }
        .periode { margin: 0 0 14px; text-align: center; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 6px 4px; text-align: center; }
        th { background: #eee; }
        td.nama { text-align: left; }
        tfoot td { font-weight: bold; background: #eee; }
        .kosong { padding: 18px; }
    </style>
</head>

<body>
    <div class="kop-surat">
        <img src="img_template/kop_baru.png" alt="Kop Surat">
    </div>

    <h3>LAPORAN KEGIATAN BBGTK</h3>
    <p class="periode">
        Periode:
        {{ $filters['tanggal_dari'] ?? '-' }} s.d. {{ $filters['tanggal_sampai'] ?? '-' }}
    </p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Kegiatan</th>
                <th>Tanggal Kegiatan</th>
                <th>Jumlah Peserta</th>
                <th>Jumlah Panitia</th>
                <th>Jumlah Tenaga Pendidik</th>
                <th>Jumlah Tenaga Kependidikan</th>
                <th>Jumlah Stakeholder</th>
                <th>Jumlah Narasumber</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reports as $report)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td class="nama">{{ $report['kegiatan']->nama_kegiatan }}</td>
                    <td>
                        {{ $report['kegiatan']->tgl_kegiatan }}
                        @if ($report['kegiatan']->tgl_selesai && $report['kegiatan']->tgl_selesai !== $report['kegiatan']->tgl_kegiatan)
                            s.d. {{ $report['kegiatan']->tgl_selesai }}
                        @endif
                    </td>
                    <td>{{ $report['peserta'] }}</td>
                    <td>{{ $report['panitia'] }}</td>
                    <td>{{ $report['tenaga_pendidik'] }}</td>
                    <td>{{ $report['tenaga_kependidikan'] }}</td>
                    <td>{{ $report['stakeholder'] }}</td>
                    <td>{{ $report['narasumber'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="kosong">Tidak ada data kegiatan sesuai filter.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($reports->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="3">Total</td>
                    <td>{{ $reports->sum('peserta') }}</td>
                    <td>{{ $reports->sum('panitia') }}</td>
                    <td>{{ $reports->sum('tenaga_pendidik') }}</td>
                    <td>{{ $reports->sum('tenaga_kependidikan') }}</td>
                    <td>{{ $reports->sum('stakeholder') }}</td>
                    <td>{{ $reports->sum('narasumber') }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>

</html>
