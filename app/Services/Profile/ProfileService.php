<?php

namespace App\Services\Profile;

use App\Models\BrandChat;
use App\Models\MeetingRequest;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
	/**
	 * @return array{user: array, stats: array, latest: array, todos: array}
	 */
	public function show(User $user): array
	{
		$chatsCount = BrandChat::where('user_id', $user->id)->count();
		$meetingsCount = MeetingRequest::where('user_id', $user->id)->count();

		$latestChats = BrandChat::where('user_id', $user->id)
			->latest('id')
			->limit(5)
			->get(['id', 'language', 'created_at']);

		$latestMeetings = MeetingRequest::where('user_id', $user->id)
			->latest('id')
			->limit(5)
			->get(['id', 'brand_chat_id', 'meeting_at', 'status', 'created_at']);

		$todos = [
			['key' => 'edit_profile', 'label' => 'Edit your profile details'],
			['key' => 'review_brand_chats', 'label' => 'Review your brand suggestions', 'count' => $chatsCount],
			['key' => 'review_meetings', 'label' => 'Review your meeting requests', 'count' => $meetingsCount],
		];

		return [
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
		];
	}

	/**
	 * @param  array{fname?: string, lname?: string, country?: string|null, time_zone?: string|null, bio?: string|null}  $data
	 */
	public function update(User $user, array $data, ?UploadedFile $image = null): array
	{
		$payload = collect($data)->only(['fname', 'lname', 'country', 'time_zone', 'bio'])->all();

		if ($image) {
			if ($user->image) {
				Storage::disk('public')->delete($user->image);
			}
			$payload['image'] = $image->store('profiles', 'public');
		}

		$user->update($payload);

		return $this->profileUserData($user->fresh());
	}

	/**
	 * @return array{ok: bool, error?: string}
	 */
	public function updatePassword(User $user, string $currentPassword, string $password): array
	{
		if (!Hash::check($currentPassword, $user->getAuthPassword())) {
			return ['ok' => false, 'error' => 'Current password is incorrect'];
		}

		$user->password = $password;
		$user->save();

		return ['ok' => true];
	}

	public function profileUserData(User $user): array
	{
		$imageUrl = null;
		if ($user->image) {
			$image = (string) $user->image;
			$imageUrl = (str_starts_with($image, 'http://') || str_starts_with($image, 'https://'))
				? $image
				: Storage::disk('public')->url($image);
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
			'name' => $user->name,
		];
	}
}
