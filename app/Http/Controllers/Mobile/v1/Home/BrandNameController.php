<?php

namespace App\Http\Controllers\Mobile\v1\Home;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\CreateBrandNamesRequest;
use App\Http\Resources\Mobile\BrandGenerateResource;
use App\Services\Brand\BrandNameService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandNameController extends Controller
{
	public function __construct(
		private readonly BrandNameService $brandNameService,
	) {
		parent::__construct();
	}

	public function generate(CreateBrandNamesRequest $request): JsonResponse
	{
		try {
			$data = $this->brandNameService->generate(
				[
					'answers' => $request->input('answers', []),
					'language' => $request->input('language', 'en'),
					'count' => (int) $request->input('count', 12),
					'tlds' => (array) $request->input('tlds', ['com', 'io', 'ai']),
					'device_token' => $request->get('device_token'),
				],
				auth('api')->id()
			);

			return $this->okResource(new BrandGenerateResource($data));
		} catch (\Throwable $e) {
			return $this->statusFail(
				['message' => 'Failed to generate brand names.', 'error' => $e->getMessage()],
				500
			);
		}
	}

	public function edit(Request $request): JsonResponse
	{
		$chatId = (int) $request->input('chat_id');
		$comment = (string) $request->input('comment', '');
		$tlds = (array) $request->input('tlds', ['com', 'io', 'ai']);

		$data = $this->brandNameService->edit($chatId, $comment, $tlds, auth('api')->id());
		if ($data === null) {
			return $this->statusFail('Chat not found', 404);
		}

		return $this->okResource(new BrandGenerateResource($data));
	}

	public function similar(Request $request): JsonResponse
	{
		$validated = $request->validate([
			'project_id' => 'required|integer',
			'name' => 'required|string|max:255',
			'count' => 'nullable|integer|min:1|max:50',
			'tlds' => 'nullable|array',
		]);

		try {
			$data = $this->brandNameService->generateSimilar(
				(int) $validated['project_id'],
				(string) $validated['name'],
				(array) ($validated['tlds'] ?? ['com', 'net', 'io', 'co', 'ai']),
				(int) ($validated['count'] ?? 12),
				auth('api')->id()
			);
			if ($data === null) {
				return $this->statusFail('Project not found', 404);
			}

			return $this->okResource(new BrandGenerateResource($data));
		} catch (\Throwable $e) {
			return $this->statusFail(
				['message' => 'Failed to generate similar names.', 'error' => $e->getMessage()],
				500
			);
		}
	}
}
