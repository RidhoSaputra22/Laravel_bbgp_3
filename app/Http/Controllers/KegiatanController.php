<?php

namespace App\Http\Controllers;

use App\Models\GolonganP3k;
use App\Models\Guru;
use App\Models\Kegiatan;
use App\Models\PesertaKegiatan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class KegiatanController extends Controller
{
    private $menu;

    public function __construct()
    {
        $this->menu = 'kegiatan';
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $datas = $this->scopedActivities()->orderBy('id', 'DESC')->get();
        $menu = $this->menu;
        
        return view('pages.admin.kegiatan.index', compact('menu', 'datas'));
    }
    
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $menu = $this->menu;
        
        return view('pages.admin.kegiatan.create', compact('menu'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $r)
    {
        
        $mulai_kegiatan = explode(" ", $r["mulai_kegiatan"]);
        $r['tgl_kegiatan'] = $mulai_kegiatan[0];
        $r['jam_mulai'] = $mulai_kegiatan[1];
        
        $selesai_kegiatan = explode(" ", $r["selesai_kegiatan"]);
        $r['tgl_selesai'] = $selesai_kegiatan[0];
        $r['jam_selesai'] = $selesai_kegiatan[1];

        if ($kabupaten = $this->kabupaten()) {
            $r['kabupaten'] = $kabupaten;
        }
        
        
        Kegiatan::create($r->all());

        return redirect()->route('kegiatan.index')->with('message', 'store');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $datas = $this->scopedActivities()->findOrFail($id);
        $menu = $this->menu;
        $datetime = array(
            'mulai_kegiatan' => $datas->tgl_kegiatan . ' ' . $datas->jam_mulai,
            'selesai_kegiatan' => $datas->tgl_selesai . ' ' . $datas->jam_selesai,
        );

        return view('pages.admin.kegiatan.edit', compact('datas', 'menu', 'datetime'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $r)
    {
        $mulai_kegiatan = explode(" ", $r["mulai_kegiatan"]);
        $r['tgl_kegiatan'] = $mulai_kegiatan[0];
        $r['jam_mulai'] = $mulai_kegiatan[1];
        
        $selesai_kegiatan = explode(" ", $r["selesai_kegiatan"]);
        $r['tgl_selesai'] = $selesai_kegiatan[0];
        $r['jam_selesai'] = $selesai_kegiatan[1];

        if ($kabupaten = $this->kabupaten()) {
            $r['kabupaten'] = $kabupaten;
        }

        $datas = $this->scopedActivities()->findOrFail($r->id);
        $datas->update($r->all());
        $menu = $this->menu;

        return redirect()->route('kegiatan.index')->with('message', 'update');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = $this->scopedActivities()->findOrFail($id);
        $data->delete();
        return response()->json($data);
    }

    public function printLaporanKegiatan(Request $request)
    {
        $filters = $request->validate([
            'kegiatan_id' => ['nullable', 'integer', 'exists:kegiatans,id'],
            'tanggal_dari' => ['nullable', 'date'],
            'tanggal_sampai' => ['nullable', 'date', 'after_or_equal:tanggal_dari'],
        ]);

        $activities = $this->scopedActivities()
            ->when($filters['kegiatan_id'] ?? null, fn ($query, $id) => $query->whereKey($id))
            ->when($filters['tanggal_dari'] ?? null, fn ($query, $date) => $query->whereDate('tgl_kegiatan', '>=', $date))
            ->when($filters['tanggal_sampai'] ?? null, fn ($query, $date) => $query->whereDate('tgl_kegiatan', '<=', $date))
            ->orderBy('tgl_kegiatan')
            ->orderBy('id')
            ->get();

        $reports = $activities->map(function (Kegiatan $activity) {
            $participants = PesertaKegiatan::query()
                ->where('id_kegiatan', $activity->id)
                ->when(
                    $this->kabupaten(),
                    fn ($query, $kabupaten) => $query->where('kabupaten', $kabupaten)
                );

            $countByStatus = fn (string $status) => (clone $participants)
                ->where('status_keikutpesertaan', $status)
                ->count();

            $countByExternalRole = function (string $role) use ($participants) {
                $nips = Guru::query()
                    ->where('eksternal_jabatan', $role)
                    ->whereNotNull('no_ktp')
                    ->pluck('no_ktp');

                return (clone $participants)->whereIn('no_ktp', $nips)->count();
            };

            return [
                'kegiatan' => $activity,
                'peserta' => $countByStatus('peserta'),
                'panitia' => $countByStatus('panitia'),
                'tenaga_pendidik' => $countByExternalRole('Tenaga Pendidik'),
                'tenaga_kependidikan' => $countByExternalRole('Tenaga Kependidikan'),
                'stakeholder' => $countByExternalRole('Stakeholder'),
                'narasumber' => $countByStatus('narasumber'),
            ];
        });

        $pdf = Pdf::loadView('pages.admin.kegiatan.cetak.laporan-kegiatan', [
            'reports' => $reports,
            'filters' => $filters,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('laporan-kegiatan.pdf');
    }

    private function kabupaten(): ?string
    {
        $user = Auth::user();

        return $user?->role === 'pegawai-kabupaten'
            ? ($user->kabupaten ?: session('kabupaten'))
            : null;
    }

    private function scopedActivities()
    {
        return Kegiatan::query()->when(
            $this->kabupaten(),
            fn ($query, $kabupaten) => $query->where('kabupaten', $kabupaten)
        );
    }
}
