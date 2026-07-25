<?php

namespace App\Services\Meeting;

use App\Models\BrandChat;
use App\Models\MeetingRequest;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MeetingService
{
	public function listForUser(?int $userId): Collection
	{
		return MeetingRequest::where('user_id', $userId)
			->latest('id')
			->get(['id', 'brand_chat_id', 'meeting_at', 'notes', 'status', 'created_at']);
	}

	/**
	 * @param  array{brand_id: int, meeting_at?: mixed, date?: string|null, time?: string|null, notes?: string|null}  $data
	 * @return array{ok: bool, error?: string, status?: int, meeting?: MeetingRequest}
	 */
	public function store(array $data, ?int $userId): array
	{
		$brandChatId = (int) $data['brand_id'];
		$brand = BrandChat::where('id', $brandChatId)
			->where('user_id', $userId)
			->first();
		if (!$brand) {
			return ['ok' => false, 'error' => 'Brand chat not found', 'status' => 404];
		}

		$meetingAt = $data['meeting_at'] ?? null;
		if (!$meetingAt) {
			$date = $data['date'] ?? null;
			$time = $data['time'] ?? null;
			$meetingAt = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $time);
		} else {
			$meetingAt = Carbon::parse($meetingAt);
		}
		if ($meetingAt->isPast()) {
			return ['ok' => false, 'error' => 'Meeting time must be in the future', 'status' => 422];
		}

		$meeting = MeetingRequest::create([
			'user_id' => $userId,
			'brand_chat_id' => $brand->id,
			'meeting_at' => $meetingAt,
			'notes' => $data['notes'] ?? null,
			'status' => 'pending',
		]);

		return ['ok' => true, 'meeting' => $meeting];
	}
}
