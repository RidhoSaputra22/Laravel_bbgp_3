<?php

namespace App\Http\Controllers;

use App\Models\GolonganP3k;
use App\Models\Kegiatan;
use Illuminate\Http\Request;

class KegiatanController extends Controller
{
    private $menu;
    public function __construct() {
        $this->menu = 'kegiatan';
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $datas = Kegiatan::orderBy('id', 'DESC')->get();
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
        
        // dd($r->all());
        
        Kegiatan::create($r->all());

        return redirect()->route('kegiatan.index')->with('message', 'store');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $datas = Kegiatan::find($id);
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

        $datas = Kegiatan::find($r->id);
        // dd($r->all());
        $datas->update($r->all());
        $menu = $this->menu;

        return redirect()->route('kegiatan.index')->with('message', 'update');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = Kegiatan::find($id);
        $data->delete();
        return response()->json($data);
    }
}
