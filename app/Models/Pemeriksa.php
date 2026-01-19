<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pemeriksa extends Model
{
    protected $table = 'pemeriksa';
    protected $primaryKey = 'id_pemeriksa';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_pemeriksa',
        'nama_pemeriksa',
        'status',
    ];
}
