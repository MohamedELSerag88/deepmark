<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DomainCheckResultResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		$data = is_array($this->resource) ? $this->resource : [];

		return [
			'domain' => $data['domain'] ?? null,
			'available' => $data['available'] ?? null,
		];
	}
}
