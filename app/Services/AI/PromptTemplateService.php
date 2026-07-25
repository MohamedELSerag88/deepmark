<?php

namespace App\Services\AI;

use App\Models\AiPrompt;
use App\Models\Question;

class PromptTemplateService
{
	public const PLACEHOLDER_KEYS = [
		'business_description',
		'target_audience',
		'brand_personality',
		'preferred_tone',
		'competitors',
		'market',
		'differentiator',
		'main_feel',
		'qa_block',
		'count',
		'language_hint',
		'brand_name',
		'selected_name',
	];

	public function get(string $key): AiPrompt
	{
		$prompt = AiPrompt::query()->where('key', $key)->first();
		if ($prompt) {
			return $prompt;
		}

		if ($key === AiPrompt::KEY_BRAND_NAMES_GENERATE) {
			return AiPrompt::query()->create([
				'key' => AiPrompt::KEY_BRAND_NAMES_GENERATE,
				'name' => 'Main Name Generation Prompt',
				'system_template' => $this->defaultBrandNamesSystemTemplate(),
				'user_template' => $this->defaultBrandNamesUserTemplate(),
			]);
		}

		if ($key === AiPrompt::KEY_BRAND_NAMES_SIMILAR) {
			return AiPrompt::query()->create([
				'key' => AiPrompt::KEY_BRAND_NAMES_SIMILAR,
				'name' => 'Similar Names Generation Prompt',
				'system_template' => $this->defaultSimilarNamesSystemTemplate(),
				'user_template' => $this->defaultSimilarNamesUserTemplate(),
			]);
		}

		throw new \RuntimeException("AI prompt template [{$key}] is not configured.");
	}

	public function defaultBrandNamesSystemTemplate(): string
	{
		$path = database_path('seeders/data/brand_names_generate_system.txt');
		if (is_file($path)) {
			return (string) file_get_contents($path);
		}

		return "You are a world-class brand strategist.\n\nUSER INPUTS\n• Business description: {{business_description}}\n• Target audience: {{target_audience}}\n• Brand personality: {{brand_personality}}\n• Preferred tone: {{preferred_tone}}\n• Competitive landscape: {{competitors}}\n• Market/Country: {{market}}\n• differentiator: {{differentiator}}\n• Name main feel: {{main_feel}}\n\n{{qa_block}}\n";
	}

	public function defaultBrandNamesUserTemplate(): string
	{
		return <<<'TXT'
After completing the naming process described in the system instructions, return ONLY STRICT JSON (no markdown, no prose outside JSON) with this shape:
{
  "project_name": "short AI project title based on the business description",
  "suggestions": [
    {
      "name": "...",
      "archetype": "The Hero|The Sage|The Caregiver|The Creator|The Explorer|The Rebel|The Magician|The Ruler|The Lover|The Jester|The Everyman|The Innocent",
      "rationale": "brief summary why this name works (1-2 sentences)",
      "description": "short meaning of the name",
      "name_type": "Descriptive|Suggestive|Abstract (Empty Vessel)|Invented|Tweaks to Existing Words / Spelling|Acronyms|Compound",
      "linguistic_style": "e.g. Classical|Portmanteau|Compound|Invented|Metaphorical|Phonetic",
      "generation_technique": "e.g. Metaphoric imagery|Pivot words|Customer feelings|Analogy|Association|Close variation",
      "brand_keywords": ["Keyword1", "Keyword2", "Keyword3"],
      "why_fits": "detailed paragraph on linguistic meaning, emotional impact, and archetype alignment"
    }
  ]
}

Provide exactly {{count}} suggestions drawn from your top recommendations (best first).

Constraints:
- Prefer names that are short, pronounceable, and commercially viable.
- Archetype must be one of the Jungian labels listed above.
- name_type must be one of the listed Name Type values.
- Names should align with the USER INPUTS and recommended archetypes.

{{language_hint}}
TXT;
	}

	public function defaultSimilarNamesSystemTemplate(): string
	{
		$path = database_path('seeders/data/brand_names_similar_system.txt');
		if (is_file($path)) {
			return (string) file_get_contents($path);
		}

		return "Generate brand names stylistically similar to {{brand_name}} / {{selected_name}}.\nBusiness: {{business_description}}\nAudience: {{target_audience}}\nPersonality: {{brand_personality}}\nTone: {{preferred_tone}}\nCompetitors: {{competitors}}\nMarket: {{market}}\nDifferentiator: {{differentiator}}\n{{qa_block}}\n";
	}

	public function defaultSimilarNamesUserTemplate(): string
	{
		return <<<'TXT'
After generating similar-name variations for {{selected_name}}, return ONLY STRICT JSON (no markdown) with this shape:
{
  "suggestions": [
    {
      "name": "...",
      "archetype": "The Hero|The Sage|...",
      "rationale": "brief summary why this name works",
      "description": "short meaning of the name",
      "name_type": "Descriptive|Suggestive|Abstract (Empty Vessel)|Invented|Tweaks to Existing Words / Spelling|Acronyms|Compound",
      "linguistic_style": "...",
      "generation_technique": "Close variation",
      "brand_keywords": ["Keyword1", "Keyword2"],
      "why_fits": "detailed paragraph"
    }
  ]
}

Provide exactly {{count}} suggestions (best first), mimicking structure, archetype/tone, and phonetic feel of {{selected_name}}.

{{language_hint}}
TXT;
	}

