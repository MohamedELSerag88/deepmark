<?php

namespace App\Http\Controllers\Mobile\v1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\SendOtpRequest;
use App\Http\Resources\Mobile\MessageResource;
use App\Services\Auth\OtpService;

class SendOtpController extends Controller
{
	public function __construct(
		private readonly OtpService $otpService,
	) {
		parent::__construct();
	}

	public function sendOtp(SendOtpRequest $request)
	{
		$result = $this->otpService->send((string) $request->input('email'));

		if (!($result['ok'] ?? false)) {
			return $this->statusFail($result['error'] ?? 'Error', 500);
		}

		return $this->statusOk(new MessageResource(['message' => trans('messages.sms_sent_with_otp')]));
	}
}
