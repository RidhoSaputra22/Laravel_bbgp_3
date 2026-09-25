<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Models\GolonganP3k;
use App\Models\Guru;
use App\Models\JabatanPenugasanGolongan;
use App\Models\Kabupaten;
use App\Models\Kegiatan;
use App\Models\Pegawai;
use App\Models\PesertaKegiatan;

use Barryvdh\DomPDF\Facade\Pdf as PDF;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class KegiatanController extends Controller
{
    public function index()
    {
        // Ambil kegiatan yang statusnya aktif (status = 1)
        $dataKegiatan = Kegiatan::where('status', 'true')->get();
        $dataPegawai = Pegawai::get();
        $data = [];

        // Ambil data peserta jika ada kegiatan aktif
        if ($dataKegiatan->isNotEmpty()) {
            $data = PesertaKegiatan::get();
        }

        return view('pages.landing.kegiatan.kegiatan', [
            'menu' => 'kegiatan',
            'data' => $data,
            'kegiatan' => $dataKegiatan,
            'pegawai' => $dataPegawai,
        ]);
    }

    public function cari(Request $request)
    {
        $cari = $request->validate([
            'cari' => 'required|string|max:50|regex:/^[A-Za-z0-9._-]+$/',
        ])['cari'];

        $data = PesertaKegiatan::where('no_ktp', $cari)->paginate(10);

        // Check if any data is found
        if ($data->isNotEmpty()) {
            // Data found, send data to the view
            return view('pages.user.kegiatan.index', [
                'menu' => 'kegiatan',
                'data' => $data
            ]);
        } else {
            // No data found, send a message to the view
            return view('pages.user.kegiatan.index', [
                'menu' => 'kegiatan',
                'message' => 'Silahkan registrasi untuk mengikuti kegiatan ini.'
            ]);
        }
    }

    public function cariPeserta(Request $request)
    {
        $validated = $request->validate([
            'kegiatan_id' => 'required|integer|exists:kegiatans,id',
            'nik' => 'required|string|max:50|regex:/^[A-Za-z0-9._-]+$/',
        ]);

        $kegiatanId = $validated['kegiatan_id'];
        $nik = $validated['nik'];
        Session::put(['nik' => $nik, 'val' => $kegiatanId]);

        $title = 'Peserta';
        $status = true;
        $peserta = PesertaKegiatan::select([
                'id', 'id_kegiatan', 'no_ktp', 'nama', 'nip', 'instansi',
                'status_keikutpesertaan', 'golongan', 'jenis_gol', 'jkl',
                'kelengkapan_peserta_transport', 'kelengkapan_peserta_biodata',
                'no_hp', 'no_wa', 'kabupaten', 'no_surat_tugas', 'tgl_surat_tugas',
            ])->where('id_kegiatan', $kegiatanId)
            ->where('no_ktp', $nik)
            ->get();

        if ($peserta->isEmpty()) {
            $title = 'Pegawai';
            $peserta = Pegawai::select([
                'id', 'nama_lengkap', 'no_ktp', 'nip', 'jabatan', 'instansi',
                'golongan', 'gender', 'kabupaten', 'no_hp',
            ])->where('no_ktp', $nik)->get();

            if ($peserta->isEmpty()) {
                $title = 'Eksternal';
                $peserta = Guru::select([
                    'id', 'nama_lengkap', 'no_ktp', 'nip', 'jabatan', 'eksternal_jabatan',
                    'satuan_pendidikan', 'gender', 'kabupaten', 'no_hp',
                ])->where('no_ktp', $nik)->get();
            }
            $status = false;
        }


        return response()->json(['data' => $peserta, 'tipe' => $title, 'success' => $status]);
    }


    public function regist(Request $r)
    {
        $validated = $r->validate([
            'kegiatan_id' => 'required|integer|exists:kegiatans,id',
            'nik' => 'required|string|max:50|regex:/^[A-Za-z0-9._-]+$/',
        ]);
        $menu = 'kegiatan';
        $kegiatanId = $validated['kegiatan_id'];
        $pegawai = Pegawai::orderBy('id', 'ASC')->get();
        $guru = Guru::where('no_ktp', $validated['nik'])->get();
        $kabupaten = Kabupaten::orderBy('id', 'ASC')->get();
        $kabupaten = Kabupaten::orderBy('id', 'ASC')->get();


        $peserta = PesertaKegiatan::where('id_kegiatan', $kegiatanId)
            ->where('no_ktp', $validated['nik'])
            ->get();

        if ($peserta->isEmpty()) {
            $title = 'Pegawai';
            $peserta = Pegawai::where('no_ktp', $validated['nik'])->get();

            if ($peserta->isEmpty()) {
                $title = 'Eksternal';
                $peserta = Guru::where('no_ktp', $validated['nik'])->get();
            }
            $status = false;
        }


        // Get data for the view, e.g., list of kabupaten, golongan, etc.
        $status = [
            'kegiatanById' => Kegiatan::find($kegiatanId),
            'kabupaten' => Kabupaten::all(),
            'golongan' => JabatanPenugasanGolongan::all(),
            'golongan_p3k' => GolonganP3k::get(),
        ];

        return view('pages.landing.kegiatan.daftar', compact('kegiatanId', 'status', 'menu', 'peserta', 'guru'));
    }

    public function store(Request $request)
    {
        $r = $request->validate([
            'kegiatan_id' => 'required|integer|exists:kegiatans,id',
            'nama' => 'required|string|max:255',
            'no_ktp' => 'required|string|max:50|regex:/^[A-Za-z0-9._-]+$/',
            'nip' => 'nullable|string|max:50',
            'alamat' => 'nullable|string|max:255',
            'kabupaten' => 'required|string|max:255',
            'asal_kabupaten' => 'nullable|string|max:255',
            'instansi' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'mata_pelajaran' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:50',
            'jkl' => 'nullable|string|in:Laki-laki,Perempuan',
            'jenis_gol' => 'required|string|in:PNS,P3K,Tidak ada golongan',
            'golongan_pns' => 'nullable|string|max:100',
            'golongan_p3k' => 'nullable|string|max:100',
            'diluar_gol' => 'nullable|string|max:100',
            'no_wa' => 'nullable|string|max:30',
            'no_hp' => 'nullable|string|max:30',
            'no_surat_tugas' => 'required|string|max:255',
            'tgl_surat_tugas' => 'required|date',
            'status_keikutpesertaan' => 'required|in:peserta,panitia,narasumber',
            'tempat_lahir' => 'nullable|string|max:255',
            'tgl_lahir' => 'nullable|date',
            'agama' => 'nullable|string|max:50',
            'pendidikan' => 'nullable|string|max:100',
            'alamat_rumah' => 'nullable|string|max:1000',
            'kabupaten_rumah' => 'nullable|string|max:255',
            'npwp' => 'nullable|string|max:50',
        ]);
        if (isset($r['jabatan'])) {
            $r['jabatan'] = ucwords(strtolower($r['jabatan']));
        }
        $menu = 'kegiatan';



        if (($r['kabupaten'] ?? null) == 'lainnya') {

            if (($r['asal_kabupaten'] ?? null) == null) {
                $r['asal_kabupaten'] = '-';
                $r['kabupaten'] = $r['asal_kabupaten'];
            } else {
                $r['kabupaten'] = $r['asal_kabupaten'];
            }
        }

        if (($r['jenis_gol'] ?? null) == 'PNS' && ($r['golongan_pns'] ?? null) != null) {
            $r['golongan'] = $r['golongan_pns'];
            $r['diluar_gol'] = null;
            $r['golongan_p3k'] = null;
        } else if (($r['jenis_gol'] ?? null) == 'P3K' && ($r['golongan_p3k'] ?? null) != null) {
            $r['golongan'] = $r['golongan_p3k'];
            $r['diluar_gol'] = null;
            $r['golongan_pns'] = null;
        } else if (($r['jenis_gol'] ?? null) == 'Tidak ada golongan' && ($r['diluar_gol'] ?? null) != null) {
            $r['golongan'] = $r['diluar_gol'];
            $r['golongan_p3k'] = null;
            $r['golongan_pns'] = null;
        } else {
            // Handle case where none of the golongan values are set
            $status = [
                'kegiatanById' => Kegiatan::find($r['kegiatan_id']),
                'kabupaten' => Kabupaten::all(),
                'golongan' => JabatanPenugasanGolongan::all(),
                'golongan_p3k' => GolonganP3k::get(),
            ];


            return redirect()->route('user.kegiatan_regist', [
                'kegiatan_id' => $status['kegiatanById']->id,
            ])->with([
                'status' => $status,
                'message' => 'error golongan',
                'menu' => 'kegiatan',
            ]);
        }

        $r['id_kegiatan'] = $r['kegiatan_id'];

        $pesertaRecord = PesertaKegiatan::create($r);

        // Update master data (Guru or Pegawai) so data is synced for future use
        $master = Pegawai::where('no_ktp', $request->no_ktp)->first();
        if (!$master) {
            $master = Guru::where('no_ktp', $request->no_ktp)->first();
        }

        if ($master) {
            $master->update([
                'tempat_lahir' => $r['tempat_lahir'] ?? $master->tempat_lahir,
                'tgl_lahir' => $r['tgl_lahir'] ?? $master->tgl_lahir,
                'agama' => $r['agama'] ?? $master->agama,
                'pendidikan' => $r['pendidikan'] ?? $master->pendidikan,
                'alamat_rumah' => $r['alamat_rumah'] ?? $master->alamat_rumah,
                'kabupaten_rumah' => $r['kabupaten_rumah'] ?? $master->kabupaten_rumah,
                'jkl' => $r['jkl'] ?? ($r['gender'] ?? $master->jkl),
                'status' => $r['status'] ?? $master->status,
                'nip' => $r['nip'] ?? $master->nip,
                'nama' => $r['nama'] ?? $master->nama,
            ]);
        }

        Session::flush();

        Session::put('no_ktp', $request->no_ktp);
        Session::put('id', $pesertaRecord->id);
        Session::put('val', $request->kegiatan_id);

        return redirect()->route('user.kegiatan')->with('message', 'sukses daftar');
    }


    public function getStatus(Request $request)
    {
        $kegiatanId = $request->validate([
            'kegiatan_id' => 'required|integer|exists:kegiatans,id',
        ])['kegiatan_id'];

        // Misalkan Anda memiliki model Kegiatan dan ingin mengambil status keikutsertaan dari database
        $kegiatan = Kegiatan::find($kegiatanId);

        if (!$kegiatan) {
            return response()->json([
                'success' => false,
                'message' => 'Kegiatan not found',
            ], 404);
        }

        // Misalnya Anda memiliki atribut 'status_keikutpesertaan' di model Kegiatan
        $status = $kegiatan->status_keikutpesertaan;

        return response()->json([
            'success' => true,
            'status_keikutpesertaan' => $status,
        ]);
    }

    public function cekDataPeserta(Request $request)
    {
        $nik = $request->validate([
            'nik' => 'required|string|max:50|regex:/^[A-Za-z0-9._-]+$/',
        ])['nik'];
        $title = 'Peserta';
        $prefillColumns = [
            'id', 'nama', 'no_ktp', 'nip', 'email', 'tempat_lahir', 'tgl_lahir',
            'agama', 'pendidikan', 'jkl', 'status', 'instansi', 'alamat_rumah',
            'no_hp', 'no_wa', 'kabupaten', 'jenis_gol', 'golongan',
        ];
        $peserta = PesertaKegiatan::select($prefillColumns)
            ->where('no_ktp', $nik)
            ->first();
        $instansi = '';



        // jika peserta ada di PesertaKegiatan, maka tambahkan jabatan
        if($peserta != null){

            // cek apakah peserta ada di tabel guru
            $guru = Guru::select([
                'id', 'nama_lengkap', 'email', 'no_ktp', 'nip', 'tempat_lahir',
                'tgl_lahir', 'gender', 'jabatan', 'status', 'agama', 'pendidikan',
                'kabupaten', 'satuan_pendidikan', 'alamat_satuan', 'alamat_rumah',
                'no_hp', 'no_wa', 'npsn_sekolah', 'nuptk', 'eksternal_jabatan',
                'tugas_jabatan', 'jenis_bank',
            ])->where('no_ktp', $nik)->first();
            if($guru != null){
                $instansi = $guru->sekolah->nama_sekolah ?? '';
                $jabatan = $guru->eksternal_jabatan ?? '';
            }else{
                // cek apakah peserta ada di tabel pegawai
                $pegawai = Pegawai::select([
                    'id', 'nama_lengkap', 'email', 'no_ktp', 'nip', 'tempat_lahir',
                    'tgl_lahir', 'gender', 'jabatan', 'status', 'agama', 'pendidikan',
                    'kabupaten', 'satuan_pendidikan', 'alamat_satuan', 'alamat_rumah',
                    'no_hp', 'no_wa', 'instansi', 'golongan', 'jenis_pegawai',
                ])->where('no_ktp', $nik)->first();
                if($pegawai != null){
                    $instansi = $pegawai->instansi ?? '';
                    $jabatan = $pegawai->jabatan ?? '';
                }
            }

            $peserta->jabatan = $jabatan ?? '';


        }

        if($peserta == null){
            $title = 'Pegawai';
            $peserta = Pegawai::select([
                'id', 'nama_lengkap', 'email', 'no_ktp', 'nip', 'tempat_lahir',
                'tgl_lahir', 'gender', 'jabatan', 'status', 'agama', 'pendidikan',
                'kabupaten', 'satuan_pendidikan', 'alamat_satuan', 'alamat_rumah',
                'no_hp', 'no_wa', 'instansi', 'golongan', 'jenis_pegawai',
            ])->where('no_ktp', $nik)->first();
            $instansi = $peserta->instansi ?? '';

            if ($peserta == null) {
                $title = 'Eksternal';
                $peserta = Guru::select([
                    'id', 'nama_lengkap', 'email', 'no_ktp', 'nip', 'tempat_lahir',
                    'tgl_lahir', 'gender', 'jabatan', 'status', 'agama', 'pendidikan',
                    'kabupaten', 'satuan_pendidikan', 'alamat_satuan', 'alamat_rumah',
                    'no_hp', 'no_wa', 'eksternal_jabatan', 'tugas_jabatan',
                ])->where('no_ktp', $nik)->first();
                $instansi = $peserta->sekolah->nama_sekolah ?? '';
            }

            $status = false;
        } else {
            $status = true;
        }

        

        Session::put('nik', $nik);
        Session::put('dataAda', $status);


        return response()->json([
            'success' => $status,
            'data' => $peserta,
            'instansi' => $instansi
        ]);
    }

    public function getPesertaByKegiatan(Request $request)
    {
        $validated = $request->validate([
            'kegiatan_id' => 'required|integer|exists:kegiatans,id',
            'nik' => 'required|string|max:50|regex:/^[A-Za-z0-9._-]+$/',
        ]);

        $data = PesertaKegiatan::select([
                'id', 'id_kegiatan', 'no_ktp', 'nama', 'instansi',
                'status_keikutpesertaan', 'golongan', 'jenis_gol', 'jkl',
                'kelengkapan_peserta_transport', 'kelengkapan_peserta_biodata',
                'no_hp', 'no_wa', 'kabupaten',
            ])->where('id_kegiatan', $validated['kegiatan_id'])
            ->where('no_ktp', $validated['nik'])
            ->get();
        return response()->json(['data' => $data]);
    }

    public function getPesertaDetail(Request $request)
    {
        $pesertaId = $request->integer('id');
        $peserta = PesertaKegiatan::select([
            'id', 'id_kegiatan', 'no_ktp', 'nama', 'instansi',
            'status_keikutpesertaan', 'golongan', 'jenis_gol', 'jkl',
            'kelengkapan_peserta_transport', 'kelengkapan_peserta_biodata',
            'no_hp', 'no_wa', 'kabupaten', 'no_surat_tugas', 'tgl_surat_tugas',
        ])->findOrFail($pesertaId);

        abort_unless(session('nik') && hash_equals((string) session('nik'), (string) $peserta->no_ktp), 403);

        return response()->json($peserta);
    }


    public function printAbsensiPeserta(Request $request)
    {
        $kegiatanId = $this->validatedKegiatanId($request);
        $kegiatan = Kegiatan::find($kegiatanId);

        // Mendapatkan data guru dari model Guru
        $data = PesertaKegiatan::where('status_keikutpesertaan', 'peserta')->where('id_kegiatan', $kegiatanId)->orderByRaw("FIELD(kabupaten, 'Kabupaten Kepulauan Selayar', 'Kota Parepare', 'Kabupaten Barru', 'Kabupaten Jeneponto', 'Kabupaten Takalar', 'Kabupaten Sidrap', 'Kabupaten Pinrang', 'Kabupaten Luwu Timur', 'Kabupaten Toraja Utara', 'Kabupaten Wajo', 'Kabupaten Pangkep', 'Kabupaten Soppeng', 'Kabupaten Bulukumba', 'Kabupaten Gowa', 'Kabupaten Maros', 'Kabupaten Tana Toraja', 'Kota Palopo', 'Kabupaten Bone', 'Kota Makassar', 'Kabupaten Enrekang', 'Kabupaten Sinjai', 'Kabupaten Luwu', 'Kabupaten Luwu Utara', 'Kabupaten Bantaeng')")->get();


        $pdf = PDF::loadView('pages.user.kegiatan.cetak.absenPeserta', compact('data', 'kegiatan'));

        // Set properties PDF
        $pdf->setPaper('a4', 'potrait'); // Set kertas ke mode landscape


        // Download PDF dengan nama file 'data_guru.pdf'
        return $pdf->stream('data_absensi_peserta_kegiatan.pdf');
        // Logic to generate PDF for Absensi Peserta
        // Return response with PDF
    }

    public function printRegistrasiPeserta(Request $request)
    {
        $kegiatanId = $this->validatedKegiatanId($request);
        $kegiatan = Kegiatan::find($kegiatanId);
        // Logic to generate PDF for Registrasi Peserta
        // Return response with PDF
        $data = PesertaKegiatan::where('status_keikutpesertaan', 'peserta')->where('id_kegiatan', $kegiatanId)->orderByRaw("FIELD(kabupaten, 'Kabupaten Kepulauan Selayar', 'Kota Parepare', 'Kabupaten Barru', 'Kabupaten Jeneponto', 'Kabupaten Takalar', 'Kabupaten Sidrap', 'Kabupaten Pinrang', 'Kabupaten Luwu Timur', 'Kabupaten Toraja Utara', 'Kabupaten Wajo', 'Kabupaten Pangkep', 'Kabupaten Soppeng', 'Kabupaten Bulukumba', 'Kabupaten Gowa', 'Kabupaten Maros', 'Kabupaten Tana Toraja', 'Kota Palopo', 'Kabupaten Bone', 'Kota Makassar', 'Kabupaten Enrekang', 'Kabupaten Sinjai', 'Kabupaten Luwu', 'Kabupaten Luwu Utara', 'Kabupaten Bantaeng')")->get();


        $pdf = PDF::loadView('pages.user.kegiatan.cetak.absenRegisterPeserta', compact('data', 'kegiatan'));

        // Set properties PDF
        $pdf->setPaper('a4', 'potrait'); // Set kertas ke mode landscape


        // Download PDF dengan nama file 'data_guru.pdf'
        return $pdf->stream('data_absensi_peserta_kegiatan.pdf');
    }

    public function printAbsensiPanitia(Request $request)
    {
        $kegiatanId = $this->validatedKegiatanId($request);
        $kegiatan = Kegiatan::find($kegiatanId);
        // Logic to generate PDF for Absensi Panitia
        // Return response with PDF

        $data = PesertaKegiatan::where('status_keikutpesertaan', 'panitia')->where('id_kegiatan', $kegiatanId)->where('id_kegiatan', $kegiatanId)->orderByRaw("FIELD(kabupaten, 'Kabupaten Kepulauan Selayar', 'Kota Parepare', 'Kabupaten Barru', 'Kabupaten Jeneponto', 'Kabupaten Takalar', 'Kabupaten Sidrap', 'Kabupaten Pinrang', 'Kabupaten Luwu Timur', 'Kabupaten Toraja Utara', 'Kabupaten Wajo', 'Kabupaten Pangkep', 'Kabupaten Soppeng', 'Kabupaten Bulukumba', 'Kabupaten Gowa', 'Kabupaten Maros', 'Kabupaten Tana Toraja', 'Kota Palopo', 'Kabupaten Bone', 'Kota Makassar', 'Kabupaten Enrekang', 'Kabupaten Sinjai', 'Kabupaten Luwu', 'Kabupaten Luwu Utara', 'Kabupaten Bantaeng')")->get();


        $pdf = PDF::loadView('pages.user.kegiatan.cetak.absenPanitia', compact('data', 'kegiatan'));

        // Set properties PDF
        $pdf->setPaper('a4', 'potrait'); // Set kertas ke mode landscape


        // Download PDF dengan nama file 'data_guru.pdf'
        return $pdf->stream('data_absensi_panitia_kegiatan.pdf');
    }


    public function printAbsensiNarasumber(Request $request)
    {
        $kegiatanId = $this->validatedKegiatanId($request);
        $kegiatan = Kegiatan::find($kegiatanId);
        // Logic to generate PDF for Absensi Narasumber
        // Return response with PDF

        $data = PesertaKegiatan::where('status_keikutpesertaan', 'narasumber')->where('id_kegiatan', $kegiatanId)->orderByRaw("FIELD(kabupaten, 'Kabupaten Kepulauan Selayar', 'Kota Parepare', 'Kabupaten Barru', 'Kabupaten Jeneponto', 'Kabupaten Takalar', 'Kabupaten Sidrap', 'Kabupaten Pinrang', 'Kabupaten Luwu Timur', 'Kabupaten Toraja Utara', 'Kabupaten Wajo', 'Kabupaten Pangkep', 'Kabupaten Soppeng', 'Kabupaten Bulukumba', 'Kabupaten Gowa', 'Kabupaten Maros', 'Kabupaten Tana Toraja', 'Kota Palopo', 'Kabupaten Bone', 'Kota Makassar', 'Kabupaten Enrekang', 'Kabupaten Sinjai', 'Kabupaten Luwu', 'Kabupaten Luwu Utara', 'Kabupaten Bantaeng')")->get();


        $pdf = PDF::loadView('pages.user.kegiatan.cetak.absenNarasumber', compact('data', 'kegiatan'));

        // Set properties PDF
        $pdf->setPaper('a4', 'potrait'); // Set kertas ke mode landscape


        // Download PDF dengan nama file 'data_guru.pdf'
        return $pdf->stream('data_absensi_narasumber_kegiatan.pdf');
    }

    public function printAbsensiTp(Request $request)
    {
        $kegiatanId = $this->validatedKegiatanId($request);
        $kegiatan = Kegiatan::find($kegiatanId);
        // Logic to generate PDF for Absensi Narasumber
        // Return response with PDF

        $data = PesertaKegiatan::join('gurus', 'peserta_kegiatans.no_ktp', '=', 'gurus.no_ktp')
            ->where('peserta_kegiatans.id_kegiatan', $kegiatanId)
            ->where('gurus.eksternal_jabatan', 'Tenaga Pendidik')
            ->orderByRaw("FIELD(peserta_kegiatans.kabupaten, 'Kabupaten Kepulauan Selayar', 'Kota Parepare', 'Kabupaten Barru', 'Kabupaten Jeneponto', 'Kabupaten Takalar', 'Kabupaten Sidrap', 'Kabupaten Pinrang', 'Kabupaten Luwu Timur', 'Kabupaten Toraja Utara', 'Kabupaten Wajo', 'Kabupaten Pangkep', 'Kabupaten Soppeng', 'Kabupaten Bulukumba', 'Kabupaten Gowa', 'Kabupaten Maros', 'Kabupaten Tana Toraja', 'Kota Palopo', 'Kabupaten Bone', 'Kota Makassar', 'Kabupaten Enrekang', 'Kabupaten Sinjai', 'Kabupaten Luwu', 'Kabupaten Luwu Utara', 'Kabupaten Bantaeng')")
            ->get();


        $pdf = PDF::loadView('pages.user.kegiatan.cetak.absenTP', compact('data', 'kegiatan'));

        // Set properties PDF
        $pdf->setPaper('a4', 'potrait'); // Set kertas ke mode landscape


        // Download PDF dengan nama file 'data_guru.pdf'
        return $pdf->stream('data_absensi_TPendidik_kegiatan.pdf');
    }

    public function printAbsensiTkp(Request $request)
    {
        $kegiatanId = $this->validatedKegiatanId($request);
        $kegiatan = Kegiatan::find($kegiatanId);
        // Logic to generate PDF for Absensi Narasumber
        // Return response with PDF

        $data = PesertaKegiatan::join('gurus', 'peserta_kegiatans.no_ktp', '=', 'gurus.no_ktp')
            ->where('peserta_kegiatans.id_kegiatan', $kegiatanId)
            ->where('gurus.eksternal_jabatan', 'Tenaga Kependidikan')
            ->orderByRaw("FIELD(peserta_kegiatans.kabupaten, 'Kabupaten Kepulauan Selayar', 'Kota Parepare', 'Kabupaten Barru', 'Kabupaten Jeneponto', 'Kabupaten Takalar', 'Kabupaten Sidrap', 'Kabupaten Pinrang', 'Kabupaten Luwu Timur', 'Kabupaten Toraja Utara', 'Kabupaten Wajo', 'Kabupaten Pangkep', 'Kabupaten Soppeng', 'Kabupaten Bulukumba', 'Kabupaten Gowa', 'Kabupaten Maros', 'Kabupaten Tana Toraja', 'Kota Palopo', 'Kabupaten Bone', 'Kota Makassar', 'Kabupaten Enrekang', 'Kabupaten Sinjai', 'Kabupaten Luwu', 'Kabupaten Luwu Utara', 'Kabupaten Bantaeng')")
            ->get();


        $pdf = PDF::loadView('pages.user.kegiatan.cetak.absenTKP', compact('data', 'kegiatan'));

        // Set properties PDF
        $pdf->setPaper('a4', 'potrait'); // Set kertas ke mode landscape


        // Download PDF dengan nama file 'data_guru.pdf'
        return $pdf->stream('data_absensi_TKependidikan_kegiatan.pdf');
    }

    public function printAbsensiStk(Request $request)
    {
        $kegiatanId = $this->validatedKegiatanId($request);
        $kegiatan = Kegiatan::find($kegiatanId);
        // Logic to generate PDF for Absensi Narasumber
        // Return response with PDF

        $data = PesertaKegiatan::join('gurus', 'peserta_kegiatans.no_ktp', '=', 'gurus.no_ktp')
            ->where('peserta_kegiatans.id_kegiatan', $kegiatanId)
            ->where('gurus.eksternal_jabatan', 'Stakeholder')
            ->orderByRaw("FIELD(peserta_kegiatans.kabupaten, 'Kabupaten Kepulauan Selayar', 'Kota Parepare', 'Kabupaten Barru', 'Kabupaten Jeneponto', 'Kabupaten Takalar', 'Kabupaten Sidrap', 'Kabupaten Pinrang', 'Kabupaten Luwu Timur', 'Kabupaten Toraja Utara', 'Kabupaten Wajo', 'Kabupaten Pangkep', 'Kabupaten Soppeng', 'Kabupaten Bulukumba', 'Kabupaten Gowa', 'Kabupaten Maros', 'Kabupaten Tana Toraja', 'Kota Palopo', 'Kabupaten Bone', 'Kota Makassar', 'Kabupaten Enrekang', 'Kabupaten Sinjai', 'Kabupaten Luwu', 'Kabupaten Luwu Utara', 'Kabupaten Bantaeng')")
            ->get();


        $pdf = PDF::loadView('pages.user.kegiatan.cetak.absenSTK', compact('data', 'kegiatan'));

        // Set properties PDF
        $pdf->setPaper('a4', 'potrait'); // Set kertas ke mode landscape


        // Download PDF dengan nama file 'data_guru.pdf'
        return $pdf->stream('data_absensi_Stakeholder_kegiatan.pdf');
    }

    public function printAbsensiPgw(Request $request)
    {
        $kegiatanId = $this->validatedKegiatanId($request);
        $kegiatan = Kegiatan::find($kegiatanId);
        // Logic to generate PDF for Absensi Narasumber
        // Return response with PDF

        $data = PesertaKegiatan::join('pegawais', 'peserta_kegiatans.no_ktp', '=', 'pegawais.no_ktp')
            ->where('peserta_kegiatans.id_kegiatan', $kegiatanId)
            ->orderByRaw("FIELD(peserta_kegiatans.kabupaten, 'Kabupaten Kepulauan Selayar', 'Kota Parepare', 'Kabupaten Barru', 'Kabupaten Jeneponto', 'Kabupaten Takalar', 'Kabupaten Sidrap', 'Kabupaten Pinrang', 'Kabupaten Luwu Timur', 'Kabupaten Toraja Utara', 'Kabupaten Wajo', 'Kabupaten Pangkep', 'Kabupaten Soppeng', 'Kabupaten Bulukumba', 'Kabupaten Gowa', 'Kabupaten Maros', 'Kabupaten Tana Toraja', 'Kota Palopo', 'Kabupaten Bone', 'Kota Makassar', 'Kabupaten Enrekang', 'Kabupaten Sinjai', 'Kabupaten Luwu', 'Kabupaten Luwu Utara', 'Kabupaten Bantaeng')")
            ->get();

        $pdf = PDF::loadView('pages.user.kegiatan.cetak.absenPGW', compact('data', 'kegiatan'));

        // Set properties PDF
        $pdf->setPaper('a4', 'potrait'); // Set kertas ke mode landscape


        // Download PDF dengan nama file 'data_guru.pdf'
        return $pdf->stream('data_absensi_pegawai_kegiatan.pdf');
    }

    private function validatedKegiatanId(Request $request): int
    {
        return (int) $request->validate([
            'kegiatan_id' => 'required|integer|exists:kegiatans,id',
        ])['kegiatan_id'];
    }
}
