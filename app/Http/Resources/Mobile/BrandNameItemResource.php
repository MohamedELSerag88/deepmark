<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrandNameItemResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		$data = is_array($this->resource) ? $this->resource : [
			'suggestion_index' => $this->suggestion_index ?? null,
			'id' => $this->id ?? null,
			'project_id' => $this->project_id ?? $this->brand_chat_id ?? null,
			'name' => $this->name ?? null,
			'archetype' => $this->archetype ?? null,
			'name_type' => $this->name_type ?? null,
			'linguistic_style' => $this->linguistic_style ?? null,
			'generation_technique' => $this->generation_technique ?? null,
			'name_length' => $this->name_length ?? null,
			'rationale' => $this->rationale ?? null,
			'description' => $this->description ?? null,
			'brand_keywords' => $this->brand_keywords ?? [],
			'why_fits' => $this->why_fits ?? null,
			'badge' => 'Evocative Brand Name',
			'domains' => $this->domains ?? null,
			'liked' => (bool) ($this->liked ?? false),
		];

		return [
			'suggestion_index' => $data['suggestion_index'] ?? null,
			'id' => $data['id'] ?? null,
			'project_id' => $data['project_id'] ?? null,
			'name' => $data['name'] ?? null,
			'archetype' => $data['archetype'] ?? null,
			'name_type' => $data['name_type'] ?? null,
			'linguistic_style' => $data['linguistic_style'] ?? null,
			'generation_technique' => $data['generation_technique'] ?? null,
			'name_length' => $data['name_length'] ?? null,
			'rationale' => $data['rationale'] ?? null,
			'description' => $data['description'] ?? null,
			'brand_keywords' => $data['brand_keywords'] ?? [],
			'why_fits' => $data['why_fits'] ?? null,
			'badge' => $data['badge'] ?? 'Evocative Brand Name',
			'domains' => $data['domains'] ?? null,
			'liked' => (bool) ($data['liked'] ?? false),
		];
	}
}
