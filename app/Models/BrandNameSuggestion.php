<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrandNameSuggestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_chat_id',
        'suggestion_index',
        'name',
        'archetype',
        'domains',
        'liked',
    ];

    protected function casts(): array
    {
        return [
            'domains' => 'array',
            'liked' => 'boolean',
        ];
    }
}
