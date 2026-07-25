<?php

namespace App\Services\Brand;

use App\Models\BrandChat;
use App\Models\BrandChatMessage;
use App\Models\BrandNameSuggestion;
use App\Services\AI\DeepSeekService;
use App\Services\AI\PromptTemplateService;
use App\Services\Concerns\DecodesAiJson;
use App\Services\Domain\DomainAvailabilityService;

class BrandNameService
{
	use DecodesAiJson;

	public function __construct(
		private readonly DeepSeekService $ai,
		private readonly DomainAvailabilityService $domains,
		private readonly PromptTemplateService $prompts,
	) {}

	/**
	 * @param  array{answers: array, language?: string, count?: int, tlds?: array, device_token?: string|null}  $data
	 * @return array{id: int, project_id: int, chat_id: int, response_message: string, items: array, payload: array, project_name?: string}
	 */
	public function generate(array $data, ?int $userId = null): array
	{
		$answers = $data['answers'] ?? [];
		$language = $data['language'] ?? 'en';
		$count = (int) ($data['count'] ?? 12);
		$requestTlds = (array) ($data['tlds'] ?? []);
		$deviceToken = $data['device_token'] ?? null;
		$tlds = $this->domains->resolveTlds($requestTlds);

		$messages = $this->prompts->buildBrandNamesGenerateMessages($answers, $count, $language);
		$raw = $this->ai->simpleChat($messages['user'], $messages['system']);
		$parsed = $this->decodeJsonLenient($raw);
		$list = (is_array($parsed) && isset($parsed['suggestions']) && is_array($parsed['suggestions']))
			? $parsed['suggestions'] : [];

		$projectName = trim((string) ($parsed['project_name'] ?? ''));
		if ($projectName === '') {
			$projectName = $this->deriveProjectNameFallback($answers);
		}

		$items = $this->mapSuggestionsToItems($list, $tlds);
		$chat = BrandChat::create([
			'topic' => 'brand_names',
			'project_name' => $projectName,
			'user_id' => $userId,
			'language' => $language,
			'answers' => $answers,
			'response' => null,
			'raw_response' => null,
			'device_token' => $deviceToken,
		]);

		$projectId = $chat->id;
		$items = $this->persistSuggestions($chat->id, $items);

		$responseMessage = 'I generated brand name suggestions for you.';
		$assistantPayload = [
			'response_message' => $responseMessage,
			'items' => $items,
			'project_name' => $projectName,
		];
		$this->storeChatMessage($chat->id, 'assistant', $responseMessage, $assistantPayload, $userId);

		return [
			'id' => $projectId,
			'project_id' => $projectId,
			'chat_id' => $projectId,
			'project_name' => $projectName,
			'response_message' => $responseMessage,
			'items' => $items,
			'payload' => $assistantPayload,
		];
	}

	/**
	 * Generate similar names for a selected suggestion (uses related-names prompt).
	 *
	 * @return array{id: int, project_id: int, chat_id: int, items: array, selected_name: string}|null
	 */
	public function generateSimilar(
		int $projectId,
		string $selectedName,
		array $tlds,
		int $count,
		?int $userId
	): ?array {
		$parent = BrandChat::where('id', $projectId)
			->where('user_id', $userId)
			->first();
		if (!$parent) {
			return null;
		}

		$selectedName = trim($selectedName);
		if ($selectedName === '') {
			return null;
		}

		$this->storeChatMessage(
			$parent->id,
			'user',
			'Generate similar names for: ' . $selectedName,
			['selected_name' => $selectedName],
			$userId
		);

		$answers = is_array($parent->answers) ? $parent->answers : [];
		$resolvedTlds = $this->domains->resolveTlds($tlds);
		$messages = $this->prompts->buildSimilarNamesMessages(
			$answers,
			$selectedName,
			max(1, $count),
			(string) ($parent->language ?? 'en')
		);

		$raw = $this->ai->simpleChat($messages['user'], $messages['system']);
		$parsed = $this->decodeJsonLenient($raw);
		$list = (is_array($parsed) && isset($parsed['suggestions']) && is_array($parsed['suggestions']))
			? $parsed['suggestions'] : [];

		$items = $this->mapSuggestionsToItems($list, $resolvedTlds, $projectId);
		BrandNameSuggestion::where('brand_chat_id', $parent->id)->delete();
		$items = $this->persistSuggestions($parent->id, $items);

		$this->storeChatMessage(
			$parent->id,
			'assistant',
			'Generated similar brand name suggestions.',
			['items' => $items, 'selected_name' => $selectedName],
			$userId
		);

		return [
			'id' => $projectId,
			'project_id' => $projectId,
			'chat_id' => $projectId,
			'selected_name' => $selectedName,
			'items' => $items,
		];
	}

