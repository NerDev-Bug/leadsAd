<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Career extends Model
{
    use HasFactory;

    protected $table = 'careers';
    protected $fillable = [
        'employment_type',
        'position',
        'details',
        'location',
        'job_description',
    ];
}
