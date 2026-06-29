<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\InternalPpnpn;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\Internal;
use App\Models\Kegiatan;
use App\Models\PesertaKegiatan;
use App\Models\User;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        // tenaga pendidik
        $tenaga_pendidik = array(
            'guruGP' => Guru::select('id')->where('eksternal_jabatan', 'Tenaga Pendidik')->where('jenis_jabatan', 'Guru')->where('kategori_jabatan', 'GP (Guru Penggerak)')->get()->count(),
            'guruTGP' => Guru::select('id')->where('eksternal_jabatan', 'Tenaga Pendidik')->where('jenis_jabatan', 'Guru')->where('kategori_jabatan', 'GP (Guru Penggerak)')->where('tugas_jabatan', 'GP (Guru Penggerak)')->get()->count(),
            'guruPP' => Guru::select('id')->where('eksternal_jabatan', 'Tenaga Pendidik')->where('jenis_jabatan', 'Guru')->where('kategori_jabatan', 'GP (Guru Penggerak)')->where('tugas_jabatan', 'PP (Pengajar Praktik)')->get()->count(),
            'guruFasil' => Guru::select('id')->where('eksternal_jabatan', 'Tenaga Pendidik')->where('jenis_jabatan', 'Guru')->where('kategori_jabatan', 'GP (Guru Penggerak)')->where('tugas_jabatan', 'Fasil (Fasilitator)')->get()->count(),
            'guruInstruktur' => Guru::select('id')->where('eksternal_jabatan', 'Tenaga Pendidik')->where('jenis_jabatan', 'Guru')->where('kategori_jabatan', 'GP (Guru Penggerak)')->where('tugas_jabatan', 'Instruktur')->get()->count(),
            'guruNonGP' => Guru::select('id')->where('eksternal_jabatan', 'Tenaga Pendidik')->where('jenis_jabatan', 'Guru')->where('kategori_jabatan', 'NoN GP (Guru Penggerak)')->get()->count(),

            'konselorGP' => Guru::select('id')->where('eksternal_jabatan', 'Tenaga Pendidik')->where('jenis_jabatan', 'Konselor')->where('kategori_jabatan', 'GP (Guru Penggerak)')->get()->count(),
            'konselorTGP' => Guru::select('id')->where('eksternal_jabatan', 'Tenaga Pendidik')->where('jenis_jabatan', 'Konselor')->where('kategori_jabatan', 'GP (Guru Penggerak)')->where('tugas_jabatan', 'GP (Guru Penggerak)')->get()->count(),
            'konselorPP' => Guru::select('id')->where('eksternal_jabatan', 'Tenaga Pendidik')->where('jenis_jabatan', 'Konselor')->where('kategori_jabatan', 'GP (Guru Penggerak)')->where('tugas_jabatan', 'PP (Pengajar Praktik)')->get()->count(),
            'konselorFasil' => Guru::select('id')->where('eksternal_jabatan', 'Tenaga Pendidik')->where('jenis_jabatan', 'Konselor')->where('kategori_jabatan', 'GP (Guru Penggerak)')->where('tugas_jabatan', 'Fasil (Fasilitator)')->get()->count(),
            'konselorInstruktur' => Guru::select('id')->where('eksternal_jabatan', 'Tenaga Pendidik')->where('jenis_jabatan', 'Konselor')->where('kategori_jabatan', 'GP (Guru Penggerak)')->where('tugas_jabatan', 'Instruktur')->get()->count(),
            'konselorNonGP' => Guru::select('id')->where('eksternal_jabatan', 'Tenaga Pendidik')->where('jenis_jabatan', 'Konselor')->where('kategori_jabatan', 'NoN GP (Guru Penggerak)')->get()->count(),

            'dosen' => Guru::select('id')->where('eksternal_jabatan', 'Tenaga Pendidik')->where('jenis_jabatan', 'Dosen')->get()->count(),
            'tutor' => Guru::select('id')->where('eksternal_jabatan', 'Tenaga Pendidik')->where('jenis_jabatan', 'Tutor')->get()->count(),
            'fasilitator' => Guru::select('id')->where('eksternal_jabatan', 'Tenaga Pendidik')->where('jenis_jabatan', 'Fasilitator')->get()->count(),
            'pamong' => Guru::select('id')->where('eksternal_jabatan', 'Tenaga Pendidik')->where('jenis_jabatan', 'Pamong Belajar')->get()->count(),
            'widya' => Guru::select('id')->where('eksternal_jabatan', 'Tenaga Pendidik')->where('jenis_jabatan', 'Widya Iswara')->get()->count(),
            'instruktur' => Guru::select('id')->where('eksternal_jabatan', 'Tenaga Pendidik')->where('jenis_jabatan', 'Instruktur')->get()->count(),




        );

        $tenaga_kependidik = array(
            'pengawasGP' => Guru::select('id')->where('eksternal_jabatan', 'Tenaga Kependidikan')->where('jenis_jabatan', 'Pengawas')->where('kategori_jabatan', 'GP (Guru Penggerak)')->get()->count(),

            'pengawasLGP' => Guru::select('id')->where('eksternal_jabatan', 'Tenaga Kependidikan')->where('jenis_jabatan', 'Pengawas')->where('kategori_jabatan', 'GP (Guru Penggerak)')->where('latar_jabatan', 'Sertifikat GP (Guru Penggerak)')->get()->count(),

            'pengawasLDC' => Guru::select('id')->where('eksternal_jabatan', 'Tenaga Kependidikan')->where('jenis_jabatan', 'Pengawas')->where('kategori_jabatan', 'GP (Guru Penggerak)')->where('latar_jabatan', 'Diklat Cawas')->get()->count(),

            'pengawasL' => Guru::select('id')->where('eksternal_jabatan', 'Tenaga Kependidikan')->where('jenis_jabatan', 'Pengawas')->where('kategori_jabatan', 'GP (Guru Penggerak)')->where('latar_jabatan', 'Lainnya')->get()->count(),


            'pengawasTGP' => Guru::select('id')->where('eksternal_jabatan', 'Tenaga Kependidikan')->where('jenis_jabatan', 'Pengawas')->where('kategori_jabatan', 'GP (Guru Penggerak)')->where('tugas_jabatan', 'GP (Guru Penggerak)')->get()->count(),
            'pengawasPP' => Guru::select('id')->where('eksternal_jabatan', 'Tenaga Kependidikan')->where('jenis_jabatan', 'Pengawas')->where('kategori_jabatan', 'GP (Guru Penggerak)')->where('tugas_jabatan', 'PP (Pengajar Praktik)')->get()->count(),
            'pengawasFasil' => Guru::select('id')->where('eksternal_jabatan', 'Tenaga Kependidikan')->where('jenis_jabatan', 'Pengawas')->where('kategori_jabatan', 'GP (Guru Penggerak)')->where('tugas_jabatan', 'Fasil (Fasilitator)')->get()->count(),
            'pengawasInstruktur' => Guru::select('id')->where('eksternal_jabatan', 'Tenaga Kependidikan')->where('jenis_jabatan', 'Pengawas')->where('kategori_jabatan', 'GP (Guru Penggerak)')->where('tugas_jabatan', 'Instruktur')->get()->count(),

            'pengawasNonGP' => Guru::select('id')->where('eksternal_jabatan', 'Tenaga Kependidikan')->where('jenis_jabatan', 'Pengawas')->where('kategori_jabatan', 'NoN GP (Guru Penggerak)')->get()->count(),

            'kepsekGP' => Guru::select('id')->where('eksternal_jabatan', 'Tenaga Kependidikan')->where('jenis_jabatan', 'Pengawas')->where('kategori_jabatan', 'GP (Guru Penggerak)')->get()->count(),

            'kepsekLGP' => Guru::select('id')->where('eksternal_jabatan', 'Tenaga Kependidikan')->where('jenis_jabatan', 'Kepala Sekolah')->where('kategori_jabatan', 'GP (Guru Penggerak)')->where('latar_jabatan', 'Sertifikat GP (Guru Penggerak)')->get()->count(),

            'kepsekLDC' => Guru::select('id')->where('eksternal_jabatan', 'Tenaga Kependidikan')->where('jenis_jabatan', 'Kepala Sekolah')->where('kategori_jabatan', 'GP (Guru Penggerak)')->where('latar_jabatan', 'Diklat Cakep')->get()->count(),

            'kepsekL' => Guru::select('id')->where('eksternal_jabatan', 'Tenaga Kependidikan')->where('jenis_jabatan', 'Kepala Sekolah')->where('kategori_jabatan', 'GP (Guru Penggerak)')->where('latar_jabatan', 'Lainnya')->get()->count(),


            'kepsekTGP' => Guru::select('id')->where('eksternal_jabatan', 'Tenaga Kependidikan')->where('jenis_jabatan', 'Kepala Sekolah')->where('kategori_jabatan', 'GP (Guru Penggerak)')->where('tugas_jabatan', 'GP (Guru Penggerak)')->get()->count(),
            'kepsekPP' => Guru::select('id')->where('eksternal_jabatan', 'Tenaga Kependidikan')->where('jenis_jabatan', 'Kepala Sekolah')->where('kategori_jabatan', 'GP (Guru Penggerak)')->where('tugas_jabatan', 'PP (Pengajar Praktik)')->get()->count(),
            'kepsekFasil' => Guru::select('id')->where('eksternal_jabatan', 'Tenaga Kependidikan')->where('jenis_jabatan', 'Kepala Sekolah')->where('kategori_jabatan', 'GP (Guru Penggerak)')->where('tugas_jabatan', 'Fasil (Fasilitator)')->get()->count(),
            'kepsekInstruktur' => Guru::select('id')->where('eksternal_jabatan', 'Tenaga Kependidikan')->where('jenis_jabatan', 'Kepala Sekolah')->where('kategori_jabatan', 'GP (Guru Penggerak)')->where('tugas_jabatan', 'Instruktur')->get()->count(),

            'kepsekNonGP' => Guru::select('id')->where('eksternal_jabatan', 'Tenaga Kependidikan')->where('jenis_jabatan', 'Kepala Sekolah')->where('kategori_jabatan', 'NoN GP (Guru Penggerak)')->get()->count(),

            'tata_usaha' => Guru::select('id')->where('eksternal_jabatan', 'Tenaga Kependidikan')->where('jenis_jabatan', 'Tata Usaha')->get()->count(),
            'pendidik' => Guru::select('id')->where('eksternal_jabatan', 'Tenaga Kependidikan')->where('jenis_jabatan', 'Pendidik')->get()->count(),
            'laboran' => Guru::select('id')->where('eksternal_jabatan', 'Tenaga Kependidikan')->where('jenis_jabatan', 'Laboran')->get()->count(),
            'pustakawan' => Guru::select('id')->where('eksternal_jabatan', 'Tenaga Kependidikan')->where('jenis_jabatan', 'Pustakawan')->get()->count(),



        );

        $stakeholder = array(
            'kpl_dinas' => Guru::select('id')->where('eksternal_jabatan', 'Stakeholder')->where('jenis_jabatan', 'Kepala Dinas')->get()->count(),
            'kpl_bidang' => Guru::select('id')->where('eksternal_jabatan', 'Stakeholder')->where('jenis_jabatan', 'Kepala Bidang')->get()->count(),
            'kpl_seksi' => Guru::select('id')->where('eksternal_jabatan', 'Stakeholder')->where('jenis_jabatan', 'Kepala Seksi')->get()->count(),
            'staff' => Guru::select('id')->where('eksternal_jabatan', 'Stakeholder')->where('jenis_jabatan', 'Staff')->get()->count(),
            'pemerhati' => Guru::select('id')->where('eksternal_jabatan', 'Stakeholder')->where('jenis_jabatan', 'Pemerhati Pendidikan')->get()->count(),
            'pers' => Guru::select('id')->where('eksternal_jabatan', 'Stakeholder')->where('jenis_jabatan', 'Pers')->get()->count(),
        );

        // dd(session('no_ktp'));

        $datas = array(
            'tenaga_pendidik' => $tenaga_pendidik,
            'tenaga_kependidik' => $tenaga_kependidik,
            'stakeholder' => $stakeholder,
            'kegiatan' => PesertaKegiatan::with(['kegiatan', 'getKegiatan'])->where('no_ktp', session('no_ktp'))->first(),
            // 'jadwalLokakarya' => Internal::where('jenis', 'Penugasan Lokakarya')->get(),

        );
        // dd($datas['kegiatan']);

        return view('pages.admin.dashboard.index', ['menu' => 'dashboard', 'datas' => $datas]);
    }

    public function getByKegiatan(Request $r)
    {
        // dd($r->all());
        try {
            $peserta = PesertaKegiatan::where('id_kegiatan', $r->kegiatan_id)->where('no_ktp', $r->nik)->first();
            return response()->json([
                'status' => $peserta == null ? false : true,
                'data' => $peserta
            ]);
        } catch (\Exception $th) {
            return response()->json([
                'status' => false,
                'data' => $th
            ]);
        }
    }

    public function getByKegiatanUser(Request $r)
    {
        // dd($r->all());
        try {
            $peserta = PesertaKegiatan::where('id_kegiatan', $r->kegiatan_id)->where('no_ktp', $r->nik)->first();
            return response()->json([
                'status' => $peserta == null ? false : true,
                'data' => $peserta
            ]);
        } catch (\Exception $th) {
            return response()->json([
                'status' => false,
                'data' => $th
            ]);
        }
    }

    public function jadwal()
    {
        $jadwalInternal = Internal::select('kota', 'jenis', 'deskripsi', 'kegiatan', 'tgl_kegiatan', 'tgl_selesai_kegiatan', 'jam_mulai', 'jam_selesai', 'nama')
            ->whereIn('jenis', ['Pendamping Lokakarya', 'Penugasan Pegawai', 'Penugasan PPNPN'])
            ->get()
            ->groupBy('kegiatan');

        $jadwal = $jadwalInternal->map(function ($items, $key) {
            $groupedByJenis = $items->groupBy('jenis');
            $penugasanPegawai = $groupedByJenis->get('Penugasan Pegawai', collect());
            $penugasanPPNPN = $groupedByJenis->get('Penugasan PPNPN', collect());

            return [
                'kegiatan' => $key,
                'deskripsi' => $items->first()->deskripsi,
                'tgl_kegiatan' => $items->first()->tgl_kegiatan,
                'tgl_selesai_kegiatan' => $items->first()->tgl_selesai_kegiatan,
                'jam_mulai' => $items->first()->jam_mulai,
                'jam_selesai' => $items->first()->jam_selesai,
                'penugasan_pegawai' => $penugasanPegawai->pluck('nama')->unique()->toArray(),
                'penugasan_ppnpn' => $penugasanPPNPN->pluck('nama')->unique()->toArray(),
            ];
        })->values();
        // dd($jadwal[46]);
        return response()->json([
            'jadwal' => $jadwal
        ]);
    }




    /**
     * Show the form for creating a new resource.
     */
    public function profile($id)
    {
        $data = Admin::find($id);
        return view('pages.admin.profile.index', ['menu' => 'profile', 'data' => $data]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function profile_update(Request $request)
    {
        $r = $request->all();


        $admin = Admin::find($r['id']);
        $user = User::find($r['id']);
        if ($r['password'] != null) {
            $r['password'] = bcrypt($r['password']);
        } else {
            unset($r['password']);
        }

        $admin->update($r);
        $user->update($r);

        return redirect()->route('dashboard')->with('message', 'update profile');
    }

    public function getJadwalByPegawai($nik)
    {
        // Ambil jadwal dari Internal hanya untuk pegawai dengan NIK tertentu
        // Ambil jadwal dari Internal dengan tiga jenis yang disebutkan
        $jadwalInternal = Internal::select('kota', 'jenis', 'deskripsi', 'kegiatan', 'tgl_kegiatan', 'tgl_selesai_kegiatan', 'jam_mulai', 'jam_selesai', 'nama')
            ->whereIn('jenis', ['Pendamping Lokakarya', 'Penugasan Pegawai', 'Penugasan PPNPN'])->where('nik', $nik)
            ->get();

        // Mengembalikan response dalam bentuk JSON
        return response()->json([
            'jadwal' => $jadwalInternal
        ]);
    }
}
