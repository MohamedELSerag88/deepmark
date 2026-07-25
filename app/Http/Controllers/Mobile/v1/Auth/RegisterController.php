<?php

namespace App\Http\Controllers\Mobile\v1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\RegisterRequest;
use App\Http\Resources\Mobile\LoginResource;
use App\Services\Auth\AuthService;

class RegisterController extends Controller
{
	public function __construct(
		private readonly AuthService $authService,
	) {
		parent::__construct();
	}

	public function register(RegisterRequest $request)
	{
		$result = $this->authService->register($request->validated());

		return $this->statusOk([
			'data' => new LoginResource($result['user']),
			'message' => trans('messages.user_created_successfully'),
		]);
	}
}
