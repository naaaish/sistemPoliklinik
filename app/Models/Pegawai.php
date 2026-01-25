<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    protected $table = 'pegawai';
    protected $primaryKey = 'nip';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nip', 'nama_pegawai', 'nik', 'jenis_kelamin', 'agama', 
        'tgl_lahir', 'tgl_masuk', 'status_pernikahan', 'no_telp', 
        'email', 'alamat', 'jabatan', 'bagian', 'foto', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'tgl_lahir' => 'date',
        'tgl_masuk' => 'date',
    ];
}