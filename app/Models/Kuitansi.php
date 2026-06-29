<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class kuitansi extends Model
{
    use HasFactory;
    protected $fillable = [
        // berelasi dengan peserta kegiatan
        'pegawai_id',
        'no_bukti',
        // 'nip',
        'no_MAK',
        'no_surat_tugas',
        'tgl_surat_tugas',
        'tahun_anggaran',
        'lokasi_asal',
        'lokasi_tujuan',
        'jenis_angkutan',
        'biaya_pergi',
        'biaya_pulang',
        'total_pp',
        'pajak_bandara',
        'biaya_asal',
        'bea_jarak',
        'biaya_tujuan',
        'total_transport',
        'biaya_penginapan',
        'uang_harian',
        'potongan',
        'total_penginapan',
        'total_harian',
        'jumlah_hari',
        'jumlah_malam',
        'bill_malam',
        'total_terima',
    ];
    public function transportasis()
    {
        return $this->hasMany(Transportasi::class);
    }

    public function peserta() {
        return $this->hasOne(PesertaKegiatan::class, 'id', 'pegawai_id');
    }

    public function kabupaten() {
        return $this->hasOne(Kabupaten::class, 'id', 'lokasi_asal');
    }


}
