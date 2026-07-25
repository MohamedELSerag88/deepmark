<?php

namespace App\Services\Concerns;

trait DecodesAiJson
{
	/**
	 * Attempts to decode JSON that may be wrapped in Markdown code fences or contain
	 * surrounding text. Falls back to extracting the first balanced JSON object.
	 */
	protected function decodeJsonLenient(string $text): ?array
	{
		$direct = json_decode($text, true);
		if (json_last_error() === JSON_ERROR_NONE && is_array($direct)) {
			return $direct;
		}

		$trimmed = trim($text);

		if (preg_match('/```(?:json)?\\s*([\\s\\S]*?)\\s*```/i', $trimmed, $m)) {
			$block = trim($m[1]);
			$fromFence = json_decode($block, true);
			if (json_last_error() === JSON_ERROR_NONE && is_array($fromFence)) {
				return $fromFence;
			}
		}

		$withoutFences = preg_replace('/```[a-z]*\\s*|```/i', '', $trimmed);
		if (is_string($withoutFences)) {
			$retry = json_decode(trim($withoutFences), true);
			if (json_last_error() === JSON_ERROR_NONE && is_array($retry)) {
				return $retry;
			}
		}

		if (preg_match('/\\{(?:[^{}]|(?R))*\\}/s', $trimmed, $m2)) {
			$object = $m2[0];
			$fromObject = json_decode($object, true);
			if (json_last_error() === JSON_ERROR_NONE && is_array($fromObject)) {
				return $fromObject;
			}
		}

		return null;
	}
}
