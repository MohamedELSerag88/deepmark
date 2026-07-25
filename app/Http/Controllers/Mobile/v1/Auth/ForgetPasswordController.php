<?php

namespace App\Http\Controllers\Mobile\v1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\ForgetPasswordRequest;
use App\Http\Resources\Mobile\MessageResource;
use App\Services\Auth\AuthService;

class ForgetPasswordController extends Controller
{
	public function __construct(
		private readonly AuthService $authService,
	) {
		parent::__construct();
	}

	public function forgetPassword(ForgetPasswordRequest $request)
	{
		$result = $this->authService->forgetPassword((string) $request->get('phone'));

		return $this->statusOk(new MessageResource([
			'message' => trans('messages.sms_sent_with_otp'),
			'token' => $result['token'],
		]));
	}
}
