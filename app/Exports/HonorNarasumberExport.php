<?php

namespace App\Exports;

use App\Models\Honor;
use App\Models\PenomoranKegiatan;
use App\Models\PesertaKegiatan;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class HonorNarasumberExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{
    use Exportable;

    protected $id_kegiatan, $id_peserta;
    protected $jabatan;
    protected $namaKegiatan, $no_surat, $tgl_surat, $kode_anggaran;
    protected $id_nomor;

    public function __construct($id_kegiatan, $jabatan, $id_nomor)
    {
        $this->id_kegiatan = $id_kegiatan;
        $this->jabatan = $jabatan;
        $this->id_nomor = $id_nomor;
    }


    protected $headers = [
        'No',
        'Nama Lengkap',
        'Gol',
        'Realisasi JP',
        'Jumlah',
        'Jumlah Honor',
        'Potongan',
        'Jumlah Diterima',
    ];



    /**
     * @return Collection
     */
    public function collection()
    {

        $nomor = PenomoranKegiatan::find($this->id_nomor);
        // dd($this->id_kegiatan);
        $this->no_surat = $nomor->no_surat;

        setlocale(LC_TIME, 'id_ID.UTF-8');
        Carbon::setLocale('id');
        $this->tgl_surat = Carbon::parse($nomor->tgl_surat)->translatedFormat('d F Y');

        $this->kode_anggaran = $nomor->kode_anggaran;
        // dd($nomor);

        // Fetch data from PesertaKegiatan and Honor models
        $pesertaKegiatan = PesertaKegiatan::where('id_kegiatan', $this->id_kegiatan)
            ->where('status_keikutpesertaan', 'narasumber')
            ->get();

        $this->namaKegiatan = $pesertaKegiatan[0]->kegiatan->nama_kegiatan ?? '';
        $id_kegiatan = $this->id_kegiatan;


        $honor = Honor::whereHas('peserta', function ($query) use ($id_kegiatan) {
            $query->where('id_kegiatan', $id_kegiatan);
            $query->where('status_keikutpesertaan', 'narasumber');
        })->with('peserta.kegiatan')->get();

        // Check if either collection is empty
        if ($pesertaKegiatan->isEmpty() || $honor->isEmpty()) {
            return new Collection([]);
        }

        $datas = [];
        $totalJumlah = 0;
        $totalHonor = 0;
        $totalPot = 0;
        $totalJP = 0;
        $totalJumlahDiterima = 0;

        // Merge data from PesertaKegiatan and Honor
        foreach ($honor as $index => $v) {
            $jumlahHonor = isset($v->jumlah_honor) ? $v->jumlah_honor : '0';
            $potongan = isset($v->potongan) ? $v->potongan : '0';
            $totalTerima = $jumlahHonor - $potongan;

            $datas[] = [
                'No' => $index + 1,
                'Nama Lengkap' => $v->peserta->nama,
                'Gol' =>  $v->golongan,
                'Realisasi JP' => isset($v->jp_realisasi) ? $v->jp_realisasi : '0',
                'Jumlah' => isset($v->jumlah) ? $v->jumlah : '0',
                'Jumlah Honor' => $jumlahHonor,
                'Potongan' => $potongan == 0 ? '0' : $potongan,
                'Jumlah Diterima' => $totalTerima, // Calculation for amount received
            ];
            $totalJumlah += isset($v->jumlah) ? $v->jumlah : 0;
            $totalJP += $v->jp_realisasi;
            $totalHonor += $jumlahHonor;
            $totalPot += $potongan;
            $totalJumlahDiterima += $totalTerima;
            // dump($datas);
        }
        $datas[] = [
            'No' => '',
            'Nama Lengkap' => '',
            'Gol' => '',
            'Realisasi JP' => $totalJP,
            'Jumlah' => $totalJumlah,
            'Jumlah Honor' => $totalHonor,
            'Potongan' => $totalPot,
            'Jumlah Diterima' => $totalJumlahDiterima,
        ];
        // dd($datas);

        // dd(true);

        return new Collection($datas);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return $this->headers;
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Access the PhpSpreadsheet object
                $sheet = $event->sheet->getDelegate();

                $customHeader = [
                    'HONORARIUM DALAM RANGKA ' . strtoupper($this->namaKegiatan)  . '  WILAYAH PROV. SULAWESI SELATAN',
                    'SESUAI SK KEPALA BALAI BESAR GURU PENGGERAK , NO: ' . $this->no_surat  . ', TANGGAL ' . $this->tgl_surat . ' DENGAN RINCIAN SBB:',
                    'Kode Anggaran : ' . $this->kode_anggaran,
                ];

                // Insert custom headers at the top
                $sheet->insertNewRowBefore(1, count($customHeader) + 2);
                foreach ($customHeader as $index => $header) {
                    $sheet->mergeCells('A' . ($index + 1) . ':H' . ($index + 1));
                    $sheet->setCellValue('A' . ($index + 1), $header);
                    $sheet->getStyle('A' . ($index + 1))->getFont()->setBold(true);
                    $sheet->getStyle('A' . ($index + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
                $extraRowStart = count($customHeader) + 1;
                $extraRowEnd = $extraRowStart + 1;
                for ($i = $extraRowStart; $i <= $extraRowEnd; $i++) {
                    $sheet->mergeCells('A' . $i . ':H' . $i);
                }

                // Insert and merge the calculation headers
                $calcHeaderRow = $extraRowEnd + 1;
                $sheet->insertNewRowBefore($calcHeaderRow + 1, 1);

                $sheet->mergeCells('A' . $calcHeaderRow . ':A' . ($calcHeaderRow + 1));
                $sheet->setCellValue('A' . $calcHeaderRow, 'No');
                $sheet->getStyle('A' . $calcHeaderRow)->getFont()->setBold(true);

                $sheet->mergeCells('B' . $calcHeaderRow . ':B' . ($calcHeaderRow + 1));
                $sheet->setCellValue('B' . $calcHeaderRow, 'Nama Fasilitator');
                $sheet->getStyle('B' . $calcHeaderRow)->getFont()->setBold(true);

                $sheet->mergeCells('C' . $calcHeaderRow . ':C' . ($calcHeaderRow + 1));
                $sheet->setCellValue('C' . $calcHeaderRow, 'Gol');
                $sheet->getStyle('C' . $calcHeaderRow)->getFont()->setBold(true);

                $sheet->mergeCells('D' . $calcHeaderRow . ':F' . $calcHeaderRow);
                $sheet->setCellValue('D' . $calcHeaderRow, 'Perhitungan');
                $sheet->getStyle('D' . $calcHeaderRow)->getFont()->setBold(true);

                $sheet->mergeCells('G' . $calcHeaderRow . ':G' . ($calcHeaderRow + 1));
                $sheet->setCellValue('G' . $calcHeaderRow, 'Potongan');
                $sheet->getStyle('G' . $calcHeaderRow)->getFont()->setBold(true);

                $sheet->mergeCells('H' . $calcHeaderRow . ':H' . ($calcHeaderRow + 1));
                $sheet->setCellValue('H' . $calcHeaderRow, 'Jumlah Diterima');
                $sheet->getStyle('H' . $calcHeaderRow)->getFont()->setBold(true);

                $subHeaderRow = $calcHeaderRow + 1;
                $sheet->setCellValue('D' . $subHeaderRow, 'JP Realisasi');
                $sheet->setCellValue('E' . $subHeaderRow, 'Jumlah');
                $sheet->setCellValue('F' . $subHeaderRow, 'Jumlah Honor');

                $sheet->getStyle('D' . $subHeaderRow)->getFont()->setBold(true);
                $sheet->getStyle('E' . $subHeaderRow)->getFont()->setBold(true);
                $sheet->getStyle('F' . $subHeaderRow)->getFont()->setBold(true);

                // Apply borders to the custom headers
                $headerRange = 'A1:H' . count($customHeader);
                $sheet->getStyle($headerRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);

                // Apply borders to the main headers
                $sheet->getStyle('A' . (count($customHeader) + 1) . ':H' . (count($customHeader) + 1))->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);

                // Center align all cells
                $totalRows = $sheet->getHighestRow();
                $totalColumns = $sheet->getHighestColumn();


                // $sheet->insertNewRowBefore($totalRows, 1);
                $sheet->setCellValue('A' . $totalRows, 'TOTAL');
                $sheet->mergeCells('A' . $totalRows . ':' . 'C' . $totalRows);
                $sheet->getStyle('A' . $totalRows . ':' . $totalColumns . $totalRows)->getFont()->setBold(true);



                $sheet->getStyle('A1:' . $totalColumns . $totalRows)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Format Jumlah, Jumlah Honor, Potongan, Jumlah Diterima as Rupiah
                $rupiahColumns = ['E', 'F', 'G', 'H']; // Columns for Jumlah, Jumlah Honor, Potongan, Jumlah Diterima
                foreach ($rupiahColumns as $column) {
                    $sheet->getStyle($column . '2:' . $column . $totalRows)
                        ->getNumberFormat()
                        ->setFormatCode('#,##0');
                }

                // Apply borders to all cells
                $allRange = 'A1:' . $totalColumns . $totalRows;
                $sheet->getStyle($allRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);
            },
        ];
    }
}
