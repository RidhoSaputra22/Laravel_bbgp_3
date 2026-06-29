<?php

namespace App\Http\Controllers;

use App\Models\Internal;
use App\Models\JabatanPenugasanGolongan;
use App\Models\JabatanPenugasanPegawai;
use App\Models\JabatanPenugasanPpnpn;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Kepegawaian;
use App\Models\Pegawai;
use App\Models\Pendamping;
use App\Models\Pendidikan;
use App\Models\SatuanPendidikan;
use Illuminate\Http\Request;

class PegawaiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $data = Pegawai::orderBy('id', 'DESC')->orderBy('is_verif', 'desc')->get();
        return view('pages.admin.pegawai.index', ['menu' => 'pegawai', 'datas' => $data]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $datas = array(
            's_kepegawaian' => Kepegawaian::get(),
            's_kependidikan' => SatuanPendidikan::get(),
            's_gelar' => Pendidikan::get(),
            's_jabatan' => JabatanPenugasanGolongan::get(),
            's_kabupaten' => Kabupaten::get(),
            's_kecamatan' => Kecamatan::get(),
            'golongan' => JabatanPenugasanGolongan::get(),
            'jabatan' => JabatanPenugasanPegawai::get(),
            // 's_sekolah' => Sekolah::select('npsn_sekolah', 'nama_sekolah', 'kecamatan', 'kabupaten')->get(),
            // 's_sekolah' => $sekolahs,
            // 's_jabPendidik' => JabatanPendidik::get(),
            // 's_jabKependidikan' => JabatanKependidikan::get(),
            // 's_jabStakeholder' => JabatanStakeHolder::get(),
            // 's_jabKategori' => ['GP (Guru Penggerak)', 'NoN GP (Guru Penggerak)'],
            // 's_jabTugas' => ['GP (Guru Penggerak)', 'PP (Pengajar Praktik)', 'Fasil (Fasilitator)', 'Instruktur'],

        );


        return view('pages.admin.pegawai.create', ['menu' => 'pegawai', 'datas' => $datas]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $r = $request->all();
        // $foto = $request->file('pas_foto');
        // $ext = $foto->getClientOriginalExtension();
        // // $r['pas_foto'] = $request->file('pas_foto');

        // $nameFoto = date('Y-m-d_H-i-s_') . $r['no_ktp'] . "." . $ext;
        // $nameFoto = date('Y-m-d_H-i-s_') . $r['no_ktp'] . "." . $ext;
        // $destinationPath = public_path('upload/pegawai');

        // $foto->move($destinationPath, $nameFoto);
        // $fileUrl = asset('upload/pegawai/' . $nameFoto);
        // dd($r);
        if ($r['jenis_pegawai'] == 'PPNPN') {
            $r['golongan'] = '';
        }

        $r['pas_foto'] = '';
        $username = strtolower(str_replace(' ', '_', $r['nama_lengkap']));
        $r['username'] = $username;

        // dd($r['username']);
        $r['is_verif'] = 'belum';

        if (isset($r['jabatan'])) {
            $r['jabatan'] = ucwords(strtolower($r['jabatan']));
            JabatanPenugasanPegawai::firstOrCreate(['name' => $r['jabatan']]);
        }

        if (isset($r['golongan']) && $r['golongan'] != '') {
            $r['golongan'] = ucwords(strtolower($r['golongan']));
            JabatanPenugasanGolongan::firstOrCreate(['name' => $r['golongan']]);
        }

        Pegawai::create($r);


        // return redirect()->route('internal.index')->with('message', 'store');
        return redirect()->route('pegawai.index')->with('message', 'store');
    }

    /**
     * Display the specified resource.
     */
    public function verifikasi(string $id)
    {
        $data = Pegawai::find($id);
        $getData = Pegawai::find($id);
        $data->is_verif = 'sudah';
        $data->save();
        return response()->json([
            'status' => $data,
            'data' => $getData,
        ]);
        // return response()->json($data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = Pegawai::find($id);

        // dd($data);
        $datas = array(
            's_kepegawaian' => Kepegawaian::get(),
            's_kependidikan' => SatuanPendidikan::get(),
            's_gelar' => Pendidikan::get(),
            's_jabatan' => JabatanPenugasanPegawai::get(),
            's_kabupaten' => Kabupaten::get(),
            's_kecamatan' => Kecamatan::get(),
            'golongan' => JabatanPenugasanGolongan::get(),
            'jabatan' => JabatanPenugasanPegawai::get(),

            // 's_sekolah' => Sekolah::select('npsn_sekolah', 'nama_sekolah', 'kecamatan', 'kabupaten')->get(),
            // 's_sekolah' => $sekolahs,
            // 's_jabPendidik' => JabatanPendidik::get(),
            // 's_jabKependidikan' => JabatanKependidikan::get(),
            // 's_jabStakeholder' => JabatanStakeHolder::get(),
            // 's_jabKategori' => ['GP (Guru Penggerak)', 'NoN GP (Guru Penggerak)'],
            // 's_jabTugas' => ['GP (Guru Penggerak)', 'PP (Pengajar Praktik)', 'Fasil (Fasilitator)', 'Instruktur'],

        );
        return view('pages.admin.pegawai.edit', ['menu' => 'pegawai', 'pegawai' => $data, 'datas' => $datas]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $r = $request->all();
        $data = Pegawai::find($r['id']);
        // $foto = $request->file('pas_foto');

        // if ($request->hasFile('pas_foto')) {
        //     $ext = $foto->getClientOriginalExtension();
        //     $nameFoto = date('Y-m-d_H-i-s_') . $r['no_ktp'] . "." . $ext;
        //     $destinationPath = public_path('upload/pegawai');

        //     $foto->move($destinationPath, $nameFoto);
        //     $fileUrl = asset('upload/pegawai/' . $nameFoto);
        //     $r['pas_foto'] = $nameFoto;
        // } else {
        // }

        if ($r['jenis_pegawai'] == 'PPNPN') {
            $r['golongan'] = '';
        }

        $r['pas_foto'] = '';
        // $r['is_verif'] = 'belum';
        // dd($r);
        if (isset($r['jabatan'])) {
            $r['jabatan'] = ucwords(strtolower($r['jabatan']));
            JabatanPenugasanPegawai::firstOrCreate(['name' => $r['jabatan']]);
        }

        if (isset($r['golongan']) && $r['golongan'] != '') {
            $r['golongan'] = ucwords(strtolower($r['golongan']));
            JabatanPenugasanGolongan::firstOrCreate(['name' => $r['golongan']]);
        }

        $data->update($r);
        if (session('role') == 'pegawai') {

            return redirect()->route('pegawai.show', session('no_ktp'))->with('message', 'update');
        }
        return redirect()->route('pegawai.index')->with('message', 'update');
        // return redirect()->route('internal.index')->with('message', 'update');
        // return redirect()->route('pegawai.index')->with('message', 'update');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = Pegawai::find($id);
        $data->delete();
        return response()->json($data);
    }

    public function detailUser(Request $request)
    {

    }

    public function show(string $id)
    {
        $kota = Kabupaten::get();
        // $findPegawai = Pegawai::find($id);
        $findPegawai = Pegawai::where('no_ktp', $id)->first();
        if ($findPegawai == null) {
            return redirect()->back()->with('message', 'gagal login');
        }
        // dd($findPegawai);
        // dd($id);
        $data = array(
            'dataPenugasanPegawai' => Internal::where('jenis', 'Penugasan Pegawai')->where('nik', $findPegawai['no_ktp'])->get() ?? [],
            'dataPenugasanPpnpn' => Internal::where('jenis', 'Penugasan PPNPN')->where('nik', $findPegawai['no_ktp'])->get() ?? [],
            'dataPendamping' => Internal::where('nik', $findPegawai['no_ktp'])->get() ?? [],
            'dataPegawai' => $findPegawai,
            'jadwalLokakarya' => Internal::where('jenis', 'Pendamping Lokakarya')->where('nik', $findPegawai['no_ktp'])->get(),

        );
        // dd($data);
        // dd($data['jadwalLokakarya']);
        return view('pages.admin.pegawai.show', ['menu' => 'pegawai', 'datas' => $data, 'pegawai' => $findPegawai]);
    }

    public function showPegawai(Request $request)
    {
        $pesertaId = $request->input('id');
        $peserta = Pegawai::find($pesertaId);

        return response()->json($peserta);
    }


    public function showDetailLokakarya(Request $request)
    {
        $pesertaId = $request->input('id');
        $peserta = Internal::find($pesertaId);

        return response()->json($peserta);
    }

    public function editPenugasan(string $id)
    {
        $title = '';
        $datas = array(
            'golongan' => JabatanPenugasanGolongan::get(),
            'jabatanPegawai' => JabatanPenugasanPegawai::get(),
            'jabatanPpnpn' => JabatanPenugasanPpnpn::get(),
            'kota' => Kabupaten::get(),
            'penugasan' => Internal::find($id),
            'pendamping' => Pendamping::find($id),
        );

        $pegawai = Pegawai::where('nip', $datas['penugasan']->nip)->first();
        // dd($pegawai);


        // if ($datas['penugasan']->jenis == 'Pendamping Lokakarya') {
        //     $title = 'Pendamping Lokakarya';
        //     return view('pages.admin.internal.editPendamping', ['menu' => 'internal', 'title' => $title, 'datas' => $datas, 'pegawai' => $pegawai]);
        // }

        if ($datas['penugasan']->jenis == 'Penugasan PPNPN') {
            $title = 'Penugasan PPNPN';
        } else {
            $title = 'Penugasan Pegawai';
        }

        return view('pages.admin.pegawai.editPenugasan', ['menu' => 'pegawai', 'title' => $title, 'datas' => $datas, 'pegawai' => $pegawai]);
    }

    public function editPendamping(string $id)
    {
        $title = '';

        $datas = array(
            'golongan' => JabatanPenugasanGolongan::get(),
            'jabatanPegawai' => JabatanPenugasanPegawai::get(),
            'jabatanPpnpn' => JabatanPenugasanPpnpn::get(),
            'kota' => Kabupaten::get(),
            // 'penugasan' => Internal::find($id),
            'pendamping' => Pendamping::find($id),
        );
        $pegawai = Pegawai::where('nip', $datas['pendamping']->nip)->first();
        // dd($datas['pendamping']->jenis);

        // if ($datas['pendamping']->jenis == 'Pendamping Lokakarya') {
        //     return view('pages.admin.internal.editPendamping', ['menu' => 'internal', 'title' => $title, 'datas' => $datas, 'pegawai' => $pegawai]);
        // }
        $title = 'Pendamping Lokakarya';


        return view('pages.admin.pegawai.editPendamping', ['menu' => 'pegawai', 'title' => $title, 'datas' => $datas, 'pegawai' => $pegawai]);
    }

    public function editUser(string $id)
    {
        $data = Pegawai::find($id);
        $datas = array(
            's_kepegawaian' => Kepegawaian::get(),
            's_kependidikan' => SatuanPendidikan::get(),
            's_gelar' => Pendidikan::get(),
            's_jabatan' => JabatanPenugasanGolongan::get(),
            's_kabupaten' => Kabupaten::get(),
            's_kecamatan' => Kecamatan::get(),
            'golongan' => JabatanPenugasanGolongan::get(),
            // 's_sekolah' => Sekolah::select('npsn_sekolah', 'nama_sekolah', 'kecamatan', 'kabupaten')->get(),
            // 's_sekolah' => $sekolahs,
            // 's_jabPendidik' => JabatanPendidik::get(),
            // 's_jabKependidikan' => JabatanKependidikan::get(),
            // 's_jabStakeholder' => JabatanStakeHolder::get(),
            // 's_jabKategori' => ['GP (Guru Penggerak)', 'NoN GP (Guru Penggerak)'],
            // 's_jabTugas' => ['GP (Guru Penggerak)', 'PP (Pengajar Praktik)', 'Fasil (Fasilitator)', 'Instruktur'],

        );
        return view('pages.admin.pegawai.editPegawai', ['menu' => 'pegawai', 'pegawai' => $data, 'datas' => $datas]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateuser(Request $request)
    {
        $r = $request->all();
        $data = Pegawai::find($r['id']);
        // $foto = $request->file('pas_foto');

        // if ($request->hasFile('pas_foto')) {
        //     $ext = $foto->getClientOriginalExtension();
        //     $nameFoto = date('Y-m-d_H-i-s_') . $r['no_ktp'] . "." . $ext;
        //     $destinationPath = public_path('upload/pegawai');

        //     $foto->move($destinationPath, $nameFoto);
        //     $fileUrl = asset('upload/pegawai/' . $nameFoto);
        //     $r['pas_foto'] = $nameFoto;
        // } else {
        // }
        $r['pas_foto'] = '';
        $r['is_verif'] = 'sudah';
        // dump($data);
        // dd($r);
        if (isset($r['jabatan'])) {
            $r['jabatan'] = ucwords(strtolower($r['jabatan']));
            JabatanPenugasanPegawai::firstOrCreate(['name' => $r['jabatan']]);
        }

        if (isset($r['golongan']) && $r['golongan'] != '') {
            $r['golongan'] = ucwords(strtolower($r['golongan']));
            JabatanPenugasanGolongan::firstOrCreate(['name' => $r['golongan']]);
        }
        
        $data->update($r);
        return redirect()->route('pegawai.show', session('no_ktp'))->with('message', 'update');
        // return redirect()->route('pegawai.index')->with('message', 'update');
    }

}
