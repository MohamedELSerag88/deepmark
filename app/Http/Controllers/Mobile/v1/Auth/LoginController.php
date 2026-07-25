<?php

namespace App\Http\Controllers\Mobile\v1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\LoginRequest;
use App\Http\Resources\Mobile\LoginResource;
use App\Services\Auth\AuthService;

class LoginController extends Controller
{
	public function __construct(
		private readonly AuthService $authService,
	) {
		parent::__construct();
	}

	public function login(LoginRequest $request)
	{
		$result = $this->authService->login($request->only(['phone', 'password']));

		if (!($result['ok'] ?? false)) {
			if (($result['error'] ?? null) === 'user_not_found') {
				return $this->statusFail(trans('messages.user_not_found'));
			}

			return $this->statusFail(['message' => trans('messages.wrong_credentials')]);
		}

		return $this->statusOk([
			'data' => new LoginResource($result['user']),
			'message' => trans('messages.user_founded_successfully'),
		]);
	}
}
