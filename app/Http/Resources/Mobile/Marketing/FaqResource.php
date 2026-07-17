<?php

namespace App\Http\Resources\Mobile\Marketing;

use App\Http\Helpers\Traits\LocalizesFields;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FaqResource extends JsonResource
{
    use LocalizesFields;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'question' => $this->pick('question'),
            'answer' => $this->pick('answer'),
            'sort_order' => $this->sort_order,
        ];
    }
}
