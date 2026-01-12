<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'username',
        'password',
        'role',
        'nama_user'
    ];

    protected $hidden = [
        'password'
    ];

    // Pakai username untuk login
    public function getAuthIdentifierName()
    {
        return 'username';
    }
}
