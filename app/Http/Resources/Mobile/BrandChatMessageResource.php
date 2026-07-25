<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrandChatMessageResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		return [
			'id' => $this->id,
			'brand_chat_id' => $this->brand_chat_id,
			'user_id' => $this->user_id,
			'role' => $this->role,
			'message' => $this->message,
			'payload' => $this->payload,
			'created_at' => $this->created_at,
		];
	}
}
