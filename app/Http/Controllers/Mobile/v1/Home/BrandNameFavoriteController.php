<?php

namespace App\Http\Controllers\Mobile\v1\Home;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\SaveFavoriteNameRequest;
use App\Models\BrandNameFavorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandNameFavoriteController extends Controller
{
	public function index(): JsonResponse
	{
		$items = BrandNameFavorite::where('user_id', auth()->id())
			->latest('id')
			->get(['id','name','archetype','domains','brand_chat_id','created_at']);

		return $this->response->statusOk([ 'data' => [ 'items' => $items ] ]);
	}

	public function store(SaveFavoriteNameRequest $request): JsonResponse
	{
		$exists = BrandNameFavorite::where('user_id', auth()->id())
			->where('name', $request->input('name'))
			->first();
		if ($exists) {
			return $this->response->statusOk([ 'data' => [ 'id' => $exists->id ] ]);
		}

		$item = BrandNameFavorite::create([
			'user_id' => auth()->id(),
			'brand_chat_id' => $request->input('brand_chat_id'),
			'name' => $request->input('name'),
			'archetype' => $request->input('archetype'),
			'domains' => $request->input('domains'),
		]);

		return $this->response->statusOk([ 'data' => [ 'id' => $item->id ] ]);
	}

	public function destroy(int $id): JsonResponse
	{
		$item = BrandNameFavorite::where('id', $id)->where('user_id', auth()->id())->first();
		if (!$item) {
			return $this->response->statusFail('Favorite not found', 404);
		}
		$item->delete();
		return $this->response->statusOk([ 'message' => 'Removed from favorites' ]);
	}
}


