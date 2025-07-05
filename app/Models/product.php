<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class product extends Model
{
    use HasFactory;

    protected $table = 'products';
    protected $fillable = [
        'type',
        'description',
        'features',
        'dosage',
        'target',
        'category',
        'image1',
        'image2',
    ];
}
