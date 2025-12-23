<?php

namespace App\Http\Controllers\Mobile\v1\Home;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\CreateMeetingRequest;
use App\Models\BrandChat;
use App\Models\MeetingRequest;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class MeetingController extends Controller {


	public function index(): JsonResponse
	{
		$list = MeetingRequest::where('user_id', auth()->id())
			->latest('id')
			->get(['id','brand_chat_id','meeting_at','notes','status','created_at']);

		return $this->response->statusOk([
			'data' => $list
		]);
	}

	public function store(CreateMeetingRequest $request): JsonResponse
	{
		$brandChatId = (int)$request->input('brand_id');
		$brand = BrandChat::where('id', $brandChatId)
			->where('user_id', auth()->id())
			->first();
		if (!$brand) {
			return $this->response->statusFail('Brand chat not found', 404);
		}

		$meetingAt = $request->input('meeting_at');
		if (!$meetingAt) {
			$date = $request->input('date'); // Y-m-d
			$time = $request->input('time'); // H:i
			$meetingAt = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $time);
		} else {
			$meetingAt = Carbon::parse($meetingAt);
		}
		if ($meetingAt->isPast()) {
			return $this->response->statusFail('Meeting time must be in the future', 422);
		}

		$meeting = MeetingRequest::create([
			'user_id' => auth()->id(),
			'brand_chat_id' => $brand->id,
			'meeting_at' => $meetingAt,
			'notes' => $request->input('notes'),
			'status' => 'pending',
		]);

		return $this->response->statusOk([
			'data' => [
				'id' => $meeting->id,
				'brand_chat_id' => $meeting->brand_chat_id,
				'meeting_at' => $meeting->meeting_at,
				'status' => $meeting->status,
			],
			'message' => 'Meeting request created'
		]);
	}
}


