<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pemeriksaan extends Model
{
    protected $table = 'pemeriksaan';
    protected $primaryKey = 'id_pemeriksaan';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];

    protected $casts = [
        'penyakit_json' => 'array',
        'diagnosa_k3_json' => 'array',
        'saran_json' => 'array',
    ];

    public function pendaftaran()
    {
        return $this->belongsTo(Pendaftaran::class);
    }

    public function resep()
    {
        return $this->hasOne(Resep::class, 'id_pemeriksaan', 'id_pemeriksaan');
    }
}
