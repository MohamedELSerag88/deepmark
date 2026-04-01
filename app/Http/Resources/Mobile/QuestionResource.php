<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @return array<string, mixed>
	 */
	public function toArray(Request $request): array
	{
		$resources = $this->formatResources($this->resources);
		$whyMatters = $this->why_matters ?? $this->extractWhyMatters($this->resources);

		return [
			"id" => $this->id,
			"question_en" => $this->question_en,
			"question_ar" => $this->question_ar,
			"question_type" => $this->question_type ?: 'text',
			"answers" => $this->answers,
			"description_en" => $this->description_en,
			"description_ar" => $this->description_ar,
			"video_url" => $this->video_url,
			"video_path" => $this->video_path,
			"image_url" => $this->image_url,
			"example_answer" => $this->example_answer,
			"why_matters" => $this->why_matters,
			"resources" => $resources,
		];
	}

	/**
	 * Format resources: use "url" key instead of "title"; keep "text".
	 *
	 * @param array|null $resources
	 * @return array
	 */
	protected function formatResources(?array $resources): array
	{
		if (!is_array($resources)) {
			return [];
		}
		$out = [];
		foreach ($resources as $item) {
			if (!is_array($item)) {
				continue;
			}
			$out[] = [
				'url' => $item['url'] ?? $item['title'] ?? null,
				'text' => $item['text'] ?? null,
			];
		}
		return $out;
	}

	/**
	 * Extract "why it matters" content from resources (first item with matching title or first item).
	 *
	 * @param array|null $resources
	 * @return string|null
	 */
	protected function extractWhyMatters(?array $resources): ?string
	{
		if (!is_array($resources)) {
			return null;
		}
		foreach ($resources as $item) {
			if (!is_array($item)) {
				continue;
			}
			$title = $item['title'] ?? $item['url'] ?? '';
			if (stripos((string)$title, 'why') !== false || stripos((string)$title, 'matters') !== false) {
				return $item['text'] ?? null;
			}
		}
		return isset($resources[0]['text']) ? $resources[0]['text'] : null;
	}
}


