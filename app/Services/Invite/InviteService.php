<?php

namespace App\Services\Invite;

use App\Models\Invitation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InviteService
{
	public function listForUser(?int $userId): Collection
	{
		return Invitation::where('user_id', $userId)
			->latest('id')
			->limit(100)
			->get(['id', 'email', 'status', 'accepted_at', 'created_at']);
	}

	/**
	 * @param  array<int, string>  $emails
	 * @return array<int, array{id: int, email: string}>
	 */
	public function store(array $emails, string $message, ?int $userId): array
	{
		$created = [];
		foreach ($emails as $email) {
			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				continue;
			}
			$token = Str::random(40);
			$invite = Invitation::create([
				'user_id' => $userId,
				'email' => $email,
				'token' => $token,
				'status' => 'pending',
			]);
			$link = rtrim(config('app.url'), '/') . '/signup?invite=' . $token;
			$body = "You've been invited to Deepmarks.\n" . ($message ? $message . "\n\n" : '') . "Join using this link: {$link}";

			Mail::raw($body, function ($m) use ($email) {
				$m->to($email)->subject('Invitation to Deepmarks');
			});
			$created[] = ['id' => $invite->id, 'email' => $email];
		}

		return $created;
	}
}
