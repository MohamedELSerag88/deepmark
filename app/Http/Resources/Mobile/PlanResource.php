<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		return [
			"id" => $this->id,
			"name" => $this->name,
			"description" => $this->description,
			"price_cents" => $this->price_cents,
			"currency" => $this->currency,
			"interval" => $this->interval,
			"features" => $this->whenLoaded('features', function () {
				return $this->features->map(function ($f) {
					return [
						"key" => $f->key,
						"label" => $f->label,
						"value" => $f->value,
					];
				});
			}),
		];
	}
}


