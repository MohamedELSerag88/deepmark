<?php

namespace App\Http\Resources\Mobile\Marketing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactSubmissionResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		return [
			'id' => $this->id,
			'name' => $this->name,
			'email' => $this->email,
			'brand' => $this->brand,
			'description' => $this->description,
			'budget' => $this->budget,
			'timeline' => $this->timeline,
			'is_read' => (bool) ($this->is_read ?? false),
			'created_at' => $this->created_at,
		];
	}
}
