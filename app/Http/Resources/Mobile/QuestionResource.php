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
			"resources" => $this->resources,
		];
	}
}


