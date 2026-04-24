<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrandChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_chat_id',
        'user_id',
        'role',
        'message',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }
}
