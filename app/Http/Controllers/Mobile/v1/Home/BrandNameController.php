<?php

namespace App\Http\Controllers\Mobile\v1\Home;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\CreateBrandNamesRequest;
use App\Models\Question;
use App\Services\AI\DeepSeekService;
use App\Services\Domain\DomainAvailabilityService;
use Illuminate\Http\JsonResponse;
use App\Models\BrandChat;

class BrandNameController extends Controller
{
	public function generate(CreateBrandNamesRequest $request, DeepSeekService $ai, DomainAvailabilityService $domains): JsonResponse
	{
		try {
			return $this->doGenerate($request, $ai, $domains);
		} catch (\Throwable $e) {
			return $this->response->statusFail(
				['message' => 'Failed to generate brand names.', 'error' => $e->getMessage()],
				500
			);
		}
	}

	private function doGenerate(CreateBrandNamesRequest $request, DeepSeekService $ai, DomainAvailabilityService $domains): JsonResponse
	{
		$answers = $request->input('answers', []);
		$language = $request->input('language', 'en');
		$count = (int)($request->input('count', 12));
		$tlds = (array)$request->input('tlds', ['com','io','ai']);

		$questionIds = collect($answers)->pluck('question_id')->all();
		$questions = Question::whereIn('id', $questionIds)->get()->keyBy('id');

		$lines = [];
		foreach ($answers as $item) {
			$q = $questions->get((int)$item['question_id']);
			if (!$q) continue;
			$value = $item['value'];
			if (is_array($value)) $value = implode(', ', $value);
			$lines[] = "- Q: {$q->question_en} | {$q->question_ar}\n  A: {$value}";
		}

		$qaBlock = implode("\n", $lines);

		$system = 'You are a senior brand naming expert. You produce STRICT JSON outputs only.';

		$instructions = "Based on the Q&A below, propose {$count} modern, short, pronounceable brand NAME options.\n"
			. "Return STRICT JSON only with this shape:\n"
			. "{ \"suggestions\": [ { \"name\": \"...\", \"archetype\": \"The Hero|The Sage|...\", \"rationale\": \"<= 14 words\" } ] }\n"
			. "Constraints:\n"
			. "- Names must be 1 word when possible; 4-10 letters; no spaces; avoid hyphens and numbers.\n"
			. "- Ensure originality vibe; avoid generic terms.\n"
			. "- Archetype is the closest Jungian archetype label (e.g., The Hero, The Sage, The Explorer).\n";

		if ($language === 'ar') {
			$instructions .= "Language: Provide names that work globally; if Arabic words are used, keep them simple.\n";
		}


		$prompt = $instructions . "\nQ&A:\n" . $qaBlock;
		$raw = $ai->simpleChat($prompt, $system);
		$parsed = $this->decodeJsonLenient($raw);
		$list = (is_array($parsed) && isset($parsed['suggestions']) && is_array($parsed['suggestions']))
			? $parsed['suggestions'] : [];

		$items = [];
		$idx = 1;
		foreach ($list as $s) {
			$name = trim((string)($s['name'] ?? ''));
			if ($name === '') continue;

			$domainResults = $domains->check($name, $tlds);
			$primary = collect($domainResults)->firstWhere('domain', strtolower($name) . '.com')
				?: (count($domainResults) ? $domainResults[0] : ['domain' => strtolower($name) . '.com', 'available' => null]);

			$items[] = [
				'suggestion_index' => $idx,
				'id' => $idx,
				'name' => $name,
				'archetype' => (string)($s['archetype'] ?? ''),
				'domains' => [
					'primary' => [
						'tld' => '.' . substr(strrchr($primary['domain'], '.'), 1),
						'available' => (bool)($primary['available'] ?? false),
						'domain' => $primary['domain'],
					],
					'list' => array_slice($domainResults, 0, 3),
					'more_count' => max(0, count($domainResults) - 3),
				],
				'liked' => false,
			];
			$idx++;
		}

		// Persist as project root (BrandChat id = project id for GET projects/{id})
		$chat = BrandChat::create([
			'topic' => 'brand_names',
			'user_id' => auth()->id(),
			'language' => $language,
			'answers' => $answers,
			'response' => ['items' => $items],
			'raw_response' => null,
			'device_token' => request()->get('device_token'),
		]);

		$projectId = $chat->id;
		foreach ($items as $k => $row) {
			$items[$k]['project_id'] = $projectId;
		}
		$chat->update(['response' => ['items' => $items]]);

		return $this->response->statusOk([
			'data' => [
				'id' => $projectId,
				'project_id' => $projectId,
				'chat_id' => $projectId,
				'items' => $items,
			],
		]);
	}

