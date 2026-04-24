<?php

namespace App\Http\Controllers\Mobile\v1\Home;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\CreateBrandTextRequest;
use App\Http\Requests\Mobile\EditBrandTextRequest;
use App\Models\Question;
use App\Services\AI\DeepSeekService;
use Illuminate\Http\JsonResponse;
use App\Models\BrandChat;
use App\Http\Requests\Mobile\CheckDomainRequest;
use App\Services\Domain\DomainAvailabilityService;
use App\Http\Requests\Mobile\ReserveDomainRequest;
use App\Services\Domain\NamecheapService;
use App\Models\DomainReservation;
use Illuminate\Support\Str;

class BrandTextController extends Controller {


	public function generate(CreateBrandTextRequest $request, DeepSeekService $ai): JsonResponse
	{
		try {
			return $this->doGenerate($request, $ai);
		} catch (\Throwable $e) {
			return $this->response->statusFail(
				['message' => 'Failed to generate brand text.', 'error' => $e->getMessage()],
				500
			);
		}
	}

	private function doGenerate(CreateBrandTextRequest $request, DeepSeekService $ai): JsonResponse
	{
		$answers = $request->input('answers', []);
		$language = $request->input('language', 'both'); // en, ar, both

		$questionIds = collect($answers)->pluck('question_id')->all();
		$questions = Question::whereIn('id', $questionIds)->get()->keyBy('id');

		$lines = [];
		foreach ($answers as $item) {
			$q = $questions->get((int)$item['question_id']);
			if (!$q) {
				continue;
			}
			$value = $item['value'];
			if (is_array($value)) {
				$value = implode(', ', $value);
			}
			$lines[] = "- Q: {$q->question_en} | {$q->question_ar}\n  A: {$value}";
		}

		$qaBlock = implode("\n", $lines);

		$system = 'You are a senior brand strategist. You produce structured JSON outputs.';

		$instructions = "Based on the following Q&A, generate brand strategy suggestions as STRICT JSON only.\n"
			. "Do NOT include markdown. Do NOT include extra commentary. Return ONLY valid JSON.\n\n"
			. "JSON structure to return:\n"
			. "{\n"
			. "  \"brand_text\": " . ($language === 'both' ? "{ \"en\": { \"taglines\": [\"...\",\"...\",\"...\"], \"mission\": \"...\", \"description\": \"...\" }, \"ar\": { \"taglines\": [\"...\",\"...\",\"...\"], \"mission\": \"...\", \"description\": \"...\" } }" : "{ \"taglines\": [\"...\",\"...\",\"...\"], \"mission\": \"...\", \"description\": \"...\" }") . ",\n"
			. "  \"colors\": [\n"
			. "    { \"name\": " . ($language === 'both' ? "{ \"en\": \"Primary\", \"ar\": \"الأساسي\" }" : "\"Primary\"") . ", \"hex\": \"#112233\", \"usage\": " . ($language === 'both' ? "{ \"en\": \"Buttons and highlights\", \"ar\": \"الأزرار والإبراز\" }" : "\"Buttons and highlights\"") . " },\n"
			. "    { \"name\": " . ($language === 'both' ? "{ \"en\": \"Secondary\", \"ar\": \"الثانوي\" }" : "\"Secondary\"") . ", \"hex\": \"#445566\", \"usage\": " . ($language === 'both' ? "{ \"en\": \"Headers and accents\", \"ar\": \"العناوين والزخارف\" }" : "\"Headers and accents\"") . " }\n"
			. "  ],\n"
			. "  \"design_details\": " . ($language === 'both' ? "{ \"en\": { \"typography\": [{ \"family\": \"Inter\", \"weights\": [\"400\",\"700\"], \"usage\": \"Headings and body\" }], \"imagery\": \"Clean, modern real-estate visuals\", \"layout\": \"Ample white space, card-based listings\" }, \"ar\": { \"typography\": [{ \"family\": \"Cairo\", \"weights\": [\"400\",\"700\"], \"usage\": \"العناوين والنص\" }], \"imagery\": \"صور عقارية حديثة وواضحة\", \"layout\": \"مساحات بيضاء وفيرة وتصميم قائم على البطاقات\" } }" : "{ \"typography\": [{ \"family\": \"Inter\", \"weights\": [\"400\",\"700\"], \"usage\": \"Headings and body\" }], \"imagery\": \"Clean, modern real-estate visuals\", \"layout\": \"Ample white space, card-based listings\" }") . "\n"
			. "}\n\n"
			. "Content rules:\n"
			. "- brand_text.taglines: 3 concise options (max 8 words each)\n"
			. "- brand_text.mission: <= 40 words; description: <= 60 words\n"
			. "- colors: up to 5 items total; valid HEX codes; include usage\n"
			. "- design_details: practical guidance for typography, imagery, layout\n";

		if ($language === 'ar') {
			$instructions .= "Language: Output all textual content in Modern Standard Arabic only.\n\n";
		} else if ($language === 'en') {
			$instructions .= "Language: Output all textual content in English only.\n\n";
		} else {
			$instructions .= "Language: Provide both English and Arabic sections as specified.\n\n";
		}

		$prompt = $instructions . "Q&A:\n" . $qaBlock;

		$suggestions = $ai->simpleChat($prompt, $system);

		$parsed = $this->decodeJsonLenient($suggestions);
		$normalized = $this->normalizeBrandTextResponse($parsed, $language);
		$ok = is_array($normalized) && isset($normalized['brand_text']);

		BrandChat::create([
			'topic' => 'brand_text',
			'user_id' => auth('api')->id(),
			'language' => $language,
			'answers' => $answers,
			'response' => $ok ? $normalized : null,
			'raw_response' => $ok ? null : $suggestions,
		]);

		if ($ok) return $this->response->statusOk(['data' => $normalized]);

		return $this->response->statusOk([
			'data' => [
				'brand_text' => null,
				'colors' => [],
				'design_details' => [],
				'raw' => $suggestions
			]
		]);
	}

