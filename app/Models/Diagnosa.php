<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Diagnosa extends Model
{
    protected $table = 'diagnosa';

    protected $primaryKey = 'id_diagnosa';
    public $incrementing = false;
    protected $keyType = 'string';
}
