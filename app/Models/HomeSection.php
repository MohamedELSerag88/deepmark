<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeSection extends Model
{
    protected $fillable = [
        'section_key',
        'content_en',
        'content_ar',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'content_en' => 'array',
            'content_ar' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
