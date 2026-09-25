<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\PesertaKegiatan;
use App\Models\Rtl;
use App\Models\RtlDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RtlController extends Controller
{
    public function index()
    {
        $role = session('role');
        $no_ktp = session('no_ktp');

        if (in_array($role, ['admin', 'superadmin', 'kepala', 'database'], true)) {
            $rtls = Rtl::with(['kegiatan', 'user', 'documents'])->orderByDesc('created_at')->get();
            return view('pages.admin.rtl.index', [
                'menu' => 'rtl',
                'datas' => $rtls
            ]);
        } else {
            // External roles
            $rtls = Rtl::with(['kegiatan', 'documents'])->where('no_ktp', $no_ktp)->orderByDesc('created_at')->get();
            
            // Get activities the user participated in but hasn't submitted RTL for
            $submittedKegiatanIds = $rtls->pluck('id_kegiatan')->unique()->toArray();
            
            // Fetch activities through PesertaKegiatan
            $myKegiatans = PesertaKegiatan::where('no_ktp', $no_ktp)
                ->whereHas('kegiatan')
                ->get()
                ->map(function($peserta) {
                    return $peserta->kegiatan;
                })
                ->filter()
                ->unique('id')
                ->whereNotIn('id', $submittedKegiatanIds);

            return view('pages.user.rtl.index', [
                'menu' => 'rtl',
                'datas' => $rtls,
                'availableKegiatans' => $myKegiatans
            ]);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_kegiatan' => 'required|integer|exists:kegiatans,id',
            'files' => 'required|array|max:10',
            'files.*' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        $no_ktp = session('no_ktp');
        
        if (!$no_ktp) {
            return redirect()->back()->with('error', 'Sesi Anda berakhir. Silakan login kembali.');
        }

        abort_unless(PesertaKegiatan::where('no_ktp', $no_ktp)
            ->where('id_kegiatan', $request->integer('id_kegiatan'))
            ->exists(), 403);

        $rtl = Rtl::where('no_ktp', $no_ktp)
                  ->where('id_kegiatan', $request->id_kegiatan)
                  ->first();

        if (!$rtl) {
            $rtl = Rtl::create([
                'no_ktp' => $no_ktp,
                'id_kegiatan' => $request->id_kegiatan,
                'status' => 'pending',
            ]);
        } else if ($rtl->status == 'approved') {
            return redirect()->back()->with('error', 'RTL untuk kegiatan ini sudah disetujui.');
        }

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                // Gunakan Storage Disk Public
                $path = $file->store('rtl', 'public');

                RtlDocument::create([
                    'rtl_id' => $rtl->id,
                    'file_path' => $path, // Simpan path dari storage (misal: rtl/namafile.pdf)
                    'original_name' => $file->getClientOriginalName(),
                ]);
            }
            
            $rtl->status = 'pending';
            $rtl->save();
        }

        return redirect()->back()->with('message', 'Dokumen RTL berhasil diunggah.');
    }

    public function update(Request $request, $id)
    {
        abort_unless(in_array(strtolower(trim((string) session('role'))), ['admin', 'superadmin', 'kepala', 'database'], true), 403);

        $rtl = Rtl::findOrFail($id);

        if ($request->has('status')) {
            $validated = $request->validate([
                'status' => 'required|in:pending,approved,rejected',
                'admin_notes' => 'nullable|string|max:5000',
                'certificate' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            ]);

            $rtl->status = $validated['status'];
            $rtl->admin_notes = $validated['admin_notes'] ?? null;

            if ($validated['status'] == 'approved' && $request->hasFile('certificate')) {
                // Simpan sertifikat ke storage
                $path = $request->file('certificate')->store('sertifikat', 'public');
                $rtl->certificate_file = $path;
            }

            $rtl->save();
            return redirect()->back()->with('message', 'Status pengajuan RTL telah diperbarui.');
        }

        return redirect()->back()->with('error', 'Gagal memproses pengajuan.');
    }

    public function deleteDocument($id)
    {
        $doc = RtlDocument::findOrFail($id);
        $isAdmin = in_array(strtolower(trim((string) session('role'))), ['admin', 'superadmin', 'kepala', 'database'], true);
        abort_unless($isAdmin || (string) $doc->rtl->no_ktp === (string) session('no_ktp'), 403);
        if ($doc->rtl->status == 'approved') {
            return response()->json(['success' => false, 'message' => 'Tidak bisa menghapus dokumen yang sudah disetujui.']);
        }

        // Hapus file dari storage
        Storage::disk('public')->delete($doc->file_path);
        $doc->delete();

        return response()->json(['success' => true]);
    }

    public function show($id)
    {
        $rtl = Rtl::with(['kegiatan', 'user', 'documents'])->findOrFail($id);

        $isAdmin = in_array(strtolower(trim((string) session('role'))), ['admin', 'superadmin', 'kepala', 'database'], true);
        abort_unless($isAdmin || (string) $rtl->no_ktp === (string) session('no_ktp'), 403);

        return response()->json($rtl);
    }

    public function reupload(Request $request, $id)
    {
        $rtl = Rtl::findOrFail($id);
        abort_unless((string) $rtl->no_ktp === (string) session('no_ktp'), 403);

        $request->validate([
            'files' => 'required|array|max:10',
            'files.*' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        abort_if($rtl->status === 'approved', 422, 'RTL yang sudah disetujui tidak dapat diubah.');

        foreach ($request->file('files') as $file) {
            RtlDocument::create([
                'rtl_id' => $rtl->id,
                'file_path' => $file->store('rtl', 'public'),
                'original_name' => $file->getClientOriginalName(),
            ]);
        }

        $rtl->update(['status' => 'pending']);

        return redirect()->back()->with('message', 'Dokumen RTL berhasil diunggah.');
    }
}
