<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pemeriksaan extends Model
{
    protected $table = 'pemeriksaan';
    protected $primaryKey = 'id_pemeriksaan';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'pendaftaran_id',
        'sistol','diastol','nadi',
        'gula_puasa','gula_2jam_pp','gula_sewaktu',
        'asam_urat','cholesterol','trigliseride',
        'suhu','berat_badan','tinggi_badan',
        'penyakit_json','diagnosa_k3_json','saran_json',
        'total_biaya'
    ];

    protected $casts = [
        'penyakit_json' => 'array',
        'diagnosa_k3_json' => 'array',
        'saran_json' => 'array',
    ];

    public function pendaftaran()
    {
        return $this->belongsTo(Pendaftaran::class);
    }

    // public function obat()
    // {
    //     return $this->hasMany(PemeriksaanObat::class);
    // }
}
