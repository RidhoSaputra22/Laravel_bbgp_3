<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use Illuminate\Http\Request;

class AgendaController extends Controller
{

    private $menu = 'agenda';

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $datas = Agenda::get();
        $menu = $this->menu;
        return view('pages.admin.agenda.index', compact('menu', 'datas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $menu = $this->menu;
        return view('pages.admin.agenda.create', compact('menu'));
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
            return redirect()->route('agenda.create')->with('message', 'size gambar');
        }

        $foto = $request->file('thumbnail');
        $ext = $foto->getClientOriginalExtension();
        // $r['pas_foto'] = $request->file('pas_foto');

        $nameFoto = date('Y-m-d_H-i-s_') . "." . $ext;
        $destinationPath = public_path('upload/agenda');

        $foto->move($destinationPath, $nameFoto);

        $fileUrl = asset('upload/agenda/' . $nameFoto);
        // dd($destinationPath);
        $r['thumbnail'] = $nameFoto;
        $r['nama_kegiatan'] = $r['judul'];
        $r['tempat_kegiatan'] = $r['lokasi_kegiatan'];
        // dd($r);
        Agenda::create($r);


        return redirect()->route('agenda.index')->with('message', 'store');
    }

    /**
     * Display the specified resource.
     */
    public function show(Agenda $agenda)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $data = Agenda::find($id);
        $menu = $this->menu;

        return view('pages.admin.agenda.edit', compact('data', 'menu'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $r = $request->all();
        $data = Agenda::find($r['id']);

        $foto = $request->file('thumbnail');



        if ($request->hasFile('thumbnail')) {
            if ($foto->getSize() / 1024 >= 512) {
                return redirect()->route('agenda.edit', $r['id'])->with('message', 'size gambar');
            }
            $ext = $foto->getClientOriginalExtension();
            $nameFoto = date('Y-m-d_H-i-s_') . "." . $ext;
            $destinationPath = public_path('upload/agenda');

            $foto->move($destinationPath, $nameFoto);

            $fileUrl = asset('upload/agenda/' . $nameFoto);
            $r['thumbnail'] = $nameFoto;
        } else {
            $r['thumbnail'] = $request->thumbnail_old;
        }

        $r['nama_kegiatan'] = $r['judul'];
        $r['tempat_kegiatan'] = $r['lokasi_kegiatan'];

        // dd($r);
        $data->update($r);

        return redirect()->route('agenda.index')->with('message', 'update');


    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $data = Agenda::find($id);
        $data->delete();
        return response()->json($data);
    }
}
