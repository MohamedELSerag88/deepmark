<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminUserResource;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
	public function login(Request $request): JsonResponse
	{
		$request->validate([
			'email' => 'required|email',
			'password' => 'required|string|min:4',
		]);

		$credentials = $request->only(['email', 'password']);
		$admin = Admin::where('email', $credentials['email'])->first();
		if (!$admin) {
			return $this->statusFail(['message' => trans('messages.wrong_credentials')], 200);
		}

		if (!$token = auth('admin')->attempt($credentials)) {
			return $this->statusFail(['message' => trans('messages.wrong_credentials')], 200);
		}

		return $this->statusOk([
			'access_token' => $token,
			'token_type' => 'bearer',
			'user' => new AdminUserResource($admin),
		]);
	}

	public function profile(Request $request): JsonResponse
	{
		$admin = auth('admin')->user();
		if (!$admin) {
			return $this->unauthorized(['message' => 'Unauthenticated'], 401);
		}

		return $this->statusOk([
			'user' => new AdminUserResource($admin),
		]);
	}
}
