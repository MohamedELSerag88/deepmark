<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrandFavoriteResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		return [
			'id' => $this->id,
			'brand_chat_id' => $this->brand_chat_id,
			'brand_name_suggestion_id' => $this->brand_name_suggestion_id,
			'created_at' => $this->created_at,
			'suggestion' => $this->whenLoaded('suggestion', function () {
				return (new BrandNameItemResource($this->suggestion))->resolve();
			}),
		];
	}
}