	/**
	 * Attempts to decode JSON that may be wrapped in Markdown code fences or contain
	 * surrounding text. Falls back to extracting the first balanced JSON object.
	 */
	private function decodeJsonLenient(string $text): ?array
	{
		// Straight decode first
		$direct = json_decode($text, true);
		if (json_last_error() === JSON_ERROR_NONE && is_array($direct)) {
			return $direct;
		}

		$trimmed = trim($text);

		// Match fenced code block ```json ... ```
		if (preg_match('/```(?:json)?\\s*([\\s\\S]*?)\\s*```/i', $trimmed, $m)) {
			$block = trim($m[1]);
			$fromFence = json_decode($block, true);
			if (json_last_error() === JSON_ERROR_NONE && is_array($fromFence)) {
				return $fromFence;
			}
		}

		// Remove fence markers and retry
		$withoutFences = preg_replace('/```[a-z]*\\s*|```/i', '', $trimmed);
		if (is_string($withoutFences)) {
			$retry = json_decode(trim($withoutFences), true);
			if (json_last_error() === JSON_ERROR_NONE && is_array($retry)) {
				return $retry;
			}
		}

		// Extract first balanced {...} object (recursive regex)
		if (preg_match('/\\{(?:[^{}]|(?R))*\\}/s', $trimmed, $m2)) {
			$object = $m2[0];
			$fromObject = json_decode($object, true);
			if (json_last_error() === JSON_ERROR_NONE && is_array($fromObject)) {
				return $fromObject;
			}
		}

		return null;
	}

	public function edit(\Illuminate\Http\Request $request, DeepSeekService $ai, DomainAvailabilityService $domains): JsonResponse
	{
		$chatId = (int)$request->input('chat_id');
		$comment = (string)$request->input('comment', '');
		$tlds = (array)$request->input('tlds', ['com','io','ai']);
		$parent = BrandChat::where('id', $chatId)
			->where('user_id', auth()->id())
			->first();
		if (!$parent) {
			return $this->response->statusFail('Chat not found', 404);
		}

		$current = $parent->response ?: json_decode((string)$parent->raw_response, true);
		if (!is_array($current)) {
			$current = ['items' => []];
		}

		$system = 'You are a senior brand naming expert. You produce STRICT JSON outputs only.';
		$instructions = "Revise the following brand name suggestions based on the user comments.\n"
			. "Return STRICT JSON of the SAME shape:\n"
			. "{ \"suggestions\": [ { \"name\": \"...\", \"archetype\": \"...\", \"rationale\": \"<= 14 words\" } ] }\n";

		$prompt = $instructions
			. "Current suggestions JSON:\n"
			. json_encode(['suggestions' => array_map(fn($i) => ['name' => $i['name'], 'archetype' => $i['archetype'] ?? null], $current['items'] ?? [])], JSON_UNESCAPED_UNICODE)
			. "\n\nUser comments:\n"
			. $comment;

		$raw = $ai->simpleChat($prompt, $system);
		$parsed = $this->decodeJsonLenient($raw);
		$list = (is_array($parsed) && isset($parsed['suggestions']) && is_array($parsed['suggestions']))
			? $parsed['suggestions'] : [];

		$items = [];
		$idx = 1;
		foreach ($list as $s) {
			$name = trim((string)($s['name'] ?? ''));
			if ($name === '') continue;
			$domainResults = $domains->check($name, $tlds);
			$primary = collect($domainResults)->firstWhere('domain', strtolower($name) . '.com')
				?: (count($domainResults) ? $domainResults[0] : ['domain' => strtolower($name) . '.com', 'available' => null]);

			$items[] = [
				'suggestion_index' => $idx,
				'id' => $idx,
				'project_id' => $chatId,
				'name' => $name,
				'archetype' => (string)($s['archetype'] ?? ''),
				'domains' => [
					'primary' => [
						'tld' => '.' . substr(strrchr($primary['domain'], '.'), 1),
						'available' => (bool)($primary['available'] ?? false),
						'domain' => $primary['domain'],
					],
					'list' => array_slice($domainResults, 0, 3),
					'more_count' => max(0, count($domainResults) - 3),
				],
				'liked' => false,
			];
			$idx++;
		}

		$response = is_array($parent->response) ? $parent->response : [];
		$response['items'] = $items;
		$parent->update(['response' => $response]);

		return $this->response->statusOk([
			'data' => [
				'id' => $chatId,
				'project_id' => $chatId,
				'chat_id' => $chatId,
				'items' => $items,
			],
		]);
	}
}


