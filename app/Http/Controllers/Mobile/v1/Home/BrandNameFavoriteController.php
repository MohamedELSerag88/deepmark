<?php

namespace App\Http\Controllers\Mobile\v1\Home;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\SaveFavoriteNameRequest;
use App\Http\Resources\Mobile\BrandFavoriteResource;
use App\Http\Resources\Mobile\MessageResource;
use App\Services\Brand\BrandFavoriteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandNameFavoriteController extends Controller
{
	public function __construct(
		private readonly BrandFavoriteService $brandFavoriteService,
	) {
		parent::__construct();
	}

	public function index(Request $request): JsonResponse
	{
		$items = $this->brandFavoriteService->list(auth('api')->id(), [
			'brand_chat_id' => $request->input('brand_chat_id'),
			'project_id' => $request->input('project_id'),
			'name' => $request->input('name'),
			'archetype' => $request->input('archetype'),
		]);

		return $this->statusOk([
			'data' => [
				'items' => BrandFavoriteResource::collection($items),
			],
		]);
	}

	public function store(SaveFavoriteNameRequest $request): JsonResponse
	{
		$result = $this->brandFavoriteService->store(
			(int) $request->input('project_id'),
			(int) $request->input('suggestion_id'),
			auth('api')->id()
		);

		if (!($result['ok'] ?? false)) {
			return $this->statusFail($result['error'] ?? 'Error', $result['status'] ?? 400);
		}

		return $this->okResource(new MessageResource(['id' => $result['id']]));
	}

	public function destroy(int $id): JsonResponse
	{
		$result = $this->brandFavoriteService->destroy($id, auth('api')->id());
		if (!($result['ok'] ?? false)) {
			return $this->statusFail($result['error'] ?? 'Error', $result['status'] ?? 400);
		}

		return $this->statusOk(new MessageResource(['message' => 'Removed from favorites']));
	}
}
