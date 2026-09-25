<?php

namespace App\Http\Controllers;

use App\Exports\HonorPanitiaExport;
use App\Exports\HonorNarasumberExport;
use App\Exports\HonorPesertaExport;
use App\Models\Honor;
use App\Models\Kegiatan;
use App\Models\PenomoranKegiatan;
use App\Models\PesertaKegiatan;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Http\Request;

class HonorController extends Controller
{
    private $menu = 'honor';

    public function __construct()
    {
        $this->menu = 'honor';
    }

    public function detectGolongan($golongan)
    {
        // Pecah string dengan delimiter '/' untuk mendapatkan prefix
        $parts = explode('/', $golongan);

        // Kembalikan bagian sebelum '/' jika ada
        return $parts[0];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $menu = $this->menu;
        $title = 'honor';
        $kegiatan = Kegiatan::get();
        $honor = Honor::get();
        $datas = [];


        foreach ($honor as $i => $v) {

            $mainGolongan = explode('/', $v->golongan)[0];

            // Tentukan pot berdasarkan golongan utama


            $total = $v->jumlah_honor - $v->potongan;

            // Tambahkan hasil perhitungan ke array $datas
            $datas[] = [
                'nama' => $v->peserta->nama ?? '',
                'jabatan' => $v->peserta->status_keikutpesertaan ?? '',
                'kegiatan' => $v->peserta->kegiatan->nama_kegiatan ?? '',
                'id_kegiatan' => $v->peserta->kegiatan->id ?? '',
                'jp_realisasi' => $v->jp_realisasi,
                'jumlah' => $this->rupiahFormat($v->jumlah),
                'jumlah_honor' => $this->rupiahFormat($v->jumlah_honor),
                'pot' => $this->rupiahFormat($v->potongan),
                'total' => $this->rupiahFormat($total),
                'id' => $v->id, // Jika perlu tambahkan ID atau atribut lain yang relevan
                'golongan' => $v->golongan,
                'jenis_gol' => $v->peserta->jenis_gol ?? $v->jenis_gol,
                'instansi' => $v->peserta->instansi ?? $v->instansi, // Atau atribut lainnya yang ingin ditampilkan
            ];

        }

        return view('pages.admin.honor.index', compact('menu', 'datas', 'title', 'kegiatan'));
    }

