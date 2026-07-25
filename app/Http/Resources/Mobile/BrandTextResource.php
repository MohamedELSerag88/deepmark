<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrandTextResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		$data = is_array($this->resource) ? $this->resource : [];

		return [
			'brand_text' => $data['brand_text'] ?? null,
			'colors' => $data['colors'] ?? [],
			'design_details' => $data['design_details'] ?? [],
			'raw' => $data['raw'] ?? null,
		];
	}
}
