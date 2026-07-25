<?php

namespace App\Http\Controllers\Mobile\v1\Home;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\UpdateProfileRequest;
use App\Http\Resources\Mobile\MessageResource;
use App\Http\Resources\Mobile\ProfileResource;
use App\Http\Resources\Mobile\ProfileUserResource;
use App\Services\Profile\ProfileService;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
	public function __construct(
		private readonly ProfileService $profileService,
	) {
		parent::__construct();
	}

	public function show(): JsonResponse
	{
		$data = $this->profileService->show(auth()->user());

		return $this->okResource(new ProfileResource($data));
	}

	public function update(UpdateProfileRequest $request): JsonResponse
	{
		$userData = $this->profileService->update(
			auth()->user(),
			$request->only(['fname', 'lname', 'country', 'time_zone', 'bio']),
			$request->file('image')
		);

		return $this->statusOk([
			'data' => new ProfileUserResource($userData),
			'message' => 'Profile updated successfully',
		]);
	}

	public function updatePassword(\Illuminate\Http\Request $request): JsonResponse
	{
		$validated = $request->validate([
			'current_password' => 'required|string',
			'password' => 'required|string|min:8|confirmed',
		]);

		$result = $this->profileService->updatePassword(
			auth()->user(),
			$validated['current_password'],
			$validated['password']
		);

		if (!($result['ok'] ?? false)) {
			return $this->statusFail(['message' => $result['error'] ?? 'Error'], 422);
		}

		return $this->statusOk(new MessageResource(['message' => 'Password updated successfully']));
	}
}
