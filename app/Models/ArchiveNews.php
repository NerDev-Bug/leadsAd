<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchiveNews extends Model
{
    use HasFactory;

    protected $table = 'archive_news';

    protected $fillable = [
        'title',
        'content',
        'published_at',
        'featured_image',
        'featured_image_2',
        'original_news_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];
}
