<?php

namespace App\Http\Controllers\Admin\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index(): JsonResponse
    {
        $faqs = Faq::query()->orderBy('sort_order')->get();
        return $this->response->statusOk(['faqs' => $faqs]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question_en' => 'required|string|max:500',
            'question_ar' => 'nullable|string|max:500',
            'answer_en' => 'required|string|max:5000',
            'answer_ar' => 'nullable|string|max:5000',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $faq = Faq::create($validated);
        return $this->response->statusOk(['faq' => $faq], 201);
    }

    public function show($id): JsonResponse
    {
        $faq = Faq::find($id);
        if (!$faq) {
            return $this->response->notFound(['message' => 'FAQ not found'], 404);
        }
        return $this->response->statusOk(['faq' => $faq]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $faq = Faq::find($id);
        if (!$faq) {
            return $this->response->notFound(['message' => 'FAQ not found'], 404);
        }
        $validated = $request->validate([
            'question_en' => 'sometimes|required|string|max:500',
            'question_ar' => 'nullable|string|max:500',
            'answer_en' => 'sometimes|required|string|max:5000',
            'answer_ar' => 'nullable|string|max:5000',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $faq->update($validated);
        return $this->response->statusOk(['faq' => $faq->fresh()]);
    }

    public function destroy($id): JsonResponse
    {
        $faq = Faq::find($id);
        if (!$faq) {
            return $this->response->notFound(['message' => 'FAQ not found'], 404);
        }
        $faq->delete();
        return $this->response->statusOk(['message' => 'Deleted', 'id' => (int) $id]);
    }
}
