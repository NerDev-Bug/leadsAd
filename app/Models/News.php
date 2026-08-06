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
        'fb_post_id',
    ];

    protected $casts = [
        'published_at' => 'date',
    ];

    protected $appends = ['content_list'];

    public function getContentListAttribute(): array
    {
        if (empty($this->content)) {
            return [];
        }

        if (str_contains($this->content, '<li')) {
            preg_match_all('/<li[^>]*>(.*?)<\/li>/is', $this->content, $matches);

            return array_values(array_filter(array_map(function ($item) {
                return trim(strip_tags(html_entity_decode($item)));
            }, $matches[1] ?? [])));
        }

        if (! str_contains($this->content, '<')) {
            $separator = match (true) {
                str_contains($this->content, "\n") => "\n",
                str_contains($this->content, ': ') => ': ',
                str_contains($this->content, ', ') => ', ',
                str_contains($this->content, ',') => ',',
                default => ': ',
            };

            return array_values(array_filter(array_map(
                'trim',
                explode($separator, $this->content)
            )));
        }

        $text = trim(strip_tags(html_entity_decode($this->content)));

        return $text !== '' ? [$text] : [];
    }
}
