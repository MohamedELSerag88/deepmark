<?php

namespace App\Http\Controllers\Admin\Marketing;

use App\Http\Controllers\Controller;
use App\Models\HomeSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeSectionController extends Controller
{
    public function index(): JsonResponse
    {
        $sections = HomeSection::query()->orderBy('sort_order')->get();
        return $this->response->statusOk(['sections' => $sections]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'section_key' => 'required|string|max:100|unique:home_sections,section_key',
            'content_en' => 'required|array',
            'content_ar' => 'nullable|array',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $section = HomeSection::create($validated);
        return $this->response->statusOk(['section' => $section], 201);
    }

    public function show($id): JsonResponse
    {
        $section = HomeSection::find($id);
        if (!$section) {
            return $this->response->notFound(['message' => 'Home section not found'], 404);
        }
        return $this->response->statusOk(['section' => $section]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $section = HomeSection::find($id);
        if (!$section) {
            return $this->response->notFound(['message' => 'Home section not found'], 404);
        }

        $validated = $request->validate([
            'section_key' => 'sometimes|required|string|max:100|unique:home_sections,section_key,' . $id,
            'content_en' => 'sometimes|required|array',
            'content_ar' => 'nullable|array',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $section->update($validated);
        return $this->response->statusOk(['section' => $section->fresh()]);
    }

    public function destroy($id): JsonResponse
    {
        $section = HomeSection::find($id);
        if (!$section) {
            return $this->response->notFound(['message' => 'Home section not found'], 404);
        }
        $section->delete();
        return $this->response->statusOk(['message' => 'Deleted', 'id' => (int) $id]);
    }
}
