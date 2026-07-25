<?php

namespace App\Http\Controllers\Mobile\v1\Home;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\CreateMeetingRequest;
use App\Http\Resources\Mobile\MeetingResource;
use App\Services\Meeting\MeetingService;
use Illuminate\Http\JsonResponse;

class MeetingController extends Controller
{
	public function __construct(
		private readonly MeetingService $meetingService,
	) {
		parent::__construct();
	}

	public function index(): JsonResponse
	{
		return $this->okResource(
			MeetingResource::collection($this->meetingService->listForUser(auth('api')->id()))
		);
	}

	public function store(CreateMeetingRequest $request): JsonResponse
	{
		$result = $this->meetingService->store(
			[
				'brand_id' => (int) $request->input('brand_id'),
				'meeting_at' => $request->input('meeting_at'),
				'date' => $request->input('date'),
				'time' => $request->input('time'),
				'notes' => $request->input('notes'),
			],
			auth('api')->id()
		);

		if (!($result['ok'] ?? false)) {
			return $this->statusFail($result['error'] ?? 'Error', $result['status'] ?? 400);
		}

		return $this->statusOk([
			'data' => new MeetingResource($result['meeting']),
			'message' => 'Meeting request created',
		]);
	}
}
