<?php

namespace App\Http\Controllers;

use App\Models\SatuanPendidikan;
use Illuminate\Http\Request;

class KependidikanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = SatuanPendidikan::get();
        return view('pages.admin.status.kependidikan.index', ['menu' => 'kependidikan', 'datas' => $data]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        SatuanPendidikan::create($validated);

        return redirect()->route('kependidikan.index')->with('message', 'store');
    }

    /**
     * Display the specified resource.
     */

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = SatuanPendidikan::find($id);

        return view('pages.admin.status.kependidikan.edit', ['menu' => 'kependidikan', 'datas' => $data]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'id' => ['required', 'integer', 'exists:satuan_pendidikans,id'],
            'name' => ['required', 'string', 'max:255'],
        ]);
        $data = SatuanPendidikan::findOrFail($validated['id']);
        $data->update(['name' => $validated['name']]);
        return redirect()->route('kependidikan.index')->with('message', 'update');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        $data = SatuanPendidikan::find($id);
        $data->delete();
        return response()->json($data);
    }
}
