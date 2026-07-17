<?php

namespace App\Http\Resources\Mobile\Marketing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomeSectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();
        $content = $this->content_en ?? [];
        if ($locale === 'ar' && !empty($this->content_ar)) {
            $content = $this->content_ar;
        }

        return [
            'key' => $this->section_key,
            'content' => $content,
            'sort_order' => $this->sort_order,
        ];
    }
}
