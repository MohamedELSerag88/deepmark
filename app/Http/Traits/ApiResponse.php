<?php

namespace App\Http\Traits;

use App\Http\Resources\Mobile\MessageResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

trait ApiResponse
{
	public const STATUS_OK = 'OK';
	public const STATUS_FAILED = 'FAILED';

	/**
	 * Success envelope: { status: "OK", ... }.
	 * Pass a Resource, ResourceCollection, or array of envelope keys
	 * (e.g. ['data' => new XResource(...), 'message' => '...']).
	 */
	protected function statusOk(mixed $data = [], int $httpStatus = 200, array $headers = []): JsonResponse
	{
		return $this->statusEnvelope(self::STATUS_OK, $data, $httpStatus, $headers);
	}

	/**
	 * Failure envelope: { status: "FAILED", ... }.
	 */
	protected function statusFail(mixed $data = [], int $httpStatus = 200, array $headers = []): JsonResponse
	{
		return $this->statusEnvelope(self::STATUS_FAILED, $data, $httpStatus, $headers);
	}

	protected function notFound(mixed $data = null, int $httpStatus = 404, array $headers = []): JsonResponse
	{
		return $this->error($data ?? ['message' => 'Not found'], $httpStatus, $headers);
	}

	protected function unauthorized(mixed $data = null, int $httpStatus = 401, array $headers = []): JsonResponse
	{
		return $this->error($data ?? ['message' => 'Unauthenticated'], $httpStatus, $headers);
	}

	protected function forbidden(mixed $data = null, int $httpStatus = 403, array $headers = []): JsonResponse
	{
		return $this->error($data ?? ['message' => 'Forbidden'], $httpStatus, $headers);
	}

	protected function bad(mixed $data = null, int $httpStatus = 400, array $headers = []): JsonResponse
	{
		return $this->error($data, $httpStatus, $headers);
	}

	protected function error(mixed $data = null, int $httpStatus = 500, array $headers = []): JsonResponse
	{
		if ($data === null) {
			$data = ['error' => 'something went wrong !'];
		}
		if (is_string($data)) {
			$data = ['error' => trans($data)];
		}

		return $this->statusFail($data, $httpStatus, $headers);
	}

	/**
	 * Convenience: wrap a Resource under `data` with optional message.
	 */
	protected function okResource(
		JsonResource|ResourceCollection|AnonymousResourceCollection $resource,
		?string $message = null,
		int $httpStatus = 200
	): JsonResponse {
		$payload = ['data' => $resource];
		if ($message !== null) {
			$payload['message'] = $message;
		}

		return $this->statusOk($payload, $httpStatus);
	}

	protected function statusEnvelope(string $status, mixed $data, int $httpStatus, array $headers = []): JsonResponse
	{
		$payload = ['status' => $status];

		if ($data instanceof MessageResource) {
			// MessageResource is an envelope fragment (message / id / token), not a `data` wrapper.
			$resolved = $this->resolveResourceValue($data);
			$payload = array_merge($payload, is_array($resolved) ? $resolved : []);
		} elseif ($data instanceof JsonResource || $data instanceof ResourceCollection || $data instanceof AnonymousResourceCollection) {
			$payload['data'] = $this->resolveResourceValue($data);
		} elseif (is_array($data)) {
			$payload = array_merge($payload, $this->resolveResourceValue($data));
		} elseif (is_string($data)) {
			$payload['message'] = trans($data);
		} elseif ($data !== null) {
			$payload['data'] = $data;
		}

		return response()->json($payload, $httpStatus, $headers);
	}

	protected function resolveResourceValue(mixed $value): mixed
	{
		if ($value instanceof JsonResource || $value instanceof ResourceCollection || $value instanceof AnonymousResourceCollection) {
			return $value->resolve(request());
		}

		if (is_array($value)) {
			$resolved = [];
			foreach ($value as $key => $item) {
				$resolved[$key] = $this->resolveResourceValue($item);
			}

			return $resolved;
		}

		return $value;
	}
}
