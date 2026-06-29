<?php

namespace App\Exports;

use App\Models\Sekolah;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SekolahsExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    ShouldAutoSize,
    WithEvents
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Sekolah::query()
            ->where('nama_kepsek', '<>', '-')  // ← Filter: hanya yang ada kepala sekolahnya
            ->whereNotNull('nama_kepsek');

        // Apply filters if provided
        if (!empty($this->filters['provinsi'])) {
            $query->where('provinsi', $this->filters['provinsi']);
        }

        if (!empty($this->filters['status_sekolah'])) {
            $query->where('status_sekolah', $this->filters['status_sekolah']);
        }

        if (!empty($this->filters['akreditasi'])) {
            $query->where('akreditasi', $this->filters['akreditasi']);
        }

        return $query->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'NO',
            'NAMA SEKOLAH',
            'NPSN',
            'JENJANG',
            'STATUS',
            'AKREDITASI',
            'ALAMAT',
            'PROVINSI',
            'KABUPATEN',
            'KECAMATAN',
            'NO. TELEPON',
            'EMAIL',
            'WEBSITE',
            'TAHUN BERDIRI',
            'KOORDINAT',

            // Data Kepala Sekolah
            'NAMA KEPALA SEKOLAH',
            'STATUS ASN',
            'NIP',
            'NO. SK',
            'NO. TELP KEPSEK',
            'EMAIL KEPSEK',

            // Data Guru
            'JUMLAH GURU',
            'GURU PNS',
            'GURU HONORER',
            'TENAGA KEPENDIDIKAN',
            'BIDANG STUDI',

            // Data Siswa
            'JUMLAH SISWA',
            'SISWA LAKI-LAKI',
            'SISWA PEREMPUAN',
            'SISWA PER KELAS',

            // Fasilitas
            'JUMLAH KELAS',
            'LABORATORIUM',
            'PERPUSTAKAAN',
            'RUANG GURU',
            'JUMLAH TOILET',
            'LAPANGAN OLAHRAGA',
            'FASILITAS IT',
            'AKSES INTERNET',

            // Program
            'EKSTRAKURIKULER',
            'PROGRAM UNGGULAN',
            'JAM BELAJAR',
        ];
    }

    /**
     * @var Sekolah $sekolah
     */
    public function map($sekolah): array
    {
        static $no = 0;
        $no++;

        $fasilitasIT = '-';
        if ($sekolah->fasilitas_it) {
            if (is_array($sekolah->fasilitas_it)) {
                // Sudah array (dari casting model)
                $fasilitasIT = implode(', ', $sekolah->fasilitas_it);
            } elseif (is_string($sekolah->fasilitas_it)) {
                // Masih string JSON
                try {
                    $decoded = json_decode($sekolah->fasilitas_it, true);
                    $fasilitasIT = is_array($decoded) ? implode(', ', $decoded) : '-';
                } catch (\Exception $e) {
                    $fasilitasIT = $sekolah->fasilitas_it;
                }
            }
        }

        return [
            $no,
            $sekolah->nama_sekolah,
            $sekolah->npsn_sekolah,
            $sekolah->bp_sekolah,
            $sekolah->status_sekolah,
            $sekolah->akreditasi,
            $sekolah->alamat,
            $sekolah->provinsi,
            $sekolah->kabupaten,
            $sekolah->kecamatan,
            $sekolah->no_telepon,
            $sekolah->email,
            $sekolah->website_url,
            $sekolah->tahun_berdiri,
            $sekolah->koordinat,

            // Data Kepala Sekolah
            $sekolah->nama_kepsek,
            strtoupper($sekolah->asn_opsi),
            $sekolah->nip_kepsek ?? '-',
            $sekolah->no_sk ?? '-',
            $sekolah->no_telp_kepsek,
            $sekolah->email_kepsek ?? '-',

            // Data Guru
            $sekolah->jumlah_guru,
            $sekolah->jumlah_guru_pns,
            $sekolah->jumlah_honorer,
            $sekolah->jumlah_kependidikan,
            $sekolah->bidang_studi ?? '-',

            // Data Siswa
            $sekolah->jumlah_siswa,
            $sekolah->jumlah_siswa_pria,
            $sekolah->jumlah_siswa_perempuan,
            $sekolah->jumlah_siswa_per_kelas ?? '-',

            // Fasilitas
            $sekolah->jumlah_kelas,
            $sekolah->laboratorium,
            $sekolah->perpustakaan,
            $sekolah->ruang_guru,
            $sekolah->jumlah_toilet,
            $sekolah->lapangan_olahraga,
            $fasilitasIT,
            $sekolah->akses_internet,

            // Program
            $sekolah->ekstrakurikuler,
            $sekolah->program_unggulan,
            $sekolah->jam_belajar,
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style untuk header
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 11,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        try {
            return [
                AfterSheet::class => function (AfterSheet $event) {
                    $sheet = $event->sheet->getDelegate();

                    // Get highest row and column
                    $highestRow = $sheet->getHighestRow();
                    $highestColumn = $sheet->getHighestColumn();

                    // Apply borders to all cells
                    $styleArray = [
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                    ];

                    $sheet->getStyle('A1:' . $highestColumn . $highestRow)->applyFromArray($styleArray);

                    // Set row height for header
                    $sheet->getRowDimension(1)->setRowHeight(25);

                    // Auto-size columns to fit content
                    foreach (range('A', $highestColumn) as $column) {
                        $sheet->getColumnDimension($column)->setAutoSize(true);
                    }

                    // Set text wrap for long text columns
                    $longTextColumns = ['G', 'Y', 'Z', 'AD', 'AO', 'AP']; // Alamat, Bidang Studi, Siswa per Kelas, Fasilitas IT, Ekstrakurikuler, Program Unggulan
                    foreach ($longTextColumns as $column) {
                        $sheet->getStyle($column . '2:' . $column . $highestRow)
                            ->getAlignment()
                            ->setWrapText(true);
                    }

                    // Center align for specific columns (numbers and short text)
                    $centerColumns = ['A', 'C', 'D', 'E', 'F', 'Q', 'V', 'W', 'X', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AL'];
                    foreach ($centerColumns as $column) {
                        $sheet->getStyle($column . '2:' . $column . $highestRow)
                            ->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }

                    // Freeze header row
                    $sheet->freezePane('A2');

                    // Set filter for header row
                    $sheet->setAutoFilter('A1:' . $highestColumn . '1');
                },
            ];
        } catch (\Exception $e) {
            return [
                response()->json($e->getMessage())
            ];
        }
    }
}
