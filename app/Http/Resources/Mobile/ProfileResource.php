<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		$data = is_array($this->resource) ? $this->resource : [];

		return [
			'user' => (new ProfileUserResource($data['user'] ?? []))->resolve(),
			'stats' => $data['stats'] ?? [],
			'latest' => $data['latest'] ?? [],
			'todos' => $data['todos'] ?? [],
		];
	}
}
