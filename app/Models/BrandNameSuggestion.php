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
        'name_type',
        'linguistic_style',
        'generation_technique',
        'name_length',
        'rationale',
        'description',
        'brand_keywords',
        'why_fits',
        'domains',
        'liked',
        'is_marketing_featured',
        'marketing_image_url',
        'marketing_author_name',
        'marketing_author_position',
        'marketing_author_avatar_url',
        'marketing_description_en',
        'marketing_description_ar',
        'marketing_lead_en',
        'marketing_lead_ar',
        'marketing_gallery_images',
        'marketing_content_en',
        'marketing_content_ar',
        'marketing_deliverables_en',
        'marketing_deliverables_ar',
    ];

    protected function casts(): array
    {
        return [
            'domains' => 'array',
            'brand_keywords' => 'array',
            'liked' => 'boolean',
            'is_marketing_featured' => 'boolean',
            'marketing_gallery_images' => 'array',
            'marketing_content_en' => 'array',
            'marketing_content_ar' => 'array',
            'marketing_deliverables_en' => 'array',
            'marketing_deliverables_ar' => 'array',
        ];
    }

    public function scopeForMarketing($query)
    {
        return $query->where(function ($q) {
            $q->where('is_marketing_featured', true)
                ->orWhere('liked', true);
        });
    }
}
