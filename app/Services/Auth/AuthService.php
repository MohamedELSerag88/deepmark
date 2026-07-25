<?php

namespace App\Services\Auth;

use App\Models\User;

class AuthService
{
	/**
	 * @param  array{phone: string, password: string}  $credentials
	 * @return array{ok: bool, user?: User, token?: string, error?: string}
	 */
	public function login(array $credentials): array
	{
		$user = User::where(['phone' => $credentials['phone']])->first();

		if (!$user) {
			return ['ok' => false, 'error' => 'user_not_found'];
		}

		if (!$token = auth('api')->attempt($credentials)) {
			return ['ok' => false, 'error' => 'wrong_credentials'];
		}

		$user->token = $token;

		return ['ok' => true, 'user' => $user, 'token' => $token];
	}

	/**
	 * @param  array{name: string, email: string, phone: string, password: string}  $data
	 * @return array{ok: bool, user: User, token: string}
	 */
	public function register(array $data): array
	{
		$fullName = trim($data['name']);
		$nameParts = preg_split('/\s+/', $fullName, 2);
		$firstName = $nameParts[0] ?? null;
		$lastName = $nameParts[1] ?? null;

		$user = User::create([
			'fname' => $firstName,
			'lname' => $lastName,
			'email' => $data['email'],
			'phone' => $data['phone'],
			'password' => $data['password'],
		]);
		$user->token = auth('api')->login($user);

		return ['ok' => true, 'user' => $user, 'token' => $user->token];
	}

	/**
	 * @return array{ok: bool, user?: User, error?: string}
	 */
	public function resetPassword(string $resetPassword, string $newPassword): array
	{
		$user = User::where(['reset_password' => $resetPassword])->first();

		if (!$user) {
			return ['ok' => false, 'error' => 'wrong_reset_password_code'];
		}

		$user->password = \Hash::make($newPassword);
		$user->reset_password = null;
		$user->save();
		$user->token = auth('api')->attempt(['phone' => $user->phone, 'password' => $newPassword]);

		return ['ok' => true, 'user' => $user];
	}

	/**
	 * @return array{ok: bool, token?: string, user?: User}
	 */
	public function forgetPassword(string $phone): array
	{
		$user = User::where(['phone' => $phone])->first();
		$resetPassword = rand(pow(10, 3), pow(10, 4) - 1);
		$user->reset_password = $resetPassword;
		$user->save();

		return ['ok' => true, 'token' => (string) $resetPassword, 'user' => $user];
	}
}
