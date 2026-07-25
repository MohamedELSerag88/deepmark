<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvitationResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		$data = is_array($this->resource) ? $this->resource : null;

		if ($data !== null) {
			return [
				'id' => $data['id'] ?? null,
				'email' => $data['email'] ?? null,
				'status' => $data['status'] ?? null,
				'accepted_at' => $data['accepted_at'] ?? null,
				'created_at' => $data['created_at'] ?? null,
			];
		}

		return [
			'id' => $this->id,
			'email' => $this->email,
			'status' => $this->status ?? null,
			'accepted_at' => $this->accepted_at ?? null,
			'created_at' => $this->created_at ?? null,
		];
	}
}
