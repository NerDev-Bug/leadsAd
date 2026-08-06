<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSession extends Model
{
    protected $fillable = [
        'access_register_id',
        'session_id',
        'ip_address',
        'user_agent',
        'browser',
        'platform',
        'device',
        'is_current',
        'last_activity',
        'logged_in_at',
        'logged_out_at',
    ];

    protected function casts(): array
    {
        return [
            'is_current' => 'boolean',
            'last_activity' => 'datetime',
            'logged_in_at' => 'datetime',
            'logged_out_at' => 'datetime',
        ];
    }

    public function accessRegister(): BelongsTo
    {
        return $this->belongsTo(AccessRegister::class);
    }
}
