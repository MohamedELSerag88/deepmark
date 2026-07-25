<?php

namespace App\Services\Brand;

use App\Models\BrandChat;
use App\Models\BrandNameFavorite;
use App\Models\BrandNameSuggestion;
use App\Models\Question;

class ProjectService
{
	/**
	 * @return array{projects: \Illuminate\Support\Collection, pagination: array}
	 */
	public function index(?int $userId, ?string $topic = null, int $perPage = 10): array
	{
		$query = BrandChat::where('user_id', $userId)->latest('id');

		if ($topic) {
			$query->where('topic', $topic);
		}

		$projects = $query->paginate($perPage);

		$items = collect($projects->items())->map(
			fn (BrandChat $chat) => $this->serializeProject($chat, [], $userId)
		);

		return [
			'projects' => $items,
			'pagination' => [
				'current_page' => $projects->currentPage(),
				'per_page' => $projects->perPage(),
				'total' => $projects->total(),
				'last_page' => $projects->lastPage(),
			],
		];
	}

	/**
	 * @param  array{name?: string, archetype?: string}  $filters
	 * @return array<string, mixed>|null
	 */
	public function show(int $id, ?int $userId, array $filters = []): ?array
	{
		$project = BrandChat::where('user_id', $userId)->where('id', $id)->first();
		if (!$project) {
			return null;
		}

		return $this->serializeProject($project, $filters, $userId);
	}

	/**
	 * Persist the user's chosen final brand name on the project.
	 *
	 * @return array<string, mixed>|null
	 */
	public function selectBrandName(int $id, string $selectedName, ?int $userId): ?array
	{
		$project = BrandChat::where('user_id', $userId)->where('id', $id)->first();
		if (!$project) {
			return null;
		}

		$selectedName = trim($selectedName);
		if ($selectedName === '') {
			return null;
		}

		$exists = BrandNameSuggestion::where('brand_chat_id', $project->id)
			->where('name', $selectedName)
			->exists();
		if (!$exists) {
			return ['error' => 'suggestion_not_found'];
		}

		$project->selected_brand_name = $selectedName;
		$project->save();

		return $this->serializeProject($project, [], $userId);
	}

	/**
	 * @param  array{name?: string, archetype?: string, name_type?: string, linguistic_style?: string, generation_technique?: string, length?: string}  $filters
	 * @return array<string, mixed>
	 */
	public function serializeProject(BrandChat $chat, array $filters = [], ?int $userId = null): array
	{
		$answers = $this->formatAnswers($chat->answers);
		$favoritedSuggestionIds = BrandNameFavorite::where('user_id', $userId)
			->where('brand_chat_id', $chat->id)
			->pluck('brand_name_suggestion_id')
			->filter(fn ($id) => $id !== null)
			->map(fn ($id) => (int) $id)
			->all();
		$favoritedLookup = array_fill_keys($favoritedSuggestionIds, true);

		$allSuggestions = BrandNameSuggestion::where('brand_chat_id', $chat->id)
			->orderBy('suggestion_index')
			->get();

		$uniqueStrings = static function ($collection, string $key) {
			return $collection
				->pluck($key)
				->filter(fn ($value) => is_string($value) && trim($value) !== '')
				->unique()
				->values()
				->all();
		};

		$archetypes = $uniqueStrings($allSuggestions, 'archetype');
		$nameTypes = $uniqueStrings($allSuggestions, 'name_type');
		$styles = $uniqueStrings($allSuggestions, 'linguistic_style');
		$techniques = $uniqueStrings($allSuggestions, 'generation_technique');

		$suggestionsQuery = BrandNameSuggestion::where('brand_chat_id', $chat->id);
		if (!empty($filters['name'])) {
			$suggestionsQuery->where('name', 'like', '%' . $filters['name'] . '%');
		}
		if (!empty($filters['archetype'])) {
			$suggestionsQuery->where('archetype', 'like', '%' . $filters['archetype'] . '%');
		}
		if (!empty($filters['name_type'])) {
			$suggestionsQuery->where('name_type', 'like', '%' . $filters['name_type'] . '%');
		}
		if (!empty($filters['linguistic_style'])) {
			$suggestionsQuery->where('linguistic_style', 'like', '%' . $filters['linguistic_style'] . '%');
		}
		if (!empty($filters['generation_technique'])) {
			$suggestionsQuery->where('generation_technique', 'like', '%' . $filters['generation_technique'] . '%');
		}
		if (!empty($filters['length'])) {
			$length = strtolower((string) $filters['length']);
			if (str_contains($length, 'short')) {
				$suggestionsQuery->whereBetween('name_length', [3, 6]);
			} elseif (str_contains($length, 'medium')) {
				$suggestionsQuery->whereBetween('name_length', [7, 10]);
			} elseif (str_contains($length, 'long')) {
				$suggestionsQuery->whereBetween('name_length', [11, 40]);
			}
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
				'name_type' => $s->name_type,
				'linguistic_style' => $s->linguistic_style,
				'generation_technique' => $s->generation_technique,
				'name_length' => $s->name_length,
				'rationale' => $s->rationale,
				'description' => $s->description,
				'brand_keywords' => $s->brand_keywords ?? [],
				'why_fits' => $s->why_fits,
				'badge' => 'Evocative Brand Name',
				'domains' => $s->domains,
				'liked' => isset($favoritedLookup[(int) $s->id]),
			])
			->values();

		return [
			'id' => $chat->id,
			'project_id' => $chat->id,
			'chat_id' => $chat->id,
			'parent_id' => $chat->parent_id,
			'topic' => $chat->topic,
			'project_name' => $chat->project_name,
			'selected_brand_name' => $chat->selected_brand_name,
			'favorites_count' => count($favoritedLookup),
			'language' => $chat->language,
			'answers' => $answers,
			'archetype' => $archetypes,
			'name_types' => $nameTypes,
			'linguistic_styles' => $styles,
			'generation_techniques' => $techniques,
			'items' => $items,
			'raw_response' => $chat->raw_response,
			'created_at' => $chat->created_at,
			'device_token' => $chat->device_token ?? null,
		];
	}

	/**
	 * @param  mixed  $answers
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
			->map(fn ($id) => (int) $id)
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

			$questionId = isset($item['question_id']) ? (int) $item['question_id'] : null;
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
