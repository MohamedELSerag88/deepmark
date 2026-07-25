<?php

namespace App\Services\Brand;

use App\Models\BrandChat;
use App\Models\BrandNameSuggestion;
use Illuminate\Support\Facades\Mail;

class BrandShareService
{
	/**
	 * @param  array{emails: array, subject?: string|null, message?: string|null, names?: array, brand_chat_id?: int|null}  $data
	 */
	public function share(array $data, ?int $userId): void
	{
		$emails = (array) ($data['emails'] ?? []);
		$subject = (string) (($data['subject'] ?? null) ?: 'Brand name suggestions from Deepmarks');
		$message = (string) ($data['message'] ?? '');

		$names = (array) ($data['names'] ?? []);
		if (empty($names) && !empty($data['brand_chat_id'])) {
			$chat = BrandChat::where('id', (int) $data['brand_chat_id'])
				->where('user_id', $userId)->first();
			if ($chat) {
				$names = BrandNameSuggestion::where('brand_chat_id', $chat->id)
					->orderBy('suggestion_index')
					->get()
					->map(fn (BrandNameSuggestion $s) => [
						'suggestion_index' => $s->suggestion_index,
						'id' => $s->suggestion_index,
						'project_id' => $chat->id,
						'name' => $s->name,
						'archetype' => $s->archetype,
						'domains' => $s->domains,
						'liked' => (bool) $s->liked,
					])
					->values()
					->all();
			}
		}

		$lines = [];
		foreach ($names as $n) {
			$primary = $n['domains']['primary']['domain'] ?? null;
			$availability = $n['domains']['primary']['available'] ?? null;
			$tag = $availability === null ? '' : ($availability ? ' (available)' : ' (taken)');
			$lines[] = "- {$n['name']}" . ($primary ? " — {$primary}{$tag}" : '');
		}
		$body = "Hi,\n\n" . (trim($message) !== '' ? $message . "\n\n" : '')
			. "Here are brand name suggestions:\n"
			. implode("\n", $lines ?: ['- <no items>']) . "\n\n"
			. "Shared via Deepmarks.";

		foreach ($emails as $email) {
			Mail::raw($body, function ($m) use ($email, $subject) {
				$m->to($email)->subject($subject);
			});
		}
	}
}
