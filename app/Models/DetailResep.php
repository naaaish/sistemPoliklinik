<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailResep extends Model
{
    protected $table = 'detail_resep';
    protected $primaryKey = null;
    public $incrementing = false;

    public $timestamps = false;
    protected $fillable = [
        'id_resep','id_obat','jumlah','satuan','subtotal'
    ];
}