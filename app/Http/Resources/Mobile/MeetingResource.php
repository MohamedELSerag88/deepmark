<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeetingResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		return [
			'id' => $this->id,
			'brand_chat_id' => $this->brand_chat_id,
			'meeting_at' => $this->meeting_at,
			'notes' => $this->notes ?? null,
			'status' => $this->status,
			'created_at' => $this->created_at ?? null,
		];
	}
}
