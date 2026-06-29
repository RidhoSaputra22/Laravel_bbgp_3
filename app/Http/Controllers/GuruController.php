<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Jabatan;
use App\Models\JabatanKependidikan;
use App\Models\JabatanPendidik;
use App\Models\JabatanStakeHolder;
use App\Models\JenisJabatan;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Kepegawaian;
use App\Models\Pendidikan;
use App\Models\SatuanPendidikan;
use App\Models\Sekolah;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Facades\Session;

class GuruController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $datas = array(
            's_kepegawaian' => Kepegawaian::select('id', 'name')->get(),
            's_kependidikan' => SatuanPendidikan::select('id', 'name')->get(),
            's_gelar' => Pendidikan::select('id', 'name')->get(),
            's_jabatan' => Jabatan::select('id', 'name')->get(),
            's_kabupaten' => Kabupaten::select('id', 'name')->get(),
            's_kecamatan' => Kecamatan::select('id', 'name')->get(),
            's_sekolah' => [], // Loaded via AJAX
            's_jabPendidik' => JabatanPendidik::select('id', 'name')->get(),
            's_jabKependidikan' => JabatanKependidikan::select('id', 'name')->get(),
            's_jabStakeholder' => JabatanStakeHolder::select('id', 'name')->get(),
            's_jabKategori' => ['GP (Guru Penggerak)', 'NoN GP (Guru Penggerak)'],
            's_jabKategoriPengawas' => ['Sertifikat GP (Guru Penggerak)', 'Diklat Cawas', 'Lainnya'],
            's_jabKategoriKepsek' => ['Sertifikat GP (Guru Penggerak)', 'Diklat Cakep', 'Lainnya'],
            's_jabTugas' => ['GP (Guru Penggerak)', 'PP (Pengajar Praktik)', 'Fasil (Fasilitator)', 'Instruktur'],
        );
        
        return view('pages.admin.guru.index', ['menu' => 'guru', 'status' => $datas]);
    }

    // public function fetchSekolah()
    // {
    //     $schools = Sekolah::select('npsn_sekolah', 'nama_sekolah', 'kecamatan', 'kabupaten')->get(); // Optimalkan query jika perlu
    //     return response()->json($schools);
    // }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $datas = array(
            's_kepegawaian' => Kepegawaian::get(),
            's_kependidikan' => SatuanPendidikan::get(),
            's_gelar' => Pendidikan::get(),
            's_jabatan' => Jabatan::get(),
            's_kabupaten' => Kabupaten::get(),
            's_kecamatan' => Kecamatan::get(),
            's_sekolah' => [],
            's_jabPendidik' => JabatanPendidik::get(),
            's_jabKependidikan' => JabatanKependidikan::get(),
            's_jabStakeholder' => JabatanStakeHolder::get(),
            's_jabKategori' => ['GP (Guru Penggerak)', 'NoN GP (Guru Penggerak)'],
            's_jabTugas' => ['GP (Guru Penggerak)', 'PP (Pengajar Praktik)', 'Fasil (Fasilitator)', 'Instruktur'],

        );
        return view('pages.admin.guru.create', ['menu' => 'guru', 'status' => $datas]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $r = $request->all();
        // dd($r);
        // $foto = $request->file('pas_foto');
        // $ext = $foto->getClientOriginalExtension();
        // // $r['pas_foto'] = $request->file('pas_foto');

        // $nameFoto = date('Y-m-d_H-i-s_') . $r['no_ktp'] . "." . $ext;
        // $destinationPath = public_path('upload/guru');

        // $foto->move($destinationPath, $nameFoto);

        // $fileUrl = asset('upload/guru/' . $nameFoto);

        $r['pas_foto'] = '';
        // $r['status'] = 'Belum Kawin';
        $r['alamat_satuan'] = '';
        $r['eksternal_jabatan'] = $r['jenisJabatan'];
        $r['jenis_jabatan'] = $r['jabJenis'];
        $r['kategori_jabatan'] = $r['jabKategori'] ?? '';
        $r['tugas_jabatan'] = $r['jabTugas'] ?? '';
        $r['is_verif'] = 'belum';
        // dd($r);

        Guru::create($r);

        return redirect()->route('guru.index')->with('message', 'store');
    }

    /**
     * Display the specified resource.
     */
    public function verifikasi(string $id)
    {

        $data = Guru::find($id);
        $getData = Guru::find($id);
        $data->is_verif = 'sudah';
        $data->save();
        return response()->json([
            'status' => $data,
            'data' => $getData,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $datas = array(
            's_kepegawaian' => Kepegawaian::get(),
            's_kependidikan' => SatuanPendidikan::get(),
            's_gelar' => Pendidikan::get(),
            's_jabatan' => Jabatan::get(),
            's_kabupaten' => Kabupaten::get(),
            's_kecamatan' => Kecamatan::get(),
            's_sekolah' => [],
            's_jabPendidik' => JabatanPendidik::get(),
            's_jabKependidikan' => JabatanKependidikan::get(),
            's_jabStakeholder' => JabatanStakeHolder::get(),
            's_jabKategori' => ['GP (Guru Penggerak)', 'NoN GP (Guru Penggerak)'],
            's_jabKategoriPengawas' => ['Sertifikat GP (Guru Penggerak)', 'Diklat Cawas', 'Lainnya'],
            's_jabKategoriKepsek' => ['Sertifikat GP (Guru Penggerak)', 'Diklat Cakep', 'Lainnya'],
            's_jabTugas' => ['GP (Guru Penggerak)', 'PP (Pengajar Praktik)', 'Fasil (Fasilitator)', 'Instruktur'],
        );

        $data = Guru::find($id);
        return view('pages.admin.guru.edit', ['menu' => 'guru', 'datas' => $data, 'status' => $datas]);
    }

    public function getDetail(Request $request)
    {
        try {
            //code...
            $pesertaId = $request->input('id');
            $peserta = Guru::find(id: $pesertaId);
    
            return response()->json([
                'data' => $peserta,
                'nama_sekolah' => $peserta->sekolah->nama_sekolah ?? '',
                'kecamatan_sekolah' => $peserta->sekolah->kecamatan ?? '',
                'kabupaten_sekolah' => $peserta->sekolah->kabupaten ?? '',
            ]);
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        //
        $r = $request->all();
        // dd($r);
        $data = Guru::find($r['id']);
        // dd($data);
        // $foto = $request->file('pas_foto');

        // if ($request->hasFile('pas_foto')) {
        //     $ext = $foto->getClientOriginalExtension();
        //     $nameFoto = date('Y-m-d_H-i-s_') . $r['no_ktp'] . "." . $ext;
        //     $destinationPath = public_path('upload/guru');

        //     $foto->move($destinationPath, $nameFoto);

        //     $fileUrl = asset('upload/guru/' . $nameFoto);
        //     $r['pas_foto'] = $nameFoto;
        // } else {
        //     $r['pas_foto'] = $request->pas_fotoLama;
        // }
        $r['pas_foto'] = '';
        // $r['status'] = 'Belum Kawin';
        $r['alamat_satuan'] = '';
        $r['eksternal_jabatan'] = $r['jenisJabatan'];

        if ($r['jabJenis'] == 'Lainnya') {
            $r['jabJenis'] = $r['jabLainnya'];
            $r['jenis_jabatan'] = $r['jabJenis'];
        } else {
            $r['jenis_jabatan'] = $r['jabJenis'];
        }

        if ($r['kabupaten'] == 'Tidak ada') {
            $r['kabupaten'] = $r['diluarKab'];
        }

        $r['kategori_jabatan'] = $r['jabKategori'] ?? '';
        $r['tugas_jabatan'] = $r['jabTugas'] ?? '';
        // $r['is_verif'] = 'belum';
        // dd($r);
        $data->update($r);
        return redirect()->route('guru.index')->with('message', 'update');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        $data = Guru::find($id);
        $data->delete();
        return response()->json($data);
    }

    public function export(Request $request)
    {
        // dd($request->all());

        // Mendapatkan data guru dari model Guru
        // $datas = Guru::all();

        $datas = Guru::query();

        // Apply filters jika ada
        if ($request->nama) {
            $datas->where('nama_lengkap', 'like', '%' . $request->nama . '%');
        }
        if ($request->jenisJabatan) {
            $datas->where('jenis_jabatan', $request->jenisJabatan);
        }
        if ($request->jabEksternal) {
            $datas->where('eksternal_jabatan', $request->jabEksternal);
        }
        if ($request->jabKategori) {
            $datas->where('kategori_jabatan', $request->jabKategori);
        }
        if ($request->jabTugas) {
            $datas->where('tugas_jabatan', $request->jabTugas);
        }
        if ($request->jabLatar) {
            $datas->where('latar_jabatan', $request->jabLatar);
        }
        if ($request->kabupatenFilter) {
            $datas->where('kabupaten', $request->kabupatenFilter);
        }

        // Dapatkan semua data setelah filter
        $datas = $datas->get();
        // dd($datas);

        $pdf = PDF::loadView('pages.admin.guru.cetak', compact('datas'));

        // Set properties PDF
        // $pdf->setPaper('a4', 'landscape'); // Set kertas ke mode landscape
        $pdf->setPaper([0, 0, 2000, 800]); // Lebar 800px, Tinggi 1000px


        // Download PDF dengan nama file 'data_guru.pdf'
        return $pdf->stream('data_eksternal_BBGTK.pdf');
    }

    public function exportByUser($id)
    {
        // Mendapatkan data guru dari model Guru
        $data = Guru::find($id);

        $pdf = PDF::loadView('pages.admin.guru.cetakByUser', compact('data'));

        // Set properties PDF
        // $pdf->setPaper('a4', 'landscape'); // Set kertas ke mode landscape
        $pdf->setPaper([0, 0, 1600, 800]); // Lebar 800px, Tinggi 1000px


        // Download PDF dengan nama file 'data_guru.pdf'
        return $pdf->stream('data_eksternal_BBGTK.pdf');
    }

    public function show(string $id)
    {
        // dd($id);
        try {
            $sekolahs = [];
            Sekolah::select('npsn_sekolah', 'nama_sekolah', 'kecamatan', 'kabupaten')
                ->chunk(500, function ($sekolahChunk) use (&$sekolahs) {
                    foreach ($sekolahChunk as $sekolah) {
                        $sekolahs[] = $sekolah;
                    }
                });
            $datas = array(
                's_kepegawaian' => Kepegawaian::get(),
                's_kependidikan' => SatuanPendidikan::get(),
                's_gelar' => Pendidikan::get(),
                's_jabatan' => Jabatan::get(),
                's_kabupaten' => Kabupaten::get(),
                's_kecamatan' => Kecamatan::get(),
                // 's_sekolah' => Sekolah::select('npsn_sekolah', 'nama_sekolah', 'kecamatan', 'kabupaten')->get(),
                's_sekolah' => $sekolahs,
                's_jabPendidik' => JabatanPendidik::get(),
                's_jabKependidikan' => JabatanKependidikan::get(),
                's_jabStakeholder' => JabatanStakeHolder::get(),
                's_jabKategori' => ['GP (Guru Penggerak)', 'NoN GP (Guru Penggerak)'],
                's_jabTugas' => ['GP (Guru Penggerak)', 'PP (Pengajar Praktik)', 'Fasil (Fasilitator)', 'Instruktur'],
    
            );
            // $data = Guru::orderBy('id','DESC')->get();
            $data = Guru::where('id', session('guru_id'))->first();
            // dd($data);
            // $data = Guru::find($id);
            // dd($data);
            if (!$data) {
                Session::flush();
                return redirect()->route('login');
            }
    
            return view('pages.admin.guru.indexByUser', ['menu' => 'guru', 'datas' => $data, 'status' => $datas]);
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
    }



    public function editByUser(string $id)
    {
        $sekolahs = [];
        Sekolah::select('npsn_sekolah', 'nama_sekolah', 'kecamatan', 'kabupaten')
            ->chunk(500, function ($sekolahChunk) use (&$sekolahs) {
                foreach ($sekolahChunk as $sekolah) {
                    $sekolahs[] = $sekolah;
                }
            });
        $datas = array(
            's_kepegawaian' => Kepegawaian::get(),
            's_kependidikan' => SatuanPendidikan::get(),
            's_gelar' => Pendidikan::get(),
            's_jabatan' => Jabatan::get(),
            's_kabupaten' => Kabupaten::get(),
            's_kecamatan' => Kecamatan::get(),
            // 's_sekolah' => Sekolah::select('npsn_sekolah', 'nama_sekolah', 'kecamatan', 'kabupaten')->get(),
            's_sekolah' => $sekolahs,
            's_jabPendidik' => JabatanPendidik::get(),
            's_jabKependidikan' => JabatanKependidikan::get(),
            's_jabStakeholder' => JabatanStakeHolder::get(),
            's_jabKategori' => ['GP (Guru Penggerak)', 'NoN GP (Guru Penggerak)'],
            's_jabKategoriPengawas' => ['Sertifikat GP (Guru Penggerak)', 'Diklat Cawas', 'Lainnya'],
            's_jabKategoriKepsek' => ['Sertifikat GP (Guru Penggerak)', 'Diklat Cakep', 'Lainnya'],
            's_jabTugas' => ['GP (Guru Penggerak)', 'Non GP (Guru Penggerak)', 'PP (Pengajar Praktik)', 'Fasil (Fasilitator)', 'Instruktur'],

        );


        $data = Guru::find($id);
        return view('pages.admin.guru.editByUser', ['menu' => 'guru', 'datas' => $data, 'status' => $datas]);
    }

    public function updateByUser(Request $request)
    {
        //
        $r = $request->all();
        // dd($r);
        $data = Guru::find($r['id']);
        // dd($data);
        // $foto = $request->file('pas_foto');

        // if ($request->hasFile('pas_foto')) {
        //     $ext = $foto->getClientOriginalExtension();
        //     $nameFoto = date('Y-m-d_H-i-s_') . $r['no_ktp'] . "." . $ext;
        //     $destinationPath = public_path('upload/guru');

        //     $foto->move($destinationPath, $nameFoto);

        //     $fileUrl = asset('upload/guru/' . $nameFoto);
        //     $r['pas_foto'] = $nameFoto;
        // } else {
        //     $r['pas_foto'] = $request->pas_fotoLama;
        // }
        $r['pas_foto'] = '';
        $r['status'] = 'Belum Kawin';
        $r['alamat_satuan'] = '';
        $r['eksternal_jabatan'] = $r['jenisJabatan'];
        $r['jenis_jabatan'] = $r['jabJenis'];
        $r['kategori_jabatan'] = $r['jabKategori'] ?? '';
        $r['tugas_jabatan'] = $r['jabTugas'] ?? '';
        // $r['is_verif'] = 'sudah';
        // $r['is_verif'] = 'belum';
        // dd($r['jenis_bank']);

        $data->update($r);
        return redirect()->route('guru.show', $r['id'])->with('message', 'update');
    }

    public function cari(Request $request)
    {
        $query = Guru::query()
            ->leftJoin('sekolahs', 'gurus.npsn_sekolah', '=', 'sekolahs.npsn_sekolah')
            ->select('gurus.*', 'sekolahs.nama_sekolah');

        $totalRecords = Guru::count();

        if ($request->nama_sekolah) {
            $query->where('sekolahs.nama_sekolah', 'like', '%' . $request->nama_sekolah . '%');
        }

        if ($request->filled('nama_lengkap')) {
            $query->where('gurus.nama_lengkap', 'like', '%' . $request->nama_lengkap . '%');
        }

        if ($request->filled('status_kepegawaian')) {
            $query->where('gurus.status_kepegawaian', $request->status_kepegawaian);
        }

        if ($request->filled('kabupaten')) {
            $query->where('gurus.kabupaten', $request->kabupaten);
        }

        if ($request->has('search') && !empty($request->search['value'])) {
            $searchValue = $request->search['value'];
            $query->where(function($q) use ($searchValue) {
                $q->where('gurus.nama_lengkap', 'like', '%' . $searchValue . '%')
                  ->orWhere('gurus.no_ktp', 'like', '%' . $searchValue . '%');
            });
        }

        $filteredRecords = $query->count();

        $result = $query->orderBy('gurus.created_at', 'desc')
            ->skip($request->get('start', 0))
            ->take($request->get('length', 10))
            ->get();

        return response()->json([
            'draw' => (int)$request->get('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'status' => true,
            'data' => $result->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_lengkap' => $item->nama_lengkap,
                    'no_ktp' => $item->no_ktp,
                    'npsn_sekolah' => $item->npsn_sekolah,
                    'nama_sekolah' => $item->nama_sekolah,
                    'kabupaten' => $item->kabupaten,
                    'status_kepegawaian' => $item->status_kepegawaian,
                    'eksternal_jabatan' => $item->eksternal_jabatan,
                    'kategori_jabatan' => $item->kategori_jabatan,
                    'jenis_jabatan' => $item->jenis_jabatan,
                    'tugas_jabatan' => $item->tugas_jabatan,
                    'latar_jabatan' => $item->latar_jabatan ?? 'tidak ada',
                    'is_verif' => $item->is_verif
                ];
            })
        ]);
    }
}
