<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $table = 'news';
    protected $fillable = [
        'title',
        'content',
        'published_at',
        'featured_image',
        'featured_image_2',
    ];

    protected $casts = [
        'published_at' => 'date',
    ];
}
