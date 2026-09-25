<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Guru;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Provinsi;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SekolahController extends Controller
{
    public function index()
    {
        // dd(1);
        return view('pages.landing.sekolah.index', [
            'menu' => 'sekolah',
            // 'activities' => $activities,
        ]);
    }

    public function store(Request $request)
    {
        // dd($request->all());

        $validated = $request->validate([
            'nama_sekolah' => 'required|string|max:255',
            'npsn_sekolah' => 'required|integer',
            'bp_sekolah' => 'required|in:TK,SD,SMP,SMA/SMK',
            'status_sekolah' => 'required|in:Negeri,Swasta',
            'akreditasi' => 'required|in:A,B,C,belum',
            'alamat' => 'required|string',
            'provinsi' => 'required|string',
            'kabupaten' => 'required|string',
            'kecamatan' => 'required|string',
            'no_telepon' => 'required|string',
            'email' => 'nullable',
            'website_url' => 'nullable',
            'tahun_berdiri' => 'nullable|integer|min:1900|max:' . date('Y'),
            'koordinat' => 'nullable|string',
            'nama_kepsek' => 'required|string',
            'asn_opsi' => 'required|in:ya,tidak',
            'nip_kepsek' => 'nullable|string',
            'no_sk' => 'nullable|string',
            'no_telp_kepsek' => 'required|string',
            'email_kepsek' => 'nullable',
            'jumlah_guru' => 'required|integer|min:0',
            'jumlah_guru_pns' => 'required|integer|min:0',
            'jumlah_honorer' => 'required|integer|min:0',
            'jumlah_kependidikan' => 'required|integer|min:0',
            'bidang_studi' => 'nullable|string',
            'jumlah_siswa' => 'required|integer|min:0',
            'jumlah_siswa_pria' => 'required|integer|min:0',
            'jumlah_siswa_perempuan' => 'required|integer|min:0',
            'jumlah_siswa_per_kelas' => 'nullable|string',
            'jumlah_kelas' => 'required|integer|min:0',
            'laboratorium' => 'required|string',
            'perpustakaan' => 'required|string',
            'ruang_guru' => 'required|string',
            'jumlah_toilet' => 'required|integer|min:0',
            'lapangan_olahraga' => 'required|string',
            'ekstrakurikuler' => 'string',
            'program_unggulan' => 'string',
            'fasilitas_it' => 'nullable|array',
            'akses_internet' => 'required|string',
            'jam_belajar' => 'required|string',
            'foto_depan' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'logo_sekolah' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'denah_lokasi' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'struktur_organisasi' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        // Cek apakah user sudah punya data sekolah
        // if (Sekolah::where('user_id', Auth::id())->exists()) {
        //     return back()->with('error', 'Anda sudah memiliki data sekolah. Silakan edit data yang sudah ada.');
        // }

        DB::beginTransaction();

        try {
            // GABUNGKAN fasilitas_it_tambahan ke dalam fasilitas_it
            if ($request->filled('fasilitas_it_tambahan')) {
                $fasilitasIt = $validated['fasilitas_it'] ?? [];
                $tambahan = array_filter(array_map('trim', explode(',', $request->fasilitas_it_tambahan)));
                $validated['fasilitas_it'] = array_merge($fasilitasIt, $tambahan);
            }

            // HAPUS fasilitas_it_tambahan dari validated
            unset($validated['fasilitas_it_tambahan']);

            $data['fasilitas_it'] = isset($validated['fasilitas_it'])
                ? json_encode($validated['fasilitas_it'])
                : null;

            // Handle file uploads

            if ($request->hasFile('foto_depan')) {
                $validated['foto_depan'] = $request->file('foto_depan')->store('sekolah/foto_depan', 'public');
            }

            if ($request->hasFile('logo_sekolah')) {
                $validated['logo_sekolah'] = $request->file('logo_sekolah')->store('sekolah/logo', 'public');
            }

            if ($request->hasFile('denah_lokasi')) {
                $validated['denah_lokasi'] = $request->file('denah_lokasi')->store('sekolah/denah', 'public');
            }

            if ($request->hasFile('struktur_organisasi')) {
                $validated['struktur_organisasi'] = $request->file('struktur_organisasi')->store('sekolah/struktur', 'public');
            }

            // Simpan data sekolah
            $sekolah = Sekolah::create($validated);

            // BUAT AKUN KEPALA SEKOLAH OTOMATIS
            $akunKepsek = $this->createAkunKepalaSekolah($request, $sekolah);



            // Commit transaction
            DB::commit();

            return redirect()->route('user.index')->with([
                'message' => 'sukses daftar sekolah',
                'registration_credentials' => [
                    'username' => $akunKepsek['username'],
                    'password' => $akunKepsek['password_plain'],
                ],
            ]);
        } catch (\Exception $e) {
            // Rollback jika ada error
            DB::rollBack();
            report($e);

            return back()->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan data sekolah.');
        }
    }

    // Tampilkan detail
    public function show($id)
    {
        $this->authorizeSchoolOwner($id);

        try {
            $sekolah = Sekolah::find($id);
            // return view('pages.admin.guru.sekolah.show', compact('sekolah'));
            return view('pages.admin.guru.showSekolah', ['sekolah' => $sekolah, 'menu' => 'data-sekolah']);
        } catch (\Exception $e) {
            report($e);
            return response()->json(['message' => 'Data sekolah tidak dapat ditampilkan.'], 500);
        }
    }

    // Tampilkan form edit
    public function edit($id)
    {
        $this->authorizeSchoolOwner($id);

        try {
            $sekolah = Sekolah::where('id', $id)
                ->firstOrFail();
            return view('pages.admin.guru.editSekolah', ['sekolah' => $sekolah, 'menu' => 'edit-data-sekolah']);
        } catch (\Exception $e) {
            report($e);
            return response()->json(['message' => 'Data sekolah tidak dapat ditampilkan.'], 500);
        }
    }

    // Update data
    public function update(Request $request, $id)
    {
        $this->authorizeSchoolOwner($id);

        $sekolah = Sekolah::where('id', $id)
            ->firstOrFail();
        // Validasi (NPSN unique kecuali milik sendiri)
        $validated = $request->validate([
            'nama_sekolah' => 'required|string|max:255',
            'npsn_sekolah' => 'required|integer',
            'bp_sekolah' => 'required|in:TK,SD,SMP,SMA/SMK',
            'status_sekolah' => 'required|in:Negeri,Swasta',
            'akreditasi' => 'required|in:A,B,C,belum',
            'alamat' => 'required|string',
            'provinsi' => 'required|string',
            'kabupaten' => 'required|string',
            'kecamatan' => 'required|string',
            'no_telepon' => 'required|string',
            'email' => 'nullable',
            'website_url' => 'nullable',
            'tahun_berdiri' => 'nullable|integer|min:1900|max:' . date('Y'),
            'koordinat' => 'nullable|string',
            'nama_kepsek' => 'required|string',
            'asn_opsi' => 'required|in:ya,tidak',
            'nip_kepsek' => 'nullable|string',
            'no_sk' => 'nullable|string',
            'no_telp_kepsek' => 'required|string',
            'email_kepsek' => 'nullable',
            'jumlah_guru' => 'required|integer|min:0',
            'jumlah_guru_pns' => 'required|integer|min:0',
            'jumlah_honorer' => 'required|integer|min:0',
            'jumlah_kependidikan' => 'required|integer|min:0',
            'bidang_studi' => 'nullable|string',
            'jumlah_siswa' => 'required|integer|min:0',
            'jumlah_siswa_pria' => 'required|integer|min:0',
            'jumlah_siswa_perempuan' => 'required|integer|min:0',
            'jumlah_siswa_per_kelas' => 'nullable|string',
            'jumlah_kelas' => 'required|integer|min:0',
            'laboratorium' => 'required|string',
            'perpustakaan' => 'required|string',
            'ruang_guru' => 'required|string',
            'jumlah_toilet' => 'required|integer|min:0',
            'lapangan_olahraga' => 'required|string',
            'fasilitas_it' => 'nullable|array',
            'akses_internet' => 'required|string',
            'jam_belajar' => 'required|string',
            'foto_depan' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'logo_sekolah' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'denah_lokasi' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'struktur_organisasi' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'ekstrakurikuler' => 'string',
            'program_unggulan' => 'string',
        ]);

        // Handle file uploads (hapus file lama jika ada)

        $foto_depan = $request->file('foto_depan');

        if ($request->hasFile('foto_depan')) {
            $ext = strtolower((string) $foto_depan->extension());
            $nameFoto = Str::uuid() . "." . $ext;
            $destinationPath = public_path('../../public_html/upload/sekolah/foto_depan/');

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $foto_depan->move($destinationPath, $nameFoto);

            $fileUrl = asset('../../public_html/upload/sekolah/foto_depan/' . $nameFoto);
            $validated['foto_depan'] = $nameFoto;
        }

        $logo_sekolah = $request->file('logo_sekolah');

        if ($request->hasFile('logo_sekolah')) {
            $ext = strtolower((string) $logo_sekolah->extension());
            $nameFoto = Str::uuid() . "." . $ext;
            $destinationPath = public_path('../../public_html/upload/sekolah/logo_sekolah/');

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $logo_sekolah->move($destinationPath, $nameFoto);

            $fileUrl = asset('../../public_html/upload/sekolah/logo_sekolah/' . $nameFoto);
            $validated['logo_sekolah'] = $nameFoto;
        }

        $denah_lokasi = $request->file('denah_lokasi');

        if ($request->hasFile('denah_lokasi')) {
            $ext = strtolower((string) $denah_lokasi->extension());
            $nameFoto = Str::uuid() . "." . $ext;
            $destinationPath = public_path('../../public_html/upload/sekolah/denah_lokasi/');

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $denah_lokasi->move($destinationPath, $nameFoto);

            $fileUrl = asset('../../public_html/upload/sekolah/denah_lokasi/' . $nameFoto);
            $validated['denah_lokasi'] = $nameFoto;
        }

        $struktur_organisasi = $request->file('struktur_organisasi');

        if ($request->hasFile('struktur_organisasi')) {
            $ext = strtolower((string) $struktur_organisasi->extension());
            $nameFoto = Str::uuid() . "." . $ext;
            $destinationPath = public_path('../../public_html/upload/sekolah/struktur_organisasi/');

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $struktur_organisasi->move($destinationPath, $nameFoto);

            $fileUrl = asset('../../public_html/upload/sekolah/struktur_organisasi/' . $nameFoto);
            $validated['struktur_organisasi'] = $nameFoto;
        }
        $user = Auth::user();
        $currentRole = strtolower(trim((string) ($user?->role ?? session('role'))));


        // Update data
        $sekolah->update($validated);

        if (in_array($currentRole, ['admin', 'superadmin', 'kepala', 'database'], true)) {
            return redirect()->route('admin.data-sekolah.index')
                ->with('message', 'update');
        }
        return redirect()->route('show.data-sekolah', $sekolah->id)
            ->with('message', 'sukses update data sekolah');
    }

    // Dashboard - tampilkan data sekolah user
    public function dashboard()
    {
        $sekolah = Sekolah::where('user_id', Auth::id())->first();

        return view('user.dashboard', compact('sekolah'));
    }

    private function createAkunKepalaSekolah($request, $sekolah)
    {
        // Generate username dari nama kepala sekolah
        // Hilangkan spasi dan special character, lowercase
        $username = strtolower(str_replace(' ', '', $request->nama_kepsek));
        $username = preg_replace('/[^a-z0-9]/', '', $username);

        // Cek apakah username sudah ada, jika ya tambahkan angka
        $originalUsername = $username;
        $counter = 1;
        while (User::where('username', $username)->exists()) {
            $username = $originalUsername . $counter;
            $counter++;
        }

        // Generate password default
        // Bisa menggunakan NPSN atau custom
        $passwordPlain = Str::random(16);

        // Data akun
        $dataAkun = [
            'name' => $request->nama_kepsek,
            'username' => $username,
            'no_ktp' => $request->nik_kepsek ?? $request->nip_kepsek, // Jika ada NIP, jika tidak pakai '-'
            'role' => 'tenaga kependidikan',
            'password' => bcrypt($passwordPlain),
            'email' => $request->email_kepsek ?? $request->email,
            'no_telepon' => $request->no_telp_kepsek,
        ];

        $dataGuru = [
            'nama_lengkap' => $request->nama_kepsek,
            'no_ktp' => $request->nik_kepsek,
            'nip' => $request->nip_kepsek,
            'email' => $request->email_kepsek,
            'no_hp' => $request->no_telp_kepsek,
            'no_wa' => $request->no_telp_kepsek,
            'is_verif' => 'sudah',
        ];

        $guru = Guru::create($dataGuru);

        // Buat di tabel Admin
        Admin::create($dataAkun);

        // Buat di tabel User
        $user = User::create($dataAkun);

        // Update user_id di sekolah ke user yang baru dibuat (opsional)
        $sekolah->update(['user_id' => $user->id]);

        return [
            'username' => $username,
            'password_plain' => $passwordPlain,
            'user_id' => $user->id
        ];
    }

    private function authorizeSchoolOwner(int|string $id): void
    {
        $sekolah = Sekolah::findOrFail($id);
        $currentUser = Auth::user();
        $currentUserId = $currentUser?->id;
        if (! $currentUserId && session('no_ktp')) {
            $currentUserId = User::where('no_ktp', session('no_ktp'))->value('id');
        }
        $currentRole = strtolower(trim((string) ($currentUser?->role ?? session('role'))));
        $isAdmin = in_array($currentRole, ['admin', 'superadmin', 'kepala', 'database'], true);

        abort_unless($isAdmin || ($currentUserId && (int) $sekolah->user_id === (int) $currentUserId), 403);
    }
}
