<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DomainReservationResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		$data = is_array($this->resource) ? $this->resource : [];

		$result = [
			'reservation_id' => $data['reservation_id'] ?? null,
			'status' => $data['status'] ?? null,
		];

		if (array_key_exists('provider_order_id', $data)) {
			$result['provider_order_id'] = $data['provider_order_id'];
		}
		if (array_key_exists('error', $data)) {
			$result['error'] = $data['error'];
		}

		return $result;
	}
}
