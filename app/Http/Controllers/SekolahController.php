<?php

namespace App\Http\Controllers;

use App\Exports\SekolahsExport;
use App\Models\Sekolah;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;


class SekolahController extends Controller
{
    private $menu;
    public function __construct()
    {
        $this->menu = 'sekolah';
    }
    public function getSekolahs(Request $request)
    {
        $perPage = min(max($request->integer('per_page', 50), 1), 100);
        $page = max($request->integer('page', 1), 1);
        $search = trim((string) $request->input('q', ''));

        $query = Sekolah::select('npsn_sekolah', 'nama_sekolah', 'kecamatan', 'kabupaten')
            ->when($search !== '', function ($query) use ($search) {
                return $query->where(function ($query) use ($search) {
                    $query->where('nama_sekolah', 'like', "%{$search}%")
                        ->orWhere('npsn_sekolah', 'like', "%{$search}%");
                });
            });

        $sekolahs = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json($sekolahs);
    }

    public function index()
    {
        try {
            $datas = Sekolah::where('nama_kepsek', '<>', '-')->get();
            $provinsiList = Sekolah::distinct()->where('provinsi', '<>', '-')->pluck('provinsi');
            $menu = $this->menu;

            return view('pages.admin.sekolah.index', compact('menu', 'datas', 'provinsiList'));
        } catch (\Exception $e) {
            report($e);

            return response()->json(['message' => 'Data sekolah tidak dapat diproses.'], 500);
        }
    }

    public function edit($id)
    {
        try {
            $sekolah = Sekolah::findOrFail($id);
            $menu = $this->menu;
            return view('pages.admin.sekolah.edit', compact('sekolah', 'menu'));
        } catch (\Exception $e) {
            report($e);

            return response()->json(['message' => 'Data sekolah tidak dapat diproses.'], 500);
        }
    }

    public function export(Request $request)
    {
        try {
            $filters = [
                'provinsi' => $request->input('provinsi'),
                'status_sekolah' => $request->input('status_sekolah'),
                'akreditasi' => $request->input('akreditasi'),
            ];
            
            $filters = array_filter($filters);
            
            $filename = 'Data_Sekolah';
            if (!empty($filters)) {
                $filename .= '_Filtered';
            }
            $filename .= '_' . date('Y-m-d_His') . '.xlsx';

            return Excel::download(new SekolahsExport($filters), $filename);
        } catch (\Exception $e) {
            report($e);

            return response()->json(['message' => 'Export data sekolah gagal.'], 500);
        }
    }
}
