<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Directory extends Model
{
    use HasFactory;

    protected $table = 'directory'; // since your table is named `directory`

    protected $fillable = [
        'area',
        'region',
        'place',
        'business_name',
        'address',
        'contact_name',
        'contact_no',
    ];
}
