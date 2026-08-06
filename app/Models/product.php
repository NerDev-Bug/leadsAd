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

    protected $appends = ['dosage_list', 'target_list'];

    public function getDosageListAttribute(): array
    {
        return $this->parseRichTextList($this->dosage);
    }

    public function getTargetListAttribute(): array
    {
        return $this->parseRichTextList($this->target);
    }

    protected function parseRichTextList(?string $value): array
    {
        if (empty($value)) {
            return [];
        }

        if (str_contains($value, '<li')) {
            preg_match_all('/<li[^>]*>(.*?)<\/li>/is', $value, $matches);

            return array_values(array_filter(array_map(function ($item) {
                return trim(strip_tags(html_entity_decode($item)));
            }, $matches[1] ?? [])));
        }

        if (! str_contains($value, '<')) {
            $separator = match (true) {
                str_contains($value, "\n") => "\n",
                str_contains($value, ', ') => ', ',
                str_contains($value, ': ') => ': ',
                str_contains($value, ',') => ',',
                default => ', ',
            };

            return array_values(array_filter(array_map(
                'trim',
                explode($separator, $value)
            )));
        }

        $text = trim(strip_tags(html_entity_decode($value)));

        return $text !== '' ? [$text] : [];
    }
}
