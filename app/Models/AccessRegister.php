<?php

namespace App\Models;

use App\Support\Role;
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
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $attributes = [
        'role' => Role::ADMINISTRATOR,
    ];

    public function permissions(): array
    {
        return Role::permissions($this->role);
    }

    public function allows(string $permission): bool
    {
        return Role::allows($this->role, $permission);
    }
}
