<?php

namespace App\Http\Controllers\Mobile\v1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\SocialLoginRequest;
use App\Http\Resources\Mobile\LoginResource;
use App\Services\Auth\SocialAuthService;
use Illuminate\Http\JsonResponse;

class SocialLoginController extends Controller
{
	public function __construct(
		private readonly SocialAuthService $socialAuthService,
	) {
		parent::__construct();
	}

	public function login(SocialLoginRequest $request): JsonResponse
	{
		$result = $this->socialAuthService->login([
			'provider' => $request->input('provider'),
			'token' => $request->input('token'),
			'email' => $request->input('email'),
			'fname' => $request->input('fname'),
			'lname' => $request->input('lname'),
		]);

		if (!($result['ok'] ?? false)) {
			return $this->statusFail(
				$result['error'] ?? 'Unable to login user',
				$result['status'] ?? 400
			);
		}

		return $this->statusOk([
			'data' => new LoginResource($result['user']),
			'message' => trans('messages.logged_in_successfully'),
		]);
	}
}
