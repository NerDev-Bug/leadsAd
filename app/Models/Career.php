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
        'qualifications',
    ];

    protected $appends = ['qualifications_list'];

    public function getQualificationsListAttribute(): array
    {
        if (empty($this->qualifications)) {
            return [];
        }

        if (str_contains($this->qualifications, '<li')) {
            preg_match_all('/<li[^>]*>(.*?)<\/li>/is', $this->qualifications, $matches);

            return array_values(array_filter(array_map(function ($item) {
                return trim(strip_tags(html_entity_decode($item)));
            }, $matches[1] ?? [])));
        }

        if (! str_contains($this->qualifications, '<')) {
            $separator = str_contains($this->qualifications, "\n") ? "\n" : ': ';

            return array_values(array_filter(array_map(
                'trim',
                explode($separator, $this->qualifications)
            )));
        }

        $text = trim(strip_tags(html_entity_decode($this->qualifications)));

        return $text !== '' ? [$text] : [];
    }
}
