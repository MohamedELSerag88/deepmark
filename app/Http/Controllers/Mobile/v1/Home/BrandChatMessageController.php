<?php

namespace App\Http\Controllers\Mobile\v1\Home;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\StoreBrandChatMessageRequest;
use App\Http\Resources\Mobile\BrandChatMessageResource;
use App\Http\Resources\Mobile\BrandNameItemResource;
use App\Services\Brand\BrandChatService;
use Illuminate\Http\JsonResponse;

class BrandChatMessageController extends Controller
{
	public function __construct(
		private readonly BrandChatService $brandChatService,
	) {
		parent::__construct();
	}

	public function index(int|string $id): JsonResponse
	{
		$result = $this->brandChatService->listMessages((int) $id, auth('api')->id());

		if ($result === null) {
			return $this->notFound(['message' => 'Project not found']);
		}

		return $this->statusOk([
			'data' => [
				'project_id' => $result['project_id'],
				'messages' => BrandChatMessageResource::collection($result['messages']),
			],
		]);
	}

	public function store(StoreBrandChatMessageRequest $request, int|string $id): JsonResponse
	{
		$result = $this->brandChatService->sendMessage(
			(int) $id,
			[
				'message' => $request->input('message'),
				'tlds' => (array) $request->input('tlds', ['com', 'io', 'ai']),
			],
			auth('api')->id()
		);

		if ($result === null) {
			return $this->notFound(['message' => 'Project not found']);
		}

		$payload = $result['payload'] ?? [];

		return $this->statusOk([
			'data' => [
				'project_id' => $result['project_id'],
				'user_message' => new BrandChatMessageResource($result['user_message']),
				'assistant_message' => new BrandChatMessageResource($result['assistant_message']),
				'payload' => [
					'response_message' => $payload['response_message'] ?? null,
					'items' => BrandNameItemResource::collection(collect($payload['items'] ?? [])),
				],
			],
			'message' => 'Chat updated successfully',
		]);
	}
}
