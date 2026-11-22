<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 24px 28px 34px; }
        body { margin: 0; padding-bottom: 28px; color: #1f2933; font-family: Arial, sans-serif; font-size: 10px; }
        .kop-surat { margin-bottom: 14px; text-align: center; }
        .kop-surat img { width: 100%; height: auto; }
        h3 { margin: 0; color: #123b5d; font-size: 16px; letter-spacing: .4px; text-align: center; }
        .subjudul { margin: 4px 0 5px; color: #52606d; font-size: 10px; text-align: center; }
        .periode { margin: 0 0 14px; color: #52606d; text-align: center; }
        .ringkasan { width: 100%; margin-bottom: 14px; }
        .ringkasan table { width: 100%; border-collapse: separate; border-spacing: 6px 0; }
        .ringkasan td { width: 25%; padding: 7px; border: 1px solid #c7d3dd; background: #f3f7fa; text-align: center; }
        .ringkasan .angka { display: block; color: #123b5d; font-size: 15px; font-weight: bold; }
        .ringkasan .label { display: block; margin-top: 2px; color: #52606d; font-size: 9px; }
        .judul-tabel { margin: 0 0 6px; color: #123b5d; font-size: 11px; }
        table.laporan { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .laporan th, .laporan td { border: 1px solid #aebdca; padding: 7px 5px; text-align: center; vertical-align: middle; }
        .laporan th { color: #fff; background: #245b7a; font-size: 9px; }
        .laporan td { color: #263238; }
        .laporan tbody tr:nth-child(even) td { background: #f7fafc; }
        .laporan td.nama { text-align: left; line-height: 1.35; }
        .laporan td.tanggal { line-height: 1.35; }
        .laporan tfoot td { color: #123b5d; background: #dce8ef; font-weight: bold; }
        .kosong { padding: 18px !important; color: #52606d; }
        .footer { position: fixed; right: 0; bottom: -18px; left: 0; padding-top: 7px; border-top: 1px solid #b8c4cc; color: #667784; font-size: 9px; text-align: center; }
    </style>
</head>

<body>
    <div class="kop-surat">
        <img src="img_template/kop_baru.png" alt="Kop Surat">
    </div>

    <h3>LAPORAN REKAPITULASI KEGIATAN</h3>
    <p class="subjudul">BBGTK Provinsi Sulawesi Selatan</p>
    <p class="periode">
        Periode Pelaporan:
        {{ ($filters['tanggal_dari'] ?? null) ? \Carbon\Carbon::parse($filters['tanggal_dari'])->locale('id')->translatedFormat('d F Y') : '-' }}
        s.d.
        {{ ($filters['tanggal_sampai'] ?? null) ? \Carbon\Carbon::parse($filters['tanggal_sampai'])->locale('id')->translatedFormat('d F Y') : '-' }}
    </p>

    <div class="ringkasan">
        <table>
            <tr>
                <td>
                    <span class="angka">{{ $reports->count() }}</span>
                    <span class="label">Total Kegiatan</span>
                </td>
                <td>
                    <span class="angka">{{ $reports->sum('peserta') }}</span>
                    <span class="label">Peserta</span>
                </td>
                <td>
                    <span class="angka">{{ $reports->sum('panitia') }}</span>
                    <span class="label">Panitia</span>
                </td>
                <td>
                    <span class="angka">{{ $reports->sum('narasumber') }}</span>
                    <span class="label">Narasumber</span>
                </td>
            </tr>
        </table>
    </div>

    <p class="judul-tabel">Rincian Kegiatan</p>

    <table class="laporan">
        <colgroup>
            <col style="width: 4%">
            <col style="width: 27%">
            <col style="width: 14%">
            <col style="width: 9%">
            <col style="width: 8%">
            <col style="width: 10%">
            <col style="width: 11%">
            <col style="width: 9%">
            <col style="width: 8%">
        </colgroup>
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
                    <td class="tanggal">
                        {{ \Carbon\Carbon::parse($report['kegiatan']->tgl_kegiatan)->locale('id')->translatedFormat('d F Y') }}
                        @if ($report['kegiatan']->tgl_selesai && $report['kegiatan']->tgl_selesai !== $report['kegiatan']->tgl_kegiatan)
                            <br>s.d. {{ \Carbon\Carbon::parse($report['kegiatan']->tgl_selesai)->locale('id')->translatedFormat('d F Y') }}
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

    <div class="footer">
        Dokumen ini dibuat secara otomatis oleh Sistem BBGTK Provinsi Sulawesi Selatan
        &middot; {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y H:i') }} WITA
    </div>
</body>

</html>
