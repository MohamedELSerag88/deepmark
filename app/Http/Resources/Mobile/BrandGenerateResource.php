<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrandGenerateResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		$data = is_array($this->resource) ? $this->resource : [];
		$items = $data['items'] ?? [];

		$result = [
			'id' => $data['id'] ?? null,
			'project_id' => $data['project_id'] ?? null,
			'chat_id' => $data['chat_id'] ?? null,
			'items' => BrandNameItemResource::collection(collect($items))->resolve(),
		];

		if (array_key_exists('project_name', $data)) {
			$result['project_name'] = $data['project_name'];
		}
		if (array_key_exists('selected_name', $data)) {
			$result['selected_name'] = $data['selected_name'];
		}
		if (array_key_exists('response_message', $data)) {
			$result['response_message'] = $data['response_message'];
		}
		if (array_key_exists('payload', $data)) {
			$payload = $data['payload'] ?? [];
			$result['payload'] = [
				'response_message' => $payload['response_message'] ?? null,
				'project_name' => $payload['project_name'] ?? null,
				'items' => BrandNameItemResource::collection(collect($payload['items'] ?? []))->resolve(),
			];
		}

		return $result;
	}
}
