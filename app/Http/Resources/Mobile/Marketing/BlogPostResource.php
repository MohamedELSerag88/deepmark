<?php

namespace App\Http\Resources\Mobile\Marketing;

use App\Http\Helpers\Traits\LocalizesFields;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogPostResource extends JsonResource
{
    use LocalizesFields;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->slug,
            'slug' => $this->slug,
            'date' => optional($this->published_at)->format('F j, Y'),
            'published_at' => optional($this->published_at)->toDateString(),
            'title' => $this->pick('title'),
            'badge' => $this->pick('badge'),
            'image' => $this->image_url,
            'author' => [
                'name' => $this->author_name,
                'title' => $this->pick('author_title'),
                'avatar' => $this->author_avatar_url,
            ],
            'lead' => $this->pick('lead'),
            'content' => $this->pick('content', []),
        ];
    }
}