	/**
	 * @param  array<int, array{question_id?: mixed, value?: mixed}>  $answers
	 */
	public function buildQaBlock(array $answers): string
	{
		$questionIds = collect($answers)->pluck('question_id')->filter()->all();
		$questions = Question::whereIn('id', $questionIds)->get()->keyBy('id');

		$lines = [];
		foreach ($answers as $item) {
			$q = $questions->get((int) ($item['question_id'] ?? 0));
			if (!$q) {
				continue;
			}
			$value = $item['value'] ?? '';
			if (is_array($value)) {
				$value = implode(', ', $value);
			}
			$lines[] = "- Q: {$q->question_en} | {$q->question_ar}\n  A: {$value}";
		}

		return implode("\n", $lines);
	}

	/**
	 * @param  array<int, array{question_id?: mixed, value?: mixed}>  $answers
	 * @return array<string, string>
	 */
	public function mapAnswerContext(array $answers): array
	{
		$context = [
			'business_description' => '-',
			'target_audience' => '-',
			'brand_personality' => '-',
			'preferred_tone' => '-',
			'competitors' => '-',
			'market' => '-',
			'differentiator' => '-',
			'main_feel' => '-',
			'qa_block' => '-',
		];

		$questionIds = collect($answers)->pluck('question_id')->filter()->all();
		$questions = Question::whereIn('id', $questionIds)->get()->keyBy('id');
		$unmappedLines = [];

		foreach ($answers as $item) {
			$q = $questions->get((int) ($item['question_id'] ?? 0));
			if (!$q) {
				continue;
			}

			$value = $item['value'] ?? '';
			if (is_array($value)) {
				$value = implode(', ', $value);
			}
			$value = trim((string) $value);
			if ($value === '') {
				$value = '-';
			}

			$key = $q->prompt_key;
			if (is_string($key) && $key !== '' && array_key_exists($key, $context) && $key !== 'qa_block') {
				$context[$key] = $value;
				continue;
			}

			$unmappedLines[] = "- Q: {$q->question_en} | {$q->question_ar}\n  A: {$value}";
		}

		$context['qa_block'] = count($unmappedLines) ? implode("\n", $unmappedLines) : '-';

		return $context;
	}

	/**
	 * @param  array<string, scalar|null>  $vars
	 */
	public function render(string $template, array $vars): string
	{
		return (string) preg_replace_callback(
			'/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/',
			function (array $matches) use ($vars) {
				$key = $matches[1];
				if (!array_key_exists($key, $vars) || $vars[$key] === null || $vars[$key] === '') {
					return '-';
				}

				return (string) $vars[$key];
			},
			$template
		);
	}

	/**
	 * @param  array<int, array{question_id?: mixed, value?: mixed}>  $answers
	 * @return array{system: string, user: string}
	 */
	public function buildBrandNamesGenerateMessages(array $answers, int $count, string $language = 'en'): array
	{
		return $this->buildMessages(AiPrompt::KEY_BRAND_NAMES_GENERATE, $answers, $count, $language);
	}

	/**
	 * @param  array<int, array{question_id?: mixed, value?: mixed}>  $answers
	 * @return array{system: string, user: string}
	 */
	public function buildSimilarNamesMessages(
		array $answers,
		string $selectedName,
		int $count,
		string $language = 'en'
	): array {
		$extra = [
			'brand_name' => $selectedName,
			'selected_name' => $selectedName,
		];

		return $this->buildMessages(AiPrompt::KEY_BRAND_NAMES_SIMILAR, $answers, $count, $language, $extra);
	}

	/**
	 * @param  array<int, array{question_id?: mixed, value?: mixed}>  $answers
	 * @param  array<string, string>  $extra
	 * @return array{system: string, user: string}
	 */
	private function buildMessages(
		string $key,
		array $answers,
		int $count,
		string $language,
		array $extra = []
	): array {
		$prompt = $this->get($key);
		$context = array_merge($this->mapAnswerContext($answers), $extra);

		$languageHint = '';
		if ($language === 'ar') {
			$languageHint = 'Language: Provide names that work globally; if Arabic words are used, keep them simple.';
		}

		$context['count'] = (string) $count;
		$context['language_hint'] = $languageHint;

		$system = $this->render((string) $prompt->system_template, $context);
		$user = $this->render((string) ($prompt->user_template ?: ''), $context);

		if (trim($user) === '') {
			$user = $this->render(
				'Return ONLY STRICT JSON with shape: { "suggestions": [ { "name": "...", "archetype": "...", "rationale": "...", "description": "...", "name_type": "...", "linguistic_style": "...", "generation_technique": "...", "brand_keywords": [], "why_fits": "..." } ] } with exactly {{count}} suggestions.',
				$context
			);
		}

		return [
			'system' => $system,
			'user' => $user,
		];
	}
}
