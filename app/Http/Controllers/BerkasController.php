<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Berkas;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BerkasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if ($this->canManageAllBerkas()) {
            $users = Pegawai::with(['berkas' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }])
                ->whereHas('berkas')
                ->get();
            return view('pages.admin.berkas.index', [
                'datas' => $users,
                'menu' => 'berkas'
            ]);
        }

        $datas = Berkas::where('nik', session('no_ktp'))
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.admin.berkas.index', [
            'datas' => $datas,
            'menu' => 'berkas'
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $r)
    {
        $validated = $r->validate([
            'nama_berkas' => 'nullable|file|mimes:pdf,doc,docx|max:10024',
            'nama_link' => 'nullable|url:http,https|max:2048',
            'nama_kegiatan' => 'required|string|max:255',
            'metode_upload' => 'required|in:upload,link',
        ]);

        abort_unless(session('no_ktp'), 403);

        try {
            $berkas = new Berkas();
            if ($r->hasFile('nama_berkas')) {
                $file = $r->file('nama_berkas');
                $fileName = Str::uuid() . '.' . $file->extension();
                Storage::disk('public')->putFileAs('berkas', $file, $fileName);
                $validated['nama_berkas'] = $fileName;
            } else {
                abort_unless(!empty($validated['nama_link']), 422, 'Link laporan wajib diisi.');
                $validated['nama_berkas'] = $validated['nama_link'];
            }

            $berkas->nama_berkas = $validated['nama_berkas'];
            $berkas->nama_kegiatan = $validated['nama_kegiatan'];
            $berkas->metode_upload = $validated['metode_upload'];
            $berkas->status = 'proses';
            $berkas->nik = session('no_ktp');
            $berkas->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Laporan telah di buat'
            ]);
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan berkas.'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = Berkas::findOrFail($id);
        $this->authorizeBerkas($data);

        return response()->json([
            'data' => $data
        ]);
        // return view('pages.admin.berkas.index', ['menu' => 'berkas'])->with('datas', json_encode($data));
    }

    // public function verifikasi(string $id)
    // {

    //     $data = Berkas::find($id);
    //     $getData = Berkas::find($id);
    //     $data->is_verif = 'sudah';
    //     $data->save();
    //     return response()->json([
    //         'status' => $data,
    //         'data' => $getData,
    //     ]);
    // }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $r)
    {
        $validated = $r->validate([
            'formId' => 'required|integer|exists:berkas,id',
            'nama_berkas' => 'nullable|file|mimes:pdf,doc,docx|max:10024',
            'nama_link' => 'nullable|url:http,https|max:2048',
            'nama_kegiatan' => 'required|string|max:255',
            'metode_upload' => 'required|in:upload,link',
        ]);

        $data = Berkas::findOrFail($validated['formId']);
        $this->authorizeBerkas($data);

        try {
            if ($r->hasFile('nama_berkas')) {
                $file = $r->file('nama_berkas');
                $fileName = Str::uuid() . '.' . $file->extension();
                Storage::disk('public')->putFileAs('berkas', $file, $fileName);
                $validated['nama_berkas'] = $fileName;
            } elseif (!empty($validated['nama_link'])) {
                $validated['nama_berkas'] = $validated['nama_link'];
            } else {
                $validated['nama_berkas'] = $data->nama_berkas;
            }

            $data->update([
                'metode_upload' => $validated['metode_upload'],
                'nama_kegiatan' => $validated['nama_kegiatan'],
                'nama_berkas' => $validated['nama_berkas'],
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Data updated successfully'
            ]);
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to upload file'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = Berkas::findOrFail($id);
        $this->authorizeBerkas($data);
        if ($data->nama_berkas && !filter_var($data->nama_berkas, FILTER_VALIDATE_URL)) {
            Storage::disk('public')->delete('berkas/' . basename($data->nama_berkas));
        }
        $data->delete();
        return response()->json($data);
    }

    public function verify($id)
    {
        $this->authorizeAdmin();
        $berkas = Berkas::findOrFail($id);

        try {
            $berkas->update(['status' => 'selesai']);

            return response()->json([
                'status' => 'success',
                'message' => 'Berkas berhasil diverifikasi'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memverifikasi berkas'
            ], 500);
        }
    }

    private function authorizeBerkas(Berkas $berkas): void
    {
        abort_unless(
            $this->canManageAllBerkas() || (string) $berkas->nik === (string) session('no_ktp'),
            403
        );
    }

    private function authorizeAdmin(): void
    {
        abort_unless($this->canManageAllBerkas(), 403);
    }

    private function canManageAllBerkas(): bool
    {
        $role = auth()->user()?->role ?? session('role');

        return in_array(strtolower(trim((string) $role)), [
            'admin',
            'superadmin',
            'kepala',
            'database',
        ], true);
    }
}
