<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dokter extends Model
{
    protected $table = 'dokter';
    protected $primaryKey = 'id_dokter';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_dokter',
        'nama',
        'jenis_dokter',
        'no_telepon',
        'status',
    ];

    public function jadwal()
    {
        return $this->hasMany(JadwalDokter::class, 'id_dokter', 'id_dokter');
    }
}