	public function history(): JsonResponse
	{
		$keyword = trim((string)request()->query('q', ''));

		$query = BrandChat::where('user_id', auth('api')->id())
			->where('topic', 'brand_text')
			->latest('id');

		// If no keyword, return recent as before
		if ($keyword === '') {
			$items = $query
				->limit(50)
				->get(['id','language','answers','response','raw_response','created_at']);

			return $this->response->statusOk([
				'data' => $items
			]);
		}

		// With keyword: fetch a larger window, filter in PHP across answers, questions, and taglines
		$candidates = $query
			->limit(200)
			->get(['id','language','answers','response','raw_response','created_at']);

		// Collect all question IDs referenced
		$allAnswerEntries = collect($candidates)->pluck('answers')->filter()->flatten(1);
		$questionIds = $allAnswerEntries->pluck('question_id')->filter()->unique()->values()->all();
		$questionsById = empty($questionIds)
			? collect()
			: Question::whereIn('id', $questionIds)->get()->keyBy('id');

		$needle = Str::lower($keyword);
		$filtered = $candidates->filter(function ($chat) use ($questionsById, $needle) {
			$parts = [];

			// Answers values + corresponding question text (en/ar) if available
			$answers = is_array($chat->answers) ? $chat->answers : [];
			foreach ($answers as $a) {
				if (isset($a['value'])) {
					if (is_array($a['value'])) {
						$parts[] = implode(' ', array_map('strval', $a['value']));
					} else {
						$parts[] = (string)$a['value'];
					}
				}
				$qid = isset($a['question_id']) ? (int)$a['question_id'] : null;
				if ($qid && $questionsById->has($qid)) {
					$q = $questionsById->get($qid);
					$parts[] = (string)($q->question_en ?? '');
					$parts[] = (string)($q->question_ar ?? '');
				}
			}

			// Response brand_text taglines (en/ar)
			$response = is_array($chat->response) ? $chat->response : null;
			if (is_array($response) && isset($response['brand_text'])) {
				$bt = $response['brand_text'];
				// both-language shape
				if (isset($bt['en']) && is_array($bt['en']) && isset($bt['en']['taglines']) && is_array($bt['en']['taglines'])) {
					$parts = array_merge($parts, array_map('strval', $bt['en']['taglines']));
				}
				if (isset($bt['ar']) && is_array($bt['ar']) && isset($bt['ar']['taglines']) && is_array($bt['ar']['taglines'])) {
					$parts = array_merge($parts, array_map('strval', $bt['ar']['taglines']));
				}
				// single-language shape
				if (isset($bt['taglines']) && is_array($bt['taglines'])) {
					$parts = array_merge($parts, array_map('strval', $bt['taglines']));
				}
			}

			$haystack = Str::lower(implode(' ', $parts));
			return $haystack !== '' && Str::contains($haystack, $needle);
		})->values()->take(50);

		return $this->response->statusOk([
			'data' => $filtered
		]);
	}

