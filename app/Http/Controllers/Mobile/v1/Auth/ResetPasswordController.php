<?php

namespace App\Http\Controllers\Mobile\v1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\RestPasswordRequest;
use App\Http\Resources\Mobile\LoginResource;
use App\Services\Auth\AuthService;

class ResetPasswordController extends Controller
{
	public function __construct(
		private readonly AuthService $authService,
	) {
		parent::__construct();
	}

	public function resetPassword(RestPasswordRequest $request)
	{
		$result = $this->authService->resetPassword(
			(string) $request->input('reset_password'),
			(string) $request->input('new_password')
		);

		if (!($result['ok'] ?? false)) {
			return $this->statusFail(trans('messages.wrong_reset_password_code'));
		}

		return $this->statusOk([
			'data' => new LoginResource($result['user']),
			'message' => trans('messages.password_updated_successfully'),
		]);
	}
}
