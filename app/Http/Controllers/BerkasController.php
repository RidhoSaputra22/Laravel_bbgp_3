<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Berkas;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class BerkasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (auth()->user()->role === 'admin' || auth()->user()->role === 'superadmin') {
            $users = Pegawai::with(['berkas' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }])
                ->whereHas('berkas')
                ->get();
            return view('pages.admin.berkas.index', [
                'datas' => $users,
                'menu' => 'berkas'
            ]);
        }

        $datas = Berkas::where('nik', session('no_ktp'))
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.admin.berkas.index', [
            'datas' => $datas,
            'menu' => 'berkas'
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $r)
    {
        try {
            $berkas = new Berkas();

            if ($r->hasFile('nama_berkas')) {
                $validator = Validator::make($r->all(), [
                    'nama_berkas' => 'required|mimes:pdf,doc,docx|max:10024',
                    'nama_kegiatan' => 'required',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'error',
                        'errors' => $validator->messages()
                    ], 422);
                }

                $foto = $r->file('nama_berkas');
                $ext = $foto->getClientOriginalExtension();
                // $r['pas_foto'] = $request->file('pas_foto');

                $fileName = date('Y-m-d_H-i-s') . "." . $ext;
                $destinationPath = '/home/simbbgps/public_html/upload/berkas';

                $foto->move($destinationPath, $fileName);

                $berkas->nama_berkas = $fileName;
            } else {
                $validator = Validator::make($r->all(), [
                    'nama_link' => 'required',
                    'nama_kegiatan' => 'required',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'error',
                        'errors' => $validator->messages()
                    ], 422);
                }
                $berkas->nama_berkas = $r->nama_link;
            }

            $berkas->nama_kegiatan = $r->nama_kegiatan;
            $berkas->metode_upload = $r->metode_upload;
            $berkas->status = 'proses';
            $berkas->nik = session('no_ktp');
            $berkas->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Laporan telah di buat'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = Berkas::find($id);
        return response()->json([
            'data' => $data
        ]);
        // return view('pages.admin.berkas.index', ['menu' => 'berkas'])->with('datas', json_encode($data));
    }

    // public function verifikasi(string $id)
    // {

    //     $data = Berkas::find($id);
    //     $getData = Berkas::find($id);
    //     $data->is_verif = 'sudah';
    //     $data->save();
    //     return response()->json([
    //         'status' => $data,
    //         'data' => $getData,
    //     ]);
    // }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $r)
    {
        try {
            $data = Berkas::find($r->formId);

            if (!$r->hasFile('nama_berkas') && empty($r->nama_link)) {
                $validator = Validator::make($r->all(), [
                    'nama_kegiatan' => 'required',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'error',
                        'errors' => $validator->messages()
                    ], 422);
                }

                $data->update([
                    'nama_kegiatan' => $r->nama_kegiatan,
                    'nik' => session('no_ktp'),
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Data updated successfully'
                ]);
            }

            if ($r->hasFile('nama_berkas')) {
                $validator = Validator::make($r->all(), [
                    'nama_berkas' => 'mimes:pdf,doc,docx|max:10024',
                    'nama_kegiatan' => 'required',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'error',
                        'errors' => $validator->messages()
                    ], 422);
                }

                $foto = $r->file('nama_berkas');
                $ext = $foto->getClientOriginalExtension();
                $fileName = date('Y-m-d_H-i-s') . "." . $ext;
                $destinationPath = '/home/simbbgps/public_html/upload/berkas';

                $foto->move($destinationPath, $fileName);

                $data->nama_berkas = $fileName;
            } else {
                $validator = Validator::make($r->all(), [
                    'nama_link' => 'required',
                    'nama_kegiatan' => 'required',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'error',
                        'errors' => $validator->messages()
                    ], 422);
                }
                $data->nama_berkas = $r->nama_link;
            }

            $data->update([
                'metode_upload' => $r->metode_upload,
                'nama_kegiatan' => $r->nama_kegiatan,
                'nama_berkas' => $data->nama_berkas,
                'nik' => session('no_ktp'),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Data updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to upload file'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        $data = Berkas::find($id);
        $data->delete();
        return response()->json($data);
    }

    public function verify($id)
    {
        try {
            $berkas = Berkas::findOrFail($id);
            $berkas->update(['status' => 'selesai']);

            return response()->json([
                'status' => 'success',
                'message' => 'Berkas berhasil diverifikasi'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memverifikasi berkas'
            ], 500);
        }
    }
}