	/**
	 * @return array{id: int, project_id: int, chat_id: int, items: array}|null
	 */
	public function edit(int $chatId, string $comment, array $tlds, ?int $userId): ?array
	{
		$parent = BrandChat::where('id', $chatId)
			->where('user_id', $userId)
			->first();
		if (!$parent) {
			return null;
		}

		if ($comment !== '') {
			$this->storeChatMessage($parent->id, 'user', $comment, null, $userId);
		}

		$currentItems = $parent->nameSuggestions()
			->orderBy('suggestion_index')
			->get()
			->map(fn (BrandNameSuggestion $s) => [
				'name' => $s->name,
				'archetype' => $s->archetype,
			])
			->values()
			->all();

		$system = 'You are a senior brand naming expert. You produce STRICT JSON outputs only.';
		$instructions = "Revise the following brand name suggestions based on the user comments.\n"
			. "Return STRICT JSON of the SAME shape:\n"
			. "{ \"suggestions\": [ { \"name\": \"...\", \"archetype\": \"...\", \"rationale\": \"...\", \"description\": \"...\", \"name_type\": \"...\", \"linguistic_style\": \"...\", \"generation_technique\": \"...\", \"brand_keywords\": [], \"why_fits\": \"...\" } ] }\n";

		$prompt = $instructions
			. "Current suggestions JSON:\n"
			. json_encode(['suggestions' => $currentItems], JSON_UNESCAPED_UNICODE)
			. "\n\nUser comments:\n"
			. $comment;

		$raw = $this->ai->simpleChat($prompt, $system);
		$parsed = $this->decodeJsonLenient($raw);
		$list = (is_array($parsed) && isset($parsed['suggestions']) && is_array($parsed['suggestions']))
			? $parsed['suggestions'] : [];

		$resolvedTlds = $this->domains->resolveTlds($tlds);
		$items = $this->mapSuggestionsToItems($list, $resolvedTlds, $chatId);

		BrandNameSuggestion::where('brand_chat_id', $parent->id)->delete();
		$items = $this->persistSuggestions($parent->id, $items);

		$this->storeChatMessage(
			$parent->id,
			'assistant',
			'Brand name suggestions updated.',
			['items' => $items],
			$userId
		);

		return [
			'id' => $chatId,
			'project_id' => $chatId,
			'chat_id' => $chatId,
			'items' => $items,
		];
	}

	/**
	 * @param  array<int, array<string, mixed>>  $list
	 * @param  array<int, string>  $tlds
	 * @return array<int, array<string, mixed>>
	 */
	private function mapSuggestionsToItems(array $list, array $tlds, ?int $projectId = null): array
	{
		$requireAvailable = (bool) config('domains.require_available_domain', true);
		$names = [];
		foreach ($list as $s) {
			$name = trim((string) ($s['name'] ?? ''));
			if ($name !== '') {
				$names[] = $name;
			}
		}

		$domainByName = $this->domains->checkPrimaryMany($names, $tlds);
		$items = $this->buildItemsFromSuggestions($list, $domainByName, $projectId, $requireAvailable);

		// If strict availability filtering removed everything, fall back to ranked unfiltered list.
		if ($requireAvailable && $items === [] && $names !== []) {
			$items = $this->buildItemsFromSuggestions($list, $domainByName, $projectId, false);
		}

		usort($items, static fn ($a, $b) => ($b['availability_score'] ?? 0) <=> ($a['availability_score'] ?? 0));
		foreach ($items as $i => $row) {
			$items[$i]['suggestion_index'] = $i + 1;
		}

		return $items;
	}

