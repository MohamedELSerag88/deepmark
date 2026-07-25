<?php

namespace App\Http\Controllers\Mobile\v1\Marketing;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\Marketing\BrandNameSuggestionResource;
use App\Models\BrandNameSuggestion;
use Illuminate\Http\JsonResponse;

class BrandNameSuggestionController extends Controller
{
    public function index(): JsonResponse
    {
        $suggestions = BrandNameSuggestion::query()
            ->forMarketing()
            ->latest()
            ->limit(24)
            ->get();

        return $this->statusOk([
            'data' => BrandNameSuggestionResource::collection($suggestions),
        ]);
    }

    public function show($id): JsonResponse
    {
        $suggestion = BrandNameSuggestion::query()
            ->where('id', $id)
            ->forMarketing()
            ->first();

        if (!$suggestion) {
            return $this->notFound(['message' => 'Project not found'], 404);
        }

        return $this->statusOk([
            'data' => new BrandNameSuggestionResource($suggestion),
        ]);
    }
}
