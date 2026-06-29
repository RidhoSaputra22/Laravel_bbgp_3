<?php

namespace App\Exports;

use App\Models\Kuitansi;
use App\Models\PenomoranKegiatan;
use App\Models\PesertaKegiatan;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;


class KuitansiExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{

    private $date_today;
    private $id_kegiatan, $namaKegiatan;
    private $id_nomor, $no_surat, $kode_anggaran, $tgl_surat;

    public function __construct($id_kegiatan, $id_nomor)
    {
        Carbon::setLocale('id');
        setlocale(LC_TIME, 'id_ID.UTF-8');
        $this->date_today = Carbon::now()->translatedFormat('d F Y');
        $this->id_kegiatan = $id_kegiatan;
        $this->id_nomor = $id_nomor;
    }


    public function collection()
    {

        $nomor = PenomoranKegiatan::find($this->id_nomor);
        // dd($this->id_kegiatan);
        $this->no_surat = $nomor->no_surat;
        $this->kode_anggaran = $nomor->kode_anggaran;
        $this->tgl_surat = Carbon::parse($nomor->tgl_surat)->translatedFormat('d F Y');

        $pesertaKegiatan = PesertaKegiatan::where('id_kegiatan', $this->id_kegiatan)
            ->get();
        $this->namaKegiatan = $pesertaKegiatan[0]->kegiatan->nama_kegiatan;
        $id_kegiatan = $this->id_kegiatan;

        $kuitansi = Kuitansi::whereHas('peserta', function ($query) use ($id_kegiatan) {
            $query->where('id_kegiatan', $id_kegiatan);
        })->with('peserta.kegiatan')->get();

        // dump($nomor);
        // dump($pesertaKegiatan);
        // dump($kuitansi);
        // dd(true);
        $datas = [];

        $totalTransport = 0;
        $totalUangHarian = 0;
        $totalJumlahDiterima = 0;

        foreach ($kuitansi as $index => $v) {
            // Fetch kuitansi data based on peserta kegiatan
            // $kuitansi = Kuitansi::where('pegawai_id', $v->id)->first();

            // Prepare data for export
            $transport = $v ? $v->total_transport : 0;
            $uangHarian = $v ? $v->uang_harian : 0;
            $jumlahDiterima = $v ? $v->total_terima : 0;

            $datas[] = [
                'No' => $index + 1,
                'Nama Lengkap' => $v->peserta->nama,
                'Instansi' => $v->peserta->instansi,
                'Asal - Tujuan' => $v->kabupaten->name . ' - ' . ($v ? $v->lokasi_tujuan : ''),
                'Transport' => $transport,
                'Uang Harian' => $uangHarian,
                'Jumlah Diterima' => $jumlahDiterima,
            ];

            // Add to totals
            $totalTransport += $transport;
            $totalUangHarian += $uangHarian;
            $totalJumlahDiterima += $jumlahDiterima;
        }

        // Add totals row
        $datas[] = [

            'No' => '',
            'Nama Lengkap' => '',
            'Instansi' => '',
            'Asal - Tujuan' => '',
            'Transport' => $totalTransport,
            'Uang Harian' => $totalUangHarian,
            'Jumlah Diterima' => $totalJumlahDiterima,
        ];

        return new Collection($datas);
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Lengkap',
            'Instansi',
            'Asal - Tujuan',
            'Transport',
            'Uang Harian',
            'Jumlah Diterima',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Insert custom headers
                $sheet->insertNewRowBefore(1, 4);
                $sheet->mergeCells('A1:G1');
                $sheet->setCellValue('A1', 'DAFTAR PEMBAYARAN');
                $sheet->mergeCells('A2:G2');
                $sheet->setCellValue('A2', 'Kegiatan ' . $this->namaKegiatan);
                $sheet->mergeCells('A3:G3');
                $sheet->setCellValue('A3', 'SESUAI SURAT TUGAS '. $this->no_surat .' tanggal ' . $this->tgl_surat);
                $sheet->mergeCells('A4:G4');
                $sheet->setCellValue('A4', 'Kode Anggaran : ' . $this->kode_anggaran);

                // Apply bold and center alignment to custom headers
                $sheet->getStyle('A1:G4')->getFont()->setBold(true);
                $sheet->getStyle('A1:G4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Apply font size to header row
                $sheet->getStyle('A5:G5')->getFont()->setSize(14);

                // Add border to header row
                $sheet->getStyle('A5:G5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->insertNewRowBefore(5, 2);
                $sheet->mergeCells('A' . 5 . ':G' . 5);
                $sheet->mergeCells('A' . 6 . ':G' . 6);


                // Calculate total rows and columns
                $totalRows = $sheet->getHighestRow();
                $totalColumns = $sheet->getHighestColumn();

                // $sheet->insertNewRowBefore($totalRows, 1);
                $sheet->setCellValue('A' . $totalRows, 'TOTAL');
                $sheet->mergeCells('A' . $totalRows . ':' . 'D' . $totalRows);
                $sheet->getStyle('A' . $totalRows . ':' . $totalColumns . $totalRows)->getFont()->setBold(true);

                // Apply border to all cells in the sheet
                $sheet->getStyle('A1:' . $totalColumns . $totalRows)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                // Center align all cells in the table
                $sheet->getStyle('A5:' . $totalColumns . $totalRows)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Format columns for Rupiah (Transport, Uang Harian, Jumlah Diterima)
                $rupiahColumns = ['E', 'F', 'G'];
                foreach ($rupiahColumns as $column) {
                    $sheet->getStyle($column . '6:' . $column . $totalRows)
                        ->getNumberFormat()
                        ->setFormatCode('#,##0');
                }

                // Menyisipkan 6 baris baru sebelum baris tanda tangan
                $sheet->insertNewRowBefore($totalRows + 1, 6);
                $sheet->setCellValue('B' . ($totalRows), 'TOTAL');
                $sheet->mergeCells('B39:D39');
                // Menempatkan teks tanda tangan dan menggabungkan sel

                $sheet->setCellValue('F' . ($totalRows + 3), 'Makassar, ' . $this->date_today);
                $sheet->mergeCells('F' . ($totalRows + 3) . ':G' . ($totalRows + 3));

                $sheet->setCellValue('F' . ($totalRows + 4), '        ');
                $sheet->setCellValue('F' . ($totalRows + 5), '        ');
                $sheet->setCellValue('F' . ($totalRows + 6), '        ');
                $sheet->setCellValue('F' . ($totalRows + 7), '        ');
                $sheet->setCellValue('F' . ($totalRows + 8), '        ');

                $sheet->setCellValue('F' . ($totalRows + 9), 'Idhil Nur Mansyur, SE');
                $sheet->mergeCells('F' . ($totalRows + 9) . ':G' . ($totalRows + 9));

                $sheet->setCellValue('F' . ($totalRows + 10), 'NIP.19790522 200312 1 001');
                $sheet->mergeCells('F' . ($totalRows + 10) . ':G' . ($totalRows + 10));

                // Mengatur teks tanda tangan menjadi tebal dan rata tengah
                $sheet->getStyle('F' . ($totalRows + 2) . ':G' . ($totalRows + 10))->getFont()->setBold(true);
                $sheet->getStyle('F' . ($totalRows + 10) . ':G' . ($totalRows + 10))->getFont()->setUnderline(true);
                $sheet->getStyle('F' . ($totalRows + 2) . ':G' . ($totalRows + 10))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Menghapus border untuk bagian tanda tangan
                $sheet->getStyle('A' . ($totalRows + 1) . ':G' . ($totalRows + 7))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_NONE);
            },
        ];
    }
}
