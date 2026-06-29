<?php

namespace App\Http\Controllers;

use App\Models\Internal;
use App\Models\KuitansiLoka;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class KuitansiLokaController extends Controller
{
    private $menu;
    public function __construct()
    {
        $this->menu = 'honor';
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $menu = $this->menu;
        $jadwalInternal = Internal::select('id', 'kota', 'jenis', 'deskripsi', 'kegiatan', 'tgl_kegiatan', 'tgl_selesai_kegiatan', 'jam_mulai', 'jam_selesai', 'nama', 'hotel', 'transport_pergi', 'bill_penginapan', 'transport_pulang', 'hari_1', 'hari_2', 'hari_3' , 'hari_4', 'hari_5', 'hari_6', 'hari_7')
            ->whereIn('jenis', ['Pendamping Lokakarya'])
            ->get()
            ->groupBy('kegiatan');


        $datas = $jadwalInternal->map(function ($items, $key) {
            $groupedByJenis = $items->groupBy('jenis');
            $penugasanLoka = $groupedByJenis->get('Pendamping Lokakarya', collect());

            // Bangun array multidimensi untuk setiap pegawai
            $penugasanPegawai = $penugasanLoka->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama' => $item->nama,
                    'hotel' => $item->hotel,
                    'transport_pergi' => $item->transport_pergi,
                    'transport_pulang' => $item->transport_pulang,
                    'bill_penginapan' => $item->bill_penginapan,
                    'hari_1' => $item->hari_1,
                    'hari_2' => $item->hari_2,
                    'hari_3' => $item->hari_3,
                    'hari_4' => $item->hari_4,
                    'hari_5' => $item->hari_5,
                    'hari_6' => $item->hari_6,
                    'hari_7' => $item->hari_7,
                ];
            });
            // dump($penugasanPegawai);


            return [
                'id' => $items->first()->id,
                'kegiatan' => $key,
                'deskripsi' => $items->first()->deskripsi,
                'kota' => $items->first()->kota,
                'tgl_kegiatan' => $items->first()->tgl_kegiatan,
                'tgl_selesai_kegiatan' => $items->first()->tgl_selesai_kegiatan,
                'jam_mulai' => $items->first()->jam_mulai,
                'jam_selesai' => $items->first()->jam_selesai,
                'penugasan_pegawai' => $penugasanPegawai->toArray(),

            ];
        })->values();


        $kuitansiLoka = KuitansiLoka::orderByDesc('id')->get();
        // dd($kuitansiLoka);
        return view('pages.admin.kuitansiLoka.index', compact('menu', 'datas', 'kuitansiLoka'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // $menu = $this->menu;
        // $kegiatan = Kegiatan::orderBy('id', 'DESC')->get();

        // $datas = array(
        //     'peserta' => PesertaKegiatan::orderByDesc('id')->get(),
        //     'kabupaten' => Kabupaten::get(),
        // );
        // // foreach ($datas['peserta'] as $i => $v) {
        // //     dd($v->pegawai->nip);
        // // }
        // // dd($datas['peserta'][0]->pegawai->nip);


        // return view('pages.admin.kuitansi.create', compact('menu', 'datas', 'kegiatan'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $r)
    {

        
        $r = $r->all();

        // dd((int)substr(str_replace('.', '', $r['transport_pergi']), 3));
        $r['transport_pergi']  = (int)  substr(str_replace('.', '', $r['transport_pergi']), 3) ?? 0;
        $r['transport_pulang']  = (int) substr(str_replace('.', '', $r['transport_pulang']), 3) ?? 0;
        $r['bill_penginapan']  = (int) substr(str_replace('.', '', $r['bill_penginapan']), 3) ?? 0;
        $r['hari_1']  = (int) substr(str_replace('.', '', $r['hari_1']), 3) ?? 0;
        $r['hari_2']  = (int) substr(str_replace('.', '', $r['hari_2']), 3) ?? 0;
        $r['hari_3']  = (int) substr(str_replace('.', '', $r['hari_3']), 3) ?? 0;
        $r['hari_4']  = (int) substr(str_replace('.', '', $r['hari_4']), 3) ?? 0;
        $r['hari_5']  = (int) substr(str_replace('.', '', $r['hari_5']), 3) ?? 0;
        $r['hari_6']  = (int) substr(str_replace('.', '', $r['hari_6']), 3) ?? 0;
        $r['hari_7']  = (int) substr(str_replace('.', '', $r['hari_7']), 3) ?? 0;
        $r['total']  = (int) substr(str_replace('.', '', $r['total']), 3) ?? 0;
        $r['internal_id'] = $r['id'];
        // dd($r);


        KuitansiLoka::create($r);


        // Redirect ke halaman index dengan pesan sukses
        return redirect()->route('kuitansiLoka.index')->with('message', 'store');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // $kuitansi = Kuitansi::findOrFail($id);
        // return view('pages.admin.kuitansi.detail', compact('kuitansi'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Temukan data kuitansi berdasarkan id
        // $kuitansi = Kuitansi::findOrFail($id);
        // $menu = $this->menu;
        // $title = 'kuitansi';
        // $kegiatan = Kegiatan::orderBy('id', 'DESC')->get();

        // $datas = array(
        //     'peserta' => PesertaKegiatan::orderByDesc('id')->get(),
        //     'kabupaten' => Kabupaten::get(),
        // );

        // // Memuat data terkait seperti transportasi
        // $transportasis = $kuitansi->transportasis;

        // // Mengembalikan view dengan data kuitansi dan transportasi
        // return view('pages.admin.kuitansi.edit', compact('menu', 'kuitansi', 'transportasis', 'title', 'datas', 'kegiatan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $r, $id)
    {
        // dump($r->all());
        // dd($id);
        $kuitansi = KuitansiLoka::findOrFail($id);

        // Perbarui data kuitansi
        $kuitansi->no_surat_tugas = $r->input('no_surat_tugas');
        $kuitansi->tgl_surat_tugas = $r->input('tgl_surat_tugas');
        $kuitansi->kode_anggaran = $r->input('kode_anggaran');
        $kuitansi->tahun_anggaran = $r->input('tahun_anggaran');
        $kuitansi->no_bukti = $r->input('no_bukti');
        // Set field lain jika diperlukan
        $kuitansi->save();
    
        // Redirect dengan pesan sukses
        return redirect()->back()->with('message', 'update');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = KuitansiLoka::find($id);
        $data->delete();
        return response()->json($data);
    }

    public function cetak(string $id)
    {

        $data = KuitansiLoka::find($id);

        // dd($data);


        $pdf = Pdf::loadView('pages.admin.kuitansiLoka.cetak.cetak', compact('data'));

        // Set properties PDF
        $pdf->setPaper('a4', 'potrait'); // Set kertas ke mode landscape


        return $pdf->stream('data_kuitansi.pdf');
    }

    public function cetakAll(Request $request)
    {
        $kegiatanId = $request->query('kegiatan_id');
        $rowIds = $request->query('rows');

        if (!$rowIds) {
            return response()->json(['error' => 'No rows specified.'], 400);
        }

        $rowIdsArray = array_map('intval', explode(',', $rowIds)); // Convert IDs to integers

        $query = KuitansiLoka::whereIn('id', $rowIdsArray);
        // dd($rowIds);
        if ($kegiatanId) {
            $query->whereHas('internalMany', function ($query) use ($kegiatanId) {
                $query->where('id', $kegiatanId);
            });
        }
        $datas = $query->get();
        
        if ($datas->isEmpty()) {
            // dd($datas);
            return response()->json(['error' => 'No data found for the given IDs.'], 404);
        }
        
        // dd($datas);
        try {
            $pdf = Pdf::loadView('pages.admin.kuitansiLoka.cetak.cetakAll.cetakAll', compact('datas'));
            $pdf->setPaper('a4', 'portrait');
            return $pdf->stream('data_kuitansi.pdf');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to generate PDF.'], 500);
        }
    }

    public function cetakRillAll(Request $request)
    {
        $kegiatanId = $request->query('kegiatan_id');
        $rowIds = $request->query('rows');

        if (!$rowIds) {
            return response()->json(['error' => 'No rows specified.'], 400);
        }

        $rowIdsArray = array_map('intval', explode(',', $rowIds)); // Convert IDs to integers

        $query = KuitansiLoka::whereIn('id', $rowIdsArray);

        if ($kegiatanId) {
            $query->whereHas('internalMany', function ($query) use ($kegiatanId) {
                $query->where('id', $kegiatanId);
            });
        }
        
        $datas = $query->get();
        // dd($datas);
        if ($datas->isEmpty()) {
            return response()->json(['error' => 'No data found for the given IDs.'], 404);
        }

        try {
            $pdf = Pdf::loadView('pages.admin.kuitansiLoka.cetak.cetakAll.cetakRillAll', compact('datas'));
            $pdf->setPaper('a4', 'portrait');
            return $pdf->stream('data_pengeluaran_rill.pdf');
        } catch (\Illuminate\Contracts\Filesystem\FileNotFoundException $e) {
            return response()->json(['error' => 'View file not found.'], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to generate PDF. ' . $e->getMessage()], 500);
        }
    }


    public function cetakPJmutlakAll(Request $request)
    {
        $kegiatanId = $request->query('kegiatan_id');
        $rowIds = $request->query('rows');

        if (!$rowIds) {
            return response()->json(['error' => 'No rows specified.'], 400);
        }

        $rowIdsArray = array_map('intval', explode(',', $rowIds)); // Convert IDs to integers

        $query = KuitansiLoka::whereIn('id', $rowIdsArray);

        if ($kegiatanId) {
            $query->whereHas('internalMany', function ($query) use ($kegiatanId) {
                $query->where('id', $kegiatanId);
            });
        }

        $datas = $query->get();

        if ($datas->isEmpty()) {
            return response()->json(['error' => 'No data found for the given IDs.'], 404);
        }

        try {
            $pdf = Pdf::loadView('pages.admin.kuitansiLoka.cetak.cetakAll.cetakPjMutlakAll', compact('datas'));
            $pdf->setPaper('a4', 'portrait');
            return $pdf->stream('data_PJ_mutlak.pdf');
        } catch (\Illuminate\Contracts\Filesystem\FileNotFoundException $e) {
            return response()->json(['error' => 'View file not found.'], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to generate PDF. ' . $e->getMessage()], 500);
        }
    }

    public function cetakAmplopAll(Request $request)
    {
        $kegiatanId = $request->query('kegiatan_id');
        $rowIds = $request->query('rows');

        if (!$rowIds) {
            return response()->json(['error' => 'No rows specified.'], 400);
        }

        $rowIdsArray = array_map('intval', explode(',', $rowIds)); // Convert IDs to integers

        $query = KuitansiLoka::whereIn('id', $rowIdsArray);

        if ($kegiatanId) {
            $query->whereHas('internalMany', function ($query) use ($kegiatanId) {
                $query->where('id', $kegiatanId);
            });
        }

        $datas = $query->get();

        if ($datas->isEmpty()) {
            return response()->json(['error' => 'No data found for the given IDs.'], 404);
        }

        try {
            $pdf = Pdf::loadView('pages.admin.kuitansiLoka.cetak.cetakAll.cetakAmplopAll', compact('datas'));
            $pdf->setPaper(array(0, 0, 825, 465));
            return $pdf->stream('data_amplop.pdf');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to generate PDF.'], 500);
        }
    }




    public function cetakRill(string $id)
    {

        $data = KuitansiLoka::find($id);

        // dd($data);


        $pdf = Pdf::loadView('pages.admin.kuitansiLoka.cetak.cetakRill', compact('data'));

        // Set properties PDF
        $pdf->setPaper('a4', 'potrait'); // Set kertas ke mode landscape


        return $pdf->stream('data_pengeluaran_rill.pdf');
    }

    public function cetakPJmutlak(string $id)
    {

        $data = KuitansiLoka::find($id);

        // dd($data);


        $pdf = Pdf::loadView('pages.admin.kuitansiLoka.cetak.cetakPjMutlak', compact('data'));

        // Set properties PDF
        $pdf->setPaper('a4', 'potrait'); // Set kertas ke mode landscape


        return $pdf->stream('data_PJ_mutlak.pdf');
    }

    public function cetakAmplop(string $id)
    {

        $data = KuitansiLoka::find($id);

        $pdf = Pdf::loadView('pages.admin.kuitansiLoka.cetak.cetakAmplop', compact('data'));

        // Set properties PDF
        $pdf->setPaper(array(0, 0, 825, 465)); // Set kertas ke mode landscape


        return $pdf->stream('data_amplop.pdf');
    }

    public function cetakPermintaan()
    {

        $data = KuitansiLoka::get();

        $pdf = Pdf::loadView('pages.admin.kuitansiLoka.cetak.cetakPermintaan', compact('data'));

        // Set properties PDF
        $pdf->setPaper('a4', 'landscape'); // Set kertas ke mode landscape

        return $pdf->stream('data_Permintaan.pdf');
    }

    public function cetakLampiran()
    {

        $data = KuitansiLoka::get();

        $pdf = Pdf::loadView('pages.admin.kuitansiLoka.cetak.cetakLampiran', compact('data'));

        // Set properties PDF
        // $pdf->setPaper('a4', 'landscape'); // Set kertas ke mode landscape
        $pdf->setPaper(array(0, 0, 2025, 865)); // Set kertas ke mode landscape


        return $pdf->stream('data_Lampiran.pdf');
    }


    // public function getPeserta(Request $r)
    // {
    //     // dd($r->all());
    //     $kegiatan = $r->input('kegiatan');
    //     $peserta = PesertaKegiatan::orderBy('id', 'DESC')
    //         ->where('id_kegiatan', $kegiatan)
    //         ->where(function ($query) {
    //             $query->where('status_keikutpesertaan', 'panitia')
    //                 ->orWhere('status_keikutpesertaan', 'narasumber');
    //         })
    //         ->get();
    //     // dd($peserta);
    //     return response()->json($peserta);
    // }
}
