<?php

namespace App\Http\Controllers;

use App\Models\Kepegawaian;
use Illuminate\Http\Request;

class KepegawaianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Kepegawaian::get();
        return view('pages.admin.status.kepegawaian.index', ['menu' => 'kepegawaian', 'datas' => $data]);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $r = $request->all();

        Kepegawaian::create($r);

        return redirect()->route('kepegawaian.index')->with('message', 'store');
    }

    /**
     * Display the specified resource.
     */

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = Kepegawaian::find($id);

        return view('pages.admin.status.kepegawaian.edit', ['menu' => 'kepegawaian', 'datas' => $data]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        //
        $r = $request->all();
        $data = Kepegawaian::find($r['id'])->first();
        $data->update($r);
        return redirect()->route('kepegawaian.index')->with('message', 'update');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        $data = Kepegawaian::find($id);
        $data->delete();
        return response()->json($data);
    }
}
