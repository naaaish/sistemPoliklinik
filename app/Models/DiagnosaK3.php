<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiagnosaK3 extends Model
{
    protected $table = 'diagnosa_k3';          // penting: harus 'diagnosa_k3'
    protected $primaryKey = 'id_diagnosa_k3';
    public $incrementing = false;
    protected $keyType = 'string';
}
