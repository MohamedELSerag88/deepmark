<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		$data = is_array($this->resource) ? $this->resource : [];

		return [
			'id' => $data['id'] ?? null,
			'project_id' => $data['project_id'] ?? null,
			'chat_id' => $data['chat_id'] ?? null,
			'parent_id' => $data['parent_id'] ?? null,
			'topic' => $data['topic'] ?? null,
			'project_name' => $data['project_name'] ?? null,
			'selected_brand_name' => $data['selected_brand_name'] ?? null,
			'favorites_count' => $data['favorites_count'] ?? 0,
			'language' => $data['language'] ?? null,
			'answers' => $data['answers'] ?? [],
			'archetype' => $data['archetype'] ?? [],
			'name_types' => $data['name_types'] ?? [],
			'linguistic_styles' => $data['linguistic_styles'] ?? [],
			'generation_techniques' => $data['generation_techniques'] ?? [],
			'items' => BrandNameItemResource::collection(collect($data['items'] ?? []))->resolve(),
			'raw_response' => $data['raw_response'] ?? null,
			'created_at' => $data['created_at'] ?? null,
			'device_token' => $data['device_token'] ?? null,
		];
	}
}
