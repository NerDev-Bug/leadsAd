<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class AccessRegister extends Authenticatable
{
    use HasFactory;

    protected $table = 'access_registers';
    protected $fillable = [
        'username',
        'email',
        'password',
    ];
}
