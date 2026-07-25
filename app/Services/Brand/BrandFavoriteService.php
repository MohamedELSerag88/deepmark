<?php

namespace App\Services\Brand;

use App\Mail\BrandingFollowUpMail;
use App\Models\BrandChat;
use App\Models\BrandNameFavorite;
use App\Models\BrandNameSuggestion;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class BrandFavoriteService
{
	/**
	 * @param  array{brand_chat_id?: mixed, project_id?: mixed, name?: string, archetype?: string}  $filters
	 */
	public function list(?int $userId, array $filters = []): Collection
	{
		$query = BrandNameFavorite::where('user_id', $userId);

		$brandChatId = $filters['brand_chat_id'] ?? $filters['project_id'] ?? null;
		if ($brandChatId !== null && $brandChatId !== '') {
			$query->where('brand_chat_id', (int) $brandChatId);
		}

		if (!empty($filters['name'])) {
			$query->whereHas('suggestion', function ($q) use ($filters) {
				$q->where('name', 'like', '%' . $filters['name'] . '%');
			});
		}
		if (!empty($filters['archetype'])) {
			$query->whereHas('suggestion', function ($q) use ($filters) {
				$q->where('archetype', 'like', '%' . $filters['archetype'] . '%');
			});
		}

		return $query
			->with(['suggestion:id,brand_chat_id,suggestion_index,name,archetype,name_type,linguistic_style,generation_technique,name_length,rationale,description,brand_keywords,why_fits,domains,liked'])
			->latest('id')
			->get([
				'id',
				'brand_chat_id',
				'brand_name_suggestion_id',
				'created_at',
			]);
	}

	/**
	 * @return array{ok: bool, id?: int, error?: string, status?: int}
	 */
	public function store(int $brandChatId, int $suggestionId, ?int $userId): array
	{
		$chat = BrandChat::where('id', $brandChatId)
			->where('user_id', $userId)
			->first();
		if (!$chat) {
			return ['ok' => false, 'error' => 'Project not found', 'status' => 404];
		}

		$suggestion = BrandNameSuggestion::where('id', $suggestionId)
			->where('brand_chat_id', $brandChatId)
			->first();
		if (!$suggestion) {
			return ['ok' => false, 'error' => 'Suggestion not found for this project', 'status' => 404];
		}

		$exists = BrandNameFavorite::where('user_id', $userId)
			->where('brand_chat_id', $brandChatId)
			->where('brand_name_suggestion_id', $suggestionId)
			->first();
		if ($exists) {
			return ['ok' => true, 'id' => $exists->id];
		}

		$item = BrandNameFavorite::create([
			'user_id' => $userId,
			'brand_chat_id' => $brandChatId,
			'brand_name_suggestion_id' => $suggestion->id,
		]);

		$this->maybeSendBrandingFollowUp($chat, $userId);

		return ['ok' => true, 'id' => $item->id];
	}

	private function maybeSendBrandingFollowUp(BrandChat $chat, ?int $userId): void
	{
		if (!$userId || $chat->branding_email_sent) {
			return;
		}

		$count = BrandNameFavorite::where('user_id', $userId)
			->where('brand_chat_id', $chat->id)
			->count();

		if ($count < 2) {
			return;
		}

		$user = User::find($userId);
		if (!$user || empty($user->email)) {
			return;
		}

		$firstName = trim((string) ($user->fname ?? ''));
		if ($firstName === '') {
			$firstName = trim((string) (explode('@', (string) $user->email)[0] ?? 'there'));
		}

		$calendly = (string) config(
			'domains.calendly_branding_url',
			'https://calendly.com/deepmarks-support/30min'
		);

		try {
			Mail::to($user->email)->send(new BrandingFollowUpMail($firstName, $calendly));
			$chat->branding_email_sent = true;
			$chat->save();
		} catch (\Throwable) {
			// Do not fail favorite save if mail transport is unavailable.
		}
	}

	/**
	 * @return array{ok: bool, error?: string, status?: int}
	 */
	public function destroy(int $id, ?int $userId): array
	{
		$item = BrandNameFavorite::where('id', $id)->where('user_id', $userId)->first();
		if (!$item) {
			return ['ok' => false, 'error' => 'Favorite not found', 'status' => 404];
		}
		$item->delete();

		return ['ok' => true];
	}
}
