<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Kegiatan {{ $guru->nama_lengkap }}</title>
    <style>
        @page { margin: 24px 28px; }
        body { margin: 20px; padding: 0; font-family: Arial, sans-serif; font-size: 12px; }
        .kop-surat { margin-bottom: 18px; text-align: center; }
        .kop-surat img { width: 100%; height: auto; }
        h3 { margin: 0 0 6px; text-align: center; font-size: 16px; }
        .subjudul { margin: 0 0 18px; color: #555; text-align: center; font-size: 11px; }
        .identitas { margin-bottom: 16px; padding: 8px 10px; border: 1px solid #999; }
        .identitas table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .identitas td { padding: 3px 4px; vertical-align: top; }
        .identitas .label { width: 18%; font-weight: bold; white-space: nowrap; }
        .identitas .separator { width: 2%; }
        .ringkasan { margin-bottom: 16px; padding: 8px 10px; border-left: 4px solid #1976b9; background: #f1f5f8; }
        .keterangan { margin: 0 0 12px; line-height: 1.5; text-align: justify; }
        .judul-daftar { margin: 0; font-weight: bold; }
        ul { margin-top: 8px; padding-left: 22px; }
        li { margin-bottom: 9px; line-height: 1.45; }
        .detail-kegiatan { color: #555; font-size: 11px; }
        .catatan { position: fixed; right: 0; bottom: 0; left: 0; margin: 0; padding-top: 8px; border-top: 1px solid #bbb; color: #666; font-size: 10px; }
    </style>
</head>

<body>
    <div class="kop-surat">
        <img src="img_template/kop_baru.png" alt="Kop Surat">
    </div>

    <h3>LAPORAN KEGIATAN YANG DIIKUTI</h3>
    <p class="subjudul">BBGTK Provinsi Sulawesi Selatan</p>

    <div class="identitas">
        <table>
            <tr>
                <td class="label">Nama Lengkap</td>
                <td class="separator">:</td>
                <td>{{ $guru->nama_lengkap ?: '-' }}</td>
                <td class="label">NIK</td>
                <td class="separator">:</td>
                <td>{{ $guru->no_ktp ?: '-' }}</td>
            </tr>
            <tr>
                <td class="label">NIP</td>
                <td class="separator">:</td>
                <td>{{ $guru->nip ?: '-' }}</td>
                <td class="label">Status Kepegawaian</td>
                <td class="separator">:</td>
                <td>{{ $guru->status_kepegawaian ?: '-' }}</td>
            </tr>
            <tr>
                <td class="label">Satuan Pendidikan</td>
                <td class="separator">:</td>
                <td>{{ $guru->satuan_pendidikan ?: '-' }}</td>
                <td class="label">Ketenagaan</td>
                <td class="separator">:</td>
                <td>{{ $guru->eksternal_jabatan ?: '-' }}</td>
            </tr>
            <tr>
                <td class="label">Jenis Jabatan</td>
                <td class="separator">:</td>
                <td>{{ $guru->jenis_jabatan ?: '-' }}</td>
                <td class="label">Kabupaten/Kota</td>
                <td class="separator">:</td>
                <td>{{ $guru->kabupaten ?: '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="ringkasan">
        <strong>Total Kegiatan Diikuti:</strong> {{ $kegiatan->count() }} kegiatan
    </div>

    <p class="keterangan">
        Berdasarkan data registrasi pada sistem BBGTK Provinsi Sulawesi Selatan, berikut adalah kegiatan yang tercatat
        telah diikuti oleh yang bersangkutan:
    </p>

    <p class="judul-daftar">Daftar Kegiatan</p>
    <ul>
        @forelse ($kegiatan as $item)
            <li>
                <strong>{{ $item->nama_kegiatan }}</strong><br>
                <span class="detail-kegiatan">
                    Tanggal:
                    {{ $item->tgl_kegiatan ? \Carbon\Carbon::parse($item->tgl_kegiatan)->locale('id')->translatedFormat('d F Y') : '-' }}
                    @if ($item->tgl_selesai && $item->tgl_selesai !== $item->tgl_kegiatan)
                        s.d. {{ \Carbon\Carbon::parse($item->tgl_selesai)->locale('id')->translatedFormat('d F Y') }}
                    @endif
                    @if ($item->tempat_kegiatan)
                        <br>Tempat: {{ $item->tempat_kegiatan }}
                    @endif
                </span>
            </li>
        @empty
            <li>Tidak ada kegiatan yang terdaftar.</li>
        @endforelse
    </ul>

    <p class="catatan">
        Dokumen ini dibuat secara otomatis dari sistem pendataan BBGTK Provinsi Sulawesi Selatan pada
        {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y H:i') }} WITA.
    </p>
</body>

</html>
