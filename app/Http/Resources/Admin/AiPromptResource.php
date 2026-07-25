<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AiPromptResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		return [
			'id' => $this->id,
			'key' => $this->key,
			'name' => $this->name,
			'system_template' => $this->system_template,
			'user_template' => $this->user_template,
			'updated_at' => optional($this->updated_at)->toISOString(),
		];
	}
}
