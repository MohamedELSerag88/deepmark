<?php

namespace App\Http\Resources\Mobile\Marketing;

use App\Http\Helpers\Traits\LocalizesFields;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PricingPackageResource extends JsonResource
{
    use LocalizesFields;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->pick('name'),
            'price_display' => $this->price_display,
            'currency_symbol' => $this->currency_symbol,
            'description' => $this->pick('description'),
            'features' => $this->pick('features', []),
            'badge' => $this->pick('badge'),
            'is_recommended' => (bool) $this->is_recommended,
            'cta_label' => $this->pick('cta_label', 'Start Now'),
            'cta_url' => $this->cta_url,
            'sort_order' => $this->sort_order,
        ];
    }
}
