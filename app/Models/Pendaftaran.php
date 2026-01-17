<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pendaftaran extends Model
{
    protected $table = 'pendaftaran';

    protected $primaryKey = 'id_pendaftaran';
    public $incrementing = false;
    protected $keyType = 'string';

    public function pasien() { return $this->belongsTo(\App\Models\Pasien::class, 'id_pasien', 'id_pasien'); }
    public function dokter() { return $this->belongsTo(\App\Models\Dokter::class, 'id_dokter', 'id_dokter'); }
    public function pemeriksa(){ return $this->belongsTo(\App\Models\Pemeriksa::class, 'id_pemeriksa', 'id_pemeriksa'); }
}
