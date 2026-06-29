<?php

namespace App\Http\Controllers;

use App\Exports\SekolahsExport;
use App\Models\Sekolah;
use Illuminate\Http\Request;
// use Maatwebsite\Excel\Excel;
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
        $perPage = $request->input('per_page', 500); // Items per page, default to 500
        $page = $request->input('page', 1); // Current page
        $search = $request->input('q', ''); // Search term

        $query = Sekolah::select('npsn_sekolah', 'nama_sekolah', 'kecamatan', 'kabupaten')
            ->when($search, function ($query, $search) {
                return $query->where('nama_sekolah', 'like', "%$search%")
                    ->orWhere('npsn_sekolah', 'like', "%$search%");
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
            return response()->json($e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $sekolah = Sekolah::findOrFail($id);
            $menu = $this->menu;
            return view('pages.admin.sekolah.edit', compact('sekolah', 'menu'));
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
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
            return response()->json($e->getMessage());
        }
    }
}
