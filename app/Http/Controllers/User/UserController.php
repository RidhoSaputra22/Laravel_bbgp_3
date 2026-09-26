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
use Illuminate\Validation\Rule;

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
        return view('pages.landing.index', ['menu' => 'profil'], compact('datas'));
    }

    public function kontak()
    {
        return view('pages.landing.kontak', ['menu' => 'kontak']);
    }


    public function detail($jenis, $id)
    {
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
        $kota = Kabupaten::get();
        $data = array(

            'dataPenugasanPegawai' => Internal::where('jenis', 'Penugasan Pegawai')->get(),
            'dataPenugasanPpnpn' => Internal::where('jenis', 'Penugasan PPNPN')->get(),
        );
        $dataPendamping = Pendamping::get();
        return view('pages.landing.internal.index', ['menu' => 'data', 'datas' => $data, 'dataPendamping' => $dataPendamping]);
    }
    public function form_pegawai()
    {
        $data = Pegawai::get();
        return view('pages.landing.eksternal.form', ['menu' => 'data']);
    }
    public function daftar_pegawai(Request $request)
    {
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'no_ktp' => 'required|string|max:50|regex:/^[A-Za-z0-9._-]+$/',
            'nip' => 'required|string|max:50',
            'tempat_lahir' => 'required|string|max:255',
            'tgl_lahir' => 'required|date',
            'gender' => 'required|in:Laki-laki,Perempuan',
            'status_kepegawaian' => 'required|string|max:100',
            'agama' => 'required|in:Islam,Kristen,Katolik,Hindu,Budha,Konghucu',
            'pendidikan' => 'required|string|max:100',
            'kabupaten' => 'required|string|max:255',
            'diluarKab' => 'nullable|string|max:255',
            'satuan_pendidikan' => 'required|string|max:255',
            'alamat_satuan' => 'nullable|string|max:1000',
            'alamat_rumah' => 'required|string|max:1000',
            'no_hp' => 'required|string|max:30',
            'no_wa' => 'required|string|max:30',
            'jabatan' => 'required|string|max:255',
            'jenisJabatan' => 'nullable|string|max:100',
            'jenis_bank' => 'required|string|max:100',
            'no_rek' => 'required|string|max:50',
        ]);

        $findNik = Guru::where('no_ktp', $validated['no_ktp'])->first()
            ?? Pegawai::where('no_ktp', $validated['no_ktp'])->first();

        if ($findNik != null)
            return redirect()->route('user.pegawai')->with('message', 'nik sudah ada');

        $kabupaten = $validated['kabupaten'] === 'Tidak ada'
            ? ($validated['diluarKab'] ?: 'Tidak ada')
            : $validated['kabupaten'];

        Pegawai::create([
            'nama_lengkap' => $validated['nama_lengkap'],
            'email' => $validated['email'],
            'no_ktp' => $validated['no_ktp'],
            'nip' => $validated['nip'],
            'tempat_lahir' => $validated['tempat_lahir'],
            'tgl_lahir' => $validated['tgl_lahir'],
            'gender' => $validated['gender'],
            'jabatan' => $validated['jabatan'],
            'jenis_pegawai' => $validated['jenisJabatan'] ?? null,
            'status' => 'Belum Kawin',
            'status_kepegawaian' => $validated['status_kepegawaian'],
            'agama' => $validated['agama'],
            'pendidikan' => $validated['pendidikan'],
            'kabupaten' => $kabupaten,
            'satuan_pendidikan' => $validated['satuan_pendidikan'],
            'alamat_satuan' => $validated['alamat_satuan'] ?? '',
            'alamat_rumah' => $validated['alamat_rumah'],
            'no_hp' => $validated['no_hp'],
            'no_wa' => $validated['no_wa'],
            'pas_foto' => '',
            'jenis_bank' => $validated['jenis_bank'],
            'no_rek' => $validated['no_rek'],
            'is_verif' => 'belum',
        ]);

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
    }
    public function daftar_guru(Request $request)
    {
        $jabatanTable = match ($request->input('jenisJabatan')) {
            'Tenaga Pendidik' => 'jabatan_pendidiks',
            'Tenaga Kependidikan' => 'jabatan_kependidikans',
            default => 'jabatan_stake_holders',
        };

        $r = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'no_ktp' => 'required|digits:16',
            'jenisJabatan' => 'required|string|in:Tenaga Pendidik,Tenaga Kependidikan,Stakeholder',
            'jabJenis' => ['required', 'string', 'max:255', Rule::exists($jabatanTable, 'name')],
            'jabLainnya' => 'nullable|string|max:255|required_if:jabJenis,Lainnya',
            'kabupaten' => [
                'required',
                'string',
                'max:255',
                Rule::in(array_merge(['Tidak ada'], Kabupaten::query()->pluck('name')->all())),
            ],
            'diluarKab' => 'nullable|string|max:255|required_if:kabupaten,Tidak ada',
            'jabKategori' => 'nullable|string|max:255',
            'jabTugas' => 'nullable|string|max:255',
            'jabLatar' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'nip' => 'required|digits:18',
            'npsn_sekolah' => 'nullable|digits:8',
            'tempat_lahir' => 'required|string|max:255',
            'tgl_lahir' => 'required|date|before:today',
            'gender' => 'required|string|in:Laki-laki,Perempuan',
            'status_kepegawaian' => 'required|exists:kepegawaians,name',
            'agama' => 'required|string|in:Islam,Kristen,Katolik,Hindu,Buddha',
            'pendidikan' => 'required|exists:pendidikans,name',
            'satuan_pendidikan' => 'required|exists:satuan_pendidikans,name',
            'alamat_satuan' => 'nullable|string|max:1000',
            'alamat_rumah' => 'required|string|max:1000',
            'no_hp' => 'required|digits_between:10,15',
            'no_wa' => 'required|digits_between:10,15',
            'no_rek' => 'required|digits_between:1,30',
            'jenis_bank' => [
                'required',
                Rule::in([
                    'Bank BCA',
                    'Bank BRI',
                    'Bank BNI',
                    'Bank BTN',
                    'Bank Mandiri',
                    'Bank Syariah Indonesia',
                    'Bank SulSelBar',
                    'Tidak ada',
                ]),
            ],
            'npwp' => 'required|digits_between:15,16',
            'nuptk' => $request->input('jenisJabatan') === 'Stakeholder'
                ? 'nullable|digits:16'
                : 'required|digits:16',
        ]);




        $getNik = Guru::where('no_ktp', $r['no_ktp'])->first();
        if ($getNik == null) {
            $r['jabatan'] = '';
            $r['pas_foto'] = '';
            $r['status'] = 'Belum Kawin';
            $r['alamat_satuan'] = $r['alamat_satuan'] ?? '';
            $r['eksternal_jabatan'] = $r['jenisJabatan'] ?? '';
            $r['agama'] = $r['agama'] === 'Buddha' ? 'Budha' : $r['agama'];

            if (($r['jabJenis'] ?? null) == 'Lainnya' && ($r['jabLainnya'] ?? null) != null) {
                $r['jabJenis'] = $r['jabLainnya'];
                $r['jenis_jabatan'] = $r['jabJenis'] ?? '';
            } else {
                $r['jenis_jabatan'] = $r['jabJenis'];
            }

            if (($r['kabupaten'] ?? null) == 'Tidak ada' && ($r['diluarKab'] ?? null) != null) {
                $r['kabupaten'] = $r['diluarKab'];
            }

            $r['kategori_jabatan'] = $r['jabKategori'] ?? '';
            $r['tugas_jabatan'] = $r['jabTugas'] ?? '';
            $r['latar_jabatan'] = $r['jabLatar'] ?? '';
            $r['is_verif'] = 'sudah';

            $role = match ($r['jenisJabatan']) {
                'Tenaga Pendidik' => 'tenaga pendidik',
                'Tenaga Kependidikan' => 'tenaga kependidikan',
                default => 'stakeholder',
            };

            $user = (string) $r['no_ktp'];
            $passwordPlain = '12345';
            $r['username'] = $user;

            $reg['name'] = $r['nama_lengkap'];
            $reg['username'] = $user;
            $reg['no_ktp'] = (string) $r['no_ktp'];
            $reg['role'] = $role;
            $reg['password'] = bcrypt($passwordPlain);




            User::create($reg);
            Admin::create($reg);
            Guru::create($r);

            // akun login



            return redirect()->route('user.guru')->with([
                'message' => 'user daftar',
                'registration_credentials' => [
                    'username' => $user,
                    'password' => $passwordPlain,
                ],
            ]);
        } else {
            return redirect()
                ->route('user.form_guru', $r['jenisJabatan'])
                ->withInput()
                ->withErrors([
                    'no_ktp' => __('validation.custom.no_ktp.duplicate', [
                        'attribute' => __('validation.attributes.no_ktp'),
                    ]),
                ]);
        }
    }

    public function getPenugasanDetail(Request $request)
    {
        $pesertaId = $request->integer('id');
        $peserta = Internal::select([
            'id', 'nama', 'nip', 'kota', 'kegiatan', 'tempat',
            'tgl_kegiatan', 'tgl_selesai_kegiatan', 'jam_mulai', 'jam_selesai',
        ])->findOrFail($pesertaId);

        return response()->json($peserta);
    }

    public function getPenugasanAll()
    {
        $data = array(

            'dataPenugasanPegawai' => Internal::select([
                'id', 'nama', 'nip', 'kota', 'kegiatan', 'tempat',
                'tgl_kegiatan', 'tgl_selesai_kegiatan', 'jam_mulai', 'jam_selesai',
            ])->where('jenis', 'Penugasan Pegawai')->get(),
            'dataPenugasanPpnpn' => Internal::select([
                'id', 'nama', 'nip', 'kota', 'kegiatan', 'tempat',
                'tgl_kegiatan', 'tgl_selesai_kegiatan', 'jam_mulai', 'jam_selesai',
            ])->where('jenis', 'Penugasan PPNPN')->get(),
        );

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function getPenugasanDetailLoka(Request $request)
    {
        $pesertaId = $request->integer('id');
        $peserta = Pendamping::select([
            'id', 'nama', 'kota', 'hotel', 'transport_pergi',
            'transport_pulang', 'hari_1', 'hari_2', 'hari_3',
        ])->findOrFail($pesertaId);

        return response()->json($peserta);
    }

    public function getPenugasanDetailEksternal(Request $request)
    {
        $pesertaId = $request->integer('id');
        $peserta = Guru::select([
            'id', 'nama_lengkap', 'gender', 'status_kepegawaian', 'kabupaten',
            'npsn_sekolah', 'eksternal_jabatan', 'jenis_jabatan',
        ])->findOrFail($pesertaId);

        return response()->json([
            'data' => $peserta,
            'sekolah' => $peserta->sekolah?->only(['npsn_sekolah', 'nama_sekolah', 'kecamatan', 'kabupaten']),
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