	/**
	 * @param  array<int, array<string, mixed>>  $list
	 * @param  array<string, array<int, array<string, mixed>>>  $domainByName
	 * @return array<int, array<string, mixed>>
	 */
	private function buildItemsFromSuggestions(
		array $list,
		array $domainByName,
		?int $projectId,
		bool $requireAvailable
	): array {
		$items = [];
		$idx = 1;

		foreach ($list as $s) {
			$name = trim((string) ($s['name'] ?? ''));
			if ($name === '') {
				continue;
			}

			$domainResults = $domainByName[$name] ?? [];
			if ($requireAvailable && !$this->domains->hasAnyAvailable($domainResults)) {
				continue;
			}

			$availableOnly = array_values(array_filter(
				$domainResults,
				static fn ($row) => !empty($row['available'])
			));
			$displayList = array_slice(
				$availableOnly !== [] ? $availableOnly : $domainResults,
				0,
				5
			);
			$label = preg_replace('/[^a-z0-9]+/u', '', mb_strtolower($name, 'UTF-8')) ?: 'brand';
			$primary = $displayList[0] ?? [
				'domain' => $label . '.com',
				'available' => false,
				'tld' => '.com',
				'buy_url' => $this->domains->namecheapSearchUrl($label . '.com', $label),
			];

			$nameLength = mb_strlen(preg_replace('/[\s\-_]+/u', '', $name) ?? $name);
			$item = [
				'suggestion_index' => $idx,
				'id' => $idx,
				'name' => $name,
				'archetype' => (string) ($s['archetype'] ?? ''),
				'name_type' => (string) ($s['name_type'] ?? ''),
				'linguistic_style' => (string) ($s['linguistic_style'] ?? ''),
				'generation_technique' => (string) ($s['generation_technique'] ?? ''),
				'name_length' => $nameLength,
				'rationale' => (string) ($s['rationale'] ?? ''),
				'description' => (string) ($s['description'] ?? ($s['rationale'] ?? '')),
				'brand_keywords' => array_values(array_filter((array) ($s['brand_keywords'] ?? []))),
				'why_fits' => (string) ($s['why_fits'] ?? ''),
				'badge' => 'Evocative Brand Name',
				'availability_score' => $this->domains->availabilityScore($domainResults),
				'domains' => [
					'primary' => [
						'tld' => $primary['tld'] ?? ('.' . substr(strrchr($primary['domain'], '.') ?: '.com', 1)),
						'available' => (bool) ($primary['available'] ?? false),
						'domain' => $primary['domain'],
						'buy_url' => $primary['buy_url'] ?? $this->domains->namecheapSearchUrl($primary['domain'], $name),
					],
					'list' => array_map(function (array $row) use ($name) {
						return [
							'domain' => $row['domain'],
							'available' => (bool) ($row['available'] ?? false),
							'tld' => $row['tld'] ?? ('.' . substr(strrchr($row['domain'], '.') ?: '', 1)),
							'buy_url' => $row['buy_url'] ?? $this->domains->namecheapSearchUrl($row['domain'], $name),
						];
					}, $displayList),
					'more_count' => max(0, count($availableOnly) - count($displayList)),
					'all' => array_map(function (array $row) use ($name) {
						return [
							'domain' => $row['domain'],
							'available' => (bool) ($row['available'] ?? false),
							'tld' => $row['tld'] ?? ('.' . substr(strrchr($row['domain'], '.') ?: '', 1)),
							'buy_url' => $row['buy_url'] ?? $this->domains->namecheapSearchUrl($row['domain'], $name),
						];
					}, $domainResults),
				],
				'liked' => false,
			];
			if ($projectId !== null) {
				$item['project_id'] = $projectId;
			}
			$items[] = $item;
			$idx++;
		}

		return $items;
	}

	/**
	 * @param  array<int, array<string, mixed>>  $items
	 * @return array<int, array<string, mixed>>
	 */
	private function persistSuggestions(int $projectId, array $items): array
	{
		foreach ($items as $k => $row) {
			$items[$k]['project_id'] = $projectId;
			$model = BrandNameSuggestion::create([
				'brand_chat_id' => $projectId,
				'suggestion_index' => (int) ($row['suggestion_index'] ?? ($k + 1)),
				'name' => (string) $row['name'],
				'archetype' => $row['archetype'] ?? null,
				'name_type' => $row['name_type'] ?? null,
				'linguistic_style' => $row['linguistic_style'] ?? null,
				'generation_technique' => $row['generation_technique'] ?? null,
				'name_length' => $row['name_length'] ?? null,
				'rationale' => $row['rationale'] ?? null,
				'description' => $row['description'] ?? null,
				'brand_keywords' => $row['brand_keywords'] ?? null,
				'why_fits' => $row['why_fits'] ?? null,
				'domains' => $row['domains'] ?? null,
				'liked' => (bool) ($row['liked'] ?? false),
			]);
			$items[$k]['id'] = $model->id;
			$items[$k]['badge'] = 'Evocative Brand Name';
		}

		return $items;
	}

	/**
	 * @param  array<int, array{question_id?: mixed, value?: mixed}>  $answers
	 */
	private function deriveProjectNameFallback(array $answers): string
	{
		$context = $this->prompts->mapAnswerContext($answers);
		$desc = trim((string) ($context['business_description'] ?? ''));
		if ($desc === '' || $desc === '-') {
			return 'Untitled Project';
		}
		$words = preg_split('/\s+/u', $desc) ?: [];
		$slice = array_slice($words, 0, 5);

		return trim(implode(' ', $slice)) ?: 'Untitled Project';
	}

	private function storeChatMessage(
		int $brandChatId,
		string $role,
		?string $message = null,
		?array $payload = null,
		?int $userId = null
	): void {
		BrandChatMessage::create([
			'brand_chat_id' => $brandChatId,
			'user_id' => $userId,
			'role' => $role,
			'message' => $message,
			'payload' => $payload,
		]);
	}
}
