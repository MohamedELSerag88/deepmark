<?php

namespace App\Services\Brand;

use App\Models\BrandChat;
use App\Models\DomainReservation;
use App\Models\Question;
use App\Services\AI\DeepSeekService;
use App\Services\Concerns\DecodesAiJson;
use App\Services\Domain\DomainAvailabilityService;
use App\Services\Domain\NamecheapService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BrandTextService
{
	use DecodesAiJson;

	public function __construct(
		private readonly DeepSeekService $ai,
		private readonly DomainAvailabilityService $domains,
		private readonly NamecheapService $namecheap,
	) {}

	/**
	 * @param  array{answers: array, language?: string}  $data
	 * @return array{ok: bool, data: array}
	 */
	public function generate(array $data, ?int $userId): array
	{
		$answers = $data['answers'] ?? [];
		$language = $data['language'] ?? 'both';

		$questionIds = collect($answers)->pluck('question_id')->all();
		$questions = Question::whereIn('id', $questionIds)->get()->keyBy('id');

		$lines = [];
		foreach ($answers as $item) {
			$q = $questions->get((int) $item['question_id']);
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
		} elseif ($language === 'en') {
			$instructions .= "Language: Output all textual content in English only.\n\n";
		} else {
			$instructions .= "Language: Provide both English and Arabic sections as specified.\n\n";
		}

		$prompt = $instructions . "Q&A:\n" . $qaBlock;

		$suggestions = $this->ai->simpleChat($prompt, $system);

		$parsed = $this->decodeJsonLenient($suggestions);
		$normalized = $this->normalizeBrandTextResponse($parsed, $language);
		$ok = is_array($normalized) && isset($normalized['brand_text']);

		BrandChat::create([
			'topic' => 'brand_text',
			'user_id' => $userId,
			'language' => $language,
			'answers' => $answers,
			'response' => $ok ? $normalized : null,
			'raw_response' => $ok ? null : $suggestions,
		]);

		if ($ok) {
			return ['ok' => true, 'data' => $normalized];
		}

		return [
			'ok' => false,
			'data' => [
				'brand_text' => null,
				'colors' => [],
				'design_details' => [],
				'raw' => $suggestions,
			],
		];
	}

	public function history(?int $userId, string $keyword = ''): Collection
	{
		$keyword = trim($keyword);

		$query = BrandChat::where('user_id', $userId)
			->where('topic', 'brand_text')
			->latest('id');

		if ($keyword === '') {
			return $query
				->limit(50)
				->get(['id', 'language', 'answers', 'response', 'raw_response', 'created_at']);
		}

		$candidates = $query
			->limit(200)
			->get(['id', 'language', 'answers', 'response', 'raw_response', 'created_at']);

		$allAnswerEntries = collect($candidates)->pluck('answers')->filter()->flatten(1);
		$questionIds = $allAnswerEntries->pluck('question_id')->filter()->unique()->values()->all();
		$questionsById = empty($questionIds)
			? collect()
			: Question::whereIn('id', $questionIds)->get()->keyBy('id');

		$needle = Str::lower($keyword);

		return $candidates->filter(function ($chat) use ($questionsById, $needle) {
			$parts = [];

			$answers = is_array($chat->answers) ? $chat->answers : [];
			foreach ($answers as $a) {
				if (isset($a['value'])) {
					if (is_array($a['value'])) {
						$parts[] = implode(' ', array_map('strval', $a['value']));
					} else {
						$parts[] = (string) $a['value'];
					}
				}
				$qid = isset($a['question_id']) ? (int) $a['question_id'] : null;
				if ($qid && $questionsById->has($qid)) {
					$q = $questionsById->get($qid);
					$parts[] = (string) ($q->question_en ?? '');
					$parts[] = (string) ($q->question_ar ?? '');
				}
			}

			$response = is_array($chat->response) ? $chat->response : null;
			if (is_array($response) && isset($response['brand_text'])) {
				$bt = $response['brand_text'];
				if (isset($bt['en']) && is_array($bt['en']) && isset($bt['en']['taglines']) && is_array($bt['en']['taglines'])) {
					$parts = array_merge($parts, array_map('strval', $bt['en']['taglines']));
				}
				if (isset($bt['ar']) && is_array($bt['ar']) && isset($bt['ar']['taglines']) && is_array($bt['ar']['taglines'])) {
					$parts = array_merge($parts, array_map('strval', $bt['ar']['taglines']));
				}
				if (isset($bt['taglines']) && is_array($bt['taglines'])) {
					$parts = array_merge($parts, array_map('strval', $bt['taglines']));
				}
			}

			$haystack = Str::lower(implode(' ', $parts));

			return $haystack !== '' && Str::contains($haystack, $needle);
		})->values()->take(50);
	}

	/**
	 * @return array{ok: bool, data: array}|null  null when chat not found
	 */
	public function edit(int $chatId, string $comment, ?string $language, ?int $userId): ?array
	{
		$parent = BrandChat::where('id', $chatId)
			->where('user_id', $userId)
			->first();
		if (!$parent) {
			return null;
		}

		$current = $parent->response ?: json_decode((string) $parent->raw_response, true);
		if (!is_array($current)) {
			$current = null;
		}

		$effectiveLanguage = $language ?: $parent->language;

		$system = 'You are a senior brand strategist. You produce structured JSON outputs.';

		$instructions = "Revise the following brand strategy JSON according to the user's comments.\n"
			. "Preserve the same JSON structure and constraints as before. Return STRICT JSON only.\n";

		if ($effectiveLanguage === 'ar') {
			$instructions .= "Language: Output all textual content in Modern Standard Arabic only.\n\n";
		} elseif ($effectiveLanguage === 'en') {
			$instructions .= "Language: Output all textual content in English only.\n\n";
		} else {
			$instructions .= "Language: Provide both English and Arabic sections as applicable.\n\n";
		}

		$prompt = $instructions
			. "Current JSON:\n"
			. json_encode($current ?: ['brand_text' => null, 'colors' => [], 'design_details' => []], JSON_UNESCAPED_UNICODE)
			. "\n\nUser comments (edits to apply):\n"
			. $comment;

		$suggestions = $this->ai->simpleChat($prompt, $system);
		$parsed = $this->decodeJsonLenient($suggestions);
		$normalized = $this->normalizeBrandTextResponse($parsed, $effectiveLanguage);
		$ok = is_array($normalized) && isset($normalized['brand_text']);

		BrandChat::create([
			'parent_id' => $parent->id,
			'topic' => 'brand_text',
			'user_id' => $userId,
			'language' => $effectiveLanguage,
			'answers' => $parent->answers,
			'response' => $ok ? $normalized : null,
			'raw_response' => $ok ? null : $suggestions,
		]);

		if ($ok) {
			return ['ok' => true, 'data' => $normalized];
		}

		return [
			'ok' => false,
			'data' => [
				'brand_text' => null,
				'colors' => [],
				'design_details' => [],
				'raw' => $suggestions,
			],
		];
	}

	public function checkDomains(string $name, array $tlds = []): array
	{
		return $this->domains->check($name, $tlds);
	}

	/**
	 * @param  array{domain: string, years?: int, whois_guard?: bool, registrant: array}  $data
	 * @return array{success: bool, reservation_id: int, status: string, provider_order_id?: string|null, error?: string|null, message?: string}
	 */
	public function reserveDomain(array $data, ?int $userId): array
	{
		$domain = (string) $data['domain'];
		$years = (int) ($data['years'] ?? 1);
		$whoisGuard = (bool) ($data['whois_guard'] ?? false);
		$registrant = (array) $data['registrant'];

		$reservation = DomainReservation::create([
			'user_id' => $userId,
			'domain' => $domain,
			'years' => $years,
			'registrant' => $registrant,
			'provider' => 'namecheap',
			'status' => 'pending',
		]);

		$result = $this->namecheap->register($domain, $registrant, $years, $whoisGuard);
		if (($result['ok'] ?? false) === true) {
			$orderId = null;
			if (isset($result['xml']->CommandResponse->DomainCreateResult)) {
				$orderId = (string) $result['xml']->CommandResponse->DomainCreateResult['OrderID'] ?? null;
			}
			$reservation->update([
				'status' => 'success',
				'provider_order_id' => $orderId,
				'response' => json_decode(json_encode($result['xml']), true),
			]);

			return [
				'success' => true,
				'reservation_id' => $reservation->id,
				'status' => $reservation->status,
				'provider_order_id' => $reservation->provider_order_id,
				'message' => 'Domain reserved successfully',
			];
		}

		$reservation->update([
			'status' => 'failed',
			'error' => (string) ($result['error'] ?? 'Unknown error'),
			'response' => isset($result['xml']) ? json_decode(json_encode($result['xml']), true) : null,
		]);

		return [
			'success' => false,
			'reservation_id' => $reservation->id,
			'status' => $reservation->status,
			'error' => $reservation->error,
		];
	}

	private function normalizeBrandTextResponse(?array $parsed, string $language): ?array
	{
		if (!is_array($parsed)) {
			return null;
		}
		if (isset($parsed['brand_text'])) {
			return $parsed;
		}

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
}
