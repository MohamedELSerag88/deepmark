<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Generic resource wrapper for array/object payloads that already match the API contract.
 */
class JsonDataResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		if ($this->resource === null) {
			return [];
		}

		if (is_array($this->resource)) {
			return $this->resource;
		}

		if (is_object($this->resource) && method_exists($this->resource, 'toArray')) {
			return $this->resource->toArray();
		}

		return (array) $this->resource;
	}
}
