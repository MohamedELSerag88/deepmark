<?php

namespace App\Http\Controllers\Admin\Marketing;

use App\Http\Controllers\Controller;
use App\Http\Resources\JsonDataResource;
use App\Models\BrandNameSuggestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandNameSuggestionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = BrandNameSuggestion::query()->latest();

        if ($request->boolean('featured_only')) {
            $query->where('is_marketing_featured', true);
        }

        $suggestions = $query->limit(200)->get();

        return $this->statusOk(['projects' => JsonDataResource::collection($suggestions)]);
    }

    public function show($id): JsonResponse
    {
        $suggestion = BrandNameSuggestion::find($id);
        if (!$suggestion) {
            return $this->notFound(['message' => 'Project not found'], 404);
        }
        return $this->statusOk(['project' => new JsonDataResource($suggestion)]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $suggestion = BrandNameSuggestion::find($id);
        if (!$suggestion) {
            return $this->notFound(['message' => 'Project not found'], 404);
        }

        $validated = $request->validate([
            'is_marketing_featured' => 'nullable|boolean',
            'marketing_image_url' => 'nullable|string|max:2000',
            'marketing_author_name' => 'nullable|string|max:255',
            'marketing_author_position' => 'nullable|string|max:255',
            'marketing_author_avatar_url' => 'nullable|string|max:2000',
            'marketing_description_en' => 'nullable|string|max:2000',
            'marketing_description_ar' => 'nullable|string|max:2000',
            'marketing_lead_en' => 'nullable|string|max:5000',
            'marketing_lead_ar' => 'nullable|string|max:5000',
            'marketing_gallery_images' => 'nullable|array',
            'marketing_content_en' => 'nullable|array',
            'marketing_content_ar' => 'nullable|array',
            'marketing_deliverables_en' => 'nullable|array',
            'marketing_deliverables_ar' => 'nullable|array',
        ]);

        $suggestion->update($validated);

        return $this->statusOk(['project' => new JsonDataResource($suggestion->fresh())]);
    }
}
