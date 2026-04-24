<?php

namespace App\Http\Controllers\Mobile\v1\Home;

use App\Http\Controllers\Controller;
use App\Models\BrandChat;
use App\Models\BrandNameFavorite;
use App\Models\BrandNameSuggestion;
use App\Models\Question;
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
        $userId = auth('api')->id();

        $query = BrandChat::where('user_id', $userId)->latest('id');

        if ($topic = $request->query('topic')) {
            $query->where('topic', $topic);
        }

        $perPage = (int)$request->query('per_page', 10);
        $projects = $query->paginate($perPage);

        $items = collect($projects->items())->map(fn (BrandChat $chat) => $this->serializeProject($chat));

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

    /**
     * Retrieve a single project (BrandChat) by ID for the authenticated user.
     */
    public function show(int|string $id): JsonResponse
    {
        $userId = auth('api')->id();
        $project = BrandChat::where('user_id', $userId)->where('id', (int)$id)->first();
        if (!$project) {
            return $this->response->notFound(['message' => 'Project not found']);
        }

        $filters = [
            'name' => (string)request()->query('name', ''),
            'archetype' => (string)request()->query('archetype', ''),
        ];

        return $this->response->statusOk([
            'data' => [
                'project' => $this->serializeProject($project, $filters),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeProject(BrandChat $chat, array $filters = []): array
    {
        $response = $chat->response;
        $archetypes = [];
        $answers = $this->formatAnswers($chat->answers);
        $favoritedSuggestionIds = BrandNameFavorite::where('user_id', auth('api')->id())
            ->where('brand_chat_id', $chat->id)
            ->pluck('brand_name_suggestion_id')
            ->filter(fn ($id) => $id !== null)
            ->map(fn ($id) => (int)$id)
            ->all();
        $favoritedLookup = array_fill_keys($favoritedSuggestionIds, true);

        $allSuggestions = BrandNameSuggestion::where('brand_chat_id', $chat->id)
            ->orderBy('suggestion_index')
            ->get();

        $archetypes = $allSuggestions
            ->pluck('archetype')
            ->filter(fn ($value) => is_string($value) && trim($value) !== '')
            ->unique()
            ->values()
            ->all();

        $suggestionsQuery = BrandNameSuggestion::where('brand_chat_id', $chat->id);
        if (!empty($filters['name'])) {
            $suggestionsQuery->where('name', 'like', '%' . $filters['name'] . '%');
        }
        if (!empty($filters['archetype'])) {
            $suggestionsQuery->where('archetype', 'like', '%' . $filters['archetype'] . '%');
        }

        $items = $suggestionsQuery
            ->orderBy('suggestion_index')
            ->get()
            ->map(fn (BrandNameSuggestion $s) => [
                'suggestion_index' => $s->suggestion_index,
                'id' => $s->id,
                'project_id' => $chat->id,
                'name' => $s->name,
                'archetype' => $s->archetype,
                'domains' => $s->domains,
                'liked' => isset($favoritedLookup[(int)$s->id]),
            ])
            ->values();

        return [
            'id' => $chat->id,
            'project_id' => $chat->id,
            'chat_id' => $chat->id,
            'parent_id' => $chat->parent_id,
            'topic' => $chat->topic,
            'language' => $chat->language,
            'answers' => $answers,
            'archetype' => $archetypes,
            'items' => $items,
            'raw_response' => $chat->raw_response,
            'created_at' => $chat->created_at,
            'device_token' => $chat->device_token ?? null,
        ];
    }

    /**
     * Convert stored answers from [{question_id, value}] to [{question, answer}].
     *
     * @param mixed $answers
     * @return array<int, array<string, mixed>>
     */
    private function formatAnswers($answers): array
    {
        if (!is_array($answers)) {
            return [];
        }

        $questionIds = collect($answers)
            ->pluck('question_id')
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int)$id)
            ->unique()
            ->values()
            ->all();

        $questions = Question::whereIn('id', $questionIds)
            ->get()
            ->keyBy('id');

        return collect($answers)->map(function ($item) use ($questions) {
            if (!is_array($item)) {
                return null;
            }

            $questionId = isset($item['question_id']) ? (int)$item['question_id'] : null;
            $question = $questionId ? $questions->get($questionId) : null;

            return [
                'question' => $question ? [
                    'id' => $question->id,
                    'question_en' => $question->question_en,
                    'question_ar' => $question->question_ar,
                    'question_type' => $question->question_type,
                ] : null,
                'answer' => $item['value'] ?? null,
            ];
        })
            ->filter()
            ->values()
            ->all();
    }
}

