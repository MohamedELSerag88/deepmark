<?php

namespace App\Services\Auth;

use App\Mail\OtpCodeMail;
use App\Models\BrandChat;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class OtpService
{
	/**
	 * @return array{ok: bool, error?: string}
	 */
	public function send(string $email): array
	{
		$user = User::firstOrNew(['email' => $email]);

		try {
			$otpCode = 123456; // random_int(100000, 999999);
			$user->otp_token = Hash::make((string) $otpCode);
			$user->otp_sent_at = Carbon::now();
			if (!$user->exists) {
				$user->password = null;
			}
			$user->save();
			Mail::to($user->email)->send(new OtpCodeMail($otpCode));

			return ['ok' => true];
		} catch (\Exception $exception) {
			return ['ok' => false, 'error' => $exception->getMessage()];
		}
	}

	/**
	 * @return array{ok: bool, user?: User, error?: string}
	 */
	public function check(string $email, string $code, ?string $deviceToken = null): array
	{
		$checkUser = User::where('email', $email)->first();
		$otpExpire = 10; // minutes
		if (!$checkUser || !$checkUser->otp_token) {
			return ['ok' => false, 'error' => 'wrong_otp_token'];
		}
		if (Carbon::now()->diffInMinutes($checkUser->otp_sent_at) > $otpExpire) {
			return ['ok' => false, 'error' => 'otp_expired'];
		}
		if (!Hash::check((string) $code, $checkUser->otp_token)) {
			return ['ok' => false, 'error' => 'wrong_otp_token'];
		}

		if (!$token = auth('api')->login($checkUser)) {
			return ['ok' => false, 'error' => 'wrong_user_data'];
		}

		$checkUser->update(['otp_token' => null]);
		$checkUser->token = $token;
		BrandChat::whereNull('user_id')
			->where('device_token', $deviceToken)
			->update(['user_id' => $checkUser->id]);

		return ['ok' => true, 'user' => $checkUser];
	}
}
