<?php

namespace App\Http\Controllers\Mobile\v1\Home;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\ShareBrandNamesRequest;
use App\Models\BrandChat;
use App\Models\BrandNameSuggestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

class BrandNameShareController extends Controller
{
	public function share(ShareBrandNamesRequest $request): JsonResponse
	{
		$emails = (array)$request->input('emails', []);
		$subject = (string)($request->input('subject') ?: 'Brand name suggestions from Deepmarks');
		$message = (string)$request->input('message', '');

		$names = (array)$request->input('names', []);
		if (empty($names) && $request->filled('brand_chat_id')) {
			$chat = BrandChat::where('id', (int)$request->input('brand_chat_id'))
				->where('user_id', auth('api')->id())->first();
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
						'liked' => (bool)$s->liked,
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

		return $this->response->statusOk(['message' => 'Shared successfully']);
	}
}


