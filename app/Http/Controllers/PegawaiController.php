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

        );


        return view('pages.admin.pegawai.create', ['menu' => 'pegawai', 'datas' => $datas]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $r = $request->all();


        if ($r['jenis_pegawai'] == 'PPNPN') {
            $r['golongan'] = '';
        }

        $r['pas_foto'] = '';
        $username = strtolower(str_replace(' ', '_', $r['nama_lengkap']));
        $r['username'] = $username;

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
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = Pegawai::find($id);

        $datas = array(
            's_kepegawaian' => Kepegawaian::get(),
            's_kependidikan' => SatuanPendidikan::get(),
            's_gelar' => Pendidikan::get(),
            's_jabatan' => JabatanPenugasanPegawai::get(),
            's_kabupaten' => Kabupaten::get(),
            's_kecamatan' => Kecamatan::get(),
            'golongan' => JabatanPenugasanGolongan::get(),
            'jabatan' => JabatanPenugasanPegawai::get(),


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



        if ($r['jenis_pegawai'] == 'PPNPN') {
            $r['golongan'] = '';
        }

        $r['pas_foto'] = '';
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
        $isAdmin = in_array(strtolower(trim((string) session('role'))), ['admin', 'superadmin', 'kepala', 'database'], true);
        abort_unless($isAdmin || (string) session('no_ktp') === (string) $id, 403);

        $kota = Kabupaten::get();
        $findPegawai = Pegawai::where('no_ktp', $id)->first();
        if ($findPegawai == null) {
            return redirect()->back()->with('message', 'gagal login');
        }
        $data = array(
            'dataPenugasanPegawai' => Internal::where('jenis', 'Penugasan Pegawai')->where('nik', $findPegawai['no_ktp'])->get() ?? [],
            'dataPenugasanPpnpn' => Internal::where('jenis', 'Penugasan PPNPN')->where('nik', $findPegawai['no_ktp'])->get() ?? [],
            'dataPendamping' => Internal::where('nik', $findPegawai['no_ktp'])->get() ?? [],
            'dataPegawai' => $findPegawai,
            'jadwalLokakarya' => Internal::where('jenis', 'Pendamping Lokakarya')->where('nik', $findPegawai['no_ktp'])->get(),

        );
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
        $isAdmin = in_array(strtolower(trim((string) session('role'))), ['admin', 'superadmin', 'kepala', 'database'], true);
        abort_unless($peserta && ($isAdmin || (string) $peserta->nik === (string) session('no_ktp')), 403);

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
            'pendamping' => Pendamping::find($id),
        );
        $pegawai = Pegawai::where('nip', $datas['pendamping']->nip)->first();

        $title = 'Pendamping Lokakarya';


        return view('pages.admin.pegawai.editPendamping', ['menu' => 'pegawai', 'title' => $title, 'datas' => $datas, 'pegawai' => $pegawai]);
    }

    public function editUser(string $id)
    {
        $data = Pegawai::find($id);
        abort_unless($data && (string) session('no_ktp') === (string) $data->no_ktp, 403);
        $datas = array(
            's_kepegawaian' => Kepegawaian::get(),
            's_kependidikan' => SatuanPendidikan::get(),
            's_gelar' => Pendidikan::get(),
            's_jabatan' => JabatanPenugasanGolongan::get(),
            's_kabupaten' => Kabupaten::get(),
            's_kecamatan' => Kecamatan::get(),
            'golongan' => JabatanPenugasanGolongan::get(),

        );
        return view('pages.admin.pegawai.editPegawai', ['menu' => 'pegawai', 'pegawai' => $data, 'datas' => $datas]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateuser(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:pegawais,id',
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'no_ktp' => 'required|string|max:50|regex:/^[A-Za-z0-9._-]+$/',
            'nip' => 'nullable|string|max:50',
            'status_kepegawaian' => 'nullable|string|max:100',
            'tempat_lahir' => 'nullable|string|max:255',
            'tgl_lahir' => 'nullable|date',
            'gender' => 'nullable|string|max:30',
            'alamat_rumah' => 'nullable|string|max:1000',
            'agama' => 'nullable|string|max:50',
            'pendidikan' => 'nullable|string|max:100',
            'satuan_pendidikan' => 'nullable|string|max:255',
            'kabupaten' => 'nullable|string|max:255',
            'alamat_satuan' => 'nullable|string|max:1000',
            'status' => 'nullable|string|max:100',
            'no_hp' => 'nullable|string|max:30',
            'no_wa' => 'nullable|string|max:30',
            'golongan' => 'nullable|string|max:100',
            'jenis_bank' => 'nullable|string|max:100',
            'no_rek' => 'nullable|string|max:50',
            'pas_foto' => 'nullable|file|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        $data = Pegawai::findOrFail($validated['id']);
        abort_unless((string) session('no_ktp') === (string) $data->no_ktp, 403);

        $payload = collect($validated)->only([
            'nama_lengkap', 'email', 'nip', 'status_kepegawaian', 'tempat_lahir',
            'tgl_lahir', 'gender', 'alamat_rumah', 'agama', 'pendidikan',
            'satuan_pendidikan', 'kabupaten', 'alamat_satuan', 'status',
            'no_hp', 'no_wa', 'golongan', 'jenis_bank', 'no_rek',
        ])->all();
        $payload['pas_foto'] = '';
        $payload['is_verif'] = 'sudah';

        if (! empty($payload['golongan'])) {
            $payload['golongan'] = ucwords(strtolower($payload['golongan']));
            JabatanPenugasanGolongan::firstOrCreate(['name' => $payload['golongan']]);
        }

        $data->update($payload);
        return redirect()->route('pegawai.show', session('no_ktp'))->with('message', 'update');
    }

}
