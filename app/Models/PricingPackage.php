<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingPackage extends Model
{
    protected $fillable = [
        'slug',
        'name_en',
        'name_ar',
        'price_display',
        'currency_symbol',
        'description_en',
        'description_ar',
        'features_en',
        'features_ar',
        'badge_en',
        'badge_ar',
        'is_recommended',
        'cta_label_en',
        'cta_label_ar',
        'cta_url',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'features_en' => 'array',
            'features_ar' => 'array',
            'is_recommended' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
