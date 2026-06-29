<?php

namespace App\Http\Controllers;

use App\Models\Pendamping;
use Illuminate\Http\Request;

class PendampingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }


    public function updatePendamping(Request $request)
    {
        $r = $request->all();
        // dd($r);
        $data = Pendamping::find($r['id']);

        $r['is_verif'] = 'sudah';
        $r['jabatan'] = $r['jabatan'] ?? '';

        // dd($data);
        $data->update($r);
        return redirect()->route('pegawai.show', session('no_ktp'))->with('message', 'update');
    }
}
