<?php

namespace App\Http\Controllers;

use App\Models\Internal;
use App\Models\InternalPpnpn;
use App\Models\JabatanPenugasanGolongan;
use App\Models\JabatanPenugasanPegawai;
use App\Models\JabatanPenugasanPpnpn;
use App\Models\Kabupaten;
use App\Models\Pegawai;
use App\Models\PegawaiPpnpn;
use App\Models\Pendamping;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InternalController extends Controller
{
    public $title = '';

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kota = Kabupaten::get();

        $jadwalInternal = Internal::select('id', 'kota', 'golongan', 'jenis', 'deskripsi', 'kegiatan', 'tgl_kegiatan', 'tgl_selesai_kegiatan', 'jam_mulai', 'jam_selesai', 'nama', 'hotel', 'transport_pergi', 'bill_penginapan', 'transport_pulang', 'hari_1', 'hari_2', 'hari_3', 'hari_4', 'hari_5', 'hari_6', 'hari_7')
            ->whereIn('jenis', ['Pendamping Lokakarya'])
            ->get()
            ->groupBy('kegiatan');

        $data = [

            'dataPenugasanPegawai' => Pegawai::orderByDesc('id')->where('jenis_pegawai', 'BBGP')->where('is_verif', 'sudah')->get(),
            'dataPenugasanPpnpn' => Pegawai::orderByDesc('id')->where('jenis_pegawai', 'PPNPN')->where('is_verif', 'sudah')->get(),
            'dataPegawai' => Pegawai::get(),
            'lokaBBGP' => $jadwalInternal->map(function ($items, $key) {
                $groupedByJenis = $items->groupBy('jenis');
                $penugasanLoka = $groupedByJenis->get('Pendamping Lokakarya', collect());

                // Bangun array multidimensi untuk setiap pegawai
                $penugasanPegawai = $penugasanLoka->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'nama' => $item->nama,
                        'golongan' => $item->golongan,
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
                    'kegiatan' => $key,
                    'deskripsi' => $items->first()->deskripsi,
                    'kota' => $items->first()->kota,
                    'tgl_kegiatan' => $items->first()->tgl_kegiatan,
                    'tgl_selesai_kegiatan' => $items->first()->tgl_selesai_kegiatan,
                    'jam_mulai' => $items->first()->jam_mulai,
                    'jam_selesai' => $items->first()->jam_selesai,
                    'penugasan_pegawai' => $penugasanPegawai->toArray(),

                ];
            })->values(),

        ];
        $dataPendamping = Pendamping::get();
        $dataPpnpn = PegawaiPpnpn::get();

        // Proses data menjadi array yang diinginkan

        // dump($jadwal);
        // dd($data['lokaBBGP'][0]);
        // dd($data['lokaPPNPN']);

        return view('pages.admin.internal.index', ['menu' => 'internal', 'datas' => $data, 'kota' => $kota, 'dataPendamping' => $dataPendamping, 'dataPpnpn' => $dataPpnpn]);
    }

    public function verifikasi(string $id)
    {
        // dd($id);
        $data = '';
        $getData = '';
        $data = Internal::find($id);
        $getData = Internal::find($id);
        if ($data == null) {
            $data = Pendamping::find($id);
            $getData = Pendamping::find($id);
        }
        $data->is_verif = 'sudah';
        $data->save();

        return response()->json([
            'status' => $data,
            'data' => $getData,
        ]);
    }

    // Lokakarya
    public function indexLokakarya($nik)
    {
        // dump($nik);
        $pegawai = Pegawai::where('no_ktp', $nik)->first();
        // dump($pegawai->no_ktp);
        $datas = [
            'penugasanLokakarya' => Internal::where('jenis', 'Pendamping Lokakarya')->where('nik', $pegawai->no_ktp)->get(),
            'penugasanPpnpn' => Internal::where('nik', $nik)->get(),
            'getJenisLokakarya' => Internal::where('jenis', 'Pendamping Lokakarya')->where('nik', $nik)->first(),
            'getNama' => Internal::where('nik', $pegawai->no_ktp)->first(),

            // 'penugasanLokakaryaPpnpn' => Internal::where('jenis', 'Pendamping Lokakarya')->where('nik', $pegawai->no_ktp)->get() ,
            // 'penugasanPpnpn' => Internal::where('nik', $pegawai->no_ktp)->get(),
            'getJenisLokakaryaPpnpn' => Internal::where('jenis', 'Pendamping Lokakarya')->where('nik', $pegawai->no_ktp)->first(),
            'getNamaPpnpn' => Pegawai::where('no_ktp', $pegawai->no_ktp)->first(),

            // 'penugasanLokakaryaPpnpn' => InternalPpnpn::where('id', $pegawai->id)->get() ,
            // // 'penugasanPpnpn' => InternalPpnpn::where('id', $pegawai->id)->get(),
            // 'getJenisLokakaryaPpnpn' => InternalPpnpn::where('id', $pegawai->id)->first(),
            // 'getNamaPpnpn' => InternalPpnpn::where('id', $pegawai->id)->first(),
            // 'getJenisPpnpn' => InternalPpnpn::where('jenis', 'Pendamping PPNPN')->where('nik', $nik)->first(),

        ];
        // dd($datas);
        if (session('role') == 'pegawai') {
            // return view('pages.admin.pegawai.show', ['menu' => ''], compact('datas'));
            return redirect()->route('pegawai.show', ['id' => session('no_ktp')])->with('message', 'update');
        }

        return view('pages.admin.internal.indexLokakarya', ['menu' => 'internal'], compact('datas'));
    }

    public function createLokakarya($id)
    {
        // $pegawai = Pegawai::find($id);
        // if ($pegawai == null) {
        //     $pegawai = PegawaiPpnpn::find($id);
        // }
        // $datas = array(
        //     'golongan' => JabatanPenugasanGolongan::get(),
        //     'jabatanPegawai' => JabatanPenugasanPegawai::get(),
        //     'jabatanPpnpn' => JabatanPenugasanPpnpn::get(),
        //     'dataPegawai' => Pegawai::get(),
        // );
        // dd($id);

        $pegawai = Pegawai::find($id) ?? PegawaiPpnpn::find($id);

        $datas = [
            // 'dataPenugasanLokakarya' => Internal::where('jenis', 'Pendamping Lokakarya')->get(),
            'dataPenugasanPpnpn' => Internal::where('jenis', 'Penugasan PPNPN')->where('nik', $pegawai['no_ktp'])->orderByDesc('id')->first(),
            'dataPenugasanPegawai' => Internal::where('jenis', 'Penugasan Pegawai')->where('nik', $pegawai['no_ktp'])->orderByDesc('id')->first(),
            // 'dataPenugasanPpnpn' => Internal::where('jenis', 'Penugasan PPNPN')->where('nik', $pegawai['no_ktp'])->latest()->get(),
            // 'dataPenugasanPegawai' => Internal::where('jenis', 'Penugasan Pegawai')->where('nik', $pegawai['no_ktp'])->latest()->get(),
            'dataPegawai' => Pegawai::get(),
            'jabatanPegawai' => JabatanPenugasanPegawai::get(),
            'golongan' => JabatanPenugasanGolongan::get(),
            'kota' => Kabupaten::get(),
        ];

        // dd($datas['dataPenugasanPegawai']);
        // dd(!$datas['dataPenugasanPpnpn']->isEmpty());
        // dd($id);
        return view('pages.admin.penugasan.lokakarya', ['menu' => ''], compact('pegawai', 'datas'));
    }

    // Khusus Pegawai yg login
    // public function storeLokakaryaPegawai(Request $r)
    // {
    //     // dd($r->all());
    //     $r = $r->all();

    //     $file = $r->file('thumbnail');
    //     if ($file->getSize() / 1024 >= 512) {
    //         return redirect()->route('pegawai.show', session('no_ktp'))->with('message', 'size gambar');
    //     }

    //     // $bukti = $request->file('bukti_bill');
    //     // $ext = $bukti->getClientOriginalExtension();
    //     // // $r['bukti_bill'] = $request->file('bukti_bill');

    //     // $nameBukti = date('Y-m-d_H-i-s_') . $r['nik'] . "." . $ext;
    //     // $destinationPath = public_path('upload/guru');

    //     // $bukti->move($destinationPath, $nameBukti);

    //     // $fileUrl = asset('upload/bukti_bill/' . $nameBukti);

    //     $mulai_kegiatan = explode(" ", $r["mulai_kegiatan"]);
    //     $r['tgl_kegiatan'] = $mulai_kegiatan[0];
    //     $r['jam_mulai'] = $mulai_kegiatan[1];

    //     $selesai_kegiatan = explode(" ", $r["selesai_kegiatan"]);
    //     $r['tgl_selesai_kegiatan'] = $selesai_kegiatan[0];
    //     $r['jam_selesai'] = $selesai_kegiatan[1];

    //     Internal::create($r);

    //     return redirect()->route('pegawai.show', session('no_ktp'))->with('message', 'store');
    // }

    // Khusus Admin dan jajarannya
    public function storeLokakarya(Request $request)
    {
        // dd($r->all());
        $r = $request->all();
        $pegawai = Pegawai::find($r['id_pegawai']) ?? PegawaiPpnpn::find($r['id_pegawai']);

        $file = $request->file('bukti_bill');
        // dd($file);
        if ($file == null) {
            unset($r['bukti_bill']);
        } else {
            if ($file->getSize() / 1024 >= 1500) {
                return session('role') == 'pegawai' ? redirect()->route('internal.create.lokakarya', $r['id_pegawai'])->with('message', 'size bukti') : redirect()->route('internal.create.lokakarya', $r['id_pegawai'])->with('message', 'size bukti');
            }

            $bukti = $request->file('bukti_bill');
            $ext = $bukti->getClientOriginalExtension();
            // dd($ext);
            if ($ext != 'pdf') {
                return session('role') == 'pegawai' ? redirect()->route('internal.create.lokakarya', $r['id_pegawai'])->with('message', 'size bukti') : redirect()->route('internal.create.lokakarya', $r['id_pegawai'])->with('message', 'size bukti');
            }
            // $r['bukti_bill'] = $request->file('bukti_bill');

            $nameBukti = date('Y-m-d_H-i-s_').$r['nik'].'.'.$ext;
            $destinationPath = public_path('upload/bukti_bill');

            $bukti->move($destinationPath, $nameBukti);
            $r['bukti_bill'] = $nameBukti;
            $fileUrl = asset('upload/bukti_bill/'.$nameBukti);
        }
        // dd($file);

        $mulai_kegiatan = explode(' ', $r['mulai_kegiatan']);
        $r['tgl_kegiatan'] = $mulai_kegiatan[0];
        $r['jam_mulai'] = $mulai_kegiatan[1];

        $selesai_kegiatan = explode(' ', $r['selesai_kegiatan']);
        $r['tgl_selesai_kegiatan'] = $selesai_kegiatan[0];
        $r['jam_selesai'] = $selesai_kegiatan[1];
        $r['tempat'] = $r['tempat'] ?? ($r['kota'] ?? null);
        $r['jabatan'] = $r['jabatan'] ?? ($pegawai->jabatan ?? null);
        $r['golongan'] = $r['golongan'] ?? ($pegawai->golongan ?? null);
        $r['is_verif'] = $r['is_verif'] ?? 'sudah';

        if ($r['hari_1'] == null) {
            $r['hari_1'] = 0;
        }
        if ($r['hari_2'] == null) {
            $r['hari_2'] = 0;
        }
        if ($r['hari_3'] == null) {

            $r['hari_3'] = 0;
        }
        if ($r['hari_4'] == null) {

            $r['hari_4'] = 0;
        }
        if ($r['hari_5'] == null) {

            $r['hari_5'] = 0;
        }
        if ($r['hari_6'] == null) {

            $r['hari_6'] = 0;
        }
        if ($r['hari_7'] == null) {

            $r['hari_7'] = 0;
        }

        // dd($r);

        Internal::create($r);

        if (session('role') == 'pegawai') {
            return redirect()->route('pegawai.show', session('no_ktp'))->with('message', 'store');
        }

        return redirect()->route('internal.index')->with('message', 'store');
    }

    public function editLokakarya($id)
    {
        // $loka = Internal::find($id);
        // // dd($loka);
        // $pegawai = Pegawai::find($id);
        // if ($pegawai == null) {
        //     $pegawai = PegawaiPpnpn::find($id);
        // }
        // $datas = array(
        //     'golongan' => JabatanPenugasanGolongan::get(),
        //     'jabatanPegawai' => JabatanPenugasanPegawai::get(),
        //     'jabatanPpnpn' => JabatanPenugasanPpnpn::get(),
        //     'kota' => Kabupaten::get(),
        //     'lokakarya' => $loka,
        // );
        $penugasan = Internal::find($id);
        // dd($penugasan);
        $datas = [
            'dataPenugasanLokakarya' => Internal::where('jenis', 'Pendamping Lokakarya')->where('nik', $penugasan->nik)->get(),
            'dataPenugasanPpnpn' => Internal::where('jenis', 'Penugasan PPNPN')->where('nik', $penugasan->nik)->get(),
            'dataPenugasanPegawai' => Internal::where('jenis', 'Penugasan Pegawai')->where('nik', $penugasan->nik)->get(),
            'dataPegawai' => Pegawai::get(),
            'jabatanPegawai' => JabatanPenugasanPegawai::get(),
            'golongan' => JabatanPenugasanGolongan::get(),
            'kota' => Kabupaten::get(),
            'mulai_kegiatan' => $penugasan->tgl_kegiatan.' '.$penugasan->jam_mulai,
            'selesai_kegiatan' => $penugasan->tgl_selesai_kegiatan.' '.$penugasan->jam_selesai,
        ];
        $pegawai = Pegawai::where('no_ktp', $penugasan->nik)->first();
        // dd($pegawai);
        $pendamping = Internal::find($id);

        // dd($pendamping);
        return view('pages.admin.penugasan.Editlokakarya', ['menu' => ''], compact('pendamping', 'datas', 'pegawai'));
    }

    public function updateLokakarya(Request $request)
    {
        // dd($r->all());
        $loka = Internal::find($request->id);
        // dd($loka);

        $r = $request->all();

        $file = $request->file('bukti_bill');
        // dd($file);
        if ($file == null) {
            $r['bukti_bill'] = $r['old_bukti_bill'];
        } else {
            if ($file->getSize() / 1024 >= 1500) {
                return session('role') == 'pegawai' ? redirect()->route('internal.create.lokakarya', $r['id_pegawai'])->with('message', 'size bukti') : redirect()->route('internal.create.lokakarya', $r['id_pegawai'])->with('message', 'size bukti');
            }

            $bukti = $request->file('bukti_bill');
            $ext = $bukti->getClientOriginalExtension();
            // dd($ext);
            if ($ext != 'pdf') {
                return session('role') == 'pegawai' ? redirect()->route('internal.create.lokakarya', $r['id_pegawai'])->with('message', 'size bukti') : redirect()->route('internal.create.lokakarya', $r['id_pegawai'])->with('message', 'size bukti');
            }
            // $r['bukti_bill'] = $request->file('bukti_bill');

            $nameBukti = date('Y-m-d_H-i-s_').$r['nik'].'.'.$ext;
            $destinationPath = public_path('upload/bukti_bill');

            $bukti->move($destinationPath, $nameBukti);
            $r['bukti_bill'] = $nameBukti;
            $fileUrl = asset('upload/bukti_bill/'.$nameBukti);
        }
        $mulai_kegiatan = explode(' ', $r['mulai_kegiatan']);
        $r['tgl_kegiatan'] = $mulai_kegiatan[0];
        $r['jam_mulai'] = $mulai_kegiatan[1];

        $selesai_kegiatan = explode(' ', $r['selesai_kegiatan']);
        $r['tgl_selesai_kegiatan'] = $selesai_kegiatan[0];
        $r['jam_selesai'] = $selesai_kegiatan[1];
        $r['tempat'] = $r['tempat'] ?? ($r['kota'] ?? $loka->tempat);
        $r['jabatan'] = $r['jabatan'] ?? $loka->jabatan;
        $r['golongan'] = $r['golongan'] ?? $loka->golongan;
        $r['is_verif'] = $r['is_verif'] ?? ($loka->is_verif ?? 'sudah');
        // dd($loka);

        if ($r['hari_1'] == null) {
            $r['hari_1'] = 0;
        }
        if ($r['hari_2'] == null) {
            $r['hari_2'] = 0;
        }
        if ($r['hari_3'] == null) {

            $r['hari_3'] = 0;
        }
        if ($r['hari_4'] == null) {

            $r['hari_4'] = 0;
        }
        if ($r['hari_5'] == null) {

            $r['hari_5'] = 0;
        }
        if ($r['hari_6'] == null) {

            $r['hari_6'] = 0;
        }
        if ($r['hari_7'] == null) {

            $r['hari_7'] = 0;
        }

        // dd($r);
        $loka->update($r);
        // dd( route('pegawai.session('no_ktp')));
        if (session('role') == 'pegawai') {
            return redirect()->route('pegawai.show', session('no_ktp'))->with('message', 'update');
        }

        return redirect()->route('internal.index.lokakarya', $loka->nik)->with('message', 'update');
    }

    public function updateLokakaryaJS(Request $r)
    {
        // dump($r->all());
        $loka = Internal::find($r->id);
        // dd($loka);

        $r = $r->all();
        // $mulai_kegiatan = explode(" ", $r["mulai_kegiatan"]);
        // $r['tgl_kegiatan'] = $mulai_kegiatan[0];
        // $r['jam_mulai'] = $mulai_kegiatan[1];

        // $selesai_kegiatan = explode(" ", $r["selesai_kegiatan"]);
        // $r['tgl_selesai_kegiatan'] = $selesai_kegiatan[0];
        // $r['jam_selesai'] = $selesai_kegiatan[1];
        // dd($loka);

        $r['transport_pergi'] = $r['transportPergi'];
        $r['transport_pulang'] = $r['transportPulang'];
        $r['bill_penginapan'] = $r['billPenginapan'];
        $r['bill_penginapan'] = $r['billPenginapan'];
        $r['hari_1'] = $r['hari1'];
        $r['hari_2'] = $r['hari2'];
        $r['hari_3'] = $r['hari3'];
        $r['hari_4'] = $r['hari4'];
        $r['hari_5'] = $r['hari5'];
        $r['hari_6'] = $r['hari6'];
        $r['hari_7'] = $r['hari7'];

        if ($r['hari_1'] == null) {
            $r['hari_1'] = 0;
        }
        if ($r['hari_2'] == null) {
            $r['hari_2'] = 0;
        }
        if ($r['hari_3'] == null) {

            $r['hari_3'] = 0;
        }
        if ($r['hari_4'] == null) {

            $r['hari_4'] = 0;
        }
        if ($r['hari_5'] == null) {

            $r['hari_5'] = 0;
        }
        if ($r['hari_6'] == null) {

            $r['hari_6'] = 0;
        }
        if ($r['hari_7'] == null) {

            $r['hari_7'] = 0;
        }

        $loka->update($r);

        return response()->json([
            'success' => true,
            'data' => $r,
        ]);
    }

    // penugasan pegawan BBGP
    public function indexPegawai($nik)
    {
        // dd($nik);
        $pegawai = Pegawai::where('no_ktp', $nik)->first() ?? Pegawai::find($nik);
        // dd($pegawai);

        $datas = [
            'penugasanPegawai' => Internal::where('jenis', 'Penugasan Pegawai')->where('nik', $pegawai->no_ktp)->get(),
            'penugasanPpnpn' => Internal::where('nik', $pegawai->no_ktp)->get(),
            'getJenisPegawai' => Internal::where('jenis', 'Penugasan Pegawai')->where('nik', $pegawai->no_ktp)->first(),
            // 'getJenisPpnpn' => Internal::where('jenis', 'Penugasan PPNPN')->where('nik', $nik)->first(),

        ];
        // dd($datas);

        return view('pages.admin.internal.indexPenugasan', ['menu' => ''], compact('datas'));
    }

    public function createPegawai($id)
    {
        $datas = [
            'dataPenugasanPegawai' => Internal::where('jenis', 'Penugasan Pegawai')->get(),
            'dataPenugasanPpnpn' => Internal::where('jenis', 'Penugasan PPNPN')->get(),
            'dataPegawai' => Pegawai::get(),
            'jabatanPegawai' => JabatanPenugasanPegawai::get(),
            'golongan' => JabatanPenugasanGolongan::get(),
            'kota' => Kabupaten::get(),

        ];

        $pegawai = Pegawai::find($id);

        return view('pages.admin.penugasan.pegawai', ['menu' => ''], compact('pegawai', 'datas'));
    }

    public function storePegawai(Request $r)
    {
        // dd($r->mulai_kegiatan);
        // dd($r->all());
        // $r = $r->all();

        // dd($r['mulai_kegiatan']);
        $mulai_kegiatan = explode(' ', $r['mulai_kegiatan']);
        $r['tgl_kegiatan'] = $mulai_kegiatan[0];
        $r['jam_mulai'] = $mulai_kegiatan[1];

        $selesai_kegiatan = explode(' ', $r['selesai_kegiatan']);
        $r['tgl_selesai_kegiatan'] = $selesai_kegiatan[0];
        $r['jam_selesai'] = $selesai_kegiatan[1];
        $r['tempat'] = $r['tempat'] ?? ($r['kota'] ?? null);
        $r['is_verif'] = $r['is_verif'] ?? 'sudah';

        // dd($r->all());

        Internal::create($r->all());

        return redirect()->route('internal.index')->with('message', 'store');
    }

    public function editPegawai($id)
    {
        $penugasan = Internal::find($id);

        $datas = [
            'dataPenugasanPegawai' => Internal::where('jenis', 'Penugasan Pegawai')->get(),
            'dataPenugasanPpnpn' => Internal::where('jenis', 'Penugasan PPNPN')->get(),
            'dataPegawai' => Pegawai::find($id),
            'jabatanPegawai' => JabatanPenugasanPegawai::get(),
            'golongan' => JabatanPenugasanGolongan::get(),
            'mulai_kegiatan' => $penugasan->tgl_kegiatan.' '.$penugasan->jam_mulai,
            'selesai_kegiatan' => $penugasan->tgl_selesai_kegiatan.' '.$penugasan->jam_selesai,
            'kota' => Kabupaten::get(),

        ];

        // dd($penugasan);
        return view('pages.admin.internal.editPenugasan', ['menu' => ''], compact('penugasan', 'datas'));
    }

    public function updatePegawai(Request $r)
    {
        // dd($r->mulai_kegiatan);
        // dd($r->all());
        // $r = $r->all();
        // dd($r['mulai_kegiatan']);
        $find = Internal::find($r->id);
        $r = $r->all();
        // dd($r);
        $mulai_kegiatan = explode(' ', $r['mulai_kegiatan']);
        $r['tgl_kegiatan'] = $mulai_kegiatan[0];
        $r['jam_mulai'] = $mulai_kegiatan[1];

        $selesai_kegiatan = explode(' ', $r['selesai_kegiatan']);
        $r['tgl_selesai_kegiatan'] = $selesai_kegiatan[0];
        $r['jam_selesai'] = $selesai_kegiatan[1];

        // dd($r->all());
        // dump($find);
        // dd($pegawai);
        $pegawai = Pegawai::where('no_ktp', $find->nik)->first();

        $find->update($r);
        // Internal::udpate($r->all());
        if (session('role') == 'pegawai') {
            return redirect()->route('pegawai.show', session('no_ktp'))->with('message', 'update');
        }

        return redirect()->route('internal.index.pegawai', $find->nik)->with('message', 'update');
    }

    public function updatePegawaiAll(Request $request)
    {
        dd($request->all());
        $pegawaiData = $request->input('pegawai');
        // Loop through each employee and update their data
        // foreach ($validatedData['pegawai'] as $index => $pegawai) {
        //     // Find employee by name or other unique identifier
        //     $employee = Employee::where('nama', $pegawai['nama'])->first();

        //     if ($employee) {
        //         // Update employee data
        //         $employee->hotel = $pegawai['hotel'];
        //         $employee->transport_pergi = $pegawai['transportPergi'];
        //         $employee->transport_pulang = $pegawai['transportPulang'];
        //         $employee->bill_penginapan = $pegawai['billPenginapan'];
        //         $employee->hari_1 = $pegawai['hari1'];
        //         $employee->hari_2 = $pegawai['hari2'];
        //         $employee->hari_3 = $pegawai['hari3'];
        //         $employee->save();
        //     }
        // }

        // Return response
        return response()->json(['success' => true]);
    }

    //  PEgawai PPNPN
    public function indexPpnpn($nik)
    {
        $pegawai = Pegawai::where('no_ktp', $nik)->first() ?? Pegawai::find($nik);

        // dd($pegawai);
        // dd($nik);
        $datas = [
            'pegawaiPpnpn' => Pegawai::where('id', $nik)->first(),
            'penugasanPpnpn' => Internal::where('jenis', 'Penugasan PPNPN')->where('nik', $pegawai->no_ktp)->get(),
            'getNama' => PegawaiPpnpn::where('nik', $nik)->first(),
            // 'getJenisPpnpn' => InternalPpnpn::where('jenis', 'Penugasan PPNPN')->first(),
            // 'getJenisPpnpn' => InternalPpnpn::where('jenis', 'Penugasan PPNPN')->where('nik', $nik)->first(),

        ];
        // dd($datas);

        return view('pages.admin.internal.indexPpnpn', ['menu' => ''], compact('datas'));
    }

    public function createPpnpn($id)
    {
        $pegawai = Pegawai::find($id);
        $datas = [
            'dataPenugasanPpnpn' => Internal::where('jenis', 'Penugasan PPNPN')->get(),
            'dataPegawai' => Pegawai::get(),
            'jabatanPegawai' => JabatanPenugasanPegawai::get(),
            'golongan' => JabatanPenugasanGolongan::get(),
            'kota' => Kabupaten::get(),
        ];

        // dd($pegawai);
        return view('pages.admin.penugasan.ppnpn', ['menu' => ''], compact('pegawai', 'datas'));
    }

    public function storePpnpn(Request $r)
    {

        // dd($r->mulai_kegiatan);
        // dd($r->all());
        $r = $r->all();

        // dd($r['mulai_kegiatan']);
        $mulai_kegiatan = explode(' ', $r['mulai_kegiatan']);
        $r['tgl_kegiatan'] = $mulai_kegiatan[0];
        $r['jam_mulai'] = $mulai_kegiatan[1];

        $selesai_kegiatan = explode(' ', $r['selesai_kegiatan']);
        $r['tgl_selesai_kegiatan'] = $selesai_kegiatan[0];
        $r['jam_selesai'] = $selesai_kegiatan[1];
        $r['tempat'] = $r['tempat'] ?? ($r['kota'] ?? null);
        $r['is_verif'] = $r['is_verif'] ?? 'sudah';

        // dd($r);

        Internal::create($r);

        // InternalPpnpn::create($r);
        return redirect()->route('internal.index')->with('message', 'store');
    }

    public function editPpnpn($id)
    {
        // $pegawai = PegawaiPpnpn::find($id);
        $penugasan = Internal::where('id', $id)->first();
        $datas = [
            'dataPegawai' => Pegawai::all(),
            'jabatanPegawai' => JabatanPenugasanPegawai::all(),
            'golongan' => JabatanPenugasanGolongan::all(),
            'kota' => Kabupaten::all(),
            'mulai_kegiatan' => $penugasan->tgl_kegiatan.' '.$penugasan->jam_mulai,
            'selesai_kegiatan' => $penugasan->tgl_selesai_kegiatan.' '.$penugasan->jam_selesai,
        ];

        // dd($datas);

        // if (!$pegawai) {
        //     return redirect()->route('internal.index')->with('error', 'Pegawai tidak ditemukan.');
        // }

        return view('pages.admin.internal.editPpnpn', ['menu' => ''], compact('datas', 'penugasan'));
    }

    public function updatePpnpn(Request $r)
    {
        $pegawai = Internal::find($r->id);

        // if (!$pegawai) {
        //     return redirect()->route('internal.index')->with('error', 'Pegawai tidak ditemukan.');
        // }
        // dd($pegawai);
        $r = $r->all();

        $mulai_kegiatan = explode(' ', $r['mulai_kegiatan']);
        $r['tgl_kegiatan'] = $mulai_kegiatan[0];
        $r['jam_mulai'] = $mulai_kegiatan[1];

        $selesai_kegiatan = explode(' ', $r['selesai_kegiatan']);
        $r['tgl_selesai_kegiatan'] = $selesai_kegiatan[0];
        $r['jam_selesai'] = $selesai_kegiatan[1];

        // dd($r);
        // Update data
        $pegawai->update($r);

        if (session('role') == 'pegawai') {
            return redirect()->route('pegawai.show', session('no_ktp'))->with('message', 'update');
        }

        return redirect()->route('internal.index')->with('message', 'update');
    }

    public function hapusLoka($id)
    {
        // dd($id);
        $data = Internal::find($id);
        $data->delete();

        return response()->json($data);
    }

    public function hapusPpnpn($id)
    {
        $data = Internal::find($id);
        if ($data) {
            $data->delete();

            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false]);
        }
    }

    public function create($jenis)
    {
        // dd($jenis);

        $datas = [
            'golongan' => JabatanPenugasanGolongan::get(),
            'jabatanPegawai' => JabatanPenugasanPegawai::get(),
            'jabatanPpnpn' => JabatanPenugasanPpnpn::get(),
            'kota' => Kabupaten::get(),
        ];

        if ($jenis == 'pendamping') {
            $title = 'Pendamping Lokakarya';

            return view('pages.admin.internal.createPendamping', ['menu' => 'internal', 'title' => $title, 'datas' => $datas]);
        }
        if ($jenis == 'penugasan ppnpn') {
            $title = 'Penugasan PPNPN';
        } else {
            $title = 'Penugasan Pegawai';
        }

        return view('pages.admin.internal.createPenugasan', ['menu' => 'internal', 'title' => $title, 'datas' => $datas]);
    }

    public function createUsername($fullName)
    {
        // Convert the full name to lowercase
        $lowercaseName = strtolower($fullName);

        // Remove any leading or trailing spaces
        $trimmedName = trim($lowercaseName);

        // Replace multiple spaces with a single space
        $singleSpacedName = preg_replace('/\s+/', ' ', $trimmedName);

        // Split the name into parts
        $nameParts = explode(' ', $singleSpacedName);

        // Concatenate the parts into a username
        $username = implode('', $nameParts);

        return $username;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $r = $request->all();
        // if ($r['jenis'] == 'Penugasan PPNPN') {
        //     $r['kegiatan'] = '-';
        //     $r['golongan'] = '-';
        //     $r['tempat'] = '-';
        //     $r['tgl_kegiatan'] = '2024-07-02';
        // }
        // dump($r['golongan']);
        if ($r['jenis'] == 'Pendamping Lokakarya') {
            $r['is_verif'] = 'sudah';
            Pendamping::create($r);

            return redirect()->route('internal.index')->with('message', 'store');
        }

        // $lowercaseName = strtolower($r['nama']);

        // // Remove any leading or trailing spaces
        // $trimmedName = trim($lowercaseName);

        // // Replace multiple spaces with a single space
        // $singleSpacedName = preg_replace('/\s+/', ' ', $trimmedName);

        // // Split the name into parts
        // $nameParts = explode(' ', $singleSpacedName);

        // // Concatenate the parts into a username
        // $username = implode('', $nameParts);

        // dd($r);
        $r['is_verif'] = 'sudah';
        $r['jabatan'] = $r['jabatan'] ?? '';

        Internal::create($r);

        return redirect()->route('internal.index')->with('message', 'store');
    }

    /**
     * Display the specified resource.
     */

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {

        $datas = [
            'golongan' => JabatanPenugasanGolongan::get(),
            'jabatanPegawai' => JabatanPenugasanPegawai::get(),
            'jabatanPpnpn' => JabatanPenugasanPpnpn::get(),
            'kota' => Kabupaten::get(),
            'penugasan' => Internal::find($id),
            'pendamping' => Pendamping::find($id),
        ];

        // dd($datas['penugasan']->jenis);

        if ($datas['penugasan']->jenis == 'Pendamping Lokakarya') {
            $title = 'Pendamping Lokakarya';

            return view('pages.admin.internal.editPendamping', ['menu' => 'internal', 'title' => $title, 'datas' => $datas]);
        }

        if ($datas['penugasan']->jenis == 'Penugasan PPNPN') {
            $title = 'Penugasan PPNPN';
        } else {
            $title = 'Penugasan Pegawai';
        }

        return view('pages.admin.internal.editPenugasan', ['menu' => 'internal', 'title' => $title, 'datas' => $datas]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        //
        $r = $request->all();

        if ($r['jenis'] == 'Pendamping Lokakarya') {
            // dd(true);
            $r['is_verif'] = 'sudah';

            // Pendamping::update($r);

            return redirect()->route('internal.index')->with('message', 'store');
        }

        // dd($r);
        $data = Internal::find($r['id']);
        $r['is_verif'] = 'sudah';
        $r['jabatan'] = $r['jabatan'] ?? '';

        $data->update($r);

        return redirect()->route('internal.index')->with('message', 'update');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // dd($id);
        $data = Internal::find($id);
        $data->delete();

        return response()->json($data);
    }

    public function hapusPenugasan($id)
    {
        // dd($id);
        $data = Internal::find($id);
        $data->delete();

        return response()->json($data);
    }

    // public function updatePegawai(Request $request)
    // {
    //     $r = $request->all();
    //     // dd($r);
    //     $data = Internal::find($r['id']);

    //     if ($r['jenis'] == 'Pendamping Lokakarya') {
    //         // dd(true);
    //         $r['is_verif'] = 'sudah';

    //         Pendamping::update($r);

    //         return redirect()->route('pegawai.show', session('no_ktp'))->with('message', 'store');
    //     }

    //     // dd($r);
    //     $r['is_verif'] = 'sudah';
    //     $r['jabatan'] = $r['jabatan'] ?? '';

    //     $data->update($r);
    //     return redirect()->route('pegawai.show', session('no_ktp'))->with('message', 'update');
    // }

    public function cariLokakarya(Request $r)
    {
        // dd($r->all());

        $input = $r->all();

        // Mencari data berdasarkan input yang diterima
        $data = Internal::where(function ($query) use ($input) {
            $query->where('nip', $input['nip']);
            if (! empty($input['nama'])) {
                $query->where('nama', 'like', '%'.$input['nama'].'%');
            }
            if (! empty($input['kegiatan'])) {
                $query->orWhere('kegiatan', 'like', '%'.$input['kegiatan'].'%');
            }
            if (! empty($input['kota'])) {
                $query->orWhere('kota', 'like', '%'.$input['kota'].'%');
            }
            if (! empty($input['hotel'])) {
                $query->orWhere('hotel', 'like', '%'.$input['hotel'].'%');
            }
        })->get();

        // dd($data);
        // Mengembalikan hasil pencarian
        return $data;
    }

    public function calendar(Request $request)
    {
        return view('calendar.index');
    }

    public function getCalendarData(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);

        $name = $request->input('name', '');
        $status = $request->input('status', '');
        // dd($status, ' ', $name);

        // Ambil daftar tanggal dalam bulan yang dimaksud
        $startDate = Carbon::createFromDate($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        $dates = [];
        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            $dates[] = ['date' => $date->format('Y-m-d')];
        }

        // Query dasar dengan LEFT JOIN untuk memastikan semua pegawai ditampilkan
        // $query = "SELECT pegawais.id, pegawais.nama_lengkap, pegawais.jenis_pegawai,
        //              GROUP_CONCAT(internals.tgl_kegiatan ORDER BY internals.tgl_kegiatan SEPARATOR ', ') AS tgl_kegiatan,
        //              GROUP_CONCAT(internals.tgl_selesai_kegiatan ORDER BY internals.tgl_selesai_kegiatan SEPARATOR ', ') AS tgl_selesai_kegiatan
        //       FROM pegawais
        //       LEFT JOIN internals ON pegawais.no_ktp = internals.nik
        //       AND YEAR(internals.tgl_kegiatan) = ? AND MONTH(internals.tgl_kegiatan) = ?";
        // Query untuk mendapatkan data pegawai
        // Query untuk mengambil data pegawai
        // Query untuk mengambil data pegawai
        $employeeQuery = 'SELECT id, nama_lengkap, jenis_pegawai, no_ktp FROM pegawais';
        $employeeBindings = [];

        // Tambahkan pencarian berdasarkan nama
        if ($name) {
            $employeeQuery .= ' WHERE nama_lengkap LIKE ?';
            $employeeBindings[] = "%$name%";
        }

        // Tambahkan filter berdasarkan status pegawai
        if ($status) {
            $employeeQuery .= $name ? ' AND' : ' WHERE';
            $employeeQuery .= ' jenis_pegawai = ?';
            $employeeBindings[] = $status;
        }

        // Jalankan query untuk mengambil data pegawai
        $employees = DB::select($employeeQuery, $employeeBindings);

        // Query untuk mengambil data penugasan dari tabel internals
        $internalQuery = 'SELECT kegiatan, jenis, deskripsi, nama, nik, tgl_kegiatan, tgl_selesai_kegiatan
                      FROM internals
                      WHERE YEAR(tgl_kegiatan) = ? AND MONTH(tgl_kegiatan) = ?';
        $internalBindings = [$year, $month];

        // Jalankan query untuk mengambil data penugasan
        $internals = DB::select($internalQuery, $internalBindings);

        $employeeData = collect($employees)->map(function ($employee) use ($internals) {
            $assignments = collect($internals)->filter(function ($internal) use ($employee) {
                return $internal->nik === $employee->no_ktp && $internal->nama === $employee->nama_lengkap;
            })->map(function ($internal) {
                // dd($internal);
                return [
                    'start' => $internal->tgl_kegiatan,
                    'end' => $internal->tgl_selesai_kegiatan,
                    'title' => $internal->kegiatan,
                    'type' => $internal->jenis,
                    'description' => $internal->deskripsi,
                ];
            });

            return [
                'name' => $employee->nama_lengkap,
                'status' => $employee->jenis_pegawai,
                'assignments' => $assignments->values()->toArray(),
            ];
        });
        // dd(1);

        // [
        //     'name' => $employee->nama_lengkap,
        //     'status' => $employee->jenis_pegawai,
        //     'assignments' => $assignments,
        // ];
        // dd($employeeData);
        // dd($employees);
        // Dapatkan nama bulan
        $monthName = Carbon::createFromDate($year, $month, 1)->format('F Y');

        // Kembalikan hasil dalam format JSON
        return response()->json([
            'dates' => $dates,
            'month' => $month,
            'year' => $year,
            'employees' => $employeeData,
            'monthName' => $monthName,
        ]);
    }
}
