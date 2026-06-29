<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use Illuminate\Http\Request;

class ArtikelController extends Controller
{
    private $menu;
    public function __construct()
    {
        $this->menu = 'artikel';
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $datas = Artikel::get();
        $menu = $this->menu;


        return view('pages.admin.artikel.index', compact('menu', 'datas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $menu = $this->menu;
        return view('pages.admin.artikel.create', compact('menu'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $r = $request->all();

        $file = $request->file('thumbnail');

        // dd($file->getSize() / 1024);
        if ($file->getSize() / 1024 >= 512) {
            return redirect()->route('artikel.create')->with('message', 'size gambar');
        }

        $foto = $request->file('thumbnail');
        $ext = $foto->getClientOriginalExtension();
        // $r['pas_foto'] = $request->file('pas_foto');

        $nameFoto = date('Y-m-d_H-i-s_') . str_replace(' ', '-', $r['judul']) . "." . $ext;
        $destinationPath = public_path('upload/artikel');

        $foto->move($destinationPath, $nameFoto);

        $fileUrl = asset('upload/artikel/' . $nameFoto);
        // dd($destinationPath);
        $r['thumbnail'] = $nameFoto;

        Artikel::create($r);


        // dd(true);
        return redirect()->route('artikel.index')->with('message', 'store');
    }

    /**
     * Display the specified resource.
     */
    public function show(Artikel $artikel)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = Artikel::find($id);
        $menu = $this->menu;

        return view('pages.admin.artikel.edit', compact('data', 'menu'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $r = $request->all();
        $data = Artikel::find($r['id']);

        $foto = $request->file('thumbnail');


        // dd($file->getSize() / 1024);

        if ($request->hasFile('thumbnail')) {
            if ($foto->getSize() / 1024 >= 512) {
                return redirect()->route('artikel.edit', $r['id'])->with('message', 'size gambar');
            }
            $ext = $foto->getClientOriginalExtension();
            $nameFoto = date('Y-m-d_H-i-s_') . $r['judul'] . "." . $ext;
            $destinationPath = public_path('upload/artikel');

            $foto->move($destinationPath, $nameFoto);

            $fileUrl = asset('upload/artikel/' . $nameFoto);
            $r['thumbnail'] = $nameFoto;
        } else {
            $r['thumbnail'] = $request->thumbnail_old;
        }

        // dd($r);
        $data->update($r);

        return redirect()->route('artikel.index')->with('message', 'update');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = Artikel::find($id);
        $data->delete();
        return response()->json($data);
    }
}
