<?php

namespace App\Http\Controllers\Mobile\v1\Home;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\SaveFavoriteNameRequest;
use App\Models\BrandChat;
use App\Models\BrandNameFavorite;
use App\Models\BrandNameSuggestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandNameFavoriteController extends Controller
{
	public function index(Request $request): JsonResponse
	{
		$query = BrandNameFavorite::where('user_id', auth('api')->id());

		// Support both keys for backward compatibility.
		$brandChatId = $request->input('brand_chat_id', $request->input('project_id'));
		if ($brandChatId !== null && $brandChatId !== '') {
			$query->where('brand_chat_id', (int)$brandChatId);
		}

		if ($request->filled('name')) {
			$query->whereHas('suggestion', function ($q) use ($request) {
				$q->where('name', 'like', '%' . $request->input('name') . '%');
			});
		}
		if ($request->filled('archetype')) {
			$query->whereHas('suggestion', function ($q) use ($request) {
				$q->where('archetype', 'like', '%' . $request->input('archetype') . '%');
			});
		}

		$items = $query
			->with(['suggestion:id,brand_chat_id,suggestion_index,name,archetype,domains,liked'])
			->latest('id')
			->get([
				'id',
				'brand_chat_id',
				'brand_name_suggestion_id',
				'created_at'
			]);

		return $this->response->statusOk([ 'data' => [ 'items' => $items ] ]);
	}

	public function store(SaveFavoriteNameRequest $request): JsonResponse
	{
		$brandChatId = (int)$request->input('project_id');
		$suggestionId = (int)$request->input('suggestion_id');

		$chat = BrandChat::where('id', $brandChatId)
			->where('user_id', auth('api')->id())
			->first();
		if (!$chat) {
			return $this->response->statusFail('Project not found', 404);
		}

		$suggestion = BrandNameSuggestion::where('id', $suggestionId)
			->where('brand_chat_id', $brandChatId)
			->first();
		if (!$suggestion) {
			return $this->response->statusFail('Suggestion not found for this project', 404);
		}

		$exists = BrandNameFavorite::where('user_id', auth('api')->id())
			->where('brand_chat_id', $brandChatId)
			->where('brand_name_suggestion_id',$suggestionId)
			->first();
		if ($exists) {
			return $this->response->statusOk([ 'data' => [ 'id' => $exists->id ] ]);
		}

		$item = BrandNameFavorite::create([
			'user_id' => auth('api')->id(),
			'brand_chat_id' => $brandChatId,
			'brand_name_suggestion_id' => $suggestion->id
		]);

		return $this->response->statusOk([ 'data' => [ 'id' => $item->id ] ]);
	}

	public function destroy(int $id): JsonResponse
	{
		$item = BrandNameFavorite::where('id', $id)->where('user_id', auth('api')->id())->first();
		if (!$item) {
			return $this->response->statusFail('Favorite not found', 404);
		}
		$item->delete();
		return $this->response->statusOk([ 'message' => 'Removed from favorites' ]);
	}
}


