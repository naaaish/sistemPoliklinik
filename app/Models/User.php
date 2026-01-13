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

    protected $hidden = ['password'];

    public function getAuthIdentifierName()
    {
        return 'username';
    }
}
