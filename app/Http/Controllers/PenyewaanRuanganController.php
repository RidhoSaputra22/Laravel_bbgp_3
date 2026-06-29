<?php

namespace App\Http\Controllers;

use App\Models\PenyewaanRuangan;
use Illuminate\Http\Request;

class PenyewaanRuanganController extends Controller
{
    private $menu;

    public function __construct()
    {
        $this->menu = 'penyewaan';
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $datas = PenyewaanRuangan::orderBy('created_at', 'desc')->get();
        $menu = $this->menu;

        return view('pages.admin.penyewaan.index', compact('datas', 'menu'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $menu = $this->menu;
        return view('pages.admin.penyewaan.create', compact('menu'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'tipe_ruangan' => 'required|in:asrama,aula,kelas,laboratorium',
            'nama_ruangan' => 'required|string|max:255',
            'harga_per_malam' => 'nullable|numeric|min:0',
            'rincian_harga' => 'nullable|string',
            'foto_utama' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'required|in:tersedia,tidak_tersedia,maintenance',
        ]);

        // Handle file upload
        if ($request->hasFile('foto_utama')) {
            $file = $request->file('foto_utama');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('../../public_html/upload/penyewaan'), $filename);
            $validated['foto_utama'] = $filename;
        }

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        PenyewaanRuangan::create($validated);

        return redirect()->route('penyewaan.index')
            ->with('success', 'Data ruangan berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = PenyewaanRuangan::findOrFail($id);
        $menu = $this->menu;

        return view('pages.admin.penyewaan.show', compact('data', 'menu'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = PenyewaanRuangan::findOrFail($id);
        $menu = $this->menu;

        return view('pages.admin.penyewaan.edit', compact('data', 'menu'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = PenyewaanRuangan::findOrFail($id);

        $validated = $request->validate([
            'tipe_ruangan' => 'required|in:asrama,aula,kelas,laboratorium',
            'nama_ruangan' => 'required|string|max:255',
            'harga_per_malam' => 'nullable|numeric|min:0',
            'rincian_harga' => 'nullable|string',
            'foto_utama' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'required|in:tersedia,tidak_tersedia,maintenance',
        ]);

        // Handle file upload
        if ($request->hasFile('foto_utama')) {
            // Delete old file
            if ($data->foto_utama && file_exists(public_path('../../public_html/upload/penyewaan/' . $data->foto_utama))) {
                unlink(public_path('../../public_html/upload/penyewaan/' . $data->foto_utama));
            }

            $file = $request->file('foto_utama');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('../../public_html/upload/penyewaan/'), $filename);
            $validated['foto_utama'] = $filename;
        }

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        $data->update($validated);

        return redirect()->route('penyewaan.index')
            ->with('success', 'Data ruangan berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = PenyewaanRuangan::findOrFail($id);

        // Delete file
        if ($data->foto_utama && file_exists(public_path('upload/penyewaan/' . $data->foto_utama))) {
            unlink(public_path('upload/penyewaan/' . $data->foto_utama));
        }

        $data->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data ruangan berhasil dihapus'
        ]);
    }

    /**
     * Public view for landing page
     */
    public function landing()
    {
        $menu = $this->menu;

        $asramas = PenyewaanRuangan::where('tipe_ruangan', 'asrama')
            ->where('is_active', 1)
            ->where('status', 'tersedia')
            ->orderBy('nama_ruangan', 'asc')
            ->get();

        $aulas = PenyewaanRuangan::where('tipe_ruangan', 'aula')
            ->where('is_active', 1)
            ->where('status', 'tersedia')
            ->orderBy('nama_ruangan', 'asc')
            ->get();

        $kelas = PenyewaanRuangan::where('tipe_ruangan', 'kelas')
            ->where('is_active', 1)
            ->where('status', 'tersedia')
            ->orderBy('nama_ruangan', 'asc')
            ->get();

        $laboratoriums = PenyewaanRuangan::where('tipe_ruangan', 'laboratorium')
            ->where('is_active', 1)
            ->where('status', 'tersedia')
            ->orderBy('nama_ruangan', 'asc')
            ->get();

        return view('pages.landing.penyewaan-fasilitas.index', compact(
            'asramas',
            'aulas',
            'kelas',
            'laboratoriums',
            'menu'
        ));
    }
}
