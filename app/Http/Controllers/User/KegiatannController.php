<?php

namespace App\Http\Controllers\kegiatan;

use App\Http\Controllers\Controller;
use App\Models\PesertaKegiatan;

use Illuminate\Http\Request;

class KegiatanController extends Controller
{
    public function index()
	{
    		// mengambil data dari table pegawai
            $data = PesertaKegiatan::get()->paginate(10);
 
    		// mengirim data pegawai ke view index
		return view('pages.user.kegiatan.index',['data' => $data]);
 
	}
    public function cari(Request $request)
	{
		// menangkap data pencarian
		$cari = $request->cari;
 
    		// mengambil data dari table pegawai sesuai pencarian data
        $data = PesertaKegiatan::get()
		->where('no_ktp','like',"%".$cari."%")
		->paginate();
 
    		// mengirim data pegawai ke view index
        return view('pages.user.kegiatan.index',['data' => $data]);
 
	}
}
