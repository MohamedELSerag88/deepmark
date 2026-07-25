<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\MessageResource;
use App\Http\Resources\Mobile\QuestionResource;
use App\Models\Question;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    /**
     * List questions (same shape as mobile API for testing).
     */
    public function index(): JsonResponse
    {
        $questions = Question::query()->latest()->get();
        return $this->statusOk([
            'data' => QuestionResource::collection($questions),
        ]);
    }

    /**
     * Create a new question.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question_en' => 'required|string|max:1000',
            'question_ar' => 'nullable|string|max:1000',
            'question_type' => 'nullable|string|max:50',
            'prompt_key' => 'nullable|string|max:100',
            'answers' => 'nullable|array',
            'description_en' => 'nullable|string|max:2000',
            'description_ar' => 'nullable|string|max:2000',
            'why_matters' => 'nullable|string|max:2000',
            'video_url' => 'nullable|string|max:2000',
            'video_path' => 'nullable|string|max:2000',
            'image_url' => 'nullable|string|max:2000',
            'example_answer' => 'nullable|string|max:1000',
            'resources' => 'nullable|array',
        ]);

        $q = Question::create($validated);
        return $this->statusOk(['question' => new QuestionResource($q)], 201);
    }

    /**
     * Show a single question.
     */
    public function show($id): JsonResponse
    {
        $q = Question::find($id);
        if (!$q) {
            return $this->notFound(['message' => 'Question not found'], 404);
        }
        return $this->statusOk(['question' => new QuestionResource($q)]);
    }

    /**
     * Update a question.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $q = Question::find($id);
        if (!$q) {
            return $this->notFound(['message' => 'Question not found'], 404);
        }
        $validated = $request->validate([
            'question_en' => 'sometimes|required|string|max:1000',
            'question_ar' => 'nullable|string|max:1000',
            'question_type' => 'nullable|string|max:50',
            'prompt_key' => 'nullable|string|max:100',
            'answers' => 'nullable|array',
            'description_en' => 'nullable|string|max:2000',
            'description_ar' => 'nullable|string|max:2000',
            'why_matters' => 'nullable|string|max:2000',
            'video_url' => 'nullable|string|max:2000',
            'video_path' => 'nullable|string|max:2000',
            'image_url' => 'nullable|string|max:2000',
            'example_answer' => 'nullable|string|max:1000',
            'resources' => 'nullable|array',
        ]);
        $q->fill($validated)->save();
        return $this->statusOk(['question' => new QuestionResource($q)]);
    }

    /**
     * Delete a question.
     */
    public function destroy($id): JsonResponse
    {
        $q = Question::find($id);
        if (!$q) {
            return $this->notFound(['message' => 'Question not found'], 404);
        }
        $q->delete();
        return $this->statusOk(new MessageResource(['message' => 'Deleted', 'id' => (int) $id]));
    }
}