    public function rupiahFormat($number)
    {
        return 'Rp ' . number_format($number, 0, ',', '.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $menu = $this->menu;
        $title = 'honor';
        $kegiatan = Kegiatan::orderBy('id', 'DESC')->get();


        $peserta = PesertaKegiatan::orderBy('id', 'DESC')->where('status_keikutpesertaan', 'narasumber')
            ->orWhere('status_keikutpesertaan', 'panitia')
            ->get();
        $status_gol = array(
            'partisipanPanitia' => PesertaKegiatan::with('kegiatan')->where('status_keikutpesertaan', 'panitia')->orderByDesc(
                'id'
            )->get(),

            'partisipanNarasumber' => PesertaKegiatan::with('kegiatan')->where('status_keikutpesertaan', 'narasumber')->orderByDesc('id')->get(),

        );
        return view('pages.admin.honor.create', compact('menu', 'peserta', 'kegiatan'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $r)
    {
        $req = $r->all();

        $getNik = Honor::where('id_peserta', $req['id_peserta'])->first();
        if ($getNik != null) {
            return redirect()->route('honor.create')->with([
                'message' => 'error nik',
                'menu' => 'kegiatan',
            ]);
        }

        $req['jp_realisasi'] = (int) str_replace(".", "", $r->jp_realisasi);
        $req['jumlah'] = (int) str_replace(".", "", $r->jumlah);
        $req['jumlah_honor'] = (int) str_replace(".", "", $r->jumlah_honor);
        $req['potongan'] = (int) str_replace(".", "", $r->potongan);
        $req['jumlah_diterima'] = (int) str_replace(".", "", $r->jumlah_diterima);
        $req['golongan'] = explode('/', $req['golongan'])[0];



        Honor::create($req);

        return redirect()->route('honor.index')->with('message', 'store');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $menu = $this->menu;
        $title = $menu;
        $kegiatan = Kegiatan::orderBy('id', 'DESC')->get();

        $datas = Honor::find($id);
        $peserta = PesertaKegiatan::where('status_keikutpesertaan', 'narasumber')
            ->orWhere('status_keikutpesertaan', 'panitia')
            ->get();

        return view('pages.admin.honor.edit', compact('menu', 'datas', 'peserta', 'title', 'kegiatan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $r)
    {
        $req = $r->all();
        $datas = Honor::find($req['id']);

        $req['jp_realisasi'] = (int) str_replace(".", "", $r->jp_realisasi);
        $req['jumlah'] = (int) str_replace(".", "", $r->jumlah);
        $req['jumlah_honor'] = (int) str_replace(".", "", $r->jumlah_honor);
        $req['potongan'] = (int) str_replace(".", "", $r->potongan);
        $req['jumlah_diterima'] = (int) str_replace(".", "", $r->jumlah_diterima);
        $req['golongan'] = explode('/', $req['golongan'])[0];
        $datas->update($req);

        return redirect()->route('honor.index')->with('message', 'update');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = Honor::find($id);
        $data->delete();
        return response()->json($data);
    }

    public function cetak($jenis)
    {
        // Ambil data honor berdasarkan jenis (panitia/narasumber)
        $honors = Honor::whereHas('peserta', function ($query) use ($jenis) {
            $query->where('status_keikutpesertaan', $jenis);
        })->with('peserta')->get();
        $honors = Honor::whereHas('peserta', function ($query) use ($jenis) {
            $query->where('status_keikutpesertaan', $jenis);
        })->with('peserta')->get();

        $datas = [];

        foreach ($honors as $i => $v) {

            $mainGolongan = explode('/', $v->golongan)[0];


            $total = $v->jumlah_honor - $v->potongan;
            // Tambahkan hasil perhitungan ke array $datas
            $datas[] = [
                'nama' => $v->peserta->nama,
                'jabatan' => $v->peserta->status_keikutpesertaan,
                'jp_realisasi' => $v->jp_realisasi,
                'jumlah' => $this->rupiahFormat($v->jumlah),
                'jumlah_honor' => $this->rupiahFormat($v->jumlah_honor),
                'pot' => $this->rupiahFormat($v->potongan),
                'total' => $this->rupiahFormat($total),
                'id' => $v->id, // Jika perlu tambahkan ID atau atribut lain yang relevan
                'golongan' => $v->golongan, // Atau atribut lainnya yang ingin ditampilkan
            ];
        }
        // Load view untuk PDF
        $pdf = PDF::loadView('pages.admin.honor.cetakHonor', compact('datas', 'jenis'));


        // Buat instance DomPDF
        $pdf->setPaper('A4', 'landscape');

        // Kirimkan PDF ke browser
        return $pdf->stream("daftar_honor_$jenis.pdf");
    }

    public function getPeserta(Request $r)
    {
        $kegiatan = $r->input('kegiatan');
        $peserta = PesertaKegiatan::orderBy('id', 'DESC')
            ->where('id_kegiatan', $kegiatan)
            ->where(function ($query) {
                $query->where('status_keikutpesertaan', 'panitia')
                    ->orWhere('status_keikutpesertaan', 'narasumber')
                    ->orWhere('status_keikutpesertaan', 'peserta');
            })
            ->get();
        return response()->json($peserta);
    }


    public function cetakExcelPanitia($id_kegiatan, $jabatan = 'panitia')
    {
        $honor = Honor::whereHas('peserta', function ($query) use ($id_kegiatan) {
            $query->where('id_kegiatan', $id_kegiatan);
            $query->where('status_keikutpesertaan', 'panitia');
        })->with('peserta.kegiatan')->get();
        if ($honor->isEmpty()) {
            return redirect()->route('honor.index')->with('message', 'error surat');
        }


        $datas = PenomoranKegiatan::orderByDesc('id')->first();
        $id_nomor = $datas->id;
        return Excel::download(new HonorPanitiaExport($id_kegiatan, $jabatan, $id_nomor), 'HonorPanitia.xlsx');
    }

    public function cetakExcelNarasumber($id_kegiatan, $jabatan = 'narasumber')
    {   
        $honor = Honor::whereHas('peserta', function ($query) use ($id_kegiatan) {
            $query->where('id_kegiatan', $id_kegiatan);
            $query->where('status_keikutpesertaan', 'narasumber');
        })->with('peserta.kegiatan')->get();
        if ($honor->isEmpty()) {
            return redirect()->route('honor.index')->with('message', 'error surat');
        }
        
        $datas = PenomoranKegiatan::orderByDesc('id')->first();
        $id_nomor = $datas->id;
        return Excel::download(new HonorNarasumberExport($id_kegiatan, $jabatan, $id_nomor), 'HonorNarasumber.xlsx');
    }

    public function cetakExcelPeserta($id_kegiatan, $jabatan = 'peserta')
    {   
        $honor = Honor::whereHas('peserta', function ($query) use ($id_kegiatan) {
            $query->where('id_kegiatan', $id_kegiatan);
            $query->where('status_keikutpesertaan', 'peserta');
        })->with('peserta.kegiatan')->get();
        if ($honor->isEmpty()) {
            return redirect()->route('honor.index')->with('message', 'error surat');
        }
        
        $datas = PenomoranKegiatan::orderByDesc('id')->first();
        $id_nomor = $datas->id;
        return Excel::download(new HonorPesertaExport($id_kegiatan, $jabatan, $id_nomor), 'HonorPeserta.xlsx');
    }

    public function storeNomor(Request $r)
    {
        PenomoranKegiatan::create($r->all());

        return response()->json([
            'status' => true,
            'data' => PenomoranKegiatan::get(),
        ]);
    }





}
