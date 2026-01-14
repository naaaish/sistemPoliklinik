<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Saran extends Model
{
    protected $table = 'saran';
    protected $primaryKey = 'id_saran';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;
}