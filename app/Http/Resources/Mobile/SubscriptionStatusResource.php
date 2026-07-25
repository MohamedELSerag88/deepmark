<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionStatusResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		if ($this->resource === null) {
			return [];
		}

		$data = is_array($this->resource) ? $this->resource : [];

		return [
			'status' => $data['status'] ?? null,
			'plan' => $data['plan'] ?? null,
			'started_at' => $data['started_at'] ?? null,
			'ends_at' => $data['ends_at'] ?? null,
		];
	}
}