	public function edit(EditBrandTextRequest $request, DeepSeekService $ai): JsonResponse
	{
		$chatId = (int)$request->input('chat_id');
		$comment = (string)$request->input('comment');
		$language = $request->input('language');

		$parent = BrandChat::where('id', $chatId)
			->where('user_id', auth('api')->id())
			->first();
		if (!$parent) {
			return $this->response->statusFail('Chat not found', 404);
		}

		$current = $parent->response ?: json_decode((string)$parent->raw_response, true);
		if (!is_array($current)) {
			$current = null;
		}

		$effectiveLanguage = $language ?: $parent->language;

		$system = 'You are a senior brand strategist. You produce structured JSON outputs.';

		$instructions = "Revise the following brand strategy JSON according to the user's comments.\n"
			. "Preserve the same JSON structure and constraints as before. Return STRICT JSON only.\n";

		if ($effectiveLanguage === 'ar') {
			$instructions .= "Language: Output all textual content in Modern Standard Arabic only.\n\n";
		} else if ($effectiveLanguage === 'en') {
			$instructions .= "Language: Output all textual content in English only.\n\n";
		} else {
			$instructions .= "Language: Provide both English and Arabic sections as applicable.\n\n";
		}

		$prompt = $instructions
			. "Current JSON:\n"
			. json_encode($current ?: ['brand_text' => null, 'colors' => [], 'design_details' => []], JSON_UNESCAPED_UNICODE)
			. "\n\nUser comments (edits to apply):\n"
			. $comment;

		$suggestions = $ai->simpleChat($prompt, $system);
		$parsed = $this->decodeJsonLenient($suggestions);
		$normalized = $this->normalizeBrandTextResponse($parsed, $effectiveLanguage);
		$ok = is_array($normalized) && isset($normalized['brand_text']);

		BrandChat::create([
			'parent_id' => $parent->id,
			'topic' => 'brand_text',
			'user_id' => auth('api')->id(),
			'language' => $effectiveLanguage,
			'answers' => $parent->answers,
			'response' => $ok ? $normalized : null,
			'raw_response' => $ok ? null : $suggestions,
		]);

		if ($ok) return $this->response->statusOk(['data' => $normalized]);

		return $this->response->statusOk([
			'data' => [
				'brand_text' => null,
				'colors' => [],
				'design_details' => [],
				'raw' => $suggestions
			]
		]);
	}

	/**
	 * Attempts to decode JSON that may be wrapped in Markdown code fences or contain
	 * surrounding text. Falls back to extracting the first balanced JSON object.
	 */
	private function decodeJsonLenient(string $text): ?array
	{
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

	/**
	 * Normalize various plausible shapes to the expected one containing 'brand_text'.
	 * Currently supports mapping { items: [{tagline: "..."}] } into brand_text.taglines.
	 */
	private function normalizeBrandTextResponse(?array $parsed, string $language): ?array
	{
		if (!is_array($parsed)) {
			return null;
		}
		if (isset($parsed['brand_text'])) {
			return $parsed;
		}

		// Handle simple { items: [{ tagline: "..."}, ...] } shape
		if (isset($parsed['items']) && is_array($parsed['items'])) {
			$taglines = [];
			foreach ($parsed['items'] as $it) {
				if (is_array($it) && isset($it['tagline']) && is_string($it['tagline'])) {
					$taglines[] = $it['tagline'];
				}
			}
			if (!empty($taglines)) {
				if ($language === 'both') {
					return ['brand_text' => ['en' => ['taglines' => $taglines]]];
				}
				return ['brand_text' => ['taglines' => $taglines]];
			}
		}

		return $parsed;
	}

	public function checkDomains(CheckDomainRequest $request, DomainAvailabilityService $domains): JsonResponse
	{
		$name = (string)$request->input('name');
		$tlds = (array)$request->input('tlds', []);
		$results = $domains->check($name, $tlds);
		return $this->response->statusOk([
			'data' => [
				'results' => $results
			]
		]);
	}

	public function reserveDomain(ReserveDomainRequest $request, NamecheapService $namecheap): JsonResponse
	{
		$domain = (string)$request->input('domain');
		$years = (int)$request->input('years', 1);
		$whoisGuard = (bool)$request->input('whois_guard', false);
		$registrant = (array)$request->input('registrant');

		$reservation = DomainReservation::create([
			'user_id' => auth('api')->id(),
			'domain' => $domain,
			'years' => $years,
			'registrant' => $registrant,
			'provider' => 'namecheap',
			'status' => 'pending',
		]);

		$result = $namecheap->register($domain, $registrant, $years, $whoisGuard);
		if (($result['ok'] ?? false) === true) {
			$orderId = null;
			if (isset($result['xml']->CommandResponse->DomainCreateResult)) {
				$orderId = (string)$result['xml']->CommandResponse->DomainCreateResult['OrderID'] ?? null;
			}
			$reservation->update([
				'status' => 'success',
				'provider_order_id' => $orderId,
				'response' => json_decode(json_encode($result['xml']), true),
			]);
			return $this->response->statusOk([
				'data' => [
					'reservation_id' => $reservation->id,
					'status' => $reservation->status,
					'provider_order_id' => $reservation->provider_order_id,
				],
				'message' => 'Domain reserved successfully'
			]);
		}

		$reservation->update([
			'status' => 'failed',
			'error' => (string)($result['error'] ?? 'Unknown error'),
			'response' => isset($result['xml']) ? json_decode(json_encode($result['xml']), true) : null,
		]);
		return $this->response->statusFail([
			'reservation_id' => $reservation->id,
			'status' => $reservation->status,
			'error' => $reservation->error,
		], 400);
	}
}


