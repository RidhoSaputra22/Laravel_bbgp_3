<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\PesertaKegiatan;
use App\Models\Rtl;
use App\Models\RtlDocument;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RtlController extends Controller
{
    public function index()
    {
        $role = session('role');
        $no_ktp = session('no_ktp');

        if (in_array($role, ['admin', 'superadmin', 'kepala', 'database', 'panitia'], true)) {
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
                // Simpan dokumen sensitif di disk privat.
                $path = $file->store('rtl', 'private_uploads');

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
        abort_unless(in_array(strtolower(trim((string) session('role'))), ['admin', 'superadmin', 'kepala', 'database', 'panitia'], true), 403);

        $rtl = Rtl::findOrFail($id);

        if ($request->has('status')) {
            $validated = $request->validate([
                'status' => 'required|in:pending,approved,rejected',
                'admin_notes' => 'nullable|string|max:5000',
                'certificate_data' => 'nullable|array',
                'certificate_data.number' => 'nullable|string|max:255',
                'certificate_data.participantName' => 'nullable|string|max:255',
                'certificate_data.institution' => 'nullable|string|max:255',
                'certificate_data.participantRole' => 'nullable|string|max:255',
                'certificate_data.activityName' => 'nullable|string|max:500',
                'certificate_data.activityDate' => 'nullable|string|max:255',
                'certificate_data.activityPlace' => 'nullable|string|max:255',
                'certificate_data.issueDate' => 'nullable|string|max:255',
                'certificate_data.signerName' => 'nullable|string|max:255',
                'certificate_data.signerNip' => 'nullable|string|max:255',
                'certificate_data.qrLink' => 'nullable|string|max:2048',
                'certificate_data.qrImage' => 'nullable|string|max:300000',
                'certificate_data.materialsTitle' => 'nullable|string|max:500',
                'certificate_data.materials' => 'nullable|array|max:50',
                'certificate_data.materials.*' => 'nullable|array',
                'certificate_data.materials.*.materi' => 'nullable|string|max:500',
                'certificate_data.materials.*.jp' => 'nullable|numeric|min:0|max:999',
            ]);

            $rtl->status = $validated['status'];
            $rtl->admin_notes = $validated['admin_notes'] ?? null;
            if (array_key_exists('certificate_data', $validated)) {
                $rtl->certificate_data = $this->certificateData($rtl, $validated['certificate_data'] ?? []);
            }

            if ($validated['status'] === 'approved') {
                $rtl->certificate_data = $this->certificateData($rtl, $rtl->certificate_data ?? []);
                $rtl->certificate_file = $this->generateCertificate($rtl, $rtl->certificate_data);
            }

            $rtl->save();
            return redirect()->back()->with('message', 'Status pengajuan RTL telah diperbarui.');
        }

        return redirect()->back()->with('error', 'Gagal memproses pengajuan.');
    }

    private function generateCertificate(Rtl $rtl, array $certificateData): string
    {
        $rtl->loadMissing(['kegiatan.penomoran', 'user']);
        $backgroundPath = public_path('img_template/bg-sertifikat.png');
        $materialsBackgroundPath = public_path('img_template/bg-sertifikat-2.png');
        $logoPath = public_path('img/logo-tutwuri.png');
        $qualityLogoPath = public_path('img/pendidikan-bermutu.png');
        $ramahLogoPath = public_path('img/kemendikdasmen-ramah.png');

        abort_unless(
            is_file($backgroundPath) && is_file($materialsBackgroundPath) && is_file($logoPath)
                && is_file($qualityLogoPath) && is_file($ramahLogoPath),
            500,
            'Template sertifikat tidak ditemukan.'
        );

        $pdf = Pdf::loadView('pages.user.rtl.certificate', [
            'background' => base64_encode(file_get_contents($backgroundPath)),
            'materialsBackground' => base64_encode(file_get_contents($materialsBackgroundPath)),
            'logo' => base64_encode(file_get_contents($logoPath)),
            'topLogoLeft' => base64_encode(file_get_contents($qualityLogoPath)),
            'topLogoRight' => base64_encode(file_get_contents($ramahLogoPath)),
            ...$certificateData,
        ])->setPaper('a4', 'landscape');

        $path = 'sertifikat/rtl-'.$rtl->id.'.pdf';
        Storage::disk('private_uploads')->put($path, $pdf->output());
        if ($rtl->certificate_file && $rtl->certificate_file !== $path) {
            Storage::disk('private_uploads')->delete($rtl->certificate_file);
        }

        return $path;
    }

    private function certificateData(Rtl $rtl, array $overrides = []): array
    {
        $rtl->loadMissing(['kegiatan.penomoran', 'user']);

        $participant = PesertaKegiatan::query()
            ->where('no_ktp', $rtl->no_ktp)
            ->where('id_kegiatan', $rtl->id_kegiatan)
            ->first();

        $activity = $rtl->kegiatan;
        $startDate = Carbon::parse($activity?->tgl_kegiatan ?? now());
        $endDate = $activity?->tgl_selesai ? Carbon::parse($activity->tgl_selesai) : null;
        $defaults = [
            'number' => $activity?->penomoran?->no_surat ?: sprintf('%04d/BBGTK-SULSEL/RTL/%d', $rtl->id, $startDate->year),
            'participantName' => $participant?->nama ?: ($rtl->user?->nama_lengkap ?: $rtl->no_ktp),
            'institution' => $participant?->instansi ?: ($rtl->user?->satuan_pendidikan ?: '-'),
            'participantRole' => ucfirst($participant?->status_keikutpesertaan ?: 'peserta'),
            'activityName' => $activity?->nama_kegiatan ?: 'Kegiatan BBGTK Sulawesi Selatan',
            'activityDate' => $this->formatCertificateDate($startDate, $endDate),
            'activityPlace' => $activity?->tempat_kegiatan ?: 'Makassar',
            'issueDate' => $this->formatCertificateDate(Carbon::now()),
            'signerName' => 'Dr. Arman Agung, M.Pd.',
            'signerNip' => '196910112001121002',
        ];

        foreach ($defaults as $key => $default) {
            $value = $overrides[$key] ?? null;
            $defaults[$key] = is_string($value) && trim($value) !== '' ? trim($value) : $default;
        }

        $defaults['qrLink'] = trim((string) ($overrides['qrLink'] ?? ''));
        $qrImage = trim((string) ($overrides['qrImage'] ?? ''));
        $defaults['qrImage'] = $defaults['qrLink'] !== '' && str_starts_with($qrImage, 'data:image/')
            ? $qrImage
            : '';

        $defaults['materialsTitle'] = is_string($overrides['materialsTitle'] ?? null)
            && trim($overrides['materialsTitle']) !== ''
            ? trim($overrides['materialsTitle'])
            : $defaults['activityName'];
        $defaults['materials'] = $this->certificateMaterials($overrides['materials'] ?? []);

        return $defaults;
    }

    private function certificateMaterials(mixed $materials): array
    {
        if (!is_array($materials)) {
            return [];
        }

        return array_values(array_filter(array_map(function ($material) {
            if (!is_array($material)) {
                return null;
            }

            $name = trim((string) ($material['materi'] ?? ''));
            $jp = trim((string) ($material['jp'] ?? ''));
            if ($name === '' && $jp === '') {
                return null;
            }

            return [
                'materi' => $name,
                'jp' => $jp,
            ];
        }, $materials)));
    }

    private function formatCertificateDate(Carbon $start, ?Carbon $end = null): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $startLabel = $start->day.' '.$months[$start->month].' '.$start->year;
        if (!$end || $end->isSameDay($start)) {
            return $startLabel;
        }

        if ($end->year === $start->year && $end->month === $start->month) {
            return $start->day.' s.d. '.$end->day.' '.$months[$end->month].' '.$end->year;
        }

        return $startLabel.' s.d. '.$end->day.' '.$months[$end->month].' '.$end->year;
    }

    public function deleteDocument($id)
    {
        $doc = RtlDocument::findOrFail($id);
        $isAdmin = in_array(strtolower(trim((string) session('role'))), ['admin', 'superadmin', 'kepala', 'database', 'panitia'], true);
        abort_unless($isAdmin || (string) $doc->rtl->no_ktp === (string) session('no_ktp'), 403);
        if ($doc->rtl->status == 'approved') {
            return response()->json(['success' => false, 'message' => 'Tidak bisa menghapus dokumen yang sudah disetujui.']);
        }

        // Hapus file dari storage
        Storage::disk('private_uploads')->delete('rtl/'.basename($doc->file_path));
        $doc->delete();

        return response()->json(['success' => true]);
    }

    public function show($id)
    {
        $rtl = Rtl::with(['kegiatan', 'user', 'documents'])->findOrFail($id);

        $this->authorizeRtl($rtl);

        $rtl->certificate_data = $this->certificateData($rtl, $rtl->certificate_data ?? []);

        return response()->json($rtl);
    }

    public function document($id)
    {
        $document = RtlDocument::with('rtl')->findOrFail($id);
        $this->authorizeRtl($document->rtl);

        $path = 'rtl/'.basename($document->file_path);
        $disk = Storage::disk('private_uploads');

        abort_unless($disk->exists($path), 404);

        return response()->file($disk->path($path));
    }

    public function certificate($id)
    {
        $rtl = Rtl::findOrFail($id);
        $this->authorizeRtl($rtl);

        abort_unless($rtl->certificate_file, 404);

        $path = 'sertifikat/'.basename($rtl->certificate_file);
        $disk = Storage::disk('private_uploads');

        abort_unless($disk->exists($path), 404);

        return response()->file($disk->path($path));
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
                'file_path' => $file->store('rtl', 'private_uploads'),
                'original_name' => $file->getClientOriginalName(),
            ]);
        }

        $rtl->update(['status' => 'pending']);

        return redirect()->back()->with('message', 'Dokumen RTL berhasil diunggah.');
    }

    private function authorizeRtl(Rtl $rtl): void
    {
        $isAdmin = in_array(strtolower(trim((string) session('role'))), ['admin', 'superadmin', 'kepala', 'database', 'panitia'], true);

        abort_unless($isAdmin || (string) $rtl->no_ktp === (string) session('no_ktp'), 403);
    }
}
