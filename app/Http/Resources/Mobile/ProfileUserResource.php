<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileUserResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		$data = is_array($this->resource) ? $this->resource : [];

		return [
			'id' => $data['id'] ?? null,
			'fname' => $data['fname'] ?? null,
			'lname' => $data['lname'] ?? null,
			'email' => $data['email'] ?? null,
			'phone' => $data['phone'] ?? null,
			'image' => $data['image'] ?? null,
			'country' => $data['country'] ?? null,
			'time_zone' => $data['time_zone'] ?? null,
			'bio' => $data['bio'] ?? null,
			'name' => $data['name'] ?? null,
		];
	}
}
