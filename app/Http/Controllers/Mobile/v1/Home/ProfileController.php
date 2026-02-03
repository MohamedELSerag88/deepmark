<?php

namespace App\Http\Controllers\Mobile\v1\Home;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\UpdateProfileRequest;
use App\Models\BrandChat;
use App\Models\MeetingRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
	public function show(): JsonResponse
	{
		$user = auth()->user();
		$chatsCount = BrandChat::where('user_id', $user->id)->count();
		$meetingsCount = MeetingRequest::where('user_id', $user->id)->count();

		$latestChats = BrandChat::where('user_id', $user->id)
			->latest('id')
			->limit(5)
			->get(['id','language','created_at']);

		$latestMeetings = MeetingRequest::where('user_id', $user->id)
			->latest('id')
			->limit(5)
			->get(['id','brand_chat_id','meeting_at','status','created_at']);

		$todos = [
			['key' => 'edit_profile', 'label' => 'Edit your profile details'],
			['key' => 'review_brand_chats', 'label' => 'Review your brand suggestions', 'count' => $chatsCount],
			['key' => 'review_meetings', 'label' => 'Review your meeting requests', 'count' => $meetingsCount],
		];

		return $this->response->statusOk([
			'data' => [
				'user' => $this->profileUserData($user),
				'stats' => [
					'chats_count' => $chatsCount,
					'meetings_count' => $meetingsCount,
				],
				'latest' => [
					'chats' => $latestChats,
					'meetings' => $latestMeetings,
				],
				'todos' => $todos,
			]
		]);
	}

	public function update(UpdateProfileRequest $request): JsonResponse
	{
		$user = auth()->user();
		$data = $request->only(['fname', 'lname', 'country', 'time_zone', 'bio']);

		if ($request->hasFile('image')) {
			if ($user->image) {
				Storage::disk('public')->delete($user->image);
			}
			$path = $request->file('image')->store('profiles', 'public');
			$data['image'] = $path;
		}

		$user->update($data);

		return $this->response->statusOk([
			'data' => $this->profileUserData($user->fresh()),
			'message' => 'Profile updated successfully',
		]);
	}

	/**
	 * Build user payload for profile (show/update) with image URL.
	 */
	private function profileUserData($user): array
	{
		$imageUrl = null;
		if ($user->image) {
			$imageUrl = Storage::disk('public')->url($user->image);
		}

		return [
			'id' => $user->id,
			'fname' => $user->fname,
			'lname' => $user->lname,
			'email' => $user->email,
			'phone' => $user->phone,
			'image' => $imageUrl,
			'country' => $user->country,
			'time_zone' => $user->time_zone,
			'bio' => $user->bio,
		];
	}
}


