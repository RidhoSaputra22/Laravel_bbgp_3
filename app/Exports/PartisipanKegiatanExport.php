<?php

namespace App\Exports;

use App\Models\PesertaKegiatan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class PartisipanKegiatanExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents, WithColumnFormatting
{
    /**
     * @return \Illuminate\Support\Collection
     */

    use Exportable;

    protected $id_kegiatan, $id_peserta;
    protected $jabatan;
    protected $namaKegiatan, $no_surat, $tgl_kegiatan, $kode_anggaran;
    protected $id_nomor;

    public function __construct($id_kegiatan, $tgl_kegiatan, $nama_kegiatan)
    {
        $this->id_kegiatan = $id_kegiatan;
        $this->tgl_kegiatan = $tgl_kegiatan;
        $this->namaKegiatan = $nama_kegiatan;
    }

    protected $headers = [
        'No',
        'NIP',
        'Nama Lengkap',
        'Pangkat/Golongan',
        'Jabatan',
        'Instansi',
        'Asal Kabupaten/Kota',
        'Surat Tugas',
    ];

    public function collection()
    {

        $data = PesertaKegiatan::with(['eksternal', 'pegawai'])
            ->where('id_kegiatan', $this->id_kegiatan)
            ->orderByRaw("FIELD(kabupaten, 'Kabupaten Kepulauan Selayar', 'Kota Parepare', 'Kabupaten Barru', 'Kabupaten Jeneponto', 'Kabupaten Takalar', 'Kabupaten Sidrap', 'Kabupaten Pinrang', 'Kabupaten Luwu Timur', 'Kabupaten Toraja Utara', 'Kabupaten Wajo', 'Kabupaten Pangkep', 'Kabupaten Soppeng', 'Kabupaten Bulukumba', 'Kabupaten Gowa', 'Kabupaten Maros', 'Kabupaten Tana Toraja', 'Kota Palopo', 'Kabupaten Bone', 'Kota Makassar', 'Kabupaten Enrekang', 'Kabupaten Sinjai', 'Kabupaten Luwu', 'Kabupaten Luwu Utara', 'Kabupaten Bantaeng')")
            ->get();

        $datas = [];
        // dd($data[21]->eksternal);


        foreach ($data as $index => $v) {
            // dd($v->eksternal);

            $jabatan = $v->eksternal && $v->eksternal->jenis_jabatan
                ? $v->eksternal->jenis_jabatan
                : ($v->pegawai && $v->pegawai->jabatan ? $v->pegawai->jabatan : '-');

            $datas[] = [
                'No' => $index + 1,
                'NIP' =>  $v->nip == "0" || $v->nip == '' ? '-' : $v->nip,
                'Nama Lengkap' => $v->nama,
                'Pangkat/Golongan' => $v->jenis_gol . ' / ' . $v->golongan,
                'Jabatan' => $jabatan,
                'Instansi' => $v->instansi,
                'Asal Kabupaten/Kota' => $v->kabupaten,
                'Surat Tugas' => $v->no_surat_tugas . ' - ' . $v->tgl_surat_tugas,
            ];

            // dump($datas);
        }

        return new Collection($datas);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return $this->headers;
    }

    public function columnFormats(): array
    {
        return [
            // 'B' => NumberFormat::FORMAT_TEXT
        ];
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
                    'KEGIATAN ' . strtoupper($this->namaKegiatan),
                    'TANGGAL ' . $this->tgl_kegiatan,
                ];

                // Insert custom headers at the top
                $sheet->insertNewRowBefore(1, count($customHeader) + 2);
                foreach ($customHeader as $index => $header) {
                    $sheet->mergeCells('A' . ($index + 1) . ':H' . ($index + 1));
                    $sheet->setCellValue('A' . ($index + 1), $header);
                    $sheet->getStyle('A' . ($index + 1))->getFont()->setBold(true);
                    $sheet->getStyle('A' . ($index + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // Insert and merge the calculation headers



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

                for ($row = count($customHeader) + 2; $row <= $totalRows; $row++) {
                    $cell = $sheet->getCell('B' . $row);
                    $cell->setValueExplicit($cell->getValue(), DataType::TYPE_STRING);
                }


                $sheet->getStyle('A1:' . $totalColumns . $totalRows)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

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
