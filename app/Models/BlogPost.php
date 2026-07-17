<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    protected $fillable = [
        'slug',
        'published_at',
        'title_en',
        'title_ar',
        'badge_en',
        'badge_ar',
        'image_url',
        'author_name',
        'author_title_en',
        'author_title_ar',
        'author_avatar_url',
        'lead_en',
        'lead_ar',
        'content_en',
        'content_ar',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'date',
            'content_en' => 'array',
            'content_ar' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
