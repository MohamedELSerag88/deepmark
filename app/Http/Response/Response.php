<?php

namespace App\Http\Response;

/**
 * @deprecated Use App\Http\Traits\ApiResponse on controllers instead.
 * Kept temporarily for any external references; prefer the trait.
 */
class Response extends \Illuminate\Http\JsonResponse
{
	public const FAILED = 'FAILED';
	public const OK = 'OK';

	public function statusOk($data = [], $status = 200, array $headers = [])
	{
		$payload = ['status' => static::OK];
		if (is_array($data)) {
			$payload = array_merge($payload, $data);
		} elseif (is_string($data)) {
			$payload['message'] = trans($data);
		}

		return new static($payload, $status, $headers);
	}

	public function statusFail($data = [], $status = 200, array $headers = [])
	{
		$payload = ['status' => static::FAILED];
		if (is_array($data)) {
			$payload = array_merge($payload, $data);
		} elseif (is_string($data)) {
			$payload['message'] = trans($data);
		}

		return new static($payload, $status, $headers);
	}

	public function notFound($data = null, $status = 404, array $headers = [])
	{
		return $this->statusFail(is_array($data) ? $data : ['error' => $data ?? 'Not found'], $status, $headers);
	}

	public function unauthorized($data = null, $status = 401, array $headers = [])
	{
		return $this->statusFail(is_array($data) ? $data : ['error' => $data ?? 'Unauthenticated'], $status, $headers);
	}
}
