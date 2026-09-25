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
            $pesertaId = $request->integer('id');
            $peserta = Guru::select([
                'id', 'nama_lengkap', 'no_ktp', 'nip', 'gender', 'status_kepegawaian',
                'kabupaten', 'npsn_sekolah', 'eksternal_jabatan', 'jenis_jabatan',
            ])->findOrFail($pesertaId);
    
            return response()->json([
                'data' => $peserta,
                'nama_sekolah' => $peserta->sekolah->nama_sekolah ?? '',
                'kecamatan_sekolah' => $peserta->sekolah->kecamatan ?? '',
                'kabupaten_sekolah' => $peserta->sekolah->kabupaten ?? '',
            ]);
        } catch (\Exception $e) {
            report($e);

            return response()->json(['message' => 'Detail data tidak dapat diproses.'], 500);
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
        $this->authorizeUserGuru((string) $id);

        // Mendapatkan data guru dari model Guru
        $data = Guru::findOrFail($id);

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
        $this->authorizeUserGuru($id);

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
        $validated = $request->validate([
            'id' => 'required|integer|exists:gurus,id',
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'no_ktp' => 'required|string|max:50|regex:/^[A-Za-z0-9._-]+$/',
            'nip' => 'nullable|string|max:50',
            'npwp' => 'nullable|string|max:50',
            'nuptk' => 'nullable|string|max:50',
            'status_kepegawaian' => 'nullable|string|max:100',
            'tempat_lahir' => 'nullable|string|max:255',
            'tgl_lahir' => 'nullable|date',
            'gender' => 'nullable|string|max:30',
            'alamat_rumah' => 'nullable|string|max:1000',
            'agama' => 'nullable|string|max:50',
            'pendidikan' => 'nullable|string|max:100',
            'satuan_pendidikan' => 'nullable|string|max:255',
            'kabupaten' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:100',
            'no_hp' => 'nullable|string|max:30',
            'no_wa' => 'nullable|string|max:30',
            'jabatan' => 'nullable|string|max:255',
            'jenis_bank' => 'nullable|string|max:100',
            'no_rek' => 'nullable|string|max:50',
            'jenisJabatan' => 'required|string|max:100',
            'jabJenis' => 'required|string|max:255',
            'jabLainnya' => 'nullable|string|max:255',
            'jabKategori' => 'nullable|string|max:255',
            'jabTugas' => 'nullable|string|max:255',
            'npsn_sekolah' => 'nullable|string|max:50',
            'pas_foto' => 'nullable|file|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        $this->authorizeUserGuru((string) $validated['id']);
        $data = Guru::findOrFail($validated['id']);

        $data->update([
            'nama_lengkap' => $validated['nama_lengkap'],
            'email' => $validated['email'] ?? null,
            'nip' => $validated['nip'] ?? null,
            'npwp' => $validated['npwp'] ?? null,
            'nuptk' => $validated['nuptk'] ?? null,
            'status_kepegawaian' => $validated['status_kepegawaian'] ?? null,
            'tempat_lahir' => $validated['tempat_lahir'] ?? null,
            'tgl_lahir' => $validated['tgl_lahir'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'alamat_rumah' => $validated['alamat_rumah'] ?? null,
            'agama' => $validated['agama'] ?? null,
            'pendidikan' => $validated['pendidikan'] ?? null,
            'satuan_pendidikan' => $validated['satuan_pendidikan'] ?? null,
            'kabupaten' => $validated['kabupaten'] ?? null,
            'status' => 'Belum Kawin',
            'no_hp' => $validated['no_hp'] ?? null,
            'no_wa' => $validated['no_wa'] ?? null,
            'jabatan' => $validated['jabatan'] ?? null,
            'jenis_bank' => $validated['jenis_bank'] ?? null,
            'no_rek' => $validated['no_rek'] ?? null,
            'npsn_sekolah' => $validated['npsn_sekolah'] ?? null,
            'pas_foto' => '',
            'alamat_satuan' => '',
            'eksternal_jabatan' => $validated['jenisJabatan'],
            'jenis_jabatan' => $validated['jabJenis'] === 'Lainnya'
                ? ($validated['jabLainnya'] ?? '')
                : $validated['jabJenis'],
            'kategori_jabatan' => $validated['jabKategori'] ?? '',
            'tugas_jabatan' => $validated['jabTugas'] ?? '',
        ]);

        return redirect()->route('guru.show', $data->id)->with('message', 'update');
    }

    private function authorizeUserGuru(?string $id): void
    {
        $isAdmin = in_array(strtolower(trim((string) session('role'))), ['admin', 'superadmin', 'kepala', 'database'], true);

        abort_unless($isAdmin || (int) session('guru_id') === (int) $id, 403);
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
