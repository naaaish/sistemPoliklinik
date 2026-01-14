<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class PemeriksaanObat extends Model
{
    protected $fillable = [
        'pemeriksaan_id',
        'obat_id',
        'jumlah',
        'satuan',
        'harga_satuan',
        'subtotal'
    ];

    public function pemeriksaan()
    {
        return $this->belongsTo(Pemeriksaan::class);
    }

    public function obat()
    {
        return $this->belongsTo(Obat::class);
    }
}
