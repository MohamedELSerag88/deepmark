<?php

namespace App\Http\Controllers\Mobile\v1\Home;

use App\Http\Controllers\Controller;
use App\Models\BrandChat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * List all projects (BrandChat) for the authenticated user.
     * Optional query params: page, per_page, topic (brand_names|brand_text)
     */
    public function index(Request $request): JsonResponse
    {
        $userId = auth()->id();

        $query = BrandChat::where('user_id', $userId)->latest('id');

        if ($topic = $request->query('topic')) {
            $query->where('topic', $topic);
        }

        $perPage = (int)$request->query('per_page', 10);
        $projects = $query->paginate($perPage);

        $items = collect($projects->items())->map(function (BrandChat $chat) {
            return [
                'id' => $chat->id,
                'parent_id' => $chat->parent_id,
                'topic' => $chat->topic,
                'language' => $chat->language,
                'answers' => $chat->answers,
                'response' => $chat->response,
                'raw_response' => $chat->raw_response,
                'created_at' => $chat->created_at,
                'updated_at' => $chat->updated_at,
                'device_token' => $chat->device_token ?? null,
            ];
        });

        return $this->response->statusOk([
            'projects' => $items,
            'pagination' => [
                'current_page' => $projects->currentPage(),
                'per_page' => $projects->perPage(),
                'total' => $projects->total(),
                'last_page' => $projects->lastPage(),
            ],
        ]);
    }
}

