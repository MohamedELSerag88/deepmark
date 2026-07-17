<?php

namespace App\Http\Resources\Mobile\Marketing;

use App\Http\Helpers\Traits\LocalizesFields;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrandNameSuggestionResource extends JsonResource
{
    use LocalizesFields;

    public function toArray(Request $request): array
    {
        $description = $this->pick('marketing_description');
        if (!$description && $this->archetype) {
            $description = 'Archetype: ' . $this->archetype;
        }

        return [
            'id' => (string) $this->id,
            'slug' => (string) $this->id,
            'name' => $this->name,
            'title' => $this->name,
            'year' => optional($this->created_at)->format('Y'),
            'description' => $description,
            'archetype' => $this->archetype,
            'domains' => $this->domains ?? [],
            'liked' => (bool) $this->liked,
            'image' => $this->marketing_image_url,
            'image_alt' => $this->name,
            'author' => [
                'name' => $this->marketing_author_name,
                'position' => $this->marketing_author_position,
                'avatar' => $this->marketing_author_avatar_url,
            ],
            'detail' => [
                'lead' => $this->pick('marketing_lead'),
                'images' => $this->marketing_gallery_images ?? [],
                'content' => $this->pick('marketing_content', []),
                'deliverables' => $this->pick('marketing_deliverables', []),
            ],
        ];
    }
}
