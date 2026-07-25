<?php

namespace App\Http\Controllers\Mobile\v1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\CheckOtpRequest;
use App\Http\Resources\Mobile\LoginResource;
use App\Services\Auth\OtpService;

class CheckOtpController extends Controller
{
	public function __construct(
		private readonly OtpService $otpService,
	) {
		parent::__construct();
	}

	public function checkOtp(CheckOtpRequest $request)
	{
		$result = $this->otpService->check(
			(string) $request->input('email'),
			(string) $request->input('otp_code'),
			request()->get('device_token')
		);

		if (!($result['ok'] ?? false)) {
			$error = $result['error'] ?? 'wrong_otp_token';
			$messageKey = match ($error) {
				'otp_expired' => 'messages.otp_expired',
				'wrong_user_data' => 'messages.wrong_user_data',
				default => 'messages.wrong_otp_token',
			};

			return $this->statusFail(trans($messageKey));
		}

		return $this->statusOk([
			'data' => new LoginResource($result['user']),
			'message' => trans('messages.logged_in_successfully'),
		]);
	}
}
