<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        return $this->response->statusOk([
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
        return $this->response->statusOk(['question' => $q], 201);
    }

    /**
     * Show a single question.
     */
    public function show($id): JsonResponse
    {
        $q = Question::find($id);
        if (!$q) {
            return $this->response->notFound(['message' => 'Question not found'], 404);
        }
        return $this->response->statusOk(['question' => $q]);
    }

    /**
     * Update a question.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $q = Question::find($id);
        if (!$q) {
            return $this->response->notFound(['message' => 'Question not found'], 404);
        }
        $validated = $request->validate([
            'question_en' => 'sometimes|required|string|max:1000',
            'question_ar' => 'nullable|string|max:1000',
            'question_type' => 'nullable|string|max:50',
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
        return $this->response->statusOk(['question' => $q]);
    }

    /**
     * Delete a question.
     */
    public function destroy($id): JsonResponse
    {
        $q = Question::find($id);
        if (!$q) {
            return $this->response->notFound(['message' => 'Question not found'], 404);
        }
        $q->delete();
        return $this->response->statusOk(['message' => 'Deleted', 'id' => (int)$id]);
    }
}
