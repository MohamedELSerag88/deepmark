<?php

namespace App\Http\Controllers\Admin\Marketing;

use App\Http\Controllers\Controller;
use App\Http\Resources\JsonDataResource;
use App\Http\Resources\Mobile\MessageResource;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaqController extends Controller
{
	public function index(): JsonResponse
	{
		$faqs = Faq::query()->orderBy('sort_order')->get();

		return $this->statusOk(['faqs' => JsonDataResource::collection($faqs)]);
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

		return $this->statusOk(['faq' => new JsonDataResource($faq)], 201);
	}

	public function show($id): JsonResponse
	{
		$faq = Faq::find($id);
		if (!$faq) {
			return $this->notFound(['message' => 'FAQ not found'], 404);
		}

		return $this->statusOk(['faq' => new JsonDataResource($faq)]);
	}

	public function update(Request $request, $id): JsonResponse
	{
		$faq = Faq::find($id);
		if (!$faq) {
			return $this->notFound(['message' => 'FAQ not found'], 404);
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

		return $this->statusOk(['faq' => new JsonDataResource($faq->fresh())]);
	}

	public function destroy($id): JsonResponse
	{
		$faq = Faq::find($id);
		if (!$faq) {
			return $this->notFound(['message' => 'FAQ not found'], 404);
		}
		$faq->delete();

		return $this->statusOk(new MessageResource(['message' => 'Deleted', 'id' => (int) $id]));
	}
}
