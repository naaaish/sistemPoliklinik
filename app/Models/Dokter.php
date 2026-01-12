<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dokter extends Model
{
    protected $table = 'dokter';
    
    protected $fillable = [
        'nama',
        'spesialisasi',
        'foto',
        'no_telepon',
        'email'
    ];

    public function jadwalDokter()
    {
        return $this->hasMany(JadwalDokter::class);
    }
}
