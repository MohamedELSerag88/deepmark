<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrandTextHistoryResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		return [
			'id' => $this->id,
			'language' => $this->language,
			'answers' => $this->answers,
			'response' => $this->response,
			'raw_response' => $this->raw_response,
			'created_at' => $this->created_at,
		];
	}
}
