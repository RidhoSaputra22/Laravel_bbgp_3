<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Agenda;
use App\Models\Artikel;
use App\Models\Berita;
use App\Models\Guru;
use App\Models\Internal;
use App\Models\Jabatan;
use App\Models\JabatanKependidikan;
use App\Models\JabatanPendidik;
use App\Models\JabatanStakeHolder;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Kegiatan;
use App\Models\Kepegawaian;
use App\Models\Pegawai;
use App\Models\Pendamping;
use App\Models\Pendidikan;
use App\Models\PesertaKegiatan;
use App\Models\SatuanPendidikan;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $datas = array(
            'berita' => Berita::orderByDesc('id')->skip(0)->take(10)->get(),
            'agenda' => Agenda::orderByDesc('id')->skip(0)->take(10)->get(),
            'artikel' => Artikel::orderByDesc('id')->skip(0)->take(10)->get(),
            'no_wa' => '6285255376376',
            'api_key' => env('YOUTUBE_API_KEY', ''),
            'channel_id' => env('CHANNEL_ID', '')
        );
        // return view('pages.user.index', ['menu' => 'profil']);
        return view('pages.landing.index', ['menu' => 'profil'], compact('datas'));
    }

    public function kontak()
    {
        return view('pages.landing.kontak', ['menu' => 'kontak']);
        // return view('pages.user.kontak', ['menu' => 'kontak']);
    }


    public function detail($jenis, $id)
    {
        // dd($jenis);
        if ($jenis == 'berita') {
            $data = Berita::find($id);
            $latest_post = Berita::orderByDesc('id')->skip(0)->take(5)->get();
        } else if ($jenis == 'artikel') {
            $data = Artikel::find($id);
            $latest_post = Artikel::orderByDesc('id')->skip(0)->take(5)->get();
        } else if ($jenis == 'agenda') {
            $data = Agenda::find($id);
            $latest_post = Agenda::orderByDesc('id')->skip(0)->take(5)->get();
            return view('pages.landing.detail-agenda', [
                'menu' => 'detail post',
                'data' => $data,
                'jenis' => $jenis,
                'latest_post' => $latest_post
            ]);
        }

        return view('pages.landing.detail-post', [
            'menu' => 'detail post',
            'data' => $data,
            'jenis' => $jenis,
            'latest_post' => $latest_post
        ]);
        // return view('pages.user.kontak', ['menu' => 'kontak']);
    }


    public function guru(Request $request)
    {
        if ($request->ajax()) {
            $data = Guru::select('npsn_sekolah', 'nama_lengkap', 'status_kepegawaian', 'eksternal_jabatan', 'kategori_jabatan', 'jenis_jabatan', 'tugas_jabatan', 'latar_jabatan')
                ->when($request->kabupaten, function ($query) use ($request) {
                    return $query->where('kabupaten', $request->kabupaten);
                })
                ->when($request->nik, function ($query) use ($request) {
                    return $query->where('nik', 'like', '%' . $request->nik . '%');
                });

            $totalRecords = $data->count();
            $filteredRecords = $data->count();

            $data = $data->skip($request->start)->take($request->length)->get();

            return response()->json([
                'draw' => $request->get('draw'),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
        }

        $status = [
            's_jabPendidik' => JabatanPendidik::get(),
            's_jabKependidikan' => JabatanKependidikan::get(),
            's_jabStakeholder' => JabatanStakeHolder::get(),
            's_kabupaten' => Kabupaten::get(),
            's_jabKategori' => ['GP (Guru Penggerak)', 'NoN GP (Guru Penggerak)'],
            's_jabKategoriPengawas' => ['Sertifikat GP (Guru Penggerak)', 'Diklat Cawas', 'Lainnya'],
            's_jabKategoriKepsek' => ['Sertifikat GP (Guru Penggerak)', 'Diklat Cakep', 'Lainnya'],
            's_jabTugas' => ['GP (Guru Penggerak)', 'PP (Pengajar Praktik)', 'Fasil (Fasilitator)', 'Instruktur'],
        ];

        return view('pages.landing.eksternal.index', ['menu' => 'data', 'status' => $status]);
    }


    public function dataguru(Request $request)
    {
        $query = Guru::with('sekolah')
            ->where('is_verif', 'sudah');

        // Total records before filtering
        $totalRecords = Guru::where('is_verif', 'sudah')->count();

        // Handle specific filters from the form
        if ($request->kabupaten) {
            $query->where('kabupaten', $request->kabupaten);
        }

        if ($request->nik) {
            $query->where('no_ktp', 'like', '%' . $request->nik . '%');
        }

        // Handle DataTables search
        if ($request->has('search') && !empty($request->search['value'])) {
            $searchValue = $request->search['value'];
            $query->where(function($q) use ($searchValue) {
                $q->where('nama_lengkap', 'like', '%' . $searchValue . '%')
                  ->orWhere('no_ktp', 'like', '%' . $searchValue . '%')
                  ->orWhere('npsn_sekolah', 'like', '%' . $searchValue . '%')
                  ->orWhere('eksternal_jabatan', 'like', '%' . $searchValue . '%')
                  ->orWhere('jenis_jabatan', 'like', '%' . $searchValue . '%');
            });
        }

        // Handle Column-specific search (from applySearch function)
        if ($request->has('columns')) {
            // Column 2: Nama Lengkap
            if (!empty($request->columns[2]['search']['value'])) {
                $query->where('nama_lengkap', 'like', '%' . $request->columns[2]['search']['value'] . '%');
            }
            // Column 4: Ketenagaan (eksternal_jabatan)
            if (!empty($request->columns[4]['search']['value'])) {
                $query->where('eksternal_jabatan', $request->columns[4]['search']['value']);
            }
            // Column 6: Jenis Jabatan
            if (!empty($request->columns[6]['search']['value'])) {
                $query->where('jenis_jabatan', $request->columns[6]['search']['value']);
            }
        }

        // Count filtered records BEFORE paging
        $filteredRecords = $query->count();

        // Apply paging and ordering
        $data = $query->orderBy('id', 'DESC')
            ->skip($request->get('start', 0))
            ->take($request->get('length', 10))
            ->get();

        // Format data for DataTables
        return response()->json([
            'draw' => (int)$request->get('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data->map(function ($item, $index) use ($request) {
                return [
                    'DT_RowIndex' => (int)$request->get('start', 0) + $index + 1,
                    'npsn_sekolah' => $item->npsn_sekolah . '<br>' . ($item->sekolah->nama_sekolah ?? ''),
                    'nama_lengkap' => $item->nama_lengkap,
                    'status_kepegawaian' => $item->status_kepegawaian,
                    'eksternal_jabatan' => $item->eksternal_jabatan,
                    'kategori_jabatan' => $item->kategori_jabatan,
                    'jenis_jabatan' => $item->jenis_jabatan,
                    'tugas_jabatan' => $item->tugas_jabatan,
                    'latar_jabatan' => $item->latar_jabatan ?? 'tidak ada',
                    'action' => '<button class="btn btn-info" onclick="showDetail(' . $item->id . ')">Detail</button>'
                ];
            })
        ]);
    }


    public function pegawai()
    {
        // $data = Pegawai::where('is_verif', 'sudah')->orderBy('id', 'DESC')->get();
        $kota = Kabupaten::get();
        // $data = Internal::get();
        $data = array(

            'dataPenugasanPegawai' => Internal::where('jenis', 'Penugasan Pegawai')->get(),
            'dataPenugasanPpnpn' => Internal::where('jenis', 'Penugasan PPNPN')->get(),
        );
        $dataPendamping = Pendamping::get();
        // $merge = $data->merge($dataPendamping);
        return view('pages.landing.internal.index', ['menu' => 'data', 'datas' => $data, 'dataPendamping' => $dataPendamping]);
        // return view('pages.user.pegawai', ['menu' => 'pegawai', 'datas' => $data, 'dataPendamping' => $dataPendamping]);
    }
    public function form_pegawai()
    {
        $data = Pegawai::get();
        return view('pages.landing.eksternal.form', ['menu' => 'data']);
        // return view('pages.user.formPegawai', ['menu' => 'pegawai']);
    }
    public function daftar_pegawai(Request $request)
    {
        $r = $request->all();
        // $foto = $request->file('pas_foto');
        // $ext = $foto->getClientOriginalExtension();
        // // $r['pas_foto'] = $request->file('pas_foto');

        // $nameFoto = date('Y-m-d_H-i-s_') . $r['no_ktp'] . "." . $ext;
        // $destinationPath = public_path('upload/pegawai');

        // $foto->move($destinationPath, $nameFoto);

        // $fileUrl = asset('upload/pegawai/' . $nameFoto);

        // $r['pas_foto'] = $nameFoto;
        // dd($r);
        $findNik = Guru::where('no_ktp', $r['no_ktp'])->first();

        if ($findNik != null)
            return redirect()->route('user.pegawai')->with('message', 'nik sudah ada');

        $r['pas_foto'] = '';
        $r['status'] = 'Belum Kawin';
        $r['alamat_satuan'] = '';
        $r['eksternal_jabatan'] = $r['jenisJabatan'];
        $r['jenis_jabatan'] = $r['jabJenis'];
        $r['kategori_jabatan'] = $r['jabKategori'];
        $r['tugas_jabatan'] = $r['jabTugas'];
        $r['is_verif'] = 'belum';

        Pegawai::create($r);

        return redirect()->route('user.pegawai')->with('message', 'user daftar');
    }
    public function form_guru($jenis)
    {
        $datas = array(
            's_kepegawaian' => Kepegawaian::get(),
            's_kependidikan' => SatuanPendidikan::get(),
            's_gelar' => Pendidikan::get(),
            's_jabatan' => Jabatan::get(),
            's_kabupaten' => Kabupaten::get(),
            's_kecamatan' => Kecamatan::get(),
            's_sekolah' => [], // Schools are loaded via AJAX in the view
            's_jabPendidik' => JabatanPendidik::get(),
            's_jabKependidikan' => JabatanKependidikan::get(),
            's_jabStakeholder' => JabatanStakeHolder::get(),
            's_jabKategori' => ['GP (Guru Penggerak)', 'NoN GP (Guru Penggerak)'],
            's_jabKategoriPengawas' => ['Sertifikat GP (Guru Penggerak)', 'Diklat Cawas', 'Lainnya'],
            's_jabKategoriKepsek' => ['Sertifikat GP (Guru Penggerak)', 'Diklat Cakep', 'Lainnya'],
            's_jabTugas' => ['GP (Guru Penggerak)', 'PP (Pengajar Praktik)', 'Fasil (Fasilitator)', 'Instruktur'],

        );
        $data = Guru::get();
        return view('pages.landing.eksternal.form', ['menu' => 'guru', 'status' => $datas, 'jenis' => $jenis]);
        // return view('pages.user.formGuru', ['menu' => 'guru', 'status' => $datas, 'jenis' => $jenis]);
    }
    public function daftar_guru(Request $request)
    {
        $r = $request->all();
        // $foto = $request->file('pas_foto');
        // $ext = $foto->getClientOriginalExtension();
        // // $r['pas_foto'] = $request->file('pas_foto');

        // $nameFoto = date('Y-m-d_H-i-s_') . $r['no_ktp'] . "." . $ext;
        // $destinationPath = public_path('upload/guru');

        // $foto->move($destinationPath, $nameFoto);

        // $fileUrl = asset('upload/guru/' . $nameFoto);

        // $r['pas_foto'] = $nameFoto;
        $getNik = Guru::where('no_ktp', $r['no_ktp'])->first();
        // dd($getNik);
        if ($getNik == null) {
            $r['jabatan'] = '';
            $r['pas_foto'] = '';
            $r['status'] = 'Belum Kawin';
            $r['alamat_satuan'] = '';
            $r['eksternal_jabatan'] = $r['jenisJabatan'] ?? '';

            if ($r['jabJenis'] == 'Lainnya' && $r['jabLainnya'] != null) {
                $r['jabJenis'] = $r['jabLainnya'];
                $r['jenis_jabatan'] = $r['jabJenis'];
            } else {
                $r['jenis_jabatan'] = $r['jabJenis'];
            }

            if ($r['kabupaten'] == 'Tidak ada' && $r['diluarKab'] != null) {
                $r['kabupaten'] = $r['diluarKab'];
            }

            $r['kategori_jabatan'] = $r['jabKategori'] ?? '';
            $r['tugas_jabatan'] = $r['jabTugas'] ?? '';
            $r['latar_jabatan'] = $r['jabLatar'] ?? '';
            $r['is_verif'] = 'sudah';

            $role = strtolower($r['jenisJabatan']);

            $user = strtolower(str_replace(' ', '', $r['nama_lengkap']));
            // dd($role);
            $reg['name'] = $r['nama_lengkap'];
            $reg['username'] = $user;
            $reg['no_ktp'] = (string) $r['no_ktp'];
            $reg['role'] = $role;
            $reg['password'] = bcrypt('12345');

            // dump($r);
            // dd($reg);



            User::create($reg);
            Admin::create($reg);
            Guru::create($r);

            // akun login



            return redirect()->route('user.guru')->with('message', 'user daftar');
        } else {
            return redirect()->route('user.guru')->with('message', 'nik daftar');
        }
    }

    public function getPenugasanDetail(Request $request)
    {
        $pesertaId = $request->input('id');
        $peserta = Internal::find($pesertaId);

        return response()->json($peserta);
    }

    public function getPenugasanAll()
    {
        $data = array(

            'dataPenugasanPegawai' => Internal::where('jenis', 'Penugasan Pegawai')->get(),
            'dataPenugasanPpnpn' => Internal::where('jenis', 'Penugasan PPNPN')->get(),
        );

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function getPenugasanDetailLoka(Request $request)
    {
        $pesertaId = $request->input('id');
        $peserta = Pendamping::find($pesertaId);

        return response()->json($peserta);
    }

    public function getPenugasanDetailEksternal(Request $request)
    {
        $pesertaId = $request->input('id');
        $peserta = Guru::find($pesertaId);

        return response()->json([
            'data' => $peserta,
            'sekolah' => $peserta->sekolah
        ]);
    }

    public function statistik()
    {
        // Data untuk Statistik Eksternal
        $datas = array(
            'GP' => Guru::where('kategori_jabatan', 'GP (Guru Penggerak)')->count(),
            'nonGP' => Guru::where('kategori_jabatan', 'NoN GP (Guru Penggerak)')->count(),
        );

        // Ambil daftar kegiatan untuk filter
        $activities = Kegiatan::all();

        return view('pages.landing.statistik.index', [
            'menu' => 'statistik',
            'datas' => $datas,
            'activities' => $activities,
        ]);
    }

    // API endpoint untuk mendapatkan statistik kegiatan berdasarkan bulan
    public function getMonthStatistics($month)
    {
        $jumlah_kegiatan = Kegiatan::whereMonth('tgl_kegiatan', $month)->count();
        // \Log::info('Fetching statistics for month: ' . $month);
        return response()->json(['jumlah_kegiatan' => $jumlah_kegiatan]);
    }

    // API endpoint untuk mendapatkan daftar kegiatan berdasarkan bulan
    public function getActivitiesByMonth($month)
    {
        $activities = Kegiatan::whereMonth('tgl_kegiatan', $month)->get();

        return response()->json($activities);
    }

    // API endpoint untuk mendapatkan statistik kegiatan berdasarkan ID dan jenis partisipasi
    public function getActivityStatistics($activityId, $participantType)
    {
        $jumlah = PesertaKegiatan::where('id_kegiatan', $activityId)
            ->where('status_keikutpesertaan', $participantType)
            ->count();

        return response()->json(['jumlah' => $jumlah]);
    }

    public function analisisPelatihan()
    {
        return view('pages.landing.analisisPelatihan.index', [
            'menu' => 'analisisPelatihan',
        ]);
    }

    public function analisisSLB()
    {
        return view('pages.landing.analisisSLB.index', [
            'menu' => 'analisisSLB',
        ]);
    }

    public function monitoring()
    {
        return view('pages.landing.monitoringKegiatan.index', [
            'menu' => 'monitoring',
        ]);
    }

    public function pengaduan()
    {
        return view('pages.landing.pengaduan.index', [
            'menu' => 'pengaduan',
        ]);
    }

    public function buletin()
    {
        return view('pages.landing.buletin-diksi.index', [
            'menu' => 'buletin-diksi',
        ]);
    }

    public function labVirtual()
    {
        return view('pages.landing.lab-virtual.index', [
            'menu' => 'lab-virtual',
        ]);
    }

    public function cari(Request $request)
    {
        $search = Guru::query();
        if ($request->kabupaten) {
            $search->where('kabupaten', $request->kabupaten);
        }
        if ($request->nik) {
            $search->where('no_ktp', 'like', '%' . $request->nik . '%');
        }
        $search->orderBy('created_at', 'desc');

        $result = $search->get();

        return response()->json([
            'status' => true,
            'data'  => $result
        ]);
    }
}
