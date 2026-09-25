<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PegawaiPpnpn extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'pegawaiPpnpns';
    protected $fillable = [
    'nama',
    'jabatan',
    'nip',
    'nik',
    ];

    public function internalPpnpn()
{
    return $this->hasMany(InternalPpnpn::class, 'id_pegawai');
}
}
