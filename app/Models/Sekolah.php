<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sekolah extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'nama_sekolah',
        'npsn_sekolah',
        'bp_sekolah',
        'status_sekolah',
        'provinsi',
        'kecamatan',
        'kabupaten',
        'alamat',
        'akreditasi',
        'no_telepon',
        'email',
        'website_url',
        'tahun_berdiri',
        'koordinat',

        'nama_kepsek',
        'asn_opsi',
        'nip_kepsek',
        'no_sk',
        'no_telp_kepsek',
        'email_kepsek',

        'jumlah_guru',
        'jumlah_guru_pns',
        'jumlah_honorer',
        'jumlah_kependidikan',
        'bidang_studi',

        'jumlah_siswa',
        'jumlah_siswa_pria',
        'jumlah_siswa_perempuan',
        'jumlah_siswa_per_kelas',
        'jumlah_kelas',

        'laboratorium',
        'perpustakaan',
        'ruang_guru',
        'jumlah_toilet',
        'lapangan_olahraga',
        'fasilitas_it',
        'akses_internet',

        'ekstrakurikuler',
        'program_unggulan',
        'jam_belajar',
        'foto_depan',
        'logo_sekolah',
        'denah_lokasi',
        'struktur_organisasi',
    ];
    
    protected $casts = [
        'fasilitas_it' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
