<?php

namespace App\Http\Controllers;

use App\Exports\PartisipanKegiatanExport;
use App\Models\GolonganP3k;
use App\Models\Guru;
use App\Models\Internal;
use App\Models\JabatanPenugasanGolongan;
use App\Models\Kabupaten;
use App\Models\Kegiatan;
use App\Models\Pegawai;
use App\Models\Pendidikan;
use App\Models\PesertaKegiatan;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PesertaKegiatanController extends Controller
{
    private $menu;
    public function __construct()
    {
        $this->menu = 'peserta';
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $menu = $this->menu;

        $datas = PesertaKegiatan::orderBy('id', 'DESC')->get();
        $kegiatan = Kegiatan::orderBy('id', 'DESC')->get();
        $kabupaten = Kabupaten::get();
        return view('pages.admin.peserta.index', compact('datas', 'menu', 'kegiatan', 'kabupaten'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kegiatan = Kegiatan::get();
        $menu = $this->menu;
        $status = array(
            'kabupaten' => Kabupaten::get(),
            'golongan' => JabatanPenugasanGolongan::get(),
            'golongan_p3k' => GolonganP3k::get(),
        );

        return view('pages.admin.peserta.create', compact('kegiatan', 'menu', 'status'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $r = $request->all();

        // dd($r);
        $getNik = PesertaKegiatan::where('no_ktp', $r['no_ktp'])->first();
        // dd($getNik);
        if ($getNik == null) {

            if ($r['kabupaten'] == 'lainnya') {

                if ($r['asal_kabupaten'] == null) {
                    $r['asal_kabupaten'] = '-';
                    $r['kabupaten'] = $r['asal_kabupaten'];
                } else {
                    $r['kabupaten'] = $r['asal_kabupaten'];
                }
            }
            // dd(false);

            if ($r['jenis_gol'] == 'PNS' && $r['golongan_pns'] != null) {
                $r['golongan'] = $r['golongan_pns'];
                $r['diluar_gol'] = null;
                $r['golongan_p3k'] = null;
            } else if ($r['jenis_gol'] == 'P3K' && $r['golongan_p3k'] != null) {
                $r['golongan'] = $r['golongan_p3k'];
                $r['diluar_gol'] = null;
                $r['golongan_pns'] = null;
            } else if ($r['jenis_gol'] == 'Tidak ada golongan' && $r['diluar_gol'] != null) {
                $r['golongan'] = $r['diluar_gol'];
                $r['golongan_p3k'] = null;
                $r['golongan_pns'] = null;
            } else {
                // Handle case where none of the golongan values are set
                $status = [
                    'kegiatanById' => Kegiatan::find($r['id_kegiatan']),
                    'kabupaten' => Kabupaten::all(),
                    'golongan' => JabatanPenugasanGolongan::all(),
                    'golongan_p3k' => GolonganP3k::get(),
                ];
                // dd($status['kegiatanById']->id);


                return redirect()->route('peserta.create', [
                    'kegiatan_id' => $status['kegiatanById']->id,
                ])->with([
                    'status' => $status,
                    'message' => 'error golongan',
                    'menu' => 'kegiatan',
                ]);
            }

            // dd($r);
            PesertaKegiatan::create($r);

            return redirect()->route('peserta.index')->with('message', 'store');
        } else {
            // dd(true);
            return redirect()->route('peserta.create')->with([
                'message' => 'error nik',
                'menu' => 'kegiatan',
            ]);
        }
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
        $datas = PesertaKegiatan::find($id);
        $kegiatan = Kegiatan::get();

        $getById = Guru::where('no_ktp', $datas->no_ktp)->first();
        if ($getById == null) {
            $getById = Pegawai::where('no_ktp', $datas->no_ktp)->first();
        }
        // dd($getById);
        $menu = $this->menu;
        $status = array(
            'kabupaten' => Kabupaten::get(),
            'golongan' => JabatanPenugasanGolongan::get(),
            'golongan_p3k' => GolonganP3k::get(),
            's_gelar' => Pendidikan::get(),
        );

        // dd($datas);
        return view('pages.admin.peserta.edit', compact('datas', 'menu', 'status', 'kegiatan', 'getById'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $r)
    {
        $datas = PesertaKegiatan::find($r->id);


        $getDataPeserta = Guru::where('no_ktp', $r->no_ktp)->first();
        if ($getDataPeserta == null) {
            $getDataPeserta = Pegawai::where('no_ktp', $r->no_ktp)->first();
        }
        // dd($getDataPeserta);
        $getDataPeserta->agama = $r->agama;
        $getDataPeserta->tgl_lahir = $r->tgl_lahir;
        $getDataPeserta->tempat_lahir = $r->tempat_lahir;
        $getDataPeserta->pendidikan = $r->pendidikan;
        $getDataPeserta->alamat_rumah = $r->alamat_rumah;
        $getDataPeserta->kabupaten_rumah = $r->kabupaten_rumah;
        $getDataPeserta->npwp = $r->npwp;

        // Handle gender mapping
        if (isset($getDataPeserta->gender)) {
            $getDataPeserta->gender = $r->jkl ?? $r->gender;
        } else if (isset($getDataPeserta->jkl)) {
            $getDataPeserta->jkl = $r->jkl ?? $r->gender;
        }

        $getDataPeserta->save();
        // $getNik = PesertaKegiatan::where('no_ktp', $r['no_ktp'])->first();
        // if ($getNik != null) {
        //     return redirect()->route('peserta.create')->with([
        //         'message' => 'error nik',
        //         'menu' => 'kegiatan',
        //     ]);
        // }

        if ($r['kabupaten'] == 'lainnya') {

            if ($r['asal_kabupaten'] == null) {
                $r['asal_kabupaten'] = '-';
                $r['kabupaten'] = $r['asal_kabupaten'];
            } else {
                $r['kabupaten'] = $r['asal_kabupaten'];
            }
        }

        if ($r['jenis_gol'] == 'PNS' && $r['golongan_pns'] != null) {
            $r['golongan'] = $r['golongan_pns'];
            $r['diluar_gol'] = null;
            $r['golongan_p3k'] = null;
        } else if ($r['jenis_gol'] == 'P3K' && $r['golongan_p3k'] != null) {
            $r['golongan'] = $r['golongan_p3k'];
            $r['diluar_gol'] = null;
            $r['golongan_pns'] = null;
        } else if ($r['jenis_gol'] == 'Tidak ada golongan' && $r['diluar_gol'] != null) {
            $r['golongan'] = $r['diluar_gol'];
            $r['golongan_p3k'] = null;
            $r['golongan_pns'] = null;
        } else {
            // Handle case where none of the golongan values are set
            $status = [
                'kegiatanById' => Kegiatan::find($r['id_kegiatan']),
                'kabupaten' => Kabupaten::all(),
                'golongan' => JabatanPenugasanGolongan::all(),
                'golongan_p3k' => GolonganP3k::get(),
            ];

            return redirect()->route('peserta.create', [
                'kegiatan_id' => $status['kegiatanById']->id,
            ])->with([
                'status' => $status,
                'message' => 'error golongan',
                'menu' => 'kegiatan',
            ]);
        }
        // dd($r->all);
        $datas->update($r->all());
        return redirect()->route('peserta.index')->with('message', 'update');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = PesertaKegiatan::find($id);
        $data->delete();
        return response()->json($data);
    }

    public function cetak($id)
    {

        $peserta = PesertaKegiatan::find($id);
        // dd($peserta->no_ktp);

        $namaKegiatan = $peserta->kegiatan->nama_kegiatan;

        // Mendapatkan data guru dari model Guru
        $getById = Guru::where('no_ktp', $peserta->no_ktp)->first();
        if ($getById == null) {
            $getById = Pegawai::where('no_ktp', $peserta->no_ktp)->first();
        }
        // $title = "DAFTAR HADIR PESERTA KOORDINASI  TEKNIS PROGRAM GERAK PENGGERAK";


        $pdf = PDF::loadView('pages.admin.peserta.cetakById', compact('getById', 'peserta', 'namaKegiatan'));

        // Set properties PDF
        $pdf->setPaper('a4', 'potrait'); // Set kertas ke mode landscape
        // $pdf->setPaper([0, 0, 1600, 800]); // Lebar 800px, Tinggi 1000px

        // Download PDF dengan nama file
        // return $pdf->stream('Biodata-' . $peserta->nama . '-' . $namaKegiatan . '.pdf');
        return $pdf->stream('Biodata-' . $peserta->nama . '-' . $namaKegiatan . '.pdf');
    }

    public function cetakByUser($id)
    {
        // dd('gas user');
        $peserta = PesertaKegiatan::find($id);
        dd($peserta);

        $namaKegiatan = $peserta->kegiatan->nama_kegiatan;

        // Mendapatkan data guru dari model Guru
        $getById = Guru::where('no_ktp', $peserta->no_ktp)->first();
        if ($getById == null) {
            $getById = Pegawai::where('no_ktp', $peserta->no_ktp)->first();
        }
        // $title = "DAFTAR HADIR PESERTA KOORDINASI  TEKNIS PROGRAM GERAK PENGGERAK";


        $pdf = PDF::loadView('pages.admin.peserta.cetakById', compact('getById', 'peserta', 'namaKegiatan'));

        // Set properties PDF
        $pdf->setPaper('a4', 'potrait'); // Set kertas ke mode landscape
        // $pdf->setPaper([0, 0, 1600, 800]); // Lebar 800px, Tinggi 1000px

        // Download PDF dengan nama file
        // return $pdf->stream('Biodata-' . $peserta->nama . '-' . $namaKegiatan . '.pdf');
        return $pdf->download('Biodata-' . $peserta->nama . '-' . $namaKegiatan . '.pdf');
    }

    public function export($id_kegiatan)
    {

        // dd('tes');
        $getKegiatan = Kegiatan::find($id_kegiatan);
        $data = PesertaKegiatan::where('id_kegiatan', $id_kegiatan)->get();
        // dd($getKegiatan);

        return Excel::download(new PartisipanKegiatanExport($id_kegiatan, $getKegiatan->tgl_kegiatan, $getKegiatan->nama_kegiatan), 'Data-Partisipan-' . $getKegiatan->nama_kegiatan . '.xlsx');
    }
}
